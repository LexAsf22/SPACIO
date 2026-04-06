<?php
// frontend/teacher/lab_usage.php
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
checkRole('teacher');

// ── Fetch approved lab usage ───────────────────────────────────────────────────
$result = djangoGet('/api/v1/reports/usage/?status=Approved&limit=50&ordering=-date');
$usage  = [];

if ($result['success'] && isset($result['data'])) {
    $usage = $result['data']['results'] ?? $result['data'] ?? [];
} else {
    $res = $conn->query("
        SELECT r.*, u.name AS student_name, l.lab_name
        FROM   reservations r
        JOIN   users        u ON r.user_id = u.id
        JOIN   laboratories l ON r.lab_id  = l.id
        WHERE  r.status = 'Approved'
        ORDER  BY r.date DESC
        LIMIT  50
    ");
    while ($row = $res->fetch_assoc()) $usage[] = $row;
}

include("../includes/header.php");
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Lab Usage</h1>
        <p class="page-subtitle">Approved reservations across all labs</p>
    </div>
    <span class="badge badge-green" style="font-size:.8rem; padding:6px 14px; font-family:var(--font-mono);">
        <?php echo count($usage); ?> approved
    </span>
</div>

<!-- Search -->
<div class="filter-bar">
    <input type="text" id="searchLab" class="form-control"
           placeholder="Search by lab or student..."
           style="min-width:260px;">
    <span class="filter-summary">Showing up to 50 most recent</span>
</div>

<!-- Table -->
<div class="table-wrap">
    <table class="sp-table" id="usageTable">
        <thead>
            <tr>
                <th style="width:44px; text-align:center;">#</th>
                <th>Student</th>
                <th>Lab</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th style="width:110px; text-align:center;">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($usage)): ?>
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <span class="empty-state-icon">🔬</span>
                        <p class="empty-state-text">No approved lab usage records found.</p>
                    </div>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($usage as $n => $u): ?>
            <tr>
                <td style="text-align:center;" class="td-mono"><?php echo $n + 1; ?></td>
                <td style="font-weight:600; color:var(--ink-800);">
                    <?php echo htmlspecialchars($u['student_name'] ?? '—'); ?>
                </td>
                <td><?php echo htmlspecialchars($u['lab_name']  ?? '—'); ?></td>
                <td class="td-mono">
                    <?php echo isset($u['date']) ? date("M d, Y", strtotime($u['date'])) : '—'; ?>
                </td>
                <td class="td-mono"><?php echo htmlspecialchars($u['time_slot'] ?? '—'); ?></td>
                <td style="text-align:center;">
                    <span class="badge badge-green">Approved</span>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('searchLab').addEventListener('input', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#usageTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>