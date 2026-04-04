<?php
<<<<<<< HEAD
// frontend/admin/dashboard.php
include("../includes/header.php");
checkRole('admin');

// ── AJAX stats request — proxy to Django ──────────────────────────────────────
if (isset($_GET['fetch_stats'])) {
    header('Content-Type: application/json');

    $result = djangoGet('/api/v1/admin/stats/');

    if ($result['success'] && isset($result['data'])) {
        echo json_encode([
            'total_reservations' => $result['data']['total_reservations'] ?? 0,
            'total_pending'      => $result['data']['total_pending']      ?? 0,
            'total_issues'       => $result['data']['total_issues']       ?? 0,
        ]);
    } else {
        // Fallback: query DB directly if Django is unreachable
        echo json_encode([
            'total_reservations' => $conn->query("SELECT id FROM reservations")->num_rows,
            'total_pending'      => $conn->query("SELECT id FROM reservations WHERE status='Pending'")->num_rows,
            'total_issues'       => $conn->query("SELECT id FROM issues")->num_rows,
        ]);
    }
    exit;
}

// ── Normal page load — fetch stats from Django ────────────────────────────────
$result = djangoGet('/api/v1/admin/stats/');

if ($result['success'] && isset($result['data'])) {
    $total_reservations = $result['data']['total_reservations'] ?? 0;
    $total_pending      = $result['data']['total_pending']      ?? 0;
    $total_issues       = $result['data']['total_issues']       ?? 0;
} else {
    // Fallback: query DB directly
    $total_reservations = $conn->query("SELECT id FROM reservations")->num_rows;
    $total_pending      = $conn->query("SELECT id FROM reservations WHERE status='Pending'")->num_rows;
    $total_issues       = $conn->query("SELECT id FROM issues")->num_rows;
}
=======
session_start();
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../includes/flash.php");

checkLogin();
checkRole('admin');

$user     = $_SESSION['user'];
$userName = $user['name'];

// ── Pull stats ──
$totalUsers       = 0;
$pendingApprovals = 0;
$openIssues       = 0;
$totalInventory   = 0;
$recentApprovals  = [];
$recentIssues     = [];

$r = $conn->query("SELECT COUNT(*) as c FROM users");
$totalUsers = $r->fetch_assoc()['c'] ?? 0;

$r = $conn->query("SELECT COUNT(*) as c FROM reservations WHERE status = 'pending'");
$pendingApprovals = $r->fetch_assoc()['c'] ?? 0;

$r = $conn->query("SELECT COUNT(*) as c FROM issues WHERE status = 'open'");
$openIssues = $r->fetch_assoc()['c'] ?? 0;

$r = $conn->query("SELECT COUNT(*) as c FROM inventory");
$totalInventory = $r->fetch_assoc()['c'] ?? 0;

$r = $conn->query("SELECT r.*, u.name as user_name FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5");
$recentApprovals = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

$r = $conn->query("SELECT i.*, u.name as reporter_name FROM issues i LEFT JOIN users u ON i.user_id = u.id ORDER BY i.created_at DESC LIMIT 5");
$recentIssues = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

// ── Page meta ──
$pageTitle   = "Dashboard";
$pageEyebrow = "Welcome back, " . htmlspecialchars(explode(' ', $userName)[0]);
$activePage  = "dashboard";

include("../includes/header.php");
>>>>>>> ec6daf5 (new)
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-eyebrow">
        <div class="eyebrow-dot"></div>
        <span class="eyebrow-text">Admin Portal</span>
    </div>
    <h1 class="page-title">Good <?php
        $h = (int)date('H');
        echo $h < 12 ? 'morning' : ($h < 17 ? 'afternoon' : 'evening');
    ?>, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>.</h1>
    <p class="page-subtitle">Full campus overview — reservations, inventory, issues, and users.</p>
</div>

<!-- Stat Cards -->
<div class="stats-grid" style="margin-bottom:28px;">

