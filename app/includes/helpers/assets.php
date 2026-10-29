<?php

function asset(string $path): string
{
    $relativePath = ltrim($path, '/');
    $fullPath = dirname(__DIR__, 3) . '/public/' . $relativePath;
    $url = '/public/' . $relativePath;

    if (is_file($fullPath)) {
        return $url . '?v=' . (string) filemtime($fullPath);
    }

    return $url;
}
