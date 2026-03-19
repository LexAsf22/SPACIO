<?php
// frontend/teacher/dashboard.php
include("../includes/header.php");
checkRole('teacher');

$user_id = (int) $_SESSION['user']['id'];

// ── AJAX stats request — proxy to Django ──────────────────────────────────────
if (isset($_GET['fetch_stats'])) {
    header('Content-Type: application/json');

    $statsResult  = djangoGet('/api/v1/teacher/stats/?user_id=' . $user_id);
    $recentResult = djangoGet('/api/v1/teacher/recent-reservations/?user_id=' . $user_id . '&limit=5');

    $issues = 0;
    $rows   = [];

    if ($statsResult['success'] && isset($statsResult['data'])) {
        $issues = (int) ($statsResult['data']['pending_issues'] ?? 0);
    }

    if ($recentResult['success'] && isset($recentResult['data'])) {
        $raw = $recentResult['data']['results'] ?? $recentResult['data'] ?? [];
        foreach ($raw as $r) {
            $rows[] = [
                'student' => $r['student_name'] ?? '—',
                'lab'     => $r['lab_name']     ?? '—',
                'date'    => isset($r['date'])
                    ? date("F d, Y", strtotime($r['date']))
                    : '—',
                'time'    => $r['time_slot']    ?? '—',
                'status'  => $r['status']       ?? '—',
            ];
        }
    }

    echo json_encode([
        'issues'       => $issues,
        'recent_count' => count($rows),
        'rows'         => $rows,
    ]);
    exit;
}

// ── Normal page load ──────────────────────────────────────────────────────────
$statsResult  = djangoGet('/api/v1/teacher/stats/?user_id=' . $user_id);
$recentResult = djangoGet('/api/v1/teacher/recent-reservations/?user_id=' . $user_id . '&limit=5');

// Stats
if ($statsResult['success'] && isset($statsResult['data'])) {
    $total_issues = (int) ($statsResult['data']['pending_issues'] ?? 0);
} else {
    // Fallback
    $total_issues = $conn->query(
        "SELECT id FROM issues WHERE user_id={$user_id} AND status='Pending'"
    )->num_rows;
}

// Recent reservations
$recent_rows = [];

if ($recentResult['success'] && isset($recentResult['data'])) {
    $recent_rows = $recentResult['data']['results'] ?? $recentResult['data'] ?? [];
} else {
    // Fallback
    $res = $conn->query("
        SELECT r.*, l.lab_name, u.name AS student_name
        FROM   reservations r
        JOIN   laboratories l ON r.lab_id   = l.id
        JOIN   users        u ON r.user_id  = u.id
        ORDER  BY r.date DESC
        LIMIT  5
    ");
    while ($r = $res->fetch_assoc()) {
        $recent_rows[] = $r;
    }
}
?>

<h2>Teacher Dashboard</h2>

<div style="display:flex; gap:20px; margin-top:20px;">

    <div style="background:#4caf50; color:white; padding:20px; flex:1; border-radius:5px;">
        <h3>Pending Issues</h3>
        <p id="pendingIssues"><?php echo $total_issues; ?></p>
    </div>

    <div style="background:#f39c12; color:white; padding:20px; flex:1; border-radius:5px;">
        <h3>Recent Reservations</h3>
        <p id="recentCount"><?php echo count($recent_rows); ?></p>
    </div>

</div>

<h3 style="margin-top:30px;">Recent Lab Reservations</h3>

<input
    type="text"
    id="searchRes"
    placeholder="Search by student or lab..."
    style="padding:10px; margin:10px 0; width:50%;"
>

<table id="recentTable" border="1" cellpadding="10" cellspacing="0"
       style="border-collapse:collapse; width:100%;">
    <tr>
        <th>Student</th>
        <th>Lab</th>
        <th>Date</th>
        <th>Time Slot</th>
        <th>Status</th>
    </tr>

    <?php foreach ($recent_rows as $r): ?>
    <tr>
        <td><?php echo htmlspecialchars($r['student_name'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($r['lab_name']     ?? '—'); ?></td>
        <td><?php echo isset($r['date']) ? date("F d, Y", strtotime($r['date'])) : '—'; ?></td>
        <td><?php echo htmlspecialchars($r['time_slot']    ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($r['status']       ?? '—'); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<script>
function refreshDashboard() {
    fetch(window.location.pathname + '?fetch_stats=1')
        .then(res => res.json())
        .then(data => {
            document.getElementById('pendingIssues').textContent = data.issues;
            document.getElementById('recentCount').textContent   = data.recent_count;

            const table = document.getElementById('recentTable');
            table.querySelectorAll('tr:not(:first-child)').forEach(r => r.remove());

            data.rows.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${r.student}</td>
                    <td>${r.lab}</td>
                    <td>${r.date}</td>
                    <td>${r.time}</td>
                    <td>${r.status}</td>
                `;
                table.appendChild(tr);
            });
        })
        .catch(err => console.error('Dashboard refresh error:', err));
}

setInterval(refreshDashboard, 60000);

let debounce;
document.getElementById('searchRes').addEventListener('keyup', function () {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#recentTable tr:not(:first-child)').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    }, 200);
});
</script>

<?php include("../includes/footer.php"); ?>