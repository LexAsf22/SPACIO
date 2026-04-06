<?php
// frontend/teacher/dashboard.php
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
checkRole('teacher');

$user    = $_SESSION['user'];
$user_id = (int) $user['id'];

// ── AJAX stats request ────────────────────────────────────────────────────────
if (isset($_GET['fetch_stats'])) {
    header('Content-Type: application/json');

    $statsResult  = djangoGet('/api/v1/teacher/stats/?user_id=' . $user_id);
    $recentResult = djangoGet('/api/v1/teacher/recent-reservations/?user_id=' . $user_id . '&limit=5');

    $issues = 0;
    $rows   = [];

    if ($statsResult['success'] && isset($statsResult['data'])) {
        $issues = (int)($statsResult['data']['pending_issues'] ?? 0);
    }

    if ($recentResult['success'] && isset($recentResult['data'])) {
        $raw = $recentResult['data']['results'] ?? $recentResult['data'] ?? [];
        foreach ($raw as $r) {
            $rows[] = [
                'student' => $r['student_name'] ?? '—',
                'lab'     => $r['lab_name']     ?? '—',
                'date'    => isset($r['date']) ? date("M d, Y", strtotime($r['date'])) : '—',
                'time'    => $r['time_slot']    ?? '—',
                'status'  => $r['status']       ?? '—',
            ];
        }
    }

    echo json_encode(['issues' => $issues, 'recent_count' => count($rows), 'rows' => $rows]);
    exit;
}

// ── Normal page load ──────────────────────────────────────────────────────────
$statsResult  = djangoGet('/api/v1/teacher/stats/?user_id=' . $user_id);
$recentResult = djangoGet('/api/v1/teacher/recent-reservations/?user_id=' . $user_id . '&limit=5');

if ($statsResult['success'] && isset($statsResult['data'])) {
    $total_issues = (int)($statsResult['data']['pending_issues'] ?? 0);
} else {
    $total_issues = $conn->query("SELECT id FROM issues WHERE user_id={$user_id} AND status='Pending'")->num_rows;
}

$recent_rows = [];
if ($recentResult['success'] && isset($recentResult['data'])) {
    $recent_rows = $recentResult['data']['results'] ?? $recentResult['data'] ?? [];
} else {
    $res = $conn->query("
        SELECT r.*, l.lab_name, u.name AS student_name
        FROM reservations r
        JOIN laboratories l ON r.lab_id  = l.id
        JOIN users        u ON r.user_id = u.id
        ORDER BY r.date DESC LIMIT 5
    ");
    while ($r = $res->fetch_assoc()) $recent_rows[] = $r;
}

$first_name = htmlspecialchars(explode(' ', trim($user['name']))[0]);

include("../includes/header.php");
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Welcome back, <?php echo $first_name; ?></h1>
        <p class="page-subtitle"><?php echo date('l, F d, Y'); ?> &mdash; Lab activity overview</p>
    </div>
    <a href="/spacio/frontend/teacher/report_issue.php" class="btn btn-primary">
        + Report Issue
    </a>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card red">
        <div class="stat-icon red">🚨</div>
        <div class="stat-body">
            <div class="stat-label">Pending Issues</div>
            <div class="stat-value" id="pendingIssues"><?php echo $total_issues; ?></div>
            <div class="stat-trend">Awaiting resolution</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon blue">📋</div>
        <div class="stat-body">
            <div class="stat-label">Recent Reservations</div>
            <div class="stat-value" id="recentCount"><?php echo count($recent_rows); ?></div>
            <div class="stat-trend">Last 5 entries</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green">🔬</div>
        <div class="stat-body">
            <div class="stat-label">Lab Usage</div>
            <div class="stat-value">—</div>
            <div class="stat-trend">
                <a href="/spacio/frontend/teacher/lab_usage.php"
                   style="color:var(--green-500); font-style:normal;">View report →</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reservations -->
<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Recent Lab Reservations</span>
        <div style="display:flex; gap:8px; align-items:center;">
            <input type="text" id="searchRes" class="form-control"
                   placeholder="Search student or lab..."
                   style="width:200px;">
            <a href="/spacio/frontend/teacher/lab_usage.php" class="btn btn-secondary btn-sm">
                View All
            </a>
        </div>
    </div>

    <div class="table-wrap" style="border:none; border-radius:0; box-shadow:none;">
        <table class="sp-table" id="recentTable">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Lab</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($recent_rows)): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <span class="empty-state-icon">📭</span>
                            <p class="empty-state-text">No recent reservations found.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($recent_rows as $r):
                    $status = $r['status'] ?? 'Pending';
                    $badge  = match(strtolower($status)) {
                        'approved' => 'badge-green',
                        'rejected' => 'badge-red',
                        default    => 'badge-yellow',
                    };
                ?>
                <tr>
                    <td style="font-weight:600; color:var(--ink-800);">
                        <?php echo htmlspecialchars($r['student_name'] ?? '—'); ?>
                    </td>
                    <td><?php echo htmlspecialchars($r['lab_name'] ?? '—'); ?></td>
                    <td class="td-mono">
                        <?php echo isset($r['date']) ? date("M d, Y", strtotime($r['date'])) : '—'; ?>
                    </td>
                    <td class="td-mono"><?php echo htmlspecialchars($r['time_slot'] ?? '—'); ?></td>
                    <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <a href="/spacio/frontend/teacher/lab_usage.php" class="text-muted text-small">
            View full lab usage report →
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions" style="grid-template-columns:1fr 1fr;">
    <a href="/spacio/frontend/teacher/report_issue.php" class="quick-action-btn">
        <div class="quick-action-icon">⚠️</div>
        <div>
            <div style="font-weight:600; margin-bottom:2px;">Report an Issue</div>
            <div class="text-muted text-small">Submit a maintenance or lab problem</div>
        </div>
    </a>
    <a href="/spacio/frontend/teacher/issue_status.php" class="quick-action-btn">
        <div class="quick-action-icon">🔍</div>
        <div>
            <div style="font-weight:600; margin-bottom:2px;">Issue Status</div>
            <div class="text-muted text-small">Track your reported issues</div>
        </div>
    </a>
</div>

<script>
function refreshDashboard() {
    fetch(window.location.pathname + '?fetch_stats=1')
        .then(res => res.json())
        .then(data => {
            document.getElementById('pendingIssues').textContent = data.issues;
            document.getElementById('recentCount').textContent   = data.recent_count;

            const tbody = document.querySelector('#recentTable tbody');
            tbody.innerHTML = '';

            if (!data.rows.length) {
                tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><span class="empty-state-icon">📭</span><p class="empty-state-text">No recent reservations.</p></div></td></tr>`;
                return;
            }

            data.rows.forEach(r => {
                const status = r.status ?? 'Pending';
                const badge  = status.toLowerCase() === 'approved' ? 'badge-green'
                             : status.toLowerCase() === 'rejected'  ? 'badge-red'
                             : 'badge-yellow';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight:600;color:var(--ink-800)">${r.student ?? '—'}</td>
                    <td>${r.lab ?? '—'}</td>
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
document.getElementById('searchRes').addEventListener('input', function () {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#recentTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    }, 200);
});
</script>

<?php include("../includes/footer.php"); ?>