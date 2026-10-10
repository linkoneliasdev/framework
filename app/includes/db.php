<?php

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? '';
$user = $_ENV['DB_USER'] ?? '';
$pass = $_ENV['DB_PASS'] ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

try {

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

    $pdo = new PDO($dsn, $user, $pass, [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES => false,

    ]);

} catch (PDOException $e) {

    if (defined('APP_DEBUG') && APP_DEBUG) {
        die('Erro DB: ' . $e->getMessage());
    }

    die('Erro ao conectar ao banco.');
}