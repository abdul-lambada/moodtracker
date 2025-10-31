<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;
use App\Models\UserRepository;
use App\Models\PositionRepository;
use App\Models\AuditRepository;

AuthManager::requireRole(['admin']);
$currentUser = AuthManager::user();

const PER_PAGE = 5;

$query = $_GET['q'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$result = UserRepository::paginateWithPosition($query, $page, PER_PAGE);
$users = $result['data'];
$totalUsers = $result['total'];
$totalPages = max((int) ceil($totalUsers / PER_PAGE), 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_token'] ?? null;
    if (!verify_csrf($token)) {
        flash('errors', ['Token keamanan tidak valid.']);
        redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
    }

    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'create':
            $payload = [
                'nama_karyawan' => trim($_POST['nama_karyawan'] ?? ''),
                'no_bundy' => trim($_POST['no_bundy'] ?? ''),
                'id_posisi' => (int) ($_POST['id_posisi'] ?? 0),
                'role' => trim($_POST['role'] ?? 'karyawan'),
                'password' => $_POST['password'] ?? '',
            ];

            $errors = [];
            if ($payload['nama_karyawan'] === '') {
                $errors[] = 'Nama karyawan wajib diisi.';
            }
            if ($payload['no_bundy'] === '') {
                $errors[] = 'Nomor bundy wajib diisi.';
            }
            if ($payload['id_posisi'] <= 0) {
                $errors[] = 'Posisi wajib dipilih.';
            }
            if ($payload['password'] === '' || strlen($payload['password']) < 6) {
                $errors[] = 'Kata sandi minimal 6 karakter.';
            }

            if (UserRepository::findByBundy($payload['no_bundy']) !== null) {
                $errors[] = 'Nomor bundy sudah digunakan.';
            }

            if (!in_array($payload['role'], ['admin', 'karyawan'], true)) {
                $errors[] = 'Peran tidak valid.';
            }

            if (!empty($errors)) {
                store_old(array_merge($payload, ['password' => '', '__form' => 'user_create']));
                flash('errors', $errors);
                redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
            }

            $userId = UserRepository::create([
                'nama_karyawan' => $payload['nama_karyawan'],
                'no_bundy' => $payload['no_bundy'],
                'password_hash' => password_hash($payload['password'], PASSWORD_BCRYPT),
                'id_posisi' => $payload['id_posisi'],
                'role' => $payload['role'],
            ]);

            AuditRepository::record($currentUser['id_user'] ?? null, 'create', 'Users', $userId, 'Menambahkan karyawan ' . $payload['nama_karyawan']);
            clear_old();
            flash('success', 'Karyawan berhasil ditambahkan.');
            redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
        case 'update':
            $id = (int) ($_POST['id_user'] ?? 0);
            if ($id <= 0) {
                flash('errors', ['Data karyawan tidak valid.']);
                redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
            }

            $existing = UserRepository::findById($id);
            if ($existing === null) {
                flash('errors', ['Data karyawan tidak ditemukan.']);
                redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
            }

            $payload = [
                'nama_karyawan' => trim($_POST['nama_karyawan'] ?? ''),
                'no_bundy' => trim($_POST['no_bundy'] ?? ''),
                'id_posisi' => (int) ($_POST['id_posisi'] ?? 0),
                'role' => trim($_POST['role'] ?? 'karyawan'),
                'password' => $_POST['password'] ?? '',
            ];

            $errors = [];
            if ($payload['nama_karyawan'] === '') {
                $errors[] = 'Nama karyawan wajib diisi.';
            }
            if ($payload['no_bundy'] === '') {
                $errors[] = 'Nomor bundy wajib diisi.';
            }
            if ($payload['id_posisi'] <= 0) {
                $errors[] = 'Posisi wajib dipilih.';
            }
            if (!in_array($payload['role'], ['admin', 'karyawan'], true)) {
                $errors[] = 'Peran tidak valid.';
            }
            if ($payload['password'] !== '' && strlen($payload['password']) < 6) {
                $errors[] = 'Kata sandi baru minimal 6 karakter.';
            }

            if (UserRepository::findByBundyExcept($payload['no_bundy'], $id) !== null) {
                $errors[] = 'Nomor bundy sudah digunakan karyawan lain.';
            }

            if (!empty($errors)) {
                store_old(array_merge($payload, ['password' => '', '__form' => 'user_edit', 'id_user' => $id]));
                flash('errors', $errors);
                redirect('users.php?edit=' . $id . '&q=' . urlencode($query) . '&page=' . $page);
            }

            $updateData = [
                'nama_karyawan' => $payload['nama_karyawan'],
                'no_bundy' => $payload['no_bundy'],
                'id_posisi' => $payload['id_posisi'],
                'role' => $payload['role'],
                'password_hash' => $payload['password'] !== '' ? password_hash($payload['password'], PASSWORD_BCRYPT) : null,
            ];

            UserRepository::update($id, $updateData);
            AuditRepository::record($currentUser['id_user'] ?? null, 'update', 'Users', $id, 'Memperbarui karyawan ' . $payload['nama_karyawan']);
            clear_old();
            flash('success', 'Karyawan berhasil diperbarui.');
            redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
        case 'delete':
            $id = (int) ($_POST['id_user'] ?? 0);
            if ($id <= 0) {
                flash('errors', ['Data karyawan tidak valid.']);
                redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
            }

            if (($currentUser['id_user'] ?? 0) === $id) {
                flash('errors', ['Anda tidak dapat menghapus akun sendiri.']);
                redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
            }

            $existing = UserRepository::findById($id);
            if ($existing === null) {
                flash('errors', ['Data karyawan tidak ditemukan.']);
                redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
            }

            UserRepository::delete($id);
            AuditRepository::record($currentUser['id_user'] ?? null, 'delete', 'Users', $id, 'Menghapus karyawan ' . $existing['nama_karyawan']);
            flash('success', 'Karyawan berhasil dihapus.');
            redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
        default:
            redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
    }
}

