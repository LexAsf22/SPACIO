<?php
include("../includes/header.php");
checkRole('teacher');

$user_id = $_SESSION['user']['id'];
$user    = $_SESSION['user'];

// ── AJAX handler ──────────────────────────────────────────
if (isset($_GET['fetch_stats'])) {
    $total_issues = $conn->query("SELECT * FROM issues WHERE user_id=$user_id AND status='Pending'")->num_rows;

    $recent_reservations = $conn->query("
        SELECT r.*, l.lab_name, u.name as student_name
        FROM reservations r
        JOIN laboratories l ON r.lab_id = l.id
        JOIN users u ON r.user_id = u.id
        ORDER BY r.date DESC
        LIMIT 5
    ");

    $rows = [];
    while ($r = $recent_reservations->fetch_assoc()) {
        $rows[] = [
            "student" => $r['student_name'],
            "lab"     => $r['lab_name'],
            "date"    => date("F d, Y", strtotime($r['date'])),
            "time"    => $r['time_slot'],
            "status"  => $r['status'],
        ];
    }

    echo json_encode([
        "issues"        => $total_issues,
        "recent_count"  => count($rows),
        "rows"          => $rows,
    ]);
    exit;
}

// ── Normal page load ──────────────────────────────────────
$total_issues = $conn->query("SELECT * FROM issues WHERE user_id=$user_id AND status='Pending'")->num_rows;

$recent_reservations = $conn->query("
    SELECT r.*, l.lab_name, u.name as student_name
    FROM reservations r
    JOIN laboratories l ON r.lab_id = l.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.date DESC
    LIMIT 5
");

$recent_count = $recent_reservations->num_rows;
?>

<style>
    /* ── Google Fonts ── */
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Literata:ital,wght@0,300;0,400;1,300&display=swap');

    /* ── Variables (shared with student dashboard) ── */
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
        --white6:  rgba(255,255,255,.06);
        --white12: rgba(255,255,255,.12);
    }

    .dash-wrap {
        font-family: 'Literata', Georgia, serif;
        color: var(--ink);
        padding: 0;
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

    .dash-title span {
        color: var(--moss);
        font-style: italic;
        font-family: 'Literata', serif;
        font-weight: 300;
    }

    .dash-date {
        font-size: .8rem;
        color: var(--gray);
        font-style: italic;
    }

    .refresh-btn {
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
        transition: background .2s, transform .15s;
    }

    .refresh-btn:hover { background: var(--canopy); transform: translateY(-1px); }
    .refresh-icon { font-size: .9rem; transition: transform .5s; }
    .refresh-btn.spinning .refresh-icon { animation: spin .6s linear infinite; }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Main body ── */
    .dash-body {
        padding: 32px 36px;
        background: var(--cream);
        min-height: calc(100vh - 120px);
    }

    /* ── Stat cards ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 36px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
        padding: 26px 28px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: box-shadow .2s, transform .2s;
        animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
    }

    .stat-card:hover {
        box-shadow: 0 6px 24px rgba(18,43,20,.09);
        transform: translateY(-2px);
    }

    .stat-card:nth-child(1) { animation-delay: .05s; }
    .stat-card:nth-child(2) { animation-delay: .10s; }
    .stat-card:nth-child(3) { animation-delay: .15s; }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .stat-icon-wrap {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stat-icon-wrap.red    { background: #ffebee; }
    .stat-icon-wrap.amber  { background: #fff8e1; }
    .stat-icon-wrap.blue   { background: #e3f2fd; }

    .stat-label {
        font-family: 'Syne', sans-serif;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--gray);
        margin-bottom: 4px;
    }

    .stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        color: var(--forest);
        letter-spacing: -.04em;
        line-height: 1;
    }

    /* alert colour for pending issues */
    .stat-value.alert { color: #c62828; }

    .stat-sub {
        font-size: .75rem;
        color: var(--gray);
        font-style: italic;
        margin-top: 4px;
    }

    /* ── Section header ── */
    .section-head {
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

    /* ── Quick actions ── */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-bottom: 36px;
    }

    .qa-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        background: #fff;
        border: 1.5px solid rgba(30,68,34,.1);
        border-radius: 12px;
        text-decoration: none;
        transition: border-color .2s, background .2s, transform .15s, box-shadow .2s;
        animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
    }

    .qa-btn:nth-child(1) { animation-delay: .05s; }
    .qa-btn:nth-child(2) { animation-delay: .10s; }
    .qa-btn:nth-child(3) { animation-delay: .15s; }

    .qa-btn:hover {
        border-color: var(--moss);
        background: var(--fog);
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(18,43,20,.08);
    }

    .qa-icon {
        width: 36px; height: 36px;
        background: var(--fog);
        border-radius: 9px;
        display: grid;
        place-items: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .qa-label {
        font-family: 'Syne', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        color: var(--forest);
        display: block;
        margin-bottom: 1px;
    }

    .qa-sub {
        font-size: .7rem;
        color: var(--gray);
        font-style: italic;
    }

    /* ── Search ── */
    .search-wrap { position: relative; }

    .search-wrap .search-icon {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        opacity: .4;
        pointer-events: none;
    }

    #searchRes {
        font-family: 'Literata', serif;
        font-size: .85rem;
        font-style: italic;
        padding: 9px 14px 9px 36px;
        background: #fff;
        border: 1.5px solid rgba(30,68,34,.12);
        border-radius: 8px;
        color: var(--ink);
        outline: none;
        width: 240px;
        transition: border-color .2s, box-shadow .2s;
    }

    #searchRes::placeholder { color: #bbb; }

    #searchRes:focus {
        border-color: var(--moss);
        box-shadow: 0 0 0 3px rgba(61,122,68,.08);
    }

    /* ── Table ── */
    .table-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
        overflow: hidden;
        animation: cardIn .5s .2s cubic-bezier(.22,1,.36,1) both;
    }

    #recentTable {
        width: 100%;
        border-collapse: collapse;
    }

    #recentTable thead tr { background: var(--forest); }

    #recentTable thead th {
        font-family: 'Syne', sans-serif;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(255,255,255,.7);
        padding: 14px 20px;
        text-align: left;
    }

    #recentTable tbody tr {
        border-bottom: 1px solid rgba(30,68,34,.06);
        transition: background .15s;
    }

    #recentTable tbody tr:last-child { border-bottom: none; }
    #recentTable tbody tr:hover { background: var(--fog); }

    #recentTable tbody td {
        font-size: .87rem;
        color: var(--ink);
        padding: 14px 20px;
        font-family: 'Literata', serif;
    }

    #recentTable tbody td:first-child {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: .85rem;
        color: var(--forest);
    }

    /* ── Status badges ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-family: 'Syne', sans-serif;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 100px;
    }

    .badge::before {
        content: '';
        width: 5px; height: 5px;
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

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 52px 20px;
        color: var(--gray);
    }

    .empty-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: .5; }
    .empty-state p { font-size: .9rem; font-style: italic; }

    /* ── Live indicator ── */
    .live-dot {
        display: inline-block;
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #4caf50;
        animation: pulse 2s infinite;
        margin-right: 2px;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(76,175,80,.5); }
        50%       { box-shadow: 0 0 0 5px rgba(76,175,80,0); }
    }

    /* ── Alert banner (issues > 0) ── */
    .alert-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff8e1;
        border: 1px solid #ffe082;
        border-left: 4px solid var(--gold);
        border-radius: 10px;
        padding: 13px 18px;
        margin-bottom: 24px;
        font-size: .85rem;
        color: #5d4037;
        animation: cardIn .4s .3s cubic-bezier(.22,1,.36,1) both;
    }

    .alert-banner a {
        margin-left: auto;
        font-family: 'Syne', sans-serif;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--forest);
        text-decoration: none;
        padding: 6px 14px;
        border: 1.5px solid var(--forest);
        border-radius: 7px;
        transition: background .2s, color .2s;
    }

    .alert-banner a:hover { background: var(--forest); color: #fff; }
</style>

<div class="dash-wrap">

    <!-- ── Page header ── -->
    <div class="dash-header">
        <div class="dash-header-left">
            <div class="dash-eyebrow">Teacher Portal</div>
            <div class="dash-title">
                Good <?php
                    $h = (int)date('H');
                    echo $h < 12 ? 'morning' : ($h < 17 ? 'afternoon' : 'evening');
                ?>, <span><?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?></span>
            </div>
        </div>
        <div>
            <div class="dash-date">
                <span class="live-dot"></span>
                <?php echo date('l, F j, Y'); ?>
            </div>
        </div>
        <button class="refresh-btn" id="refreshBtn" onclick="refreshDashboard()">
            <span class="refresh-icon">↻</span> Refresh
        </button>
    </div>

    <!-- ── Body ── -->
    <div class="dash-body">

        <!-- Alert banner (only shows if pending issues exist) -->
        <?php if ($total_issues > 0): ?>
        <div class="alert-banner">
            <span style="font-size:1.1rem;">⚠️</span>
            <span>You have <strong><?php echo $total_issues; ?> pending issue<?php echo $total_issues > 1 ? 's' : ''; ?></strong> awaiting resolution.</span>
            <a href="../teacher/issue_status.php">View Issues →</a>
        </div>
        <?php endif; ?>

        <!-- Stat cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrap red">⚠️</div>
                <div class="stat-body">
                    <div class="stat-label">Pending Issues</div>
                    <div class="stat-value <?php echo $total_issues > 0 ? 'alert' : ''; ?>" id="pendingIssues"><?php echo $total_issues; ?></div>
                    <div class="stat-sub">Reported by you</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrap amber">📋</div>
                <div class="stat-body">
                    <div class="stat-label">Recent Reservations</div>
                    <div class="stat-value" id="recentCount"><?php echo $recent_count; ?></div>
                    <div class="stat-sub">Last 5 bookings</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrap blue">🏫</div>
                <div class="stat-body">
                    <div class="stat-label">Campus</div>
                    <div class="stat-value" style="font-size:1.1rem; letter-spacing:-.01em;"><?php echo htmlspecialchars($user['campus']); ?></div>
                    <div class="stat-sub"><?php echo htmlspecialchars($user['department'] ?? 'Faculty'); ?></div>
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="section-head" style="margin-bottom:14px;">
            <div class="section-title">Quick Actions</div>
        </div>

        <div class="quick-actions">
            <a class="qa-btn" href="../teacher/lab_usage.php">
                <div class="qa-icon">📊</div>
                <div class="qa-text">
                    <span class="qa-label">Lab Usage</span>
                    <span class="qa-sub">View utilisation</span>
                </div>
            </a>
            <a class="qa-btn" href="../teacher/report_issue.php">
                <div class="qa-icon">⚠️</div>
                <div class="qa-text">
                    <span class="qa-label">Report Issue</span>
                    <span class="qa-sub">Log a problem</span>
                </div>
            </a>
            <a class="qa-btn" href="../teacher/issue_status.php">
                <div class="qa-icon">🔍</div>
                <div class="qa-text">
                    <span class="qa-label">Issue Status</span>
                    <span class="qa-sub">Track reports</span>
                </div>
            </a>
        </div>

        <!-- Recent reservations table -->
        <div class="section-head">
            <div class="section-title">Recent Lab Reservations</div>
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchRes" placeholder="Search student or lab…">
            </div>
        </div>

        <div class="table-card">
            <table id="recentTable">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Laboratory</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $hasRows = false;
                while ($r = $recent_reservations->fetch_assoc()):
                    $hasRows = true;
                    $status = $r['status'];
                    $badgeClass = match(strtolower($status)) {
                        'approved' => 'badge-approved',
                        'pending'  => 'badge-pending',
                        'rejected' => 'badge-rejected',
                        default    => 'badge-default',
                    };
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['lab_name']); ?></td>
                    <td><?php echo date("F d, Y", strtotime($r['date'])); ?></td>
                    <td><?php echo htmlspecialchars($r['time_slot']); ?></td>
                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                </tr>
                <?php endwhile; ?>

                <?php if (!$hasRows): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <p>No recent reservations found.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /.dash-body -->
