<?php
/**
 * Helper de Validação
 */
class ValidacaoHelper {
    
    /**
     * Validar se um campo é obrigatório
     */
    public static function required($valor) {
        if (is_array($valor)) {
            return !empty($valor);
        }
        return trim($valor) !== '';
    }

    /**
     * Validar email
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar tamanho mínimo
     */
    public static function minLength($valor, $min) {
        return strlen($valor) >= $min;
    }

    /**
     * Validar tamanho máximo
     */
    public static function maxLength($valor, $max) {
        return strlen($valor) <= $max;
    }

    /**
     * Validar se é número
     */
    public static function numeric($valor) {
        return is_numeric($valor);
    }

    /**
     * Validar se é inteiro
     */
    public static function integer($valor) {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validar se é float
     */
    public static function float($valor) {
        return filter_var($valor, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * Validar se é URL
     */
    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validar se é telefone (formato angolano)
     */
    public static function telefone($telefone) {
        // Remove espaços e caracteres especiais
        $limpo = preg_replace('/[^0-9]/', '', $telefone);
        
        // Aceita: 923456789 ou +244923456789
        if (strlen($limpo) === 9) {
            return true;
        }
        if (strlen($limpo) === 12 && substr($limpo, 0, 3) === '244') {
            return true;
        }
        return false;
    }

    /**
     * Validar NIF (simples)
     */
    public static function nif($nif) {
        $limpo = preg_replace('/[^0-9]/', '', $nif);
        return strlen($limpo) >= 9 && strlen($limpo) <= 14;
    }

    /**
     * Validar data no formato YYYY-MM-DD
     */
    public static function data($data) {
        $d = explode('-', $data);
        if (count($d) !== 3) return false;
        return checkdate((int)$d[1], (int)$d[2], (int)$d[0]);
    }

    /**
     * Validar se o valor está entre dois números
     */
    public static function between($valor, $min, $max) {
        return $valor >= $min && $valor <= $max;
    }

    /**
     * Validar se o valor está na lista de opções
     */
    public static function in($valor, $opcoes) {
        return in_array($valor, $opcoes);
    }

    /**
     * Validar senha (mínimo 8 caracteres, com letras e números)
     */
    public static function senha($senha) {
        if (strlen($senha) < 8) return false;
        if (!preg_match('/[A-Za-z]/', $senha)) return false;
        if (!preg_match('/[0-9]/', $senha)) return false;
        return true;
    }

    /**
     * Validar CPF/CNPJ (simplificado para Angola)
     */
    public static function documento($doc) {
        $limpo = preg_replace('/[^0-9]/', '', $doc);
        return strlen($limpo) >= 9;
    }

    /**
     * Validar CEP (simplificado)
     */
    public static function cep($cep) {
        $limpo = preg_replace('/[^0-9]/', '', $cep);
        return strlen($limpo) === 8;
    }

    /**
     * Validar se é um array não vazio
     */
    public static function arrayNaoVazio($arr) {
        return is_array($arr) && !empty($arr);
    }

    /**
     * Validar arquivo (tipo e tamanho)
     */
    public static function arquivo($file, $tiposPermitidos = [], $maxSize = null) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Validar tipo
        if (!empty($tiposPermitidos)) {
            $tipo = mime_content_type($file['tmp_name']);
            if (!in_array($tipo, $tiposPermitidos)) {
                return false;
            }
        }

        // Validar tamanho
        if ($maxSize && $file['size'] > $maxSize) {
            return false;
        }

        return true;
    }

    /**
     * Validar múltiplos campos
     */
    public static function validar($dados, $regras) {
        $erros = [];

        foreach ($regras as $campo => $regrasCampo) {
            $valor = $dados[$campo] ?? null;
            $regrasArray = explode('|', $regrasCampo);

            foreach ($regrasArray as $regra) {
                $param = null;
                if (strpos($regra, ':') !== false) {
                    list($regra, $param) = explode(':', $regra);
                }

                $metodo = $regra;
                if (!method_exists(self::class, $metodo)) {
                    continue;
                }

                $valido = true;
                if ($param !== null) {
                    $valido = self::$metodo($valor, $param);
                } else {
                    $valido = self::$metodo($valor);
                }

                if (!$valido) {
                    $erros[$campo] = "O campo {$campo} é inválido.";
                    break;
                }
            }
        }

        return $erros;
    }
}