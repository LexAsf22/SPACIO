<?php
// frontend/includes/footer.php
?>
</div> <!-- end content -->

<footer class="site-footer">
    <div class="footer-glow-line"></div>
    <div class="footer-inner">

        <!-- Left: Brand -->
        <div class="footer-brand">
            <div class="footer-icon">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                    <path d="M3 3h8v8H3V3z" stroke="#4ade6e" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M13 3h8v8h-8V3z" stroke="#4ade6e" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M3 13h8v8H3v-8z" stroke="#4ade6e" stroke-width="2" stroke-linejoin="round"/>
                    <circle cx="17" cy="17" r="4" stroke="#4ade6e" stroke-width="2"/>
                </svg>
            </div>
            <span class="footer-logo">Campus<strong>System</strong></span>
        </div>

        <!-- Center: Copyright -->
        <div class="footer-copy">
            &copy; <?= date('Y') ?> Campus System &mdash; All Rights Reserved
        </div>

        <!-- Right: Links -->
        <div class="footer-links">
            <a href="#" class="footer-link">Privacy</a>
            <span class="footer-sep"></span>
            <a href="#" class="footer-link">Support</a>
            <span class="footer-sep"></span>
            <a href="#" class="footer-link">Contact</a>
        </div>

    </div>
</footer>

<style>
/* ─────────────────────────────────────────────
   Spacio Footer — synced with header.php
   --sidebar-w in header.php = 260px
   Footer spans full viewport width (left:0)
   Content is offset via padding-left to clear sidebar
───────────────────────────────────────────────── */
.site-footer {
    position: fixed;
    bottom: 0;
    left: 0;       /* full width — sidebar covers left portion (z-index:100 > 99) */
    right: 0;
    z-index: 99;
    height: 42px;

    background: #0b1a0d;
    border-top: 1px solid rgba(74, 222, 110, 0.10);
    box-shadow: 0 -4px 28px rgba(0, 0, 0, 0.45);
    overflow: hidden;
    font-family: 'DM Sans', 'Segoe UI', system-ui, sans-serif;
}

/* Full-width shimmer line across the very top edge */
.footer-glow-line {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent            0%,
        transparent           20%,
        rgba(74,222,110,0.50) 38%,
        rgba(144,238,144,0.8) 50%,
        rgba(74,222,110,0.50) 62%,
        transparent           80%,
        transparent           100%
    );
}

/* Three-column row — left-padding clears the 260px sidebar */
.footer-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding-left: calc(260px + 24px);
    padding-right: 24px;
    gap: 12px;
}

/* LEFT — Brand */
.footer-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.footer-icon {
    width: 22px;
    height: 22px;
    background: rgba(74, 222, 110, 0.08);
    border: 1px solid rgba(74, 222, 110, 0.22);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.footer-logo {
    font-size: 11.5px;
    font-weight: 400;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.28);
    white-space: nowrap;
}

.footer-logo strong {
    font-weight: 700;
    color: rgba(255, 255, 255, 0.62);
}

/* CENTER — Copyright */
.footer-copy {
    font-size: 11.5px;
    color: rgba(255, 255, 255, 0.28);
    letter-spacing: 0.02em;
    text-align: center;
    flex: 1;
    white-space: nowrap;
}

/* RIGHT — Links */
.footer-links {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

.footer-link {
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.28);
    text-decoration: none;
    padding: 3px 7px;
    border-radius: 4px;
    transition: color 0.18s ease, background 0.18s ease;
}

.footer-link:hover {
    color: #4ade6e;
    background: rgba(74, 222, 110, 0.10);
}

.footer-sep {
    width: 1px;
    height: 11px;
    background: rgba(255, 255, 255, 0.10);
    flex-shrink: 0;
    margin: 0 2px;
}

/* Responsive */
@media (max-width: 900px) {
    .footer-brand { display: none; }
    .footer-inner { padding-left: calc(260px + 16px); padding-right: 16px; }
}

@media (max-width: 768px) {
    .footer-inner { padding-left: 16px; padding-right: 16px; }
    .footer-links { display: none; }
}
</style>

</body>
</html>