</div><!-- /.dash-wrap -->

<script>
    function badgeClass(status) {
        const s = status.toLowerCase();
        if (s === 'approved') return 'badge badge-approved';
        if (s === 'pending')  return 'badge badge-pending';
        if (s === 'rejected') return 'badge badge-rejected';
        return 'badge badge-default';
    }

    function refreshDashboard() {
        const btn = document.getElementById('refreshBtn');
        btn.classList.add('spinning');

        fetch(window.location.pathname + '?fetch_stats=1')
            .then(res => res.json())
            .then(data => {
                const issueEl = document.getElementById('pendingIssues');
                issueEl.textContent = data.issues;
                issueEl.className   = 'stat-value' + (data.issues > 0 ? ' alert' : '');

                document.getElementById('recentCount').textContent = data.recent_count;

                const tbody = document.querySelector('#recentTable tbody');
                tbody.innerHTML = '';

                if (data.rows.length === 0) {
                    tbody.innerHTML = `
                        <tr><td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon">📭</div>
                                <p>No recent reservations found.</p>
                            </div>
                        </td></tr>`;
                } else {
                    data.rows.forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${r.student}</td>
                            <td>${r.lab}</td>
                            <td>${r.date}</td>
                            <td>${r.time}</td>
                            <td><span class="${badgeClass(r.status)}">${r.status}</span></td>`;
                        tbody.appendChild(tr);
                    });
                }
            })
            .catch(() => {})
            .finally(() => {
                setTimeout(() => btn.classList.remove('spinning'), 600);
            });
    }

    setInterval(refreshDashboard, 60000);

    let debounce;
    document.getElementById('searchRes').addEventListener('keyup', function () {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#recentTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        }, 200);
    });
</script>

<?php include("../includes/footer.php"); ?>