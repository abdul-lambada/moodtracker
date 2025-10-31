<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;
use App\Models\UserRepository;

if (AuthManager::check()) {
    $user = AuthManager::user();
    if (($user['role'] ?? 'karyawan') === 'admin') {
        redirect('../admin/index.php');
    }

    redirect('../mood/create.php');
}

$errors = flash('errors') ?? [];
$success = flash('success');
$oldInput = session_get('__old', []);
clear_old();

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Karyawan | MoodTracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'mood-primary': '#4F46E5',
                        'mood-primary-dark': '#4338CA',
                        'mood-accent': '#22D3EE',
                        'mood-surface': '#FFFFFF',
                        'mood-soft': '#EEF2FF',
                        'mood-muted': '#64748B',
                        'mood-ink': '#0F172A',
                        'mood-border': '#CBD5F5'
                    }
                }
            }
        };
    </script>
    <style>
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(18px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .page-enter {
            opacity: 0;
            transform: translateY(18px);
            animation: fadeInUp 0.6s ease forwards;
        }

        .page-enter-delay {
            opacity: 0;
            transform: translateY(18px);
            animation: fadeInUp 0.6s ease forwards;
            animation-delay: 0.18s;
        }

        .page-enter-delay-lg {
            opacity: 0;
            transform: translateY(18px);
            animation: fadeInUp 0.6s ease forwards;
            animation-delay: 0.32s;
        }
    </style>
</head>
<body class="bg-mood-soft text-mood-ink min-h-screen flex items-center justify-center">
    <div class="w-full max-w-lg px-6 py-10 bg-mood-surface border border-mood-border rounded-3xl shadow-xl page-enter">
        <div class="text-center space-y-2 page-enter-delay">
            <p class="text-sm text-mood-muted">MoodTracker</p>
            <h1 class="text-3xl font-semibold text-mood-ink">Daftar Akun Karyawan</h1>
            <p class="text-sm text-mood-muted">Isi data di bawah untuk mulai mencatat mood harian. Posisi akan ditetapkan otomatis oleh admin.</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="mt-6 p-3 rounded-2xl bg-mood-accent/10 border border-mood-accent/30 text-mood-primary text-sm page-enter-delay">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mt-6 p-3 rounded-2xl bg-red-50 border border-red-100 text-red-500 text-sm space-y-1 page-enter-delay">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register_process.php" method="POST" class="mt-8 space-y-5 page-enter-delay-lg">
            <?php echo csrf_field(); ?>
            <div>
                <label for="nama_karyawan" class="block text-sm font-semibold text-mood-muted">Nama Karyawan</label>
                <input type="text" id="nama_karyawan" name="nama_karyawan" value="<?php echo htmlspecialchars($oldInput['nama_karyawan'] ?? ''); ?>" class="mt-2 w-full rounded-2xl border border-mood-border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-mood-primary" placeholder="Masukkan nama lengkap" required>
            </div>
            <div>
                <label for="no_bundy" class="block text-sm font-semibold text-mood-muted">Nomor Bundy</label>
                <input type="text" id="no_bundy" name="no_bundy" value="<?php echo htmlspecialchars($oldInput['no_bundy'] ?? ''); ?>" class="mt-2 w-full rounded-2xl border border-mood-border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-mood-primary" placeholder="Masukkan nomor bundy" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-mood-muted">Kata Sandi</label>
                <input type="password" id="password" name="password" class="mt-2 w-full rounded-2xl border border-mood-border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-mood-primary" placeholder="Minimal 6 karakter" required>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-mood-muted">Konfirmasi Kata Sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="mt-2 w-full rounded-2xl border border-mood-border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-mood-primary" placeholder="Ulangi kata sandi" required>
            </div>
            <button type="submit" class="w-full bg-mood-primary hover:bg-mood-primary-dark text-white font-semibold py-3 rounded-2xl">Daftar</button>
        </form>

        <div class="mt-6 text-center text-sm text-mood-muted space-y-2">
            <div>
                Sudah punya akun?
                <a href="login_employee.php" class="text-mood-primary hover:text-mood-primary-dark">Masuk di sini</a>
            </div>
            <a href="../index.php" class="text-mood-muted hover:text-mood-ink">&larr; Kembali ke Landing Page</a>
        </div>
    </div>
</body>
</html>
