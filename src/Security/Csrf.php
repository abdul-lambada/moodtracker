<?php

declare(strict_types=1);

namespace App\Security;

use RuntimeException;

final class Csrf
{
    private const SESSION_KEY = '__csrf_tokens';
    private const TOKEN_BYTES = 32;
    private const TTL = 3600;

    public static function token(): string
    {
        self::ensureSession();
        $tokens = $_SESSION[self::SESSION_KEY] ?? [];
        self::sweepExpired($tokens);

        $token = bin2hex(random_bytes(self::TOKEN_BYTES));
        $tokens[$token] = time();
        $_SESSION[self::SESSION_KEY] = $tokens;

        return $token;
    }

    public static function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        self::ensureSession();
        $tokens = $_SESSION[self::SESSION_KEY] ?? [];
        self::sweepExpired($tokens);

        if (!isset($tokens[$token])) {
            return false;
        }

        unset($tokens[$token]);
        $_SESSION[self::SESSION_KEY] = $tokens;

        return true;
    }

    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session must be started before using CSRF protection.');
        }
    }

    private static function sweepExpired(array &$tokens): void
    {
        $now = time();
        foreach ($tokens as $storedToken => $timestamp) {
            if ($now - $timestamp > self::TTL) {
                unset($tokens[$storedToken]);
            }
        }
    }
}