$errors = flash('errors') ?? [];
$success = flash('success');
$oldInput = session_get('__old', []);
clear_old();
$oldForm = $oldInput['__form'] ?? null;
unset($oldInput['__form']);

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$editUser = null;
if ($editId) {
    $editUser = UserRepository::findById($editId);
    if ($editUser === null) {
        flash('errors', ['Data karyawan tidak ditemukan.']);
        redirect('users.php?q=' . urlencode($query) . '&page=' . $page);
    }
}

$positions = PositionRepository::all();

$createDefaults = [
    'nama_karyawan' => '',
    'no_bundy' => '',
    'id_posisi' => '',
    'role' => 'karyawan',
];
$createValues = $createDefaults;
if ($oldForm === 'user_create') {
    $createValues = array_merge($createValues, $oldInput);
}

$editValues = [];
if ($editUser !== null) {
    $editValues = $editUser;
    if ($oldForm === 'user_edit' && isset($oldInput['id_user']) && (int) $oldInput['id_user'] === (int) $editUser['id_user']) {
        $editValues = array_merge($editValues, $oldInput);
    }
}

?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Manajemen Karyawan">
    <meta name="author" content="MoodTracker">
    <title>Manajemen Karyawan | MoodTracker</title>
    <link href="../../sb-admin-2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link href="../../sb-admin-2/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .toast {
            border-radius: 0.5rem;
            box-shadow: 0 20px 45px -15px rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(10px);
            padding: 0;
        }

        .toast.toast-error {
            background: #e74a3b !important;
        }

        .toast.toast-success {
            background: #1cc88a !important;
        }

        .toast .close {
            color: rgba(255, 255, 255, 0.85);
            opacity: 1;
        }
    </style>
</head>

