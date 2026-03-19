<?php
// frontend/teacher/lab_usage.php
include("../includes/header.php");
checkRole('teacher');

// ── Fetch approved lab usage from Django ──────────────────────────────────────
$result = djangoGet('/api/v1/reports/usage/?status=Approved&limit=50&ordering=-date');
$usage  = [];

if ($result['success'] && isset($result['data'])) {
    $usage = $result['data']['results'] ?? $result['data'] ?? [];
} else {
    // Fallback: query DB directly if Django is unreachable
    $res = $conn->query("
        SELECT r.*, u.name AS student_name, l.lab_name
        FROM   reservations r
        JOIN   users        u ON r.user_id = u.id
        JOIN   laboratories l ON r.lab_id  = l.id
        WHERE  r.status = 'Approved'
        ORDER  BY r.date DESC
        LIMIT  50
    ");
    while ($row = $res->fetch_assoc()) {
        $usage[] = $row;
    }
}
?>

<h2>Lab Usage Monitoring</h2>

<input type="text" id="searchLab" placeholder="Search by lab or student..."
       style="padding:10px; margin:10px 0; width:50%;">

<table id="usageTable" border="1" cellpadding="10" cellspacing="0"
       style="border-collapse:collapse; width:100%;">
    <tr>
        <th>Student</th>
        <th>Lab</th>
        <th>Date</th>
        <th>Time Slot</th>
    </tr>

    <?php if (empty($usage)): ?>
    <tr>
        <td colspan="4" style="text-align:center; color:#888; font-style:italic; padding:16px;">
            No approved lab usage records found.
        </td>
    </tr>
    <?php else: ?>
    <?php foreach ($usage as $u): ?>
    <tr>
        <td><?php echo htmlspecialchars($u['student_name'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($u['lab_name']     ?? '—'); ?></td>
        <td><?php echo isset($u['date']) ? date("F d, Y", strtotime($u['date'])) : '—'; ?></td>
        <td><?php echo htmlspecialchars($u['time_slot']    ?? '—'); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

<script>
document.getElementById('searchLab').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#usageTable tr:not(:first-child)').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>