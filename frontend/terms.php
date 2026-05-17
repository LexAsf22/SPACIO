<?php
// frontend/terms.php
// Authenticated page — uses header.php + footer.php + main.css
// Accessible from the footer inside the dashboard.
// ?tab=terms (default) → Terms & Conditions
// ?tab=privacy         → Data Privacy Policy

$activeTab = (($_GET['tab'] ?? '') === 'privacy') ? 'privacy' : 'terms';
include_once("includes/header.php");
?>

<!-- ── Page Header ──────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <?php echo $activeTab === 'privacy' ? 'Data Privacy Policy' : 'Terms &amp; Conditions'; ?>
        </h1>
        <p class="page-subtitle">
            Campus Lab &amp; Classroom Management System &mdash; Lorma Colleges &middot; Effective April 2026
        </p>
    </div>
</div>

<!-- ── Tab Bar ──────────────────────────────────────────────── -->
<div class="tc-tabs">
    <a href="?tab=terms"
       class="tc-tab <?php echo $activeTab === 'terms' ? 'active' : ''; ?>">
        📋 Terms &amp; Conditions
    </a>
    <a href="?tab=privacy"
       class="tc-tab <?php echo $activeTab === 'privacy' ? 'active' : ''; ?>">
        🔒 Data Privacy Policy
    </a>
</div>

<!-- ── Notice Banner ────────────────────────────────────────── -->
<?php if ($activeTab === 'terms'): ?>
<div class="status-alert warning" style="margin-bottom:24px;">
    <span>⚖️</span>
    <div>
        <strong style="font-family:var(--font-display);font-size:.78rem;display:block;margin-bottom:2px;">Two versions of each clause</strong>
        Each section shows the formal legal language alongside a plain-language summary — so you know exactly what you're agreeing to.
    </div>
</div>
<?php else: ?>
<div class="status-alert success" style="margin-bottom:24px;">
    <span>🔒</span>
    <div>
        <strong style="font-family:var(--font-display);font-size:.78rem;display:block;margin-bottom:2px;">Data Privacy Policy</strong>
        This policy outlines how SPACIO collects, uses, stores, and protects your personal information in compliance with applicable data protection laws and institutional policies.
    </div>
</div>
<?php endif; ?>

<?php if ($activeTab === 'terms'): ?>

<!-- ══════════════════════════════════════════════════════════
     TERMS & CONDITIONS
