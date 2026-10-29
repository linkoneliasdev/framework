<?php

require_once __DIR__ . '/app/includes/session.php';
startAppSession();

require_once __DIR__ . '/app/includes/config.php';
require_once __DIR__ . '/app/includes/db.php';
require_once __DIR__ . '/app/includes/helpers/assets.php';
require_once __DIR__ . '/app/includes/auth.php';
require_once __DIR__ . '/app/includes/layout.php';

requireLogin();

/*
 * Atualiza o papel diretamente do banco em cada requisição.
 * Assim, alterações/revogações de permissão não ficam presas à sessão.
 */
$userRole = syncUserRole($pdo);
$layoutRole = getLayoutRole($userRole);

$pagina = $_GET['pagina'] ?? 'dashboard';
$pagina = trim((string) $pagina, '/');
$pagina = urldecode($pagina);
$pagina = preg_replace('/[^a-zA-Z0-9_\/-]/', '', $pagina);
$pagina = preg_replace('#/+#', '/', $pagina);

if ($pagina === '') {
    $pagina = 'dashboard';
}

if (strpos($pagina, '..') !== false) {
    $pagina = '404';
}

$partes = explode('/', $pagina);

if (count($partes) > 1) {
    $roleNaUrl = array_shift($partes);

    if ($roleNaUrl !== $layoutRole) {
        http_response_code(403);
        $pagina = '403';
    } else {
        $pagina = implode('/', $partes);
    }
}

$pagesRoot = realpath(__DIR__ . '/pages');
$baseDir = $pagesRoot ? $pagesRoot . DIRECTORY_SEPARATOR : '';
$roleDir = $baseDir !== '' ? realpath($baseDir . $layoutRole) : false;
$arquivo = $roleDir ? realpath($roleDir . DIRECTORY_SEPARATOR . $pagina . '.php') : false;

require_once __DIR__ . "/app/layout/{$layoutRole}/header.php";
require_once __DIR__ . "/app/layout/{$layoutRole}/sidebar.php";
require_once __DIR__ . "/app/layout/{$layoutRole}/navbar.php";

echo '<div class="content-wrapper">';
echo '<div class="container-xxl flex-grow-1 container-p-y">';

if ($pagina === '403') {
    include $baseDir . '403.php';
} elseif (
    $arquivo !== false &&
    $roleDir !== false &&
    strpos($arquivo, $roleDir . DIRECTORY_SEPARATOR) === 0 &&
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
