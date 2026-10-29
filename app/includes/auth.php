<?php

function isLogged(): bool
{
    return isset($_SESSION['user']['id']) && (int) $_SESSION['user']['id'] > 0;
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function currentUserRole(): string
{
    $role = $_SESSION['user']['role'] ?? 'user';

    return in_array($role, ['admin', 'collaborator', 'user'], true)
        ? $role
        : 'user';
}

/**
 * Revalida o papel no banco em cada requisição autenticada.
 * O papel nunca deve ser aceito de parâmetros enviados pelo navegador.
 */
function syncUserRole(PDO $pdo): string
{
    if (!isLogged()) {
        return 'user';
    }

    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $_SESSION['user']['id']]);
    $role = $stmt->fetchColumn();

    if ($role === false) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        header('Location: /login');
        exit;
    }

    $role = strtolower(trim((string) $role));

    if (!in_array($role, ['admin', 'collaborator', 'user'], true)) {
        $role = 'user';
    }

    $_SESSION['user']['role'] = $role;
    $_SESSION['user']['role_checked_at'] = time();

    return $role;
}

function isAdmin(): bool
{
    return currentUserRole() === 'admin';
}

function isCollaborator(): bool
{
    return currentUserRole() === 'collaborator';
}

/**
 * Mantido para compatibilidade com código existente.
 * Agora a função também sincroniza o papel com o banco.
 */
function isAdminDb(PDO $pdo): bool
{
    return syncUserRole($pdo) === 'admin';
}

function requireRole(array $allowedRoles, PDO $pdo): void
{
    $role = syncUserRole($pdo);

    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        header('Location: /403');
        exit;
    }
}

function requireLogin(): void
{
    if (!isLogged()) {
        header('Location: /login');
        exit;
    }
}

function requireGuest(): void
{
    if (isLogged()) {
        header('Location: /');
        exit;
    }
}
