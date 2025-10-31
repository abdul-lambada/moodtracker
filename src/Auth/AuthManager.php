<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\UserRepository;

final class AuthManager
{
    private const SESSION_KEY = '__auth_user';

    public static function attempt(string $noBundy, string $password): bool
    {
        $user = UserRepository::findByBundy($noBundy);
        if ($user === null) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        self::storeUser($user);

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    public static function user(): ?array
    {
        $user = $_SESSION[self::SESSION_KEY] ?? null;
        return is_array($user) ? $user : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function loginUser(array $user): void
    {
        self::storeUser($user);
    }

    public static function requireRole(array $roles = []): void
    {
        if (!self::check()) {
            session_put('__auth_redirect', $_SERVER['REQUEST_URI'] ?? '/');
            flash('errors', ['Silakan masuk terlebih dahulu.']);
            redirect('../auth/login.php');
        }

        if ($roles === []) {
            return;
        }

        $user = self::user();
        if ($user === null) {
            redirect('../auth/login.php');
        }

        if (!in_array($user['role'], $roles, true)) {
            flash('errors', ['Anda tidak memiliki akses ke halaman tersebut.']);
            \abort(403, 'admin', [
                'title' => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki hak untuk mengakses halaman ini. Silakan hubungi administrator.',
            ]);
        }
    }

    private static function storeUser(array $user): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'id_user' => (int) $user['id_user'],
            'nama_karyawan' => $user['nama_karyawan'],
            'no_bundy' => $user['no_bundy'],
            'role' => $user['role'],
        ];
    }
}
