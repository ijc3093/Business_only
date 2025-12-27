<?php
// /Business_only/admin/includes/session_admin.php

if (session_status() === PHP_SESSION_NONE) {
    session_name('BUSINESS_ONLY_ADMIN');
    session_start();
}

function sendNoCacheHeaders(): void
{
    // Prevent browser back-button showing cached admin pages
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
}

function requireAdminLogin(): void
{
    sendNoCacheHeaders();

    // ✅ Must exist AFTER admin login
    if (empty($_SESSION['admin_login']) || empty($_SESSION['admin_id'])) {
        header("Location: index.php");
        exit;
    }
}

function setAdminSession(string $login, int $role, int $adminId, string $image = 'default.jpg'): void
{
    // Optional but good security
    session_regenerate_id(true);

    $_SESSION['admin_login'] = $login;       // username or email
    $_SESSION['admin_id']    = (int)$adminId;
    $_SESSION['userRole']    = (int)$role;   // 1 Admin, 2 Manager, 3 Gospel, 4 Staff
    $_SESSION['admin_image'] = $image ?: 'default.jpg';
}

function clearAdminSession(): void
{
    sendNoCacheHeaders();

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
