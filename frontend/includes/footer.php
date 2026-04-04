<?php
/*
 * frontend/includes/footer.php
 * ─────────────────────────────────────────────────────────
 * Closes the layout shell opened by header.php.
 * Must always be included at the bottom of every page
 * that includes header.php.
 */
?>

        </main>
        <!-- End .page-content -->

        <!-- ── App Footer Strip ── -->
        <footer style="
            padding: 16px 32px;
            border-top: 1px solid rgba(255,255,255,.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            flex-shrink: 0;
        ">
            <span style="
                font-family: 'Syne', sans-serif;
                font-size: .65rem;
                color: rgba(255,255,255,.15);
                letter-spacing: .08em;
            ">
                &copy; 2026 <strong style="color:rgba(185,222,187,.3); font-weight:500;">Spacio</strong>
                &nbsp;·&nbsp; Campus Lab &amp; Classroom Management
            </span>
            <span style="
                font-family: 'Syne', sans-serif;
                font-size: .62rem;
                color: rgba(255,255,255,.1);
                font-style: italic;
                letter-spacing: .04em;
            ">
                Role-based access &mdash; students, teachers &amp; admins
            </span>
        </footer>

    </div>
    <!-- End .main-area -->

</div>
<!-- End .app-shell -->

<!-- Shared JS -->
<script src="/spacio/js/app.js"></script>

</body>
</html>