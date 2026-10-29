<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/includes/session.php';
startAppSession();

require_once __DIR__ . '/../app/includes/config.php';
require_once __DIR__ . '/../app/includes/db.php';
require_once __DIR__ . '/../app/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

function projectJson(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    projectJson(405, ['success' => false, 'message' => 'Método não permitido.']);
}

$user = currentUser();
$userId = (int) ($user['id'] ?? 0);

if ($userId <= 0) {
    projectJson(401, ['success' => false, 'message' => 'Sua sessão expirou. Entre novamente.']);
}

$csrfSession = (string) ($_SESSION['csrf_projects'] ?? '');
$csrfRequest = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

if ($csrfSession === '' || $csrfRequest === '' || !hash_equals($csrfSession, $csrfRequest)) {
    projectJson(419, ['success' => false, 'message' => 'Sessão do formulário expirada. Atualize a página e tente novamente.']);
}

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = (string) ($input['action'] ?? '');

function normalizeProjectDomain(string $value): ?string
{
    $value = trim(mb_strtolower($value, 'UTF-8'));

    if ($value === '' || strlen($value) > 300) {
        return null;
    }

    if (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $value)) {
        $value = 'https://' . $value;
    }

    $parts = parse_url($value);
    if (!is_array($parts) || empty($parts['host'])) {
        return null;
    }

    if (
        isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])
        || (isset($parts['path']) && $parts['path'] !== '' && $parts['path'] !== '/')
        || isset($parts['query']) || isset($parts['fragment'])
    ) {
        return null;
    }

    $host = rtrim(strtolower((string) $parts['host']), '.');

    if (
        $host === ''
        || strlen($host) > 253
        || !str_contains($host, '.')
        || filter_var($host, FILTER_VALIDATE_IP)
        || !preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(?:\.(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?))+$/i', $host)
    ) {
        return null;
    }

    return $host;
}

try {
    if ($action === 'create') {
        $name = trim((string) ($input['name'] ?? ''));
        $domain = normalizeProjectDomain((string) ($input['domain'] ?? ''));

        if ($name === '' || mb_strlen($name) > 120) {
            projectJson(422, ['success' => false, 'message' => 'Informe um nome de projeto com até 120 caracteres.']);
        }

        if ($domain === null) {
            projectJson(422, ['success' => false, 'message' => 'Informe um domínio válido, como exemplo.com.br. Não inclua caminhos ou portas.']);
        }

        // Chave pública identificadora, não é uma senha nem um segredo.
        $publicKey = 'prj_' . bin2hex(random_bytes(16));

        $stmt = $pdo->prepare(
            "INSERT INTO projects (user_id, name, domain, public_key, status)
             VALUES (:user_id, :name, :domain, :public_key, 'active')"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':domain' => $domain,
            ':public_key' => $publicKey,
        ]);

        projectJson(201, [
            'success' => true,
            'message' => 'Projeto criado com sucesso.',
            'project' => [
                'id' => (int) $pdo->lastInsertId(),
                'name' => $name,
                'domain' => $domain,
                'public_key' => $publicKey,
                'status' => 'active',
            ],
        ]);
    }

    if ($action === 'update') {
        $projectId = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        $name = trim((string) ($input['name'] ?? ''));
        $domain = normalizeProjectDomain((string) ($input['domain'] ?? ''));

        if (!$projectId || $projectId <= 0) {
            projectJson(422, ['success' => false, 'message' => 'Projeto inválido.']);
        }
        if ($name === '' || mb_strlen($name) > 120) {
            projectJson(422, ['success' => false, 'message' => 'Informe um nome com até 120 caracteres.']);
        }
        if ($domain === null) {
            projectJson(422, ['success' => false, 'message' => 'Informe um domínio válido, como exemplo.com.br.']);
        }

        $stmt = $pdo->prepare(
            'UPDATE projects
             SET name = :name, domain = :domain
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':name' => $name,
            ':domain' => $domain,
            ':id' => $projectId,
            ':user_id' => $userId,
        ]);

        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ? LIMIT 1');
            $check->execute([$projectId, $userId]);
            if (!$check->fetchColumn()) {
                projectJson(404, ['success' => false, 'message' => 'Projeto não encontrado.']);
            }
        }

        projectJson(200, ['success' => true, 'message' => 'Projeto atualizado.']);
    }

    if ($action === 'toggle') {
        $projectId = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        $newStatus = (string) ($input['status'] ?? '');

        if (!$projectId || $projectId <= 0 || !in_array($newStatus, ['active', 'inactive'], true)) {
            projectJson(422, ['success' => false, 'message' => 'Dados inválidos para alterar o status.']);
        }

        $stmt = $pdo->prepare(
            'UPDATE projects SET status = :status WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':status' => $newStatus,
            ':id' => $projectId,
            ':user_id' => $userId,
        ]);

        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ? LIMIT 1');
            $check->execute([$projectId, $userId]);
            if (!$check->fetchColumn()) {
                projectJson(404, ['success' => false, 'message' => 'Projeto não encontrado.']);
            }
        }

        projectJson(200, ['success' => true, 'message' => 'Status atualizado.']);
    }

    projectJson(400, ['success' => false, 'message' => 'Ação inválida.']);
} catch (Throwable $e) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        error_log('Projects API: ' . $e->getMessage());
    }

    projectJson(500, [
        'success' => false,
        'message' => 'Não foi possível processar a solicitação. Tente novamente.',
    ]);
}
