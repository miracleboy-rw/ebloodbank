<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/ebloodbank/index.php?page=register');
}

$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

if ($password !== $confirm) {
    $_SESSION['reg_error'] = 'Password dan konfirmasi tidak cocok.';
    redirect('/ebloodbank/index.php?page=register');
}
if (strlen($password) < 6) {
    $_SESSION['reg_error'] = 'Password minimal 6 karakter.';
    redirect('/ebloodbank/index.php?page=register');
}

$role = $_POST['role'] ?? 'donor';
if (!in_array($role, ['donor', 'rs'])) {
    $_SESSION['reg_error'] = 'Role tidak valid.';
    redirect('/ebloodbank/index.php?page=register');
}

$result = register([
    'nik'           => sanitize($_POST['nik'] ?? ''),
    'name'          => sanitize($_POST['name'] ?? ''),
    'email'         => sanitize($_POST['email'] ?? ''),
    'password'      => $password,
    'role'          => $role,
    'blood_type'    => $_POST['blood_type'] ?? null,
    'rhesus'        => $_POST['rhesus'] ?? null,
    'phone'         => sanitize($_POST['phone'] ?? ''),
    'hospital_name' => sanitize($_POST['hospital_name'] ?? ''),
]);

if ($result['success']) {
    flashMessage('success', 'Akun berhasil dibuat! Silakan login.');
    redirect('/ebloodbank/index.php?page=login');
} else {
    $_SESSION['reg_error'] = $result['message'];
    redirect('/ebloodbank/index.php?page=register');
}
