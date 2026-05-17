<?php
// reset_password.php
// Place this at: /spacio/reset_password.php (same level as login.php, forgot_password.php)

session_start();
require_once "backend/config/database.php";
require_once "backend/config/helpers.php";
require_once "backend/config/auth.php";

// Redirect already-logged-in users
redirectIfLoggedIn();

$token   = trim($_GET['token'] ?? '');
$error   = null;
$success = null;

// ── Validate token exists and is not obviously junk ──────────────────────────
if (empty($token) || strlen($token) !== 64 || !ctype_xdigit($token)) {
    $error = "invalid_token";
}

// ── Handle form submission ───────────────────────────────────────────────────
if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $newPassword     = $_POST['new_password']     ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "Please fill in both password fields.";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $result = djangoPost('/api/v1/auth/reset-password/', [
            'token'            => $token,
            'new_password'     => $newPassword,
            'confirm_password' => $confirmPassword,
        ]);

        if ($result['success']) {
            $success = true;
            $_SESSION['reset_completed'] = true;
        } else {
            // Surface Django's error message
            $raw   = $result['data']['message'] ?? $result['data']['detail'] ?? "Something went wrong. Please request a new reset link.";
            $error = is_array($raw) ? implode(' ', $raw) : $raw;
        }
    }
}

$isInvalidToken = ($error === "invalid_token");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spacio — Reset Password</title>
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
        .back-link:hover .back-arrow { transform: translateX(-3px); }
        .back-arrow { font-size: 1rem; transition: transform .2s; display: inline-block; }

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

        /* Password strength meter on the left panel */
        .strength-guide {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .strength-guide li {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .guide-icon {
            width: 30px; height: 30px;
            border-radius: 8px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            display: grid;
            place-items: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .guide-text {
            font-family: 'Syne', sans-serif;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .03em;
            color: rgba(255,255,255,.35);
            line-height: 1.4;
        }

        .guide-text strong { color: rgba(255,255,255,.6); font-weight: 700; }

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

        /* ══ CARD ══ */
        .reset-card {
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

        .eyebrow-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); }

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
            margin-bottom: 36px;
            line-height: 1.6;
        }

        /* ══ ALERTS ══ */
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
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }

        /* Invalid-token state — full card replacement */
        .invalid-token-card {
            text-align: center;
        }

        .invalid-icon {
            font-size: 2.8rem;
            margin-bottom: 20px;
            display: block;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%,100% { opacity: 1; }
            50%      { opacity: .5; }
        }

        .invalid-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.03em;
            margin-bottom: 12px;
        }

        .invalid-msg {
            font-size: .88rem;
            color: rgba(255,255,255,.35);
            font-style: italic;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        /* Success state */
        .success-card { text-align: center; }

        .success-icon-wrap {
            width: 72px; height: 72px;
            background: rgba(74,146,82,.15);
            border: 2px solid rgba(116,187,122,.3);
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 2rem;
            margin: 0 auto 24px;
            animation: successPop .5s cubic-bezier(.34,1.56,.64,1) both;
        }

        @keyframes successPop {
            from { opacity: 0; transform: scale(.4); }
            to   { opacity: 1; transform: scale(1); }
        }

        .success-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.03em;
            margin-bottom: 12px;
        }

        .success-msg {
            font-size: .88rem;
            color: rgba(255,255,255,.35);
            font-style: italic;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        /* ══ FORM ══ */
        .form-group { margin-bottom: 20px; }

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

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 15px; top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            opacity: .4;
            pointer-events: none;
        }

        .form-group input[type="password"],
        .form-group input[type="text"] {
            width: 100%;
            padding: 14px 52px 14px 44px;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.1);
            border-radius: 10px;
            font-family: 'Literata', serif;
            font-size: .92rem;
            color: #fff;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }

        .form-group input::placeholder { color: rgba(255,255,255,.2); font-style: italic; }

        .form-group input:focus {
            border-color: rgba(116,187,122,.5);
            background: rgba(255,255,255,.09);
            box-shadow: 0 0 0 4px rgba(116,187,122,.08);
        }

        .form-group input.input-error {
            border-color: rgba(220,53,53,.5);
        }

        .form-group input.input-ok {
            border-color: rgba(116,187,122,.5);
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

        /* ── Password strength bar ── */
        .strength-bar-wrap {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .strength-segments {
            display: flex;
            gap: 4px;
            flex: 1;
        }

        .strength-seg {
            flex: 1;
            height: 3px;
            border-radius: 99px;
            background: rgba(255,255,255,.08);
            transition: background .3s;
        }

        .strength-label {
            font-family: 'Syne', sans-serif;
            font-size: .63rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255,255,255,.25);
            min-width: 42px;
            text-align: right;
            transition: color .3s;
        }

        .match-hint {
            margin-top: 6px;
            font-family: 'Syne', sans-serif;
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .04em;
            min-height: 16px;
            transition: color .2s;
        }

        /* ══ BUTTON ══ */
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
            transition: background .2s, transform .15s, box-shadow .2s, opacity .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .btn-submit::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,.1) 100%);
        }

        .btn-submit:hover:not(:disabled) {
            background: var(--gold-lt);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(196,154,42,.35);
        }

        .btn-submit:active:not(:disabled) { transform: translateY(0); }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; }
        .btn-arrow { transition: transform .2s; font-size: 1rem; }
        .btn-submit:hover:not(:disabled) .btn-arrow { transform: translateX(4px); }

        .btn-secondary {
            display: block;
            width: 100%;
            padding: 14px;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.1);
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-size: .85rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: rgba(255,255,255,.6);
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: background .2s, border-color .2s, color .2s, transform .15s;
            margin-bottom: 24px;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,.1);
            border-color: rgba(255,255,255,.2);
            color: #fff;
            transform: translateY(-1px);
        }

        .back-to-login {
            text-align: center;
            font-size: .82rem;
            color: rgba(255,255,255,.3);
            font-style: italic;
        }

        .back-to-login a {
            color: var(--sprout);
            text-decoration: none;
            font-style: normal;
            font-family: 'Syne', sans-serif;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .04em;
            transition: color .2s;
        }

        .back-to-login a:hover { color: var(--mist); }

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

        .footer-copy { font-size: .7rem; color: rgba(255,255,255,.2); letter-spacing: .06em; }
        .footer-copy strong { color: rgba(185,222,187,.4); font-weight: 500; }
        .footer-right { font-size: .7rem; color: rgba(255,255,255,.15); font-style: italic; }

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

        <a class="back-link" href="login.php">
            <span class="back-arrow">←</span> Back to Sign In
        </a>

        <div class="brand">
            <div class="brand-mark">S</div>
            <span class="brand-name">Spacio</span>
        </div>

        <?php if ($success): ?>
        <h2 class="left-headline">
            You're all<br>
            <span class="accent">set and</span><br>
            ready to go.
        </h2>
        <p class="left-sub">Your password has been updated. Head back and sign in with your new credentials.</p>
        <?php elseif ($isInvalidToken): ?>
        <h2 class="left-headline">
            Link has<br>
            <span class="accent">expired or</span><br>
            is invalid.
        </h2>
        <p class="left-sub">Reset links are single-use and expire after 1 hour. Request a fresh one from the forgot password page.</p>
        <?php else: ?>
        <h2 class="left-headline">
            Choose a<br>
            <span class="accent">strong new</span><br>
            password.
        </h2>
        <p class="left-sub">Pick something you haven't used before. Your new password will be active immediately.</p>

        <ul class="strength-guide">
            <li>
                <div class="guide-icon">🔡</div>
                <div class="guide-text"><strong>8+ characters</strong> — the longer, the better.</div>
            </li>
            <li>
                <div class="guide-icon">🔢</div>
                <div class="guide-text"><strong>Mix letters &amp; numbers</strong> for extra strength.</div>
            </li>
            <li>
                <div class="guide-icon">🔣</div>
                <div class="guide-text"><strong>Special characters</strong> like !@# add another layer.</div>
            </li>
            <li>
                <div class="guide-icon">🚫</div>
                <div class="guide-text"><strong>Avoid reusing</strong> a password you've used elsewhere.</div>
            </li>
        </ul>
        <?php endif; ?>
    </div>

    <!-- ══ RIGHT PANEL ══ -->
    <div class="panel-right">
        <div class="reset-card">

            <?php if ($success): ?>
            <!-- ── SUCCESS STATE ── -->
            <div class="success-card">
                <div class="success-icon-wrap">✓</div>

                <h1 class="success-title">Password updated!</h1>
                <p class="success-msg">
                    Your Spacio password has been reset successfully.<br>
                    You can now sign in with your new password.
                </p>

                <a href="login.php" class="btn-submit" style="text-decoration:none;" id="redirectBtn">
                    Back to Sign In <span id="countdown">(5)</span>
                    <span class="btn-arrow">→</span>
                </a>
                <script>
                    let secs = 5;
                    const el = document.getElementById('countdown');
                    const timer = setInterval(() => {
                        secs--;
                        el.textContent = '(' + secs + ')';
                        if (secs <= 0) {
                            clearInterval(timer);
                            window.close();
                            window.location.href = 'login.php';
                        }
                    }, 1000);
                </script>
            </div>

            <?php elseif ($isInvalidToken): ?>
            <!-- ── INVALID TOKEN STATE ── -->
            <div class="invalid-token-card">
                <span class="invalid-icon">🔗</span>

                <h1 class="invalid-title">Link invalid or expired</h1>
                <p class="invalid-msg">
                    This password reset link is no longer valid.<br>
                    It may have already been used or it expired after 1 hour.
                </p>

                <a href="forgot_password.php" class="btn-submit" style="text-decoration:none;">
                    Request a new link
                    <span class="btn-arrow">→</span>
                </a>

                <p class="back-to-login">
                    Remembered it? <a href="login.php">Back to Sign In</a>
                </p>
            </div>

            <?php else: ?>
            <!-- ── RESET FORM ── -->
            <div class="card-eyebrow">
                <div class="eyebrow-dot"></div>
                <span class="eyebrow-text">Set New Password</span>
            </div>

            <h1 class="card-title">Create a new<br>password</h1>
            <p class="card-sub">Must be at least 8 characters. Enter it twice to confirm.</p>

            <?php if ($error && !$isInvalidToken): ?>
            <div class="error-box">
                <span>⚠</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <!-- New Password -->
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="At least 8 characters"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="pw-toggle" data-target="new_password" id="toggle1">Show</button>
                    </div>
                    <!-- Strength bar -->
                    <div class="strength-bar-wrap">
                        <div class="strength-segments" id="strengthSegs">
                            <div class="strength-seg" id="seg1"></div>
                            <div class="strength-seg" id="seg2"></div>
                            <div class="strength-seg" id="seg3"></div>
                            <div class="strength-seg" id="seg4"></div>
                        </div>
                        <span class="strength-label" id="strengthLabel"></span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Repeat your new password"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="pw-toggle" data-target="confirm_password" id="toggle2">Show</button>
                    </div>
                    <p class="match-hint" id="matchHint"></p>
                </div>

                <button type="submit" name="reset" class="btn-submit" id="submitBtn" disabled>
                    Reset Password
                    <span class="btn-arrow">→</span>
                </button>
            </form>

            <p class="back-to-login">
                Remembered it? <a href="login.php">Back to Sign In</a>
            </p>
            <?php endif; ?>

        </div>
    </div>

