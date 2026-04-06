<?php
// frontend/student/reserve_lab.php
include("../includes/header.php");
checkRole('student');

$user_id = (int) $_SESSION['user']['id'];

if (isset($_POST['reserve'])) {
    $lab_id    = (int)  $_POST['lab'];
    $date      = trim(  $_POST['date']      ?? '');
    $time_slot = trim(  $_POST['time_slot'] ?? '');

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
            $msg = $result['data']['detail'] ?? $result['data']['message'] ?? "Something went wrong. Please try again.";
            setFlash($msg, "error");
        }
    }

    echo "<script>window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
}

$labsResult = djangoGet('/api/v1/labs/');
$labs_by_campus = [];

if ($labsResult['success'] && isset($labsResult['data'])) {
    foreach ($labsResult['data'] as $lab) {
        $campus = $lab['campus'] ?? 'Other';
        $labs_by_campus[$campus][] = $lab;
    }
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Reserve a Lab</h1>
        <p class="page-subtitle">Book a laboratory for your class or study session.</p>
    </div>
    <a href="/spacio/frontend/student/my_reservations.php" class="btn btn-secondary">📋 My Reservations</a>
</div>

<?php echo getFlash(); ?>

<div style="max-width:820px; margin:0 auto;">

    <!-- Quick nav cards -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
        <div class="stat-card" style="border:2px solid var(--green-500); background:var(--green-50);">
            <div class="stat-icon green">🔬</div>
            <div class="stat-body">
                <div class="stat-label">Currently Booking</div>
                <div style="font-size:.85rem; color:var(--green-700); font-weight:600; margin-top:2px;">Lab Reservation</div>
            </div>
        </div>
        <a href="/spacio/frontend/student/reserve_equipment.php" class="stat-card" style="text-decoration:none; transition:box-shadow .15s, transform .15s;" onmouseover="this.style.boxShadow='var(--shadow-md)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
            <div class="stat-icon blue">🖥️</div>
            <div class="stat-body">
                <div class="stat-label">Switch to</div>
                <div style="font-size:.85rem; color:var(--green-600); font-weight:600; margin-top:2px;">Reserve Equipment →</div>
            </div>
        </a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start;">

        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">🔬 New Lab Reservation</span>
            </div>
            <div class="card-body">
                <form method="POST" id="reserveLabForm">
                    <div class="form-group">
                        <label class="form-label required">Select Lab</label>
                        <select name="lab" required class="form-control">
                            <option value="">-- Choose a Lab --</option>
                            <?php foreach ($labs_by_campus as $campus => $labs): ?>
                                <optgroup label="── <?php echo htmlspecialchars($campus); ?> ──">
                                    <?php foreach ($labs as $lab): ?>
                                        <option value="<?php echo (int) $lab['id']; ?>">
                                            <?php echo htmlspecialchars($lab['lab_name'] ?? $lab['name'] ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Date</label>
                        <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required class="form-control">
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
                    <div id="availabilityMsg" class="availability-msg"></div>
                    <button name="reserve" type="submit" class="btn btn-primary btn-full">Submit Reservation</button>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="display:flex; flex-direction:column; gap:16px;">
            <div class="card">
                <div class="card-header"><span class="card-title">📌 Guidelines</span></div>
                <div class="card-body" style="padding-top:12px;">
                    <ul style="color:var(--gray-600); font-size:.84rem; line-height:2.2; padding-left:16px; margin:0;">
                        <li>Requires admin approval</li>
                        <li>Book at least 1 day ahead</li>
                        <li>Each slot is 1.5 hours</li>
                        <li>One reservation per slot</li>
                    </ul>
                </div>
            </div>
            <div class="card" style="background:var(--green-50); border-color:var(--green-100);">
                <div class="card-body" style="text-align:center; padding:20px 16px;">
                    <div style="font-size:1.6rem; margin-bottom:6px;">📋</div>
                    <div style="font-weight:600; color:var(--gray-900); margin-bottom:4px; font-size:.88rem;">Track Your Bookings</div>
                    <div class="text-muted text-small" style="margin-bottom:12px;">View all reservation statuses</div>
                    <a href="/spacio/frontend/student/my_reservations.php" class="btn btn-primary btn-full btn-sm">My Reservations →</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('reserveLabForm').addEventListener('submit', function (e) {
    if (!confirm("Submit this lab reservation request?")) e.preventDefault();
});

const labSelect  = document.querySelector('select[name="lab"]');
const dateInput  = document.querySelector('input[name="date"]');
const slotSelect = document.querySelector('select[name="time_slot"]');
const msg        = document.getElementById('availabilityMsg');

function checkAvailability() {
    const lab  = labSelect.value;
    const date = dateInput.value;
    const slot = slotSelect.value;
    if (!lab || !date || !slot) { msg.className = 'availability-msg'; return; }
    fetch(`http://localhost:8000/api/v1/availability/labs/?lab_id=${lab}&date=${date}&time_slot=${encodeURIComponent(slot)}`, {
        headers: { 'Authorization': 'Bearer <?php echo $_SESSION["jwt"] ?? ""; ?>' }
    })
    .then(res => res.json())
    .then(data => {
        msg.className = 'availability-msg ' + (data.available ? 'available' : 'unavailable');
        msg.textContent = data.available ? '✓ This slot is available!' : '✗ Already booked. Choose another.';
    })
    .catch(() => { msg.className = 'availability-msg'; });
}

labSelect.addEventListener('change', checkAvailability);
dateInput.addEventListener('change', checkAvailability);
slotSelect.addEventListener('change', checkAvailability);
</script>

<?php include("../includes/footer.php"); ?>