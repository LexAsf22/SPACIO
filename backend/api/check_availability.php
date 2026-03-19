<?php
// backend/api/check_availability.php
require_once "../config/auth.php";
require_once "../config/helpers.php";

checkLogin();

header('Content-Type: application/json');

$lab_id    = (int)   ($_GET['lab_id']    ?? 0);
$date      =  trim(   $_GET['date']      ?? '');
$time_slot =  trim(   $_GET['time_slot'] ?? '');

// ── Basic input guard (same as before) ──
if (!$lab_id || !$date || !$time_slot) {
    echo json_encode(['available' => true]);
    exit;
}

// ── Forward request to Django availability API ──
$endpoint = '/api/v1/availability/labs/'
    . '?lab_id='    . urlencode($lab_id)
    . '&date='      . urlencode($date)
    . '&time_slot=' . urlencode($time_slot);

$result = djangoGet($endpoint);

// ── If Django is reachable, return its answer ──
if ($result['success'] && isset($result['data']['available'])) {
    echo json_encode([
        'available' => (bool) $result['data']['available'],
        'source'    => 'django',
    ]);
    exit;
}

// ── Fallback: query the DB directly if Django is down ──
// Remove this block once Django is confirmed stable in production.
require_once "../config/database.php";

$stmt = $conn->prepare("
    SELECT id FROM reservations
    WHERE lab_id = ? AND date = ? AND time_slot = ? AND status != 'Rejected'
");
$stmt->bind_param("iss", $lab_id, $date, $time_slot);
$stmt->execute();
$stmt->store_result();

echo json_encode([
    'available' => $stmt->num_rows === 0,
    'source'    => 'fallback',
]);