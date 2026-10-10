<?php

session_start();

require_once __DIR__ . '/app/includes/config.php';
require_once __DIR__ . '/app/includes/db.php';
require_once __DIR__ . '/app/includes/functions.php';
require_once __DIR__ . '/app/includes/auth.php';
require_once __DIR__ . '/app/includes/layout.php';

requireLogin();

$layoutRole = getLayoutRole();

$pagina =  $_GET['pagina'] ?? '/dashboard';

$pagina = trim($pagina, '/');
    
if ($pagina === '') {
    $pagina =  '/dashboard';
}

$pagina = urldecode($pagina);
$pagina = preg_replace('/[^a-zA-Z0-9_\/\-]/', '', $pagina);
$pagina = preg_replace('#/+#', '/', $pagina);

if (strpos($pagina, '..') !== false) {
    $pagina = '404';
}

if ($layoutRole === 'admin' && !isAdminDb($pdo)) {
    http_response_code(403);
    $pagina = '403';
}

$partes = explode('/', $pagina);

if (count($partes) > 1) {

    $roleNaUrl = $partes[0];

    if ($roleNaUrl !== $layoutRole) {

        http_response_code(403);

        $pagina = '403';

    } else {

        // remove o prefixo (user/admin)
        $pagina = implode('/', array_slice($partes, 1));
    }
}

$baseDir = realpath(__DIR__ . '/pages') . DIRECTORY_SEPARATOR;
$arquivo = realpath($baseDir . $layoutRole . '/' . $pagina . '.php');

require_once __DIR__ . "/app/layout/{$layoutRole}/header.php";
require_once __DIR__ . "/app/layout/{$layoutRole}/sidebar.php";
require_once __DIR__ . "/app/layout/{$layoutRole}/navbar.php";

echo '<div class="content-wrapper">';
echo '<div class="container-xxl flex-grow-1 container-p-y">';

if ($pagina === '403') {
    include $baseDir . '403.php';
} elseif (
    $arquivo &&
    strpos($arquivo, $baseDir) === 0 &&
    is_file($arquivo)
) {
    include $arquivo;
} else {
    http_response_code(404);
    include $baseDir . '404.php';
}

echo '</div>';

require_once __DIR__ . "/app/layout/{$layoutRole}/footer.php";

echo '</div>';

require_once __DIR__ . "/app/layout/{$layoutRole}/scripts.php";