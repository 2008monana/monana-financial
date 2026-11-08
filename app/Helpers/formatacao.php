<?php
/**
 * Funções auxiliares de formatação, usadas nas views.
 */

if (!function_exists('formatarKz')) {
    function formatarKz(float $valor): string
    {
        return number_format($valor, 0, ',', '.') . ' Kz';
    }
}

if (!function_exists('formatarDataCurta')) {
    function formatarDataCurta(string $dataIso): string
    {
        $timestamp = strtotime($dataIso);
        return $timestamp ? date('d/m', $timestamp) : $dataIso;
    }
}

if (!function_exists('calcularVariacaoPercentual')) {
    function calcularVariacaoPercentual(float $atual, float $anterior): float
    {
        if ($anterior == 0.0) {
            return $atual > 0 ? 100.0 : 0.0;
        }
        return (($atual - $anterior) / abs($anterior)) * 100;
    }
}

if (!function_exists('iniciaisNome')) {
    function iniciaisNome(string $nome): string
    {
        $substr = function_exists('mb_substr') ? 'mb_substr' : 'substr';
        $partes = preg_split('/\s+/', trim($nome));
        $iniciais = strtoupper($substr($partes[0] ?? '', 0, 1));
        if (count($partes) > 1) {
            $iniciais .= strtoupper($substr(end($partes), 0, 1));
        }
        return $iniciais ?: '?';
    }
}

if (!function_exists('nomePerfilFormatado')) {
    function nomePerfilFormatado(string $perfil): string
    {
        return match ($perfil) {
            'super_admin'      => 'Super Administrador',
            'admin_empresa'    => 'Administrador da Empresa',
            'usuario_interno'  => 'Utilizador Interno',
            'visualizador'     => 'Visualizador',
            default            => ucfirst($perfil),
        };
    }
}
