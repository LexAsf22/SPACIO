<?php
// frontend/includes/header.php
$base = __DIR__ . '/../../backend/config/';

include_once($base . 'auth.php');
include_once($base . 'database.php');
include_once($base . 'helpers.php');
checkLogin();

$user = $_SESSION['user'];
$role = strtolower($user['role']); // ← normalize to lowercase; Django returns 'student'/'teacher'/'admin'

// Role-based nav config
$nav = [];
$dashboardLink = '/spacio/frontend/' . strtolower($role) . '/dashboard.php';

if ($role === 'student') {
    $nav = [
        ['href' => '/spacio/frontend/student/dashboard.php',            'icon' => '⊞', 'label' => 'Dashboard'],
        ['href' => '/spacio/frontend/student/reserve_lab.php',          'icon' => '🔬', 'label' => 'Reserve Lab'],
        ['href' => '/spacio/frontend/student/reserve_equipment.php',    'icon' => '🖥', 'label' => 'Reserve Equipment'],
        ['href' => '/spacio/frontend/student/my_reservations.php',      'icon' => '📋', 'label' => 'My Reservations'],
        ['href' => '/spacio/frontend/student/reservation_history.php',  'icon' => '🕓', 'label' => 'History'],
    ];
}

if ($role === 'teacher') {
    $nav = [
        ['href' => '/spacio/frontend/teacher/dashboard.php',             'icon' => '⊞', 'label' => 'Dashboard'],
        ['href' => '/spacio/frontend/teacher/lab_usage.php',             'icon' => '📊', 'label' => 'Lab Usage'],
        ['href' => '/spacio/frontend/teacher/report_issue.php',          'icon' => '⚠', 'label' => 'Report Issue'],
        ['href' => '/spacio/frontend/teacher/issue_status.php',          'icon' => '🔍', 'label' => 'Issue Status'],
        ['href' => '/spacio/frontend/teacher/reservation_history.php',   'icon' => '🕓', 'label' => 'History'],
    ];
}

if ($role === 'admin') {
    $nav = [
        ['href' => '/spacio/frontend/admin/dashboard.php',   'icon' => '⊞', 'label' => 'Dashboard'],
        ['href' => '/spacio/frontend/admin/approvals.php',   'icon' => '✓', 'label' => 'Approve Reservations'],
        ['href' => '/spacio/frontend/admin/inventory.php',   'icon' => '📦', 'label' => 'Inventory'],
        ['href' => '/spacio/frontend/admin/maintenance.php', 'icon' => '🔧', 'label' => 'Maintenance'],
        ['href' => '/spacio/frontend/admin/reports.php',     'icon' => '📈', 'label' => 'Reports & Analytics'],
        ['href' => '/spacio/frontend/admin/history.php',     'icon' => '🕓', 'label' => 'History'],
    ];
}

// Current page detection for active state
$currentPath = $_SERVER['PHP_SELF'];

// User initials for avatar
$nameParts = explode(' ', trim($user['name']));
$initials  = strtoupper(
    (isset($nameParts[0]) ? $nameParts[0][0] : '') .
    (isset($nameParts[1]) ? $nameParts[1][0] : '')
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spacio — Campus Lab & Classroom Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/spacio/css/main.css">
</head>
<body>

<!-- ── Sidebar ─────────────────────────────────────────────── -->
<aside class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="<?php echo $dashboardLink; ?>" class="sidebar-logo-mark">
            <div class="sidebar-logo-icon">Sp</div>
            <div class="sidebar-logo-text">
                <span class="sidebar-logo-name">Spacio</span>
                <span class="sidebar-logo-sub">Lab Management</span>
            </div>
        </a>
    </div>

    <!-- Role badge -->
    <div class="sidebar-role">
        <div class="sidebar-role-dot"></div>
        <span class="sidebar-role-label">Role</span>
        <span class="sidebar-role-name"><?php echo htmlspecialchars($role); ?></span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Navigation</div>
        <?php foreach ($nav as $item):
            $isActive = strpos($currentPath, basename($item['href'])) !== false;
        ?>
            <a href="<?php echo $item['href']; ?>"
               class="sidebar-link <?php echo $isActive ? 'active' : ''; ?>">
                <span class="sidebar-link-icon"><?php echo $item['icon']; ?></span>
                <?php echo htmlspecialchars($item['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- User & Logout -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?php echo $initials; ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="sidebar-user-campus"><?php echo htmlspecialchars($user['campus'] ?? 'N/A'); ?></div>
            </div>
        </div>
        <a href="/spacio/logout.php" class="sidebar-logout">
            <span>↪</span> Sign out
        </a>
    </div>

</aside>

<!-- ── Top Bar ─────────────────────────────────────────────── -->
<header class="topbar">
    <div class="topbar-title" id="pageTitle">Dashboard</div>
    <div class="topbar-actions">
        <span class="topbar-badge">
            <span class="topbar-badge-dot"></span>
            <?php echo htmlspecialchars($user['campus'] ?? 'N/A'); ?>
        </span>
    </div>
</header>

<?php if (!empty($_SESSION['show_terms_modal'])): ?>
<div class="terms-overlay" id="termsOverlay">
    <div class="terms-modal" id="termsModal">

        <div class="terms-modal-header">
            <div class="terms-modal-logo">
                <div class="sidebar-logo-icon" style="width:32px;height:32px;font-size:13px;">Sp</div>
                <div>
                    <div style="font-family:var(--font-display);font-weight:700;font-size:.95rem;color:var(--ink-900);">Welcome to Spacio</div>
                    <div style="font-size:.7rem;color:var(--ink-400);margin-top:1px;">Please review and accept before continuing</div>
                </div>
            </div>
        </div>

        <div class="terms-modal-body">
            <div class="terms-modal-notice">
                <span>📋</span>
                <div>
                    <strong>Terms &amp; Conditions and Data Privacy Policy</strong>
                    By using Spacio, you agree to use the system responsibly, keep your
                    credentials private, make honest reservations, and handle all lab
                    equipment with care. Your personal data is collected solely for
                    academic and administrative purposes and will not be shared with
                    third parties without your consent.
                </div>
            </div>
            <p class="terms-modal-hint">
                Read the full
                <a href="/spacio/frontend/terms.php" target="_blank">Terms &amp; Conditions</a>
                and
                <a href="/spacio/frontend/terms.php?tab=privacy" target="_blank">Data Privacy Policy</a>
                before accepting.
            </p>
        </div>

        <div class="terms-modal-footer">
            <form method="POST" action="/spacio/frontend/accept_terms.php">
                <button type="submit" class="terms-accept-btn">
                    ✓ &nbsp;I Accept — Continue to Dashboard
                </button>
            </form>
            <p class="terms-modal-sub">You will not be asked again after accepting.</p>
        </div>

    </div>
</div>
<?php endif; ?>

<!-- ── Page Content ────────────────────────────────────────── -->
<main class="main-content">
<div class="page-body">

<script>
// Set topbar title to current page's h1/h2 or nav label
(function() {
    const currentHref = window.location.pathname;
    const links = document.querySelectorAll('.sidebar-link');
    links.forEach(link => {
        if (currentHref.includes(link.getAttribute('href').split('/').pop().replace('.php',''))) {
            const label = link.textContent.trim();
            document.getElementById('pageTitle').textContent = label;
        }
    });
})();
</script>