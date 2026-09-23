<?php
/**
 * views/includes/cabecalho.php
 * Cabeçalho HTML para as views do módulo de Funcionários.
 * Compatível com Bootstrap 5 (CDN) + Font Awesome.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dados do utilizador (compatível com todas as versões da sessão)
$usuario = $_SESSION['usuario'] ?? [
    'id'         => $_SESSION['usuario_id']      ?? 0,
    'nome'       => $_SESSION['usuario_nome']    ?? 'Utilizador',
    'email'      => $_SESSION['usuario_email']   ?? '',
    'perfil'     => $_SESSION['usuario_perfil']  ?? 'visualizador',
    'empresa_id' => $_SESSION['empresa_id']      ?? null,
];

$titulo_pagina = $titulo_pagina ?? 'MonanaFinancial';
$base_url = defined('URL_BASE') ? URL_BASE : '/monana-financial/public';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo_pagina); ?> — MonanaFinancial</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Sora', sans-serif; }
        .card { border: none; border-radius: 12px; }
        .card-header { border-radius: 12px 12px 0 0 !important; }
        .btn { border-radius: 8px; }
        .form-control, .form-select { border-radius: 8px; }
        .table th { font-size: 13px; text-transform: uppercase; letter-spacing: .03em; color: #64748b; }
        a.text-primary { color: #0e2748 !important; }
    </style>
</head>
<body>
