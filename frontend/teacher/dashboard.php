<?php
<<<<<<< HEAD
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
=======
session_start();
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../includes/flash.php");

checkLogin();
checkRole('teacher');

$user   = $_SESSION['user'];
$userId = $user['id'];
$userName = $user['name'];

// ── Pull stats ──
$openIssues     = 0;
$resolvedIssues = 0;
$myIssues       = 0;
$recentIssues   = [];

$r = $conn->prepare("SELECT COUNT(*) as c FROM issues WHERE status = 'open'");
$r->execute();
$openIssues = $r->get_result()->fetch_assoc()['c'] ?? 0;

$r = $conn->prepare("SELECT COUNT(*) as c FROM issues WHERE status = 'resolved'");
$r->execute();
$resolvedIssues = $r->get_result()->fetch_assoc()['c'] ?? 0;

// Count issues reported by this teacher
$r = $conn->prepare("SELECT COUNT(*) as c FROM issues WHERE user_id = ?");
$r->bind_param("i", $userId);
$r->execute();
$myIssues = $r->get_result()->fetch_assoc()['c'] ?? 0;

// Get last 5 issues reported by this teacher
$r = $conn->prepare("SELECT * FROM issues WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$r->bind_param("i", $userId);
$r->execute();
$recentIssues = $r->get_result()->fetch_all(MYSQLI_ASSOC);

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
        <span class="eyebrow-text">Teacher Portal</span>
    </div>
    <h1 class="page-title">Good <?php
        $h = (int)date('H');
        echo $h < 12 ? 'morning' : ($h < 17 ? 'afternoon' : 'evening');
    ?>, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>.</h1>
    <p class="page-subtitle">Monitor lab usage, report issues, and stay on top of your campus resources.</p>
</div>

<!-- Stat Cards -->
<div class="stats-grid" style="margin-bottom:28px;">

    <div class="stat-card accent-red stagger-1">
        <span class="stat-card-icon">🚨</span>
        <div class="stat-card-value" data-count="<?php echo $openIssues; ?>">
            <?php echo $openIssues; ?>
        </div>
        <div class="stat-card-label">Open Issues</div>
        <span class="stat-card-trend <?php echo $openIssues > 0 ? 'down' : 'up'; ?>">
            <?php echo $openIssues > 0 ? 'Needs attention' : 'All resolved'; ?>
        </span>
    </div>

    <div class="stat-card accent-green stagger-2">
        <span class="stat-card-icon">✅</span>
        <div class="stat-card-value" data-count="<?php echo $resolvedIssues; ?>">
            <?php echo $resolvedIssues; ?>
        </div>
        <div class="stat-card-label">Resolved Issues</div>
        <span class="stat-card-trend up">Fixed</span>
    </div>

    <div class="stat-card accent-gold stagger-3">
        <span class="stat-card-icon">📋</span>
        <div class="stat-card-value" data-count="<?php echo $myIssues; ?>">
            <?php echo $myIssues; ?>
        </div>
        <div class="stat-card-label">My Reports</div>
        <span class="stat-card-trend neutral">Submitted by you</span>
    </div>

    <div class="stat-card accent-blue stagger-4">
        <span class="stat-card-icon">📊</span>
        <div class="stat-card-value">—</div>
        <div class="stat-card-label">Labs in Use</div>
        <span class="stat-card-trend neutral">Today</span>
>>>>>>> ec6daf5 (new)
    </div>

</div>

<!-- Quick Actions + Recent Issues -->
<div class="grid-2" style="gap:20px; align-items:start;">

<<<<<<< HEAD
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

            <a href="report_issue.php" class="btn btn-ghost" style="justify-content:flex-start; width:100%; gap:14px; padding:14px 16px; border-radius:10px; font-size:.82rem; text-transform:none; letter-spacing:0;">
                <span style="font-size:1.2rem;">🚨</span>
                <div style="text-align:left;">
                    <div style="font-weight:700; color:#fff; font-size:.85rem;">Report an Issue</div>
                    <div style="font-size:.73rem; color:rgba(255,255,255,.3); font-style:italic; margin-top:1px;">Flag broken equipment or classroom problems</div>
                </div>
                <span style="margin-left:auto; opacity:.3;">→</span>
            </a>

            <a href="lab_usage.php" class="btn btn-ghost" style="justify-content:flex-start; width:100%; gap:14px; padding:14px 16px; border-radius:10px; font-size:.82rem; text-transform:none; letter-spacing:0;">
                <span style="font-size:1.2rem;">📊</span>
                <div style="text-align:left;">
                    <div style="font-weight:700; color:#fff; font-size:.85rem;">Lab Usage</div>
                    <div style="font-size:.73rem; color:rgba(255,255,255,.3); font-style:italic; margin-top:1px;">View current and upcoming lab activity</div>
                </div>
                <span style="margin-left:auto; opacity:.3;">→</span>
            </a>

            <a href="issue_status.php" class="btn btn-ghost" style="justify-content:flex-start; width:100%; gap:14px; padding:14px 16px; border-radius:10px; font-size:.82rem; text-transform:none; letter-spacing:0;">
                <span style="font-size:1.2rem;">📋</span>
                <div style="text-align:left;">
                    <div style="font-weight:700; color:#fff; font-size:.85rem;">Issue Status</div>
                    <div style="font-size:.73rem; color:rgba(255,255,255,.3); font-style:italic; margin-top:1px;">Track the status of your submitted reports</div>
                </div>
                <span style="margin-left:auto; opacity:.3;">→</span>
            </a>

        </div>
    </div>

    <!-- Recent Issues -->
    <div class="card stagger-3">
        <div class="card-header">
            <div>
                <div class="card-title">My Recent Reports</div>
                <div class="card-subtitle">Issues you've submitted</div>
            </div>
            <a href="issue_status.php" class="btn btn-ghost btn-sm">View All</a>
        </div>

        <?php if (empty($recentIssues)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🚨</div>
            <div class="empty-state-title">No issues reported</div>
            <div class="empty-state-desc">Your reported issues will appear here.</div>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Issue</th>
                    <th>Location</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentIssues as $issue):
                    $status = $issue['status'] ?? 'open';
                    $badgeClass = match($status) {
                        'resolved'    => 'badge-green',
                        'in_progress' => 'badge-blue',
                        default       => 'badge-red',
                    };
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($issue['title'] ?? $issue['description'] ?? '—'); ?></td>
                    <td style="color:rgba(255,255,255,.4); font-size:.8rem;">
                        <?php echo htmlspecialchars($issue['location'] ?? '—'); ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
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