<?php
// ============================================
// Entry Point — E-BloodBank
// ============================================

define('BASE_PATH', __DIR__);
define('BASE_URL', '/ebloodbank');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/helper.php';
require_once __DIR__ . '/routes.php';
