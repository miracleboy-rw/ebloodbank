<?php
// Donor — Process Profile Update
requireRole('donor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/ebloodbank/index.php?page=profile');
}

$db   = getDB();
$user = currentUser();

$name    = sanitize($_POST['name']    ?? '');
$phone   = sanitize($_POST['phone']   ?? '');
$address = sanitize($_POST['address'] ?? '');
$nik     = sanitize($_POST['nik']     ?? '');
$bt      = in_array($_POST['blood_type'] ?? '', ['A','B','AB','O']) ? $_POST['blood_type'] : null;
$rh      = in_array($_POST['rhesus']    ?? '', ['+','-'])           ? $_POST['rhesus']    : null;

// Validasi
if (empty($name)) {
    flashMessage('error', 'Nama tidak boleh kosong.');
    redirect('/ebloodbank/index.php?page=profile');
}

// Cek NIK unik jika diubah
if (!empty($nik) && $nik !== $user['nik']) {
    $chk = $db->prepare("SELECT id FROM users WHERE nik = ? AND id != ?");
    $chk->execute([$nik, $user['id']]);
    if ($chk->fetch()) {
        flashMessage('error', 'NIK sudah digunakan akun lain.');
        redirect('/ebloodbank/index.php?page=profile');
    }
}

// Update password jika diisi
$passwordSql = '';
$params      = [];

if (!empty($_POST['new_password'])) {
    $currentPw = $_POST['current_password'] ?? '';
    if (!password_verify($currentPw, $user['password'])) {
        flashMessage('error', 'Password saat ini salah.');
        redirect('/ebloodbank/index.php?page=profile');
    }
    if (strlen($_POST['new_password']) < 6) {
        flashMessage('error', 'Password baru minimal 6 karakter.');
        redirect('/ebloodbank/index.php?page=profile');
    }
    $passwordSql = ', password = ?';
    $params[]    = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
}

$sql = "UPDATE users SET name=?, phone=?, address=?, nik=?, blood_type=?, rhesus=? $passwordSql WHERE id=?";
array_unshift($params, $name, $phone, $address, $nik ?: null, $bt, $rh);
$params[] = $user['id'];

$stmt = $db->prepare($sql);
$stmt->execute($params);

// Update session name
$_SESSION['user_name'] = $name;

logActivity($user['id'], 'PROFILE_UPDATE', 'Donor memperbarui profil');
flashMessage('success', '✅ Profil berhasil diperbarui.');
redirect('/ebloodbank/index.php?page=profile');
