<?php
// frontend/admin/inventory.php
include("../includes/header.php");
checkRole('admin');

// ── Add equipment ─────────────────────────────────────────────────────────────
if (isset($_POST['add'])) {
    $lab_id = (int)  $_POST['lab'];
    $name   = trim(  $_POST['equipment_name'] ?? '');
    $qty    = (int)  $_POST['quantity'];

    if ($name === '' || $lab_id === 0 || $qty < 1) {
        setFlash("Please fill in all fields correctly.", "error");
    } else {
        $result = djangoPost('/api/v1/admin/inventory/', [
            'equipment_name' => $name,
            'lab_id'         => $lab_id,
            'quantity'       => $qty,
            'status'         => 'Available',
        ]);

        if ($result['success']) {
            setFlash("Equipment added successfully!", "success");
        } else {
            $msg = $result['data']['detail'] ?? $result['data']['message'] ?? 'Failed to add equipment.';
            setFlash($msg, "error");
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ── Delete equipment ──────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id     = (int) $_GET['delete'];
    $result = djangoPut('/api/v1/admin/inventory/' . $id . '/', ['deleted' => true]);

    // Note: use djangoPost with a DELETE method wrapper if your Django endpoint uses DELETE.
    // For now this sends a soft-delete flag. Adjust to match your Django endpoint design.
    if ($result['success']) {
        setFlash("Equipment deleted.", "error");
    } else {
        $msg = $result['data']['detail'] ?? $result['data']['message'] ?? 'Delete failed.';
        setFlash($msg, "error");
    }

    $qs = http_build_query([
        'page'       => $_GET['page']       ?? 1,
        'search'     => $_GET['search']     ?? '',
        'lab_filter' => $_GET['lab_filter'] ?? 0,
    ]);
    header("Location: " . $_SERVER['PHP_SELF'] . '?' . $qs);
    exit;
}

// ── Fetch equipment list from Django ──────────────────────────────────────────
$per_page   = 10;
$page       = max(1, (int) ($_GET['page']       ?? 1));
$search     = trim(         $_GET['search']     ?? '');
$filter_lab = (int)         ($_GET['lab_filter'] ?? 0);

$endpoint = '/api/v1/admin/inventory/'
    . '?page='       . $page
    . '&search='     . urlencode($search)
    . '&lab_filter=' . $filter_lab
    . '&per_page='   . $per_page;

$result    = djangoGet($endpoint);
$equipment = [];
$total_rows  = 0;
$total_pages = 1;

if ($result['success'] && isset($result['data'])) {
    $equipment   = $result['data']['results']     ?? $result['data'] ?? [];
    $total_rows  = $result['data']['count']        ?? count($equipment);
    $total_pages = $result['data']['total_pages']  ?? max(1, ceil($total_rows / $per_page));
}

$page   = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

// ── Fetch labs for the Add form dropdown ──────────────────────────────────────
$labsResult = djangoGet('/api/v1/labs/');
$labs       = ($labsResult['success'] && isset($labsResult['data']))
    ? $labsResult['data']
    : [];

function pageUrl(int $p): string {
    $q         = $_GET;
    $q['page'] = $p;
    unset($q['delete']);
    return '?' . http_build_query($q);
}
?>

<style>
    .inv-wrap            { max-width: 860px; font-size: .9rem; }
    .inv-form            { background:#f9f9f9; border:1px solid #ddd; border-radius:6px; padding:14px 16px; margin-bottom:16px; }
    .inv-form h3         { margin:0 0 10px; font-size:.93rem; color:#2c5f2e; }
    .inv-grid            { display:grid; grid-template-columns:1fr 1fr 100px; gap:10px; align-items:end; }
    .inv-field label     { display:block; font-size:.72rem; font-weight:700; color:#555; margin-bottom:3px; text-transform:uppercase; letter-spacing:.04em; }
    .inv-field select,
    .inv-field input     { width:100%; padding:7px 9px; border:1px solid #ccc; border-radius:4px; font-size:.88rem; box-sizing:border-box; }

    .filter-bar          { display:flex; gap:7px; align-items:center; flex-wrap:wrap; margin-bottom:10px; }
    .filter-bar input    { padding:7px 10px; border:1px solid #ccc; border-radius:4px; font-size:.85rem; width:200px; }
    .filter-bar select   { padding:7px 10px; border:1px solid #ccc; border-radius:4px; font-size:.85rem; }

    .btn-green           { padding:7px 16px; background:#2c5f2e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:.85rem; }
    .btn-green:hover     { background:#1e3d1a; }
    .btn-search          { padding:7px 14px; background:#2c5f2e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:.85rem; }
    .btn-clear           { padding:7px 11px; background:#e74c3c; color:#fff; border-radius:4px; text-decoration:none; font-size:.82rem; }
    .summary             { margin-left:auto; font-size:.8rem; color:#777; }

    .inv-table           { width:100%; border-collapse:collapse; font-size:.85rem; margin-bottom:10px; }
    .inv-table thead th  { background:#2c5f2e; color:#fff; padding:8px 10px; text-align:left; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
    .inv-table tbody td  { padding:7px 10px; border-bottom:1px solid #eee; vertical-align:middle; }
    .inv-table tbody tr:hover { background:#f7f7f7; }

    .badge               { padding:2px 8px; border-radius:10px; font-size:.74rem; font-weight:600; display:inline-block; white-space:nowrap; }
    .badge-green         { background:#d4edda; color:#155724; }
    .badge-yellow        { background:#fff3cd; color:#856404; }
    .badge-red           { background:#f8d7da; color:#721c24; }
    .badge-gray          { background:#e2e3e5; color:#383d41; }

    .btn-delete          { display:inline-block; padding:3px 9px; background:#e74c3c; color:#fff; border-radius:3px; text-decoration:none; font-size:.76rem; }
    .btn-delete:hover    { background:#c0392b; color:#fff; }

    .pagination          { display:flex; gap:4px; align-items:center; flex-wrap:wrap; margin-top:4px; }
    .pagination a        { padding:5px 10px; border:1px solid #ccc; border-radius:4px; text-decoration:none; color:#333; font-size:.82rem; }
    .pagination a:hover  { background:#f0f0f0; }
    .pagination a.active { background:#2c5f2e; color:#fff; border-color:#2c5f2e; font-weight:700; }
    .pagination .pg-disabled { padding:5px 10px; border:1px solid #eee; border-radius:4px; color:#ccc; font-size:.82rem; cursor:default; user-select:none; }
    .pg-info             { font-size:.8rem; color:#777; margin-left:4px; }
</style>

<div class="inv-wrap">

<h2 style="margin:0 0 14px; font-size:1.2rem;">Inventory Management</h2>
<?php echo getFlash(); ?>

<!-- ADD FORM -->
<div class="inv-form">
    <h3>+ Add Equipment</h3>
    <form method="POST">
        <div class="inv-grid">
            <div class="inv-field">
                <label>Lab</label>
                <select name="lab" required>
                    <option value="">-- Select Lab --</option>
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?php echo (int) $lab['id']; ?>">
                            <?php echo htmlspecialchars($lab['lab_name'] ?? $lab['name'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="inv-field">
                <label>Equipment Name</label>
                <input type="text" name="equipment_name" placeholder="e.g. Microscope" required>
            </div>
            <div class="inv-field">
                <label>Quantity</label>
                <input type="number" name="quantity" placeholder="0" min="1" required>
            </div>
        </div>
        <div style="margin-top:10px;">
            <button name="add" class="btn-green">Add Equipment</button>
        </div>
    </form>
</div>

<!-- SEARCH & FILTER -->
<form method="GET" class="filter-bar">
    <input
        type="text"
        name="search"
        value="<?php echo htmlspecialchars($search); ?>"
        placeholder="Search name, lab, status..."
    >
    <select name="lab_filter">
        <option value="">All Labs</option>
        <?php foreach ($labs as $lab):
            $sel = ($filter_lab === (int) $lab['id']) ? 'selected' : '';
        ?>
            <option value="<?php echo (int) $lab['id']; ?>" <?php echo $sel; ?>>
                <?php echo htmlspecialchars($lab['lab_name'] ?? $lab['name'] ?? ''); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-search">Search</button>
    <?php if ($search !== '' || $filter_lab > 0): ?>
        <a href="?" class="btn-clear">✕ Clear</a>
    <?php endif; ?>
    <span class="summary">
        Showing <?php echo min($offset + 1, $total_rows); ?>–<?php echo min($offset + $per_page, $total_rows); ?>
        of <?php echo $total_rows; ?> items
    </span>
</form>

<!-- TABLE -->
<table class="inv-table">
    <thead>
        <tr>
            <th style="width:36px; text-align:center;">#</th>
            <th>Equipment</th>
            <th>Lab</th>
            <th style="width:55px; text-align:center;">Qty</th>
            <th style="width:110px; text-align:center;">Status</th>
            <th style="width:65px; text-align:center;">Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($equipment)): ?>
        <tr>
            <td colspan="6" style="text-align:center; color:#888; padding:18px; font-style:italic;">
                <?php echo $search !== '' ? 'No results found.' : 'No equipment on record.'; ?>
            </td>
        </tr>
    <?php else: ?>
        <?php
        $n = $offset + 1;
        foreach ($equipment as $e):
            $status = $e['status'] ?? 'Available';
            $badge  = match($status) {
                'Available'         => 'badge-green',
                'Under Maintenance' => 'badge-yellow',
                'Unavailable'       => 'badge-red',
                default             => 'badge-gray',
            };
            $delete_url = '?' . http_build_query([
                'delete'     => $e['id'],
                'page'       => $page,
                'search'     => $search,
                'lab_filter' => $filter_lab,
            ]);
        ?>
        <tr>
            <td style="text-align:center; color:#aaa;"><?php echo $n++; ?></td>
            <td><?php echo htmlspecialchars($e['equipment_name']); ?></td>
            <td><?php echo htmlspecialchars($e['lab_name'] ?? '—'); ?></td>
            <td style="text-align:center;"><?php echo (int) $e['quantity']; ?></td>
            <td style="text-align:center;">
                <span class="badge <?php echo $badge; ?>">
                    <?php echo htmlspecialchars($status); ?>
                </span>
            </td>
            <td style="text-align:center;">
                <a href="<?php echo $delete_url; ?>" class="btn-delete deleteBtn">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<!-- PAGINATION -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="<?php echo pageUrl(1); ?>">«</a>
        <a href="<?php echo pageUrl($page - 1); ?>">‹</a>
    <?php else: ?>
        <span class="pg-disabled">«</span>
        <span class="pg-disabled">‹</span>
    <?php endif; ?>

    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <a href="<?php echo pageUrl($i); ?>" <?php echo $i === $page ? 'class="active"' : ''; ?>>
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="<?php echo pageUrl($page + 1); ?>">›</a>
        <a href="<?php echo pageUrl($total_pages); ?>">»</a>
    <?php else: ?>
        <span class="pg-disabled">›</span>
        <span class="pg-disabled">»</span>
    <?php endif; ?>

    <span class="pg-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
</div>

</div>

<script>
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm("Delete this equipment? This cannot be undone.")) e.preventDefault();
    });
});
</script>

<?php include("../includes/footer.php"); ?>