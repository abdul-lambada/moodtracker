<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap/helpers.php';
require_once dirname(__DIR__) . '/bootstrap/environment.php';
require_once dirname(__DIR__) . '/bootstrap/app.php';
require_once dirname(__DIR__) . '/bootstrap/autoload.php';

use App\Database\Connection;
use App\Auth\AuthManager;
use App\Models\UserRepository;
use App\Models\PositionRepository;

$pdo = Connection::instance();

$stmt = $pdo->prepare('SELECT id_catatan, mood, catatan_mood, output_harian, tanggal_catatan FROM Catatan_Harian ORDER BY tanggal_catatan DESC LIMIT 5');
$stmt->execute();
$latestEntries = $stmt->fetchAll();

$headerUser = null;
$headerPosition = '';

if (AuthManager::check()) {
    $headerUser = AuthManager::user();
    $userDetails = UserRepository::findById((int) $headerUser['id_user']);
    if ($userDetails !== null) {
        $headerUser = array_merge($headerUser, $userDetails);
        if (isset($userDetails['id_posisi'])) {
            $position = PositionRepository::find((int) $userDetails['id_posisi']);
            $headerPosition = $position['nama_posisi'] ?? '';
        }
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodTracker</title>
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
        .emoji-bubble {
            transition: transform 0.4s ease, box-shadow 0.4s ease, background-color 0.4s ease;
            will-change: transform;
        }

        .emoji-bubble:hover {
            transform: translateY(-8px) scale(1.08);
            box-shadow: 0 18px 35px -22px rgba(15, 23, 42, 0.45);
        }

        .emoji-bubble.emoji-active {
            transform: translateY(-10px) scale(1.12);
            background-color: #e2e8f0;
            box-shadow: 0 20px 40px -20px rgba(15, 23, 42, 0.55);
        }

        .emoji-bubble span {
            display: inline-block;
            animation: emojiWave 6s ease-in-out infinite;
        }

        @keyframes emojiWave {
            0%, 100% { transform: rotate(0deg); }
            5% { transform: rotate(8deg); }
            10% { transform: rotate(-6deg); }
            15% { transform: rotate(4deg); }
            20% { transform: rotate(-2deg); }
            25% { transform: rotate(0deg); }
        }

        .hero-card {
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            background-image: radial-gradient(circle at top left, rgba(34, 211, 238, 0.18), transparent 55%), radial-gradient(circle at bottom right, rgba(79, 70, 229, 0.16), transparent 50%);
        }

        .hero-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -34px rgba(15, 23, 42, 0.55);
        }

        .cta-button {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 30px -22px rgba(79, 70, 229, 0.45);
        }

        .cta-button-secondary {
            position: relative;
            overflow: hidden;
        }

        .cta-button-secondary::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent, rgba(79, 70, 229, 0.18), transparent);
            transform: translateX(-150%);
            transition: transform 0.45s ease;
        }

        .cta-button-secondary:hover::after {
            transform: translateX(150%);
        }

        .summary-card {
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: "";
            position: absolute;
            inset: -120% 50% -40% -120%;
            background: linear-gradient(140deg, rgba(34, 211, 238, 0.18), rgba(79, 70, 229, 0.2));
            transform: rotate(10deg);
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .summary-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 26px 48px -30px rgba(15, 23, 42, 0.5);
            border-color: rgba(79, 70, 229, 0.35);
        }

        .summary-card:hover::before {
            opacity: 1;
        }

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
<body class="bg-mood-soft text-mood-ink min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-12 space-y-10 page-enter">
        <header class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between page-enter-delay">
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-bold">MoodTracker</h1>
                <p class="text-sm text-mood-muted mt-1">Catatan mood untuk tim Anda.</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full md:w-auto">
                <nav class="flex flex-wrap justify-center sm:justify-start gap-3 text-sm font-medium">
                    <a href="<?php echo $headerUser !== null ? 'mood/create.php' : 'auth/login_employee.php'; ?>" class="text-mood-muted hover:text-mood-ink">Catat</a>
                    <a href="<?php echo $headerUser !== null ? 'mood/history.php' : 'auth/login_employee.php'; ?>" class="text-mood-muted hover:text-mood-ink">Riwayat</a>
                </nav>
                <?php if ($headerUser !== null): ?>
                    <div class="bg-mood-surface border border-mood-border rounded-2xl px-4 py-3 text-sm text-mood-muted w-full sm:w-auto sm:min-w-[220px] text-center sm:text-right">
                        <div class="font-semibold text-mood-ink">Halo, <?php echo htmlspecialchars($headerUser['nama_karyawan'] ?? ''); ?>!</div>
                        <dl class="mt-3 space-y-1">
                            <div class="flex justify-between">
                                <dt class="text-mood-muted">No Bundy</dt>
                                <dd class="font-medium text-mood-ink"><?php echo htmlspecialchars($headerUser['no_bundy'] ?? '-'); ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-mood-muted">Peran</dt>
                                <dd class="font-medium text-mood-ink"><?php echo htmlspecialchars($headerUser['role'] ?? '-'); ?></dd>
                            </div>
                            <?php if ($headerPosition !== ''): ?>
                                <div class="flex justify-between">
                                    <dt class="text-mood-muted">Posisi</dt>
                                    <dd class="font-medium text-mood-ink"><?php echo htmlspecialchars($headerPosition); ?></dd>
                                </div>
                            <?php endif; ?>
                        </dl>
                        <div class="mt-3 text-xs">
                            <a href="auth/logout.php" class="text-mood-muted hover:text-mood-ink">Keluar</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="auth/login_employee.php" class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-mood-border text-sm font-semibold text-mood-ink hover:bg-mood-surface">Masuk</a>
                <?php endif; ?>
            </div>
        </header>

        <section class="space-y-6 page-enter-delay-lg">
            <div class="bg-mood-surface border border-mood-border rounded-3xl shadow-sm px-6 py-6 space-y-3 hero-card">
                <h2 class="text-3xl font-semibold leading-snug text-mood-ink">Pantau dinamika mood tim Anda secara real-time.</h2>
                <p class="text-base text-mood-muted">MoodTracker membantu organisasi mencatat emosi, memahami konteks, dan menindaklanjuti produktivitas harian dengan cepat.</p>
            </div>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                <a href="<?php echo $headerUser !== null ? 'mood/create.php' : 'auth/login_employee.php'; ?>" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-mood-primary text-white text-sm font-semibold hover:bg-mood-primary-dark cta-button">Catat Mood</a>
                <a href="<?php echo $headerUser !== null ? 'mood/history.php' : 'auth/login_employee.php'; ?>" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full border border-mood-border text-sm font-semibold text-mood-ink hover:bg-mood-surface cta-button cta-button-secondary">Lihat Riwayat</a>
            </div>
        </section>

        <section class="bg-mood-surface border border-mood-border rounded-3xl shadow-sm p-6 space-y-6 page-enter-delay summary-card">
            <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-mood-muted">Mood Hari Ini</p>
                    <h3 class="text-xl font-semibold mt-1 text-mood-ink">Kondisi tim terlihat stabil</h3>
                </div>
                <span class="text-sm text-mood-muted"><?php echo date('D, d M'); ?></span>
            </header>
            <div class="flex items-center gap-3 flex-wrap" id="moodEmojis">
                <?php
                    $preset = ['🤩', '😄', '😐', '😔', '😢'];
                    foreach ($preset as $index => $emoji):
                        $delay = $index * 0.3;
                ?>
                    <div class="emoji-bubble flex flex-col items-center justify-center w-16 h-16 rounded-2xl bg-mood-soft text-2xl" data-delay="<?php echo $delay; ?>">
                        <span style="animation-delay: <?php echo $delay; ?>s;"><?php echo $emoji; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="grid gap-3 text-sm">
                <div class="flex items-center justify-between text-mood-muted">
                    <span>Mood terbanyak</span>
                    <span class="font-medium text-mood-ink">😄 Senang</span>
                </div>
                <div class="flex items-center justify-between text-mood-muted">
                    <span>Catatan terbaru</span>
                    <?php if (count($latestEntries) > 0): ?>
                        <span class="font-medium truncate max-w-[220px] text-mood-ink"><?php echo htmlspecialchars($latestEntries[0]['catatan_mood'] ?? 'Tidak ada catatan.'); ?></span>
                    <?php else: ?>
                        <span class="font-medium text-mood-ink">Belum ada catatan</span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="space-y-4 page-enter-delay-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-lg font-semibold">Catatan mood terbaru</h2>
                <a href="mood/history.php" class="text-sm font-medium text-mood-muted hover:text-mood-ink">Lihat semua</a>
            </div>
            <div class="space-y-3">
                <?php foreach ($latestEntries as $entry): ?>
                    <article class="bg-mood-surface border border-mood-border rounded-2xl px-4 py-3 summary-card">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-sm text-mood-muted">
                            <span><?php echo htmlspecialchars(date('d M, H:i', strtotime($entry['tanggal_catatan']))); ?></span>
                            <span class="font-semibold text-mood-ink"><?php echo htmlspecialchars($entry['mood']); ?></span>
                        </div>
                        <?php if (!empty($entry['catatan_mood'])): ?>
                            <p class="mt-2 text-sm text-mood-ink"><?php echo nl2br(htmlspecialchars($entry['catatan_mood'])); ?></p>
                        <?php endif; ?>
                        <p class="mt-2 text-xs text-mood-muted">Output: <?php echo nl2br(htmlspecialchars($entry['output_harian'] ?? '-')); ?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (count($latestEntries) === 0): ?>
                    <p class="text-sm text-mood-muted">Belum ada catatan tersimpan.</p>
                <?php endif; ?>
            </div>
        </section>

        <footer class="text-xs text-mood-muted border-t border-mood-border pt-6 page-enter-delay">
            <p>&copy; <?php echo date('Y'); ?> MoodTracker. Dibuat untuk mendukung keseimbangan emosional tim.</p>
        </footer>
    </div>
</body>
<script>
    (function () {
        const items = document.querySelectorAll('#moodEmojis .emoji-bubble');
        if (!items.length) return;

        let index = 0;

        function activate(idx) {
            items.forEach(item => item.classList.remove('emoji-active'));
            items[idx].classList.add('emoji-active');
        }

        activate(0);

        setInterval(() => {
            index = (index + 1) % items.length;
            activate(index);
        }, 2200);
    })();
</script>
</html>
