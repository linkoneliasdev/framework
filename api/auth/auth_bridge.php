<?php

session_start();

require_once __DIR__ . '/../../app/includes/config.php';
require_once __DIR__ . '/../../app/includes/db.php';

use Kreait\Firebase\Factory;


if (!isset($_POST['idToken'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Token não enviado'
    ]);
    exit;
}

$idToken = $_POST['idToken'];
$nome_input = $_POST['nome'] ?? '';
$phone_input = $_POST['phone'] ?? '';
$device_hash = $_POST['device_hash'] ?? '';

try {

    $factory = (new Factory)
        ->withServiceAccount($_ENV['FIREBASE_CREDENTIALS']);

    $auth = $factory->createAuth();

    $verifiedIdToken = $auth->verifyIdToken($idToken);

    $uid = $verifiedIdToken->claims()->get('sub');
    $email = $verifiedIdToken->claims()->get('email');
    $nome = $nome_input ?: ($verifiedIdToken->claims()->get('name') ?? 'Usuário');

   /*Salvar e dar um update no usuario*/
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
        ':phone' => $phone_input,
        ':device_hash' => $device_hash,
        ':name_update' => $nome
    ]);

    /*Buscar usuario com o role  = nivel*/
    $stmtUser = $pdo->prepare("
        SELECT id, uid, name, email, role
        FROM users
        WHERE uid = ?
        LIMIT 1
    ");

    $stmtUser->execute([$uid]);

    $user = $stmtUser->fetch();

    if (!$user) {
        throw new Exception("Erro ao buscar usuário.");
    }

  /*Criando a session*/
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'uid' => $user['uid'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'user'
    ];

    echo json_encode([
        'success' => true
    ]);

} catch (\Throwable $e) {

    if (APP_DEBUG) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao autenticar'
        ]);
    }
}