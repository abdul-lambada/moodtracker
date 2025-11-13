<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Models\MoodRepository;
use App\Models\UserRepository;
use App\Models\AuditRepository;
use App\Auth\AuthManager;

AuthManager::requireRole(['admin']);
$currentUser = AuthManager::user();

$totalMoods = MoodRepository::countAll();
$totalUsers = UserRepository::countAll();
$latestMood = MoodRepository::latestMood();
$topMood = MoodRepository::topMood();
$moodStats = MoodRepository::statsByMood();
$dailyTrend = MoodRepository::dailyTrend(14);
$latestAudits = AuditRepository::latest(5);
$latestEntries = MoodRepository::latest(5);
$moodColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'];

?><!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="MoodTracker Dashboard">
    <meta name="author" content="MoodTracker">

    <title>MoodTracker Dashboard</title>

    <link href="../../sb-admin-2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link href="../../sb-admin-2/css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <div id="wrapper">

        <?php $activeMenu = 'index.php'; include __DIR__ . '/partials/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <?php include __DIR__ . '/partials/topbar.php'; ?>

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <a href="../mood/create.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Catat Mood Baru</a>
                    </div>

                    <div class="row">

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Catatan</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalMoods); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-heart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Karyawan</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalUsers); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Mood Terbanyak</div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo htmlspecialchars($topMood['mood'] ?? '-'); ?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1"><?php echo isset($topMood['total']) ? (int) $topMood['total'] . ' catatan' : 'Belum ada catatan'; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Catatan Per Hari</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($dailyTrend); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Tren Catatan 14 Hari Terakhir</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="trendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Distribusi Mood</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="moodChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <?php foreach ($moodStats as $index => $stat): ?>
                                            <span class="mr-2">
                                                <i class="fas fa-circle" style="color: <?php echo $moodColors[$index % count($moodColors)]; ?>;"></i> <?php echo htmlspecialchars($stat['mood']); ?> (<?php echo (int) $stat['total']; ?>)
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($moodStats) === 0): ?>
                                            <span class="text-muted">Belum ada data</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Catatan Mood Terbaru</h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($latestEntries as $item): ?>
                                        <div class="mb-3">
                                            <div class="small text-gray-500"><?php echo date('d M Y H:i', strtotime($item['tanggal_catatan'])); ?></div>
                                            <div class="font-weight-bold text-primary">
                                                <?php echo htmlspecialchars($item['nama_karyawan']); ?>
                                                <span class="badge badge-pill badge-info ml-2"><?php echo htmlspecialchars($item['mood']); ?></span>
                                            </div>
                                            <?php if (!empty($item['catatan_mood'])): ?>
                                                <div class="small text-gray-600"><?php echo htmlspecialchars($item['catatan_mood']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($latestEntries) === 0): ?>
                                        <p class="text-muted small mb-0">Belum ada catatan tersimpan.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terakhir</h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($latestAudits as $audit): ?>
                                        <div class="mb-3">
                                            <div class="small text-gray-500"><?php echo date('d M Y H:i', strtotime($audit['created_at'])); ?></div>
                                            <div class="font-weight-bold text-primary"><?php echo htmlspecialchars($audit['action']); ?> &mdash; <?php echo htmlspecialchars($audit['entity']); ?></div>
                                            <div class="small text-gray-600"><?php echo htmlspecialchars($audit['description'] ?? '-'); ?></div>
                                            <div class="small text-gray-400">Petugas: <?php echo htmlspecialchars($audit['nama_karyawan'] ?? 'Sistem'); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($latestAudits) === 0): ?>
                                        <p class="text-muted small mb-0">Belum ada aktivitas terekam.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
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
    <script src="../../sb-admin-2/vendor/chart.js/Chart.min.js"></script>

    <script>
        const trendData = <?php echo json_encode($dailyTrend, JSON_THROW_ON_ERROR); ?>;
        const moodStats = <?php echo json_encode($moodStats, JSON_THROW_ON_ERROR); ?>;

        const trendLabels = trendData.map(item => item.tanggal);
        const trendValues = trendData.map(item => parseInt(item.total, 10));

        const ctxTrend = document.getElementById('trendChart');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Total Catatan',
                    data: trendValues,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    lineTension: 0.3,
                    fill: true,
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        time: { unit: 'date' },
                        gridLines: { display: false },
                        ticks: { maxTicksLimit: 10 }
                    }],
                    yAxes: [{
                        ticks: {
                            min: 0,
                            callback: value => Number.isInteger(value) ? value : null
                        },
                        gridLines: { color: 'rgb(234, 236, 244)', zeroLineColor: 'rgb(234, 236, 244)', drawBorder: false }
                    }]
                },
                legend: { display: false }
            }
        });

        const moodLabels = moodStats.map(item => item.mood);
        const moodValues = moodStats.map(item => parseInt(item.total, 10));

        const ctxMood = document.getElementById('moodChart');
        const moodColors = <?php echo json_encode($moodColors, JSON_THROW_ON_ERROR); ?>;
        new Chart(ctxMood, {
            type: 'doughnut',
            data: {
                labels: moodLabels,
                datasets: [{
                    data: moodValues,
                    backgroundColor: moodColors,
                }]
            },
            options: {
                maintainAspectRatio: false,
                legend: { display: false },
                cutoutPercentage: 70
            }
        });
    </script>

</body>

</html>