<<<<<<< HEAD
    <div style="background:#4caf50; color:white; padding:20px; flex:1; border-radius:5px;">
        <h3>Total Reservations</h3>
        <p id="totalReservations"><?php echo (int) $total_reservations; ?></p>
    </div>

    <div style="background:#f39c12; color:white; padding:20px; flex:1; border-radius:5px;">
        <h3>Pending Approvals</h3>
        <p id="pendingReservations"><?php echo (int) $total_pending; ?></p>
    </div>

    <div style="background:#e74c3c; color:white; padding:20px; flex:1; border-radius:5px;">
        <h3>Reported Issues</h3>
        <p id="reportedIssues"><?php echo (int) $total_issues; ?></p>
=======
    <div class="stat-card accent-gold stagger-1">
        <span class="stat-card-icon">⏳</span>
        <div class="stat-card-value" data-count="<?php echo $pendingApprovals; ?>">
            <?php echo $pendingApprovals; ?>
        </div>
        <div class="stat-card-label">Pending Approvals</div>
        <span class="stat-card-trend <?php echo $pendingApprovals > 0 ? 'down' : 'up'; ?>">
            <?php echo $pendingApprovals > 0 ? 'Needs review' : 'All cleared'; ?>
        </span>
    </div>

    <div class="stat-card accent-red stagger-2">
        <span class="stat-card-icon">🚨</span>
        <div class="stat-card-value" data-count="<?php echo $openIssues; ?>">
            <?php echo $openIssues; ?>
        </div>
        <div class="stat-card-label">Open Issues</div>
        <span class="stat-card-trend <?php echo $openIssues > 0 ? 'down' : 'up'; ?>">
            <?php echo $openIssues > 0 ? 'Action required' : 'All resolved'; ?>
        </span>
    </div>

    <div class="stat-card accent-blue stagger-3">
        <span class="stat-card-icon">👥</span>
        <div class="stat-card-value" data-count="<?php echo $totalUsers; ?>">
            <?php echo $totalUsers; ?>
        </div>
        <div class="stat-card-label">Registered Users</div>
        <span class="stat-card-trend up">Campus-wide</span>
    </div>

    <div class="stat-card accent-green stagger-4">
        <span class="stat-card-icon">📦</span>
        <div class="stat-card-value" data-count="<?php echo $totalInventory; ?>">
            <?php echo $totalInventory; ?>
        </div>
        <div class="stat-card-label">Inventory Items</div>
        <span class="stat-card-trend neutral">Tracked</span>
>>>>>>> ec6daf5 (new)
    </div>

</div>

<<<<<<< HEAD
<script>
function loadDashboardStats() {
    fetch(window.location.pathname + '?fetch_stats=1')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalReservations').textContent  = data.total_reservations;
            document.getElementById('pendingReservations').textContent = data.total_pending;
            document.getElementById('reportedIssues').textContent      = data.total_issues;
        })
        .catch(error => console.error('Dashboard stats error:', error));
}

// Refresh every 60 seconds
setInterval(loadDashboardStats, 60000);
</script>

=======
<!-- Alert banners for urgent items -->
<?php if ($pendingApprovals > 0): ?>
<div class="flash flash-warning" style="margin-bottom:20px;">
    <span class="flash-icon">⚡</span>
    <span class="flash-message">
        <strong><?php echo $pendingApprovals; ?> reservation<?php echo $pendingApprovals > 1 ? 's' : ''; ?></strong>
        waiting for your approval.
        <a href="approvals.php" style="color:inherit; font-family:'Syne',sans-serif; font-weight:700; font-size:.8rem; margin-left:8px;">Review now →</a>
    </span>
</div>
<?php endif; ?>

