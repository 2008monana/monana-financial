<?php
/**
 * Configuração Cloudinary (opcional)
 *
 * Copie/edite este ficheiro com as suas credenciais de
 * https://cloudinary.com/console -> "Dashboard > Keys":
 *
 *   'nuvem'   => nome da sua cloud (ex.: 'monana-financeiro')
 *   'chave'   => API Key
 *   'segredo' => API Secret
 *   'preset'  => (opcional) Upload Preset sem assinatura
 *
 * Enquanto 'nuvem' for 'EXEMPLO', o sistema usa automaticamente
 * upload local em public/uploads/avatares (fallback).
 */

return [
    'nuvem'   => 'EXEMPLO',
    'chave'   => '',
    'segredo' => '',
    'preset'  => '',
];
