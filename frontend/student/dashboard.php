<?php
// frontend/student/dashboard.php
include("../includes/header.php");
checkRole('student');

$user_id = (int) $_SESSION['user']['id'];

// ── AJAX stats request — proxy to Django ──────────────────────────────────────
if (isset($_GET['fetch_stats'])) {
    header('Content-Type: application/json');

    $result = djangoGet('/api/v1/student/dashboard/?user_id=' . $user_id);

    if ($result['success'] && isset($result['data'])) {
        echo json_encode([
            'labs'   => $result['data']['available_labs']       ?? 0,
            'active' => $result['data']['active_reservations']  ?? 0,
            'rows'   => $result['data']['upcoming_reservations'] ?? [],
        ]);
    } else {
        // Fallback: query DB directly
        $available_labs      = $conn->query("SELECT id FROM laboratories")->num_rows;
        $active_reservations = $conn->query("SELECT id FROM reservations WHERE user_id={$user_id} AND status='Approved'")->num_rows;

        $upcoming = $conn->query("
            SELECT r.*, l.lab_name
            FROM   reservations r
            JOIN   laboratories l ON r.lab_id = l.id
            WHERE  r.user_id = {$user_id} AND r.date >= CURDATE()
            ORDER  BY r.date ASC LIMIT 5
        ");

        $rows = [];
        while ($r = $upcoming->fetch_assoc()) {
            $rows[] = [
                'lab'    => $r['lab_name'],
                'date'   => date("F d, Y", strtotime($r['date'])),
                'time'   => $r['time_slot'],
                'status' => $r['status'],
            ];
        }

        echo json_encode([
            'labs'   => $available_labs,
            'active' => $active_reservations,
            'rows'   => $rows,
        ]);
    }
    exit;
}

// ── Normal page load — fetch from Django ──────────────────────────────────────
$result = djangoGet('/api/v1/student/dashboard/?user_id=' . $user_id);

if ($result['success'] && isset($result['data'])) {
    $available_labs      = (int) ($result['data']['available_labs']       ?? 0);
    $active_reservations = (int) ($result['data']['active_reservations']  ?? 0);
    $upcoming_rows       =        $result['data']['upcoming_reservations'] ?? [];
} else {
    // Fallback: query DB directly
    $available_labs      = $conn->query("SELECT id FROM laboratories")->num_rows;
    $active_reservations = $conn->query("SELECT id FROM reservations WHERE user_id={$user_id} AND status='Approved'")->num_rows;

    $upcoming = $conn->query("
        SELECT r.*, l.lab_name
        FROM   reservations r
        JOIN   laboratories l ON r.lab_id = l.id
        WHERE  r.user_id = {$user_id} AND r.date >= CURDATE()
        ORDER  BY r.date ASC LIMIT 5
    ");

    $upcoming_rows = [];
    while ($r = $upcoming->fetch_assoc()) {
        $upcoming_rows[] = [
            'lab'    => $r['lab_name'],
            'date'   => date("F d, Y", strtotime($r['date'])),
            'time'   => $r['time_slot'],
            'status' => $r['status'],
        ];
    }
}
?>

<h2>Student Dashboard</h2>

<div style="display:flex; gap:20px; margin-top:20px;">

    <div style="background:#4caf50; color:white; padding:20px; flex:1; border-radius:5px;">
        <h3>Available Labs</h3>
        <p id="availableLabs"><?php echo $available_labs; ?></p>
    </div>

    <div style="background:#f39c12; color:white; padding:20px; flex:1; border-radius:5px;">
        <h3>Active Reservations</h3>
        <p id="activeReservations"><?php echo $active_reservations; ?></p>
    </div>

</div>

<h3 style="margin-top:30px;">Upcoming Reservations</h3>

<input
    id="searchReservations"
    placeholder="Search reservations..."
    style="padding:10px; margin:10px 0; width:50%;"
>

<table id="upcomingTable" border="1" cellpadding="10" cellspacing="0"
       style="border-collapse:collapse; width:100%;">
    <thead style="background:#2c5f2e; color:white;">
        <tr>
            <th>Lab</th>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($upcoming_rows as $r): ?>
        <tr>
            <td><?php echo htmlspecialchars($r['lab']    ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($r['date']   ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($r['time']   ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($r['status'] ?? '—'); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
function refreshDashboard() {
    fetch(window.location.pathname + '?fetch_stats=1')
        .then(res => res.json())
        .then(data => {
            document.getElementById('availableLabs').textContent      = data.labs;
            document.getElementById('activeReservations').textContent = data.active;

            const tbody = document.querySelector('#upcomingTable tbody');
            tbody.innerHTML = '';

            data.rows.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${r.lab    ?? '—'}</td>
                    <td>${r.date   ?? '—'}</td>
                    <td>${r.time   ?? '—'}</td>
                    <td>${r.status ?? '—'}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => console.error('Dashboard refresh error:', error));
}

// Auto-refresh every 60 seconds
setInterval(refreshDashboard, 60000);

// Search with debounce
let debounce;
document.getElementById('searchReservations').addEventListener('keyup', function () {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#upcomingTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    }, 200);
});
</script>

<?php include("../includes/footer.php"); ?>