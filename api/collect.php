<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/includes/config.php';
require_once __DIR__ . '/../app/includes/db.php';

header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

function collectFail(int $status = 204): never
{
    http_response_code($status);
    exit;
}

function collectHost(string $url): ?string
{
    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['host'])) {
        return null;
    }

    $host = strtolower(rtrim((string) $parts['host'], '.'));
    if ($host === '' || strlen($host) > 253) {
        return null;
    }

    return $host;
}

function collectCleanUrl($value, int $max = 2048): ?string
{
    if (!is_string($value) || $value === '') {
        return null;
    }

    $parts = parse_url($value);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
        return null;
    }

    $scheme = strtolower((string) $parts['scheme']);
    if (!in_array($scheme, ['http', 'https'], true)) {
        return null;
    }

    $host = strtolower((string) $parts['host']);
    $path = (string) ($parts['path'] ?? '/');
    $path = '/' . ltrim($path, '/');
    $safe = $scheme . '://' . $host . $path;

    if (!empty($parts['query'])) {
        parse_str((string) $parts['query'], $query);
        $allowed = [];
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'msclkid'] as $name) {
            if (isset($query[$name]) && is_scalar($query[$name])) {
                $value = trim((string) $query[$name]);
                if ($value !== '') {
                    $allowed[$name] = mb_substr($value, 0, 255);
                }
            }
        }
        if ($allowed) {
            $safe .= '?' . http_build_query($allowed);
        }
    }

    return mb_substr($safe, 0, $max);
}

$raw = (string) file_get_contents('php://input');
if ($raw === '' || strlen($raw) > 8192) {
    collectFail(413);
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    collectFail(400);
}

$key = trim((string) ($data['project_key'] ?? ''));
$eventName = trim((string) ($data['event_name'] ?? ''));
$pageUrl = collectCleanUrl($data['page_url'] ?? null);
$referrerUrl = collectCleanUrl($data['referrer_url'] ?? null);

if (
    !preg_match('/^prj_[a-f0-9]{32}$/', $key)
    || !preg_match('/^[a-zA-Z][a-zA-Z0-9_.:-]{0,63}$/', $eventName)
    || $pageUrl === null
) {
    collectFail(400);
}

$origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
$refererHeader = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
$originHost = $origin !== '' && strtolower($origin) !== 'null'
    ? collectHost($origin)
    : collectHost($refererHeader);

if ($originHost === null) {
    collectFail(403);
}

$stmt = $pdo->prepare(
    "SELECT id, domain FROM projects
     WHERE public_key = :public_key AND status = 'active'
     LIMIT 1"
);
$stmt->execute([':public_key' => $key]);
$project = $stmt->fetch();

if (!$project) {
    collectFail(404);
}

$projectHost = strtolower(rtrim((string) $project['domain'], '.'));
$pageHost = collectHost($pageUrl);

if ($originHost !== $projectHost || $pageHost !== $projectHost) {
    collectFail(403);
}

$clientId = (string) ($data['client_id'] ?? '');
$sessionId = (string) ($data['session_id'] ?? '');

if (!preg_match('/^[a-f0-9-]{36}$/i', $clientId)) {
    $clientId = '';
}
if (!preg_match('/^[a-f0-9-]{36}$/i', $sessionId)) {
    $sessionId = '';
}

$eventData = $data['event_data'] ?? [];
if (!is_array($eventData)) {
    $eventData = [];
}

$cleanEventData = [];
foreach (array_slice($eventData, 0, 10, true) as $name => $value) {
    if (
        is_string($name)
        && preg_match('/^[a-zA-Z][a-zA-Z0-9_-]{0,39}$/', $name)
        && (is_string($value) || is_numeric($value) || is_bool($value))
    ) {
        $cleanEventData[$name] = mb_substr((string) $value, 0, 500);
    }
}

$encodedEventData = json_encode($cleanEventData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($encodedEventData === false || strlen($encodedEventData) > 4096) {
    $encodedEventData = '{}';
}

$pageQuery = [];
parse_str((string) parse_url($pageUrl, PHP_URL_QUERY), $pageQuery);
$utm = [];
foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $field) {
    $value = $pageQuery[$field] ?? null;
    $utm[$field] = is_scalar($value) && trim((string) $value) !== ''
        ? mb_substr(trim((string) $value), 0, 255)
        : null;
}

$insert = $pdo->prepare(
    'INSERT INTO project_events
        (project_id, event_name, client_id, session_id, page_url, referrer_url,
         utm_source, utm_medium, utm_campaign, utm_term, utm_content, event_data)
     VALUES
        (:project_id, :event_name, :client_id, :session_id, :page_url, :referrer_url,
         :utm_source, :utm_medium, :utm_campaign, :utm_term, :utm_content, :event_data)'
);

$insert->execute([
    ':project_id' => (int) $project['id'],
    ':event_name' => $eventName,
    ':client_id' => $clientId !== '' ? $clientId : null,
    ':session_id' => $sessionId !== '' ? $sessionId : null,
    ':page_url' => $pageUrl,
    ':referrer_url' => $referrerUrl,
    ':utm_source' => $utm['utm_source'],
    ':utm_medium' => $utm['utm_medium'],
    ':utm_campaign' => $utm['utm_campaign'],
    ':utm_term' => $utm['utm_term'],
    ':utm_content' => $utm['utm_content'],
    ':event_data' => $encodedEventData,
]);

http_response_code(204);
