<?php

function getLayoutRole(?string $role = null): string
{
    $role = $role ?? currentUserRole();

    $allowedRoles = [
        'user',
        'collaborator',
        'admin',
    ];

    return in_array($role, $allowedRoles, true) ? $role : 'user';
}

function isActive($paginas): string
{
    $paginaAtual = $_GET['pagina'] ?? 'dashboard';
    $paginaAtual = trim((string) $paginaAtual, '/');
    $paginaAtual = urldecode($paginaAtual);
    $paginaAtual = preg_replace('/[^a-zA-Z0-9_\/-]/', '', $paginaAtual);
    $paginaAtual = strtolower($paginaAtual ?: 'dashboard');

    // Quando a URL contém o prefixo do perfil, compara apenas o nome da página.
    $partes = explode('/', $paginaAtual);
    if (count($partes) > 1 && in_array($partes[0], ['admin', 'collaborator', 'user'], true)) {
        array_shift($partes);
        $paginaAtual = implode('/', $partes);
    }

    foreach ((array) $paginas as $pagina) {
        $pagina = strtolower(trim((string) $pagina, '/'));

        if ($pagina === $paginaAtual) {
            return 'active open';
        }
    }

    return '';
}
