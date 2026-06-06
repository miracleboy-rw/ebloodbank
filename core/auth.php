<?php
// ============================================
// Auth Core — E-BloodBank
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helper.php';

function login($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email']= $user['email'];
        $_SESSION['blood_type']= $user['blood_type'];
        logActivity($user['id'], 'LOGIN', 'User login berhasil');
        return ['success' => true, 'role' => $user['role']];
    }
    return ['success' => false, 'message' => 'Email atau password salah'];
}

function logout() {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'LOGOUT', 'User logout');
    }
    session_destroy();
    header('Location: /ebloodbank/index.php?page=login');
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /ebloodbank/index.php?page=login');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /ebloodbank/index.php?page=dashboard');
        exit;
    }
}

function register($data) {
    $db = getDB();

    // Validasi email unik
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email sudah terdaftar'];
    }

    // Validasi NIK unik (jika diisi)
    if (!empty($data['nik'])) {
        $stmt = $db->prepare("SELECT id FROM users WHERE nik = ?");
        $stmt->execute([$data['nik']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'NIK sudah terdaftar'];
        }
    }

    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO users (nik, name, email, password, role, blood_type, rhesus, phone, hospital_name)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['nik']           ?? null,
        $data['name'],
        $data['email'],
        $hashed,
        $data['role']          ?? 'donor',
        $data['blood_type']    ?? null,
        $data['rhesus']        ?? null,
        $data['phone']         ?? null,
        $data['hospital_name'] ?? null,
    ]);
    $newId = $db->lastInsertId();
    logActivity($newId, 'REGISTER', 'Akun baru terdaftar: ' . $data['email']);
    return ['success' => true];
}
