<?php
// Defines BASE_URL, starts session, and provides auth helpers

require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require that a user be logged in.
 * Redirects to login.php if not.
 */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'public/login.php');
        exit;
    }
}

/**
 * Require that the current user’s role be in the given array.
 * Redirects to login if not logged in; shows “Access denied” otherwise.
 *
 * @param string[] $roles
 */
function require_role(array $roles): void {
    require_login();
    if (! in_array($_SESSION['role'] ?? '', $roles, true)) {
        echo '<p>Access denied.</p>';
        require_once __DIR__ . '/footer.php';
        exit;
    }
}
