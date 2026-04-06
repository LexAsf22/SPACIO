<?php
// frontend/includes/flash.php

function setFlash(string $message, string $type = 'success'): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = [
        'message' => $message,
        'type'    => $type,
    ];
}

function getFlash(): string {
    if (!isset($_SESSION['flash'])) return '';

    $flash   = $_SESSION['flash'];
    $message = htmlspecialchars($flash['message']);
    $type    = $flash['type'];
    unset($_SESSION['flash']);

    $icon = $type === 'success' ? '✓' : '✕';
    $cls  = $type === 'success' ? 'success' : 'error';

    return "
        <div class='flash {$cls}'>
            <span class='flash-icon'>{$icon}</span>
            <span>{$message}</span>
        </div>
    ";
}