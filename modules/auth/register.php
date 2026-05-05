<?php
if (isLoggedIn()) redirect('/ebloodbank/index.php?page=dashboard');
$error = $_SESSION['reg_error'] ?? null;
unset($_SESSION['reg_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Daftar — E-BloodBank</title>
<meta name="description" content="Daftar akun baru di E-BloodBank">
<link rel="stylesheet" href="/ebloodbank/public/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-box" style="max-width:520px">
    <div class="auth-logo">
      <div class="logo-icon">🩸</div>
      <h1>Daftar <span>Akun</span></h1>
      <p>Bergabung dengan komunitas donor darah Indonesia</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error">❌ <?= sanitize($error) ?></div>
    <?php endif; ?>

    <!-- Role Tabs -->
    <div class="auth-tabs">
      <div class="auth-tab active" data-role="donor">🩸 Donor</div>
      <div class="auth-tab" data-role="rs">🏥 Rumah Sakit</div>
    </div>

    <form action="/ebloodbank/index.php?page=do_register" method="POST">
      <input type="hidden" name="role" id="role-input" value="donor">

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="Nama lengkap" required>
        </div>
        <div class="form-group">
          <label class="form-label">No. Telepon</label>
          <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email <span class="req">*</span></label>
        <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password <span class="req">*</span></label>
          <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required minlength="6">
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password <span class="req">*</span></label>
          <input type="password" name="password_confirm" class="form-control" placeholder="Ulangi password" required>
        </div>
      </div>

      <!-- Donor fields -->
      <div id="donor-fields">
        <div class="form-group">
          <label class="form-label">NIK (KTP)</label>
          <input type="text" name="nik" class="form-control" placeholder="16 digit NIK" maxlength="16">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Golongan Darah</label>
            <select name="blood_type" class="form-control">
              <option value="">-- Pilih --</option>
              <option>A</option><option>B</option><option>AB</option><option>O</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Rhesus</label>
            <select name="rhesus" class="form-control">
              <option value="">-- Pilih --</option>
              <option value="+">Positif (+)</option>
              <option value="-">Negatif (-)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Hospital field (hidden by default) -->
      <div id="hospital-field" style="display:none">
        <div class="form-group">
          <label class="form-label">Nama Rumah Sakit <span class="req">*</span></label>
          <input type="text" name="hospital_name" class="form-control" placeholder="Nama RS resmi">
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
        ✅ Buat Akun
      </button>
    </form>

    <div class="auth-footer">
      Sudah punya akun? <a href="/ebloodbank/index.php?page=login">Login Sekarang</a>
    </div>
  </div>
</div>
<script src="/ebloodbank/public/js/main.js"></script>
</body>
</html>
