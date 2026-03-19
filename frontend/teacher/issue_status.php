<?php
// frontend/teacher/issue_status.php
include("../includes/header.php");
checkRole('teacher');

$user_id = (int) $_SESSION['user']['id'];

// ── Fetch this teacher's issues from Django ───────────────────────────────────
$result = djangoGet('/api/v1/issues/?user_id=' . $user_id . '&ordering=-created_at');
$issues = [];

if ($result['success'] && isset($result['data'])) {
    $issues = $result['data']['results'] ?? $result['data'] ?? [];
} else {
    // Fallback: query DB directly if Django is unreachable
    $res = $conn->query(
        "SELECT * FROM issues WHERE user_id={$user_id} ORDER BY created_at DESC"
    );
    while ($i = $res->fetch_assoc()) {
        $issues[] = $i;
    }
}
?>

<h2>My Reported Issues</h2>

<input type="text" id="searchIssues" placeholder="Search issues..."
       style="padding:10px; margin:10px 0; width:50%;">

<table id="issueTable" border="1" cellpadding="10" cellspacing="0"
       style="border-collapse:collapse; width:100%;">
    <tr>
        <th>ID</th>
        <th>Campus</th>
        <th>Room</th>
        <th>Category</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Reported At</th>
    </tr>

    <?php if (empty($issues)): ?>
    <tr>
        <td colspan="7" style="text-align:center; color:#888; font-style:italic; padding:16px;">
            No issues reported yet.
        </td>
    </tr>
    <?php else: ?>
    <?php foreach ($issues as $i): ?>
    <tr>
        <td><?php echo (int)                                     $i['id']; ?></td>
        <td><?php echo htmlspecialchars($i['campus']     ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($i['room']       ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($i['category']   ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($i['priority']   ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($i['status']     ?? '—'); ?></td>
        <td>
            <?php echo isset($i['created_at'])
                ? date("F d, Y H:i", strtotime($i['created_at']))
                : '—';
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

<script>
document.getElementById('searchIssues').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#issueTable tr:not(:first-child)').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>