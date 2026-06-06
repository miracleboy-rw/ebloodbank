<?php
// do_login.php — Process login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/ebloodbank/index.php?page=login');
}

$email    = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Email dan password wajib diisi.';
    redirect('/ebloodbank/index.php?page=login');
}

$result = login($email, $password);

if ($result['success']) {
    flashMessage('success', 'Selamat datang kembali!');
    switch ($result['role']) {
        case 'pmi':  redirect('/ebloodbank/index.php?page=admin_dashboard'); break;
        case 'rs':   redirect('/ebloodbank/index.php?page=rs_dashboard'); break;
        default:     redirect('/ebloodbank/index.php?page=dashboard'); break;
    }
} else {
    $_SESSION['login_error'] = $result['message'];
    redirect('/ebloodbank/index.php?page=login');
}
