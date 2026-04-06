<?php
// backend/config/helpers.php

// ─────────────────────────────────────────────
// DATE HELPERS
// ─────────────────────────────────────────────

function formatDate($date) {
    return date("F d, Y", strtotime($date));
}

// ─────────────────────────────────────────────
// FLASH MESSAGES
// ─────────────────────────────────────────────

function setFlash($message, $type = "success") {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $safe = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
        return "<div class='flash {$flash['type']}'>{$safe}</div>";
    }
    return "";
}

// ─────────────────────────────────────────────
// USER HELPERS
// ─────────────────────────────────────────────

function getUser($conn, $id) {
    $id  = (int) $id;
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// ─────────────────────────────────────────────
// REDIRECT HELPER
// ─────────────────────────────────────────────

function redirect($url) {
    header("Location: " . $url);
    exit;
}

// ─────────────────────────────────────────────
// DJANGO API BRIDGE
// ─────────────────────────────────────────────

/**
 * Get the Django API base URL from .env
 */
function getDjangoBaseUrl() {
    return rtrim($_ENV['DJANGO_API_URL'] ?? 'http://localhost:8000', '/');
}

/**
 * Get the shared API secret key from .env
 */
function getApiKey() {
    return $_ENV['API_SECRET_KEY'] ?? '';
}

/**
 * Get the stored JWT token from the current session
 */
function getJwtToken() {
    return $_SESSION['jwt'] ?? null;
}

/**
 * Build the headers array for every Django API request.
 * Attaches the API key and JWT token (if available).
 */
function buildDjangoHeaders() {
    $headers = [
        'Content-Type: application/json',
        'X-API-Key: ' . getApiKey(),
    ];

    $jwt = getJwtToken();
    if ($jwt) {
        $headers[] = 'Authorization: Bearer ' . $jwt;
    }

    return $headers;
}

/**
 * Refresh the JWT access token using the stored refresh token.
 */
function refreshJwtToken() {
    $refresh = $_SESSION['jwt_refresh'] ?? null;
    if (!$refresh) return false;

    $url  = getDjangoBaseUrl() . '/api/v1/auth/refresh/';
    $body = json_encode(['refresh' => $refresh]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response   = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode === 200) {
        $data = json_decode($response, true);
        $_SESSION['jwt']         = $data['access'];
        $_SESSION['jwt_refresh'] = $data['refresh'] ?? $refresh;
        return true;
    }

    return false;
}

/**
 * Send a GET request to the Django API.
 *
 * @param  string $endpoint  e.g. '/api/v1/availability/labs/'
 * @return array  ['success' => bool, 'data' => mixed, 'status' => int]
 */
function djangoGet($endpoint) {
    $url = getDjangoBaseUrl() . $endpoint;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => buildDjangoHeaders(),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response   = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Django GET error [{$endpoint}]: {$curlError}");
        return ['success' => false, 'data' => null, 'status' => 0, 'error' => $curlError];
    }

    if ($statusCode === 401 && refreshJwtToken()) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => buildDjangoHeaders(),
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    $decoded = json_decode($response, true);
    return [
        'success' => $statusCode >= 200 && $statusCode < 300,
        'data'    => $decoded,
        'status'  => $statusCode,
    ];
}

/**
 * Send a POST request to the Django API.
 *
 * @param  string $endpoint  e.g. '/api/v1/reservations/'
 * @param  array  $data      Associative array — will be JSON-encoded
 * @return array  ['success' => bool, 'data' => mixed, 'status' => int]
 */
function djangoPost($endpoint, array $data = []) {
    $url  = getDjangoBaseUrl() . $endpoint;
    $body = json_encode($data);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => buildDjangoHeaders(),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response   = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Django POST error [{$endpoint}]: {$curlError}");
        return ['success' => false, 'data' => null, 'status' => 0, 'error' => $curlError];
    }

    $decoded = json_decode($response, true);
    return [
        'success' => $statusCode >= 200 && $statusCode < 300,
        'data'    => $decoded,
        'status'  => $statusCode,
    ];
}

/**
 * Send a PUT request to the Django API (for updates).
 *
 * @param  string $endpoint  e.g. '/api/v1/reservations/5/'
 * @param  array  $data      Associative array — will be JSON-encoded
 * @return array  ['success' => bool, 'data' => mixed, 'status' => int]
 */
function djangoPut($endpoint, array $data = []) {
    $url  = getDjangoBaseUrl() . $endpoint;
    $body = json_encode($data);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => buildDjangoHeaders(),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response   = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Django PUT error [{$endpoint}]: {$curlError}");
        return ['success' => false, 'data' => null, 'status' => 0, 'error' => $curlError];
    }

    $decoded = json_decode($response, true);
    return [
        'success' => $statusCode >= 200 && $statusCode < 300,
        'data'    => $decoded,
        'status'  => $statusCode,
    ];
}
?>