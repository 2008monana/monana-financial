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
    /** Tamanho máximo permitido para imagens de perfil: 2 MB */
    public const TAMANHO_MAXIMO = 2 * 1024 * 1024;

    /** Tipos MIME aceites para upload de imagem */
    private const TIPOS_MIME_VALIDOS = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/gif', 'image/webp'];

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
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_INI_SIZE
            || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_FORM_SIZE) {
            return self::falha('A imagem excede o limite de 2 MB.');
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return self::falha('Nenhum ficheiro recebido ou erro no upload.');
        }
        if (!is_uploaded_file($file['tmp_name'] ?? '')) {
            return self::falha('Ficheiro de upload inválido.');
        }
        if (($file['size'] ?? 0) > self::TAMANHO_MAXIMO) {
            return self::falha('A imagem excede o limite de 2 MB.');
        }

        // Verificação robusta do tipo real da imagem (não depende apenas do getimagesize)
        $tipoMime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $tipoMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $tipoMime = @mime_content_type($file['tmp_name']);
        }
        if ($tipoMime !== null && !in_array(strtolower($tipoMime), self::TIPOS_MIME_VALIDOS, true)) {
            return self::falha('Formato inválido: use JPG, PNG, GIF ou WebP.');
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
        if (!is_writable($destino)) {
            return self::falha('A pasta de destino não tem permissões de escrita (verifique o Apache/XAMPP).');
        }

        // Extensão derivada do tipo REAL da imagem (nunca do nome enviado pelo utilizador)
        $info = @getimagesize($file['tmp_name']);
        $extensoesPorTipo = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_WEBP => 'webp',
        ];
        $ext = $info !== false ? ($extensoesPorTipo[$info[2]] ?? 'jpg') : 'jpg';

        $nome = 'avatar_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $caminhoCompleto = $destino . '/' . $nome;

        // Em contexto web usa move_uploaded_file; em CLI/teste aceita cópia normal
        $gravou = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $caminhoCompleto)
            : @copy($file['tmp_name'], $caminhoCompleto);

        if (!$gravou || !file_exists($caminhoCompleto)) {
            return self::falha('Falha ao gravar o ficheiro no servidor.');
        }
        @chmod($caminhoCompleto, 0644);

        return [
            'sucesso'   => true,
            'url'       => URL_BASE . '/uploads/' . $subpasta . '/' . $nome,
            'public_id' => 'local:' . $subpasta . '/' . $nome,
            'servidor'  => 'local',
            'erro'      => null,
        ];
    }

    /** Remove um asset do Cloudinary ou local (melhor esforço) */
    public static function removerImagem(string $publicId): bool
    {
        // Ficheiro local em public/uploads/avatares — apaga do disco
        if (strpos($publicId, 'local:') === 0) {
            $relativo = substr($publicId, strlen('local:'));
            if (basename($relativo) !== $relativo && strpos($relativo, '..') === false) {
                $caminho = CAMINHO_UPLOADS . '/' . $relativo;
                if (is_file($caminho)) {
                    @unlink($caminho);
                }
            }
            return true;
        }

        $cfg = self::configuracao();
        if (!$cfg || strpos($publicId, 'uploads/') === 0) {
            return true; // nada a fazer no Cloudinary (upload local antigo)
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
