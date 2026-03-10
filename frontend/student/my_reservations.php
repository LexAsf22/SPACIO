<?php
include("../includes/header.php");
checkRole('student');

$user_id = $_SESSION['user']['id'];

$reservations = $conn->query("
    SELECT r.*, l.lab_name
    FROM reservations r
    LEFT JOIN laboratories l ON r.lab_id = l.id
    WHERE r.user_id = $user_id
    ORDER BY r.date DESC
");

if (!$reservations) {
    die("SQL Error: " . $conn->error);
}

$total    = $reservations->num_rows;
$approved = $conn->query("SELECT COUNT(*) as c FROM reservations WHERE user_id=$user_id AND status='Approved'")->fetch_assoc()['c'];
$pending  = $conn->query("SELECT COUNT(*) as c FROM reservations WHERE user_id=$user_id AND status='Pending'")->fetch_assoc()['c'];
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
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: var(--moss);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .page-eyebrow::before {
        content: '';
        width: 18px; height: 2px;
        background: var(--gold);
        border-radius: 2px;
    }

    .page-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--forest);
        letter-spacing: -.03em;
        line-height: 1;
    }

    /* ── Body ── */
    .page-body {
        padding: 32px 36px;
        background: var(--cream);
        min-height: calc(100vh - 120px);
    }

    /* ── Summary cards ── */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
        margin-bottom: 32px;
    }

    .sum-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 12px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 14px;
        animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
        transition: box-shadow .2s, transform .2s;
    }

    .sum-card:hover { box-shadow: 0 4px 18px rgba(18,43,20,.08); transform: translateY(-2px); }
    .sum-card:nth-child(1) { animation-delay: .05s; }
    .sum-card:nth-child(2) { animation-delay: .10s; }
    .sum-card:nth-child(3) { animation-delay: .15s; }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .sum-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: grid; place-items: center;
        font-size: 18px; flex-shrink: 0;
    }

    .sum-icon.blue   { background: #e3f2fd; }
    .sum-icon.green  { background: #e8f5e9; }
    .sum-icon.amber  { background: #fff8e1; }

    .sum-label {
        font-family: 'Syne', sans-serif;
        font-size: .65rem; font-weight: 700;
        letter-spacing: .1em; text-transform: uppercase;
        color: var(--gray); margin-bottom: 3px;
    }

    .sum-value {
        font-family: 'Syne', sans-serif;
        font-size: 1.6rem; font-weight: 800;
        color: var(--forest); letter-spacing: -.04em; line-height: 1;
    }

    /* ── Toolbar ── */
    .toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
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

    .search-wrap .search-icon {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%);
        font-size: 13px; opacity: .35; pointer-events: none;
    }

    #searchRes {
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

    #searchRes::placeholder { color: #bbb; }
    #searchRes:focus { border-color: var(--moss); box-shadow: 0 0 0 3px rgba(61,122,68,.08); }

    /* Filter tabs */
    .filter-tabs {
        display: flex; gap: 4px;
        background: rgba(30,68,34,.06);
        border-radius: 8px;
        padding: 3px;
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

    .filter-tab.active {
        background: var(--forest);
        color: #fff;
    }

    /* ── Table ── */
    .table-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
        overflow: hidden;
        animation: cardIn .5s .2s cubic-bezier(.22,1,.36,1) both;
    }

    #myResTable {
        width: 100%;
        border-collapse: collapse;
    }

    #myResTable thead tr { background: var(--forest); }

    #myResTable thead th {
        font-family: 'Syne', sans-serif;
        font-size: .67rem; font-weight: 700;
        letter-spacing: .12em; text-transform: uppercase;
        color: rgba(255,255,255,.65);
        padding: 14px 20px; text-align: left;
        white-space: nowrap;
    }

    #myResTable tbody tr {
        border-bottom: 1px solid rgba(30,68,34,.06);
        transition: background .15s;
    }

    #myResTable tbody tr:last-child { border-bottom: none; }
    #myResTable tbody tr:hover { background: var(--fog); }

    #myResTable tbody td {
        font-size: .87rem; color: var(--ink);
        padding: 14px 20px;
        font-family: 'Literata', serif;
    }

    #myResTable tbody td:nth-child(2) {
        font-family: 'Syne', sans-serif;
        font-weight: 700; font-size: .85rem;
        color: var(--forest);
    }

    /* Type chip */
    .type-chip {
        font-family: 'Syne', sans-serif;
        font-size: .65rem; font-weight: 700;
        letter-spacing: .08em; text-transform: uppercase;
        padding: 3px 10px;
        background: var(--fog);
        color: var(--fern);
        border: 1px solid rgba(46,107,52,.15);
        border-radius: 6px;
    }

    /* Status badges */
    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-family: 'Syne', sans-serif;
        font-size: .65rem; font-weight: 700;
        letter-spacing: .08em; text-transform: uppercase;
        padding: 4px 10px; border-radius: 100px;
    }

    .badge::before {
        content: ''; width: 5px; height: 5px;
        border-radius: 50%;
    }

    .badge-approved { background: #e8f5e9; color: #2e7d32; }
    .badge-approved::before { background: #4caf50; }
    .badge-pending  { background: #fff8e1; color: #e65100; }
    .badge-pending::before  { background: #ff9800; }
    .badge-rejected { background: #ffebee; color: #c62828; }
    .badge-rejected::before { background: #f44336; }
    .badge-default  { background: #f5f5f5; color: #666; }
    .badge-default::before  { background: #999; }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 56px 20px;
        color: var(--gray);
    }

    .empty-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: .45; }
    .empty-state p { font-size: .9rem; font-style: italic; margin-bottom: 18px; }

    .empty-link {
        font-family: 'Syne', sans-serif;
        font-size: .78rem; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase;
        padding: 10px 24px;
        background: var(--forest); color: #fff;
        border-radius: 8px; text-decoration: none;
        display: inline-block;
        transition: background .2s;
    }

    .empty-link:hover { background: var(--canopy); }

    /* No results row */
    .no-results-row td {
        text-align: center;
        padding: 32px !important;
        color: var(--gray);
        font-style: italic;
        font-size: .87rem;
    }
</style>

<div class="page-wrap">

    <!-- Page header -->
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Student Portal</div>
            <div class="page-title">My Reservations</div>
        </div>
        <a href="../student/reserve_lab.php" style="
            font-family:'Syne',sans-serif; font-size:.78rem; font-weight:700;
            letter-spacing:.07em; text-transform:uppercase;
            padding:10px 20px; background:var(--forest); color:#fff;
            border-radius:8px; text-decoration:none;
            transition:background .2s;
            display:inline-flex; align-items:center; gap:6px;
        " onmouseover="this.style.background='#1e4422'" onmouseout="this.style.background='#122b14'">
            + New Reservation
        </a>
    </div>

    <div class="page-body">

        <!-- Summary cards -->
        <div class="summary-grid">
            <div class="sum-card">
                <div class="sum-icon blue">📋</div>
                <div>
                    <div class="sum-label">Total</div>
                    <div class="sum-value"><?= $total ?></div>
                </div>
            </div>
            <div class="sum-card">
                <div class="sum-icon green">✅</div>
                <div>
                    <div class="sum-label">Approved</div>
                    <div class="sum-value"><?= $approved ?></div>
                </div>
            </div>
            <div class="sum-card">
                <div class="sum-icon amber">⏳</div>
                <div>
                    <div class="sum-label">Pending</div>
                    <div class="sum-value"><?= $pending ?></div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="section-title">All Reservations</div>
            <div class="toolbar-right">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">All</button>
                    <button class="filter-tab" data-filter="approved">Approved</button>
                    <button class="filter-tab" data-filter="pending">Pending</button>
                    <button class="filter-tab" data-filter="rejected">Rejected</button>
                </div>
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchRes" placeholder="Search…">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-card">
            <table id="myResTable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Laboratory</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($reservations->num_rows > 0): ?>
                    <?php while ($r = $reservations->fetch_assoc()):
                        $status = $r['status'];
                        $badgeClass = match(strtolower($status)) {
                            'approved' => 'badge-approved',
                            'pending'  => 'badge-pending',
                            'rejected' => 'badge-rejected',
                            default    => 'badge-default',
                        };
                    ?>
                    <tr data-status="<?= strtolower($status) ?>">
                        <td><span class="type-chip">Lab</span></td>
                        <td><?= htmlspecialchars($r['lab_name'] ?? '—') ?></td>
                        <td><?= date("F d, Y", strtotime($r['date'])) ?></td>
                        <td><?= htmlspecialchars($r['time_slot']) ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon">📭</div>
                                <p>You haven't made any reservations yet.</p>
                                <a class="empty-link" href="../student/reserve_lab.php">Reserve a Lab →</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    // ── Search ──
    let searchTerm   = '';
    let activeFilter = 'all';

    function applyFilters() {
        const rows = document.querySelectorAll('#myResTable tbody tr[data-status]');
        let visible = 0;

        rows.forEach(row => {
            const matchSearch = row.textContent.toLowerCase().includes(searchTerm);
            const matchFilter = activeFilter === 'all' || row.dataset.status === activeFilter;
            const show = matchSearch && matchFilter;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // No results row
        let noRow = document.getElementById('noResultsRow');
        if (visible === 0 && rows.length > 0) {
            if (!noRow) {
                noRow = document.createElement('tr');
                noRow.id = 'noResultsRow';
                noRow.className = 'no-results-row';
                noRow.innerHTML = '<td colspan="5">No reservations match your search or filter.</td>';
                document.querySelector('#myResTable tbody').appendChild(noRow);
            }
            noRow.style.display = '';
        } else if (noRow) {
            noRow.style.display = 'none';
        }
    }

    // Search input
    let debounce;
    document.getElementById('searchRes').addEventListener('keyup', function () {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            searchTerm = this.value.toLowerCase();
            applyFilters();
        }, 180);
    });

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.dataset.filter;
            applyFilters();
        });
    });
</script>

<?php include("../includes/footer.php"); ?>