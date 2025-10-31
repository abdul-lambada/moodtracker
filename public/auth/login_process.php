<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$token = $_POST['_token'] ?? null;
if (!verify_csrf($token)) {
    flash('errors', ['Token keamanan tidak valid.']);
    redirect('login.php');
}

$noBundy = trim($_POST['no_bundy'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? '../mood/create.php';

if ($noBundy === '' || $password === '') {
    flash('errors', ['Nomor bundy dan kata sandi wajib diisi.']);
    redirect('login.php');
}

if (!AuthManager::attempt($noBundy, $password)) {
    flash('errors', ['Kredensial tidak valid.']);
    redirect('login.php');
}

$user = AuthManager::user();
$target = session_get('__auth_redirect', $redirect);
session_forget('__auth_redirect');

if (($user['role'] ?? 'karyawan') === 'admin') {
    redirect('../admin/index.php');
}

redirect($target ?: '../mood/create.php');
