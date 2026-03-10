<?php
include("../includes/header.php");
include(__DIR__ . "/../../backend/config/helpers.php");
checkRole('teacher');

$user_id = $_SESSION['user']['id'];
$user    = $_SESSION['user'];

$issues = $conn->query("SELECT * FROM issues WHERE user_id=$user_id ORDER BY created_at DESC");

// Tally counts for stat cards
$total = $pending = $resolved = $in_progress = 0;
$all_issues = [];
while ($i = $issues->fetch_assoc()) {
    $all_issues[] = $i;
    $total++;
    $s = strtolower($i['status']);
    if ($s === 'pending')     $pending++;
    elseif ($s === 'resolved') $resolved++;
    else                       $in_progress++;
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Literata:ital,wght@0,300;0,400;1,300&display=swap');

    :root {
        --forest:  #122b14;
        --canopy:  #1e4422;
        --moss:    #3d7a44;
        --fog:     #e4f2e5;
        --cream:   #f8f4ee;
        --gold:    #c49a2a;
        --ink:     #141414;
        --gray:    #6b7c6d;
    }

    .dash-wrap {
        font-family: 'Literata', Georgia, serif;
        color: var(--ink);
        animation: dashIn .55s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes dashIn {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Page header ── */
    .dash-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        padding: 32px 36px 28px;
        border-bottom: 1px solid rgba(30,68,34,.1);
        background: #fff;
    }

    .dash-eyebrow {
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

    .dash-eyebrow::before {
        content: '';
        width: 18px; height: 2px;
        background: var(--gold);
        border-radius: 2px;
    }

    .dash-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--forest);
        letter-spacing: -.03em;
        line-height: 1;
    }

    .dash-date {
        font-size: .8rem;
        color: var(--gray);
        font-style: italic;
    }

    .live-dot {
        display: inline-block;
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #4caf50;
        animation: pulse 2s infinite;
        margin-right: 2px;
    }

    @keyframes pulse {
        0%,100% { box-shadow: 0 0 0 0 rgba(76,175,80,.5); }
        50%      { box-shadow: 0 0 0 5px rgba(76,175,80,0); }
    }

    .report-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-family: 'Syne', sans-serif;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 9px 18px;
        background: var(--forest);
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: background .2s, transform .15s;
    }

    .report-btn:hover { background: var(--canopy); transform: translateY(-1px); }

    /* ── Body ── */
    .dash-body {
        padding: 32px 36px;
        background: var(--cream);
        min-height: calc(100vh - 120px);
    }

    /* ── Flash message ── */
    .flash-wrap { margin-bottom: 20px; }

    /* ── Stat cards ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
        padding: 22px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow .2s, transform .2s;
        animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
    }

    .stat-card:hover { box-shadow: 0 6px 24px rgba(18,43,20,.09); transform: translateY(-2px); }
    .stat-card:nth-child(1) { animation-delay: .04s; }
    .stat-card:nth-child(2) { animation-delay: .08s; }
    .stat-card:nth-child(3) { animation-delay: .12s; }
    .stat-card:nth-child(4) { animation-delay: .16s; }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .stat-icon-wrap {
        width: 46px; height: 46px;
        border-radius: 11px;
        display: grid;
        place-items: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .stat-icon-wrap.slate  { background: #f1f5f9; }
    .stat-icon-wrap.amber  { background: #fff8e1; }
    .stat-icon-wrap.green  { background: #e8f5e9; }
    .stat-icon-wrap.blue   { background: #e3f2fd; }

    .stat-label {
        font-family: 'Syne', sans-serif;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--gray);
        margin-bottom: 3px;
    }

    .stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--forest);
        letter-spacing: -.04em;
        line-height: 1;
    }

    .stat-value.alert { color: #c62828; }
    .stat-value.success { color: #2e7d32; }

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
        font-size: 1rem;
        font-weight: 800;
        color: var(--forest);
        letter-spacing: -.02em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title::before {
        content: '';
        width: 3px; height: 18px;
        background: var(--gold);
        border-radius: 2px;
    }

    .toolbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* filter pills */
    .filter-pills {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .pill {
        font-family: 'Syne', sans-serif;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 6px 14px;
        border-radius: 100px;
        border: 1.5px solid rgba(30,68,34,.14);
        background: #fff;
        color: var(--gray);
        cursor: pointer;
        transition: all .18s;
    }

    .pill:hover, .pill.active {
        background: var(--forest);
        color: #fff;
        border-color: var(--forest);
    }

    /* search */
    .search-wrap { position: relative; }

    .search-wrap .search-icon {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        opacity: .4;
        pointer-events: none;
    }

    #searchIssues {
        font-family: 'Literata', serif;
        font-size: .85rem;
        font-style: italic;
        padding: 8px 14px 8px 34px;
        background: #fff;
        border: 1.5px solid rgba(30,68,34,.12);
        border-radius: 8px;
        color: var(--ink);
        outline: none;
        width: 220px;
        transition: border-color .2s, box-shadow .2s;
    }

    #searchIssues::placeholder { color: #bbb; }
    #searchIssues:focus {
        border-color: var(--moss);
        box-shadow: 0 0 0 3px rgba(61,122,68,.08);
    }

    /* ── Table card ── */
    .table-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
        overflow: hidden;
        animation: cardIn .5s .2s cubic-bezier(.22,1,.36,1) both;
    }

    #issueTable {
        width: 100%;
        border-collapse: collapse;
    }

    #issueTable thead tr { background: var(--forest); }

    #issueTable thead th {
        font-family: 'Syne', sans-serif;
        font-size: .67rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(255,255,255,.7);
        padding: 14px 18px;
        text-align: left;
        white-space: nowrap;
    }

    #issueTable tbody tr {
        border-bottom: 1px solid rgba(30,68,34,.06);
        transition: background .15s;
    }

    #issueTable tbody tr:last-child { border-bottom: none; }
    #issueTable tbody tr:hover { background: var(--fog); }

    #issueTable tbody td {
        font-size: .86rem;
        color: var(--ink);
        padding: 13px 18px;
        font-family: 'Literata', serif;
        vertical-align: middle;
    }

    /* ID column */
    .issue-id {
        font-family: 'Syne', sans-serif;
        font-size: .72rem;
        font-weight: 700;
        color: var(--gray);
        background: var(--fog);
        padding: 3px 8px;
        border-radius: 5px;
        letter-spacing: .04em;
    }

    /* category + room bold */
    #issueTable tbody td.td-room {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: .84rem;
        color: var(--forest);
    }

    /* ── Badges ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-family: 'Syne', sans-serif;
        font-size: .63rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 100px;
        white-space: nowrap;
    }

    .badge::before {
        content: '';
        width: 5px; height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Status */
    .badge-pending     { background: #fff8e1; color: #e65100; }
    .badge-pending::before     { background: #ff9800; }
    .badge-resolved    { background: #e8f5e9; color: #2e7d32; }
    .badge-resolved::before    { background: #4caf50; }
    .badge-inprogress  { background: #e3f2fd; color: #1565c0; }
    .badge-inprogress::before  { background: #2196f3; }
    .badge-default     { background: #f5f5f5; color: #666; }
    .badge-default::before     { background: #999; }

    /* Priority */
    .badge-high   { background: #ffebee; color: #c62828; }
    .badge-high::before   { background: #f44336; }
    .badge-medium { background: #fff8e1; color: #ef6c00; }
    .badge-medium::before { background: #ff9800; }
    .badge-low    { background: #e8f5e9; color: #2e7d32; }
    .badge-low::before    { background: #4caf50; }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 52px 20px;
        color: var(--gray);
    }

    .empty-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: .5; }
    .empty-state p { font-size: .9rem; font-style: italic; margin-bottom: 16px; }

    .empty-link {
        font-family: 'Syne', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 10px 22px;
        background: var(--forest);
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        transition: background .2s;
    }

    .empty-link:hover { background: var(--canopy); }
</style>

<div class="dash-wrap">

    <!-- ── Page header ── -->
    <div class="dash-header">
        <div>
            <div class="dash-eyebrow">Teacher Portal</div>
            <div class="dash-title">Issue Status</div>
        </div>
        <div class="dash-date">
            <span class="live-dot"></span>
            <?php echo date('l, F j, Y'); ?>
        </div>
        <a class="report-btn" href="../teacher/report_issue.php">
            ＋ Report New Issue
        </a>
    </div>

    <!-- ── Body ── -->
    <div class="dash-body">

        <!-- Flash messages -->
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="flash-wrap"><?php echo $flash; ?></div>
        <?php endif; ?>

        <!-- Stat cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrap slate">📋</div>
                <div>
                    <div class="stat-label">Total Issues</div>
                    <div class="stat-value"><?php echo $total; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap amber">⏳</div>
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value <?php echo $pending > 0 ? 'alert' : ''; ?>"><?php echo $pending; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap blue">🔄</div>
                <div>
                    <div class="stat-label">In Progress</div>
                    <div class="stat-value"><?php echo $in_progress; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap green">✅</div>
                <div>
                    <div class="stat-label">Resolved</div>
                    <div class="stat-value <?php echo $resolved > 0 ? 'success' : ''; ?>"><?php echo $resolved; ?></div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="section-title">All Reported Issues</div>
            <div class="toolbar-right">
                <div class="filter-pills">
                    <button class="pill active" onclick="filterTable('all', this)">All</button>
                    <button class="pill" onclick="filterTable('pending', this)">Pending</button>
                    <button class="pill" onclick="filterTable('in progress', this)">In Progress</button>
                    <button class="pill" onclick="filterTable('resolved', this)">Resolved</button>
                </div>
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchIssues" placeholder="Search issues…">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-card">
            <table id="issueTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Campus</th>
                        <th>Room</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Reported At</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($all_issues)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <p>No issues reported yet.</p>
                            <a class="empty-link" href="../teacher/report_issue.php">Report an Issue →</a>
                        </div>
                    </td>
                </tr>
                <?php else: foreach ($all_issues as $i):
                    $status   = $i['status'];
                    $priority = $i['priority'] ?? '';

                    $statusClass = match(strtolower($status)) {
                        'pending'     => 'badge-pending',
                        'resolved'    => 'badge-resolved',
                        'in progress' => 'badge-inprogress',
                        default       => 'badge-default',
                    };

                    $priorityClass = match(strtolower($priority)) {
                        'high'   => 'badge-high',
                        'medium' => 'badge-medium',
                        'low'    => 'badge-low',
                        default  => 'badge-default',
                    };
                ?>
                <tr data-status="<?php echo strtolower($status); ?>">
                    <td><span class="issue-id">#<?php echo htmlspecialchars($i['id']); ?></span></td>
                    <td><?php echo htmlspecialchars($i['campus']); ?></td>
                    <td class="td-room"><?php echo htmlspecialchars($i['room']); ?></td>
                    <td><?php echo htmlspecialchars($i['category']); ?></td>
                    <td><span class="badge <?php echo $priorityClass; ?>"><?php echo htmlspecialchars($priority); ?></span></td>
                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                    <td><?php echo date("M d, Y · g:ia", strtotime($i['created_at'])); ?></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /.dash-body -->
</div><!-- /.dash-wrap -->

<script>
    // ── Filter pills ──
    function filterTable(status, btn) {
        document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('#issueTable tbody tr').forEach(row => {
            if (status === 'all') {
                row.style.display = '';
            } else {
                const rowStatus = (row.dataset.status || '').toLowerCase();
                row.style.display = rowStatus.includes(status) ? '' : 'none';
            }
        });
    }

    // ── Search with debounce ──
    let debounce;
    document.getElementById('searchIssues').addEventListener('keyup', function () {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            const filter = this.value.toLowerCase();

            // reset pills
            document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
            document.querySelector('.pill[onclick*="all"]').classList.add('active');

            document.querySelectorAll('#issueTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        }, 200);
    });
</script>

<?php include("../includes/footer.php"); ?>