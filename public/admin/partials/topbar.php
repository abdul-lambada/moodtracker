<?php
?>
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?php if (isset($currentUser['nama_lengkap'])): ?>
                        <?php echo htmlspecialchars($currentUser['nama_lengkap']); ?>
                    <?php else: ?>
                        Admin
                    <?php endif; ?>
                </span>
                <i class="fas fa-user-circle fa-lg text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="../index.php">
                    <i class="fas fa-home fa-sm fa-fw mr-2 text-gray-400"></i>
                    Landing Page
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Keluar
                </a>
            </div>
        </li>
    </ul>

</nav>
