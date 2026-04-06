<?php
// frontend/teacher/report_issue.php

// ── ALL logic BEFORE any HTML output ─────────────────────────
include_once("../../backend/config/auth.php");
include_once("../../backend/config/database.php");
include_once("../../backend/config/helpers.php");
checkLogin();
checkRole('teacher');

if (isset($_POST['report'])) {
    $campus      =       $_POST['campus']      ?? '';
    $room        = trim( $_POST['room']        ?? '');
    $category    =       $_POST['category']    ?? '';
    $description = trim( $_POST['description'] ?? '');
    $priority    =       $_POST['priority']    ?? '';
    $user_id     = (int) $_SESSION['user']['id'];

    $allowed_campuses   = ['CLI', 'CHS'];
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

// ── HTML output starts here ───────────────────────────────────
include("../includes/header.php");
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Report an Issue</h1>
        <p class="page-subtitle">Submit a classroom or lab problem for admin review</p>
    </div>
</div>

<?php echo getFlash(); ?>

<div style="max-width: 560px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Issue Details</span>
        </div>
        <div class="card-body">
            <form method="POST" id="issueForm">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div class="form-group">
                        <label class="form-label required">Campus</label>
                        <select name="campus" class="form-control" required>
                            <option value="">— Select Campus —</option>
                            <option value="CLI">CLI</option>
                            <option value="CHS">CHS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Room / Lab</label>
                        <input type="text" name="room" class="form-control"
                               placeholder="e.g. Room 201, Lab 3" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="">— Select Category —</option>
                        <option value="Equipment">Equipment</option>
                        <option value="Facility">Facility</option>
                        <option value="Software">Software</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Description</label>
                    <textarea name="description" class="form-control"
                              rows="4" placeholder="Describe the issue in detail..." required></textarea>
                </div>

                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label required">Priority</label>
                    <div class="priority-group">
                        <div class="priority-option low">
                            <input type="radio" name="priority" id="pLow" value="Low" required>
                            <label class="priority-label" for="pLow">Low</label>
                        </div>
                        <div class="priority-option med">
                            <input type="radio" name="priority" id="pMed" value="Medium">
                            <label class="priority-label" for="pMed">Medium</label>
                        </div>
                        <div class="priority-option high">
                            <input type="radio" name="priority" id="pHigh" value="High">
                            <label class="priority-label" for="pHigh">High</label>
                        </div>
                    </div>
                </div>

                <button name="report" type="submit" class="btn btn-primary btn-full">
                    Submit Report
                </button>

            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('issueForm').addEventListener('submit', function (e) {
    if (!confirm("Submit this issue report?")) e.preventDefault();
});
</script>

<?php include("../includes/footer.php"); ?>