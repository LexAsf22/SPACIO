<?php
// frontend/accept_terms.php
// Clears the terms modal session flag and redirects to dashboard.

$base = __DIR__ . '/../backend/config/';
include_once($base . 'auth.php');
include_once($base . 'database.php');
include_once($base . 'helpers.php');

checkLogin();

$user = $_SESSION['user'];
$role = strtolower($user['role']);

// Record acceptance in Django (best effort)
djangoPost('/api/v1/auth/accept-terms/', [
    'user_id' => $user['id'] ?? null,
]);

// Clear flag — modal will never show again
unset($_SESSION['show_terms_modal']);

// Redirect to their role dashboard
$dashboards = [
    'student' => '/spacio/frontend/student/dashboard.php',
    'teacher' => '/spacio/frontend/teacher/dashboard.php',
    'admin'   => '/spacio/frontend/admin/dashboard.php',
];
header('Location: ' . ($dashboards[$role] ?? '/spacio/index.php'));
exit;