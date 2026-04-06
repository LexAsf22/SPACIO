<?php
// frontend/admin/inventory.php
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
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
    if ($result['success']) {
        setFlash("Equipment removed.", "error");
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

// ── Fetch equipment list ──────────────────────────────────────────────────────
$per_page   = 10;
$page       = max(1, (int)($_GET['page']       ?? 1));
$search     = trim(        $_GET['search']     ?? '');
$filter_lab = (int)       ($_GET['lab_filter'] ?? 0);

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
    $equipment   = $result['data']['results']    ?? $result['data'] ?? [];
    $total_rows  = $result['data']['count']       ?? count($equipment);
    $total_pages = $result['data']['total_pages'] ?? max(1, ceil($total_rows / $per_page));
}

$page   = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

// ── Labs for dropdown ─────────────────────────────────────────────────────────
$labsResult = djangoGet('/api/v1/labs/');
$labs       = ($labsResult['success'] && isset($labsResult['data']))
    ? $labsResult['data']
    : [];

function pageUrl(int $p): string {
    $q = $_GET;
    $q['page'] = $p;
    unset($q['delete']);
    return '?' . http_build_query($q);
}

include("../includes/header.php");
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Inventory</h1>
        <p class="page-subtitle">Manage lab equipment and availability</p>
    </div>
    <span class="badge badge-gray" style="font-size:.8rem; padding:6px 14px; font-family:var(--font-mono);">
        <?php echo $total_rows; ?> items total
    </span>
</div>

<?php echo getFlash(); ?>

<!-- Add Equipment Form -->
<div class="inv-form-panel">
    <h3>+ Add Equipment</h3>
    <form method="POST">
        <div class="inv-form-grid">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label required">Lab</label>
                <select name="lab" class="form-control" required>
                    <option value="">— Select Lab —</option>
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?php echo (int)$lab['id']; ?>">
                            <?php echo htmlspecialchars($lab['lab_name'] ?? $lab['name'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label required">Equipment Name</label>
                <input type="text" name="equipment_name" class="form-control"
                       placeholder="e.g. Microscope" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label required">Quantity</label>
                <input type="number" name="quantity" class="form-control"
                       placeholder="1" min="1" required>
            </div>
            <div style="padding-top:22px;">
                <button name="add" class="btn btn-primary btn-full">Add</button>
            </div>
        </div>
    </form>
</div>

<!-- Search & Filter -->
<form method="GET" class="filter-bar">
    <input type="text" name="search" class="form-control"
           value="<?php echo htmlspecialchars($search); ?>"
           placeholder="Search name, lab, status...">
    <select name="lab_filter" class="form-control" style="min-width:0; width:auto;">
        <option value="">All Labs</option>
        <?php foreach ($labs as $lab):
            $sel = ($filter_lab === (int)$lab['id']) ? 'selected' : '';
        ?>
            <option value="<?php echo (int)$lab['id']; ?>" <?php echo $sel; ?>>
                <?php echo htmlspecialchars($lab['lab_name'] ?? $lab['name'] ?? ''); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($search !== '' || $filter_lab > 0): ?>
        <a href="?" class="btn btn-secondary">✕ Clear</a>
    <?php endif; ?>
    <span class="filter-summary">
        Showing <?php echo min($offset + 1, $total_rows); ?>–<?php echo min($offset + $per_page, $total_rows); ?>
        of <?php echo $total_rows; ?>
    </span>
</form>

<!-- Table -->
<div class="table-wrap">
    <table class="sp-table">
        <thead>
            <tr>
                <th style="width:44px; text-align:center;">#</th>
                <th>Equipment</th>
                <th>Lab</th>
                <th style="width:70px; text-align:center;">Qty</th>
                <th style="width:130px; text-align:center;">Status</th>
                <th style="width:80px; text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($equipment)): ?>
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <span class="empty-state-icon">📦</span>
                        <p class="empty-state-text">
                            <?php echo $search !== '' ? 'No results found.' : 'No equipment on record.'; ?>
                        </p>
                    </div>
                </td>
            </tr>
        <?php else:
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
                <td style="text-align:center;" class="td-mono"><?php echo $n++; ?></td>
                <td style="font-weight:500; color:var(--ink-800);">
                    <?php echo htmlspecialchars($e['equipment_name']); ?>
                </td>
                <td style="color:var(--ink-500);"><?php echo htmlspecialchars($e['lab_name'] ?? '—'); ?></td>
                <td style="text-align:center;" class="td-mono"><?php echo (int)$e['quantity']; ?></td>
                <td style="text-align:center;">
                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span>
                </td>
                <td style="text-align:center;">
                    <a href="<?php echo $delete_url; ?>" class="btn btn-danger btn-xs deleteBtn">Delete</a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?php echo pageUrl(1); ?>" class="pg-btn">«</a>
            <a href="<?php echo pageUrl($page - 1); ?>" class="pg-btn">‹</a>
        <?php else: ?>
            <span class="pg-btn disabled">«</span>
            <span class="pg-btn disabled">‹</span>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <a href="<?php echo pageUrl($i); ?>"
               class="pg-btn <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="<?php echo pageUrl($page + 1); ?>" class="pg-btn">›</a>
            <a href="<?php echo pageUrl($total_pages); ?>" class="pg-btn">»</a>
        <?php else: ?>
            <span class="pg-btn disabled">›</span>
            <span class="pg-btn disabled">»</span>
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