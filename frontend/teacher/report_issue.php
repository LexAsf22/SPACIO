<?php
include("../includes/header.php");
include(__DIR__ . "/../../backend/config/helpers.php");
checkRole('teacher');

$user    = $_SESSION['user'];
$user_id = $user['id'];

// Handle issue submission
if (isset($_POST['report'])) {
    $campus   = $_POST['campus'];
    $room     = $_POST['room'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $notes    = trim($_POST['notes'] ?? '');

    $stmt = $conn->prepare("INSERT INTO issues(user_id,campus,room,category,priority,notes,status,created_at)
        VALUES(?,?,?,?,?,?,'Pending',NOW())");
    $stmt->bind_param("isssss", $user_id, $campus, $room, $category, $priority, $notes);
    $stmt->execute();

    setFlash("Issue reported successfully!", "success");
    header("Location: issue_status.php");
    exit;
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

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-family: 'Syne', sans-serif;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 9px 18px;
        background: transparent;
        color: var(--forest);
        border: 1.5px solid rgba(30,68,34,.25);
        border-radius: 8px;
        text-decoration: none;
        transition: background .2s, border-color .2s;
    }

    .back-btn:hover {
        background: var(--fog);
        border-color: var(--moss);
    }

    /* ── Body ── */
    .dash-body {
        padding: 40px 36px;
        background: var(--cream);
        min-height: calc(100vh - 110px);
        display: flex;
        gap: 36px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    /* ── Form card ── */
    .form-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.09);
        border-radius: 16px;
        padding: 36px 36px 40px;
        flex: 1 1 460px;
        max-width: 600px;
        animation: cardIn .5s .05s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .form-card-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--forest);
        letter-spacing: -.02em;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-card-title::before {
        content: '';
        width: 3px; height: 18px;
        background: var(--gold);
        border-radius: 2px;
        flex-shrink: 0;
    }

    .form-card-sub {
        font-size: .83rem;
        color: var(--gray);
        font-style: italic;
        margin-bottom: 28px;
        padding-left: 11px;
    }

    /* ── Flash ── */
    .flash-wrap { margin-bottom: 22px; }

    /* ── Field groups ── */
    .field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 500px) { .field-row { grid-template-columns: 1fr; } }

    .field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 18px;
    }

    .field:last-child { margin-bottom: 0; }

    .field label {
        font-family: 'Syne', sans-serif;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--moss);
    }

    .field label .req {
        color: #e53935;
        margin-left: 2px;
    }

    .field input,
    .field select,
    .field textarea {
        font-family: 'Literata', serif;
        font-size: .9rem;
        color: var(--ink);
        background: var(--cream);
        border: 1.5px solid rgba(30,68,34,.14);
        border-radius: 9px;
        padding: 11px 14px;
        outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
        appearance: none;
        -webkit-appearance: none;
        width: 100%;
    }

    .field select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7c6d' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .field textarea {
        resize: vertical;
        min-height: 90px;
        font-style: italic;
    }

    .field input::placeholder,
    .field textarea::placeholder { color: #bbb; font-style: italic; }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        border-color: var(--moss);
        box-shadow: 0 0 0 3px rgba(61,122,68,.09);
        background: #fff;
    }

    .field input.error,
    .field select.error { border-color: #e53935; box-shadow: 0 0 0 3px rgba(229,57,53,.08); }

    .field-hint {
        font-size: .72rem;
        color: var(--gray);
        font-style: italic;
    }

    /* ── Priority selector ── */
    .priority-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .priority-opt { display: none; }

    .priority-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 14px 10px;
        border: 1.5px solid rgba(30,68,34,.12);
        border-radius: 11px;
        cursor: pointer;
        background: var(--cream);
        transition: all .18s;
        text-align: center;
    }

    .priority-label:hover { background: var(--fog); border-color: var(--moss); }

    .priority-opt:checked + .priority-label {
        border-width: 2px;
    }

    .priority-opt[value="Low"]:checked    + .priority-label { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
    .priority-opt[value="Medium"]:checked + .priority-label { background: #fff8e1; border-color: #ff9800; color: #e65100; }
    .priority-opt[value="High"]:checked   + .priority-label { background: #ffebee; border-color: #f44336; color: #c62828; }

    .priority-icon { font-size: 1.3rem; }

    .priority-name {
        font-family: 'Syne', sans-serif;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    /* ── Submit ── */
    .submit-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid rgba(30,68,34,.08);
    }

    .submit-note {
        font-size: .78rem;
        color: var(--gray);
        font-style: italic;
    }

    .submit-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'Syne', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 12px 26px;
        background: var(--forest);
        color: #fff;
        border: none;
        border-radius: 9px;
        cursor: pointer;
        transition: background .2s, transform .15s, box-shadow .2s;
    }

    .submit-btn:hover {
        background: var(--canopy);
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(18,43,20,.18);
    }

    .submit-btn:active { transform: translateY(0); }

    /* ── Info sidebar ── */
    .info-sidebar {
        flex: 0 0 260px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        animation: cardIn .5s .15s cubic-bezier(.22,1,.36,1) both;
    }

    .info-block {
        background: #fff;
        border: 1px solid rgba(30,68,34,.09);
        border-radius: 14px;
        padding: 20px 22px;
    }

    .info-block-title {
        font-family: 'Syne', sans-serif;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--moss);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(30,68,34,.06);
    }

    .info-item:last-child { border-bottom: none; padding-bottom: 0; }

    .info-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 5px;
    }

    .dot-low    { background: #4caf50; }
    .dot-medium { background: #ff9800; }
    .dot-high   { background: #f44336; }

    .info-item-text {}

    .info-item-label {
        font-family: 'Syne', sans-serif;
        font-size: .72rem;
        font-weight: 700;
        color: var(--forest);
        margin-bottom: 2px;
    }

    .info-item-desc {
        font-size: .78rem;
        color: var(--gray);
        font-style: italic;
        line-height: 1.4;
    }

    .info-tip {
        display: flex;
        gap: 10px;
        font-size: .8rem;
        color: var(--gray);
        line-height: 1.5;
        padding: 10px 0;
        border-bottom: 1px solid rgba(30,68,34,.06);
    }

    .info-tip:last-child { border-bottom: none; padding-bottom: 0; }
    .info-tip-icon { flex-shrink: 0; }
</style>

<div class="dash-wrap">

    <!-- ── Page header ── -->
    <div class="dash-header">
        <div>
            <div class="dash-eyebrow">Teacher Portal</div>
            <div class="dash-title">Report an Issue</div>
        </div>
        <a class="back-btn" href="issue_status.php">← Back to Issues</a>
    </div>

    <!-- ── Body ── -->
    <div class="dash-body">

        <!-- ── Form card ── -->
        <div class="form-card">
            <div class="form-card-title">Issue Details</div>
            <p class="form-card-sub">Fill in the details below. Your report will be submitted as <em>Pending</em> and reviewed by admin.</p>

            <!-- Flash -->
            <?php $flash = getFlash(); if ($flash): ?>
            <div class="flash-wrap"><?php echo $flash; ?></div>
            <?php endif; ?>

            <form method="POST" id="issueForm" novalidate>

                <!-- Campus & Room -->
                <div class="field-row">
                    <div class="field">
                        <label for="campus">Campus <span class="req">*</span></label>
                        <select name="campus" id="campus" required>
                            <option value="">Select campus…</option>
                            <option value="Campus A">Campus A</option>
                            <option value="Campus B">Campus B</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="room">Room / Lab <span class="req">*</span></label>
                        <input type="text" name="room" id="room" placeholder="e.g. Lab 3, Room 201" required>
                    </div>
                </div>

                <!-- Category -->
                <div class="field">
                    <label for="category">Issue Category <span class="req">*</span></label>
                    <select name="category" id="category" required>
                        <option value="">Select a category…</option>
                        <option value="Equipment">🔧 Equipment</option>
                        <option value="Facility">🏢 Facility</option>
                        <option value="Software">💻 Software</option>
                        <option value="Network">🌐 Network</option>
                        <option value="Other">📋 Other</option>
                    </select>
                </div>

                <!-- Priority -->
                <div class="field">
                    <label>Priority <span class="req">*</span></label>
                    <div class="priority-grid">
                        <div>
                            <input class="priority-opt" type="radio" name="priority" id="p-low" value="Low" required>
                            <label class="priority-label" for="p-low">
                                <span class="priority-icon">🟢</span>
                                <span class="priority-name">Low</span>
                            </label>
                        </div>
                        <div>
                            <input class="priority-opt" type="radio" name="priority" id="p-medium" value="Medium">
                            <label class="priority-label" for="p-medium">
                                <span class="priority-icon">🟡</span>
                                <span class="priority-name">Medium</span>
                            </label>
                        </div>
                        <div>
                            <input class="priority-opt" type="radio" name="priority" id="p-high" value="High">
                            <label class="priority-label" for="p-high">
                                <span class="priority-icon">🔴</span>
                                <span class="priority-name">High</span>
                            </label>
                        </div>
                    </div>
                    <span class="field-hint">Choose the urgency level of this issue.</span>
                </div>

                <!-- Notes -->
                <div class="field">
                    <label for="notes">Additional Notes <span style="color:var(--gray); font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                    <textarea name="notes" id="notes" placeholder="Describe the issue in more detail…"></textarea>
                </div>

                <!-- Submit row -->
                <div class="submit-row">
                    <span class="submit-note">Fields marked <span style="color:#e53935;">*</span> are required.</span>
                    <button class="submit-btn" name="report" type="submit">
                        <span>⚑</span> Submit Report
                    </button>
                </div>

            </form>
        </div>

        <!-- ── Info sidebar ── -->
        <aside class="info-sidebar">

            <div class="info-block">
                <div class="info-block-title">⚡ Priority Guide</div>
                <div class="info-item">
                    <span class="info-dot dot-low"></span>
                    <div class="info-item-text">
                        <div class="info-item-label">Low</div>
                        <div class="info-item-desc">Minor inconvenience, no disruption to classes.</div>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-dot dot-medium"></span>
                    <div class="info-item-text">
                        <div class="info-item-label">Medium</div>
                        <div class="info-item-desc">Affects some students; needs attention soon.</div>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-dot dot-high"></span>
                    <div class="info-item-text">
                        <div class="info-item-label">High</div>
                        <div class="info-item-desc">Blocks lab use or poses a safety concern.</div>
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="info-block-title">📋 Submission Tips</div>
                <div class="info-tip">
                    <span class="info-tip-icon">🏷️</span>
                    <span>Be specific with the room — include the building name if possible.</span>
                </div>
                <div class="info-tip">
                    <span class="info-tip-icon">📸</span>
                    <span>Use the notes field to describe what you observed or any error messages.</span>
                </div>
                <div class="info-tip">
                    <span class="info-tip-icon">🔄</span>
                    <span>Track your report's progress on the <a href="issue_status.php" style="color:var(--moss); font-style:italic;">Issue Status</a> page.</span>
                </div>
            </div>

        </aside>

    </div><!-- /.dash-body -->
</div><!-- /.dash-wrap -->

<script>
    const form = document.getElementById('issueForm');

    // ── Client-side validation ──
    form.addEventListener('submit', function (e) {
        let valid = true;

        // Clear previous errors
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        ['campus', 'room', 'category'].forEach(name => {
            const el = form.querySelector(`[name="${name}"]`);
            if (!el.value.trim()) { el.classList.add('error'); valid = false; }
        });

        if (!form.querySelector('[name="priority"]:checked')) {
            form.querySelectorAll('.priority-label').forEach(l => l.style.borderColor = '#e53935');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            form.querySelector('.error, .priority-label')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Confirm dialog
        if (!confirm('Submit this issue report?')) e.preventDefault();
    });

    // ── Clear error styling on change ──
    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('change', () => el.classList.remove('error'));
    });

    form.querySelectorAll('.priority-opt').forEach(opt => {
        opt.addEventListener('change', () => {
            form.querySelectorAll('.priority-label').forEach(l => l.style.borderColor = '');
        });
    });
</script>

<?php include("../includes/footer.php"); ?>