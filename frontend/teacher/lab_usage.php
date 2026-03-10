<?php
include("../includes/header.php");
include(__DIR__ . "/../../backend/config/helpers.php");
checkRole('teacher');

// Summary counts
$total_usage   = $conn->query("SELECT COUNT(*) as c FROM reservations WHERE status='Approved'")->fetch_assoc()['c'];
$today_usage   = $conn->query("SELECT COUNT(*) as c FROM reservations WHERE status='Approved' AND date=CURDATE()")->fetch_assoc()['c'];
$total_labs    = $conn->query("SELECT COUNT(*) as c FROM laboratories")->fetch_assoc()['c'];
$total_students = $conn->query("SELECT COUNT(DISTINCT user_id) as c FROM reservations WHERE status='Approved'")->fetch_assoc()['c'];

// Main usage data
$usage = $conn->query("
    SELECT r.*, u.name AS student_name, l.lab_name
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN laboratories l ON r.lab_id = l.id
    WHERE r.status = 'Approved'
    ORDER BY r.date DESC
    LIMIT 50
");

// Labs list for filter dropdown
$labs_list = $conn->query("SELECT DISTINCT lab_name FROM laboratories ORDER BY lab_name");
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

    .export-btn {
        font-family: 'Syne', sans-serif;
        font-size: .75rem; font-weight: 700;
        letter-spacing: .08em; text-transform: uppercase;
        padding: 9px 18px;
        background: var(--forest); color: #fff;
        border: none; border-radius: 8px;
        cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        transition: background .2s, transform .15s;
    }

    .export-btn:hover { background: var(--canopy); transform: translateY(-1px); }

    /* ── Body ── */
    .page-body {
        padding: 32px 36px;
        background: var(--cream);
        min-height: calc(100vh - 120px);
    }

    /* ── Stat cards ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
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
        width: 44px; height: 44px; border-radius: 10px;
        display: grid; place-items: center;
        font-size: 19px; flex-shrink: 0;
    }

    .stat-icon.green  { background: #e8f5e9; }
    .stat-icon.blue   { background: #e3f2fd; }
    .stat-icon.amber  { background: #fff8e1; }
    .stat-icon.purple { background: #f3e5f5; }

    .stat-label {
        font-family: 'Syne', sans-serif;
        font-size: .64rem; font-weight: 700;
        letter-spacing: .1em; text-transform: uppercase;
        color: var(--gray); margin-bottom: 3px;
    }

    .stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 1.65rem; font-weight: 800;
        color: var(--forest); letter-spacing: -.04em; line-height: 1;
    }

    .stat-sub {
        font-size: .72rem; color: var(--gray);
        font-style: italic; margin-top: 3px;
    }

    /* ── Toolbar ── */
    .toolbar {
        display: flex; align-items: center;
        justify-content: space-between;
        flex-wrap: wrap; gap: 12px;
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

    /* Lab filter dropdown */
    .lab-filter-wrap { position: relative; }

    .lab-filter-wrap::after {
        content: '▾';
        position: absolute; right: 12px; top: 50%;
        transform: translateY(-50%);
        font-size: .7rem; color: var(--gray); pointer-events: none;
    }

    #labFilter {
        font-family: 'Syne', sans-serif;
        font-size: .72rem; font-weight: 700;
        letter-spacing: .04em;
        padding: 8px 32px 8px 12px;
        background: #fff;
        border: 1.5px solid rgba(30,68,34,.12);
        border-radius: 8px;
        color: var(--forest); outline: none;
        appearance: none; cursor: pointer;
        transition: border-color .2s;
    }

    #labFilter:focus { border-color: var(--moss); }

    /* Date filter */
    #dateFilter {
        font-family: 'Literata', serif;
        font-size: .85rem; font-style: italic;
        padding: 8px 12px;
        background: #fff;
        border: 1.5px solid rgba(30,68,34,.12);
        border-radius: 8px;
        color: var(--ink); outline: none;
        transition: border-color .2s;
    }

    #dateFilter:focus { border-color: var(--moss); }

    /* Search */
    .search-wrap { position: relative; }

    .search-icon {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%);
        font-size: 13px; opacity: .35; pointer-events: none;
    }

    #searchLab {
        font-family: 'Literata', serif;
        font-size: .85rem; font-style: italic;
        padding: 9px 14px 9px 36px;
        background: #fff;
        border: 1.5px solid rgba(30,68,34,.12);
        border-radius: 8px;
        color: var(--ink); outline: none; width: 210px;
        transition: border-color .2s, box-shadow .2s;
    }

    #searchLab::placeholder { color: #bbb; }
    #searchLab:focus { border-color: var(--moss); box-shadow: 0 0 0 3px rgba(61,122,68,.08); }

    /* Result count */
    .result-count {
        font-family: 'Syne', sans-serif;
        font-size: .7rem; font-weight: 700;
        color: var(--gray); letter-spacing: .04em;
        white-space: nowrap;
    }

    /* ── Table card ── */
    .table-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
        overflow: hidden;
        animation: cardIn .5s .15s cubic-bezier(.22,1,.36,1) both;
    }

    #usageTable {
        width: 100%; border-collapse: collapse;
    }

    #usageTable thead tr { background: var(--forest); }

    #usageTable thead th {
        font-family: 'Syne', sans-serif;
        font-size: .67rem; font-weight: 700;
        letter-spacing: .12em; text-transform: uppercase;
        color: rgba(255,255,255,.65);
        padding: 14px 20px; text-align: left;
        white-space: nowrap;
    }

    #usageTable tbody tr {
        border-bottom: 1px solid rgba(30,68,34,.06);
        transition: background .15s;
    }

    #usageTable tbody tr:last-child { border-bottom: none; }
    #usageTable tbody tr:hover { background: var(--fog); }

    #usageTable tbody td {
        font-size: .87rem; color: var(--ink);
        padding: 14px 20px;
        font-family: 'Literata', serif;
    }

    /* Student cell */
    .student-cell {
        display: flex; align-items: center; gap: 10px;
    }

    .student-avatar {
        width: 30px; height: 30px; border-radius: 50%;
        background: var(--forest);
        display: grid; place-items: center;
        font-family: 'Syne', sans-serif;
        font-size: .7rem; font-weight: 800;
        color: #fff; flex-shrink: 0;
        text-transform: uppercase;
    }

    .student-name {
        font-family: 'Syne', sans-serif;
        font-size: .84rem; font-weight: 700;
        color: var(--forest);
    }

    /* Lab chip */
    .lab-chip {
        font-family: 'Syne', sans-serif;
        font-size: .65rem; font-weight: 700;
        letter-spacing: .07em; text-transform: uppercase;
        padding: 3px 10px;
        background: var(--fog);
        color: var(--fern);
        border: 1px solid rgba(46,107,52,.15);
        border-radius: 6px;
        white-space: nowrap;
    }

    /* Time chip */
    .time-chip {
        font-family: 'Syne', sans-serif;
        font-size: .65rem; font-weight: 700;
        letter-spacing: .06em;
        padding: 3px 10px;
        background: var(--sand);
        color: var(--bark, #3a2e1e);
        border-radius: 6px;
        white-space: nowrap;
    }

    /* Today badge */
    .today-badge {
        font-family: 'Syne', sans-serif;
        font-size: .58rem; font-weight: 800;
        letter-spacing: .1em; text-transform: uppercase;
        padding: 2px 7px;
        background: #e8f5e9; color: #2e7d32;
        border-radius: 4px; margin-left: 6px;
        vertical-align: middle;
    }

    /* Empty / no results */
    .empty-row td {
        text-align: center; padding: 48px 20px !important;
        color: var(--gray); font-style: italic; font-size: .9rem;
    }

    .empty-icon { font-size: 2rem; display: block; margin-bottom: 8px; opacity: .4; }

    /* ── Responsive ── */
    @media (max-width: 800px) {
        .page-body { padding: 20px; }
        .page-header { padding: 24px 20px 20px; }
        .toolbar { flex-direction: column; align-items: flex-start; }
        .toolbar-right { width: 100%; }
        #searchLab { width: 100%; }
    }
</style>

<div class="page-wrap">

    <!-- Page header -->
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Teacher Portal</div>
            <div class="page-title">Lab Usage Monitor</div>
            <div class="page-sub">
                <span class="live-dot"></span>
                Showing approved reservations — <?= date('F j, Y') ?>
            </div>
        </div>
        <button class="export-btn" onclick="exportCSV()">↓ Export CSV</button>
    </div>

    <div class="page-body">

        <!-- Stat cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green">📋</div>
                <div>
                    <div class="stat-label">Total Sessions</div>
                    <div class="stat-value"><?= $total_usage ?></div>
                    <div class="stat-sub">All time approved</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">📅</div>
                <div>
                    <div class="stat-label">Today</div>
                    <div class="stat-value"><?= $today_usage ?></div>
                    <div class="stat-sub">Sessions today</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">🔬</div>
                <div>
                    <div class="stat-label">Labs</div>
                    <div class="stat-value"><?= $total_labs ?></div>
                    <div class="stat-sub">Active laboratories</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">🎓</div>
                <div>
                    <div class="stat-label">Students</div>
                    <div class="stat-value"><?= $total_students ?></div>
                    <div class="stat-sub">Unique users</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="section-title">Usage Log</div>
                <span class="result-count" id="resultCount"></span>
            </div>
            <div class="toolbar-right">

                <!-- Lab filter -->
                <div class="lab-filter-wrap">
                    <select id="labFilter">
                        <option value="">All Labs</option>
                        <?php while ($lab = $labs_list->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars(strtolower($lab['lab_name'])) ?>">
                            <?= htmlspecialchars($lab['lab_name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Date filter -->
                <input type="date" id="dateFilter" title="Filter by date">

                <!-- Search -->
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchLab" placeholder="Search student or lab…">
                </div>

            </div>
        </div>

        <!-- Table -->
        <div class="table-card">
            <table id="usageTable">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Laboratory</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php
                $rows = [];
                $today = date('Y-m-d');
                while ($u = $usage->fetch_assoc()):
                    $rows[] = $u;
                    $isToday = substr($u['date'], 0, 10) === $today;
                    $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $u['student_name']), 0, 2))));
                    $dateFormatted = function_exists('formatDate') ? formatDate($u['date']) : date("F d, Y", strtotime($u['date']));
                ?>
                <tr
                    data-student="<?= htmlspecialchars(strtolower($u['student_name'])) ?>"
                    data-lab="<?= htmlspecialchars(strtolower($u['lab_name'])) ?>"
                    data-date="<?= htmlspecialchars(substr($u['date'], 0, 10)) ?>"
                >
                    <td>
                        <div class="student-cell">
                            <div class="student-avatar"><?= $initials ?></div>
                            <span class="student-name"><?= htmlspecialchars($u['student_name']) ?></span>
                        </div>
                    </td>
                    <td><span class="lab-chip"><?= htmlspecialchars($u['lab_name']) ?></span></td>
                    <td>
                        <?= htmlspecialchars($dateFormatted) ?>
                        <?php if ($isToday): ?><span class="today-badge">Today</span><?php endif; ?>
                    </td>
                    <td><span class="time-chip"><?= htmlspecialchars($u['time_slot']) ?></span></td>
                </tr>
                <?php endwhile; ?>

                <?php if (empty($rows)): ?>
                <tr class="empty-row">
                    <td colspan="4">
                        <span class="empty-icon">📭</span>
                        No approved lab sessions found.
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    // Store all rows data for CSV
    const allRows = <?= json_encode(array_map(fn($r) => [
        'student' => $r['student_name'],
        'lab'     => $r['lab_name'],
        'date'    => $r['date'],
        'time'    => $r['time_slot'],
    ], $rows)) ?>;

    // ── Filters ──
    let searchTerm  = '';
    let labFilter   = '';
    let dateFilter  = '';

    function applyFilters() {
        const rows    = document.querySelectorAll('#tableBody tr[data-student]');
        let visible   = 0;

        rows.forEach(row => {
            const matchSearch = !searchTerm
                || row.dataset.student.includes(searchTerm)
                || row.dataset.lab.includes(searchTerm);
            const matchLab  = !labFilter  || row.dataset.lab  === labFilter;
            const matchDate = !dateFilter || row.dataset.date === dateFilter;
            const show = matchSearch && matchLab && matchDate;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // No results row
        let noRow = document.getElementById('noResultsRow');
        if (visible === 0 && rows.length > 0) {
            if (!noRow) {
                noRow = document.createElement('tr');
                noRow.id = 'noResultsRow';
                noRow.className = 'empty-row';
                noRow.innerHTML = '<td colspan="4"><span class="empty-icon">🔍</span>No results match your search or filter.</td>';
                document.getElementById('tableBody').appendChild(noRow);
            }
            noRow.style.display = '';
        } else if (noRow) {
            noRow.style.display = 'none';
        }

        document.getElementById('resultCount').textContent =
            visible > 0 ? `${visible} record${visible !== 1 ? 's' : ''}` : '';
    }

    // Init count
    applyFilters();

    // Search
    let debounce;
    document.getElementById('searchLab').addEventListener('keyup', function () {
        clearTimeout(debounce);
        debounce = setTimeout(() => { searchTerm = this.value.toLowerCase(); applyFilters(); }, 180);
    });

    // Lab filter
    document.getElementById('labFilter').addEventListener('change', function () {
        labFilter = this.value;
        applyFilters();
    });

    // Date filter
    document.getElementById('dateFilter').addEventListener('change', function () {
        dateFilter = this.value;
        applyFilters();
    });

    // ── CSV Export ──
    function exportCSV() {
        const rows    = document.querySelectorAll('#tableBody tr[data-student]');
        const visible = [...rows].filter(r => r.style.display !== 'none');

        const header = ['Student', 'Laboratory', 'Date', 'Time Slot'];
        const lines  = [header.join(',')];

        visible.forEach(row => {
            const cells = row.querySelectorAll('td');
            const student  = cells[0].querySelector('.student-name')?.textContent.trim() ?? '';
            const lab      = cells[1].textContent.trim();
            const date     = cells[2].textContent.trim().replace('Today', '').trim();
            const timeslot = cells[3].textContent.trim();
            lines.push([student, lab, date, timeslot].map(v => `"${v}"`).join(','));
        });

        const blob = new Blob([lines.join('\n')], { type: 'text/csv' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = `lab-usage-${new Date().toISOString().slice(0,10)}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    }
</script>

<?php include("../includes/footer.php"); ?>