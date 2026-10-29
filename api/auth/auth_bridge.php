<?php

require_once __DIR__ . '/../../app/includes/config.php';
require_once __DIR__ . '/../../app/includes/session.php';
startAppSession();
require_once __DIR__ . '/../../app/includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido',
    ]);
    exit;
}

if (empty($_POST['idToken']) || !is_string($_POST['idToken'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token não enviado',
    ]);
    exit;
}

$idToken = $_POST['idToken'];
$nomeInput = trim((string) ($_POST['nome'] ?? ''));
$phoneInput = trim((string) ($_POST['phone'] ?? ''));
$deviceHash = trim((string) ($_POST['device_hash'] ?? ''));

try {
    $factory = (new \Kreait\Firebase\Factory())
        ->withServiceAccount($_ENV['FIREBASE_CREDENTIALS']);

    $auth = $factory->createAuth();
    $verifiedIdToken = $auth->verifyIdToken($idToken);

    $uid = (string) $verifiedIdToken->claims()->get('sub');
    $email = (string) $verifiedIdToken->claims()->get('email');
    $tokenName = (string) ($verifiedIdToken->claims()->get('name') ?? 'Usuário');
    $nome = mb_substr($nomeInput !== '' ? $nomeInput : $tokenName, 0, 150);
    $phone = mb_substr($phoneInput, 0, 30);
    $deviceHash = mb_substr($deviceHash, 0, 255);

    $stmt = $pdo->prepare("
        INSERT INTO users (uid, name, email, phone, device_hash, created_at, last_login)
        VALUES (:uid, :name, :email, :phone, :device_hash, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            name = :name_update,
            last_login = NOW()
    ");

    $stmt->execute([
        ':uid' => $uid,
        ':name' => $nome,
        ':email' => $email,
        ':phone' => $phone,
        ':device_hash' => $deviceHash,
        ':name_update' => $nome,
    ]);

    $stmtUser = $pdo->prepare("
        SELECT id, uid, name, email, role
        FROM users
        WHERE uid = ?
        LIMIT 1
    ");
    $stmtUser->execute([$uid]);
    $user = $stmtUser->fetch();

    if (!$user) {
        throw new RuntimeException('Não foi possível carregar o usuário autenticado.');
    }

    $role = strtolower(trim((string) ($user['role'] ?? 'user')));
    if (!in_array($role, ['admin', 'collaborator', 'user'], true)) {
        $role = 'user';
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'uid' => $user['uid'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $role,
    ];

    echo json_encode(['success' => true]);
} catch (\Throwable $e) {
    http_response_code(401);

    if (APP_DEBUG) {
        error_log('Falha no auth bridge: ' . $e->getMessage());
    }

    echo json_encode([
        'success' => false,
        'message' => APP_DEBUG ? 'Erro ao autenticar. Consulte o log do servidor.' : 'Erro ao autenticar',
    ]);
}
