<?php
declare(strict_types=1);

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    return;
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    return;
}

foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
        continue;
    }
    [$k, $v] = explode('=', $line, 2);
    $key = trim($k);
    $value = trim($v, " \t\n\r\0\x0B\"");
    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
}
