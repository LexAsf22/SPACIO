<?php
// logout.php (root)
require_once "backend/config/helpers.php";
require_once "backend/config/auth.php";

// ── Notify Django to blacklist the JWT token ──
// This only runs if a JWT exists in the session
$jwt = getSessionJwt();

if ($jwt) {
    djangoPost('/api/v1/auth/logout/', [
        'refresh' => $_SESSION['jwt_refresh'] ?? '',
    ]);
    // Note: we proceed with local logout even if the Django call fails.
    // The JWT will naturally expire on its own after the access token TTL.
}

// ── Destroy the PHP session completely ──
destroyAuthSession();

// ── Redirect to login ──
header("Location: /spacio/login.php");
exit;