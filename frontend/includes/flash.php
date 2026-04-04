<?php
/*
 * frontend/includes/flash.php
 * ─────────────────────────────────────────────────────────
 * Flash message system — uses app.css design tokens.
 *
 * Usage:
 *   setFlash("Reservation submitted!", "success");
 *   setFlash("Something went wrong.", "error");
 *   setFlash("Note: Lab closes early today.", "info");
 *   setFlash("Approval pending.", "warning");
 *
 *   // In template: echo getFlash();
 *   // Or use: <?php echo getFlash(); ?>
 */

function setFlash(string $message, string $type = 'success'): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'message' => $message,
        'type'    => $type,
    ];
}

function getFlash(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['flash'])) return '';

    $flash   = $_SESSION['flash'];
    $message = htmlspecialchars($flash['message']);
    $type    = in_array($flash['type'], ['success','error','info','warning'], true)
               ? $flash['type']
               : 'info';

    unset($_SESSION['flash']);

    // Map type to icon
    $icons = [
        'success' => '✓',
        'error'   => '⚠',
        'info'    => 'ℹ',
        'warning' => '⚡',
    ];

    $icon = $icons[$type];

    return <<<HTML
    <div class="flash flash-{$type}" role="alert">
        <span class="flash-icon">{$icon}</span>
        <span class="flash-message">{$message}</span>
        <button class="flash-close" aria-label="Dismiss">&times;</button>
    </div>
    HTML;
}

/*
 * Convenience wrappers
 */
function flashSuccess(string $msg): void { setFlash($msg, 'success'); }
function flashError(string $msg):   void { setFlash($msg, 'error');   }
function flashInfo(string $msg):    void { setFlash($msg, 'info');    }
function flashWarning(string $msg): void { setFlash($msg, 'warning'); }