<?php
// frontend/admin/approvals.php
include("../includes/header.php");
checkRole('admin');

// ── Approve ───────────────────────────────────────────────────────────────────
if (isset($_GET['approve'])) {
    $id     = (int) $_GET['approve'];
    $result = djangoPost('/api/v1/admin/approvals/' . $id . '/', ['action' => 'approve']);

    if ($result['success']) {
        setFlash("Reservation approved successfully!", "success");
    } else {
        $msg = $result['data']['detail'] ?? $result['data']['message'] ?? 'Approval failed. Please try again.';
        setFlash($msg, "error");
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ── Reject ────────────────────────────────────────────────────────────────────
if (isset($_GET['reject'])) {
    $id     = (int) $_GET['reject'];
    $result = djangoPost('/api/v1/admin/approvals/' . $id . '/', ['action' => 'reject']);

    if ($result['success']) {
        setFlash("Reservation rejected.", "error");
    } else {
        $msg = $result['data']['detail'] ?? $result['data']['message'] ?? 'Rejection failed. Please try again.';
        setFlash($msg, "error");
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ── Fetch pending reservations from Django ────────────────────────────────────
$result       = djangoGet('/api/v1/admin/approvals/');
$reservations = [];

if ($result['success'] && isset($result['data'])) {
    $reservations = $result['data'];
}

$pending_count = count($reservations);
?>

<h2>Pending Reservations
    <span style="
        background:#f39c12; color:white;
        padding:3px 10px; border-radius:12px;
        font-size:.85rem; vertical-align:middle;
    ">
        <?php echo $pending_count; ?> pending
    </span>
</h2>

<?php echo getFlash(); ?>

<?php if ($pending_count === 0): ?>
    <div style="
        background:#d4edda; color:#155724;
        border:1px solid #c3e6cb;
        padding:16px; border-radius:8px; margin-top:20px;
    ">
        ✓ No pending reservations. All caught up!
    </div>
<?php else: ?>

    <input
        type="text"
        id="searchInput"
        placeholder="Search by student, lab, or equipment..."
        style="padding:10px; margin:10px 0; width:50%; border:1px solid #ccc; border-radius:5px;"
    >

    <table id="approvalTable" border="1" cellpadding="10" cellspacing="0"
           style="border-collapse:collapse; width:100%; margin-top:10px;">
        <thead style="background:#2c5f2e; color:white;">
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Type</th>
                <th>Name</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $row):
            $isLab = !empty($row['lab_id']);
            $type  = $isLab ? 'Lab' : 'Equipment';
            $name  = $isLab
                ? ($row['lab_name']       ?? '—')
                : ($row['equipment_name'] ?? '—');
        ?>
            <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['student_name'] ?? '—'); ?></td>
                <td><?php echo $type; ?></td>
                <td><?php echo htmlspecialchars($name); ?></td>
                <td><?php echo date("F d, Y", strtotime($row['date'] ?? 'now')); ?></td>
                <td><?php echo htmlspecialchars($row['time_slot'] ?? '—'); ?></td>
                <td style="white-space:nowrap;">
                    <a
                        href="?approve=<?php echo (int) $row['id']; ?>"
                        class="approveBtn"
                        style="
                            background:#28a745; color:white;
                            padding:6px 14px; border-radius:4px;
                            text-decoration:none; font-size:.85rem;
                            margin-right:6px;
                        "
                    >✓ Approve</a>
                    <a
                        href="?reject=<?php echo (int) $row['id']; ?>"
                        class="rejectBtn"
                        style="
                            background:#e74c3c; color:white;
                            padding:6px 14px; border-radius:4px;
                            text-decoration:none; font-size:.85rem;
                        "
                    >✗ Reject</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<script>
document.querySelectorAll('.approveBtn').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm("Approve this reservation?")) e.preventDefault();
    });
});

document.querySelectorAll('.rejectBtn').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm("Reject this reservation?")) e.preventDefault();
    });
});

document.getElementById('searchInput')?.addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#approvalTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>