══════════════════════════════════════════════════════════ -->

    <?php
    $sections = [
        [
            'num'   => '01',
            'title' => 'Acceptance of Terms',
            'legal' => 'By accessing and using the College Laboratory &amp; Classroom Management System, users acknowledge and agree to be bound by these Terms and Conditions. Continued use of the system constitutes acceptance of any updates or modifications.',
            'plain' => 'By using SPACIO, you agree to follow all the rules stated here.',
        ],
        [
            'num'   => '03',
            'title' => 'Account Security',
            'legal' => 'Users are solely responsible for maintaining the confidentiality of their login credentials. Any activity conducted under a user\'s account shall be deemed authorized by the account holder. Unauthorized sharing of accounts is strictly prohibited.',
            'plain' => 'Keep your username and password private. You are responsible for anything done using your account.',
        ],
        [
            'num'   => '04',
            'title' => 'Reservation and Booking Policy',
            'legal' => 'All reservations are subject to administrative approval. The system reserves the right to approve, reject, or cancel any booking request. Repeated non-attendance, misuse, or fraudulent reservations may result in suspension of booking privileges.',
            'plain' => 'All bookings must be approved. Avoid fake bookings or skipping your schedule, or your access may be limited.',
        ],
        [
            'num'   => '05',
            'title' => 'Use of Laboratory Resources',
            'legal' => 'Users shall exercise proper care in handling all laboratory equipment, computers, and materials. Any damage resulting from negligence or improper use shall be the responsibility of the user and may lead to disciplinary action.',
            'plain' => 'Handle all equipment carefully. If you damage something due to misuse, you may be held responsible.',
        ],
        [
            'num'   => '06',
            'title' => 'Issue Reporting and Maintenance',
            'legal' => 'Users must ensure that all reported issues are accurate and submitted in good faith. False, misleading, or malicious reports are strictly prohibited and may result in sanctions.',
            'plain' => 'Report problems honestly. Do not submit false reports.',
        ],
        [
            'num'   => '07',
            'title' => 'System Availability',
            'legal' => 'The system is provided on an &ldquo;as-is&rdquo; and &ldquo;as-available&rdquo; basis. The administration does not guarantee uninterrupted access and shall not be held liable for any disruptions, delays, or technical issues.',
            'plain' => 'The system may sometimes be unavailable due to maintenance or technical problems.',
        ],
        [
            'num'   => '09',
            'title' => 'Modifications to Terms',
            'legal' => 'The administration reserves the right to amend these Terms and Conditions at any time. Continued use of the system after such changes constitutes acceptance of the revised terms.',
            'plain' => 'The rules may change anytime. Continued use means you accept the changes.',
        ],
        [
            'num'   => '10',
            'title' => 'Termination of Access',
            'legal' => 'The administration reserves the right to suspend or terminate user access without prior notice in cases of violation of these Terms and Conditions.',
            'plain' => 'Your account may be suspended or removed if you break the rules.',
        ],
        [
            'num'   => '11',
            'title' => 'Governing Agreement',
            'legal' => 'These Terms and Conditions constitute a binding agreement between the user and the system administrators.',
            'plain' => 'By continuing to use the system, you confirm that you understand and agree to these Terms.',
        ],
    ];
    ?>

    <!-- Section 02 — User Roles (has lists, handled separately) -->
    <div class="card tc-card">
        <div class="card-header">
            <div>
                <div class="tc-section-num">Section 02</div>
                <div class="card-title">User Roles and Responsibilities</div>
            </div>
        </div>
        <div class="card-body">
            <div class="tc-cols">
                <div class="tc-col tc-col-legal">
                    <div class="tc-col-badge tc-badge-legal">⚖ Legal</div>
                    <p class="tc-prose">Users shall comply with their designated roles within the system:</p>
                    <ul class="tc-list">
                        <li><strong>Students</strong> shall ensure accurate reservation of laboratory resources and adherence to approved schedules.</li>
                        <li><strong>Teachers</strong> shall supervise laboratory usage and report issues as necessary.</li>
                        <li><strong>Administrators (Custodians)</strong> shall manage inventory, approve reservations, and oversee maintenance operations.</li>
                    </ul>
                    <p class="tc-prose" style="margin-top:8px;">Failure to fulfill these responsibilities may result in appropriate administrative action.</p>
                </div>
                <div class="tc-col tc-col-plain">
                    <div class="tc-col-badge tc-badge-plain">💬 Plain</div>
                    <ul class="tc-list">
                        <li><strong>Students</strong> must make correct reservations and follow schedules.</li>
                        <li><strong>Teachers</strong> monitor lab usage and report issues.</li>
                        <li><strong>Admins</strong> manage bookings, inventory, and maintenance.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 08 — Prohibited Activities (has lists, handled separately) -->
    <div class="card tc-card">
        <div class="card-header">
            <div>
                <div class="tc-section-num">Section 08</div>
                <div class="card-title">Prohibited Activities</div>
            </div>
        </div>
        <div class="card-body">
            <div class="tc-cols">
                <div class="tc-col tc-col-legal">
                    <div class="tc-col-badge tc-badge-legal">⚖ Legal</div>
                    <p class="tc-prose">Users shall not:</p>
                    <ul class="tc-list">
                        <li>Attempt unauthorized access to restricted areas of the system</li>
                        <li>Interfere with or disrupt system operations</li>
                        <li>Input false or misleading information</li>
                        <li>Misuse laboratory resources or facilities</li>
                    </ul>
                    <p class="tc-prose" style="margin-top:8px;">Violations may result in suspension, termination, or further administrative action.</p>
                </div>
                <div class="tc-col tc-col-plain">
                    <div class="tc-col-badge tc-badge-plain">💬 Plain</div>
                    <p class="tc-prose">Do not:</p>
                    <ul class="tc-list">
                        <li>Try to hack or access restricted areas</li>
                        <li>Enter false information</li>
                        <li>Misuse lab equipment</li>
                    </ul>
                    <p class="tc-prose" style="margin-top:8px;">Breaking the rules may lead to account suspension.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Remaining sections via loop -->
    <?php foreach ($sections as $s): ?>
    <div class="card tc-card">
        <div class="card-header">
            <div>
                <div class="tc-section-num">Section <?php echo $s['num']; ?></div>
                <div class="card-title"><?php echo $s['title']; ?></div>
            </div>
        </div>
        <div class="card-body">
            <div class="tc-cols">
                <div class="tc-col tc-col-legal">
                    <div class="tc-col-badge tc-badge-legal">⚖ Legal</div>
                    <p class="tc-prose"><?php echo $s['legal']; ?></p>
                </div>
                <div class="tc-col tc-col-plain">
                    <div class="tc-col-badge tc-badge-plain">💬 Plain</div>
                    <p class="tc-prose"><?php echo $s['plain']; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

