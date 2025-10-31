<?php

declare(strict_types=1);

use function htmlspecialchars;

$appName = config('app.name', 'MoodTracker');
$title = isset($title) ? (string) $title : 'Terjadi Kesalahan';
$message = isset($message) ? (string) $message : 'Terjadi gangguan pada sistem. Silakan hubungi administrator.';
$statusCode = isset($statusCode) ? (int) $statusCode : 500;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo htmlspecialchars($title); ?> | <?php echo htmlspecialchars($appName); ?></title>
    <link href="../sb-admin-2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link href="../sb-admin-2/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #1cc88a 100%);
        }
        .error-card {
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 30px 60px -25px rgba(15, 23, 42, 0.55);
        }
        .error-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.85);
            color: #4e73df;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: 0 18px 36px -20px rgba(15, 23, 42, 0.4);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-6 col-lg-7 col-md-9">
            <div class="card error-card border-0">
                <div class="card-body p-5 text-center">
                    <div class="error-icon mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1 class="h3 text-gray-900 mb-2"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="mb-4 text-gray-600"><?php echo nl2br(htmlspecialchars($message)); ?></p>
                    <div class="badge badge-pill badge-primary px-3 py-2 mb-4">Kode: <?php echo htmlspecialchars((string) $statusCode); ?></div>
                    <div class="d-flex flex-column flex-sm-row justify-content-center">
                        <a href="javascript:history.back()" class="btn btn-primary btn-icon-split mb-2 mb-sm-0 mr-sm-3">
                            <span class="icon text-white-50">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span class="text">Kembali</span>
                        </a>
                        <a href="/public/admin/index.php" class="btn btn-light btn-icon-split">
                            <span class="icon text-gray-600">
                                <i class="fas fa-home"></i>
                            </span>
                            <span class="text">Ke Dashboard</span>
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center py-3 text-muted small">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. Semua hak dilindungi.
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../sb-admin-2/vendor/jquery/jquery.min.js"></script>
<script src="../sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../sb-admin-2/js/sb-admin-2.min.js"></script>
</body>
</html>
