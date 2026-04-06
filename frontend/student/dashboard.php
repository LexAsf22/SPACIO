<?php
// frontend/student/dashboard.php
include("../includes/header.php");
checkRole('student');

$user_id = (int) $_SESSION['user']['id'];

// ── AJAX stats request ────────────────────────────────────────────────────────
if (isset($_GET['fetch_stats'])) {
    header('Content-Type: application/json');
    $result = djangoGet('/api/v1/student/dashboard/?user_id=' . $user_id);
    if ($result['success'] && isset($result['data'])) {
        echo json_encode([
            'labs'   => $result['data']['available_labs']        ?? 0,
            'active' => $result['data']['active_reservations']   ?? 0,
            'rows'   => $result['data']['upcoming_reservations'] ?? [],
        ]);
    } else {
        $available_labs      = $conn->query("SELECT id FROM laboratories")->num_rows;
        $active_reservations = $conn->query("SELECT id FROM reservations WHERE user_id={$user_id} AND status='Approved'")->num_rows;
        $upcoming = $conn->query("
            SELECT r.*, l.lab_name FROM reservations r
            JOIN laboratories l ON r.lab_id = l.id
            WHERE r.user_id = {$user_id} AND r.date >= CURDATE()
            ORDER BY r.date ASC LIMIT 5
        ");
        $rows = [];
        while ($r = $upcoming->fetch_assoc()) {
            $rows[] = ['lab' => $r['lab_name'], 'date' => date("F d, Y", strtotime($r['date'])), 'time' => $r['time_slot'], 'status' => $r['status']];
        }
        echo json_encode(['labs' => $available_labs, 'active' => $active_reservations, 'rows' => $rows]);
    }
    exit;
}

// ── Normal page load ──────────────────────────────────────────────────────────
$result = djangoGet('/api/v1/student/dashboard/?user_id=' . $user_id);
if ($result['success'] && isset($result['data'])) {
    $available_labs      = (int) ($result['data']['available_labs']        ?? 0);
    $active_reservations = (int) ($result['data']['active_reservations']   ?? 0);
    $upcoming_rows       =        $result['data']['upcoming_reservations'] ?? [];
} else {
    $available_labs      = $conn->query("SELECT id FROM laboratories")->num_rows;
    $active_reservations = $conn->query("SELECT id FROM reservations WHERE user_id={$user_id} AND status='Approved'")->num_rows;
    $upcoming = $conn->query("
        SELECT r.*, l.lab_name FROM reservations r
        JOIN laboratories l ON r.lab_id = l.id
        WHERE r.user_id = {$user_id}
        ORDER BY r.date DESC LIMIT 5
    ");
    $upcoming_rows = [];
    while ($r = $upcoming->fetch_assoc()) {
        $upcoming_rows[] = ['lab' => $r['lab_name'], 'date' => date("F d, Y", strtotime($r['date'])), 'time' => $r['time_slot'], 'status' => $r['status']];
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Welcome back, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?> 👋</h1>
        <p class="page-subtitle">Here's what's happening with your reservations today.</p>
    </div>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">🔬</div>
        <div class="stat-body">
            <div class="stat-label">Available Labs</div>
            <div class="stat-value" id="availableLabs"><?php echo $available_labs; ?></div>
            <div class="stat-trend">Ready to reserve</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">✅</div>
        <div class="stat-body">
            <div class="stat-label">Active Reservations</div>
            <div class="stat-value" id="activeReservations"><?php echo $active_reservations; ?></div>
            <div class="stat-trend">Approved & upcoming</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">📋</div>
        <div class="stat-body">
            <div class="stat-label">Upcoming</div>
            <div class="stat-value"><?php echo count($upcoming_rows); ?></div>
            <div class="stat-trend">Scheduled reservations</div>
        </div>
    </div>
</div>

<!-- Upcoming Reservations Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Upcoming Reservations</span>
        <div class="filter-bar" style="margin:0;">
            <input
                id="searchReservations"
                type="text"
                class="form-control"
                placeholder="Search..."
                style="min-width:160px; width:160px;"
            >
            <a href="/spacio/frontend/student/my_reservations.php" class="btn btn-secondary btn-sm">
                View All
            </a>
        </div>
    </div>

    <div class="table-wrap" style="border:none; border-radius:0; box-shadow:none;">
        <table class="sp-table" id="upcomingTable">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($upcoming_rows)): ?>
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <div class="empty-state-text">No upcoming reservations. <a href="/spacio/frontend/student/reserve_lab.php">Reserve a lab</a> to get started.</div>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($upcoming_rows as $r):
                    $status = $r['status'] ?? 'Pending';
                    $badge  = match(strtolower($status)) {
                        'approved' => 'badge-green',
                        'rejected' => 'badge-red',
                        default    => 'badge-yellow',
                    };
                ?>
                <tr>
                    <td><span class="badge badge-blue"><?php echo isset($r['lab']) && $r['lab'] !== '—' ? 'Lab' : 'Equipment'; ?></span></td>
                    <td><strong><?php echo htmlspecialchars($r['lab'] !== '—' ? $r['lab'] : ($r['equipment'] ?? '—')); ?></strong></td>
                    <td class="td-mono"><?php echo htmlspecialchars($r['date'] ?? '—'); ?></td>
                    <td class="td-mono"><?php echo htmlspecialchars($r['time'] ?? '—'); ?></td>
                    <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <a href="/spacio/frontend/student/my_reservations.php" class="text-muted text-small">
            View all reservations →
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:20px;">
    <a href="/spacio/frontend/student/reserve_lab.php" class="card" style="text-decoration:none; padding:20px; display:flex; align-items:center; gap:14px; transition: box-shadow .15s, transform .15s;" onmouseover="this.style.boxShadow='var(--shadow-md)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
        <div class="stat-icon green" style="flex-shrink:0;">🔬</div>
        <div>
            <div style="font-weight:600; color:var(--gray-900); margin-bottom:2px;">Reserve a Lab</div>
            <div class="text-muted text-small">Book a laboratory for your class or study session</div>
        </div>
    </a>
    <a href="/spacio/frontend/student/reserve_equipment.php" class="card" style="text-decoration:none; padding:20px; display:flex; align-items:center; gap:14px; transition: box-shadow .15s, transform .15s;" onmouseover="this.style.boxShadow='var(--shadow-md)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
        <div class="stat-icon blue" style="flex-shrink:0;">🖥️</div>
        <div>
            <div style="font-weight:600; color:var(--gray-900); margin-bottom:2px;">Reserve Equipment</div>
            <div class="text-muted text-small">Borrow equipment for your academic needs</div>
        </div>
    </a>
</div>

<script>
function refreshDashboard() {
    fetch(window.location.pathname + '?fetch_stats=1')
        .then(res => res.json())
        .then(data => {
            document.getElementById('availableLabs').textContent      = data.labs;
            document.getElementById('activeReservations').textContent = data.active;
            const tbody = document.querySelector('#upcomingTable tbody');
            tbody.innerHTML = '';
            if (!data.rows.length) {
                tbody.innerHTML = `<tr><td colspan="4"><div class="empty-state"><div class="empty-state-icon">📭</div><div class="empty-state-text">No upcoming reservations.</div></div></td></tr>`;
                return;
            }
            data.rows.forEach(r => {
                const status = r.status ?? 'Pending';
                const badge  = status === 'Approved' ? 'badge-green' : status === 'Rejected' ? 'badge-red' : 'badge-yellow';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${r.lab ?? '—'}</strong></td>
                    <td class="td-mono">${r.date ?? '—'}</td>
                    <td class="td-mono">${r.time ?? '—'}</td>
                    <td><span class="badge ${badge}">${status}</span></td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => console.error('Dashboard refresh error:', err));
}

setInterval(refreshDashboard, 60000);

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