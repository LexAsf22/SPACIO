<?php
<<<<<<< HEAD
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
=======
session_start();
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../includes/flash.php");

checkLogin();
checkRole('student');

$pageTitle  = "My Reservations";
$activePage = "my_reservations";  // matches nav item

$user      = $_SESSION['user'];
$userId    = $user['id'];
$userName  = $user['name'];

// ── Pull stats ──
$totalReservations = 0;
$pendingCount      = 0;
$approvedCount     = 0;
$recentReservations = [];

$r = $conn->prepare("SELECT COUNT(*) as total FROM reservations WHERE user_id = ?");
$r->bind_param("i", $userId); $r->execute();
$totalReservations = $r->get_result()->fetch_assoc()['total'] ?? 0;

$r = $conn->prepare("SELECT COUNT(*) as c FROM reservations WHERE user_id = ? AND status = 'pending'");
$r->bind_param("i", $userId); $r->execute();
$pendingCount = $r->get_result()->fetch_assoc()['c'] ?? 0;

$r = $conn->prepare("SELECT COUNT(*) as c FROM reservations WHERE user_id = ? AND status = 'approved'");
$r->bind_param("i", $userId); $r->execute();
$approvedCount = $r->get_result()->fetch_assoc()['c'] ?? 0;

$r = $conn->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$r->bind_param("i", $userId); $r->execute();
$recentReservations = $r->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Page meta ──
$pageTitle   = "Dashboard";
$pageEyebrow = "Welcome back, " . htmlspecialchars(explode(' ', $userName)[0]);
$activePage  = "dashboard";

include("../includes/header.php");
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-eyebrow">
        <div class="eyebrow-dot"></div>
        <span class="eyebrow-text">Student Portal</span>
    </div>
    <h1 class="page-title">Good <?php
        $h = (int)date('H');
        echo $h < 12 ? 'morning' : ($h < 17 ? 'afternoon' : 'evening');
    ?>, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>.</h1>
    <p class="page-subtitle">Here's a snapshot of your reservations and available resources.</p>
</div>

<!-- Stat Cards -->
<div class="stats-grid" style="margin-bottom:28px;">

    <div class="stat-card accent-green stagger-1">
        <span class="stat-card-icon">🗓️</span>
        <div class="stat-card-value" data-count="<?php echo $totalReservations; ?>">
            <?php echo $totalReservations; ?>
        </div>
        <div class="stat-card-label">Total Reservations</div>
        <span class="stat-card-trend neutral">All time</span>
    </div>

    <div class="stat-card accent-gold stagger-2">
        <span class="stat-card-icon">⏳</span>
        <div class="stat-card-value" data-count="<?php echo $pendingCount; ?>">
            <?php echo $pendingCount; ?>
        </div>
        <div class="stat-card-label">Pending Approval</div>
        <span class="stat-card-trend <?php echo $pendingCount > 0 ? 'neutral' : 'up'; ?>">
            <?php echo $pendingCount > 0 ? 'Awaiting review' : 'All clear'; ?>
        </span>
    </div>

    <div class="stat-card accent-blue stagger-3">
        <span class="stat-card-icon">✅</span>
        <div class="stat-card-value" data-count="<?php echo $approvedCount; ?>">
            <?php echo $approvedCount; ?>
        </div>
        <div class="stat-card-label">Approved</div>
        <span class="stat-card-trend up">Confirmed</span>
    </div>

    <div class="stat-card accent-green stagger-4">
        <span class="stat-card-icon">🏫</span>
        <div class="stat-card-value">—</div>
        <div class="stat-card-label">Labs Available</div>
        <span class="stat-card-trend up">Open now</span>
>>>>>>> ec6daf5 (new)
    </div>

</div>

<!-- Quick Actions + Recent Reservations -->
<div class="grid-2" style="gap:20px; align-items:start;">