<body id="page-top">
<div id="wrapper">

    <?php $activeMenu = 'users.php'; include __DIR__ . '/partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">
            <?php include __DIR__ . '/partials/topbar.php'; ?>

            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Manajemen Karyawan</h1>
                    <form class="form-inline" method="GET" action="users.php">
                        <div class="input-group mr-2">
                            <input type="text" name="q" class="form-control" placeholder="Cari nama, bundy, posisi" value="<?php echo htmlspecialchars($query); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Dashboard
                    </a>
                </div>

                <div id="toast-container" class="position-fixed" style="top: 1rem; right: 1rem; z-index: 1080;"></div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary"><?php echo $editUser ? 'Edit Karyawan' : 'Tambah Karyawan'; ?></h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?php echo $editUser ? 'users.php?edit=' . (int) $editUser['id_user'] . '&q=' . urlencode($query) . '&page=' . $page : 'users.php?q=' . urlencode($query) . '&page=' . $page; ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="<?php echo $editUser ? 'update' : 'create'; ?>">
                                    <?php if ($editUser): ?>
                                        <input type="hidden" name="id_user" value="<?php echo (int) $editUser['id_user']; ?>">
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label for="nama_karyawan">Nama Karyawan</label>
                                        <input type="text" class="form-control" id="nama_karyawan" name="nama_karyawan" value="<?php echo htmlspecialchars($editUser ? ($editValues['nama_karyawan'] ?? '') : ($createValues['nama_karyawan'] ?? '')); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="no_bundy">Nomor Bundy</label>
                                        <input type="text" class="form-control" id="no_bundy" name="no_bundy" value="<?php echo htmlspecialchars($editUser ? ($editValues['no_bundy'] ?? '') : ($createValues['no_bundy'] ?? '')); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="id_posisi">Posisi</label>
                                        <select class="form-control" id="id_posisi" name="id_posisi" required>
                                            <option value="">Pilih Posisi</option>
                                            <?php foreach ($positions as $position): ?>
                                                <option value="<?php echo (int) $position['id_posisi']; ?>" <?php echo (int) ($editUser ? ($editValues['id_posisi'] ?? 0) : ($createValues['id_posisi'] ?? 0)) === (int) $position['id_posisi'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($position['nama_posisi']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="role">Peran</label>
                                        <select class="form-control" id="role" name="role" required>
                                            <option value="karyawan" <?php echo (($editUser ? ($editValues['role'] ?? '') : ($createValues['role'] ?? '')) === 'karyawan') ? 'selected' : ''; ?>>Karyawan</option>
                                            <option value="admin" <?php echo (($editUser ? ($editValues['role'] ?? '') : ($createValues['role'] ?? '')) === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="password"><?php echo $editUser ? 'Kata Sandi Baru (opsional)' : 'Kata Sandi'; ?></label>
                                        <input type="password" class="form-control" id="password" name="password" <?php echo $editUser ? '' : 'required'; ?> placeholder="Minimal 6 karakter">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block"><?php echo $editUser ? 'Simpan Perubahan' : 'Tambah Karyawan'; ?></button>
                                    <?php if ($editUser): ?>
                                        <a href="users.php" class="btn btn-light btn-block mt-2">Batal</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Daftar Karyawan</h6>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nama</th>
                                            <th>Bundy</th>
                                            <th>Posisi</th>
                                            <th>Peran</th>
                                            <th>Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($users) === 0): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada karyawan.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user['nama_karyawan']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['no_bundy']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['nama_posisi']); ?></td>
                                                    <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'] ?? 'now'))); ?></td>
                                                    <td class="d-flex">
                                                        <a href="users.php?edit=<?php echo (int) $user['id_user']; ?>&q=<?php echo urlencode($query); ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-info mr-2">Edit</a>
                                                        <form method="POST" action="users.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page; ?>" class="js-confirm-form">
                                                            <?php echo csrf_field(); ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id_user" value="<?php echo (int) $user['id_user']; ?>">
                                                            <button type="button" class="btn btn-sm btn-danger js-confirm-button" data-message="Hapus karyawan <?php echo htmlspecialchars($user['nama_karyawan']); ?>?">Hapus</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                                <?php if ($totalPages > 1): ?>
                                    <nav>
                                        <ul class="pagination">
                                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="users.php?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($query); ?>">Sebelumnya</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="users.php?page=<?php echo $i; ?>&q=<?php echo urlencode($query); ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="users.php?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($query); ?>">Berikutnya</a>
                                            </li>
                                        </ul>
                                    </nav>
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

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmModalMessage">Apakah Anda yakin?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmModalConfirm">Ya, lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script src="../../sb-admin-2/vendor/jquery/jquery.min.js"></script>
<script src="../../sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../sb-admin-2/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../../sb-admin-2/js/sb-admin-2.min.js"></script>
<script>
    (function () {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const successMessages = <?php echo json_encode($success ? [$success] : []); ?>;
        const errorMessages = <?php echo json_encode($errors ?? []); ?>;

        function createToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type} show shadow mb-2`;
            toast.style.minWidth = '280px';
            toast.style.background = type === 'success' ? '#1cc88a' : '#e74a3b';
            toast.style.color = '#fff';
            toast.innerHTML = `<div class="toast-body d-flex justify-content-between align-items-center">${message}<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('show'), 4000);
            setTimeout(() => toast.remove(), 4500);
        }

        successMessages.forEach(msg => createToast(msg, 'success'));
        errorMessages.forEach(msg => createToast(msg, 'error'));
    })();

    (function ($) {
        let formTarget = null;

        $(document).on('click', '.js-confirm-button', function () {
            formTarget = $(this).closest('form')[0];
            const message = $(this).data('message') || 'Apakah Anda yakin?';
            $('#confirmModalMessage').text(message);
            $('#confirmModal').modal('show');
        });

        $('#confirmModalConfirm').on('click', function () {
            if (formTarget) {
                formTarget.submit();
            }
            $('#confirmModal').modal('hide');
        });

        $('#confirmModal').on('hidden.bs.modal', function () {
            formTarget = null;
        });
    })(jQuery);
</script>
</body>

</html>
