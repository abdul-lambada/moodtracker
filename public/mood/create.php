<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;
use App\Models\UserRepository;
use App\Models\PositionRepository;
use App\Models\MoodRepository;

if (!AuthManager::check()) {
    session_put('__auth_redirect', $_SERVER['REQUEST_URI'] ?? '/mood/create.php');
    flash('errors', ['Silakan masuk terlebih dahulu untuk mencatat mood.']);
    redirect('../auth/login_employee.php');
}

$currentUser = AuthManager::user();
$userRecord = UserRepository::findById((int) $currentUser['id_user']);
$positionName = '';

if ($userRecord && isset($userRecord['id_posisi'])) {
    $position = PositionRepository::find((int) $userRecord['id_posisi']);
    $positionName = $position['nama_posisi'] ?? '';
}

$moodOptions = config('moods.options', []);
$errors = flash('errors') ?? [];
$success = flash('success');
$oldInput = session_get('__old', []);
clear_old();

$today = date('Y-m-d');
$alreadySubmitted = MoodRepository::hasEntryForDate((int) $currentUser['id_user'], $today);
$fieldsetDisabledAttr = $alreadySubmitted ? 'disabled' : '';

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Mood | MoodTracker</title>
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
</head>
<body class="bg-mood-soft text-mood-ink min-h-screen">
    <style>
        .mood-emoji-bubble {
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        }

        .mood-emoji-bubble:hover {
            transform: translateY(-6px) scale(1.05);
            box-shadow: 0 18px 35px -22px rgba(15, 23, 42, 0.35);
        }

        .mood-emoji-bubble.emoji-active {
            transform: translateY(-8px) scale(1.08);
            background-color: #e2e8f0;
            box-shadow: 0 18px 35px -18px rgba(15, 23, 42, 0.45);
        }

        .mood-emoji-bubble span {
            display: inline-block;
        }

        .emoji-animate span {
            animation: emojiWave 0.8s ease forwards;
        }

        @keyframes emojiWave {
            0% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(12deg) scale(1.08); }
            50% { transform: rotate(-10deg) scale(1.12); }
            75% { transform: rotate(6deg) scale(1.05); }
            100% { transform: rotate(0deg) scale(1); }
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

        @keyframes noteGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.15); }
            50% { box-shadow: 0 0 0 6px rgba(79, 70, 229, 0.08); }
        }

        .submission-note {
            animation: fadeInUp 0.6s ease forwards, noteGlow 3.6s ease-in-out infinite;
            display: inline-block;
        }

        .section-card {
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }

        .section-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 45px -30px rgba(15, 23, 42, 0.45);
        }
    </style>
    <div class="max-w-3xl mx-auto px-6 py-12 space-y-6 page-enter">
        <header class="flex items-center justify-between page-enter-delay">
            <div>
                <a href="../index.php" class="text-sm text-mood-muted hover:text-mood-ink">&larr; Beranda</a>
                <h1 class="text-2xl font-semibold mt-2 text-mood-ink">Catat Mood Harian</h1>
                <p class="text-sm text-mood-muted mt-1">Sampaikan perasaan dan aktivitas utama Anda hari ini.</p>
                <p class="text-xs text-mood-muted mt-3 bg-mood-surface border border-mood-border rounded-2xl px-3 py-2 submission-note">Catatan: Setiap karyawan hanya dapat mengirim satu catatan mood per hari.</p>
            </div>
            <div class="bg-mood-surface border border-mood-border rounded-2xl px-4 py-3 text-sm text-mood-muted min-w-[220px] section-card">
                <div class="font-semibold text-mood-ink">Halo, <?php echo htmlspecialchars($currentUser['nama_karyawan']); ?>!</div>
                <dl class="mt-3 space-y-1">
                    <div class="flex justify-between">
                        <dt class="text-mood-muted">No Bundy</dt>
                        <dd class="font-medium text-mood-ink"><?php echo htmlspecialchars($currentUser['no_bundy']); ?></dd>
                    </div>
                    <?php if ($positionName !== ''): ?>
                        <div class="flex justify-between">
                            <dt class="text-mood-muted">Posisi</dt>
                            <dd class="font-medium text-mood-ink"><?php echo htmlspecialchars($positionName); ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </header>

        <?php if ($alreadySubmitted): ?>
            <div class="p-4 rounded-2xl bg-mood-accent/10 border border-mood-accent/30 text-mood-primary text-sm page-enter-delay">
                Anda sudah mengisi catatan mood untuk hari ini. Silakan kembali lagi besok.
            </div>
        <?php elseif (!empty($success)): ?>
            <div class="p-4 rounded-2xl bg-mood-accent/10 border border-mood-accent/30 text-mood-primary text-sm page-enter-delay">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!$alreadySubmitted && !empty($errors)): ?>
            <div class="p-4 rounded-2xl bg-red-50 border border-red-100 text-red-500 text-sm space-y-1 page-enter-delay">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="store.php" method="POST" class="space-y-6 bg-mood-surface border border-mood-border rounded-3xl shadow-sm p-6 page-enter-delay-lg section-card">
            <?php echo csrf_field(); ?>
            <?php if ($alreadySubmitted): ?>
                <p class="text-sm font-medium text-mood-primary">Formulir dimatikan karena catatan untuk hari ini sudah tersimpan.</p>
            <?php endif; ?>
            <fieldset <?php echo $fieldsetDisabledAttr; ?> class="space-y-6 <?php echo $alreadySubmitted ? 'opacity-60 pointer-events-none' : ''; ?>">
                <div>
                    <label for="tanggal_catatan" class="block text-xs font-semibold text-mood-muted uppercase tracking-wide">Tanggal &amp; Waktu</label>
                    <input
                        type="datetime-local"
                        id="tanggal_catatan"
                        name="tanggal_catatan"
                        value="<?php echo htmlspecialchars($oldInput['tanggal_catatan'] ?? date('Y-m-d\TH:i')); ?>"
                        class="mt-2 w-full rounded-2xl border border-mood-border px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-mood-primary"
                        required
                    >
                </div>

                <div>
                    <span class="block text-xs font-semibold text-mood-muted uppercase tracking-wide">Pilih Mood</span>
                    <p class="text-xs text-mood-muted mt-1">Mood Anda akan disimpan bersama catatan opsional.</p>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <?php $selectedMood = $oldInput['mood'] ?? ($moodOptions[0] ?? ''); ?>
                        <?php foreach ($moodOptions as $option): ?>
                            <label class="js-mood-option mood-emoji-bubble relative flex items-center gap-3 rounded-2xl border <?php echo $selectedMood === $option ? 'border-mood-primary bg-mood-primary/5 emoji-active' : 'border-mood-border bg-mood-surface'; ?> px-4 py-2.5 cursor-pointer transition" data-mood="<?php echo htmlspecialchars($option); ?>" data-index="<?php echo $index ?? 0; ?>">
                                <input type="radio" name="mood" value="<?php echo htmlspecialchars($option); ?>" class="sr-only" <?php echo $selectedMood === $option ? 'checked' : ''; ?>>
                                <span class="text-xl" style="animation-delay: <?php echo ($index ?? 0) * 0.3; ?>s;">
                                    <?php echo htmlspecialchars(mb_substr($option, 0, 2)); ?>
                                </span>
                                <span class="text-sm font-medium text-mood-ink"><?php echo htmlspecialchars($option); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label for="catatan_mood" class="block text-xs font-semibold text-mood-muted uppercase tracking-wide">Catatan Mood</label>
                    <textarea
                        id="catatan_mood"
                        name="catatan_mood"
                        rows="4"
                        class="mt-2 w-full rounded-2xl border border-mood-border px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-mood-primary"
                        placeholder="Contoh: Capek karena mesin rusak"
                    ><?php echo htmlspecialchars($oldInput['catatan_mood'] ?? ''); ?></textarea>
                    <p class="text-xs text-mood-muted mt-1">Opsional, ceritakan alasan mood Anda hari ini.</p>
                </div>

                <div>
                    <span class="block text-xs font-semibold text-mood-muted uppercase tracking-wide">Output Harian</span>
                    <p class="text-xs text-mood-muted mt-1">Pilih pencapaian hari ini terhadap target.</p>
                    <?php $outputOptions = ['Sesuai Target', 'Di Bawah Target', 'Di Atas Target']; ?>
                    <?php $selectedOutput = $oldInput['output_harian'] ?? $outputOptions[0]; ?>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <?php foreach ($outputOptions as $idx => $opt): ?>
                            <label class="js-output-option mood-emoji-bubble relative flex items-center gap-3 rounded-2xl border <?php echo $selectedOutput === $opt ? 'border-mood-primary bg-mood-primary/5 emoji-active' : 'border-mood-border bg-mood-surface'; ?> px-4 py-2.5 cursor-pointer transition" data-output="<?php echo htmlspecialchars($opt); ?>" data-index="<?php echo $idx; ?>">
                                <input type="radio" name="output_harian" value="<?php echo htmlspecialchars($opt); ?>" class="sr-only" <?php echo $selectedOutput === $opt ? 'checked' : ''; ?> required>
                                <span class="text-sm font-medium text-mood-ink"><?php echo htmlspecialchars($opt); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="history.php" class="text-sm font-semibold text-mood-muted hover:text-mood-ink">Riwayat</a>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-mood-primary text-white text-sm font-semibold hover:bg-mood-primary-dark">
                        Simpan Catatan
                    </button>
                </div>
            </fieldset>
        </form>
    </div>
