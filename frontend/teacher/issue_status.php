<?php
// frontend/teacher/issue_status.php
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
checkRole('teacher');

$user_id = (int) $_SESSION['user']['id'];

// ── Fetch this teacher's issues ───────────────────────────────────────────────
$result = djangoGet('/api/v1/issues/?user_id=' . $user_id . '&ordering=-created_at');
$issues = [];

if ($result['success'] && isset($result['data'])) {
    $issues = $result['data']['results'] ?? $result['data'] ?? [];
} else {
    $res = $conn->query(
        "SELECT * FROM issues WHERE user_id={$user_id} ORDER BY created_at DESC"
    );
    while ($i = $res->fetch_assoc()) $issues[] = $i;
}

include("../includes/header.php");
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">My Reported Issues</h1>
        <p class="page-subtitle">Track the status of issues you've submitted</p>
    </div>
    <a href="/spacio/frontend/teacher/report_issue.php" class="btn btn-primary">
        + Report New Issue
    </a>
</div>

<!-- Search -->
<div class="filter-bar">
    <input type="text" id="searchIssues" class="form-control"
           placeholder="Search by room, category, status..."
           style="min-width:260px;">
    <span class="filter-summary"><?php echo count($issues); ?> issue(s) on record</span>
</div>

<!-- Table -->
<div class="table-wrap">
    <table class="sp-table" id="issueTable">
        <thead>
            <tr>
                <th style="width:50px; text-align:center;">#</th>
                <th>Campus</th>
                <th>Room</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Reported</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($issues)): ?>
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <span class="empty-state-icon">📋</span>
                        <p class="empty-state-text">No issues reported yet.</p>
                    </div>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($issues as $i):
                $status   = $i['status']   ?? 'Pending';
                $priority = $i['priority'] ?? 'Low';

                $status_badge = match($status) {
                    'Done'        => 'badge-green',
                    'In Progress' => 'badge-blue',
                    default       => 'badge-yellow',
                };

                $priority_badge = match($priority) {
                    'High'   => 'badge-red',
                    'Medium' => 'badge-yellow',
                    default  => 'badge-gray',
                };
            ?>
            <tr>
                <td style="text-align:center;" class="td-mono"><?php echo (int)$i['id']; ?></td>
                <td><?php echo htmlspecialchars($i['campus']   ?? '—'); ?></td>
                <td style="font-weight:500; color:var(--ink-800);">
                    <?php echo htmlspecialchars($i['room']     ?? '—'); ?>
                </td>
                <td><?php echo htmlspecialchars($i['category'] ?? '—'); ?></td>
                <td>
                    <span class="badge <?php echo $priority_badge; ?>">
                        <?php echo htmlspecialchars($priority); ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?php echo $status_badge; ?>">
                        <?php echo htmlspecialchars($status); ?>
                    </span>
                </td>
                <td class="td-mono">
                    <?php echo isset($i['created_at'])
                        ? date("M d, Y H:i", strtotime($i['created_at']))
                        : '—';
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('searchIssues').addEventListener('input', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#issueTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>