<<<<<<< HEAD
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
=======
    <!-- Quick Actions -->
    <div class="card stagger-2">
        <div class="card-header">
            <div>
                <div class="card-title">Quick Actions</div>
                <div class="card-subtitle">Jump to common tasks</div>
            </div>
        </div>
        <div class="card-body" style="display:flex; flex-direction:column; gap:10px;">

            <a href="reserve_lab.php" class="btn btn-ghost" style="justify-content:flex-start; width:100%; gap:14px; padding:14px 16px; border-radius:10px; font-size:.82rem; text-transform:none; letter-spacing:0;">
                <span style="font-size:1.2rem;">🏫</span>
                <div style="text-align:left;">
                    <div style="font-weight:700; color:#fff; font-size:.85rem;">Reserve a Lab</div>
                    <div style="font-size:.73rem; color:rgba(255,255,255,.3); font-style:italic; margin-top:1px;">Book lab time or a computer station</div>
                </div>
                <span style="margin-left:auto; opacity:.3;">→</span>
            </a>

            <a href="reserve_equipment.php" class="btn btn-ghost" style="justify-content:flex-start; width:100%; gap:14px; padding:14px 16px; border-radius:10px; font-size:.82rem; text-transform:none; letter-spacing:0;">
                <span style="font-size:1.2rem;">🔬</span>
                <div style="text-align:left;">
                    <div style="font-weight:700; color:#fff; font-size:.85rem;">Reserve Equipment</div>
                    <div style="font-size:.73rem; color:rgba(255,255,255,.3); font-style:italic; margin-top:1px;">Request lab equipment or tools</div>
                </div>
                <span style="margin-left:auto; opacity:.3;">→</span>
            </a>

            <a href="view_labs.php" class="btn btn-ghost" style="justify-content:flex-start; width:100%; gap:14px; padding:14px 16px; border-radius:10px; font-size:.82rem; text-transform:none; letter-spacing:0;">
                <span style="font-size:1.2rem;">🗺️</span>
                <div style="text-align:left;">
                    <div style="font-weight:700; color:#fff; font-size:.85rem;">Browse Labs</div>
                    <div style="font-size:.73rem; color:rgba(255,255,255,.3); font-style:italic; margin-top:1px;">View available labs and schedules</div>
                </div>
                <span style="margin-left:auto; opacity:.3;">→</span>
            </a>

            <a href="view_equipment.php" class="btn btn-ghost" style="justify-content:flex-start; width:100%; gap:14px; padding:14px 16px; border-radius:10px; font-size:.82rem; text-transform:none; letter-spacing:0;">
                <span style="font-size:1.2rem;">📦</span>
                <div style="text-align:left;">
                    <div style="font-weight:700; color:#fff; font-size:.85rem;">Browse Equipment</div>
                    <div style="font-size:.73rem; color:rgba(255,255,255,.3); font-style:italic; margin-top:1px;">Check what's available to borrow</div>
                </div>
                <span style="margin-left:auto; opacity:.3;">→</span>
            </a>

        </div>
    </div>

    <!-- Recent Reservations -->
    <div class="card stagger-3">
        <div class="card-header">
            <div>
                <div class="card-title">Recent Reservations</div>
                <div class="card-subtitle">Your last 5 requests</div>
            </div>
            <a href="my_reservations.php" class="btn btn-ghost btn-sm">View All</a>
        </div>

        <?php if (empty($recentReservations)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🗓️</div>
            <div class="empty-state-title">No reservations yet</div>
            <div class="empty-state-desc">Your bookings will appear here once you make one.</div>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item / Lab</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentReservations as $res):
                    $status = $res['status'] ?? 'pending';
                    $badgeClass = match($status) {
                        'approved' => 'badge-green',
                        'rejected' => 'badge-red',
                        default    => 'badge-gold',
                    };
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($res['item_name'] ?? $res['lab_name'] ?? '—'); ?></td>
                    <td style="color:rgba(255,255,255,.4); font-size:.8rem;">
                        <?php echo isset($res['created_at']) ? date('M j, Y', strtotime($res['created_at'])) : '—'; ?>
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

</div>
>>>>>>> ec6daf5 (new)

<?php include("../includes/footer.php"); ?>