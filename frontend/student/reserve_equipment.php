<?php
include("../includes/header.php");
include("../../backend/config/helpers.php");
checkRole('student');

// ── Handle submission ──────────────────────────────────────
if (isset($_POST['reserve'])) {
    $equipment_id = (int)$_POST['equipment'];
    $date         = $conn->real_escape_string($_POST['date']);
    $user_id      = (int)$_SESSION['user']['id'];

    $check = $conn->query("SELECT id FROM reservations WHERE equipment_id=$equipment_id AND date='$date'");
    if ($check->num_rows > 0) {
        setFlash("That equipment is already reserved for this date. Please choose another date.", "error");
    } else {
        $conn->query("INSERT INTO reservations (user_id, equipment_id, date, status) VALUES ($user_id, $equipment_id, '$date', 'Pending')");
        setFlash("Equipment reservation submitted successfully! It is now pending approval.", "success");
    }
}

$equipment = $conn->query("SELECT e.*, l.lab_name FROM equipment e JOIN laboratories l ON e.lab_id = l.id ORDER BY l.lab_name, e.equipment_name");
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

    /* ── Body ── */
    .page-body {
        padding: 36px;
        background: var(--cream);
        min-height: calc(100vh - 120px);
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 28px;
        align-items: start;
    }

    /* ── Flash messages ── */
    .flash {
        display: flex; align-items: flex-start; gap: 12px;
        border-radius: 12px; padding: 15px 18px;
        font-size: .87rem; font-style: italic;
        margin-bottom: 24px;
        animation: fadeUp .4s both;
    }

    .flash-icon { font-size: 1.1rem; flex-shrink: 0; }

    .flash.success {
        background: rgba(52,199,89,.1);
        border: 1px solid rgba(52,199,89,.25);
        color: #1b6e30;
    }

    .flash.error {
        background: rgba(220,53,53,.09);
        border: 1px solid rgba(220,53,53,.25);
        color: #c0392b;
    }

    /* ── Form card ── */
    .form-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 16px;
        overflow: hidden;
        animation: cardIn .5s .05s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .form-card-header {
        padding: 24px 28px 20px;
        border-bottom: 1px solid rgba(30,68,34,.07);
    }

    .form-card-title {
        font-family: 'Syne', sans-serif;
        font-size: 1rem; font-weight: 800;
        color: var(--forest); letter-spacing: -.02em;
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 4px;
    }

    .form-card-title::before {
        content: '';
        width: 3px; height: 18px;
        background: var(--gold); border-radius: 2px;
    }

    .form-card-sub {
        font-size: .82rem; color: var(--gray);
        font-style: italic; padding-left: 11px;
    }

    .form-card-body { padding: 28px; }

    /* ── Form fields ── */
    .form-group { margin-bottom: 22px; }

    .form-group label {
        display: block;
        font-family: 'Syne', sans-serif;
        font-size: .68rem; font-weight: 700;
        letter-spacing: .12em; text-transform: uppercase;
        color: var(--gray); margin-bottom: 8px;
    }

    .input-wrap { position: relative; }

    .input-icon {
        position: absolute; left: 14px; top: 50%;
        transform: translateY(-50%);
        font-size: 15px; opacity: .4; pointer-events: none;
    }

    .form-group select,
    .form-group input[type="date"] {
        width: 100%;
        padding: 13px 16px 13px 44px;
        background: var(--cream);
        border: 1.5px solid rgba(30,68,34,.12);
        border-radius: 10px;
        font-family: 'Literata', serif;
        font-size: .92rem; color: var(--ink);
        outline: none; appearance: none;
        transition: border-color .2s, background .2s, box-shadow .2s;
        cursor: pointer;
    }

    .form-group select:focus,
    .form-group input[type="date"]:focus {
        border-color: var(--moss);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(61,122,68,.07);
    }

    /* Select arrow */
    .select-wrap::after {
        content: '▾';
        position: absolute; right: 14px; top: 50%;
        transform: translateY(-50%);
        font-size: .75rem; color: var(--gray);
        pointer-events: none;
    }

    /* Selected equipment preview */
    .eq-preview {
        margin-top: 10px;
        padding: 12px 14px;
        background: var(--fog);
        border: 1px solid rgba(46,107,52,.15);
        border-radius: 9px;
        display: none;
        align-items: center;
        gap: 10px;
        animation: fadeUp .3s both;
    }

    .eq-preview.show { display: flex; }

    .eq-preview-icon {
        width: 34px; height: 34px;
        background: var(--forest); border-radius: 8px;
        display: grid; place-items: center;
        font-size: 15px; flex-shrink: 0;
    }

    .eq-preview-name {
        font-family: 'Syne', sans-serif;
        font-size: .8rem; font-weight: 700;
        color: var(--forest);
    }

    .eq-preview-lab {
        font-size: .74rem; color: var(--gray); font-style: italic;
    }

    /* Date hint */
    .date-hint {
        margin-top: 8px;
        font-size: .75rem; color: var(--gray); font-style: italic;
        padding-left: 2px;
    }

    /* Submit button */
    .btn-submit {
        width: 100%; padding: 15px;
        background: var(--forest); color: #fff;
        border: none; border-radius: 10px;
        font-family: 'Syne', sans-serif;
        font-size: .88rem; font-weight: 800;
        letter-spacing: .07em; text-transform: uppercase;
        cursor: pointer;
        transition: background .2s, transform .15s, box-shadow .2s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        position: relative; overflow: hidden;
        margin-top: 8px;
    }

    .btn-submit::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,.07) 100%);
    }

    .btn-submit:hover {
        background: var(--canopy);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(18,43,20,.18);
    }

    .btn-submit:active { transform: translateY(0); }
    .btn-arrow { transition: transform .2s; }
    .btn-submit:hover .btn-arrow { transform: translateX(4px); }

    /* ── Info sidebar ── */
    .info-stack {
        display: flex; flex-direction: column; gap: 16px;
    }

    .info-card {
        background: #fff;
        border: 1px solid rgba(30,68,34,.08);
        border-radius: 14px;
        padding: 22px 24px;
        animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
    }

    .info-card:nth-child(1) { animation-delay: .1s; }
    .info-card:nth-child(2) { animation-delay: .18s; }

    .info-card-title {
        font-family: 'Syne', sans-serif;
        font-size: .78rem; font-weight: 800;
        color: var(--forest); letter-spacing: -.01em;
        margin-bottom: 14px;
        display: flex; align-items: center; gap: 7px;
    }

    .info-card-title::before {
        content: '';
        width: 3px; height: 14px;
        background: var(--gold); border-radius: 2px;
    }

    .info-steps { list-style: none; display: flex; flex-direction: column; gap: 12px; }

    .info-step {
        display: flex; align-items: flex-start; gap: 11px;
        font-size: .8rem; color: var(--gray); font-style: italic;
        line-height: 1.5;
    }

    .step-num {
        width: 22px; height: 22px; border-radius: 50%;
        border: 1.5px solid rgba(196,154,42,.4);
        display: grid; place-items: center;
        font-family: 'Syne', sans-serif;
        font-size: .6rem; font-weight: 800;
        color: var(--gold); flex-shrink: 0;
    }

    .info-rules { list-style: none; display: flex; flex-direction: column; gap: 10px; }

    .info-rule {
        display: flex; align-items: flex-start; gap: 9px;
        font-size: .78rem; color: var(--gray); font-style: italic; line-height: 1.5;
    }

    .rule-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--moss); margin-top: 5px; flex-shrink: 0;
    }

    /* ── Responsive ── */
    @media (max-width: 860px) {
        .page-body { grid-template-columns: 1fr; }
    }
