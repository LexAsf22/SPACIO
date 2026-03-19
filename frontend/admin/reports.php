<?php
// frontend/admin/reports.php
include("../includes/header.php");
checkRole('admin');

// ── Fetch report data from Django ─────────────────────────────────────────────
$result = djangoGet('/api/v1/reports/reservations/');

if ($result['success'] && isset($result['data'])) {
    $approved = (int) ($result['data']['approved'] ?? 0);
    $rejected = (int) ($result['data']['rejected'] ?? 0);
    $pending  = (int) ($result['data']['pending']  ?? 0);
    $total    = $approved + $rejected + $pending;
} else {
    // Fallback: query DB directly if Django is unreachable
    $approved = $conn->query("SELECT id FROM reservations WHERE status='Approved'")->num_rows;
    $rejected = $conn->query("SELECT id FROM reservations WHERE status='Rejected'")->num_rows;
    $pending  = $conn->query("SELECT id FROM reservations WHERE status='Pending'")->num_rows;
    $total    = $approved + $rejected + $pending;
}
?>

<h2>Reports &amp; Analytics</h2>

<canvas id="reservationChart" width="400" height="200"
        style="background:#f9f9f9; margin-top:20px;"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('reservationChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Approved', 'Rejected', 'Pending'],
        datasets: [{
            label: 'Reservations',
            data: [
                <?php echo $approved; ?>,
                <?php echo $rejected; ?>,
                <?php echo $pending; ?>
            ],
            backgroundColor: ['#2ecc71', '#e74c3c', '#f39c12'],
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php include("../includes/footer.php"); ?>