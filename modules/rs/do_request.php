<?php
// RS — Process blood request submission
requireRole('rs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/ebloodbank/index.php?page=rs_request');
}

$db   = getDB();
$user = currentUser();

$bt  = in_array($_POST['blood_type'] ?? '', ['A','B','AB','O']) ? $_POST['blood_type'] : '';
$rh  = in_array($_POST['rhesus']     ?? '', ['+','-'])          ? $_POST['rhesus']     : '';
$cmp = sanitize($_POST['component']  ?? 'Whole Blood');
$qty = max(1, (int)($_POST['quantity'] ?? 1));
$urg = in_array($_POST['urgency'] ?? '', ['normal','emergency']) ? $_POST['urgency'] : 'normal';
$pat = sanitize($_POST['patient_name']  ?? '');
$age = !empty($_POST['patient_age']) ? (int)$_POST['patient_age'] : null;
$dia = sanitize($_POST['diagnosis'] ?? '');
$not = sanitize($_POST['notes']     ?? '');

// Validasi wajib
if (empty($bt) || empty($rh)) {
    flashMessage('error', 'Golongan darah dan rhesus wajib diisi.');
    redirect('/ebloodbank/index.php?page=rs_request');
}

if ($qty < 1 || $qty > 100) {
    flashMessage('error', 'Jumlah kantong harus antara 1–100.');
    redirect('/ebloodbank/index.php?page=rs_request');
}

// Insert request
$ins = $db->prepare("
    INSERT INTO requests
        (hospital_id, blood_type, rhesus, component, quantity, urgency,
         patient_name, patient_age, diagnosis, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$ins->execute([
    $user['id'], $bt, $rh, $cmp, $qty, $urg,
    $pat ?: null, $age, $dia ?: null, $not ?: null,
]);

$reqId = $db->lastInsertId();

logActivity($user['id'], 'REQUEST_CREATE',
    "Request #{$reqId}: {$bt}{$rh} {$qty} ktg ({$urg})");

if ($urg === 'emergency') {
    flashMessage('success', '🚨 Request emergency berhasil dikirim! PMI akan segera merespons.');
} else {
    flashMessage('success', '✅ Request darah berhasil dikirim ke PMI. Harap tunggu konfirmasi.');
}

redirect('/ebloodbank/index.php?page=rs_my_requests');
