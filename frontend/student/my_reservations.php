<?php
// frontend/student/my_reservations.php
include("../includes/header.php");
checkRole('student');

$user_id = (int) $_SESSION['user']['id'];

$result       = djangoGet('/api/v1/reservations/my/?user_id=' . $user_id);
$reservations = [];

if ($result['success'] && isset($result['data'])) {
    $reservations = $result['data']['results'] ?? $result['data'] ?? [];
}

$total     = count($reservations);
$labs      = array_filter($reservations, fn($r) => !empty($r['lab_id']));
$equipment = array_filter($reservations, fn($r) => empty($r['lab_id']));
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">My Reservations</h1>
        <p class="page-subtitle">All your lab and equipment bookings in one place.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="/spacio/frontend/student/reserve_lab.php" class="btn btn-secondary">+ Reserve Lab</a>
        <a href="/spacio/frontend/student/reserve_equipment.php" class="btn btn-primary">+ Reserve Equipment</a>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon blue">📋</div>
        <div class="stat-body">
            <div class="stat-label">Total</div>
            <div class="stat-value"><?php echo $total; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">🔬</div>
        <div class="stat-body">
            <div class="stat-label">Lab Reservations</div>
            <div class="stat-value"><?php echo count($labs); ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">🖥️</div>
        <div class="stat-body">
            <div class="stat-label">Equipment Reservations</div>
            <div class="stat-value"><?php echo count($equipment); ?></div>
        </div>
    </div>
</div>

<!-- Reservations Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">All Reservations</span>
        <div class="filter-bar" style="margin:0;">
            <input type="text" id="searchRes" class="form-control" placeholder="Search..." style="width:180px;">
            <select id="typeFilter" class="form-control" style="width:160px;">
                <option value="all">All Types</option>
                <option value="lab">Lab Only</option>
                <option value="equipment">Equipment Only</option>
            </select>
            <select id="statusFilter" class="form-control" style="width:160px;">
                <option value="all">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <div class="empty-state-text">
                No reservations yet.
                <a href="/spacio/frontend/student/reserve_lab.php">Reserve a lab</a> or
                <a href="/spacio/frontend/student/reserve_equipment.php">reserve equipment</a>.
            </div>
        </div>
    <?php else: ?>
        <div class="table-wrap" style="border:none; border-radius:0; box-shadow:none;">
            <table class="sp-table" id="myResTable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reservations as $r):
                    $isLab  = !empty($r['lab_id']);
                    $type   = $isLab ? 'Lab' : 'Equipment';
                    $name   = $isLab ? ($r['lab_name'] ?? '—') : ($r['equipment_name'] ?? '—');
                    $status = $r['status'] ?? 'Pending';
                    $badge  = match(strtolower($status)) {
                        'approved' => 'badge-green',
                        'rejected' => 'badge-red',
                        default    => 'badge-yellow',
                    };
                    $typeBadge = $isLab ? 'badge-blue' : 'badge-gray';
                ?>
                    <tr data-type="<?php echo strtolower($type); ?>" data-status="<?php echo strtolower($status); ?>">
                        <td><span class="badge <?php echo $typeBadge; ?>"><?php echo $type; ?></span></td>
                        <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                        <td class="td-mono"><?php echo isset($r['date']) ? date("F d, Y", strtotime($r['date'])) : '—'; ?></td>
                        <td class="td-mono"><?php echo htmlspecialchars($r['time_slot'] ?? '—'); ?></td>
                        <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function filterTable() {
    const search = document.getElementById('searchRes').value.toLowerCase();
    const type   = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;

    document.querySelectorAll('#myResTable tbody tr').forEach(row => {
        const matchSearch = row.textContent.toLowerCase().includes(search);
        const matchType   = type   === 'all' || row.dataset.type   === type;
        const matchStatus = status === 'all' || row.dataset.status === status;
        row.style.display = matchSearch && matchType && matchStatus ? '' : 'none';
    });
}

document.getElementById('searchRes')?.addEventListener('keyup', filterTable);
document.getElementById('typeFilter')?.addEventListener('change', filterTable);
document.getElementById('statusFilter')?.addEventListener('change', filterTable);
</script>

<?php include("../includes/footer.php"); ?>