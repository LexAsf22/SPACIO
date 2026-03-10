<?php
// frontend/includes/header.php
include("../../backend/config/auth.php");
include("../../backend/config/database.php");
checkLogin();

$user = $_SESSION['user'];
$role = $user['role'];

$dashboardLinks = [
    'student' => '../student/dashboard.php',
    'teacher' => '../teacher/dashboard.php',
    'admin'   => '../admin/dashboard.php',
];

$dashboardLink = $dashboardLinks[$role] ?? '../../index.php';

$roleIcons = [
    'student' => '🎓',
    'teacher' => '📋',
    'admin'   => '⚙️',
];
$roleIcon = $roleIcons[$role] ?? '👤';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spacio — Campus Lab & Classroom Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-w: 260px;
            --bg-deep: #0b1a0d;
            --bg-mid: #122116;
            --bg-card: #172b1c;
            --accent: #4ade6e;
            --accent-dim: #2a7a3e;
            --accent-glow: rgba(74, 222, 110, 0.15);
            --accent-glow-strong: rgba(74, 222, 110, 0.3);
            --text-primary: #e8f5ea;
            --text-secondary: #7aad82;
            --text-muted: #3d6644;
            --border: rgba(74, 222, 110, 0.12);
            --topbar-h: 64px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f1f11;
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ── SIDEBAR ───────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg-deep);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border);
            z-index: 100;
            overflow: hidden;
        }

        /* subtle grid texture */
        .sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: 0.4;
            pointer-events: none;
        }

        /* glow orb */
        .sidebar::after {
            content: '';
            position: absolute;
            top: -60px; left: 50%;
            transform: translateX(-50%);
            width: 220px; height: 220px;
            background: radial-gradient(circle, var(--accent-glow-strong) 0%, transparent 70%);
            pointer-events: none;
        }

        /* ── BRAND ─────────────────────────────────────────── */
        .brand {
            padding: 28px 24px 22px;
            position: relative;
            border-bottom: 1px solid var(--border);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand-mark {
            width: 36px; height: 36px;
            background: var(--accent);
            border-radius: 10px;
            display: grid;
            place-items: center;
            box-shadow: 0 0 16px var(--accent-glow-strong);
            flex-shrink: 0;
        }

        .brand-mark svg {
            width: 20px; height: 20px;
            fill: var(--bg-deep);
        }

        .brand-text {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 20px;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .brand-sub {
            font-size: 10px;
            font-weight: 300;
            color: var(--text-muted);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ── ROLE BADGE ────────────────────────────────────── */
        .role-badge {
            margin: 18px 20px;
            padding: 12px 14px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .role-icon {
            font-size: 18px;
            line-height: 1;
            flex-shrink: 0;
        }

        .role-info {}

        .role-name {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 13px;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .role-label {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        /* ── NAV ───────────────────────────────────────────── */
        .nav {
            flex: 1;
            padding: 8px 16px;
            overflow-y: auto;
            scrollbar-width: none;
        }

        .nav::-webkit-scrollbar { display: none; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 12px 8px 6px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            padding: 10px 12px;
            margin-bottom: 2px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 400;
            transition: all 0.18s ease;
            position: relative;
        }

        .nav a .nav-icon {
            font-size: 15px;
            line-height: 1;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav a:hover {
            background: var(--accent-glow);
            color: var(--text-primary);
        }

        .nav a.active {
            background: var(--accent-glow);
            color: var(--accent);
            font-weight: 500;
        }

        .nav a.active::before {
            content: '';
            position: absolute;
            left: 0; top: 20%; bottom: 20%;
            width: 3px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
            box-shadow: 0 0 8px var(--accent);
        }

        /* ── LOGOUT ────────────────────────────────────────── */
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 13px;
            transition: all 0.18s ease;
        }

        .logout-btn:hover {
            color: #f87171;
            background: rgba(248, 113, 113, 0.08);
        }

        /* ── MAIN CONTENT ──────────────────────────────────── */
        .layout-main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── TOPBAR ────────────────────────────────────────── */
        .topbar {
            height: var(--topbar-h);
            background: rgba(11, 26, 13, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-greeting {
            font-size: 13px;
            color: var(--text-muted);
        }

        .topbar-name {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 15px;
            color: var(--text-primary);
        }

        .topbar-divider {
            width: 1px;
            height: 18px;
            background: var(--border);
            margin: 0 4px;
        }

        .topbar-campus {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--text-secondary);
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 5px 10px;
        }

        .topbar-campus span.dot {
            width: 6px; height: 6px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--accent);
            flex-shrink: 0;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-time {
            font-family: 'Syne', sans-serif;
            font-size: 12px;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        /* ── PAGE BODY ─────────────────────────────────────── */
        .page-body {
            flex: 1;
            padding: 32px 36px;
        }

        /* ── ANIMATIONS ────────────────────────────────────── */
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateX(-8px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .nav a {
            animation: fadeSlideIn 0.3s ease both;
        }

        <?php
        // stagger animation delays for nav items
        for ($i = 1; $i <= 8; $i++) {
            echo ".nav a:nth-child({$i}) { animation-delay: " . ($i * 0.04) . "s; }";
        }
        ?>
    </style>
</head>
<body>

<!-- ── SIDEBAR ─────────────────────────────────────────────── -->
<aside class="sidebar">

    <div class="brand">
        <a href="<?php echo $dashboardLink; ?>" class="brand-logo">
            <div class="brand-mark">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm13 0a4 4 0 110 8 4 4 0 010-8z"/>
                </svg>
            </div>
            <div>
                <div class="brand-text">Spacio</div>
                <div class="brand-sub">Lab &amp; Room Management</div>
            </div>
        </a>
    </div>

    <div class="role-badge">
        <span class="role-icon"><?php echo $roleIcon; ?></span>
        <div class="role-info">
            <div class="role-name"><?php echo ucfirst($role); ?></div>
            <div class="role-label">Active session</div>
        </div>
    </div>

    <nav class="nav">
        <div class="nav-section-label">Navigation</div>

        <a href="<?php echo $dashboardLink; ?>">
            <span class="nav-icon">◈</span> Dashboard
        </a>

        <?php if ($role == "student"): ?>
            <div class="nav-section-label">Reservations</div>
            <a href="../student/reserve_lab.php">
                <span class="nav-icon">🖥</span> Reserve Lab
            </a>
            <a href="../student/reserve_equipment.php">
                <span class="nav-icon">🔧</span> Reserve Equipment
            </a>
            <a href="../student/my_reservations.php">
                <span class="nav-icon">📅</span> My Reservations
            </a>
            <div class="nav-section-label">Resources</div>
            <a href="../student/view_computers.php">
                <span class="nav-icon">💻</span> View Computers
            </a>
        <?php endif; ?>

        <?php if ($role == "teacher"): ?>
            <div class="nav-section-label">Classroom</div>
            <a href="../teacher/lab_usage.php">
                <span class="nav-icon">📊</span> Lab Usage
            </a>
            <div class="nav-section-label">Support</div>
            <a href="../teacher/report_issue.php">
                <span class="nav-icon">⚠</span> Report Issue
            </a>
            <a href="../teacher/issue_status.php">
                <span class="nav-icon">🔍</span> Issue Status
            </a>
        <?php endif; ?>

        <?php if ($role == "admin"): ?>
            <div class="nav-section-label">Management</div>
            <a href="../admin/approvals.php">
                <span class="nav-icon">✅</span> Approve Reservations
            </a>
            <a href="../admin/inventory.php">
                <span class="nav-icon">📦</span> Inventory
            </a>
            <a href="../admin/maintenance.php">
                <span class="nav-icon">🛠</span> Maintenance
            </a>
            <div class="nav-section-label">Insights</div>
            <a href="../admin/reports.php">
                <span class="nav-icon">📈</span> Reports &amp; Analytics
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="../../logout.php" class="logout-btn">
            <span style="font-size:15px">↩</span>
            Sign Out
        </a>
    </div>

</aside>

<!-- ── MAIN ────────────────────────────────────────────────── -->
<div class="layout-main">

    <header class="topbar">
        <div class="topbar-left">
            <div>
                <div class="topbar-greeting">Welcome back,</div>
                <div class="topbar-name"><?php echo htmlspecialchars($user['name']); ?></div>
            </div>
            <div class="topbar-divider"></div>
            <div class="topbar-campus">
                <span class="dot"></span>
                <?php echo htmlspecialchars($user['campus']); ?>
            </div>
        </div>
        <div class="topbar-right">
            <div class="topbar-time" id="js-clock"></div>
        </div>
    </header>

    <div class="page-body">
    <!-- Page content goes here -->

    <script>
        (function () {
            const clock = document.getElementById('js-clock');
            function tick() {
                const now = new Date();
                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                const d = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
                clock.textContent = `${d}  ${h}:${m}`;
            }
            tick();
            setInterval(tick, 30000);

            // Mark active nav link
            const links = document.querySelectorAll('.nav a');
            links.forEach(link => {
                if (link.href === window.location.href) link.classList.add('active');
            });
        })();
    </script>