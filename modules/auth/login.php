<?php
if (isLoggedIn()) redirect('/ebloodbank/index.php?page=dashboard');
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — E-BloodBank</title>
<meta name="description" content="Masuk ke akun E-BloodBank Anda">
<link rel="stylesheet" href="/ebloodbank/public/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <div class="logo-icon">🩸</div>
      <h1>E-<span>Blood</span>Bank</h1>
      <p>Selamat datang kembali! Silakan login.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error">❌ <?= sanitize($error) ?></div>
    <?php endif; ?>

    <form action="/ebloodbank/index.php?page=do_login" method="POST" id="login-form">
      <div class="form-group">
        <label class="form-label" for="email">Email <span class="req">*</span></label>
        <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password <span class="req">*</span></label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
        🔑 Masuk ke Akun
      </button>
    </form>

    <div class="auth-footer">
      Belum punya akun? <a href="/ebloodbank/index.php?page=register">Daftar Sekarang</a>
    </div>
    <div class="auth-footer" style="margin-top:8px">
      <a href="/ebloodbank/index.php?page=home" style="color:var(--text-muted)">← Kembali ke Beranda</a>
    </div>

    <hr class="divider">
    <div style="background:var(--surface2);border-radius:8px;padding:12px;font-size:12px;color:var(--text-muted)">
      <strong>Demo Login:</strong><br>
      PMI: admin@pmi.id | RS: admin@rscipto.id | Donor: budi@donor.id<br>
      Password semua: <code>password</code>
    </div>
  </div>
</div>
<script src="/ebloodbank/public/js/main.js"></script>
</body>
</html>
