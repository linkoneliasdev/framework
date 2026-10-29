<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/includes/config.php';
require_once __DIR__ . '/../app/includes/db.php';

header('Content-Type: application/javascript; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, max-age=0');

$key = trim((string) ($_GET['key'] ?? ''));

if (!preg_match('/^prj_[a-f0-9]{32}$/', $key)) {
    http_response_code(404);
    echo '/* RivolLeads: project not found. */';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, public_key FROM projects WHERE public_key = :public_key AND status = 'active' LIMIT 1"
);
$stmt->execute([':public_key' => $key]);
$project = $stmt->fetch();

if (!$project || !filter_var(APP_URL, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url(APP_URL, PHP_URL_SCHEME)), ['http', 'https'], true)) {
    http_response_code(404);
    echo '/* RivolLeads: tracker unavailable. */';
    exit;
}

$endpoint = APP_URL . '/api/collect.php';
$projectKey = (string) $project['public_key'];

echo '(function(){';
echo "'use strict';";
echo 'var endpoint = ' . json_encode($endpoint, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';';
echo 'var projectKey = ' . json_encode($projectKey, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';';
echo <<<'JS'
function makeId() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return window.crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0;
        return (c === 'x' ? r : (r & 3 | 8)).toString(16);
    });
}
function storedId(storage, key) {
    try {
        var value = storage.getItem(key);
        if (!value || !/^[a-f0-9-]{36}$/i.test(value)) {
            value = makeId();
            storage.setItem(key, value);
        }
        return value;
    } catch (e) {
        return makeId();
    }
}
function cleanUrl(value) {
    try {
        var url = new URL(value, window.location.href);
        if (url.protocol !== 'http:' && url.protocol !== 'https:') return '';
        var safe = new URL(url.origin + url.pathname);
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'msclkid'].forEach(function (name) {
            var val = url.searchParams.get(name);
            if (val) safe.searchParams.set(name, val.slice(0, 255));
        });
        return safe.toString().slice(0, 2048);
    } catch (e) {
        return '';
    }
}
function safeText(value, max) {
    if (value === null || typeof value === 'undefined') return null;
    if (typeof value === 'object') {
        try { value = JSON.stringify(value); } catch (e) { return null; }
    }
    return String(value).slice(0, max);
}
function send(name, data) {
    if (typeof name !== 'string' || !/^[a-zA-Z][a-zA-Z0-9_.:-]{0,63}$/.test(name)) return;
    var metadata = {};
    if (data && typeof data === 'object') {
        Object.keys(data).slice(0, 10).forEach(function (key) {
            if (/^[a-zA-Z][a-zA-Z0-9_-]{0,39}$/.test(key)) {
                var value = data[key];
                if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
                    metadata[key] = safeText(value, 500);
                }
            }
        });
    }
    var payload = {
        project_key: projectKey,
        event_name: name,
        client_id: storedId(window.localStorage, 'rivolleads_client_' + projectKey),
        session_id: storedId(window.sessionStorage, 'rivolleads_session_' + projectKey),
        page_url: cleanUrl(window.location.href),
        referrer_url: document.referrer ? cleanUrl(document.referrer) : null,
        event_data: metadata
    };
    try {
        var body = JSON.stringify(payload);
        if (body.length > 8192) return;
        fetch(endpoint, {
            method: 'POST',
            mode: 'no-cors',
            credentials: 'omit',
            keepalive: true,
            headers: { 'Content-Type': 'text/plain;charset=UTF-8' },
            body: body
        }).catch(function () {});
    } catch (e) {}
}
window.RivolLeads = window.RivolLeads || {};
window.RivolLeads.track = function (eventName, data) {
    send(eventName, data);
};
send('pageview', {});
})();
JS;