<?php else: /* ── PRIVACY POLICY ── */ ?>

<!-- ══════════════════════════════════════════════════════════
     DATA PRIVACY POLICY
══════════════════════════════════════════════════════════ -->

    <?php
    $privacySections = [
        [
            'num'   => '01',
            'title' => 'Collection of Data',
            'prose' => 'The system collects personal information, including but not limited to names, user roles (student, teacher, administrator), login credentials, and system activity data for operational purposes.',
            'list'  => [],
        ],
        [
            'num'   => '02',
            'title' => 'Purpose of Data Processing',
            'prose' => 'Collected data shall be used solely for:',
            'list'  => [
                'User authentication and account management',
                'Laboratory reservations and scheduling',
                'Issue reporting and maintenance tracking',
                'System monitoring and performance improvement',
                'Administrative reporting and analytics',
            ],
        ],
        [
            'num'   => '03',
            'title' => 'Data Protection and Security',
            'prose' => 'The system implements appropriate technical and organizational measures to protect personal data against unauthorized access, alteration, disclosure, or destruction.',
            'list'  => [],
        ],
        [
            'num'   => '04',
            'title' => 'Data Sharing and Disclosure',
            'prose' => 'Personal data shall not be shared with third parties without user consent, except when required by law or authorized by the institution.',
            'list'  => [],
        ],
        [
            'num'   => '05',
            'title' => 'Data Retention',
            'prose' => 'User data shall be retained only for as long as necessary to fulfill its intended academic and administrative purposes, after which it will be securely deleted or archived.',
            'list'  => [],
        ],
        [
            'num'   => '06',
            'title' => 'User Rights',
            'prose' => 'Users have the right to:',
            'list'  => [
                'Access their personal data',
                'Request correction of inaccurate information',
                'Request deletion of their data, subject to institutional policies',
            ],
        ],
        [
            'num'   => '07',
            'title' => 'Monitoring and Logs',
            'prose' => 'The system may monitor user activity and maintain logs for security, auditing, and system improvement purposes.',
            'list'  => [],
        ],
        [
            'num'   => '08',
            'title' => 'Policy Updates',
            'prose' => 'This Data Privacy Policy may be updated periodically. Continued use of the system constitutes acceptance of any changes.',
            'list'  => [],
        ],
        [
            'num'   => '09',
            'title' => 'Consent',
            'prose' => 'By using the system, users consent to the collection and processing of their personal data as described in this policy.',
            'list'  => [],
        ],
    ];
    ?>

    <?php foreach ($privacySections as $s): ?>
    <div class="card tc-card">
        <div class="card-header">
            <div>
                <div class="tc-section-num">Section <?php echo $s['num']; ?></div>
                <div class="card-title"><?php echo $s['title']; ?></div>
            </div>
        </div>
        <div class="card-body">
            <p class="tc-prose"><?php echo $s['prose']; ?></p>
            <?php if (!empty($s['list'])): ?>
            <ul class="tc-list" style="margin-top:8px;">
                <?php foreach ($s['list'] as $item): ?>
                    <li><?php echo $item; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

