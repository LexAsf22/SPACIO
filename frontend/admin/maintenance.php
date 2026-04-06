<?php
// frontend/admin/maintenance.php
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
checkRole('admin');

// ── Status update ─────────────────────────────────────────────────────────────
$allowed_statuses = ['In Progress', 'Done'];

if (isset($_GET['status'], $_GET['id'])) {
    $id     = (int)    $_GET['id'];
    $status =           $_GET['status'];

    if (!in_array($status, $allowed_statuses, true)) {
        setFlash("Invalid status value.", "error");
    } else {
        $result = djangoPost('/api/v1/issues/' . $id . '/status/', [
            'status' => $status,
        ]);

        if ($result['success']) {
            setFlash("Issue status updated!", "success");
        } else {
            $msg = $result['data']['detail'] ?? $result['data']['message'] ?? 'Update failed.';
            setFlash($msg, "error");
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ── Fetch issues from Django ──────────────────────────────────────────────────
$result = djangoGet('/api/v1/issues/');
$issues = [];

if ($result['success'] && isset($result['data'])) {
    $issues = $result['data']['results'] ?? $result['data'] ?? [];
}

include("../includes/header.php");
?>

<style>
    .maint-wrap           { max-width:960px; font-size:.9rem; }

    .maint-table          { width:100%; border-collapse:collapse; font-size:.85rem; margin-bottom:10px; }
    .maint-table thead th {
        background:#2c5f2e; color:#fff;
        padding:8px 10px; text-align:left;
        font-size:.75rem; text-transform:uppercase;
        letter-spacing:.04em; white-space:nowrap;
    }
    .maint-table tbody td  { padding:8px 10px; border-bottom:1px solid #eee; vertical-align:top; }
    .maint-table tbody tr:hover { background:#f7f7f7; }

    .desc-text            { font-size:.82rem; color:#555; font-style:italic; line-height:1.4; }
    .desc-none            { font-size:.78rem; color:#bbb; font-style:italic; }

    .badge                { padding:2px 8px; border-radius:10px; font-size:.74rem; font-weight:600; display:inline-block; white-space:nowrap; }
    .badge-yellow         { background:#fff3cd; color:#856404; }
    .badge-blue           { background:#cce5ff; color:#004085; }
    .badge-green          { background:#d4edda; color:#155724; }

    .priority-low         { color:#28a745; font-weight:700; font-size:.8rem; }
    .priority-medium      { color:#f39c12; font-weight:700; font-size:.8rem; }
    .priority-high        { color:#e74c3c; font-weight:700; font-size:.8rem; }

    .btn-inprogress       { display:inline-block; padding:4px 10px; background:#f39c12; color:#fff; border-radius:3px; text-decoration:none; font-size:.76rem; white-space:nowrap; }
    .btn-inprogress:hover { background:#d68910; color:#fff; }
    .btn-done             { display:inline-block; padding:4px 10px; background:#28a745; color:#fff; border-radius:3px; text-decoration:none; font-size:.76rem; white-space:nowrap; }
    .btn-done:hover       { background:#1e7e34; color:#fff; }
    .btn-resolved         { font-size:.78rem; color:#888; font-style:italic; }

    .search-bar           { display:flex; gap:8px; align-items:center; margin-bottom:12px; flex-wrap:wrap; }
    .search-bar input     { padding:7px 10px; border:1px solid #ccc; border-radius:4px; font-size:.85rem; width:220px; }
    .search-bar select    { padding:7px 10px; border:1px solid #ccc; border-radius:4px; font-size:.85rem; }
</style>

<div class="maint-wrap">

<h2 style="margin:0 0 14px; font-size:1.2rem;">Maintenance Requests</h2>
<?php echo getFlash(); ?>

<!-- SEARCH & FILTER -->
<div class="search-bar">
    <input type="text" id="searchIssues" placeholder="Search teacher, room, category...">
    <select id="filterStatus">
        <option value="">All Statuses</option>
        <option value="Pending">Pending</option>
        <option value="In Progress">In Progress</option>
        <option value="Done">Done</option>
    </select>
    <select id="filterPriority">
        <option value="">All Priorities</option>
        <option value="High">High</option>
        <option value="Medium">Medium</option>
        <option value="Low">Low</option>
    </select>
</div>

<?php if (empty($issues)): ?>
    <div style="
        background:#d4edda; color:#155724;
        border:1px solid #c3e6cb;
        padding:14px 16px; border-radius:6px;
    ">
        ✓ No maintenance requests at this time.
    </div>
<?php else: ?>

<table class="maint-table" id="issuesTable">
    <thead>
        <tr>
            <th>Teacher</th>
            <th>Campus / Room</th>
            <th>Category</th>
            <th>Description</th>
            <th style="width:70px;">Priority</th>
            <th style="width:90px;">Status</th>
            <th style="width:140px;">Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($issues as $i):
        $badge = match($i['status'] ?? 'Pending') {
            'Pending'     => 'badge-yellow',
            'In Progress' => 'badge-blue',
            'Done'        => 'badge-green',
            default       => 'badge-yellow',
        };

        $pclass = match($i['priority'] ?? '') {
            'High'   => 'priority-high',
            'Medium' => 'priority-medium',
            'Low'    => 'priority-low',
            default  => '',
        };

        $url_progress = '?' . http_build_query(['id' => $i['id'], 'status' => 'In Progress']);
        $url_done     = '?' . http_build_query(['id' => $i['id'], 'status' => 'Done']);
    ?>
    <tr>
        <td>
            <?php echo htmlspecialchars($i['teacher_name'] ?? '—'); ?>
            <div style="font-size:.76rem; color:#999; margin-top:2px;">
                <?php echo isset($i['created_at']) ? date("M d, Y", strtotime($i['created_at'])) : '—'; ?>
            </div>
        </td>

        <td>
            <?php echo htmlspecialchars($i['campus'] ?? '—'); ?>
            <div style="font-size:.8rem; color:#666; margin-top:2px;">
                Room: <?php echo htmlspecialchars($i['room'] ?? '—'); ?>
            </div>
        </td>

        <td><?php echo htmlspecialchars($i['category'] ?? '—'); ?></td>

        <td style="max-width:220px;">
            <?php if (!empty($i['description'])): ?>
                <div class="desc-text">
                    <?php echo nl2br(htmlspecialchars($i['description'])); ?>
                </div>
            <?php else: ?>
                <span class="desc-none">No description provided.</span>
            <?php endif; ?>
        </td>

        <td>
            <span class="<?php echo $pclass; ?>">
                <?php echo htmlspecialchars($i['priority'] ?? '—'); ?>
            </span>
        </td>

        <td>
            <span class="badge <?php echo $badge; ?>">
                <?php echo htmlspecialchars($i['status'] ?? 'Pending'); ?>
            </span>
        </td>

        <td>
            <?php if (($i['status'] ?? '') === 'Done'): ?>
                <span class="btn-resolved">✓ Resolved</span>

            <?php elseif (($i['status'] ?? '') === 'In Progress'): ?>
                <a href="<?php echo $url_done; ?>" class="btn-done statusBtn">
                    Mark Done
                </a>

            <?php else: ?>
                <a href="<?php echo $url_progress; ?>" class="btn-inprogress statusBtn">
                    In Progress
                </a>
                &nbsp;
                <a href="<?php echo $url_done; ?>" class="btn-done statusBtn">
                    Mark Done
                </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>
</div>

<script>
document.querySelectorAll('.statusBtn').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm("Update this issue's status?")) e.preventDefault();
    });
});

const searchInput    = document.getElementById('searchIssues');
const filterStatus   = document.getElementById('filterStatus');
const filterPriority = document.getElementById('filterPriority');

function applyFilters() {
    const search   = searchInput.value.toLowerCase();
    const status   = filterStatus.value.toLowerCase();
    const priority = filterPriority.value.toLowerCase();

    document.querySelectorAll('#issuesTable tbody tr').forEach(row => {
        const text    = row.textContent.toLowerCase();
        const rowStat = row.querySelector('td:nth-child(6)')?.textContent.trim().toLowerCase() ?? '';
        const rowPri  = row.querySelector('td:nth-child(5)')?.textContent.trim().toLowerCase() ?? '';

        const matchSearch   = search   === '' || text.includes(search);
        const matchStatus   = status   === '' || rowStat.includes(status);
        const matchPriority = priority === '' || rowPri.includes(priority);

        row.style.display = (matchSearch && matchStatus && matchPriority) ? '' : 'none';
    });
}

searchInput.addEventListener('keyup',    applyFilters);
filterStatus.addEventListener('change',  applyFilters);
filterPriority.addEventListener('change', applyFilters);
</script>

<?php include("../includes/footer.php"); ?>