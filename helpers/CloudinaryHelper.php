<?php
/**
 * CloudinaryHelper
 * Upload de imagens (fotos de perfil) para o Cloudinary via API REST.
 *
 * Configuração em config/cloudinary.php:
 *   return [
 *       'nuvem'  => 'nome-da-nuvem',
 *       'chave'  => 'api_key',
 *       'segredo'=> 'api_secret',
 *   ];
 *
 * Se a configuração estiver ausente ou incompleta, o upload local é usado
 * como fallback (pasta public/uploads/avatares), mantendo o sistema funcional
 * sem credenciais Cloudinary.
 */

require_once CAMINHO_RAIZ . '/config/constants.php';

class CloudinaryHelper
{
    /** Lê e valida a configuração do Cloudinary */
    public static function configuracao(): ?array
    {
        $arquivo = CAMINHO_RAIZ . '/config/cloudinary.php';
        if (!file_exists($arquivo)) {
            return null;
        }
        $cfg = require $arquivo;
        if (!is_array($cfg)
            || empty($cfg['nuvem']) || empty($cfg['chave']) || empty($cfg['segredo'])
            || $cfg['nuvem'] === 'EXEMPLO') {
            return null;
        }
        return $cfg;
    }

    /**
     * Faz upload de um ficheiro de imagem.
     *
     * @param  array  $file   Item de $_FILES (ex.: $_FILES['foto'])
     * @param  string $pasta  Pasta/prefixo no Cloudinary (ex.: 'avatares')
     * @return array          ['sucesso'=>bool, 'url'=>string, 'public_id'=>?string, 'erro'=>?string]
     */
    public static function enviarImagem(array $file, string $pasta = 'monana/avatares'): array
    {
        // Validações básicas antes de qualquer upload
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return self::falha('Nenhum ficheiro recebido ou erro no upload.');
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return self::falha('A imagem excede o limite de 2 MB.');
        }

        $info = @getimagesize($file['tmp_name']);
        $tiposPermitidos = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_WEBP => 'webp',
        ];
        if ($info === false || !isset($tiposPermitidos[$info[2]])) {
            return self::falha('Formato inválido: use JPG, PNG, GIF ou WebP.');
        }

        $cfg = self::configuracao();
        return $cfg ? self::enviarCloudinary($file, $pasta, $cfg)
                    : self::enviarLocal($file, $pasta);
    }

    /** Upload assinado para a API de upload do Cloudinary */
    private static function enviarCloudinary(array $file, string $pasta, array $cfg): array
    {
        $publicId = $pasta . '/' . bin2hex(random_bytes(8));
        $timestamp = time();

        // Assinatura SHA1 dos parâmetros (ordem alfabética)
        $params = [
            'folder'    => $pasta,
            'public_id' => basename($publicId),
            'timestamp' => $timestamp,
            'upload_preset' => $cfg['preset'] ?? '',
        ];
        $params = array_filter($params, fn($v) => $v !== '');
        ksort($params);
        $assinaturaBase = urldecode(http_build_query($params)) . $cfg['segredo'];
        $assinatura = sha1($assinaturaBase);

        $url = "https://api.cloudinary.com/v1_1/{$cfg['nuvem']}/image/upload";

        $postfields = [
            'file'        => new CURLFile($file['tmp_name'], 'image/' . pathinfo($file['name'], PATHINFO_EXTENSION), $file['name']),
            'api_key'     => $cfg['chave'],
            'timestamp'   => $timestamp,
            'signature'   => $assinatura,
            'folder'      => $pasta,
            'public_id'   => basename($publicId),
        ];
        if (!empty($params['upload_preset'])) {
            $postfields['upload_preset'] = $params['upload_preset'];
        }

        if (!function_exists('curl_init')) {
            return self::enviarLocal($file, $pasta); // fallback se ext-curl indisponível
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT    => 30,
        ]);
        $resposta = curl_exec($ch);
        $codigo   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erroCurl = curl_error($ch);
        curl_close($ch);

        if ($resposta === false) {
            return self::falha('Falha de conexão ao Cloudinary: ' . $erroCurl);
        }

        $json = json_decode($resposta, true);
        if ($codigo !== 200 || empty($json['secure_url'])) {
            $msg = $json['error']['message'] ?? "Resposta inesperada (HTTP {$codigo}).";
            return self::falha('Cloudinary: ' . $msg);
        }

        return [
            'sucesso'    => true,
            'url'        => $json['secure_url'],
            'public_id'  => $json['public_id'] ?? $publicId,
            'servidor'   => 'cloudinary',
            'erro'       => null,
        ];
    }

    /** Fallback: grava na pasta local public/uploads/avatares */
    private static function enviarLocal(array $file, string $pasta): array
    {
        $subpasta = basename($pasta); // 'avatares'
        $destino  = CAMINHO_UPLOADS . '/' . $subpasta;
        if (!is_dir($destino) && !@mkdir($destino, 0775, true)) {
            return self::falha('Não foi possível criar a pasta de destino.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
        $nome = 'avatar_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $caminhoCompleto = $destino . '/' . $nome;

        $gravou = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $caminhoCompleto)
            : @rename($file['tmp_name'], $caminhoCompleto); // contexto de teste/CLI

        if (!$gravou) {
            return self::falha('Falha ao gravar o ficheiro no servidor.');
        }
        @chmod($caminhoCompleto, 0644);

        return [
            'sucesso'   => true,
            'url'       => URL_BASE . '/uploads/' . $subpasta . '/' . $nome,
            'public_id' => 'uploads/' . $subpasta . '/' . $nome,
            'servidor'  => 'local',
            'erro'      => null,
        ];
    }

    /** Remove um asset do Cloudinary (melhor esforço) */
    public static function removerImagem(string $publicId): bool
    {
        $cfg = self::configuracao();
        if (!$cfg || strpos($publicId, 'uploads/') === 0) {
            return true; // nada a fazer no Cloudinary (upload local)
        }
        if (!function_exists('curl_init')) {
            return false;
        }
        $timestamp = time();
        $assinatura = sha1("public_id={$publicId}&timestamp={$timestamp}{$cfg['segredo']}");
        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cfg['nuvem']}/image/destroy");
        curl_setopt_array($ch, [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'public_id' => $publicId,
                'version'   => $timestamp,
                'signature' => $assinatura,
                'api_key'   => $cfg['chave'],
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT    => 20,
        ]);
        $resposta = curl_exec($ch);
        curl_close($ch);
        $json = json_decode((string) $resposta, true);
        return ($json['result'] ?? '') === 'ok';
    }

    private static function falha(string $erro): array
    {
        return ['sucesso' => false, 'url' => null, 'public_id' => null, 'erro' => $erro];
    }
}
