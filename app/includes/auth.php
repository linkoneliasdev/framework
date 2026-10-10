<?php

function isLogged()
{
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function isAdmin()
{
    return isset($_SESSION['user']['role']) 
        && $_SESSION['user']['role'] === 'admin';
}

function isAdminDb($pdo)
{
    if (!isset($_SESSION['user']['id'])) {
        return false;
    }

    // cache de 5 minutos
    if (isset($_SESSION['user']['role_checked_at'])) {

        $lastCheck = $_SESSION['user']['role_checked_at'];

        if (time() - $lastCheck < 300) {
            return $_SESSION['user']['role'] === 'admin';
        }
    }

    // consulta banco
    $stmt = $pdo->prepare("
        SELECT role 
        FROM users 
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$_SESSION['user']['id']]);

    $role = $stmt->fetchColumn() ?: 'user';

    // atualiza sessão
    $_SESSION['user']['role'] = $role;
    $_SESSION['user']['role_checked_at'] = time();

    return $role === 'admin';
}

function currentUserRole()
{
    return $_SESSION['user']['role'] ?? 'user';
}

function requireLogin()
{
    if (!isLogged()) {
        header('Location: /login');
        exit;
    }
}

function requireGuest()
{
    if (isLogged()) {
        header("Location: /");
        exit;
    }
}