</div>

<footer>
    <p class="footer-copy">&copy; 2026 <strong>Spacio</strong> &nbsp;·&nbsp; Campus Lab &amp; Classroom Management</p>
    <p class="footer-right">Secure password reset</p>
</footer>

<script>
// ── Show/hide password toggles ───────────────────────────────────────────────
document.querySelectorAll('.pw-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const input  = document.getElementById(btn.dataset.target);
        const hidden = input.type === 'password';
        input.type   = hidden ? 'text' : 'password';
        btn.textContent = hidden ? 'Hide' : 'Show';
    });
});

// ── Strength + match logic ───────────────────────────────────────────────────
const pwInput      = document.getElementById('new_password');
const cfInput      = document.getElementById('confirm_password');
const submitBtn    = document.getElementById('submitBtn');
const matchHint    = document.getElementById('matchHint');
const strengthLabel = document.getElementById('strengthLabel');
const segs         = [
    document.getElementById('seg1'),
    document.getElementById('seg2'),
    document.getElementById('seg3'),
    document.getElementById('seg4'),
];

const COLORS = {
    weak:   '#ef4444',
    fair:   '#f97316',
    good:   '#eab308',
    strong: '#74bb7a',
};

function scorePassword(pw) {
    let score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[0-9]/.test(pw) && /[a-zA-Z]/.test(pw)) score++;
    if (/[^a-zA-Z0-9]/.test(pw)) score++;
    return score; // 0–4
}

