<?php
// ── includes/session.php ─────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['client_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentClient(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'       => $_SESSION['client_id'],
        'name'     => $_SESSION['client_name'],
        'email'    => $_SESSION['client_email'],
        'service'  => $_SESSION['client_service'],
    ];
}

function logout(): void {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
