<?php

function getLayoutRole()
{
    $role = currentUserRole();

    $allowedRoles = [
        'user',
        'admin'
    ];

    if (!in_array($role, $allowedRoles, true)) {
        return 'user';
    }

    return $role;
}

function isActive($paginas)
{
    // página atual SEM role
    $paginaAtual = $_GET['pagina'] ?? 'dashboard';

    $paginaAtual = trim($paginaAtual, '/');
    $paginaAtual = urldecode($paginaAtual);
    $paginaAtual = preg_replace('/[^a-zA-Z0-9_\-]/', '', $paginaAtual);
    $paginaAtual = strtolower($paginaAtual ?: 'dashboard');

    $paginas = (array) $paginas;

    foreach ($paginas as $pagina) {

        $pagina = strtolower(trim($pagina, '/'));

        if ($pagina === $paginaAtual) {
            return 'active open';
        }
    }

    return '';
}