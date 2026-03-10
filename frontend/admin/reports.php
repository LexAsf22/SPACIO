<?php
include("../includes/header.php");
include(__DIR__ . "/../../backend/config/helpers.php");
checkRole('admin');

$total_reservations = $conn->query("SELECT * FROM reservations")->num_rows;
$approved = $conn->query("SELECT * FROM reservations WHERE status='Approved'")->num_rows;
$rejected = $conn->query("SELECT * FROM reservations WHERE status='Rejected'")->num_rows;
$pending = $conn->query("SELECT * FROM reservations WHERE status='Pending'")->num_rows;
?>

<h2>Reports & Analytics</h2>

<canvas id="reservationChart" width="400" height="200" style="background:#f9f9f9;margin-top:20px;"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('reservationChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Approved','Rejected','Pending'],
        datasets: [{
            label: 'Reservations',
            data: [<?php echo $approved; ?>, <?php echo $rejected; ?>, <?php echo $pending; ?>],
            backgroundColor: ['#2ecc71','#e74c3c','#f39c12']
        }]
    },
    options: {responsive:true, scales:{y:{beginAtZero:true}}}
});
</script>

<?php include("../includes/footer.php"); ?>