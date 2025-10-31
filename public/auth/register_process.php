<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;
use App\Models\UserRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

$token = $_POST['_token'] ?? null;
if (!verify_csrf($token)) {
    flash('errors', ['Token keamanan tidak valid.']);
    redirect('register.php');
}

$payload = [
    'nama_karyawan' => trim($_POST['nama_karyawan'] ?? ''),
    'no_bundy' => trim($_POST['no_bundy'] ?? ''),
    'password' => $_POST['password'] ?? '',
    'password_confirmation' => $_POST['password_confirmation'] ?? '',
];

store_old($payload);

$errors = [];

if ($payload['nama_karyawan'] === '') {
    $errors[] = 'Nama karyawan wajib diisi.';
}

if ($payload['no_bundy'] === '') {
    $errors[] = 'Nomor bundy wajib diisi.';
}

if ($payload['password'] === '' || strlen($payload['password']) < 6) {
    $errors[] = 'Kata sandi minimal 6 karakter.';
}

if ($payload['password'] !== $payload['password_confirmation']) {
    $errors[] = 'Konfirmasi kata sandi tidak cocok.';
}

if (UserRepository::findByBundy($payload['no_bundy']) !== null) {
    $errors[] = 'Nomor bundy sudah terdaftar.';
}

if (!empty($errors)) {
    flash('errors', $errors);
    redirect('register.php');
}

$userId = UserRepository::create([
    'nama_karyawan' => $payload['nama_karyawan'],
    'no_bundy' => $payload['no_bundy'],
    'password_hash' => password_hash($payload['password'], PASSWORD_BCRYPT),
    'id_posisi' => 1,
    'role' => 'karyawan',
]);

$createdUser = UserRepository::findById($userId);

if ($createdUser !== null) {
    AuthManager::loginUser($createdUser);
    clear_old();
    flash('success', 'Akun berhasil dibuat. Selamat datang, ' . $createdUser['nama_karyawan'] . '!');
    redirect('../mood/create.php');
}

flash('errors', ['Terjadi kesalahan saat membuat akun. Silakan coba lagi.']);
redirect('register.php');
