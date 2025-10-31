<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;
use App\Models\MoodRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('create.php');
}

$token = $_POST['_token'] ?? null;
if (!verify_csrf($token)) {
    flash('errors', ['Token keamanan tidak valid. Silakan coba lagi.']);
    redirect('create.php');
}

$currentUser = AuthManager::user();
if ($currentUser === null) {
    flash('errors', ['Sesi Anda berakhir. Silakan masuk kembali.']);
    redirect('../auth/login.php');
}

$payload = [
    'id_user' => (int) $currentUser['id_user'],
    'tanggal_catatan' => trim($_POST['tanggal_catatan'] ?? ''),
    'mood' => trim($_POST['mood'] ?? ''),
    'catatan_mood' => trim($_POST['catatan_mood'] ?? ''),
    'output_harian' => trim($_POST['output_harian'] ?? ''),
];

store_old([
    'tanggal_catatan' => $payload['tanggal_catatan'],
    'mood' => $payload['mood'],
    'catatan_mood' => $payload['catatan_mood'],
    'output_harian' => $payload['output_harian'],
]);

$errors = [];

if ($payload['tanggal_catatan'] === '') {
    $errors[] = 'Tanggal dan waktu wajib diisi.';
}

if ($payload['mood'] === '') {
    $errors[] = 'Mood wajib dipilih.';
}

$allowedMoods = config('moods.options', []);
if ($payload['mood'] !== '' && !in_array($payload['mood'], $allowedMoods, true)) {
    $errors[] = 'Mood yang dipilih tidak valid.';
}

if ($payload['output_harian'] === '') {
    $errors[] = 'Output harian wajib diisi.';
}

if ($payload['tanggal_catatan'] !== '') {
    $entryTimestamp = strtotime($payload['tanggal_catatan']);
    if ($entryTimestamp === false) {
        $errors[] = 'Tanggal catatan tidak valid.';
    } else {
        $entryDate = date('Y-m-d', $entryTimestamp);
        if (MoodRepository::hasEntryForDate($payload['id_user'], $entryDate)) {
            $errors[] = 'Anda sudah mengisi catatan mood untuk tanggal tersebut.';
        }
    }
}

if (!empty($errors)) {
    flash('errors', $errors);
    redirect('create.php');
}

try {
    MoodRepository::create([
        'id_user' => $payload['id_user'],
        'mood' => $payload['mood'],
        'catatan_mood' => $payload['catatan_mood'] !== '' ? $payload['catatan_mood'] : null,
        'output_harian' => $payload['output_harian'],
        'tanggal_catatan' => date('Y-m-d H:i:s', strtotime($payload['tanggal_catatan'])),
    ]);

    clear_old();
    flash('success', 'Catatan mood berhasil disimpan.');
} catch (Throwable $e) {
    flash('errors', ['Terjadi kesalahan saat menyimpan data. Silakan coba lagi.']);
}

redirect('create.php');
