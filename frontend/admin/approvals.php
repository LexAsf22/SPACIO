<?php
// frontend/admin/approvals.php
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
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

// ── Fetch pending reservations ────────────────────────────────────────────────
$result       = djangoGet('/api/v1/admin/approvals/');
$reservations = [];
if ($result['success'] && isset($result['data'])) {
    $reservations = $result['data'];
}
$pending_count = count($reservations);

include("../includes/header.php");
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Pending Approvals</h1>
        <p class="page-subtitle">Review and action reservation requests</p>
    </div>
    <?php if ($pending_count > 0): ?>
        <span class="badge badge-yellow" style="font-size:.8rem; padding:6px 14px;">
            <?php echo $pending_count; ?> pending
        </span>
    <?php endif; ?>
</div>

<?php echo getFlash(); ?>

<?php if ($pending_count === 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <span class="empty-state-icon">✓</span>
                <p class="empty-state-text">All caught up — no pending reservations.</p>
            </div>
        </div>
    </div>
<?php else: ?>

    <!-- Search -->
    <div class="filter-bar">
        <input
            type="text"
            id="searchInput"
            class="form-control"
            placeholder="Search by student, lab, or equipment..."
            style="min-width:260px;"
        >
        <span class="filter-summary"><?php echo $pending_count; ?> request(s) awaiting review</span>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table class="sp-table" id="approvalTable">
            <thead>
                <tr>
                    <th style="width:44px; text-align:center;">#</th>
                    <th>Student</th>
                    <th>Type</th>
                    <th>Resource</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th style="text-align:center; width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($reservations as $i => $row):
                $isLab = !empty($row['lab_id']);
                $type  = $isLab ? 'Lab' : 'Equipment';
                $name  = $isLab
                    ? ($row['lab_name']       ?? '—')
                    : ($row['equipment_name'] ?? '—');
            ?>
                <tr>
                    <td style="text-align:center;" class="td-mono"><?php echo (int)$row['id']; ?></td>
                    <td>
                        <div style="font-weight:600; color:var(--ink-800);">
                            <?php echo htmlspecialchars($row['student_name'] ?? '—'); ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?php echo $isLab ? 'badge-blue' : 'badge-gray'; ?>">
                            <?php echo $type; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($name); ?></td>
                    <td class="td-mono"><?php echo date("M d, Y", strtotime($row['date'] ?? 'now')); ?></td>
                    <td class="td-mono"><?php echo htmlspecialchars($row['time_slot'] ?? '—'); ?></td>
                    <td style="text-align:center; white-space:nowrap;">
                        <div style="display:flex; gap:6px; justify-content:center;">
                            <a href="?approve=<?php echo (int)$row['id']; ?>"
                               class="btn btn-approve btn-sm approveBtn">
                                ✓ Approve
                            </a>
                            <a href="?reject=<?php echo (int)$row['id']; ?>"
                               class="btn btn-reject btn-sm rejectBtn">
                                ✗ Reject
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

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

document.getElementById('searchInput')?.addEventListener('input', function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#approvalTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>