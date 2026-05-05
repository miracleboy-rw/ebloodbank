<?php
// Header / Sidebar layout
$user = currentUser();
$currentPage = $_GET['page'] ?? 'dashboard';

function navItem($icon, $label, $page, $currentPage) {
    $active = $currentPage === $page ? 'active' : '';
    return "<a class='nav-item $active' href='/ebloodbank/index.php?page=$page'>
        <span class='nav-icon'>$icon</span> $label
    </a>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'E-BloodBank' ?> — E-BloodBank</title>
<meta name="description" content="Sistem Manajemen Donor Darah Digital — E-BloodBank">
<link rel="stylesheet" href="/ebloodbank/public/css/style.css">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🩸</text></svg>">
</head>
<body>
<div class="app-layout">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <h2>🩸 E-Blood<span>Bank</span></h2>
    <p>Sistem Donor Darah Digital</p>
  </div>

  <nav class="sidebar-nav">
  <?php if (hasRole('donor')): ?>
    <div class="nav-section">Donor</div>
    <?= navItem('🏠', 'Dashboard', 'dashboard', $currentPage) ?>
    <?= navItem('📅', 'Event Donor', 'events', $currentPage) ?>
    <?= navItem('🎫', 'Booking Saya', 'my_bookings', $currentPage) ?>
    <?= navItem('📋', 'Riwayat Donor', 'history', $currentPage) ?>
    <?= navItem('👤', 'Profil', 'profile', $currentPage) ?>

  <?php elseif (hasRole('pmi')): ?>
    <div class="nav-section">PMI Admin</div>
    <?= navItem('🏠', 'Dashboard', 'admin_dashboard', $currentPage) ?>
    <?= navItem('📅', 'Event Donor', 'admin_events', $currentPage) ?>
    <?= navItem('🩸', 'Stok Darah', 'admin_stock', $currentPage) ?>
    <?= navItem('🧪', 'Screening', 'admin_screening', $currentPage) ?>
    <div class="nav-section">Permintaan</div>
    <?= navItem('🏥', 'Request RS', 'admin_requests', $currentPage) ?>
    <?= navItem('👥', 'Data User', 'admin_users', $currentPage) ?>
    <?= navItem('📊', 'Laporan', 'reports', $currentPage) ?>

  <?php elseif (hasRole('rs')): ?>
    <div class="nav-section">Rumah Sakit</div>
    <?= navItem('🏠', 'Dashboard', 'rs_dashboard', $currentPage) ?>
    <?= navItem('🩸', 'Stok Darah', 'rs_stock', $currentPage) ?>
    <?= navItem('📋', 'Request Darah', 'rs_request', $currentPage) ?>
    <?= navItem('📜', 'Riwayat Request', 'rs_my_requests', $currentPage) ?>
  <?php endif; ?>

    <div class="nav-section" style="margin-top:auto">Akun</div>
    <?= navItem('👤', 'Profil', 'profile', $currentPage) ?>
    <a class="nav-item" href="/ebloodbank/index.php?page=logout" data-confirm="Yakin mau logout?">
      <span class="nav-icon">🚪</span> Logout
    </a>
  </nav>
</aside>

<!-- Main -->
<div class="main-content">
  <!-- Topbar -->
  <header class="topbar">
    <div class="d-flex align-center gap-3">
      <button id="sidebar-toggle" class="btn btn-secondary btn-sm btn-icon" style="display:none">☰</button>
      <div class="topbar-left">
        <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
        <?php if (isset($pageSubtitle)): ?>
          <p><?= $pageSubtitle ?></p>
        <?php endif; ?>
      </div>
    </div>
    <div class="topbar-right">
      <div class="topbar-user">
        <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
        <div class="user-info">
          <strong><?= sanitize($user['name'] ?? '') ?></strong>
          <span><?= ucfirst($user['role'] ?? '') ?></span>
        </div>
      </div>
    </div>
  </header>

  <!-- Flash Message -->
  <?php if ($flash = getFlash()): ?>
    <div style="padding:0 28px;margin-top:16px">
      <?= renderFlash() ?>
    </div>
  <?php endif; ?>

  <!-- Page Body -->
  <main class="page-body">
