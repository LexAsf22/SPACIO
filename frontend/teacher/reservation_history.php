<?php
// frontend/teacher/reservation_history.php
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
checkRole('teacher');

$result       = djangoGet('/api/v1/reservations/history/');
$reservations = [];

if ($result['success'] && isset($result['data'])) {
    $reservations = $result['data'];
}

include("../includes/header.php");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Reservation History</h1>
        <p class="page-subtitle">All student lab and equipment reservations.</p>
    </div>
    <button onclick="printHistory()" class="btn btn-secondary">🖨️ Print</button>
</div>

<div class="filter-bar">
    <input type="text" id="searchRes" class="form-control" placeholder="Search student, lab..." style="min-width:220px;">
    <select id="typeFilter" class="form-control" style="width:160px;">
        <option value="all">All Types</option>
        <option value="lab">Lab</option>
        <option value="equipment">Equipment</option>
    </select>
    <select id="statusFilter" class="form-control" style="width:160px;">
        <option value="all">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
    </select>
    <span class="filter-summary"><?php echo count($reservations); ?> record(s)</span>
</div>

<div id="printArea">
<div class="print-header" style="display:none;">
    <h2>Spacio — Reservation History</h2>
    <p>Generated: <?php echo date('F d, Y h:i A'); ?></p>
</div>

<div class="table-wrap">
    <table class="sp-table" id="historyTable">
        <thead>
            <tr>
                <th style="width:44px; text-align:center;">#</th>
                <th>Student</th>
                <th>Type</th>
                <th>Name</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($reservations)): ?>
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <div class="empty-state-text">No reservations on record.</div>
                    </div>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($reservations as $i => $r):
                $isLab  = !empty($r['lab_id']);
                $type   = $isLab ? 'Lab' : 'Equipment';
                $name   = $isLab ? ($r['lab_name'] ?? '—') : ($r['equipment_name'] ?? '—');
                $status = $r['status'] ?? 'Pending';
                $badge  = match(strtolower($status)) {
                    'approved' => 'badge-green',
                    'rejected' => 'badge-red',
                    default    => 'badge-yellow',
                };
            ?>
            <tr data-type="<?php echo strtolower($type); ?>" data-status="<?php echo strtolower($status); ?>">
                <td style="text-align:center;" class="td-mono"><?php echo $i + 1; ?></td>
                <td style="font-weight:600; color:var(--ink-800);"><?php echo htmlspecialchars($r['student_name'] ?? '—'); ?></td>
                <td><span class="badge <?php echo $isLab ? 'badge-blue' : 'badge-gray'; ?>"><?php echo $type; ?></span></td>
                <td style="font-weight:500;"><?php echo htmlspecialchars($name); ?></td>
                <td class="td-mono"><?php echo isset($r['date']) ? date("M d, Y", strtotime($r['date'])) : '—'; ?></td>
                <td class="td-mono"><?php echo htmlspecialchars($r['time_slot'] ?? '—'); ?></td>
                <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                <td class="td-mono"><?php echo isset($r['created_at']) ? date("M d, Y", strtotime($r['created_at'])) : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<div style="margin-top:16px; display:flex; gap:10px;">
    <button onclick="downloadPDF()" class="btn btn-primary">⬇️ Download PDF</button>
    <button onclick="printHistory()" class="btn btn-secondary">🖨️ Print</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script>
function filterTable() {
    const search = document.getElementById('searchRes').value.toLowerCase();
    const type   = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;
    document.querySelectorAll('#historyTable tbody tr').forEach(row => {
        const matchSearch = row.textContent.toLowerCase().includes(search);
        const matchType   = type   === 'all' || row.dataset.type   === type;
        const matchStatus = status === 'all' || row.dataset.status === status;
        row.style.display = matchSearch && matchType && matchStatus ? '' : 'none';
    });
}
document.getElementById('searchRes').addEventListener('keyup', filterTable);
document.getElementById('typeFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function printHistory() {
    document.querySelector('.print-header').style.display = 'block';
    window.print();
    document.querySelector('.print-header').style.display = 'none';
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFontSize(14);
    doc.text('Spacio — Reservation History', 14, 15);
    doc.setFontSize(9);
    doc.text('Generated: <?php echo date('F d, Y h:i A'); ?>', 14, 22);

    const rows = [];
    document.querySelectorAll('#historyTable tbody tr').forEach((tr, i) => {
        if (tr.style.display === 'none') return;
        const cells = tr.querySelectorAll('td');
        if (cells.length < 8) return;
        rows.push([
            i + 1,
            cells[1].textContent.trim(),
            cells[2].textContent.trim(),
            cells[3].textContent.trim(),
            cells[4].textContent.trim(),
            cells[5].textContent.trim(),
            cells[6].textContent.trim(),
            cells[7].textContent.trim(),
        ]);
    });

    doc.autoTable({
        startY: 27,
        head: [['#','Student','Type','Name','Date','Time Slot','Status','Submitted']],
        body: rows,
        styles: { fontSize: 8 },
        headStyles: { fillColor: [44, 95, 45] },
    });

    doc.save('reservation_history.pdf');
}
</script>

<style>
@media print {
    .page-header button,
    .filter-bar,
    div[style*="margin-top:16px"],
    nav, aside, header { display: none !important; }
    .print-header { display: block !important; }
}
</style>

<?php include("../includes/footer.php"); ?>