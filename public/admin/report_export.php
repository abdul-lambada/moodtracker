<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Auth\AuthManager;
use App\Models\MoodRepository;
use App\Models\UserRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

AuthManager::requireRole(['admin']);

$filters = [
    'start' => $_GET['start'] ?? '',
    'end' => $_GET['end'] ?? '',
    'user' => $_GET['user'] ?? '',
    'mood' => $_GET['mood'] ?? '',
];

$reportResult = MoodRepository::report($filters);
$data = $reportResult['data'] ?? [];
$totalRecords = count($data);

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

$users = UserRepository::listWithPosition();
$userMap = [];
foreach ($users as $user) {
    $userMap[(int) $user['id_user']] = $user['nama_karyawan'];
}

$filterSummary = [];
if ($filters['start']) {
    $filterSummary[] = 'Mulai: ' . htmlspecialchars($filters['start']);
}
if ($filters['end']) {
    $filterSummary[] = 'Selesai: ' . htmlspecialchars($filters['end']);
}
if ($filters['user'] && isset($userMap[(int) $filters['user']])) {
    $filterSummary[] = 'Karyawan: ' . htmlspecialchars($userMap[(int) $filters['user']]);
}
if ($filters['mood']) {
    $filterSummary[] = 'Mood: ' . htmlspecialchars($filters['mood']);
}

$uniqueUsers = [];
$moodDistribution = [];
foreach ($data as $row) {
    $uniqueUsers[$row['id_user']] = true;
    $moodKey = $row['mood'] ?: 'Tidak diketahui';
    if (!isset($moodDistribution[$moodKey])) {
        $moodDistribution[$moodKey] = 0;
    }
    $moodDistribution[$moodKey]++;
}
$totalUsers = count($uniqueUsers);

$filenameParts = [];
$filenameParts[] = 'laporan-mood';
if ($filters['start'] && $filters['end']) {
    $filenameParts[] = $filters['start'] . '_to_' . $filters['end'];
} elseif ($filters['start']) {
    $filenameParts[] = 'from_' . $filters['start'];
} elseif ($filters['end']) {
    $filenameParts[] = 'until_' . $filters['end'];
}
if ($filters['user'] && isset($userMap[(int) $filters['user']])) {
    $filenameParts[] = strtolower(str_replace(' ', '-', $userMap[(int) $filters['user']]));
}
if ($filters['mood']) {
    $filenameParts[] = strtolower(str_replace([' ', '🤩', '😄', '😐', '😔', '😢'], ['', '', '', '', '', ''], $filters['mood']));
}
$filenameParts[] = date('Ymd_His');
$fileName = implode('_', array_filter($filenameParts));

ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 18px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; font-weight: 600; }
        .meta { font-size: 12px; color: #6b7280; margin: 2px 0; }
        .summary-table td { border: none; padding: 4px 0; }
        .summary-highlight { font-size: 14px; font-weight: 600; }
        .badge { display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; border-radius: 9999px; padding: 4px 10px; font-size: 11px; margin-right: 6px; }
        .mood-chip { display: inline-block; padding: 3px 8px; border-radius: 9999px; background: #f3f4f6; margin-right: 4px; font-size: 11px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Catatan Mood</h1>
    <p class="meta">Dibuat pada: <?php echo date('d M Y H:i'); ?> &nbsp; • &nbsp; Total catatan: <?php echo $totalRecords; ?></p>
    <?php if (!empty($filterSummary)): ?>
        <p class="meta">Filter: <?php echo implode(' | ', $filterSummary); ?></p>
    <?php endif; ?>

    <table class="summary-table">
        <tr>
            <td class="summary-highlight">Total Catatan:</td>
            <td><?php echo $totalRecords; ?></td>
        </tr>
        <tr>
            <td class="summary-highlight">Jumlah Karyawan:</td>
            <td><?php echo $totalUsers; ?></td>
        </tr>
        <tr>
            <td class="summary-highlight">Distribusi Mood:</td>
            <td>
                <?php if (empty($moodDistribution)): ?>
                    <span class="meta">Tidak ada data.</span>
                <?php else: ?>
                    <?php foreach ($moodDistribution as $moodLabel => $count): ?>
                        <span class="mood-chip"><?php echo htmlspecialchars($moodLabel); ?> &times; <?php echo $count; ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <table>
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>Karyawan</th>
            <th>Mood</th>
            <th>Catatan</th>
            <th>Output</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($totalRecords === 0): ?>
            <tr>
                <td colspan="5" style="text-align:center; color:#9ca3af;">Tidak ada data untuk filter ini.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php $timestamp = $row['tanggal_catatan'] ? strtotime($row['tanggal_catatan']) : null; ?>
                    <td>
                        <strong><?php echo $timestamp ? htmlspecialchars(date('d M Y', $timestamp)) : '-'; ?></strong><br>
                        <span class="meta"><?php echo $timestamp ? htmlspecialchars(date('H:i', $timestamp)) : ''; ?></span>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['nama_karyawan']); ?></strong><br>
                        <span class="meta">Bundy: <?php echo htmlspecialchars($row['no_bundy']); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($row['mood']); ?></td>
                    <td><?php echo htmlspecialchars($row['catatan_mood'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['output_harian'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream($fileName . '.pdf', ['Attachment' => true]);
exit;
