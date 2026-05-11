<?php
// terms.php
// Location: /spacio/terms.php  (root — same level as login.php, register.php)
// STANDALONE — does NOT include header.php because that calls checkLogin().
// T&C must be readable by unauthenticated users (e.g. from the register form).

// Active tab: ?tab=privacy  →  Data Privacy Policy
//             (default)     →  Terms & Conditions
$activeTab = (($_GET['tab'] ?? '') === 'privacy') ? 'privacy' : 'terms';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spacio — <?= $activeTab === 'privacy' ? 'Data Privacy Policy' : 'Terms &amp; Conditions' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Literata:ital,opsz,wght@0,7..72,300;0,7..72,400;1,7..72,300;1,7..72,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --night:   #0b1f0d;
            --forest:  #122b14;
            --canopy:  #1e4422;
            --fern:    #2e6b34;
            --moss:    #4a9252;
            --sprout:  #74bb7a;
            --mist:    #b9debb;
            --fog:     #e4f2e5;
            --cream:   #f8f4ee;
            --sand:    #ede6d8;
            --gold:    #c49a2a;
            --gold-lt: #e2bb5a;
            --ink:     #141414;
            --gray:    #7a8c7c;
            --red-lt:  rgba(220,53,53,.12);
            --red-bd:  rgba(220,53,53,.25);
            --grn-lt:  rgba(52,199,89,.08);
            --grn-bd:  rgba(52,199,89,.2);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Literata', Georgia, serif;
            background: var(--cream);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ══ TOP BAR ══ */
        .t-topbar {
            background: var(--night);
            padding: 0 48px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .t-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .t-brand-mark {
            width: 30px; height: 30px;
            background: var(--gold);
            border-radius: 7px;
            display: grid; place-items: center;
            font-family: 'Syne', sans-serif;
            font-size: 13px; font-weight: 800;
            color: var(--night);
            flex-shrink: 0;
        }

        .t-brand-name {
            font-family: 'Syne', sans-serif;
            font-size: .95rem; font-weight: 800;
            color: #fff; letter-spacing: -.01em;
        }

        .t-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-family: 'Syne', sans-serif;
            font-size: .72rem; font-weight: 600;
            letter-spacing: .09em; text-transform: uppercase;
            color: rgba(255,255,255,.38);
            text-decoration: none;
            transition: color .2s;
            padding: 6px 0;
        }

        .t-back:hover { color: var(--gold); }
        .t-back-arrow { transition: transform .2s; display: inline-block; }
        .t-back:hover .t-back-arrow { transform: translateX(-3px); }

        /* ══ HERO BANNER ══ */
        .t-hero {
            background: var(--night);
            padding: 48px 48px 0;
            position: relative;
            overflow: hidden;
        }

        .t-hero-bg {
            position: absolute; inset: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 60% 80% at 80% 50%, rgba(46,107,52,.22) 0%, transparent 65%),
                radial-gradient(ellipse 40% 60% at 10% 80%, rgba(196,154,42,.08) 0%, transparent 55%);
        }

        .t-hero-grid {
            position: absolute; inset: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: radial-gradient(ellipse 85% 100% at 50% 50%, black 20%, transparent 100%);
        }

        .t-hero-inner {
            position: relative; z-index: 1;
            max-width: 900px; margin: 0 auto;
        }

        .t-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .t-eyebrow-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); }

        .t-eyebrow-text {
            font-family: 'Syne', sans-serif;
            font-size: .68rem; font-weight: 700;
            letter-spacing: .16em; text-transform: uppercase;
            color: var(--gold);
        }

        .t-hero h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.9rem, 3.5vw, 2.8rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: -.03em;
            line-height: 1.08;
            margin-bottom: 10px;
        }

        .t-hero h1 .accent {
            color: var(--sprout);
            font-style: italic;
            font-family: 'Literata', serif;
            font-weight: 300;
        }

        .t-hero-sub {
            font-size: .9rem;
            color: rgba(255,255,255,.38);
            font-style: italic;
            line-height: 1.75;
            max-width: 520px;
            margin-bottom: 32px;
        }

        /* ══ TAB NAV ══ */
        .t-tabs {
            display: flex;
            gap: 0;
            margin-top: 12px;
        }

        .t-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            font-family: 'Syne', sans-serif;
            font-size: .78rem; font-weight: 600;
            letter-spacing: .04em;
            color: rgba(255,255,255,.38);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s, background .2s;
        }

        .t-tab:hover {
            color: rgba(255,255,255,.7);
            background: rgba(255,255,255,.04);
        }

        .t-tab.active {
            color: #fff;
            border-bottom-color: var(--gold);
            background: rgba(255,255,255,.05);
        }

        /* ══ MAIN CONTENT ══ */
        .t-main {
            flex: 1;
            padding: 40px 48px 64px;
            background: var(--cream);
        }

        .t-wrap {
            max-width: 900px;
            margin: 0 auto;
        }

        /* ══ NOTICE BANNER ══ */
        .t-notice {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: rgba(196,154,42,.08);
            border: 1px solid rgba(196,154,42,.2);
            border-left: 3px solid var(--gold);
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 32px;
        }

        .t-notice-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }

        .t-notice-text {
            font-size: .85rem;
            color: #5a4010;
            line-height: 1.65;
        }

        .t-notice-text strong {
            font-family: 'Syne', sans-serif;
            font-size: .75rem; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase;
            display: block; margin-bottom: 3px;
            color: var(--gold);
        }

        /* ══ SECTION CARDS ══ */
        .t-section {
            background: #fff;
            border: 1px solid #e5e9e6;
            border-radius: 10px;
            padding: 24px 28px;
            margin-bottom: 12px;
            transition: border-color .2s;
        }

        .t-section:hover { border-color: rgba(116,187,122,.35); }

        .t-section-num {
            font-family: 'Syne', sans-serif;
            font-size: .65rem; font-weight: 700;
            letter-spacing: .14em; text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 4px;
        }

        .t-section-title {
            font-family: 'Syne', sans-serif;
            font-size: .97rem; font-weight: 700;
            color: var(--forest);
            letter-spacing: -.01em;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0f2f0;
        }

        /* ══ TWO-COLUMN (Legal vs Plain) ══ */
        .t-cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .t-col {
            padding: 14px 16px;
            border-radius: 7px;
        }

        .t-col-head {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
        }

        .t-col-badge {
            font-family: 'Syne', sans-serif;
            font-size: .65rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .t-col.legal {
            background: #fff8f7;
            border: 1px solid rgba(220,53,53,.12);
        }

        .t-col.legal .t-col-badge {
            background: rgba(220,53,53,.1);
            color: #b91c1c;
        }

        .t-col.plain {
            background: #f0fdf4;
            border: 1px solid rgba(74,146,82,.15);
        }

        .t-col.plain .t-col-badge {
            background: rgba(74,146,82,.12);
            color: #166534;
        }

        .t-col p,
        .t-col li {
            font-size: .875rem;
            line-height: 1.72;
            color: #3d4d3e;
        }

        .t-col ul {
            padding-left: 18px;
            margin: 6px 0;
        }

        .t-col li { margin-bottom: 3px; }

        /* ══ PRIVACY TAB (single column prose) ══ */
        .t-prose {
            font-size: .9rem;
            line-height: 1.75;
            color: #3d4d3e;
        }

        .t-prose ul {
            padding-left: 20px;
            margin: 8px 0;
        }

        .t-prose li { margin-bottom: 4px; }

        /* ══ BOTTOM ACTIONS ══ */
        .t-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 36px;
            padding-top: 28px;
            border-top: 1px solid #e5e9e6;
            flex-wrap: wrap;
        }

        .t-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 26px;
            border-radius: 8px;
            font-family: 'Syne', sans-serif;
            font-size: .8rem; font-weight: 700;
            letter-spacing: .05em; text-transform: uppercase;
            text-decoration: none;
            transition: background .2s, transform .15s, box-shadow .2s;
        }

        .t-btn:hover { transform: translateY(-1px); }

        .t-btn-primary {
            background: var(--night);
            color: #fff;
        }

        .t-btn-primary:hover { background: var(--forest); box-shadow: 0 6px 20px rgba(11,31,13,.25); }

        .t-btn-secondary {
            background: var(--sand);
            color: var(--fern);
            border: 1px solid rgba(46,107,52,.15);
        }

        .t-btn-secondary:hover { background: #e0d9cb; }

        .t-btn-ghost {
            color: rgba(0,0,0,.4);
            font-size: .75rem;
            padding: 8px 0;
        }

        .t-btn-ghost:hover { color: var(--fern); transform: none; }

        .t-actions-spacer { flex: 1; }

        /* ══ FOOTER ══ */
        .t-footer {
            background: var(--night);
            border-top: 1px solid rgba(255,255,255,.04);
            padding: 18px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .t-footer-copy {
            font-size: .7rem;
            color: rgba(255,255,255,.2);
            letter-spacing: .06em;
        }

        .t-footer-copy strong { color: rgba(185,222,187,.35); font-weight: 500; }

        .t-footer-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .t-footer-links a {
            font-family: 'Syne', sans-serif;
            font-size: .68rem; font-weight: 600;
            letter-spacing: .08em; text-transform: uppercase;
            color: rgba(255,255,255,.25);
            text-decoration: none;
            transition: color .2s;
        }

        .t-footer-links a:hover { color: var(--gold); }

        .t-footer-sep { color: rgba(255,255,255,.1); font-size: .8rem; }

        /* ══ RESPONSIVE ══ */
        @media (max-width: 720px) {
            .t-topbar, .t-hero, .t-main, .t-footer { padding-left: 22px; padding-right: 22px; }
            .t-cols { grid-template-columns: 1fr; }
            .t-tab { padding: 10px 16px; font-size: .72rem; }
            .t-section { padding: 18px; }
            .t-hero { padding-top: 36px; }
        }
    </style>
</head>
<body>

<!-- ── Top Bar ────────────────────────────────────────── -->
<nav class="t-topbar">
    <a class="t-brand" href="/spacio/index.php">
        <div class="t-brand-mark">S</div>
        <span class="t-brand-name">Spacio</span>
    </a>
    <a class="t-back" href="/spacio/login.php">
        <span class="t-back-arrow">←</span>
        Sign In
    </a>
</nav>

<!-- ── Hero ──────────────────────────────────────────── -->
<div class="t-hero">
    <div class="t-hero-bg"></div>
    <div class="t-hero-grid"></div>

    <div class="t-hero-inner">
        <div class="t-eyebrow">
            <div class="t-eyebrow-dot"></div>
            <span class="t-eyebrow-text">Legal &amp; Privacy</span>
        </div>

        <h1>
            <?php if ($activeTab === 'privacy'): ?>
                Data <span class="accent">Privacy</span> Policy
            <?php else: ?>
                Terms &amp; <span class="accent">Conditions</span>
            <?php endif; ?>
        </h1>
        <p class="t-hero-sub">
            College Laboratory &amp; Classroom Management System &mdash;
            Lorma Colleges &middot; Effective April 2026
        </p>
    </div>

    <!-- Tab navigation sits at the bottom of the hero -->
    <div class="t-hero-inner">
        <nav class="t-tabs" role="tablist">
            <a href="?tab=terms"
               class="t-tab <?= $activeTab === 'terms' ? 'active' : '' ?>"
               role="tab"
               aria-selected="<?= $activeTab === 'terms' ? 'true' : 'false' ?>">
                📋 Terms &amp; Conditions
            </a>
            <a href="?tab=privacy"
               class="t-tab <?= $activeTab === 'privacy' ? 'active' : '' ?>"
               role="tab"
               aria-selected="<?= $activeTab === 'privacy' ? 'true' : 'false' ?>">
                🔒 Data Privacy Policy
            </a>
        </nav>
    </div>
</div>

<!-- ── Main Content ───────────────────────────────────── -->
<main class="t-main">
<div class="t-wrap">

<?php if ($activeTab === 'terms'): ?>

    <!-- NOTICE -->
    <div class="t-notice">
        <span class="t-notice-icon">⚖️</span>
        <div class="t-notice-text">
            <strong>Two versions of each clause</strong>
            Each section below shows the formal legal language alongside a plain-language
            summary — so you know exactly what you're agreeing to.
        </div>
    </div>

    <!-- SECTION 1 -->
    <div class="t-section">
        <div class="t-section-num">Section 01</div>
        <div class="t-section-title">Acceptance of Terms</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>By accessing and using the College Laboratory &amp; Classroom Management System,
                    users acknowledge and agree to be bound by these Terms and Conditions.
                    Continued use of the system constitutes acceptance of any updates or
                    modifications.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>By using SPACIO, you agree to follow all the rules stated here.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 2 -->
    <div class="t-section">
        <div class="t-section-num">Section 02</div>
        <div class="t-section-title">User Roles and Responsibilities</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>Users shall comply with their designated roles within the system:</p>
                <ul>
                    <li><strong>Students</strong> shall ensure accurate reservation of laboratory
                        resources and adherence to approved schedules.</li>
                    <li><strong>Teachers</strong> shall supervise laboratory usage and report
                        issues as necessary.</li>
                    <li><strong>Administrators (Custodians)</strong> shall manage inventory,
                        approve reservations, and oversee maintenance operations.</li>
                </ul>
                <p>Failure to fulfill these responsibilities may result in appropriate
                    administrative action.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <ul>
                    <li><strong>Students</strong> must make correct reservations and follow schedules.</li>
                    <li><strong>Teachers</strong> monitor lab usage and report issues.</li>
                    <li><strong>Admins</strong> manage bookings, inventory, and maintenance.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- SECTION 3 -->
    <div class="t-section">
        <div class="t-section-num">Section 03</div>
        <div class="t-section-title">Account Security</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>Users are solely responsible for maintaining the confidentiality of their
                    login credentials. Any activity conducted under a user's account shall be
                    deemed authorized by the account holder. Unauthorized sharing of accounts
                    is strictly prohibited.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>Keep your username and password private. You are responsible for anything
                    done using your account.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 4 -->
    <div class="t-section">
        <div class="t-section-num">Section 04</div>
        <div class="t-section-title">Reservation and Booking Policy</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>All reservations are subject to administrative approval. The system reserves
                    the right to approve, reject, or cancel any booking request. Repeated
                    non-attendance, misuse, or fraudulent reservations may result in suspension
                    of booking privileges.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>All bookings must be approved. Avoid fake bookings or skipping your schedule,
                    or your access may be limited.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 5 -->
    <div class="t-section">
        <div class="t-section-num">Section 05</div>
        <div class="t-section-title">Use of Laboratory Resources</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>Users shall exercise proper care in handling all laboratory equipment,
                    computers, and materials. Any damage resulting from negligence or improper
                    use shall be the responsibility of the user and may lead to disciplinary
                    action.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>Handle all equipment carefully. If you damage something due to misuse,
                    you may be held responsible.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 6 -->
    <div class="t-section">
        <div class="t-section-num">Section 06</div>
        <div class="t-section-title">Issue Reporting and Maintenance</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>Users must ensure that all reported issues are accurate and submitted in good
                    faith. False, misleading, or malicious reports are strictly prohibited and
                    may result in sanctions.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>Report problems honestly. Do not submit false reports.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 7 -->
    <div class="t-section">
        <div class="t-section-num">Section 07</div>
        <div class="t-section-title">System Availability</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>The system is provided on an &ldquo;as-is&rdquo; and &ldquo;as-available&rdquo;
                    basis. The administration does not guarantee uninterrupted access and shall
                    not be held liable for any disruptions, delays, or technical issues.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>The system may sometimes be unavailable due to maintenance or technical
                    problems.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 8 -->
    <div class="t-section">
        <div class="t-section-num">Section 08</div>
        <div class="t-section-title">Prohibited Activities</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>Users shall not:</p>
                <ul>
                    <li>Attempt unauthorized access to restricted areas of the system</li>
                    <li>Interfere with or disrupt system operations</li>
                    <li>Input false or misleading information</li>
                    <li>Misuse laboratory resources or facilities</li>
                </ul>
                <p>Violations may result in suspension, termination, or further administrative
                    action.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>Do not:</p>
                <ul>
                    <li>Try to hack or access restricted areas</li>
                    <li>Enter false information</li>
                    <li>Misuse lab equipment</li>
                </ul>
                <p>Breaking the rules may lead to account suspension.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 9 -->
    <div class="t-section">
        <div class="t-section-num">Section 09</div>
        <div class="t-section-title">Modifications to Terms</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>The administration reserves the right to amend these Terms and Conditions at
                    any time. Continued use of the system after such changes constitutes
                    acceptance of the revised terms.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>The rules may change anytime. Continued use means you accept the changes.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 10 -->
    <div class="t-section">
        <div class="t-section-num">Section 10</div>
        <div class="t-section-title">Termination of Access</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>The administration reserves the right to suspend or terminate user access
                    without prior notice in cases of violation of these Terms and Conditions.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>Your account may be suspended or removed if you break the rules.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 11 -->
    <div class="t-section">
        <div class="t-section-num">Section 11</div>
        <div class="t-section-title">Governing Agreement</div>
        <div class="t-cols">
            <div class="t-col legal">
                <div class="t-col-head">
                    <span class="t-col-badge">⚖ Legal</span>
                </div>
                <p>These Terms and Conditions constitute a binding agreement between the user
                    and the system administrators.</p>
            </div>
            <div class="t-col plain">
                <div class="t-col-head">
                    <span class="t-col-badge">💬 Plain</span>
                </div>
                <p>By continuing to use the system, you confirm that you understand and agree
                    to these Terms.</p>
            </div>
        </div>
    </div>

<?php else: /* PRIVACY TAB */ ?>

    <!-- NOTICE -->
    <div class="t-notice">
        <span class="t-notice-icon">🔒</span>
        <div class="t-notice-text">
            <strong>Data Privacy Policy</strong>
            This policy outlines how SPACIO collects, uses, stores, and protects your
            personal information in compliance with applicable data protection laws and
            institutional policies.
        </div>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 01</div>
        <div class="t-section-title">Collection of Data</div>
        <p class="t-prose">The system collects personal information, including but not limited to names,
            user roles (student, teacher, administrator), login credentials, and system
            activity data for operational purposes.</p>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 02</div>
        <div class="t-section-title">Purpose of Data Processing</div>
        <p class="t-prose">Collected data shall be used solely for:</p>
        <ul class="t-prose" style="padding-left:20px; margin-top:8px;">
            <li>User authentication and account management</li>
            <li>Laboratory reservations and scheduling</li>
            <li>Issue reporting and maintenance tracking</li>
            <li>System monitoring and performance improvement</li>
            <li>Administrative reporting and analytics</li>
        </ul>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 03</div>
        <div class="t-section-title">Data Protection and Security</div>
        <p class="t-prose">The system implements appropriate technical and organizational measures to protect
            personal data against unauthorized access, alteration, disclosure, or destruction.</p>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 04</div>
        <div class="t-section-title">Data Sharing and Disclosure</div>
        <p class="t-prose">Personal data shall not be shared with third parties without user consent,
            except when required by law or authorized by the institution.</p>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 05</div>
        <div class="t-section-title">Data Retention</div>
        <p class="t-prose">User data shall be retained only for as long as necessary to fulfill its intended
            academic and administrative purposes, after which it will be securely deleted
            or archived.</p>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 06</div>
        <div class="t-section-title">User Rights</div>
        <p class="t-prose">Users have the right to:</p>
        <ul class="t-prose" style="padding-left:20px; margin-top:8px;">
            <li>Access their personal data</li>
            <li>Request correction of inaccurate information</li>
            <li>Request deletion of their data, subject to institutional policies</li>
        </ul>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 07</div>
        <div class="t-section-title">Monitoring and Logs</div>
        <p class="t-prose">The system may monitor user activity and maintain logs for security, auditing,
            and system improvement purposes.</p>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 08</div>
        <div class="t-section-title">Policy Updates</div>
        <p class="t-prose">This Data Privacy Policy may be updated periodically. Continued use of the
            system constitutes acceptance of any changes.</p>
    </div>

    <div class="t-section">
        <div class="t-section-num">Section 09</div>
        <div class="t-section-title">Consent</div>
        <p class="t-prose">By using the system, users consent to the collection and processing of their
            personal data as described in this policy.</p>
    </div>

<?php endif; ?>

    <!-- Bottom Actions -->
    <div class="t-actions">
        <div class="t-actions-spacer"></div>
        <a class="t-btn t-btn-ghost"
           href="?tab=<?= $activeTab === 'terms' ? 'privacy' : 'terms' ?>">
            <?= $activeTab === 'terms' ? 'View Privacy Policy →' : '← View Terms & Conditions' ?>
        </a>
    </div>

</div><!-- /t-wrap -->
</main>

<!-- ── Footer ─────────────────────────────────────────── -->
<footer class="t-footer">
    <p class="t-footer-copy">&copy; <?= date('Y') ?> <strong>Spacio</strong> &nbsp;&middot;&nbsp; Campus Lab &amp; Classroom Management &nbsp;&middot;&nbsp; Lorma Colleges</p>
    <div class="t-footer-links">
        <a href="/spacio/frontend/about.php">About</a>
        <span class="t-footer-sep">|</span>
        <a href="/spacio/frontend/contact.php">Contact</a>
        <span class="t-footer-sep">|</span>
        <a href="/spacio/frontend/team.php">Developer Team</a>
        <span class="t-footer-sep">|</span>
        <a href="/spacio/frontend/help.php">FAQ / Help</a>
        <span class="t-footer-sep">|</span>
        <a href="/spacio/frontend/status.php">System Status</a>
        <span class="t-footer-sep">|</span>
        <a href="?tab=terms">Terms &amp; Conditions</a>
        <span class="t-footer-sep">|</span>
        <a href="?tab=privacy">Privacy Policy</a>
        <span class="t-footer-sep">|</span>
        <a href="/spacio/login.php">Sign In</a>
    </div>
</footer>

</body>
</html>