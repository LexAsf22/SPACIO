<?php
// frontend/student/view_computers.php
include("../includes/header.php");
checkRole('student');

// ── Fetch computers from Django ───────────────────────────────────────────────
$result    = djangoGet('/api/v1/labs/computers/');
$computers = [];

if ($result['success'] && isset($result['data'])) {
    $computers = $result['data']['results'] ?? $result['data'] ?? [];
}
?>

<h2>Available Computers</h2>

<input type="text" id="searchComp" placeholder="Search by lab or computer..."
       style="padding:10px; margin:10px 0; width:50%;">

<table id="compTable" border="1" cellpadding="10" cellspacing="0"
       style="border-collapse:collapse; width:100%;">
    <thead>
        <tr>
            <th>Lab</th>
            <th>Computer Name / ID</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($computers)): ?>
        <tr>
            <td colspan="3" style="text-align:center; color:#888; font-style:italic;">
                No computers on record.
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($computers as $c): ?>
        <tr>
            <td><?php echo htmlspecialchars($c['lab_name']      ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($c['computer_name'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($c['status']        ?? '—'); ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<script>
document.getElementById('searchComp').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#compTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>