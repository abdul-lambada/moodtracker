<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Models\MoodRepository;
use App\Models\UserRepository;

$userId = isset($_GET['user']) ? (int) $_GET['user'] : null;
$date = isset($_GET['date']) ? trim($_GET['date']) : null;
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$perPage = 5;

if ($date !== null && $date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = null;
}

$users = UserRepository::all();
$paginationResult = MoodRepository::filterPaginated($userId ?: null, $date ?: null, $page, $perPage);
$moods = $paginationResult['data'];

if ($page > $paginationResult['last_page'] && $paginationResult['last_page'] > 0) {
    $redirectParams = [];
    if ($userId !== null && $userId > 0) {
        $redirectParams['user'] = $userId;
    }
    if ($date !== null && $date !== '') {
        $redirectParams['date'] = $date;
    }
    $redirectParams['page'] = $paginationResult['last_page'];
    redirect('history.php' . ($redirectParams !== [] ? '?' . http_build_query($redirectParams) : ''));
}

$filterParams = [];
if ($userId !== null && $userId > 0) {
    $filterParams['user'] = $userId;
}
if ($date !== null && $date !== '') {
    $filterParams['date'] = $date;
}

$buildPageUrl = static function (int $targetPage) use ($filterParams) {
    $params = $filterParams;
    $params['page'] = $targetPage;
    $query = http_build_query($params);
    return 'history.php' . ($query !== '' ? '?' . $query : '');
};

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Mood | MoodTracker</title>
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

        .filter-card {
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .filter-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 40px -32px rgba(15, 23, 42, 0.45);
            border-color: rgba(79, 70, 229, 0.35);
        }

        .history-card {
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            position: relative;
            overflow: hidden;
        }

        .history-card::before {
            content: "";
            position: absolute;
            inset: -120% 60% 0 -120%;
            background: linear-gradient(120deg, rgba(34, 211, 238, 0.15), rgba(79, 70, 229, 0.25));
            transform: rotate(12deg);
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .history-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 45px -28px rgba(15, 23, 42, 0.5);
            border-color: rgba(79, 70, 229, 0.35);
        }

        .history-card:hover::before {
            opacity: 1;
        }

        .history-card > * {
            position: relative;
        }
    </style>
    <div class="max-w-4xl mx-auto px-6 py-12 space-y-8 page-enter">
        <header class="flex items-center justify-between page-enter-delay">
            <div>
                <a href="../index.php" class="text-sm text-mood-muted hover:text-mood-ink">&larr; Beranda</a>
                <h1 class="text-2xl font-semibold mt-2 text-mood-ink">Riwayat Mood</h1>
                <p class="text-sm text-mood-muted mt-1">Telusuri catatan mood berdasarkan karyawan dan tanggal.</p>
            </div>
            <a href="create.php" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-mood-primary text-white text-sm font-medium hover:bg-mood-primary-dark">+ Catat Mood</a>
        </header>

        <form method="GET" class="bg-mood-surface border border-mood-border rounded-3xl shadow-sm p-5 grid grid-cols-1 md:grid-cols-4 gap-4 page-enter-delay filter-card">
            <input type="hidden" name="page" value="1">
            <div class="md:col-span-2">
                <label for="user" class="text-xs font-semibold text-mood-muted uppercase tracking-wide">Karyawan</label>
                <select id="user" name="user" class="mt-2 w-full rounded-2xl border border-mood-border px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-mood-primary">
                    <option value="">Semua karyawan</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo (int) $user['id_user']; ?>" <?php echo ($userId === (int) $user['id_user']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['nama_karyawan']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="date" class="text-xs font-semibold text-mood-muted uppercase tracking-wide">Tanggal</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date ?? ''); ?>" class="mt-2 w-full rounded-2xl border border-mood-border px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-mood-primary">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-2xl bg-mood-primary text-white text-sm font-semibold hover:bg-mood-primary-dark">Terapkan</button>
                <a href="history.php" class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl border border-mood-border text-mood-muted text-sm font-semibold hover:text-mood-ink">Reset</a>
            </div>
        </form>

        <section class="space-y-3 page-enter-delay-lg">
            <?php if (count($moods) === 0): ?>
                <div class="bg-mood-surface border border-mood-border rounded-3xl py-6 text-center text-sm text-mood-muted">Tidak ada catatan untuk filter yang dipilih.</div>
            <?php else: ?>
                <?php foreach ($moods as $item): ?>
                    <article class="bg-mood-surface border border-mood-border rounded-3xl p-5 space-y-2 history-card">
                        <div class="flex items-center justify-between text-xs text-mood-muted">
                            <span><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($item['tanggal_catatan']))); ?></span>
                            <span class="font-semibold text-mood-ink"><?php echo htmlspecialchars($item['mood']); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-mood-ink">
                            <span><?php echo htmlspecialchars($item['nama_karyawan']); ?></span>
                            <span class="text-xs text-mood-muted">Bundy: <?php echo htmlspecialchars($item['no_bundy']); ?></span>
                        </div>
                        <?php if (!empty($item['catatan_mood'])): ?>
                            <p class="text-sm text-mood-ink">Catatan: <?php echo nl2br(htmlspecialchars($item['catatan_mood'])); ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-mood-muted">Output: <?php echo nl2br(htmlspecialchars($item['output_harian'] ?? '-')); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <?php if ($paginationResult['last_page'] > 1): ?>
            <?php
                $currentPage = $paginationResult['current_page'];
                $lastPage = $paginationResult['last_page'];
                $total = $paginationResult['total'];
                $start = $total === 0 ? 0 : (($currentPage - 1) * $paginationResult['per_page']) + 1;
                $end = min($total, $currentPage * $paginationResult['per_page']);
                $windowStart = max(1, $currentPage - 2);
                $windowEnd = min($lastPage, $currentPage + 2);
            ?>
            <div class="page-enter-delay flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-sm text-mood-muted">
                <div>
                    Menampilkan <?php echo $start; ?>-<?php echo $end; ?> dari <?php echo $total; ?> catatan.
                </div>
                <nav class="flex flex-wrap items-center gap-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?php echo htmlspecialchars($buildPageUrl($currentPage - 1)); ?>" class="px-3 py-1 rounded-full border border-mood-border text-mood-muted hover:text-mood-ink">Sebelumnya</a>
                    <?php endif; ?>

                    <?php if ($windowStart > 1): ?>
                        <a href="<?php echo htmlspecialchars($buildPageUrl(1)); ?>" class="px-3 py-1 rounded-full border border-mood-border text-mood-muted hover:text-mood-ink">1</a>
                        <?php if ($windowStart > 2): ?>
                            <span class="px-2">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                        <?php if ($i === $currentPage): ?>
                            <span class="px-3 py-1 rounded-full bg-mood-primary text-white"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($buildPageUrl($i)); ?>" class="px-3 py-1 rounded-full border border-mood-border text-mood-muted hover:text-mood-ink"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($windowEnd < $lastPage): ?>
                        <?php if ($windowEnd < $lastPage - 1): ?>
                            <span class="px-2">...</span>
                        <?php endif; ?>
                        <a href="<?php echo htmlspecialchars($buildPageUrl($lastPage)); ?>" class="px-3 py-1 rounded-full border border-mood-border text-mood-muted hover:text-mood-ink"><?php echo $lastPage; ?></a>
                    <?php endif; ?>

                    <?php if ($currentPage < $lastPage): ?>
                        <a href="<?php echo htmlspecialchars($buildPageUrl($currentPage + 1)); ?>" class="px-3 py-1 rounded-full border border-mood-border text-mood-muted hover:text-mood-ink">Berikutnya</a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
