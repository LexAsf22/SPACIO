<?php
// frontend/admin/dashboard.php
include("../includes/header.php");
checkRole('admin');

// ── AJAX stats request ────────────────────────────────────────────────────────
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
        echo json_encode([
            'total_reservations' => $conn->query("SELECT id FROM reservations")->num_rows,
            'total_pending'      => $conn->query("SELECT id FROM reservations WHERE status='Pending'")->num_rows,
            'total_issues'       => $conn->query("SELECT id FROM issues")->num_rows,
        ]);
    }
    exit;
}

// ── Normal page load ──────────────────────────────────────────────────────────
$result = djangoGet('/api/v1/admin/stats/');
if ($result['success'] && isset($result['data'])) {
    $total_reservations = $result['data']['total_reservations'] ?? 0;
    $total_pending      = $result['data']['total_pending']      ?? 0;
    $total_issues       = $result['data']['total_issues']       ?? 0;
} else {
    $total_reservations = $conn->query("SELECT id FROM reservations")->num_rows;
    $total_pending      = $conn->query("SELECT id FROM reservations WHERE status='Pending'")->num_rows;
    $total_issues       = $conn->query("SELECT id FROM issues")->num_rows;
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle"><?php echo date('l, F d, Y'); ?> &mdash; System overview</p>
    </div>
    <a href="/spacio/frontend/admin/approvals.php" class="btn btn-primary">
        View Pending Approvals
    </a>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon green">📅</div>
        <div class="stat-body">
            <div class="stat-label">Total Reservations</div>
            <div class="stat-value" id="totalReservations"><?php echo (int)$total_reservations; ?></div>
            <div class="stat-trend">All time</div>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange">⏳</div>
        <div class="stat-body">
            <div class="stat-label">Pending Approvals</div>
            <div class="stat-value" id="pendingReservations"><?php echo (int)$total_pending; ?></div>
            <div class="stat-trend">Awaiting your action</div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red">🚨</div>
        <div class="stat-body">
            <div class="stat-label">Reported Issues</div>
            <div class="stat-value" id="reportedIssues"><?php echo (int)$total_issues; ?></div>
            <div class="stat-trend">Needs attention</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Quick Actions</span>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="/spacio/frontend/admin/approvals.php" class="quick-action-btn">
                <div class="quick-action-icon">✓</div>
                <span>Approve Reservations</span>
            </a>
            <a href="/spacio/frontend/admin/inventory.php" class="quick-action-btn">
                <div class="quick-action-icon">📦</div>
                <span>Manage Inventory</span>
            </a>
            <a href="/spacio/frontend/admin/maintenance.php" class="quick-action-btn">
                <div class="quick-action-icon">🔧</div>
                <span>Maintenance</span>
            </a>
            <a href="/spacio/frontend/admin/reports.php" class="quick-action-btn">
                <div class="quick-action-icon">📈</div>
                <span>Reports &amp; Analytics</span>
            </a>
        </div>
    </div>
</div>

<!-- Status Summary -->
<div class="status-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Reservation Status</span>
        </div>
        <div class="card-body">
            <?php if ($total_pending > 0): ?>
                <div class="status-alert warning">
                    <span>⚠️</span>
                    <span><?php echo (int)$total_pending; ?> reservation(s) are waiting for your approval.</span>
                </div>
                <a href="/spacio/frontend/admin/approvals.php" class="btn btn-primary btn-full">
                    Review Now →
                </a>
            <?php else: ?>
                <div class="status-alert success">
                    <span>✅</span>
                    <span>All reservations are up to date.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">Issue Tracker</span>
        </div>
        <div class="card-body">
            <?php if ($total_issues > 0): ?>
                <div class="status-alert danger">
                    <span>🚨</span>
                    <span><?php echo (int)$total_issues; ?> issue(s) have been reported.</span>
                </div>
                <a href="/spacio/frontend/admin/maintenance.php" class="btn btn-danger btn-full">
                    View Issues →
                </a>
            <?php else: ?>
                <div class="status-alert success">
                    <span>✅</span>
                    <span>No issues reported.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function loadDashboardStats() {
    fetch(window.location.pathname + '?fetch_stats=1')
        .then(r => r.json())
        .then(data => {
            document.getElementById('totalReservations').textContent   = data.total_reservations;
            document.getElementById('pendingReservations').textContent = data.total_pending;
            document.getElementById('reportedIssues').textContent      = data.total_issues;
        })
        .catch(err => console.error('Dashboard stats error:', err));
}
setInterval(loadDashboardStats, 60000);
</script>

<?php include("../includes/footer.php"); ?>