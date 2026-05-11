<?php include_once("includes/header.php"); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">System Status</h1>
        <p class="page-subtitle">Current operational status of Spacio</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="status-alert success">
            <span>✓</span> All systems are operational.
        </div>
        <p class="text-muted text-small">Last checked: <?php echo date('F j, Y, g:i A'); ?></p>
    </div>
</div>

<?php include_once("includes/footer.php"); ?>