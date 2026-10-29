<?php

/**
 * Inicializa a sessão com cookies seguros sem alterar o fluxo atual da aplicação.
 * O atributo Secure é habilitado automaticamente quando a requisição usa HTTPS.
 */
function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    $isHttps = isset($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== ''
        && strtolower((string) $_SERVER['HTTPS']) !== 'off';

    // Respeita proxy HTTPS comum quando o cabeçalho é definido pelo proxy confiável.
    if (!$isHttps && !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $isHttps = strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
