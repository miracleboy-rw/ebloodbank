<?php
// ============================================
// Middleware — E-BloodBank
// ============================================

require_once __DIR__ . '/auth.php';

/**
 * Guard: hanya izinkan akses jika user login dan memiliki salah satu role yang diberikan.
 * Contoh: guardRole(['pmi', 'donor'])
 */
function guardRole(array $allowedRoles) {
    if (!isLoggedIn()) {
        flashMessage('error', 'Silakan login terlebih dahulu.');
        redirect('/ebloodbank/index.php?page=login');
    }
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        flashMessage('error', 'Anda tidak memiliki akses ke halaman ini.');
        redirect('/ebloodbank/index.php?page=dashboard');
    }
}

/**
 * Guard: hanya donor
 */
function guardDonor() {
    guardRole(['donor']);
}

/**
 * Guard: hanya PMI admin
 */
function guardPMI() {
    guardRole(['pmi']);
}

/**
 * Guard: hanya RS admin
 */
function guardRS() {
    guardRole(['rs']);
}

/**
 * Guard: PMI atau RS
 */
function guardStaff() {
    guardRole(['pmi', 'rs']);
}

/**
 * Cek apakah request method sesuai, jika tidak redirect
 */
function requireMethod(string $method) {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        http_response_code(405);
        die('Method Not Allowed');
    }
}

/**
 * Validasi CSRF token sederhana menggunakan session
 */
function generateCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiter sederhana berbasis session (per halaman, per menit)
 */
function rateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 60): bool {
    $sessionKey = "rl_{$key}";
    $now = time();
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = ['count' => 0, 'reset_at' => $now + $windowSeconds];
    }
    if ($now > $_SESSION[$sessionKey]['reset_at']) {
        $_SESSION[$sessionKey] = ['count' => 0, 'reset_at' => $now + $windowSeconds];
    }
    $_SESSION[$sessionKey]['count']++;
    return $_SESSION[$sessionKey]['count'] <= $maxAttempts;
}
