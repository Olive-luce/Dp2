<?php
$role = $_SESSION['role'] ?? 'citizen';
$dashboardMap = [
    'admin' => 'admin/dashboard.php',
    'responder' => 'responder/dashboard.php',
    'volunteer' => 'volunteer/dashboard.php',
    'citizen' => 'citizen/dashboard.php',
];
$dashboard = $dashboardMap[$role] ?? 'index.php';
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$activeClass = function ($target) use ($currentPath) {
    return substr($currentPath, -strlen($target)) === $target ? 'active' : '';
};
?>
<nav class="sidebar" id="sidebarMenu" aria-label="Sidebar navigation">
    <div class="sidebar-card">
        <div class="sidebar-section-title">Operations Menu</div>
        <h5 class="fw-bold mb-1 mt-2">Emergency Command</h5>
        <p class="mb-0 text-white-50">Role-based coordination</p>
    </div>
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link <?php echo $activeClass('admin/dashboard.php') || $activeClass('responder/dashboard.php') || $activeClass('volunteer/dashboard.php') || $activeClass('citizen/dashboard.php') || $currentPath === '' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/<?php echo $dashboard; ?>">
                <span class="nav-icon"><i class="fa-solid fa-gauge-high"></i></span>
                <span class="nav-label">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeClass('modules/incidents/map.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/modules/incidents/map.php">
                <span class="nav-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                <span class="nav-label">Interactive Map</span>
            </a>
        </li>
        <?php if ($role === 'admin'): ?>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('admin/incidents.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/incidents.php"><span class="nav-icon"><i class="fa-solid fa-burst"></i></span><span class="nav-label">Incidents</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('admin/communications.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/communications.php"><span class="nav-icon"><i class="fa-solid fa-bullhorn"></i></span><span class="nav-label">Communications</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('admin/volunteers.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/volunteers.php"><span class="nav-icon"><i class="fa-solid fa-users"></i></span><span class="nav-label">Volunteers</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('admin/shelters.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/shelters.php"><span class="nav-icon"><i class="fa-solid fa-person-shelter"></i></span><span class="nav-label">Shelters</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('admin/resources.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/resources.php"><span class="nav-icon"><i class="fa-solid fa-boxes-stacked"></i></span><span class="nav-label">Resources</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('admin/reports.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/reports.php"><span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span><span class="nav-label">Reports</span></a></li>
        <?php elseif ($role === 'responder'): ?>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('responder/incidents.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/responder/incidents.php"><span class="nav-icon"><i class="fa-solid fa-burst"></i></span><span class="nav-label">Dispatch</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('responder/resources.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/responder/resources.php"><span class="nav-icon"><i class="fa-solid fa-boxes-stacked"></i></span><span class="nav-label">Resources</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('citizen/communications.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/citizen/communications.php"><span class="nav-icon"><i class="fa-solid fa-bullhorn"></i></span><span class="nav-label">Advisories</span></a></li>
        <?php elseif ($role === 'volunteer'): ?>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('volunteer/tasks.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/volunteer/tasks.php"><span class="nav-icon"><i class="fa-solid fa-list-check"></i></span><span class="nav-label">Tasks</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('volunteer/checkin.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/volunteer/checkin.php"><span class="nav-icon"><i class="fa-solid fa-location-crosshairs"></i></span><span class="nav-label">Check-in</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('citizen/communications.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/citizen/communications.php"><span class="nav-icon"><i class="fa-solid fa-bullhorn"></i></span><span class="nav-label">Advisories</span></a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('citizen/report.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/citizen/report.php"><span class="nav-icon"><i class="fa-solid fa-exclamation"></i></span><span class="nav-label">Report Incident</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('citizen/shelters.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/citizen/shelters.php"><span class="nav-icon"><i class="fa-solid fa-person-shelter"></i></span><span class="nav-label">Shelters</span></a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeClass('citizen/communications.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/citizen/communications.php"><span class="nav-icon"><i class="fa-solid fa-bullhorn"></i></span><span class="nav-label">Advisories</span></a></li>
        <?php endif; ?>
    </ul>
    <div class="mt-4 d-lg-none">
        <button class="btn btn-outline-light btn-sm w-100" id="sidebarClose" type="button"><i class="fa-solid fa-xmark me-2"></i>Close</button>
    </div>
</nav>
