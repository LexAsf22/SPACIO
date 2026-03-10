<?php
include("../includes/header.php");
checkRole('student');

$computers = $conn->query("
    SELECT c.*, l.lab_name
    FROM computers c
    JOIN laboratories l ON c.lab_id = l.id
    ORDER BY l.lab_name, c.computer_name
");

if (!$computers) {
    die("SQL Error: " . $conn->error);
}

// Group computers by lab and count statuses
$labs      = [];
$total     = 0;
$available = 0;
$in_use    = 0;
$offline   = 0;

while ($c = $computers->fetch_assoc()) {
    $labs[$c['lab_name']][] = $c;
    $total++;
    $s = strtolower($c['status']);
    if ($s === 'available')   $available++;
    elseif ($s === 'in use')  $in_use++;
    elseif ($s === 'offline') $offline++;
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Literata:ital,wght@0,300;0,400;1,300&display=swap');

    :root {
        --night:   #0b1f0d;
        --forest:  #122b14;
        --canopy:  #1e4422;
        --fern:    #2e6b34;
        --moss:    #3d7a44;
        --sprout:  #74bb7a;
        --mist:    #b9debb;
        --fog:     #e4f2e5;
        --cream:   #f8f4ee;
        --sand:    #ede6d8;
        --gold:    #c49a2a;
        --gold-lt: #e2bb5a;
        --ink:     #141414;
        --gray:    #6b7c6d;
    }

    .page-wrap {
        font-family: 'Literata', Georgia, serif;
        color: var(--ink);
        animation: fadeUp .5s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Page header ── */
    .page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        padding: 32px 36px 28px;
        border-bottom: 1px solid rgba(30,68,34,.1);
        background: #fff;
    }

    .page-eyebrow {
        font-family: 'Syne', sans-serif;
        font-size: .68rem; font-weight: 700;
        letter-spacing: .16em; text-transform: uppercase;
        color: var(--moss);
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 6px;
    }

    .page-eyebrow::before {
        content: '';
        width: 18px; height: 2px;
        background: var(--gold); border-radius: 2px;
    }

    .page-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.65rem; font-weight: 800;
        color: var(--forest); letter-spacing: -.03em; line-height: 1;
    }

    .page-sub {
        font-size: .85rem; color: var(--gray);
        font-style: italic; margin-top: 4px;
    }

    /* Live dot */
    .live-dot {
        display: inline-block;
        width: 7px; height: 7px; border-radius: 50%;
        background: #4caf50;
        animation: pulse 2s infinite;
        margin-right: 4px;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(76,175,80,.5); }
        50%       { box-shadow: 0 0 0 5px rgba(76,175,80,0); }
    }

    .live-label {
        font-family: 'Syne', sans-serif;
        font-size: .7rem; font-weight: 600;
        color: var(--gray); font-style: normal;
        letter-spacing: .06em;
    }

    /* ── Body ── */
    .page-body {
        padding: 32px 36px;
        background: var(--cream);
        min-height: calc(100vh - 120px);
    }

    /* ── Stat cards ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 12px;
        padding: 20px 22px;
        display: flex; align-items: center; gap: 14px;
        transition: box-shadow .2s, transform .2s;
        animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
    }

    .stat-card:hover { box-shadow: 0 4px 18px rgba(18,43,20,.08); transform: translateY(-2px); }
    .stat-card:nth-child(1) { animation-delay: .04s; }
    .stat-card:nth-child(2) { animation-delay: .08s; }
    .stat-card:nth-child(3) { animation-delay: .12s; }
    .stat-card:nth-child(4) { animation-delay: .16s; }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .stat-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: grid; place-items: center;
        font-size: 18px; flex-shrink: 0;
    }

    .stat-icon.slate  { background: #e8eaf6; }
    .stat-icon.green  { background: #e8f5e9; }
    .stat-icon.amber  { background: #fff8e1; }
    .stat-icon.red    { background: #ffebee; }

    .stat-label {
        font-family: 'Syne', sans-serif;
        font-size: .64rem; font-weight: 700;
        letter-spacing: .1em; text-transform: uppercase;
        color: var(--gray); margin-bottom: 3px;
    }

    .stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 1.6rem; font-weight: 800;
        color: var(--forest); letter-spacing: -.04em; line-height: 1;
    }

    /* ── Toolbar ── */
    .toolbar {
        display: flex; align-items: center;
        justify-content: space-between;
        flex-wrap: wrap; gap: 12px;
        margin-bottom: 20px;
    }

    .section-title {
        font-family: 'Syne', sans-serif;
        font-size: 1rem; font-weight: 800;
        color: var(--forest); letter-spacing: -.02em;
        display: flex; align-items: center; gap: 8px;
    }

    .section-title::before {
        content: '';
        width: 3px; height: 18px;
        background: var(--gold); border-radius: 2px;
    }

    .toolbar-right {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }

    /* Search */
    .search-wrap { position: relative; }

    .search-icon {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%);
        font-size: 13px; opacity: .35; pointer-events: none;
    }

    #searchComp {
        font-family: 'Literata', serif;
        font-size: .85rem; font-style: italic;
        padding: 9px 14px 9px 36px;
        background: #fff;
        border: 1.5px solid rgba(30,68,34,.12);
        border-radius: 8px;
        color: var(--ink); outline: none;
        width: 220px;
        transition: border-color .2s, box-shadow .2s;
    }

    #searchComp::placeholder { color: #bbb; }
    #searchComp:focus { border-color: var(--moss); box-shadow: 0 0 0 3px rgba(61,122,68,.08); }

    /* Filter tabs */
    .filter-tabs {
        display: flex; gap: 4px;
        background: rgba(30,68,34,.06);
        border-radius: 8px; padding: 3px;
    }

    .filter-tab {
        font-family: 'Syne', sans-serif;
        font-size: .68rem; font-weight: 700;
        letter-spacing: .07em; text-transform: uppercase;
        padding: 6px 14px;
        border: none; border-radius: 6px;
        cursor: pointer; background: none;
        color: var(--gray);
        transition: background .2s, color .2s;
    }

    .filter-tab.active { background: var(--forest); color: #fff; }

    /* View toggle */
    .view-toggle {
        display: flex; gap: 4px;
        background: rgba(30,68,34,.06);
        border-radius: 8px; padding: 3px;
    }

    .view-btn {
        font-family: 'Syne', sans-serif;
        font-size: .75rem; font-weight: 700;
        padding: 6px 12px;
        border: none; border-radius: 6px;
        cursor: pointer; background: none;
        color: var(--gray);
        transition: background .2s, color .2s;
    }

    .view-btn.active { background: var(--forest); color: #fff; }

    /* ── Lab sections ── */
    .lab-section {
        margin-bottom: 28px;
        animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
    }

    .lab-header {
        display: flex; align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .lab-name {
        font-family: 'Syne', sans-serif;
        font-size: .88rem; font-weight: 800;
        color: var(--forest); letter-spacing: -.01em;
        display: flex; align-items: center; gap: 8px;
    }

    .lab-name::before {
        content: '🏫';
        font-size: .9rem;
    }

    .lab-count {
        font-family: 'Syne', sans-serif;
        font-size: .65rem; font-weight: 700;
        letter-spacing: .08em; text-transform: uppercase;
        padding: 3px 10px;
        background: var(--fog);
        color: var(--fern);
        border: 1px solid rgba(46,107,52,.15);
        border-radius: 100px;
    }

    /* ── Card grid view ── */
    .computer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 10px;
    }

    .computer-grid.hidden { display: none; }

    .comp-card {
        background: #fff;
        border: 1.5px solid rgba(30,68,34,.09);
        border-radius: 12px;
        padding: 16px;
        display: flex; flex-direction: column; gap: 10px;
        transition: box-shadow .2s, transform .2s, border-color .2s;
        cursor: default;
    }

    .comp-card:hover {
        box-shadow: 0 4px 16px rgba(18,43,20,.08);
        transform: translateY(-2px);
        border-color: rgba(61,122,68,.2);
    }

    .comp-card-top {
        display: flex; align-items: center;
        justify-content: space-between;
    }

    .comp-icon {
        width: 36px; height: 36px; border-radius: 9px;
        background: var(--fog);
        display: grid; place-items: center;
        font-size: 16px;
    }

    .comp-status-dot {
        width: 8px; height: 8px; border-radius: 50%;
    }

    .dot-available { background: #4caf50; box-shadow: 0 0 0 3px rgba(76,175,80,.15); }
    .dot-in-use    { background: #ff9800; box-shadow: 0 0 0 3px rgba(255,152,0,.15); }
    .dot-offline   { background: #f44336; box-shadow: 0 0 0 3px rgba(244,67,54,.15); }
    .dot-default   { background: #9e9e9e; box-shadow: 0 0 0 3px rgba(158,158,158,.15); }

    .comp-name {
        font-family: 'Syne', sans-serif;
        font-size: .8rem; font-weight: 700;
        color: var(--forest); letter-spacing: -.01em;
    }

    .comp-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-family: 'Syne', sans-serif;
        font-size: .62rem; font-weight: 700;
        letter-spacing: .07em; text-transform: uppercase;
        padding: 3px 8px; border-radius: 100px;
        align-self: flex-start;
    }

    .badge-available { background: #e8f5e9; color: #2e7d32; }
    .badge-in-use    { background: #fff8e1; color: #e65100; }
    .badge-offline   { background: #ffebee; color: #c62828; }
    .badge-default   { background: #f5f5f5; color: #666; }

    /* ── Table view ── */
    .table-wrap { display: none; }
    .table-wrap.show { display: block; }

    .table-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .comp-table { width: 100%; border-collapse: collapse; }

    .comp-table thead tr { background: var(--forest); }

    .comp-table thead th {
        font-family: 'Syne', sans-serif;
        font-size: .67rem; font-weight: 700;
        letter-spacing: .12em; text-transform: uppercase;
        color: rgba(255,255,255,.65);
        padding: 13px 20px; text-align: left;
    }

    .comp-table tbody tr {
        border-bottom: 1px solid rgba(30,68,34,.06);
        transition: background .15s;
    }

    .comp-table tbody tr:last-child { border-bottom: none; }
    .comp-table tbody tr:hover { background: var(--fog); }

    .comp-table tbody td {
        font-size: .87rem; color: var(--ink);
        padding: 13px 20px;
        font-family: 'Literata', serif;
    }

    .comp-table tbody td:nth-child(2) {
        font-family: 'Syne', sans-serif;
        font-weight: 700; font-size: .83rem;
        color: var(--forest);
    }

    /* Empty state */
    .empty-state {
        text-align: center; padding: 48px 20px;
        color: var(--gray);
    }

    .empty-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: .45; }
    .empty-state p { font-size: .9rem; font-style: italic; }

    /* No results */
    #noResults {
        display: none;
        text-align: center; padding: 40px;
        color: var(--gray); font-style: italic; font-size: .9rem;
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
    }

    /* ── Responsive ── */
    @media (max-width: 700px) {
        .page-body { padding: 20px; }
        .page-header { padding: 24px 20px 20px; }
        .toolbar { flex-direction: column; align-items: flex-start; }
        .toolbar-right { width: 100%; flex-wrap: wrap; }
        #searchComp { width: 100%; }
    }
</style>

<div class="page-wrap">

    <!-- Page header -->
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Student Portal</div>
            <div class="page-title">Available Computers</div>
            <div class="page-sub">
                <span class="live-dot"></span>
                <span class="live-label">Live status — <?= date('F j, Y') ?></span>
            </div>
        </div>
    </div>

    <div class="page-body">

        <!-- Stat cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon slate">💻</div>
                <div>
                    <div class="stat-label">Total</div>
                    <div class="stat-value"><?= $total ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div>
                    <div class="stat-label">Available</div>
                    <div class="stat-value"><?= $available ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">⚡</div>
                <div>
                    <div class="stat-label">In Use</div>
                    <div class="stat-value"><?= $in_use ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">🔴</div>
                <div>
                    <div class="stat-label">Offline</div>
                    <div class="stat-value"><?= $offline ?></div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="section-title">Computers by Lab</div>
            <div class="toolbar-right">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">All</button>
                    <button class="filter-tab" data-filter="available">Available</button>
                    <button class="filter-tab" data-filter="in use">In Use</button>
                    <button class="filter-tab" data-filter="offline">Offline</button>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" id="gridViewBtn" title="Grid view">▦ Grid</button>
                    <button class="view-btn" id="listViewBtn" title="List view">☰ List</button>
                </div>
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchComp" placeholder="Search computers…">
                </div>
            </div>
        </div>

        <!-- No results message -->
        <div id="noResults">No computers match your search or filter.</div>

        <!-- Content: grouped by lab -->
        <?php foreach ($labs as $labName => $computers): ?>
        <div class="lab-section" data-lab="<?= htmlspecialchars(strtolower($labName)) ?>">

            <div class="lab-header">
                <div class="lab-name"><?= htmlspecialchars($labName) ?></div>
                <span class="lab-count"><?= count($computers) ?> unit<?= count($computers) !== 1 ? 's' : '' ?></span>
            </div>

            <!-- Grid view -->
            <div class="computer-grid" id="grid-<?= md5($labName) ?>">
                <?php foreach ($computers as $c):
                    $s          = strtolower($c['status']);
                    $dotClass   = match($s) { 'available' => 'dot-available', 'in use' => 'dot-in-use', 'offline' => 'dot-offline', default => 'dot-default' };
                    $badgeClass = match($s) { 'available' => 'badge-available', 'in use' => 'badge-in-use', 'offline' => 'badge-offline', default => 'badge-default' };
                ?>
                <div class="comp-card" data-status="<?= htmlspecialchars($s) ?>" data-name="<?= htmlspecialchars(strtolower($c['computer_name'])) ?>">
                    <div class="comp-card-top">
                        <div class="comp-icon">💻</div>
                        <div class="comp-status-dot <?= $dotClass ?>"></div>
                    </div>
                    <div class="comp-name"><?= htmlspecialchars($c['computer_name']) ?></div>
                    <span class="comp-badge <?= $badgeClass ?>"><?= htmlspecialchars($c['status']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Table / list view -->
            <div class="table-wrap" id="list-<?= md5($labName) ?>">
                <div class="table-card">
                    <table class="comp-table">
                        <thead>
                            <tr>
                                <th>Laboratory</th>
                                <th>Computer Name / ID</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($computers as $c):
                            $s          = strtolower($c['status']);
                            $badgeClass = match($s) { 'available' => 'badge-available', 'in use' => 'badge-in-use', 'offline' => 'badge-offline', default => 'badge-default' };
                        ?>
                        <tr data-status="<?= htmlspecialchars($s) ?>" data-name="<?= htmlspecialchars(strtolower($c['computer_name'])) ?>">
                            <td><?= htmlspecialchars($labName) ?></td>
                            <td><?= htmlspecialchars($c['computer_name']) ?></td>
                            <td><span class="comp-badge <?= $badgeClass ?>"><?= htmlspecialchars($c['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php endforeach; ?>

        <?php if (empty($labs)): ?>
        <div class="empty-state">
            <div class="empty-icon">🖥️</div>
            <p>No computers found in the system.</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
    let currentView   = 'grid';
    let currentFilter = 'all';
    let searchTerm    = '';

    // ── View toggle ──
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');

    gridBtn.addEventListener('click', () => {
        currentView = 'grid';
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
        document.querySelectorAll('.computer-grid').forEach(g => g.classList.remove('hidden'));
        document.querySelectorAll('.table-wrap').forEach(t => t.classList.remove('show'));
        applyFilters();
    });

    listBtn.addEventListener('click', () => {
        currentView = 'list';
        listBtn.classList.add('active');
        gridBtn.classList.remove('active');
        document.querySelectorAll('.computer-grid').forEach(g => g.classList.add('hidden'));
        document.querySelectorAll('.table-wrap').forEach(t => t.classList.add('show'));
        applyFilters();
    });

    // ── Filter tabs ──
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            applyFilters();
        });
    });

    // ── Search ──
    let debounce;
    document.getElementById('searchComp').addEventListener('keyup', function () {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            searchTerm = this.value.toLowerCase();
            applyFilters();
        }, 180);
    });

    // ── Apply all filters ──
    function applyFilters() {
        let visibleTotal = 0;

        document.querySelectorAll('.lab-section').forEach(section => {
            const items = currentView === 'grid'
                ? section.querySelectorAll('.comp-card')
                : section.querySelectorAll('tbody tr');

            let sectionVisible = 0;

            items.forEach(item => {
                const statusMatch = currentFilter === 'all' || item.dataset.status === currentFilter;
                const searchMatch = !searchTerm || item.dataset.name?.includes(searchTerm)
                    || item.textContent.toLowerCase().includes(searchTerm);
                const show = statusMatch && searchMatch;
                item.style.display = show ? '' : 'none';
                if (show) sectionVisible++;
            });

            section.style.display = sectionVisible > 0 ? '' : 'none';
            visibleTotal += sectionVisible;
        });

        document.getElementById('noResults').style.display = visibleTotal === 0 ? 'block' : 'none';
    }
</script>

<?php include("../includes/footer.php"); ?>