</body>
<script>
    (function () {
        const options = document.querySelectorAll('.js-mood-option');
        if (!options.length) return;

        function activateOption(target) {
            options.forEach(option => {
                option.classList.remove('border-mood-primary', 'bg-mood-primary/5');
                option.classList.add('border-mood-border', 'bg-mood-surface');
                option.classList.remove('emoji-active');
            });

            target.classList.remove('border-mood-border', 'bg-mood-surface');
            target.classList.add('border-mood-primary', 'bg-mood-primary/5');
            target.classList.add('emoji-active', 'emoji-animate');
            const radio = target.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }

            const cleanAnimation = () => {
                target.classList.remove('emoji-animate');
                target.removeEventListener('animationend', cleanAnimation);
            };
            target.addEventListener('animationend', cleanAnimation);
        }

        options.forEach(option => {
            option.addEventListener('click', function () {
                activateOption(this);
            });
        });
    })();

    // Output selector behavior (separate from mood)
    (function () {
        const outputs = document.querySelectorAll('.js-output-option');
        if (!outputs.length) return;

        function activate(target) {
            outputs.forEach(el => {
                el.classList.remove('border-mood-primary', 'bg-mood-primary/5', 'emoji-active');
                el.classList.add('border-mood-border', 'bg-mood-surface');
            });

            target.classList.remove('border-mood-border', 'bg-mood-surface');
            target.classList.add('border-mood-primary', 'bg-mood-primary/5', 'emoji-active');
            const radio = target.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        outputs.forEach(el => {
            el.addEventListener('click', function () { activate(this); });
        });
    })();
</script>
</html>
