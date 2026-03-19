<?php
// register.php (root)
require_once "backend/config/database.php";
require_once "backend/config/helpers.php";
require_once "backend/config/auth.php";

// Redirect already-logged-in users
redirectIfLoggedIn();

$errorFields = [];
$success     = null;

if (isset($_POST['register'])) {

    $name       = trim($_POST['name']       ?? '');
    $school_id  = trim($_POST['school_id']  ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   =      $_POST['password']   ?? '';
    $role       =      $_POST['role']       ?? '';
    $campus     = trim($_POST['campus']     ?? '');
    $course     = trim($_POST['course']     ?? '');
    $department = trim($_POST['department'] ?? '');

    // ── Client-side validation (keep this — catches errors before hitting Django) ──
    if (empty($name))                                       $errorFields['name']      = "Full name is required.";
    if (empty($school_id))                                  $errorFields['school_id'] = "Student / Teacher ID is required.";
    if (empty($email))                                      $errorFields['email']     = "Email address is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))     $errorFields['email']     = "Please enter a valid email address.";
    if (empty($password))                                   $errorFields['password']  = "Password is required.";
    elseif (strlen($password) < 8)                         $errorFields['password']  = "Password must be at least 8 characters.";
    if (empty($role))                                       $errorFields['role']      = "Please select a role.";
    if (empty($campus))                                     $errorFields['campus']    = "Campus is required.";
    if ($role === 'student' && empty($course))              $errorFields['course']    = "Course is required for students.";
    if ($role === 'teacher' && empty($department))          $errorFields['department']= "Department is required for teachers.";

    // ── Call Django register API if validation passes ──
    if (empty($errorFields)) {

        $result = djangoPost('/api/v1/auth/register/', [
            'name'       => $name,
            'school_id'  => $school_id,
            'email'      => $email,
            'password'   => $password,
            'role'       => $role,
            'campus'     => $campus,
            'course'     => $course,
            'department' => $department,
        ]);

        if ($result['success']) {
            $success = true;
            // Clear form values on success
            $old = ['name'=>'','school_id'=>'','email'=>'','role'=>'','campus'=>'','course'=>'','department'=>''];

        } else {
            // Map Django field errors back to PHP $errorFields
            $data = $result['data'] ?? [];

            // Django REST Framework returns field errors as arrays e.g. {"email": ["already exists"]}
            $fieldMap = ['name','school_id','email','password','role','campus','course','department'];
            foreach ($fieldMap as $field) {
                if (!empty($data[$field])) {
                    $errorFields[$field] = is_array($data[$field])
                        ? implode(' ', $data[$field])
                        : $data[$field];
                }
            }

            // Catch non-field errors
            if (empty($errorFields)) {
                $errorFields['general'] = $data['detail']
                    ?? $data['message']
                    ?? "Registration failed. Please try again.";
            }
        }
    }
}

// Retain old form values on error
if (!isset($old)) {
    $old = [
        'name'       => htmlspecialchars($_POST['name']       ?? ''),
        'school_id'  => htmlspecialchars($_POST['school_id']  ?? ''),
        'email'      => htmlspecialchars($_POST['email']      ?? ''),
        'role'       =>                  $_POST['role']       ?? '',
        'campus'     => htmlspecialchars($_POST['campus']     ?? ''),
        'course'     => htmlspecialchars($_POST['course']     ?? ''),
        'department' => htmlspecialchars($_POST['department'] ?? ''),
    ];
}

function fieldClass(array $err, string $key): string {
    return isset($err[$key]) ? ' invalid' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spacio — Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Literata:ital,wght@0,300;0,400;1,300;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --night:   #0b1f0d;
            --forest:  #122b14;
            --canopy:  #1e4422;
            --fern:    #2e6b34;
            --sprout:  #74bb7a;
            --mist:    #b9debb;
            --gold:    #c49a2a;
            --gold-lt: #e2bb5a;
            --cream:   #f8f4ee;
            --ink:     #141414;
            --red:     #f87171;
            --green:   #6ee7b7;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Literata', Georgia, serif;
            background: var(--night);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .page-bg {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 55% 55% at 15% 30%, rgba(46,107,52,.26) 0%, transparent 65%),
                radial-gradient(ellipse 45% 50% at 85% 70%, rgba(196,154,42,.09) 0%, transparent 60%),
                radial-gradient(ellipse 50% 45% at 75% 10%, rgba(74,146,82,.11) 0%, transparent 55%);
        }

        .page-grid {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(ellipse 90% 90% at 50% 50%, black 20%, transparent 100%);
        }

        .page-wrap {
            position: relative; z-index: 1;
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            min-height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .panel-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 60px;
            position: relative;
            overflow: hidden;
        }

        .left-deco {
            position: absolute;
            width: 460px; height: 460px;
            border-radius: 50%;
            border: 1px solid rgba(116,187,122,.07);
            bottom: -130px; left: -110px;
            pointer-events: none;
        }

        .left-deco-2 {
            width: 280px; height: 280px;
            border-color: rgba(196,154,42,.06);
            top: -60px; right: -40px;
        }

        .back-link {
            position: absolute;
            top: 36px; left: 48px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Syne', sans-serif;
            font-size: .72rem; font-weight: 600;
            letter-spacing: .1em; text-transform: uppercase;
            color: rgba(255,255,255,.3);
            text-decoration: none;
            transition: color .2s;
        }

        .back-link:hover { color: rgba(255,255,255,.65); }
        .back-arrow { transition: transform .2s; }
        .back-link:hover .back-arrow { transform: translateX(-3px); }

        .brand {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 52px;
        }

        .brand-mark {
            width: 38px; height: 38px;
            background: var(--gold); border-radius: 9px;
            display: grid; place-items: center;
            font-family: 'Syne', sans-serif;
            font-size: 16px; font-weight: 800;
            color: var(--night);
        }

        .brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem; font-weight: 800;
            color: #fff; letter-spacing: -.01em;
        }

        .left-headline {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.9rem, 2.8vw, 2.6rem);
            font-weight: 800; color: #fff;
            line-height: 1.09; letter-spacing: -.03em;
            margin-bottom: 18px;
        }

        .left-headline .accent {
            color: var(--sprout);
            font-style: italic;
            font-family: 'Literata', serif;
            font-weight: 300;
        }

        .left-sub {
            font-size: .9rem; color: rgba(255,255,255,.38);
            font-style: italic; line-height: 1.8;
            max-width: 340px; margin-bottom: 48px;
        }

        .steps { display: flex; flex-direction: column; gap: 0; }

        .step {
            display: flex; align-items: flex-start; gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(255,255,255,.05);
        }

        .step:last-child { border-bottom: none; }

        .step-num {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 1.5px solid rgba(196,154,42,.4);
            display: grid; place-items: center;
            font-family: 'Syne', sans-serif;
            font-size: .68rem; font-weight: 800;
            color: var(--gold);
            flex-shrink: 0; margin-top: 1px;
        }

        .step-title {
            font-family: 'Syne', sans-serif;
            font-size: .8rem; font-weight: 700;
            color: rgba(255,255,255,.6);
            letter-spacing: .02em; margin-bottom: 2px;
        }

        .step-desc {
            font-size: .75rem; color: rgba(255,255,255,.28);
            font-style: italic; line-height: 1.6;
        }

        /* ── RIGHT PANEL ── */
        .panel-right {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 60px 64px 60px 52px;
            overflow-y: auto;
            position: relative;
        }

        .panel-right::before {
            content: '';
            position: absolute;
            top: 10%; bottom: 10%; left: 0;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(255,255,255,.06) 30%, rgba(255,255,255,.06) 70%, transparent);
        }

        .register-card {
            width: 100%;
            max-width: 500px;
            padding: 16px 0;
            animation: riseIn .65s cubic-bezier(.22,1,.36,1) both;
        }

        @keyframes riseIn {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            margin-bottom: 18px;
        }

        .eyebrow-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); }

        .eyebrow-text {
            font-family: 'Syne', sans-serif;
            font-size: .68rem; font-weight: 700;
            letter-spacing: .16em; text-transform: uppercase;
            color: var(--gold);
        }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 2rem; font-weight: 800;
            color: #fff; letter-spacing: -.03em;
            line-height: 1.1; margin-bottom: 6px;
        }

        .card-sub {
            font-size: .87rem; color: rgba(255,255,255,.33);
            font-style: italic; line-height: 1.6;
            margin-bottom: 36px;
        }

        .alert {
            display: flex; align-items: flex-start; gap: 11px;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: .83rem;
            font-style: italic;
            margin-bottom: 26px;
            animation: riseIn .4s both;
        }

        .alert-icon { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

        .alert.error {
            background: rgba(220,53,53,.11);
            border: 1px solid rgba(220,53,53,.28);
            color: var(--red);
        }

        .alert.success {
            background: rgba(52,199,89,.1);
            border: 1px solid rgba(52,199,89,.25);
            color: var(--green);
        }

        .alert.success a {
            color: var(--sprout);
            font-style: normal;
            font-family: 'Syne', sans-serif;
            font-size: .8rem; font-weight: 700;
            text-decoration: none;
        }

        .alert.success a:hover { color: var(--mist); }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group { margin-bottom: 18px; }
        .form-group.full { grid-column: 1 / -1; }

        .form-group label {
            display: block;
            font-family: 'Syne', sans-serif;
            font-size: .68rem; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase;
            color: rgba(255,255,255,.45);
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            font-size: 14px; opacity: .35;
            pointer-events: none;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 13px 14px 13px 42px;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.09);
            border-radius: 10px;
            font-family: 'Literata', serif;
            font-size: .9rem; color: #fff;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
            appearance: none;
        }

        .form-group select { padding-left: 42px; cursor: pointer; }
        .form-group input::placeholder { color: rgba(255,255,255,.18); font-style: italic; }

        .form-group input:focus,
        .form-group select:focus {
            border-color: rgba(116,187,122,.45);
            background: rgba(255,255,255,.09);
            box-shadow: 0 0 0 4px rgba(116,187,122,.07);
        }

        .form-group input.invalid,
        .form-group select.invalid {
            border-color: rgba(248,113,113,.45);
            background: rgba(248,113,113,.05);
        }

        .form-group input.invalid:focus,
        .form-group select.invalid:focus {
            box-shadow: 0 0 0 4px rgba(248,113,113,.07);
        }

        .select-wrap::after {
            content: '▾';
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            font-size: .75rem;
            color: rgba(255,255,255,.3);
            pointer-events: none;
        }

        .pw-toggle {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer;
            font-family: 'Syne', sans-serif;
            font-size: .65rem; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase;
            color: rgba(255,255,255,.28);
            padding: 4px 6px;
            transition: color .2s;
        }

        .pw-toggle:hover { color: var(--sprout); }

        .field-hint {
            min-height: 18px;
            font-size: .73rem;
            color: var(--red);
            font-style: italic;
            margin-top: 5px;
            padding-left: 2px;
            line-height: 1.4;
        }

        .cond-field {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height .35s cubic-bezier(.22,1,.36,1), opacity .3s ease, margin .3s ease;
            margin-bottom: 0;
        }

        .cond-field.visible {
            max-height: 100px;
            opacity: 1;
            margin-bottom: 18px;
        }

        .strength-bar { display: flex; gap: 4px; margin-top: 7px; }

        .strength-seg {
            flex: 1; height: 3px;
            border-radius: 2px;
            background: rgba(255,255,255,.08);
            transition: background .3s;
        }

        .form-divider {
            display: flex; align-items: center; gap: 12px;
            margin: 6px 0 22px;
        }

        .form-divider hr { flex: 1; border: none; border-top: 1px solid rgba(255,255,255,.07); }

        .form-divider span {
            font-family: 'Syne', sans-serif;
            font-size: .65rem; color: rgba(255,255,255,.2);
            letter-spacing: .1em; text-transform: uppercase;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: var(--gold);
            color: var(--night);
            border: none; border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-size: .88rem; font-weight: 800;
            letter-spacing: .07em; text-transform: uppercase;
            cursor: pointer;
            transition: background .2s, transform .15s, box-shadow .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            position: relative; overflow: hidden;
            margin-top: 8px;
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
        .btn-arrow { transition: transform .2s; }
        .btn-submit:hover .btn-arrow { transform: translateX(4px); }

        .signin-row {
            text-align: center;
            font-size: .83rem;
            color: rgba(255,255,255,.28);
            font-style: italic;
            margin-top: 24px;
        }

        .signin-row a {
            color: var(--sprout);
            text-decoration: none;
            font-style: normal;
            font-family: 'Syne', sans-serif;
            font-size: .78rem; font-weight: 700;
            transition: color .2s;
        }

        .signin-row a:hover { color: var(--mist); }

        footer {
            position: relative; z-index: 1;
            background: rgba(0,0,0,.2);
            border-top: 1px solid rgba(255,255,255,.04);
            padding: 16px 48px;
            display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }

        .footer-copy { font-size: .7rem; color: rgba(255,255,255,.18); letter-spacing: .06em; }
        .footer-copy strong { color: rgba(185,222,187,.35); font-weight: 500; }

        @media (max-width: 900px) {
            .page-wrap { grid-template-columns: 1fr; }
            .panel-left { display: none; }
            .panel-right { padding: 60px 28px; }
            .panel-right::before { display: none; }
            footer { padding: 16px 28px; }
        }

        @media (max-width: 480px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="page-bg"></div>
<div class="page-grid"></div>

<div class="page-wrap">

    <!-- ══ LEFT PANEL ══ -->
    <div class="panel-left">
        <div class="left-deco"></div>
        <div class="left-deco left-deco-2"></div>

        <a class="back-link" href="index.php">
            <span class="back-arrow">←</span> Back to Spacio
        </a>

        <div class="brand">
            <div class="brand-mark">S</div>
            <span class="brand-name">Spacio</span>
        </div>

        <h2 class="left-headline">
            Join your<br>
            <span class="accent">campus</span><br>
            community.
        </h2>

        <p class="left-sub">
            Create your account in minutes. Once registered, you'll have access to everything Spacio offers — based on your role.
        </p>

        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-body">
                    <div class="step-title">Fill in your details</div>
                    <div class="step-desc">Your name, ID, email, and a secure password.</div>
                </div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-body">
                    <div class="step-title">Choose your role</div>
                    <div class="step-desc">Student or teacher — each gets a tailored dashboard.</div>
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-body">
                    <div class="step-title">Start using Spacio</div>
                    <div class="step-desc">Reserve labs, track equipment, and report issues — instantly.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ RIGHT PANEL ══ -->
    <div class="panel-right">
        <div class="register-card">

            <div class="card-eyebrow">
                <div class="eyebrow-dot"></div>
                <span class="eyebrow-text">New Account</span>
            </div>

            <h1 class="card-title">Create your<br>account</h1>
            <p class="card-sub">All fields are required unless marked optional.</p>

            <?php if (!empty($errorFields['general'])): ?>
            <div class="alert error">
                <span class="alert-icon">⚠</span>
                <span><?= htmlspecialchars($errorFields['general']) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert success">
                <span class="alert-icon">✓</span>
                <span>Account created! <a href="login.php">Sign in now →</a></span>
            </div>
            <?php endif; ?>

            <form id="regForm" method="POST" novalidate>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <div class="input-wrap">
                            <span class="input-icon">👤</span>
                            <input type="text" id="name" name="name"
                                placeholder="Juan Dela Cruz"
                                value="<?= $old['name'] ?>"
                                class="<?= fieldClass($errorFields,'name') ?>">
                        </div>
                        <div class="field-hint"><?= $errorFields['name'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="school_id">Student / Teacher ID</label>
                        <div class="input-wrap">
                            <span class="input-icon">🪪</span>
                            <input type="text" id="school_id" name="school_id"
                                placeholder="e.g. 2024-00123"
                                value="<?= $old['school_id'] ?>"
                                class="<?= fieldClass($errorFields,'school_id') ?>">
                        </div>
                        <div class="field-hint"><?= $errorFields['school_id'] ?? '' ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <span class="input-icon">✉</span>
                        <input type="email" id="email" name="email"
                            placeholder="you@campus.edu"
                            value="<?= $old['email'] ?>"
                            autocomplete="email"
                            class="<?= fieldClass($errorFields,'email') ?>">
                    </div>
                    <div class="field-hint"><?= $errorFields['email'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password"
                            placeholder="Minimum 8 characters"
                            autocomplete="new-password"
                            class="<?= fieldClass($errorFields,'password') ?>">
                        <button type="button" class="pw-toggle" id="pwToggle">Show</button>
                    </div>
                    <div class="strength-bar" id="strengthBar">
                        <div class="strength-seg" id="s1"></div>
                        <div class="strength-seg" id="s2"></div>
                        <div class="strength-seg" id="s3"></div>
                        <div class="strength-seg" id="s4"></div>
                    </div>
                    <div class="field-hint"><?= $errorFields['password'] ?? '' ?></div>
                </div>

                <div class="form-divider">
                    <hr><span>Campus Info</span><hr>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role</label>
                        <div class="input-wrap select-wrap">
                            <span class="input-icon">🎭</span>
                            <select id="role" name="role"
                                class="<?= fieldClass($errorFields,'role') ?>">
                                <option value="">Select Role</option>
                                <option value="student" <?= $old['role']==='student'?'selected':'' ?>>Student</option>
                                <option value="teacher" <?= $old['role']==='teacher'?'selected':'' ?>>Teacher</option>
                            </select>
                        </div>
                        <div class="field-hint"><?= $errorFields['role'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="campus">Campus</label>
                        <div class="input-wrap">
                            <span class="input-icon">🏫</span>
                            <input type="text" id="campus" name="campus"
                                placeholder="Main Campus"
                                value="<?= $old['campus'] ?>"
                                class="<?= fieldClass($errorFields,'campus') ?>">
                        </div>
                        <div class="field-hint"><?= $errorFields['campus'] ?? '' ?></div>
                    </div>
                </div>

                <div class="form-group cond-field <?= $old['role']==='student'?'visible':'' ?>" id="courseField">
                    <label for="course">Course</label>
                    <div class="input-wrap">
                        <span class="input-icon">📚</span>
                        <input type="text" id="course" name="course"
                            placeholder="e.g. BSCS, BSIT"
                            value="<?= $old['course'] ?>"
                            class="<?= fieldClass($errorFields,'course') ?>">
                    </div>
                    <div class="field-hint"><?= $errorFields['course'] ?? '' ?></div>
                </div>

                <div class="form-group cond-field <?= $old['role']==='teacher'?'visible':'' ?>" id="deptField">
                    <label for="department">Department</label>
                    <div class="input-wrap">
                        <span class="input-icon">🏛️</span>
                        <input type="text" id="department" name="department"
                            placeholder="e.g. College of Computing"
                            value="<?= $old['department'] ?>"
                            class="<?= fieldClass($errorFields,'department') ?>">
                    </div>
                    <div class="field-hint"><?= $errorFields['department'] ?? '' ?></div>
                </div>

                <button type="submit" name="register" class="btn-submit">
                    Create Account
                    <span class="btn-arrow">→</span>
                </button>

            </form>

            <p class="signin-row">
                Already have an account? <a href="login.php">Sign in →</a>
            </p>

        </div>
    </div>

</div>

<footer>
    <p class="footer-copy">&copy; 2026 <strong>Spacio</strong> &nbsp;·&nbsp; Campus Lab &amp; Classroom Management</p>
</footer>

<script>
    const pwToggle = document.getElementById('pwToggle');
    const pwInput  = document.getElementById('password');

    pwToggle.addEventListener('click', () => {
        const hide = pwInput.type === 'password';
        pwInput.type = hide ? 'text' : 'password';
        pwToggle.textContent = hide ? 'Hide' : 'Show';
    });

    const segs   = ['s1','s2','s3','s4'].map(id => document.getElementById(id));
    const colors = ['#f87171','#fb923c','#facc15','#6ee7b7'];

    pwInput.addEventListener('input', () => {
        const v = pwInput.value;
        let score = 0;
        if (v.length >= 8)           score++;
        if (/[A-Z]/.test(v))         score++;
        if (/[0-9]/.test(v))         score++;
        if (/[^A-Za-z0-9]/.test(v))  score++;
        segs.forEach((s, i) => {
            s.style.background = i < score ? colors[score - 1] : 'rgba(255,255,255,.08)';
        });
    });

    const roleSelect  = document.getElementById('role');
    const courseField = document.getElementById('courseField');
    const deptField   = document.getElementById('deptField');

    roleSelect.addEventListener('change', () => {
        const val = roleSelect.value;
        courseField.classList.toggle('visible', val === 'student');
        deptField.classList.toggle('visible',   val === 'teacher');
    });
</script>

</body>
</html>