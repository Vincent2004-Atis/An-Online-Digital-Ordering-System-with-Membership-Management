<?php
/**
 * Middleware — Authentication & Authorization Guard
 * Amazing World Marketing Corporation Ordering System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(string $redirect = '/amazingworldmarketingcorp/auth/login.php'): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /amazingworldmarketingcorp/auth/login.php');
        exit;
    }
}

function requireCustomer(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'customer') {
        header('Location: /amazingworldmarketingcorp/admin/dashboard.php');
        exit;
    }
}

function requireMember(): void {
    requireCustomer();
    if (empty($_SESSION['member_status']) || $_SESSION['member_status'] !== 'member') {
        header('Location: /amazingworldmarketingcorp/customer/membership.php');
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isMember(): bool {
    return isLoggedIn() && ($_SESSION['member_status'] ?? '') === 'member';
}

function apiUnauthorized(string $message = 'Unauthorized'): void {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// NOTE: e() function is defined in includes/security.php
// Do NOT redeclare it here to avoid "Cannot redeclare" fatal error