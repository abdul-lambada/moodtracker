<?php

declare(strict_types=1);

use function htmlspecialchars;

$appName = config('app.name', 'MoodTracker');
$title = isset($title) ? (string) $title : 'Terjadi Kesalahan';
$message = isset($message) ? (string) $message : 'Silakan kembali ke halaman sebelumnya atau hubungi administrator.';
$statusCode = isset($statusCode) ? (int) $statusCode : 500;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | <?php echo htmlspecialchars($appName); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="max-w-xl w-full bg-white shadow-xl rounded-3xl px-8 py-10 text-center space-y-6">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-indigo-100 text-indigo-600 text-3xl font-semibold shadow-inner">
                <?php echo htmlspecialchars((string) $statusCode); ?>
            </div>
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold text-slate-900"><?php echo htmlspecialchars($title); ?></h1>
                <p class="text-sm text-slate-500 leading-relaxed"><?php echo nl2br(htmlspecialchars($message)); ?></p>
            </div>
            <div class="pt-2 flex flex-col sm:flex-row sm:justify-center sm:space-x-3 space-y-3 sm:space-y-0">
                <a href="javascript:history.back()" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-700 transition">Kembali</a>
                <a href="/" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
