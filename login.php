<?php
// login.php (root)
require_once "backend/config/database.php";
require_once "backend/config/helpers.php";
require_once "backend/config/auth.php";

// Redirect already-logged-in users
redirectIfLoggedIn();

$error = null;

if (isset($_POST['login'])) {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    // ── Call Django auth API ──
    $result = djangoPost('/api/v1/auth/login/', [
        'email'    => $email,
        'password' => $password,
    ]);

    if ($result['success'] && isset($result['data']['access'])) {

        $data = $result['data'];

        // Build user array from Django response
        $user = [
            'id'    => $data['user']['id'],
            'name'  => $data['user']['name'],
            'email' => $data['user']['email'],
            'role'  => $data['user']['role'],
            'campus' => $data['user']['campus'] ?? '',
        ];

        // Store JWT + user in session (defined in auth.php)
        storeAuthSession($user, $data['access'], $data['refresh']);

        // Redirect to role dashboard
        $dashboards = [
    'student' => '/spacio/frontend/student/dashboard.php',
    'teacher' => '/spacio/frontend/teacher/dashboard.php',
    'admin'   => '/spacio/frontend/admin/dashboard.php',
];
header("Location: " . ($dashboards[$user['role']] ?? '/spacio/index.php'));
        exit;

    } else {
        // Show error from Django response, or a fallback message
        $raw   = $result['data']['detail'] ?? $result['data']['message'] ?? "Invalid email or password. Please try again.";
        $error = is_array($raw) ? implode(' ', array_map(fn($v) => is_array($v) ? implode(' ', $v) : $v, $raw)) : $raw;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spacio — Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Literata:ital,wght@0,300;0,400;1,300;1,400&display=swap" rel="stylesheet">
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
        }

        html, body { height: 100%; }

        body {
            font-family: 'Literata', Georgia, serif;
            background: var(--night);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* ══ BACKGROUND ══ */
        .page-bg {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 60% 60% at 20% 50%, rgba(46,107,52,.28) 0%, transparent 65%),
                radial-gradient(ellipse 50% 50% at 80% 20%, rgba(196,154,42,.10) 0%, transparent 60%),
                radial-gradient(ellipse 40% 60% at 70% 80%, rgba(74,146,82,.12) 0%, transparent 55%);
            pointer-events: none;
        }

        .page-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image: radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(ellipse 90% 90% at 50% 50%, black 20%, transparent 100%);
            pointer-events: none;
        }

        /* ══ LAYOUT ══ */
        .page-wrap {
            position: relative; z-index: 1;
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ══ LEFT PANEL ══ */
        .panel-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 64px;
            position: relative;
        }

        .back-link {
            position: absolute;
            top: 36px; left: 48px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Syne', sans-serif;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
            text-decoration: none;
            transition: color .2s;
        }

        .back-link:hover { color: rgba(255,255,255,.75); }

        .back-arrow {
            font-size: 1rem;
            transition: transform .2s;
        }

        .back-link:hover .back-arrow { transform: translateX(-3px); }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 56px;
        }

        .brand-mark {
            width: 40px; height: 40px;
            background: var(--gold);
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-family: 'Syne', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: var(--night);
        }

        .brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.01em;
        }

        .left-headline {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.08;
            letter-spacing: -.03em;
            margin-bottom: 20px;
        }

        .left-headline .accent {
            color: var(--sprout);
            font-style: italic;
            font-family: 'Literata', serif;
            font-weight: 300;
        }

        .left-sub {
            font-size: .92rem;
            color: rgba(255,255,255,.4);
            font-style: italic;
            line-height: 1.8;
            max-width: 360px;
            margin-bottom: 52px;
        }

        .left-features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .left-features li {
            display: flex;
            align-items: center;
            gap: 14px;
            font-family: 'Syne', sans-serif;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .04em;
            color: rgba(255,255,255,.45);
        }

        .feat-icon {
            width: 34px; height: 34px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .left-deco-ring {
            position: absolute;
            width: 420px; height: 420px;
            border-radius: 50%;
            border: 1px solid rgba(116,187,122,.07);
            bottom: -120px; left: -100px;
            pointer-events: none;
        }

        /* ══ RIGHT PANEL ══ */
        .panel-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 64px;
            position: relative;
        }

        .panel-right::before {
            content: '';
            position: absolute;
            top: 10%; bottom: 10%; left: 0;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(255,255,255,.07) 30%, rgba(255,255,255,.07) 70%, transparent);
        }

        /* ══ LOGIN CARD ══ */
        .login-card {
            width: 100%;
            max-width: 420px;
            animation: riseIn .65s cubic-bezier(.22,1,.36,1) both;
        }

        @keyframes riseIn {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .eyebrow-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--gold);
        }

        .eyebrow-text {
            font-family: 'Syne', sans-serif;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 2.1rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.03em;
            line-height: 1.1;
            margin-bottom: 8px;
        }

        .card-sub {
            font-size: .88rem;
            color: rgba(255,255,255,.35);
            font-style: italic;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        /* ══ ERROR ══ */
        .error-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(220,53,53,.12);
            border: 1px solid rgba(220,53,53,.3);
            border-radius: 10px;
            padding: 13px 16px;
            margin-bottom: 24px;
            font-size: .83rem;
            color: #f87171;
            font-style: italic;
            animation: shake .4s cubic-bezier(.36,.07,.19,.97);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-6px); }
            40%       { transform: translateX(6px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }

        /* ══ SESSION EXPIRED NOTICE ══ */
        .notice-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(196,154,42,.10);
            border: 1px solid rgba(196,154,42,.3);
            border-radius: 10px;
            padding: 13px 16px;
            margin-bottom: 24px;
            font-size: .83rem;
            color: #e2bb5a;
            font-style: italic;
        }

        /* ══ FORM ══ */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-family: 'Syne', sans-serif;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255,255,255,.5);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px; top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            opacity: .4;
            pointer-events: none;
        }

        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="text"] {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.1);
            border-radius: 10px;
            font-family: 'Literata', serif;
            font-size: .92rem;
            color: #fff;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }

        .form-group input::placeholder {
            color: rgba(255,255,255,.2);
            font-style: italic;
        }

        .form-group input:focus {
            border-color: rgba(116,187,122,.5);
            background: rgba(255,255,255,.09);
            box-shadow: 0 0 0 4px rgba(116,187,122,.08);
        }

        .pw-toggle {
            position: absolute;
            right: 13px; top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-family: 'Syne', sans-serif;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            padding: 4px 6px;
            transition: color .2s;
        }

        .pw-toggle:hover { color: var(--sprout); }

        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            accent-color: var(--sprout);
            width: 14px; height: 14px;
            cursor: pointer;
        }

        .remember-label span {
            font-size: .78rem;
            color: rgba(255,255,255,.35);
            font-style: italic;
        }

        .forgot-link {
            font-family: 'Syne', sans-serif;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .06em;
            color: rgba(255,255,255,.3);
            text-decoration: none;
            transition: color .2s;
        }

        .forgot-link:hover { color: var(--gold); }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: var(--gold);
            color: var(--night);
            border: none;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-size: .88rem;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .2s, transform .15s, box-shadow .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,.1) 100%);
        }

        .btn-submit:hover {
            background: var(--gold-lt);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(196,154,42,.35);
        }

        .btn-submit:active { transform: translateY(0); }

        .btn-arrow { transition: transform .2s; font-size: 1rem; }
        .btn-submit:hover .btn-arrow { transform: translateX(4px); }

        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 28px 0;
        }

        .divider hr { flex: 1; border: none; border-top: 1px solid rgba(255,255,255,.07); }
        .divider span { font-size: .68rem; color: rgba(255,255,255,.2); letter-spacing: .1em; text-transform: uppercase; font-family: 'Syne', sans-serif; }

        .signup-row {
            text-align: center;
            font-size: .82rem;
            color: rgba(255,255,255,.3);
            font-style: italic;
        }

        .signup-row a {
            color: var(--sprout);
            text-decoration: none;
            font-style: normal;
            font-family: 'Syne', sans-serif;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .04em;
            transition: color .2s;
        }

        .signup-row a:hover { color: var(--mist); }

        footer {
            position: relative; z-index: 1;
            background: rgba(0,0,0,.2);
            border-top: 1px solid rgba(255,255,255,.04);
            padding: 18px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-copy {
            font-size: .7rem;
            color: rgba(255,255,255,.2);
            letter-spacing: .06em;
        }

        .footer-copy strong { color: rgba(185,222,187,.4); font-weight: 500; }

        .footer-right {
            font-size: .7rem;
            color: rgba(255,255,255,.15);
            font-style: italic;
        }

        @media (max-width: 860px) {
            .page-wrap { grid-template-columns: 1fr; }
            .panel-left { display: none; }
            .panel-right { padding: 60px 28px; min-height: 100vh; }
            .panel-right::before { display: none; }
            footer { padding: 18px 28px; }
        }
    </style>
</head>
<body>

<div class="page-bg"></div>
<div class="page-grid"></div>

<div class="page-wrap">

    <!-- ══ LEFT PANEL ══ -->
    <div class="panel-left">
        <div class="left-deco-ring"></div>

        <a class="back-link" href="index.php">
            <span class="back-arrow">←</span> Back to Spacio
        </a>

        <div class="brand">
            <div class="brand-mark">S</div>
            <span class="brand-name">Spacio</span>
        </div>

        <h2 class="left-headline">
            Your campus,<br>
            <span class="accent">all in one</span><br>
            dashboard.
        </h2>

        <p class="left-sub">
            Sign in to access reservations, lab management, inventory, and reporting — tailored to your role.
        </p>

        <ul class="left-features">
            <li>
                <div class="feat-icon">🗓️</div>
                Lab &amp; room reservations
            </li>
            <li>
                <div class="feat-icon">📦</div>
                Inventory &amp; equipment tracking
            </li>
            <li>
                <div class="feat-icon">🚨</div>
                Issue reporting &amp; resolution
            </li>
            <li>
                <div class="feat-icon">📊</div>
                Campus-wide reports
            </li>
        </ul>
    </div>

    <!-- ══ RIGHT PANEL ══ -->
    <div class="panel-right">
        <div class="login-card">

            <div class="card-eyebrow">
                <div class="eyebrow-dot"></div>
                <span class="eyebrow-text">Campus Portal</span>
            </div>

            <h1 class="card-title">Sign in to<br>Spacio</h1>
            <p class="card-sub">Enter your credentials — we'll route you to the right dashboard automatically.</p>

            <?php if (($_GET['reason'] ?? '') === 'session_expired'): ?>
            <div class="notice-box">
                <span>🕐</span>
                <span>Your session expired. Please sign in again.</span>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="error-box">
                <span>⚠</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <span class="input-icon">✉</span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@campus.edu"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="pw-toggle" id="pwToggle">Show</button>
                    </div>
                </div>

                <div class="options-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember">
                        <span>Remember me for 30 days</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" name="login" class="btn-submit">
                    Sign In
                    <span class="btn-arrow">→</span>
                </button>

            </form>

            <div class="divider">
                <hr><span>New to Spacio?</span><hr>
            </div>

            <p class="signup-row">
                Don't have an account? <a href="register.php">Create one</a>
            </p>

        </div>
    </div>

</div>

<footer>
    <p class="footer-copy">&copy; 2026 <strong>Spacio</strong> &nbsp;·&nbsp; Campus Lab &amp; Classroom Management</p>
    <p class="footer-right">Role-based access — students, teachers &amp; admins</p>
</footer>

<script>
    const pwToggle = document.getElementById('pwToggle');
    const pwInput  = document.getElementById('password');

    pwToggle.addEventListener('click', () => {
        const isHidden = pwInput.type === 'password';
        pwInput.type   = isHidden ? 'text' : 'password';
        pwToggle.textContent = isHidden ? 'Hide' : 'Show';
    });
</script>

</body>
</html>