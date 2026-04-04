<?php
session_start();
if(isset($_SESSION['user'])){
    $role = strtolower($_SESSION['user']['role']);
    $dashboards = [
        'student' => 'frontend/student/dashboard.php',
        'teacher' => 'frontend/teacher/dashboard.php',
        'admin'   => 'frontend/admin/dashboard.php',
    ];
    header("Location: " . ($dashboards[$role] ?? 'login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spacio — Campus Lab & Classroom Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Literata:ital,wght@0,300;0,400;1,300;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --night:    #0b1f0d;
            --forest:   #122b14;
            --canopy:   #1e4422;
            --fern:     #2e6b34;
            --moss:     #4a9252;
            --sprout:   #74bb7a;
            --mist:     #b9debb;
            --fog:      #e4f2e5;
            --cream:    #f8f4ee;
            --sand:     #ede6d8;
            --gold:     #c49a2a;
            --gold-lt:  #e2bb5a;
            --ink:      #141414;
            --gray:     #7a8c7c;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Literata', Georgia, serif;
            background: var(--cream);
            color: var(--ink);
            overflow-x: hidden;
        }

        /* ══ NAVBAR ══ */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 60px;
            transition: background .4s, backdrop-filter .4s, box-shadow .4s;
        }

        nav.scrolled {
            background: rgba(11,31,13,.9);
            backdrop-filter: blur(16px);
            box-shadow: 0 1px 0 rgba(255,255,255,.06);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-logo-mark {
            width: 34px; height: 34px;
            background: var(--gold);
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: var(--night);
            flex-shrink: 0;
        }

        .nav-logo-text {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.01em;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 36px;
            list-style: none;
        }

        .nav-links a {
            font-family: 'Syne', sans-serif;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.55);
            text-decoration: none;
            transition: color .2s;
        }

        .nav-links a:hover { color: #fff; }

        .nav-cta {
            font-family: 'Syne', sans-serif;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 10px 24px;
            background: var(--gold);
            color: var(--night);
            border-radius: 6px;
            text-decoration: none;
            transition: background .2s, transform .15s;
        }

        .nav-cta:hover { background: var(--gold-lt); transform: translateY(-1px); }

        /* ══ HERO ══ */
        .hero {
            min-height: 100vh;
            background: var(--night);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 70% 60% at 60% 40%, rgba(46,107,52,.3) 0%, transparent 65%),
                radial-gradient(ellipse 40% 40% at 15% 80%, rgba(196,154,42,.12) 0%, transparent 55%),
                radial-gradient(ellipse 50% 50% at 85% 10%, rgba(74,146,82,.1) 0%, transparent 60%);
        }

        .hero-grid {
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 100%);
        }

        .hero-ring {
            position: absolute;
            width: 720px; height: 720px;
            border-radius: 50%;
            border: 1px solid rgba(116,187,122,.07);
            right: -180px; top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .hero-ring-2 { width: 500px; height: 500px; border-color: rgba(196,154,42,.07); right: -60px; }

        .hero-inner {
            position: relative; z-index: 2;
            max-width: 1200px; margin: 0 auto;
            padding: 140px 60px 100px; width: 100%;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            animation: fadeUp .7s .1s both;
        }

        .eyebrow-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); }

        .eyebrow-text {
            font-family: 'Syne', sans-serif;
            font-size: .72rem; font-weight: 600;
            letter-spacing: .18em; text-transform: uppercase;
            color: var(--gold);
        }

        .hero h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(3rem, 6vw, 5.8rem);
            font-weight: 800; color: #fff;
            line-height: 1.0; letter-spacing: -.03em;
            max-width: 820px; margin-bottom: 28px;
            animation: fadeUp .7s .2s both;
        }

        .hero h1 .accent {
            color: var(--sprout);
            font-style: italic;
            font-family: 'Literata', serif;
            font-weight: 300;
        }

        .hero-sub {
            font-size: 1.05rem; color: rgba(255,255,255,.45);
            font-weight: 300; font-style: italic;
            line-height: 1.8; max-width: 520px;
            margin-bottom: 48px;
            animation: fadeUp .7s .3s both;
        }

        .hero-actions {
            display: flex; gap: 16px; flex-wrap: wrap;
            animation: fadeUp .7s .4s both;
        }

        .btn-primary {
            font-family: 'Syne', sans-serif;
            font-size: .88rem; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase;
            padding: 16px 36px;
            background: var(--gold); color: var(--night);
            border-radius: 8px; text-decoration: none;
            transition: background .2s, transform .15s, box-shadow .2s;
            display: inline-flex; align-items: center; gap: 8px;
        }

        .btn-primary:hover { background: var(--gold-lt); transform: translateY(-2px); box-shadow: 0 10px 30px rgba(196,154,42,.3); }

        .btn-secondary {
            font-family: 'Syne', sans-serif;
            font-size: .88rem; font-weight: 600;
            letter-spacing: .06em; text-transform: uppercase;
            padding: 16px 32px;
            border: 1.5px solid rgba(255,255,255,.15);
            color: rgba(255,255,255,.7);
            border-radius: 8px; text-decoration: none;
            transition: border-color .2s, color .2s;
        }

        .btn-secondary:hover { border-color: rgba(255,255,255,.4); color: #fff; }

        .hero-stats {
            display: flex; gap: 48px;
            margin-top: 72px; padding-top: 48px;
            border-top: 1px solid rgba(255,255,255,.07);
            animation: fadeUp .7s .5s both;
        }

        .stat-num {
            font-family: 'Syne', sans-serif;
            font-size: 2rem; font-weight: 800; color: #fff;
            letter-spacing: -.04em; line-height: 1; margin-bottom: 6px;
        }

        .stat-num span { color: var(--sprout); }
        .stat-label { font-size: .75rem; color: rgba(255,255,255,.35); font-style: italic; }

        /* ══ FEATURES ══ */
        .features-section {
            background: var(--cream);
            padding: 120px 60px;
            position: relative;
        }

        .features-section::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(46,107,52,.2) 30%, rgba(196,154,42,.2) 70%, transparent);
        }

        .section-wrap { max-width: 1200px; margin: 0 auto; }

        .section-eyebrow {
            font-family: 'Syne', sans-serif;
            font-size: .7rem; font-weight: 700;
            letter-spacing: .18em; text-transform: uppercase;
            color: var(--fern); margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px;
        }

        .section-eyebrow::before { content: ''; width: 24px; height: 1.5px; background: var(--gold); }

        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2rem, 3.5vw, 3rem); font-weight: 800;
            color: var(--forest); letter-spacing: -.03em;
            line-height: 1.1; max-width: 560px;
            margin-bottom: 16px;
        }

        .section-sub {
            font-size: .95rem; color: var(--gray);
            font-style: italic; font-weight: 300;
            line-height: 1.8; max-width: 480px;
            margin-bottom: 72px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            background: rgba(30,68,34,.08);
            border-radius: 16px;
            overflow: hidden;
        }

        .feature-card {
            background: var(--cream);
            padding: 44px 40px;
            transition: background .25s;
            position: relative;
        }

        .feature-card:hover { background: var(--fog); }

        .feature-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 40px; right: 40px; height: 2px;
            background: var(--gold);
            transform: scaleX(0); transform-origin: left;
            transition: transform .3s cubic-bezier(.22,1,.36,1);
        }

        .feature-card:hover::after { transform: scaleX(1); }

        .feature-icon {
            width: 52px; height: 52px;
            background: var(--forest); border-radius: 12px;
            display: grid; place-items: center;
            font-size: 22px; margin-bottom: 24px;
        }

        .feature-num {
            font-family: 'Syne', sans-serif;
            font-size: .65rem; font-weight: 700;
            letter-spacing: .14em; color: var(--gold); margin-bottom: 12px;
        }

        .feature-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.05rem; font-weight: 700;
            color: var(--forest); letter-spacing: -.01em; margin-bottom: 10px;
        }

        .feature-desc {
            font-size: .85rem; color: var(--gray);
            line-height: 1.75; font-weight: 300; font-style: italic;
        }

        /* ══ ABOUT ══ */
        .about-section {
            background: var(--forest);
            padding: 120px 60px;
            position: relative; overflow: hidden;
        }

        .about-bg {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 50% 60% at 90% 50%, rgba(74,146,82,.12) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 10% 20%, rgba(196,154,42,.08) 0%, transparent 55%);
            pointer-events: none;
        }

        .about-inner {
            position: relative; z-index: 1;
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 100px; align-items: center;
        }

        .about-left .section-eyebrow { color: var(--mist); }
        .about-left .section-eyebrow::before { background: var(--gold); }
        .about-left .section-title { color: #fff; max-width: 100%; }
        .about-left .section-sub { color: rgba(255,255,255,.45); margin-bottom: 40px; }

        .about-tag-row { display: flex; flex-wrap: wrap; gap: 10px; }

        .about-tag {
            font-family: 'Syne', sans-serif;
            font-size: .72rem; font-weight: 600;
            letter-spacing: .08em; text-transform: uppercase;
            padding: 7px 16px;
            border: 1px solid rgba(185,222,187,.2);
            border-radius: 100px; color: var(--mist);
        }

        .about-right { display: flex; flex-direction: column; gap: 16px; }

        .about-card {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 14px; padding: 28px 32px;
            display: flex; align-items: flex-start; gap: 20px;
            transition: background .2s, border-color .2s;
        }

        .about-card:hover { background: rgba(255,255,255,.08); border-color: rgba(116,187,122,.2); }
        .about-card-icon { font-size: 24px; flex-shrink: 0; margin-top: 2px; }

        .about-card-title {
            font-family: 'Syne', sans-serif;
            font-size: .9rem; font-weight: 700;
            color: #fff; margin-bottom: 6px; letter-spacing: -.01em;
        }

        .about-card-desc { font-size: .82rem; color: rgba(255,255,255,.4); font-style: italic; line-height: 1.7; }

        /* ══ CTA ══ */
        .cta-section {
            background: var(--cream);
            padding: 120px 60px;
            text-align: center; position: relative; overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 70% 70% at 50% 50%, rgba(46,107,52,.07) 0%, transparent 65%);
            pointer-events: none;
        }

        .cta-inner { position: relative; z-index: 1; max-width: 700px; margin: 0 auto; }
        .cta-section .section-eyebrow { justify-content: center; }
        .cta-section .section-title { margin: 0 auto 16px; text-align: center; max-width: 100%; }
        .cta-section .section-sub { margin: 0 auto 48px; text-align: center; }

        .cta-login-btn {
            font-family: 'Syne', sans-serif;
            font-size: .9rem; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase;
            padding: 18px 52px;
            background: var(--forest); color: #fff;
            border-radius: 8px; text-decoration: none;
            transition: background .2s, transform .15s, box-shadow .2s;
            display: inline-flex; align-items: center; gap: 10px;
        }

        .cta-login-btn:hover { background: var(--canopy); transform: translateY(-2px); box-shadow: 0 12px 32px rgba(11,31,13,.25); }

        .cta-login-btn .arrow { transition: transform .2s; }
        .cta-login-btn:hover .arrow { transform: translateX(4px); }

        .campus-badges { display: flex; justify-content: center; gap: 12px; margin-top: 40px; flex-wrap: wrap; }

        .campus-badge {
            font-family: 'Syne', sans-serif;
            font-size: .7rem; font-weight: 600;
            letter-spacing: .1em; text-transform: uppercase;
            padding: 8px 18px;
            background: var(--sand);
            border: 1px solid rgba(30,68,34,.1);
            border-radius: 100px; color: var(--fern);
        }

        /* ══ FOOTER ══ */
        footer {
            background: var(--night);
            padding: 32px 60px;
            display: flex; align-items: center;
            justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
        }

        .footer-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }

        .footer-copy { font-size: .72rem; color: rgba(255,255,255,.25); letter-spacing: .06em; }
        .footer-copy strong { color: rgba(185,222,187,.5); font-weight: 500; }

        /* ══ ANIMATIONS ══ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .reveal {
            opacity: 0; transform: translateY(32px);
            transition: opacity .7s cubic-bezier(.22,1,.36,1), transform .7s cubic-bezier(.22,1,.36,1);
        }

        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-delay-1 { transition-delay: .1s; }
        .reveal-delay-2 { transition-delay: .2s; }
        .reveal-delay-3 { transition-delay: .3s; }
        .reveal-delay-4 { transition-delay: .4s; }

        /* ══ RESPONSIVE ══ */
        @media (max-width: 900px) {
            nav { padding: 20px 28px; }
            .nav-links { display: none; }
            .hero-inner, .features-section, .about-section, .cta-section { padding-left: 28px; padding-right: 28px; }
            .features-grid { grid-template-columns: 1fr; }
            .about-inner { grid-template-columns: 1fr; gap: 52px; }
            .hero-stats { gap: 28px; }
            footer { padding: 24px 28px; }
        }

        @media (max-width: 600px) {
            .hero h1 { font-size: 2.6rem; }
            .hero-stats { flex-direction: column; gap: 20px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav id="navbar">
    <a class="nav-logo" href="#">
        <div class="nav-logo-mark">S</div>
        <span class="nav-logo-text">Spacio</span>
    </a>
    <ul class="nav-links">
        <li><a href="#features">Features</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#login-cta">Sign In</a></li>
    </ul>
    <a class="nav-cta" href="login.php">Sign In</a>
</nav>

<!-- HERO -->
<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>
    <div class="hero-ring"></div>
    <div class="hero-ring hero-ring-2"></div>

    <div class="hero-inner">
        <div class="hero-eyebrow">
            <div class="eyebrow-dot"></div>
            <span class="eyebrow-text">Campus Lab &amp; Classroom Management</span>
        </div>

        <h1>One platform.<br><span class="accent">Every space,</span><br>managed.</h1>

        <p class="hero-sub">
            Spacio brings reservations, inventory tracking, and issue reporting together — across all campus labs and classrooms, for everyone.
        </p>

        <div class="hero-actions">
            <a class="btn-primary" href="login.php">
                Sign In to Spacio <span>→</span>
            </a>
            <a class="btn-secondary" href="#features">Explore Features</a>
        </div>

        <div class="hero-stats">
            <div class="stat-item">
                <div class="stat-num">2<span>+</span></div>
                <div class="stat-label">Campuses supported</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">3<span>×</span></div>
                <div class="stat-label">User roles — students, teachers, admins</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">Est. <span>'26</span></div>
                <div class="stat-label">Built for the modern campus</div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features-section" id="features">
    <div class="section-wrap">
        <div class="section-eyebrow reveal">What Spacio Does</div>
        <h2 class="section-title reveal reveal-delay-1">Everything your campus needs, in one place.</h2>
        <p class="section-sub reveal reveal-delay-2">Designed for students, teachers, and administrators — Spacio simplifies how your campus manages its spaces and resources.</p>

        <div class="features-grid">
            <div class="feature-card reveal reveal-delay-1">
                <div class="feature-num">01</div>
                <div class="feature-icon">🗓️</div>
                <div class="feature-title">Lab &amp; Room Reservations</div>
                <p class="feature-desc">Students and teachers can book laboratory time, computer stations, and classrooms in advance — no more scheduling conflicts or double bookings.</p>
            </div>
            <div class="feature-card reveal reveal-delay-2">
                <div class="feature-num">02</div>
                <div class="feature-icon">🔬</div>
                <div class="feature-title">Equipment Management</div>
                <p class="feature-desc">Track lab equipment, computers, and campus resources. Know what's available, what's in use, and what needs maintenance — all in real time.</p>
            </div>
            <div class="feature-card reveal reveal-delay-3">
                <div class="feature-num">03</div>
                <div class="feature-icon">📦</div>
                <div class="feature-title">Inventory Tracking</div>
                <p class="feature-desc">Admins get a full view of campus inventory across both buildings. Monitor stock levels, log changes, and stay on top of supplies.</p>
            </div>
            <div class="feature-card reveal reveal-delay-1">
                <div class="feature-num">04</div>
                <div class="feature-icon">🚨</div>
                <div class="feature-title">Issue Reporting</div>
                <p class="feature-desc">Teachers and students can flag broken equipment or classroom issues instantly. Admins receive and resolve reports from a single dashboard.</p>
            </div>
            <div class="feature-card reveal reveal-delay-2">
                <div class="feature-num">05</div>
                <div class="feature-icon">📊</div>
                <div class="feature-title">Campus Reports</div>
                <p class="feature-desc">Generate detailed usage and maintenance reports per building, room, or resource — giving administrators the insights they need to plan ahead.</p>
            </div>
            <div class="feature-card reveal reveal-delay-3">
                <div class="feature-num">06</div>
                <div class="feature-icon">🏫</div>
                <div class="feature-title">Multi-Campus Support</div>
                <p class="feature-desc">Manage multiple buildings and campuses under one account. Each space is organized, trackable, and accessible — wherever it is.</p>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section class="about-section" id="about">
    <div class="about-bg"></div>
    <div class="about-inner">
        <div class="about-left">
            <div class="section-eyebrow reveal">About Spacio</div>
            <h2 class="section-title reveal reveal-delay-1">Built for how your campus actually works.</h2>
            <p class="section-sub reveal reveal-delay-2">
                Spacio is a dedicated campus management platform built to reduce friction between students, teachers, and administrators. From a single reservation to a full inventory audit — it's all here.
            </p>
            <div class="about-tag-row reveal reveal-delay-3">
                <span class="about-tag">Web-based</span>
                <span class="about-tag">Multi-role access</span>
                <span class="about-tag">Real-time</span>
                <span class="about-tag">Multi-campus</span>
                <span class="about-tag">Est. 2026</span>
            </div>
        </div>

        <div class="about-right">
            <div class="about-card reveal reveal-delay-1">
                <div class="about-card-icon">🎓</div>
                <div class="about-card-body">
                    <div class="about-card-title">For Students</div>
                    <p class="about-card-desc">Reserve equipment, book lab sessions, and check computer availability — without the back-and-forth.</p>
                </div>
            </div>
            <div class="about-card reveal reveal-delay-2">
                <div class="about-card-icon">📋</div>
                <div class="about-card-body">
                    <div class="about-card-title">For Teachers</div>
                    <p class="about-card-desc">Monitor lab usage, report classroom issues, and get full visibility into space availability across both campuses.</p>
                </div>
            </div>
            <div class="about-card reveal reveal-delay-3">
                <div class="about-card-icon">🔧</div>
                <div class="about-card-body">
                    <div class="about-card-title">For Admins &amp; Staff</div>
                    <p class="about-card-desc">Full control over inventory, reports, issue resolution, and campus-wide resource management — from one dashboard.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA / LOGIN -->
<section class="cta-section" id="login-cta">
    <div class="cta-inner">
        <div class="section-eyebrow reveal">Get Started</div>
        <h2 class="section-title reveal reveal-delay-1">Ready to manage your campus smarter?</h2>
        <p class="section-sub reveal reveal-delay-2">Sign in with your campus credentials. Spacio automatically routes you to the right dashboard based on your role — student, teacher, or admin.</p>

        <div class="reveal reveal-delay-3">
            <a class="cta-login-btn" href="login.php">
                Sign In to Spacio
                <span class="arrow">→</span>
            </a>
        </div>

        <div class="campus-badges reveal reveal-delay-4">
            <span class="campus-badge">🏫 Main Campus</span>
            <span class="campus-badge">🏢 Satellite Campus</span>
            <span class="campus-badge">🔐 Role-based Access</span>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <a class="footer-logo" href="#">
        <div class="nav-logo-mark" style="width:28px;height:28px;font-size:12px;">S</div>
        <span class="nav-logo-text">Spacio</span>
    </a>
    <p class="footer-copy">&copy; 2026 <strong>Spacio</strong> &nbsp;·&nbsp; Campus Lab &amp; Classroom Management System</p>
</footer>

<script>
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 40);
    });

    // Scroll reveal
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.12 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>