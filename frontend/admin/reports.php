<?php
// frontend/admin/reports.php
include("../includes/header.php");
checkRole('admin');

// ── Fetch report data ─────────────────────────────────────────────────────────
$result = djangoGet('/api/v1/reports/reservations/');

if ($result['success'] && isset($result['data'])) {
    $approved = (int)($result['data']['approved'] ?? 0);
    $rejected = (int)($result['data']['rejected'] ?? 0);
    $pending  = (int)($result['data']['pending']  ?? 0);
    $total    = $approved + $rejected + $pending;
} else {
    $approved = $conn->query("SELECT id FROM reservations WHERE status='Approved'")->num_rows;
    $rejected = $conn->query("SELECT id FROM reservations WHERE status='Rejected'")->num_rows;
    $pending  = $conn->query("SELECT id FROM reservations WHERE status='Pending'")->num_rows;
    $total    = $approved + $rejected + $pending;
}

$approval_rate = $total > 0 ? round(($approved / $total) * 100) : 0;
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Reports &amp; Analytics</h1>
        <p class="page-subtitle">Reservation statistics and system usage overview</p>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom:24px;">
    <div class="stat-card green">
        <div class="stat-icon green">📊</div>
        <div class="stat-body">
            <div class="stat-label">Total</div>
            <div class="stat-value"><?php echo $total; ?></div>
            <div class="stat-trend">All reservations</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green">✅</div>
        <div class="stat-body">
            <div class="stat-label">Approved</div>
            <div class="stat-value"><?php echo $approved; ?></div>
            <div class="stat-trend"><?php echo $approval_rate; ?>% approval rate</div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red">✗</div>
        <div class="stat-body">
            <div class="stat-label">Rejected</div>
            <div class="stat-value"><?php echo $rejected; ?></div>
            <div class="stat-trend">Declined requests</div>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange">⏳</div>
        <div class="stat-body">
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?php echo $pending; ?></div>
            <div class="stat-trend">Awaiting review</div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Reservation Breakdown</span>
        <span class="badge badge-gray"><?php echo $total; ?> total</span>
    </div>
    <div class="card-body chart-container">
        <div class="chart-legend">
            <div class="chart-legend-item">
                <div class="chart-legend-dot" style="background:#3d7a41;"></div>
                Approved (<?php echo $approved; ?>)
            </div>
            <div class="chart-legend-item">
                <div class="chart-legend-dot" style="background:#f87171;"></div>
                Rejected (<?php echo $rejected; ?>)
            </div>
            <div class="chart-legend-item">
                <div class="chart-legend-dot" style="background:#d97706;"></div>
                Pending (<?php echo $pending; ?>)
            </div>
        </div>
        <canvas id="reservationChart" height="90"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('reservationChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Approved', 'Rejected', 'Pending'],
        datasets: [{
            label: 'Reservations',
            data: [<?php echo $approved; ?>, <?php echo $rejected; ?>, <?php echo $pending; ?>],
            backgroundColor: [
                'rgba(61,122,65,.85)',
                'rgba(248,113,113,.85)',
                'rgba(217,119,6,.85)',
            ],
            borderColor: [
                'rgba(61,122,65,1)',
                'rgba(248,113,113,1)',
                'rgba(217,119,6,1)',
            ],
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(20,21,16,.92)',
                titleColor: '#f0f1eb',
                bodyColor: '#9a9d87',
                padding: 12,
                cornerRadius: 6,
                callbacks: {
                    label: function(ctx) {
                        const total = <?php echo $total; ?>;
                        const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                        return ` ${ctx.raw} reservations (${pct}%)`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: {
                    color: '#757860',
                    font: { family: "'Instrument Sans', sans-serif", weight: '600', size: 12 }
                },
                border: { color: '#dfe0d5' }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(194,196,176,.4)', drawBorder: false },
                ticks: {
                    color: '#9a9d87',
                    font: { family: "'DM Mono', monospace", size: 11 },
                    stepSize: 1
                },
                border: { display: false }
            }
        }
    }
});
</script>

<?php include("../includes/footer.php"); ?>