<?php endif; ?>

<!-- ── Bottom Switcher ──────────────────────────────────────── -->
<div class="tc-switch">
    <a href="?tab=<?php echo $activeTab === 'terms' ? 'privacy' : 'terms'; ?>"
       class="btn btn-secondary">
        <?php echo $activeTab === 'terms'
            ? '🔒 &nbsp;View Data Privacy Policy →'
            : '← &nbsp;View Terms &amp; Conditions'; ?>
    </a>
</div>

<!-- ── Page-scoped styles ───────────────────────────────────── -->
<style>
/* ── Tab Bar ── */
.tc-tabs {
    display: flex;
    gap: 0;
    background: white;
    border: 1px solid var(--ink-200);
    border-radius: var(--border-radius);
    padding: 4px;
    margin-bottom: 24px;
    width: fit-content;
    box-shadow: var(--shadow-sm);
}

.tc-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 20px;
    border-radius: calc(var(--border-radius) - 2px);
    font-family: var(--font-display);
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .03em;
    color: var(--ink-400);
    text-decoration: none;
    transition: background .15s var(--ease), color .15s var(--ease);
}

.tc-tab:hover {
    color: var(--ink-700);
    background: var(--ink-50);
}

.tc-tab.active {
    background: var(--green-700);
    color: white;
}

/* ── Section Cards ── */
.tc-card {
    margin-bottom: 10px;
    transition: border-color .2s, box-shadow .2s;
}

.tc-card:hover {
    border-color: var(--green-200);
    box-shadow: var(--shadow);
}

.tc-section-num {
    font-family: var(--font-mono);
    font-size: .65rem;
    font-weight: 500;
    color: var(--ink-300);
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 2px;
}

/* ── Two-column layout ── */
.tc-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

@media (max-width: 680px) {
    .tc-cols { grid-template-columns: 1fr; }
    .tc-tabs { width: 100%; }
    .tc-tab  { flex: 1; justify-content: center; font-size: .72rem; padding: 8px 10px; }
}

.tc-col {
    padding: 14px 16px;
    border-radius: var(--border-radius-sm);
}

.tc-col-legal {
    background: #fff8f7;
    border: 1px solid rgba(220,53,53,.12);
}

.tc-col-plain {
    background: var(--green-50);
    border: 1px solid var(--green-100);
}

.tc-col-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 10px;
}

.tc-badge-legal {
    background: rgba(220,53,53,.08);
    color: #b91c1c;
    border: 1px solid rgba(220,53,53,.15);
}

.tc-badge-plain {
    background: rgba(61,122,65,.08);
    color: var(--green-600);
    border: 1px solid var(--green-100);
}

/* ── Prose & Lists ── */
.tc-prose {
    font-size: .875rem;
    line-height: 1.72;
    color: var(--ink-700);
}

.tc-list {
    padding-left: 18px;
    margin: 6px 0;
}

.tc-list li {
    font-size: .875rem;
    line-height: 1.72;
    color: var(--ink-700);
    margin-bottom: 3px;
}

/* ── Bottom Switcher ── */
.tc-switch {
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid var(--ink-200);
    display: flex;
    justify-content: flex-end;
}
</style>

<?php include_once("includes/footer.php"); ?>