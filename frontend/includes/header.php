<?php
/*
 * frontend/includes/header.php
 * ─────────────────────────────────────────────────────────
 * Shared sidebar + topbar for all dashboard pages.
 *
 * Usage — at the top of every dashboard page:
 *
 *   $pageTitle     = "My Reservations";   // shown in topbar + <title>
 *   $pageEyebrow   = "Student";           // optional small label above title
 *   $activePage    = "my_reservations";   // matches nav-item data-page values
 *   include("../includes/header.php");
 *
 * Requires:
 *   - session already started
 *   - $conn available (database)
 *   - checkLogin() already called
 */

// Guard: session must be active and user must be set
if (!isset($_SESSION['user'])) {
    header("Location: /spacio/login.php");
    exit;
}

$user   = $_SESSION['user'];
$role   = $user['role'];
$name   = $user['name']   ?? 'User';
$campus = $user['campus'] ?? 'Campus';

// Avatar initials (first letter of first + last name)
$nameParts = explode(' ', trim($name));
$initials  = strtoupper(substr($nameParts[0], 0, 1));
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
}

// Page meta defaults
$pageTitle   = $pageTitle   ?? 'Dashboard';
$pageEyebrow = $pageEyebrow ?? ucfirst($role) . ' Portal';
$activePage  = $activePage  ?? '';

// Role label
$roleLabels = [
    'student' => 'Student',
    'teacher' => 'Teacher',
    'admin'   => 'Administrator',
];
$roleLabel = $roleLabels[$role] ?? ucfirst($role);

// Determine base path (2 levels deep: frontend/[role]/)
$basePath = '/spacio';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> — Spacio</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Literata:ital,wght@0,300;0,400;1,300;1,400&display=swap" rel="stylesheet">

    <!-- Shared design system -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/app.css">
</head>
<body>

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay" style="
    display:none; position:fixed; inset:0; z-index:45;
    background:rgba(0,0,0,.6); backdrop-filter:blur(4px);
" onclick="this.classList.remove('active')"></div>

<style>
    #sidebar-overlay.active { display:block; }
</style>

