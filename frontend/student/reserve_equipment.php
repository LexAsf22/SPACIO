<?php
// frontend/student/reserve_equipment.php
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
checkRole('student');

$user_id = (int) $_SESSION['user']['id'];

// ── Handle form submission ────────────────────────────────────────────────────
if (isset($_POST['reserve'])) {
    $equipment_id = (int)  $_POST['equipment'];
    $date         = trim(  $_POST['date']      ?? '');
    $time_slot    = trim(  $_POST['time_slot'] ?? '');

    if ($date < date('Y-m-d')) {
        setFlash("Please select a future date.", "error");
    } else {
        $result = djangoPost('/api/v1/reservations/', [
            'user_id'      => $user_id,
            'equipment_id' => $equipment_id,
            'date'         => $date,
            'time_slot'    => $time_slot,
            'type'         => 'equipment',
        ]);

        if ($result['success']) {
            setFlash("Equipment reservation submitted! Waiting for admin approval.", "success");
        } else {
            $msg = $result['data']['detail'] ?? $result['data']['message'] ?? "Something went wrong. Please try again.";
            setFlash($msg, "error");
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ── Fetch equipment list ──────────────────────────────────────────────────────
$equipResult     = djangoGet('/api/v1/availability/equipment/list/');
$equip_by_campus = [];

if ($equipResult['success'] && isset($equipResult['data'])) {
    foreach ($equipResult['data'] as $e) {
        $campus = $e['campus'] ?? 'Other';
        $equip_by_campus[$campus][] = $e;
    }
}

include("../includes/header.php");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Reserve Equipment</h1>
        <p class="page-subtitle">Borrow equipment for your academic needs.</p>
    </div>
    <a href="/spacio/frontend/student/my_reservations.php" class="btn btn-secondary">📋 My Reservations</a>
</div>

<?php echo getFlash(); ?>

<div style="max-width:820px; margin:0 auto;">

    <!-- Quick nav cards -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
        <a href="/spacio/frontend/student/reserve_lab.php" class="stat-card" style="text-decoration:none; transition:box-shadow .15s, transform .15s;" onmouseover="this.style.boxShadow='var(--shadow-md)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
            <div class="stat-icon green">🔬</div>
            <div class="stat-body">
                <div class="stat-label">Switch to</div>
                <div style="font-size:.85rem; color:var(--green-600); font-weight:600; margin-top:2px;">Reserve a Lab →</div>
            </div>
        </a>
        <div class="stat-card" style="border:2px solid var(--green-500); background:var(--green-50);">
            <div class="stat-icon blue">🖥️</div>
            <div class="stat-body">
                <div class="stat-label">Currently Booking</div>
                <div style="font-size:.85rem; color:var(--green-700); font-weight:600; margin-top:2px;">Equipment Reservation</div>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start;">

        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">🖥️ New Equipment Reservation</span>
            </div>
            <div class="card-body">
                <form method="POST" id="reserveEqForm">

                    <div class="form-group">
                        <label class="form-label required">Select Equipment</label>
                        <select name="equipment" required class="form-control">
                            <option value="">-- Choose Equipment --</option>
                            <?php foreach ($equip_by_campus as $campus => $items): ?>
                                <optgroup label="── <?php echo htmlspecialchars($campus); ?> ──">
                                    <?php foreach ($items as $e): ?>
                                        <option value="<?php echo (int)$e['id']; ?>">
                                            <?php echo htmlspecialchars($e['equipment_name'] ?? ''); ?>
                                            — <?php echo htmlspecialchars($e['lab_name'] ?? ''); ?>
                                            (<?php echo (int)($e['quantity'] ?? 0); ?> available)
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Date Needed</label>
                        <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>"
                               required class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Time Slot</label>
                        <select name="time_slot" required class="form-control">
                            <option value="">-- Choose a Time Slot --</option>
                            <option value="7:30-9:00">7:30 AM – 9:00 AM</option>
                            <option value="9:00-10:30">9:00 AM – 10:30 AM</option>
                            <option value="10:30-12:00">10:30 AM – 12:00 PM</option>
                            <option value="13:00-14:30">1:00 PM – 2:30 PM</option>
                            <option value="14:30-16:00">2:30 PM – 4:00 PM</option>
                        </select>
                    </div>

                    <button name="reserve" type="submit" class="btn btn-primary btn-full">
                        Submit Reservation
                    </button>

                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="display:flex; flex-direction:column; gap:16px;">
            <div class="card">
                <div class="card-header"><span class="card-title">📌 Guidelines</span></div>
                <div class="card-body" style="padding-top:12px;">
                    <ul style="color:var(--ink-500); font-size:.84rem; line-height:2.2; padding-left:16px; margin:0;">
                        <li>Requires admin approval</li>
                        <li>Return equipment same day</li>
                        <li>Handle with care</li>
                        <li>Report damage immediately</li>
                    </ul>
                </div>
            </div>
            <div class="card" style="background:var(--green-50); border-color:var(--green-100);">
                <div class="card-body" style="text-align:center; padding:20px 16px;">
                    <div style="font-size:1.6rem; margin-bottom:6px;">📋</div>
                    <div style="font-weight:600; color:var(--ink-900); margin-bottom:4px; font-size:.88rem;">Track Your Bookings</div>
                    <div class="text-muted text-small" style="margin-bottom:12px;">View all reservation statuses</div>
                    <a href="/spacio/frontend/student/my_reservations.php" class="btn btn-primary btn-full btn-sm">
                        My Reservations →
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('reserveEqForm').addEventListener('submit', function (e) {
    if (!confirm("Submit this equipment reservation?")) e.preventDefault();
});
</script>

<?php include("../includes/footer.php"); ?>