<?php if ($openIssues > 0): ?>
<div class="flash flash-error" style="margin-bottom:24px;">
    <span class="flash-icon">⚠</span>
    <span class="flash-message">
        <strong><?php echo $openIssues; ?> open issue<?php echo $openIssues > 1 ? 's' : ''; ?></strong>
        need attention.
        <a href="maintenance.php" style="color:inherit; font-family:'Syne',sans-serif; font-weight:700; font-size:.8rem; margin-left:8px;">View issues →</a>
    </span>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="card stagger-2" style="margin-bottom:20px;">
    <div class="card-header">
        <div>
            <div class="card-title">Admin Actions</div>
            <div class="card-subtitle">Common operations</div>
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">

            <a href="approvals.php" class="btn btn-ghost" style="flex-direction:column; align-items:flex-start; gap:6px; padding:16px; border-radius:10px; text-transform:none; letter-spacing:0; height:auto;">
                <span style="font-size:1.4rem;">✅</span>
                <div style="font-family:'Syne',sans-serif; font-weight:700; color:#fff; font-size:.83rem;">Approvals</div>
                <div style="font-size:.72rem; color:rgba(255,255,255,.3); font-style:italic;">Review reservation requests</div>
            </a>

            <a href="inventory.php" class="btn btn-ghost" style="flex-direction:column; align-items:flex-start; gap:6px; padding:16px; border-radius:10px; text-transform:none; letter-spacing:0; height:auto;">
                <span style="font-size:1.4rem;">📦</span>
                <div style="font-family:'Syne',sans-serif; font-weight:700; color:#fff; font-size:.83rem;">Inventory</div>
                <div style="font-size:.72rem; color:rgba(255,255,255,.3); font-style:italic;">Manage campus resources</div>
            </a>

            <a href="maintenance.php" class="btn btn-ghost" style="flex-direction:column; align-items:flex-start; gap:6px; padding:16px; border-radius:10px; text-transform:none; letter-spacing:0; height:auto;">
                <span style="font-size:1.4rem;">🔧</span>
                <div style="font-family:'Syne',sans-serif; font-weight:700; color:#fff; font-size:.83rem;">Maintenance</div>
                <div style="font-size:.72rem; color:rgba(255,255,255,.3); font-style:italic;">Resolve reported issues</div>
            </a>

            <a href="reports.php" class="btn btn-ghost" style="flex-direction:column; align-items:flex-start; gap:6px; padding:16px; border-radius:10px; text-transform:none; letter-spacing:0; height:auto;">
                <span style="font-size:1.4rem;">📈</span>
                <div style="font-family:'Syne',sans-serif; font-weight:700; color:#fff; font-size:.83rem;">Reports</div>
                <div style="font-size:.72rem; color:rgba(255,255,255,.3); font-style:italic;">Analytics &amp; usage data</div>
            </a>

        </div>
    </div>
</div>

<!-- Recent Approvals + Recent Issues -->
<div class="grid-2" style="gap:20px; align-items:start;">

    <!-- Pending Reservations -->
    <div class="card stagger-3">
        <div class="card-header">
            <div>
                <div class="card-title">Recent Reservations</div>
                <div class="card-subtitle">Latest requests submitted</div>
            </div>
            <a href="approvals.php" class="btn btn-primary btn-sm">Review All</a>
        </div>

        <?php if (empty($recentApprovals)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">✅</div>
            <div class="empty-state-title">No reservations yet</div>
            <div class="empty-state-desc">Reservation requests will appear here.</div>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Item / Lab</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentApprovals as $res):
                    $status = $res['status'] ?? 'pending';
                    $badgeClass = match($status) {
                        'approved' => 'badge-green',
                        'rejected' => 'badge-red',
                        default    => 'badge-gold',
                    };
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($res['user_name'] ?? '—'); ?></td>
                    <td style="color:rgba(255,255,255,.5); font-size:.8rem;">
                        <?php echo htmlspecialchars($res['item_name'] ?? $res['lab_name'] ?? '—'); ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Recent Issues -->
    <div class="card stagger-4">
        <div class="card-header">
            <div>
                <div class="card-title">Recent Issues</div>
                <div class="card-subtitle">Latest reports from teachers</div>
            </div>
            <a href="maintenance.php" class="btn btn-ghost btn-sm">View All</a>
        </div>

        <?php if (empty($recentIssues)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🚨</div>
            <div class="empty-state-title">No open issues</div>
            <div class="empty-state-desc">Issue reports will appear here when submitted.</div>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Issue</th>
                    <th>Reported By</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentIssues as $issue):
                    $status = $issue['status'] ?? 'open';
                    $badgeClass = match($status) {
                        'resolved'    => 'badge-green',
                        'in_progress' => 'badge-blue',
                        default       => 'badge-red',
                    };
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($issue['title'] ?? $issue['description'] ?? '—'); ?></td>
                    <td style="color:rgba(255,255,255,.4); font-size:.8rem;">
                        <?php echo htmlspecialchars($issue['reporter_name'] ?? '—'); ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

>>>>>>> ec6daf5 (new)
<?php include("../includes/footer.php"); ?>