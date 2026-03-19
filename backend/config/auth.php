<?php
// backend/config/auth.php
session_start();

// ─────────────────────────────────────────────
// CORE AUTH CHECKS
// ─────────────────────────────────────────────

/**
 * Redirect to login if the user is not authenticated.
 * Also verifies a JWT token exists in the session.
 * Call this at the top of any protected page.
 */
function checkLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: ../../login.php");
        exit;
    }

    // If JWT is missing but user session exists, force re-login
    if (!isset($_SESSION['jwt'])) {
        session_destroy();
        header("Location: ../../login.php?reason=session_expired");
        exit;
    }
}

/**
 * Ensure the logged-in user has one of the allowed roles.
 * Accepts a single role string or an array of allowed roles.
 *
 * Usage:
 *   checkRole('admin');
 *   checkRole(['admin', 'teacher']);
 */
function checkRole($roles) {
    checkLogin();

    $allowed = is_array($roles) ? $roles : [$roles];

    if (!in_array($_SESSION['user']['role'], $allowed, true)) {
        redirectToDashboard();
    }
}

/**
 * Redirect an already-logged-in user away from guest-only pages
 * (e.g. index.php, login.php, register.php).
 * Call this at the top of those pages.
 */
function redirectIfLoggedIn() {
    if (isset($_SESSION['user'])) {
        redirectToDashboard();
    }
}

/**
 * Send the current user to their role-specific dashboard.
 */
function redirectToDashboard() {
    $role = $_SESSION['user']['role'] ?? '';

    $dashboards = [
        'student' => '../../frontend/student/dashboard.php',
        'teacher' => '../../frontend/teacher/dashboard.php',
        'admin'   => '../../frontend/admin/dashboard.php',
    ];

    $destination = $dashboards[$role] ?? '../../login.php';
    header("Location: " . $destination);
    exit;
}

// ─────────────────────────────────────────────
// SESSION HELPERS
// ─────────────────────────────────────────────

/**
 * Return the current logged-in user array, or null if not logged in.
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Return the role of the current user, or null if not logged in.
 */
function getCurrentRole() {
    return $_SESSION['user']['role'] ?? null;
}

// ─────────────────────────────────────────────
// JWT SESSION MANAGEMENT
// ─────────────────────────────────────────────

/**
 * Store user data and JWT tokens in the session after a successful login.
 * Called by login.php after Django returns a successful auth response.
 *
 * @param array  $user         User data from Django (id, name, email, role)
 * @param string $accessToken  Short-lived JWT access token
 * @param string $refreshToken Long-lived JWT refresh token
 */
function storeAuthSession(array $user, string $accessToken, string $refreshToken) {
    session_regenerate_id(true); // Prevent session fixation attacks

    $_SESSION['user']          = $user;
    $_SESSION['jwt']           = $accessToken;
    $_SESSION['jwt_refresh']   = $refreshToken;
    $_SESSION['logged_in_at']  = time();
}

/**
 * Get the JWT access token from the session.
 * Used by helpers.php djangoGet/djangoPost to attach to API calls.
 */
function getSessionJwt() {
    return $_SESSION['jwt'] ?? null;
}

/**
 * Destroy the auth session completely.
 * Called by logout.php after Django invalidates the token.
 */
function destroyAuthSession() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
?>