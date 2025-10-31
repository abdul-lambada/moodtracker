<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';
require_once dirname(__DIR__, 2) . '/bootstrap/environment.php';
require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

use App\Auth\AuthManager;
use App\Models\MoodRepository;
use App\Models\UserRepository;
use App\Models\AuditRepository;

AuthManager::requireRole(['admin']);
$currentUser = AuthManager::user();

$moodOptions = config('moods.options', []);
if ($moodOptions === []) {
    $moodOptions = ['🤩 Sangat Senang', '😄 Senang', '😐 Netral', '😔 Kurang Semangat', '😢 Depresi'];
}

const MOOD_PER_PAGE = 5;
$query = $_GET['q'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$paginate = MoodRepository::paginateWithUser($query, $page, MOOD_PER_PAGE);
$entries = $paginate['data'];
$totalEntries = $paginate['total'];
$totalPages = max((int) ceil($totalEntries / MOOD_PER_PAGE), 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_token'] ?? null;
    if (!verify_csrf($token)) {
        flash('errors', ['Token keamanan tidak valid.']);
        redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
    }

    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'create':
            $payload = [
                'id_user' => (int) ($_POST['id_user'] ?? 0),
                'mood' => trim($_POST['mood'] ?? ''),
                'catatan_mood' => trim($_POST['catatan_mood'] ?? ''),
                'output_harian' => trim($_POST['output_harian'] ?? ''),
                'tanggal_catatan' => trim($_POST['tanggal_catatan'] ?? ''),
            ];

            $errors = validateMoodPayload($payload, $moodOptions);

            if (!empty($errors)) {
                store_old(array_merge($payload, ['__form' => 'mood_create']));
                flash('errors', $errors);
                redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
            }

            $timestamp = strtotime($payload['tanggal_catatan']);
            $formatted = $timestamp ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');

            $id = MoodRepository::create([
                'id_user' => $payload['id_user'],
                'mood' => $payload['mood'],
                'catatan_mood' => $payload['catatan_mood'] !== '' ? $payload['catatan_mood'] : null,
                'output_harian' => $payload['output_harian'],
                'tanggal_catatan' => $formatted,
            ]);

            AuditRepository::record($currentUser['id_user'] ?? null, 'create', 'Catatan_Harian', $id, 'Menambahkan catatan mood baru.');
            clear_old();
            flash('success', 'Catatan mood berhasil ditambahkan.');
            redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
        case 'update':
            $id = (int) ($_POST['id_catatan'] ?? 0);
            if ($id <= 0) {
                flash('errors', ['Data catatan tidak valid.']);
                redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
            }

            $existing = MoodRepository::find($id);
            if ($existing === null) {
                flash('errors', ['Catatan tidak ditemukan.']);
                redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
            }

            $payload = [
                'id_user' => (int) ($_POST['id_user'] ?? 0),
                'mood' => trim($_POST['mood'] ?? ''),
                'catatan_mood' => trim($_POST['catatan_mood'] ?? ''),
                'output_harian' => trim($_POST['output_harian'] ?? ''),
                'tanggal_catatan' => trim($_POST['tanggal_catatan'] ?? ''),
            ];

            $errors = validateMoodPayload($payload, $moodOptions);

            if (!empty($errors)) {
                store_old(array_merge($payload, ['__form' => 'mood_edit', 'id_catatan' => $id]));
                flash('errors', $errors);
                redirect('moods.php?edit=' . $id . '&q=' . urlencode($query) . '&page=' . $page);
            }

            $timestamp = strtotime($payload['tanggal_catatan']);
            $formatted = $timestamp ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');

            MoodRepository::update($id, [
                'id_user' => $payload['id_user'],
                'mood' => $payload['mood'],
                'catatan_mood' => $payload['catatan_mood'] !== '' ? $payload['catatan_mood'] : null,
                'output_harian' => $payload['output_harian'],
                'tanggal_catatan' => $formatted,
            ]);

            AuditRepository::record($currentUser['id_user'] ?? null, 'update', 'Catatan_Harian', $id, 'Memperbarui catatan mood.');
            clear_old();
            flash('success', 'Catatan mood berhasil diperbarui.');
            redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
        case 'delete':
            $id = (int) ($_POST['id_catatan'] ?? 0);
            if ($id <= 0) {
                flash('errors', ['Data catatan tidak valid.']);
                redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
            }

            $existing = MoodRepository::find($id);
            if ($existing === null) {
                flash('errors', ['Catatan tidak ditemukan.']);
                redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
            }

            MoodRepository::delete($id);
            AuditRepository::record($currentUser['id_user'] ?? null, 'delete', 'Catatan_Harian', $id, 'Menghapus catatan mood.');
            flash('success', 'Catatan mood berhasil dihapus.');
            redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
        default:
            redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
    }
}

