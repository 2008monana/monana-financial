<?php
/**
 * Helper de Formatação
 */
class FormatacaoHelper {
    
    /**
     * Formatar valor monetário (Kwanza)
     */
    public static function moeda($valor, $simbolo = true) {
        $formatado = number_format($valor, 2, ',', '.');
        return $simbolo ? 'Kz ' . $formatado : $formatado;
    }

    /**
     * Formatar valor monetário (sem Kz)
     */
    public static function moedaSemSimbolo($valor) {
        return number_format($valor, 2, ',', '.');
    }

    /**
     * Formatar data no formato português
     */
    public static function data($data, $formato = 'd/m/Y') {
        if (!$data) return '-';
        $timestamp = strtotime($data);
        return date($formato, $timestamp);
    }

    /**
     * Formatar data e hora
     */
    public static function dataHora($data) {
        if (!$data) return '-';
        $timestamp = strtotime($data);
        return date('d/m/Y H:i', $timestamp);
    }

    /**
     * Formatar número com separadores de milhar
     */
    public static function numero($numero, $decimais = 0) {
        return number_format($numero, $decimais, ',', '.');
    }

    /**
     * Formatar percentual
     */
    public static function percentual($valor, $total, $decimais = 1) {
        if ($total == 0) return '0%';
        return number_format(($valor / $total) * 100, $decimais, ',', '.') . '%';
    }

    /**
     * Formatar telefone (angolano)
     */
    public static function telefone($telefone) {
        $limpo = preg_replace('/[^0-9]/', '', $telefone);
        
        if (strlen($limpo) === 9) {
            return substr($limpo, 0, 3) . ' ' . substr($limpo, 3, 3) . ' ' . substr($limpo, 6, 3);
        }
        
        if (strlen($limpo) === 12 && substr($limpo, 0, 3) === '244') {
            $resto = substr($limpo, 3);
            return '+244 ' . substr($resto, 0, 3) . ' ' . substr($resto, 3, 3) . ' ' . substr($resto, 6, 3);
        }
        
        return $telefone;
    }

    /**
     * Formatar NIF
     */
    public static function nif($nif) {
        $limpo = preg_replace('/[^0-9]/', '', $nif);
        if (strlen($limpo) === 9) {
            return substr($limpo, 0, 3) . '.' . substr($limpo, 3, 3) . '.' . substr($limpo, 6, 3);
        }
        if (strlen($limpo) === 14) {
            return substr($limpo, 0, 2) . '.' . substr($limpo, 2, 3) . '.' . substr($limpo, 5, 3) . '/' . 
                   substr($limpo, 8, 4) . '-' . substr($limpo, 12, 2);
        }
        return $nif;
    }

    /**
     * Converter data para formato ISO (YYYY-MM-DD)
     */
    public static function toISO($data) {
        if (!$data) return null;
        $partes = explode('/', $data);
        if (count($partes) === 3) {
            return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
        }
        return $data;
    }

    /**
     * Converter data ISO para formato português
     */
    public static function fromISO($data) {
        if (!$data) return null;
        $partes = explode('-', $data);
        if (count($partes) === 3) {
            return $partes[2] . '/' . $partes[1] . '/' . $partes[0];
        }
        return $data;
    }

    /**
     * Obter nome do mês
     */
    public static function nomeMes($mes, $abreviado = false) {
        $nomes = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        
        $abreviados = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];
        
        if ($abreviado) {
            return $abreviados[(int)$mes] ?? $mes;
        }
        return $nomes[(int)$mes] ?? $mes;
    }

    /**
     * Obter nome do dia da semana
     */
    public static function nomeDia($data, $abreviado = false) {
        $dias = [
            'Sunday' => 'Domingo', 'Monday' => 'Segunda', 'Tuesday' => 'Terça',
            'Wednesday' => 'Quarta', 'Thursday' => 'Quinta', 'Friday' => 'Sexta',
            'Saturday' => 'Sábado'
        ];
        
        $diasAbreviados = [
            'Sunday' => 'Dom', 'Monday' => 'Seg', 'Tuesday' => 'Ter',
            'Wednesday' => 'Qua', 'Thursday' => 'Qui', 'Friday' => 'Sex',
            'Saturday' => 'Sáb'
        ];
        
        $timestamp = strtotime($data);
        $nome = date('l', $timestamp);
        
        if ($abreviado) {
            return $diasAbreviados[$nome] ?? $nome;
        }
        return $dias[$nome] ?? $nome;
    }

    /**
     * Truncar texto
     */
    public static function truncar($texto, $limite = 50, $sufixo = '...') {
        if (strlen($texto) <= $limite) return $texto;
        return substr($texto, 0, $limite) . $sufixo;
    }

    /**
     * Sanitizar texto
     */
    public static function sanitizar($texto) {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Converter para slug (URL amigável)
     */
    public static function slug($texto) {
        $texto = preg_replace('/[áàãâä]/u', 'a', $texto);
        $texto = preg_replace('/[éèêë]/u', 'e', $texto);
        $texto = preg_replace('/[íìîï]/u', 'i', $texto);
        $texto = preg_replace('/[óòõôö]/u', 'o', $texto);
        $texto = preg_replace('/[úùûü]/u', 'u', $texto);
        $texto = preg_replace('/[ç]/u', 'c', $texto);
        $texto = preg_replace('/[^a-zA-Z0-9\s-]/', '', $texto);
        $texto = preg_replace('/[\s-]+/', '-', $texto);
        $texto = strtolower(trim($texto, '-'));
        return $texto;
    }

    /**
     * Formatar bytes para formato legível
     */
    public static function bytes($bytes, $decimais = 2) {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return number_format($bytes, $decimais, ',', '.') . ' ' . $unidades[$i];
    }

    /**
     * Formatar duração (segundos para H:i:s)
     */
    public static function duracao($segundos) {
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segundos = $segundos % 60;
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
    }
}