<div class="app-shell">

    <!-- ═══════════════════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════════════════════ -->
    <aside class="sidebar">

        <!-- Brand -->
        <a class="sidebar-brand" href="<?php echo $basePath; ?>/index.php">
            <div class="sidebar-brand-mark">S</div>
            <div>
                <div class="sidebar-brand-text">Spacio</div>
                <div class="sidebar-brand-role"><?php echo $roleLabel; ?></div>
            </div>
        </a>

        <!-- Navigation -->
        <nav class="sidebar-nav">

            <!-- ── All roles: Dashboard ── -->
            <span class="nav-section-label">Overview</span>

            <?php if ($role === 'student'): ?>
            <a class="nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/student/dashboard.php"
               data-page="dashboard">
                <span class="nav-icon">⊞</span>
                Dashboard
            </a>

            <div class="nav-divider"></div>
            <span class="nav-section-label">Reservations</span>

            <a class="nav-item <?php echo $activePage === 'reserve_lab' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/student/reserve_lab.php"
               data-page="reserve_lab">
                <span class="nav-icon">🏫</span>
                Reserve a Lab
            </a>

            <a class="nav-item <?php echo $activePage === 'reserve_equipment' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/student/reserve_equipment.php"
               data-page="reserve_equipment">
                <span class="nav-icon">🔬</span>
                Reserve Equipment
            </a>

            <a class="nav-item <?php echo $activePage === 'my_reservations' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/student/my_reservations.php"
               data-page="my_reservations">
                <span class="nav-icon">🗓️</span>
                My Reservations
            </a>

            <div class="nav-divider"></div>
            <span class="nav-section-label">Browse</span>

            <a class="nav-item <?php echo $activePage === 'view_labs' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/student/view_labs.php"
               data-page="view_labs">
                <span class="nav-icon">🗺️</span>
                View Labs
            </a>

            <a class="nav-item <?php echo $activePage === 'view_equipment' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/student/view_equipment.php"
               data-page="view_equipment">
                <span class="nav-icon">📦</span>
                View Equipment
            </a>

            <?php elseif ($role === 'teacher'): ?>
            <a class="nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/teacher/dashboard.php"
               data-page="dashboard">
                <span class="nav-icon">⊞</span>
                Dashboard
            </a>

            <div class="nav-divider"></div>
            <span class="nav-section-label">Lab Management</span>

            <a class="nav-item <?php echo $activePage === 'lab_usage' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/teacher/lab_usage.php"
               data-page="lab_usage">
                <span class="nav-icon">📊</span>
                Lab Usage
            </a>

            <div class="nav-divider"></div>
            <span class="nav-section-label">Issues</span>

            <a class="nav-item <?php echo $activePage === 'report_issue' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/teacher/report_issue.php"
               data-page="report_issue">
                <span class="nav-icon">🚨</span>
                Report an Issue
            </a>

            <a class="nav-item <?php echo $activePage === 'issue_status' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/teacher/issue_status.php"
               data-page="issue_status">
                <span class="nav-icon">📋</span>
                Issue Status
            </a>

            <?php elseif ($role === 'admin'): ?>
            <a class="nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/admin/dashboard.php"
               data-page="dashboard">
                <span class="nav-icon">⊞</span>
                Dashboard
            </a>

            <div class="nav-divider"></div>
            <span class="nav-section-label">Operations</span>

            <a class="nav-item <?php echo $activePage === 'approvals' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/admin/approvals.php"
               data-page="approvals">
                <span class="nav-icon">✅</span>
                Approvals
            </a>

            <a class="nav-item <?php echo $activePage === 'inventory' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/admin/inventory.php"
               data-page="inventory">
                <span class="nav-icon">📦</span>
                Inventory
            </a>

            <a class="nav-item <?php echo $activePage === 'maintenance' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/admin/maintenance.php"
               data-page="maintenance">
                <span class="nav-icon">🔧</span>
                Maintenance
            </a>

            <div class="nav-divider"></div>
            <span class="nav-section-label">Analytics</span>

            <a class="nav-item <?php echo $activePage === 'reports' ? 'active' : ''; ?>"
               href="<?php echo $basePath; ?>/frontend/admin/reports.php"
               data-page="reports">
                <span class="nav-icon">📈</span>
                Reports &amp; Analytics
            </a>

            <?php endif; ?>

        </nav>

        <!-- User card -->
        <div class="sidebar-user">
            <div class="user-avatar"><?php echo htmlspecialchars($initials); ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($name); ?></div>
                <div class="user-meta"><?php echo htmlspecialchars($campus); ?></div>
            </div>
        </div>

        <!-- Logout -->
        <a class="logout-btn" href="<?php echo $basePath; ?>/logout.php">
            <span>→</span> Sign Out
        </a>

    </aside>

    <!-- ═══════════════════════════════════════════════════
         MAIN AREA
    ════════════════════════════════════════════════════ -->
    <div class="main-area">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-page-title"><?php echo htmlspecialchars($pageTitle); ?></div>
                <div class="topbar-breadcrumb"><?php echo htmlspecialchars($pageEyebrow); ?></div>
            </div>
            <div class="topbar-right">
                <span class="live-dot">Live</span>
                <span class="topbar-campus-badge">🏫 <?php echo htmlspecialchars($campus); ?></span>
                <span class="topbar-time" id="topbar-clock"></span>
            </div>

            <!-- Mobile menu button (hidden on desktop via CSS) -->
            <button id="mobile-menu-btn" style="
                display:none;
                background:none; border:none; cursor:pointer;
                color:rgba(255,255,255,.6); font-size:1.4rem;
                padding:4px; margin-left:12px;
            " aria-label="Toggle menu">☰</button>
        </header>

        <!-- Flash messages output -->
        <?php
        if (function_exists('getFlash')) {
            echo getFlash();
        }
        ?>

        <!-- Page content starts here -->
        <main class="page-content">

<?php
// Mobile menu button visibility
?>
<style>
@media (max-width: 768px) {
    #mobile-menu-btn { display:block !important; }
}
</style>