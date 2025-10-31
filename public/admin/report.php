<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;
use App\Models\UserRepository;
use App\Models\MoodRepository;

AuthManager::requireRole(['admin']);
$currentUser = AuthManager::user();

$moodOptions = config('moods.options', []);
if ($moodOptions === []) {
    $moodOptions = ['🤩 Sangat Senang', '😄 Senang', '😐 Netral', '😔 Kurang Semangat', '😢 Depresi'];
}

$users = UserRepository::listWithPosition();

$filters = [
    'start' => $_GET['start'] ?? '',
    'end' => $_GET['end'] ?? '',
    'user' => $_GET['user'] ?? '',
    'mood' => $_GET['mood'] ?? '',
];

$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$perPage = 5;

$reportResult = MoodRepository::report($filters, $page, $perPage);
$results = $reportResult['data'];
$totalRecords = $reportResult['total'];
$totalPages = max((int) ceil($totalRecords / $perPage), 1);

$filterQuery = http_build_query(array_filter(array_merge($filters, ['page' => $page]), static function ($value) {
    return $value !== null && $value !== '';
}));

?><!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Laporan Mood">
    <meta name="author" content="MoodTracker">
    <title>Laporan Mood | MoodTracker</title>
    <link href="../../sb-admin-2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link href="../../sb-admin-2/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">

    <?php $activeMenu = 'report.php'; include __DIR__ . '/partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <?php include __DIR__ . '/partials/topbar.php'; ?>

            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Laporan Mood</h1>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row gy-3 gx-3 align-items-end">
                            <div class="col-lg-3 col-md-6">
                                <label for="start" class="form-label small text-muted">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start" name="start" value="<?php echo htmlspecialchars($filters['start']); ?>">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label for="end" class="form-label small text-muted">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end" name="end" value="<?php echo htmlspecialchars($filters['end']); ?>">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label for="user" class="form-label small text-muted">Karyawan</label>
                                <select class="form-control" id="user" name="user">
                                    <option value="">Semua Karyawan</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo (int) $user['id_user']; ?>" <?php echo ((string) $filters['user'] === (string) $user['id_user']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['nama_karyawan']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label for="mood" class="form-label small text-muted">Mood</label>
                                <select class="form-control" id="mood" name="mood">
                                    <option value="">Semua Mood</option>
                                    <?php foreach ($moodOptions as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $filters['mood'] === $option ? 'selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 d-flex py-2">
                                <button type="submit" class="btn btn-primary flex-grow-1 mr-2">Terapkan</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='report.php';">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Hasil Laporan (<?php echo $totalRecords; ?> catatan)</h6>
                        <?php if ($totalRecords > 0): ?>
                            <a href="report_export.php<?php echo $filterQuery ? '?' . $filterQuery : ''; ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Export PDF</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Mood</th>
                                <th>Catatan</th>
                                <th>Output Harian</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($totalRecords === 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada data untuk filter yang dipilih.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold"><?php echo htmlspecialchars(date('d M Y', strtotime($row['tanggal_catatan']))); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars(date('H:i', strtotime($row['tanggal_catatan']))); ?></div>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold"><?php echo htmlspecialchars($row['nama_karyawan']); ?></div>
                                            <div class="text-muted small">Bundy: <?php echo htmlspecialchars($row['no_bundy']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['mood']); ?></td>
                                        <td><?php echo htmlspecialchars($row['catatan_mood'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['output_harian'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($totalPages > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php
                                    $baseQuery = array_filter($filters, static function ($value) {
                                        return $value !== null && $value !== '';
                                    });
                                    ?>
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="report.php?<?php echo http_build_query(array_merge($baseQuery, ['page' => $page - 1])); ?>">Sebelumnya</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="report.php?<?php echo http_build_query(array_merge($baseQuery, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="report.php?<?php echo http_build_query(array_merge($baseQuery, ['page' => $page + 1])); ?>">Berikutnya</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/partials/footer.php'; ?>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="../../sb-admin-2/vendor/jquery/jquery.min.js"></script>
<script src="../../sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../sb-admin-2/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../../sb-admin-2/js/sb-admin-2.min.js"></script>
</body>

</html>
