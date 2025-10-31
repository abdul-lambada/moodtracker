<?php

declare(strict_types=1);

(function (): void {
    $dotenvPath = base_path('.env');
    if (!file_exists($dotenvPath)) {
        return;
    }

    $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, null);
        $name = trim($name);
        $value = $value === null ? '' : trim($value);

        if ($name === '') {
            continue;
        }

        if (!array_key_exists($name, $_ENV) && !array_key_exists($name, $_SERVER)) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
        }
    }
})();
