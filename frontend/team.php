<?php include_once("includes/header.php"); ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Developer Team</h1>
        <p class="page-subtitle">The people behind Spacio</p>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">

    <!-- Developer 1 -->
    <div class="card">
        <div class="card-body" style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; padding: 32px 24px;">
            <div class="sidebar-avatar" style="width: 64px; height: 64px; border-radius: 14px; font-size: 1.4rem;">
                VT
            </div>
            <div>
                <div class="page-title" style="font-size: 1.1rem;">Van Phillip Tamayo</div>
                <div class="page-subtitle" style="margin-top: 4px;">Backend Developer</div>
            </div>
            <span class="badge badge-green">Lead Developer</span>
            <p class="text-muted text-small" style="max-width: 240px;">
                Responsible for backend architecture, Django REST API, and database design.
            </p>
        </div>
    </div>

    <!-- Developer 2 -->
    <div class="card">
        <div class="card-body" style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; padding: 32px 24px;">
            <div class="sidebar-avatar" style="width: 64px; height: 64px; border-radius: 14px; font-size: 1.4rem;">
                LH
            </div>
            <div>
                <div class="page-title" style="font-size: 1.1rem;">Luigi Hufana</div>
                <div class="page-subtitle" style="margin-top: 4px;">Frontend Developer</div>
            </div>
            <span class="badge badge-blue">UI / UX</span>
            <p class="text-muted text-small" style="max-width: 240px;">
                Responsible for frontend design, PHP views, and the overall user experience.
            </p>
        </div>
    </div>

</div>

<div class="card" style="margin-top: 14px;">
    <div class="card-body" style="text-align: center; padding: 20px;">
        <p class="text-muted text-small">
            Spacio is a capstone project developed by students of
            <strong>Lorma Colleges</strong> — <?php echo date('Y'); ?>.
        </p>
    </div>
</div>

<?php include_once("includes/footer.php"); ?>