$errors = flash('errors') ?? [];
$success = flash('success');
$oldInput = session_get('__old', []);
clear_old();
$oldForm = $oldInput['__form'] ?? null;
unset($oldInput['__form']);

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$editMood = null;
if ($editId) {
    $editMood = MoodRepository::find($editId);
    if ($editMood === null) {
        flash('errors', ['Catatan tidak ditemukan.']);
        redirect('moods.php?q=' . urlencode($query) . '&page=' . $page);
    }
}

$users = UserRepository::all();

$createDefaults = [
    'id_user' => '',
    'mood' => '',
    'catatan_mood' => '',
    'output_harian' => '',
    'tanggal_catatan' => date('Y-m-d\TH:i'),
];
$createValues = $createDefaults;
if ($oldForm === 'mood_create') {
    $createValues = array_merge($createValues, $oldInput);
}

$editValues = [];
if ($editMood !== null) {
    $editValues = $editMood;
    if ($oldForm === 'mood_edit' && isset($oldInput['id_catatan']) && (int) $oldInput['id_catatan'] === (int) $editMood['id_catatan']) {
        $editValues = array_merge($editValues, $oldInput);
    }
}

?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Manajemen Catatan Mood">
    <meta name="author" content="MoodTracker">
    <title>Manajemen Catatan Mood | MoodTracker</title>
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

    <?php $activeMenu = 'moods.php'; include __DIR__ . '/partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">
            <?php include __DIR__ . '/partials/topbar.php'; ?>

            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Manajemen Catatan Mood</h1>
                    <form class="form-inline" method="GET" action="moods.php">
                        <div class="input-group mr-2">
                            <input type="text" name="q" class="form-control" placeholder="Cari mood, catatan, karyawan" value="<?php echo htmlspecialchars($query); ?>">
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
                                <h6 class="m-0 font-weight-bold text-primary"><?php echo $editMood ? 'Edit Catatan Mood' : 'Tambah Catatan Mood'; ?></h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?php echo $editMood ? 'moods.php?edit=' . (int) $editMood['id_catatan'] . '&q=' . urlencode($query) . '&page=' . $page : 'moods.php?q=' . urlencode($query) . '&page=' . $page; ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="<?php echo $editMood ? 'update' : 'create'; ?>">
                                    <?php if ($editMood): ?>
                                        <input type="hidden" name="id_catatan" value="<?php echo (int) $editMood['id_catatan']; ?>">
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label for="id_user">Karyawan</label>
                                        <select class="form-control" id="id_user" name="id_user" required>
                                            <option value="">Pilih karyawan</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?php echo (int) $user['id_user']; ?>" <?php echo (int) ($editMood ? ($editValues['id_user'] ?? 0) : ($createValues['id_user'] ?? 0)) === (int) $user['id_user'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($user['nama_karyawan'] . ' (' . $user['no_bundy'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="mood">Mood</label>
                                        <select class="form-control" id="mood" name="mood" required>
                                            <option value="">Pilih mood</option>
                                            <?php foreach ($moodOptions as $option): ?>
                                                <option value="<?php echo htmlspecialchars($option); ?>" <?php echo (($editMood ? ($editValues['mood'] ?? '') : ($createValues['mood'] ?? '')) === $option) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_catatan">Tanggal &amp; Waktu</label>
                                        <input type="datetime-local" class="form-control" id="tanggal_catatan" name="tanggal_catatan" value="<?php echo htmlspecialchars($editMood ? date('Y-m-d\TH:i', strtotime($editValues['tanggal_catatan'])) : ($createValues['tanggal_catatan'] ?? date('Y-m-d\TH:i'))); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="catatan_mood">Catatan Mood (Opsional)</label>
                                        <textarea class="form-control" id="catatan_mood" name="catatan_mood" rows="3" placeholder="Contoh: Capek karena mesin rusak (opsional)"><?php echo htmlspecialchars($editMood ? ($editValues['catatan_mood'] ?? '') : ($createValues['catatan_mood'] ?? '')); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="output_harian">Output Harian</label>
                                        <textarea class="form-control" id="output_harian" name="output_harian" rows="3" required><?php echo htmlspecialchars($editMood ? ($editValues['output_harian'] ?? '') : ($createValues['output_harian'] ?? '')); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block"><?php echo $editMood ? 'Simpan Perubahan' : 'Tambah Catatan'; ?></button>
                                    <?php if ($editMood): ?>
                                        <a href="moods.php" class="btn btn-light btn-block mt-2">Batal</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Daftar Catatan Mood</h6>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Karyawan</th>
                                        <th>Mood</th>
                                        <th>Catatan</th>
                                        <th>Output</th>
                                        <th>Aksi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (count($entries) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada catatan.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($entries as $entry): ?>
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold"><?php echo htmlspecialchars(date('d M Y', strtotime($entry['tanggal_catatan']))); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars(date('H:i', strtotime($entry['tanggal_catatan']))); ?></div>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold"><?php echo htmlspecialchars($entry['nama_karyawan']); ?></div>
                                                    <div class="text-muted small">Bundy: <?php echo htmlspecialchars($entry['no_bundy']); ?></div>
                                                </td>
                                                <td><?php echo htmlspecialchars($entry['mood']); ?></td>
                                                <td><?php echo htmlspecialchars($entry['catatan_mood'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($entry['output_harian'] ?? ''); ?></td>
                                                <td class="d-flex">
                                                    <a href="moods.php?edit=<?php echo (int) $entry['id_catatan']; ?>&q=<?php echo urlencode($query); ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-info mr-2">Edit</a>
                                                    <form method="POST" action="moods.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page; ?>" class="js-confirm-form">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id_catatan" value="<?php echo (int) $entry['id_catatan']; ?>">
                                                        <button type="button" class="btn btn-sm btn-danger js-confirm-button" data-message="Hapus catatan mood ini?">Hapus</button>
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
                                                <a class="page-link" href="moods.php?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($query); ?>">Sebelumnya</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="moods.php?page=<?php echo $i; ?>&q=<?php echo urlencode($query); ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="moods.php?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($query); ?>">Berikutnya</a>
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
            toast.innerHTML = `<div class="toast-body d-flex justify-content-between align-items-center">${message}<button type="button" class="ml-2 mb-1 close" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>`;
            const closeBtn = toast.querySelector('.close');
            closeBtn.addEventListener('click', () => toast.remove());
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

<?php
function validateMoodPayload(array $payload, array $allowedMoods): array
{
    $errors = [];

    if ($payload['id_user'] <= 0) {
        $errors[] = 'Karyawan wajib dipilih.';
    }
    if ($payload['mood'] === '') {
        $errors[] = 'Mood wajib dipilih.';
    }
    if (!in_array($payload['mood'], $allowedMoods, true)) {
        $errors[] = 'Mood tidak valid.';
    }
    if ($payload['catatan_mood'] === '') {
        $errors[] = 'Catatan mood wajib diisi.';
    }
    if ($payload['output_harian'] === '') {
        $errors[] = 'Output harian wajib diisi.';
    }
    if ($payload['tanggal_catatan'] === '' || strtotime($payload['tanggal_catatan']) === false) {
        $errors[] = 'Tanggal catatan tidak valid.';
    }

    return $errors;
}
