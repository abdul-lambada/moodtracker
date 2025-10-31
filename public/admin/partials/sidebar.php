<?php

if (!isset($activeMenu) || $activeMenu === '') {
    $activeMenu = basename($_SERVER['PHP_SELF']);
}

if (!function_exists('adminSidebarItemClass')) {
    function adminSidebarItemClass(string $menu, string $activeMenu): string
    {
        return $activeMenu === $menu ? 'nav-item active' : 'nav-item';
    }
}

?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-smile"></i>
        </div>
        <div class="sidebar-brand-text mx-3">MoodTracker</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="<?php echo adminSidebarItemClass('index.php', $activeMenu); ?>">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Manajemen
    </div>

    <li class="<?php echo adminSidebarItemClass('moods.php', $activeMenu); ?>">
        <a class="nav-link" href="moods.php">
            <i class="fas fa-fw fa-heart"></i>
            <span>Catatan Mood</span></a>
    </li>

    <li class="<?php echo adminSidebarItemClass('users.php', $activeMenu); ?>">
        <a class="nav-link" href="users.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Karyawan</span></a>
    </li>

    <li class="<?php echo adminSidebarItemClass('report.php', $activeMenu); ?>">
        <a class="nav-link" href="report.php">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Laporan</span></a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
