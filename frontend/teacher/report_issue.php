<?php
// frontend/teacher/report_issue.php
include("../includes/header.php");
checkRole('teacher');

if (isset($_POST['report'])) {
    $campus      =       $_POST['campus']      ?? '';
    $room        = trim( $_POST['room']        ?? '');
    $category    =       $_POST['category']    ?? '';
    $description = trim( $_POST['description'] ?? '');
    $priority    =       $_POST['priority']    ?? '';
    $user_id     = (int) $_SESSION['user']['id'];

    // ── Validate allowed values (keep PHP-side validation — catches bad input before hitting Django) ──
    $allowed_campuses   = ['Campus A', 'Campus B'];
    $allowed_categories = ['Equipment', 'Facility', 'Software'];
    $allowed_priorities = ['Low', 'Medium', 'High'];

    if (!in_array($campus,   $allowed_campuses,   true)) {
        setFlash("Invalid campus selected.", "error");
    } elseif (!in_array($category, $allowed_categories, true)) {
        setFlash("Invalid category selected.", "error");
    } elseif (!in_array($priority, $allowed_priorities, true)) {
        setFlash("Invalid priority selected.", "error");
    } elseif (empty($room)) {
        setFlash("Room / Lab field is required.", "error");
    } else {

        // ── Call Django issues API ──
        $result = djangoPost('/api/v1/issues/', [
            'user_id'     => $user_id,
            'campus'      => $campus,
            'room'        => $room,
            'category'    => $category,
            'description' => $description,
            'priority'    => $priority,
            'status'      => 'Pending',
        ]);

        if ($result['success']) {
            setFlash("Issue reported successfully! Admin has been notified.", "success");
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
?>

<h2>Report a Classroom / Lab Issue</h2>
<?php echo getFlash(); ?>

<div style="max-width:520px; background:#f9f9f9; padding:24px; border-radius:8px; border:1px solid #ddd;">
    <form method="POST" id="issueForm">

        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">Campus</label>
            <select name="campus" required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="">-- Select Campus --</option>
                <option value="Campus A">Campus A</option>
                <option value="Campus B">Campus B</option>
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">Room / Lab</label>
            <input
                type="text"
                name="room"
                placeholder="e.g. Room 201, Lab 3"
                required
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;"
            >
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">Category</label>
            <select name="category" required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="">-- Select Category --</option>
                <option value="Equipment">Equipment</option>
                <option value="Facility">Facility</option>
                <option value="Software">Software</option>
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">Description</label>
            <textarea
                name="description"
                rows="4"
                placeholder="Describe the issue in detail..."
                required
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; resize:vertical;"
            ></textarea>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:6px;">Priority</label>
            <div style="display:flex; gap:12px;">
                <?php foreach (['Low' => '#28a745', 'Medium' => '#f39c12', 'High' => '#e74c3c'] as $level => $color): ?>
                <label style="flex:1; text-align:center; cursor:pointer;">
                    <input type="radio" name="priority" value="<?php echo $level; ?>" required
                           style="display:none;" class="priorityRadio">
                    <span class="priorityBtn" data-value="<?php echo $level; ?>" style="
                        display:block; padding:10px; border-radius:5px;
                        border:2px solid <?php echo $color; ?>;
                        color:<?php echo $color; ?>;
                        font-weight:bold;
                        transition: all .2s;
                    ">
                        <?php echo $level; ?>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button
            name="report"
            type="submit"
            style="width:100%; padding:12px; background:#2c5f2e; color:white; border:none; border-radius:5px; font-size:1rem; cursor:pointer;"
        >
            Submit Report
        </button>

    </form>
</div>

<script>
document.querySelectorAll('.priorityRadio').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.priorityBtn').forEach(btn => {
            btn.style.background = 'white';
            btn.style.color      = btn.style.borderColor;
        });
        const btn        = this.nextElementSibling;
        btn.style.background = btn.style.borderColor;
        btn.style.color      = 'white';
    });
});

document.getElementById('issueForm').addEventListener('submit', function (e) {
    if (!confirm("Submit this issue report?")) e.preventDefault();
});
</script>

<?php include("../includes/footer.php"); ?>