function updateStrength() {
    const pw    = pwInput.value;
    const score = pw.length === 0 ? 0 : scorePassword(pw);
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', COLORS.weak, COLORS.fair, COLORS.good, COLORS.strong];

    segs.forEach((seg, i) => {
        seg.style.background = i < score ? colors[score] : 'rgba(255,255,255,.08)';
    });

    strengthLabel.textContent = pw.length ? labels[score] : '';
    strengthLabel.style.color = pw.length ? colors[score] : 'rgba(255,255,255,.25)';
}

function updateMatch() {
    const pw = pwInput.value;
    const cf = cfInput.value;

    if (!cf.length) {
        matchHint.textContent = '';
        cfInput.classList.remove('input-ok', 'input-error');
        return;
    }

    if (pw === cf) {
        matchHint.textContent = '✓ Passwords match';
        matchHint.style.color = '#74bb7a';
        cfInput.classList.add('input-ok');
        cfInput.classList.remove('input-error');
    } else {
        matchHint.textContent = '✗ Passwords do not match';
        matchHint.style.color = '#f87171';
        cfInput.classList.add('input-error');
        cfInput.classList.remove('input-ok');
    }
}

function checkSubmittable() {
    const pw    = pwInput.value;
    const cf    = cfInput.value;
    const valid = pw.length >= 8 && pw === cf;
    submitBtn.disabled = !valid;
}

pwInput.addEventListener('input', () => { updateStrength(); updateMatch(); checkSubmittable(); });
cfInput.addEventListener('input', () => { updateMatch(); checkSubmittable(); });
</script>

</body>
</html>