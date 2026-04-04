<?php
// frontend/student/reserve_equipment.php
include("../includes/header.php");
checkRole('student');

$user_id = (int) $_SESSION['user']['id'];

// ── Handle reservation form submission ────────────────────────────────────────
if (isset($_POST['reserve'])) {
    $equipment_id = (int)  $_POST['equipment'];
    $date         =  trim( $_POST['date'] ?? '');

    if ($date < date('Y-m-d')) {
        setFlash("Please select a future date.", "error");
    } else {
        $result = djangoPost('/api/v1/reservations/', [
            'user_id'      => $user_id,
            'equipment_id' => $equipment_id,
            'date'         => $date,
            'type'         => 'equipment',
        ]);

        if ($result['success']) {
            setFlash("Equipment reservation submitted! Waiting for admin approval.", "success");
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

// ── Fetch available equipment for dropdown ────────────────────────────────────
$equipResult    = djangoGet('/api/v1/availability/equipment/');
$equip_by_campus = [];

if ($equipResult['success'] && isset($equipResult['data'])) {
    foreach ($equipResult['data'] as $e) {
        $campus = $e['campus'] ?? 'Other';
        $equip_by_campus[$campus][] = $e;
    }
}

// ── Fetch student's recent equipment reservations ─────────────────────────────
$recentResult = djangoGet('/api/v1/reservations/my/?type=equipment&limit=5&user_id=' . $user_id);
$recent_rows  = [];

if ($recentResult['success'] && isset($recentResult['data'])) {
    $recent_rows = $recentResult['data']['results'] ?? $recentResult['data'] ?? [];
}
?>

<h2>Reserve Equipment</h2>
<?php echo getFlash(); ?>

<div style="max-width:520px; background:#f9f9f9; padding:24px; border-radius:8px; border:1px solid #ddd;">
    <form method="POST" id="reserveEqForm">

        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">
                Select Equipment
            </label>
            <select name="equipment" required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="">-- Choose Equipment --</option>
<<<<<<< HEAD
                <?php foreach ($equip_by_campus as $campus => $items): ?>
                    <optgroup label="── <?php echo htmlspecialchars($campus); ?> ──">
                        <?php foreach ($items as $e): ?>
                            <option value="<?php echo (int) $e['id']; ?>">
                                <?php echo htmlspecialchars($e['equipment_name'] ?? $e['name'] ?? ''); ?>
                                — <?php echo htmlspecialchars($e['lab_name'] ?? ''); ?>
                                (<?php echo (int) ($e['quantity'] ?? 0); ?> available)
=======
                <?php
                $campuses = ['CHS', 'CLI'];
                foreach ($campuses as $campus):
                    $equipment = $conn->query("
                        SELECT e.*, l.lab_name
                        FROM   equipment e
                        JOIN   laboratories l ON e.lab_id = l.id
                        WHERE  l.campus = '$campus'
                        AND    e.quantity > 0
                        AND    e.status = 'Available'
                        ORDER  BY l.lab_name, e.equipment_name
                    ");
                    if ($equipment->num_rows === 0) continue;
                ?>
                    <optgroup label="── <?php echo $campus; ?> ──">
                        <?php while ($e = $equipment->fetch_assoc()): ?>
                            <option value="<?php echo $e['id']; ?>">
                                <?php echo htmlspecialchars($e['equipment_name']); ?>
                                — <?php echo htmlspecialchars($e['lab_name']); ?>
                                (<?php echo (int)$e['quantity']; ?> available)
>>>>>>> ec6daf5 (new)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">
                Date Needed
            </label>
            <input
                type="date"
                name="date"
                min="<?php echo date('Y-m-d'); ?>"
                required
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;"
            >
        </div>

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

<!-- Recent equipment reservations -->
<div style="margin-top:36px;">
    <h3 style="margin-bottom:12px;">Your Recent Equipment Reservations</h3>

    <?php if (empty($recent_rows)): ?>
        <p style="color:#888; font-style:italic;">No equipment reservations yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0"
               style="border-collapse:collapse; width:100%; max-width:700px;">
            <thead style="background:#2c5f2e; color:white;">
                <tr>
                    <th>Equipment</th>
                    <th>Lab</th>
                    <th>Date</th>
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
                    <td><?php echo htmlspecialchars($r['equipment_name'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($r['lab_name']       ?? '—'); ?></td>
                    <td><?php echo isset($r['date']) ? date("F d, Y", strtotime($r['date'])) : '—'; ?></td>
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
document.getElementById('reserveEqForm').addEventListener('submit', function (e) {
    if (!confirm("Submit this equipment reservation?")) e.preventDefault();
});
</script>

<?php include("../includes/footer.php"); ?>