</style>

<div class="page-wrap">

    <!-- Page header -->
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Student Portal</div>
            <div class="page-title">Reserve Equipment</div>
            <div class="page-sub">Select a piece of equipment and your preferred date to submit a reservation.</div>
        </div>
        <a href="my_reservations.php" style="
            font-family:'Syne',sans-serif; font-size:.75rem; font-weight:700;
            letter-spacing:.08em; text-transform:uppercase;
            padding:9px 18px;
            background:transparent; color:var(--forest);
            border:1.5px solid rgba(30,68,34,.2);
            border-radius:8px; text-decoration:none;
            display:inline-flex; align-items:center; gap:6px;
            transition:border-color .2s, background .2s;
        " onmouseover="this.style.background='var(--fog)'" onmouseout="this.style.background='transparent'">
            ← My Reservations
        </a>
    </div>

    <div class="page-body">

        <!-- Left: Form -->
        <div>
            <?php
            // Render flash if helpers.php provides getFlash()
            $flash = getFlash();
            if ($flash): ?>
            <div class="flash <?= strpos($flash, 'error') !== false ? 'error' : 'success' ?>">
                <span class="flash-icon"><?= strpos($flash, 'error') !== false ? '⚠' : '✓' ?></span>
                <span><?= $flash ?></span>
            </div>
            <?php endif; ?>

            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-title">Reservation Details</div>
                    <div class="form-card-sub">Fill in the form below to submit your request.</div>
                </div>

                <div class="form-card-body">
                    <form method="POST" id="reserveEqForm">

                        <!-- Equipment selector -->
                        <div class="form-group">
                            <label for="equipment">Equipment</label>
                            <div class="input-wrap select-wrap">
                                <span class="input-icon">🔧</span>
                                <select id="equipment" name="equipment" required>
                                    <option value="">Select equipment…</option>
                                    <?php while ($e = $equipment->fetch_assoc()): ?>
                                    <option value="<?= $e['id'] ?>"
                                        data-name="<?= htmlspecialchars($e['equipment_name']) ?>"
                                        data-lab="<?= htmlspecialchars($e['lab_name']) ?>">
                                        <?= htmlspecialchars($e['equipment_name']) ?> — <?= htmlspecialchars($e['lab_name']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Preview -->
                            <div class="eq-preview" id="eqPreview">
                                <div class="eq-preview-icon">🔬</div>
                                <div>
                                    <div class="eq-preview-name" id="previewName"></div>
                                    <div class="eq-preview-lab" id="previewLab"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="form-group">
                            <label for="date">Reservation Date</label>
                            <div class="input-wrap">
                                <span class="input-icon">📅</span>
                                <input type="date" id="date" name="date"
                                    min="<?= date('Y-m-d') ?>"
                                    required>
                            </div>
                            <div class="date-hint">Only today or future dates are allowed.</div>
                        </div>

                        <button type="submit" name="reserve" class="btn-submit">
                            Submit Reservation
                            <span class="btn-arrow">→</span>
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Info sidebar -->
        <div class="info-stack">

            <div class="info-card">
                <div class="info-card-title">How it works</div>
                <ul class="info-steps">
                    <li class="info-step">
                        <div class="step-num">1</div>
                        <span>Choose the equipment you need from the dropdown list.</span>
                    </li>
                    <li class="info-step">
                        <div class="step-num">2</div>
                        <span>Pick your preferred reservation date.</span>
                    </li>
                    <li class="info-step">
                        <div class="step-num">3</div>
                        <span>Submit — your request goes to the admin for approval.</span>
                    </li>
                    <li class="info-step">
                        <div class="step-num">4</div>
                        <span>Check your status in <a href="my_reservations.php" style="color:var(--moss);font-style:normal;font-family:'Syne',sans-serif;font-size:.75rem;font-weight:700;">My Reservations</a>.</span>
                    </li>
                </ul>
            </div>

            <div class="info-card">
                <div class="info-card-title">Guidelines</div>
                <ul class="info-rules">
                    <li class="info-rule"><div class="rule-dot"></div><span>Only one reservation per equipment per day is allowed.</span></li>
                    <li class="info-rule"><div class="rule-dot"></div><span>Reservations must be made at least one day in advance.</span></li>
                    <li class="info-rule"><div class="rule-dot"></div><span>Handle all borrowed equipment with care.</span></li>
                    <li class="info-rule"><div class="rule-dot"></div><span>Return equipment promptly after your session.</span></li>
                </ul>
            </div>

        </div>

    </div>
</div>

<script>
    // ── Equipment preview ──
    const select   = document.getElementById('equipment');
    const preview  = document.getElementById('eqPreview');
    const prevName = document.getElementById('previewName');
    const prevLab  = document.getElementById('previewLab');

    select.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (this.value && opt.dataset.name) {
            prevName.textContent = opt.dataset.name;
            prevLab.textContent  = opt.dataset.lab;
            preview.classList.add('show');
        } else {
            preview.classList.remove('show');
        }
    });

    // ── Confirm on submit ──
    document.getElementById('reserveEqForm').addEventListener('submit', function (e) {
        const eq   = document.getElementById('equipment');
        const date = document.getElementById('date');
        const name = eq.options[eq.selectedIndex]?.dataset.name ?? 'this equipment';

        if (!confirm(`Submit reservation for "${name}" on ${date.value}?`)) {
            e.preventDefault();
        }
    });
</script>

<?php include("../includes/footer.php"); ?>