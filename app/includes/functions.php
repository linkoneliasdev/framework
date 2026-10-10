<?php 

function asset($path)
{
    $fullPath = __DIR__ . '/../../public/' . ltrim($path, '/');

    if (file_exists($fullPath)) {
        return '/public/' . $path . '?v=' . filemtime($fullPath);
    }

    return '/public/' . $path;
}
?>