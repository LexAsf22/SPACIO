<?php
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
?>

<h2>Admin Dashboard</h2>

<div style="display:flex; gap:20px; margin-top:20px;">

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
    </div>

</div>

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

<?php include("../includes/footer.php"); ?>