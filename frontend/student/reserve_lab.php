<?php
// frontend/student/reserve_lab.php
include("../includes/header.php");
checkRole('student');

$user_id = (int) $_SESSION['user']['id'];

// ── Handle reservation form submission ────────────────────────────────────────
if (isset($_POST['reserve'])) {
    $lab_id    = (int)   $_POST['lab'];
    $date      =  trim(  $_POST['date']      ?? '');
    $time_slot =  trim(  $_POST['time_slot'] ?? '');

    if ($date < date('Y-m-d')) {
        setFlash("Please select a future date.", "error");
    } else {
        $result = djangoPost('/api/v1/reservations/', [
            'user_id'   => $user_id,
            'lab_id'    => $lab_id,
            'date'      => $date,
            'time_slot' => $time_slot,
            'type'      => 'lab',
        ]);

        if ($result['success']) {
            setFlash("Reservation submitted! Waiting for admin approval.", "success");
        } else {
            $msg = $result['data']['detail']
                ?? $result['data']['message']
                ?? "Something went wrong. Please try again.";
            setFlash($msg, "error");
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ── Fetch labs for dropdown ───────────────────────────────────────────────────
$labsResult = djangoGet('/api/v1/labs/');
$labs_by_campus = [];

if ($labsResult['success'] && isset($labsResult['data'])) {
    foreach ($labsResult['data'] as $lab) {
        $campus = $lab['campus'] ?? 'Other';
        $labs_by_campus[$campus][] = $lab;
    }
}

// ── Fetch student's recent lab reservations ───────────────────────────────────
$recentResult   = djangoGet('/api/v1/reservations/my/?type=lab&limit=5&user_id=' . $user_id);
$recent_rows    = [];

if ($recentResult['success'] && isset($recentResult['data'])) {
    $recent_rows = $recentResult['data']['results'] ?? $recentResult['data'] ?? [];
}
?>

<h2>Reserve a Lab</h2>
<?php echo getFlash(); ?>

<div style="max-width:520px; background:#f9f9f9; padding:24px; border-radius:8px; border:1px solid #ddd;">
    <form method="POST" id="reserveLabForm">

        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">
                Select Lab
            </label>
            <select name="lab" required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="">-- Choose a Lab --</option>
                <?php foreach ($labs_by_campus as $campus => $labs): ?>
                    <optgroup label="── <?php echo htmlspecialchars($campus); ?> ──">
                        <?php foreach ($labs as $lab): ?>
                            <option value="<?php echo (int) $lab['id']; ?>">
                                <?php echo htmlspecialchars($lab['lab_name'] ?? $lab['name'] ?? ''); ?>
                                <?php echo !empty($lab['total_computers'])
                                    ? ' (' . (int) $lab['total_computers'] . ' computers)'
                                    : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">
                Date
            </label>
            <input
                type="date"
                name="date"
                min="<?php echo date('Y-m-d'); ?>"
                required
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;"
            >
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">
                Time Slot
            </label>
            <select name="time_slot" required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="">-- Choose a Time Slot --</option>
                <option value="7:30-9:00">7:30 AM – 9:00 AM</option>
                <option value="9:00-10:30">9:00 AM – 10:30 AM</option>
                <option value="10:30-12:00">10:30 AM – 12:00 PM</option>
                <option value="13:00-14:30">1:00 PM – 2:30 PM</option>
                <option value="14:30-16:00">2:30 PM – 4:00 PM</option>
            </select>
        </div>

        <!-- Live availability checker display -->
        <div id="availabilityMsg" style="
            display:none; padding:10px 14px;
            border-radius:5px; margin-bottom:16px; font-size:.9rem;
        "></div>

        <button
            name="reserve"
            type="submit"
            style="
                width:100%; padding:12px;
                background:#2c5f2e; color:white;
                border:none; border-radius:5px;
                font-size:1rem; cursor:pointer;
                transition: background .2s;
            "
            onmouseover="this.style.background='#1e3d1a'"
            onmouseout="this.style.background='#2c5f2e'"
        >
            Submit Reservation
        </button>

    </form>
</div>

<!-- Recent lab reservations -->
<div style="margin-top:36px;">
    <h3 style="margin-bottom:12px;">Your Recent Lab Reservations</h3>

    <?php if (empty($recent_rows)): ?>
        <p style="color:#888; font-style:italic;">No lab reservations yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0"
               style="border-collapse:collapse; width:100%; max-width:700px;">
            <thead style="background:#2c5f2e; color:white;">
                <tr>
                    <th>Lab</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recent_rows as $r):
                $status = $r['status'] ?? 'Pending';
                $badge  = match($status) {
                    'Approved' => 'background:#d4edda; color:#155724;',
                    'Rejected' => 'background:#f8d7da; color:#721c24;',
                    default    => 'background:#fff3cd; color:#856404;',
                };
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['lab_name'] ?? '—'); ?></td>
                    <td><?php echo isset($r['date']) ? date("F d, Y", strtotime($r['date'])) : '—'; ?></td>
                    <td><?php echo htmlspecialchars($r['time_slot'] ?? '—'); ?></td>
                    <td>
                        <span style="padding:3px 10px; border-radius:12px; font-size:.82rem; <?php echo $badge; ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.getElementById('reserveLabForm').addEventListener('submit', function (e) {
    if (!confirm("Submit this lab reservation request?")) e.preventDefault();
});

// ── Live availability check ───────────────────────────────────────────────────
const labSelect  = document.querySelector('select[name="lab"]');
const dateInput  = document.querySelector('input[name="date"]');
const slotSelect = document.querySelector('select[name="time_slot"]');
const msg        = document.getElementById('availabilityMsg');

function checkAvailability() {
    const lab  = labSelect.value;
    const date = dateInput.value;
    const slot = slotSelect.value;

    if (!lab || !date || !slot) {
        msg.style.display = 'none';
        return;
    }

    fetch(`/spacio/backend/api/check_availability.php?lab_id=${lab}&date=${date}&time_slot=${encodeURIComponent(slot)}`)
        .then(res => res.json())
        .then(data => {
            msg.style.display = 'block';
            if (data.available) {
                msg.style.background = '#d4edda';
                msg.style.color      = '#155724';
                msg.style.border     = '1px solid #c3e6cb';
                msg.textContent      = '✓ This slot is available!';
            } else {
                msg.style.background = '#f8d7da';
                msg.style.color      = '#721c24';
                msg.style.border     = '1px solid #f5c6cb';
                msg.textContent      = '✗ This slot is already booked. Please choose another.';
            }
        })
        .catch(() => { msg.style.display = 'none'; });
}

labSelect.addEventListener('change',  checkAvailability);
dateInput.addEventListener('change',  checkAvailability);
slotSelect.addEventListener('change', checkAvailability);
</script>

<?php include("../includes/footer.php"); ?>