<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login_employee.php');
}

$token = $_POST['_token'] ?? null;
if (!verify_csrf($token)) {
    flash('errors', ['Token keamanan tidak valid.']);
    redirect('login_employee.php');
}

$noBundy = trim($_POST['no_bundy'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? '../mood/create.php';

if ($noBundy === '' || $password === '') {
    flash('errors', ['Nomor bundy dan kata sandi wajib diisi.']);
    redirect('login_employee.php');
}

if (!AuthManager::attempt($noBundy, $password)) {
    flash('errors', ['Kredensial tidak valid.']);
    redirect('login_employee.php');
}

$user = AuthManager::user();
if (($user['role'] ?? 'karyawan') === 'admin') {
    flash('errors', ['Gunakan halaman login admin untuk akun tersebut.']);
    AuthManager::logout();
    redirect('login.php');
}

$target = session_get('__auth_redirect', $redirect);
session_forget('__auth_redirect');
redirect($target ?: '../mood/create.php');
