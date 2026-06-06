<?php
// PMI Admin Dashboard
requireRole('pmi');
$pageTitle   = 'Dashboard PMI';
$currentPage = 'admin_dashboard';
$db = getDB();

// Stats
$totalUsers     = $db->query("SELECT COUNT(*) FROM users WHERE role='donor'")->fetchColumn();
$totalEvents    = $db->query("SELECT COUNT(*) FROM events WHERE status='active'")->fetchColumn();
$totalDonations = $db->query("SELECT SUM(total_donations) FROM users WHERE role='donor'")->fetchColumn() ?? 0;
$pendingReq     = $db->query("SELECT COUNT(*) FROM requests WHERE status='pending'")->fetchColumn();
$pendingScreen  = $db->query("SELECT COUNT(*) FROM bookings WHERE status='confirmed'")->fetchColumn();

// Stock per blood type
$stocks = $db->query("SELECT blood_type, rhesus, quantity, min_stock, component FROM blood_stock WHERE component='Whole Blood' ORDER BY blood_type, rhesus")->fetchAll();

// Recent requests
$recentReq = $db->query("
    SELECT r.*, u.name as hospital_name FROM requests r
    JOIN users u ON r.hospital_id = u.id
    ORDER BY r.created_at DESC LIMIT 5
")->fetchAll();

// Bookings today
$todayBook = $db->query("
    SELECT b.*, u.name as donor_name, e.title as event_title
    FROM bookings b JOIN users u ON b.user_id=u.id JOIN events e ON b.event_id=e.id
    WHERE DATE(b.booking_time) = CURDATE() AND b.status='confirmed'
    LIMIT 5
")->fetchAll();

include __DIR__ . '/../../views/layouts/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card red">
    <div class="stat-icon">👥</div>
    <div class="stat-value"><?= $totalUsers ?></div>
    <div class="stat-label">Total Donor Terdaftar</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon">🩸</div>
    <div class="stat-value"><?= $totalDonations ?></div>
    <div class="stat-label">Total Kantong Darah</div>
  </div>
  <div class="stat-card orange">
    <div class="stat-icon">🏥</div>
    <div class="stat-value"><?= $pendingReq ?></div>
    <div class="stat-label">Request Menunggu</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon">🧪</div>
    <div class="stat-value"><?= $pendingScreen ?></div>
    <div class="stat-label">Perlu Screening</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">

<!-- Blood Stock Chart -->
<div class="card">
  <div class="card-header">
    <h3>🩸 Stok Darah (Whole Blood)</h3>
    <a href="/ebloodbank/index.php?page=admin_stock" class="btn btn-primary btn-sm">Kelola Stok</a>
  </div>
  <div class="card-body">
    <div class="blood-grid">
      <?php foreach ($stocks as $s):
        $status = getStockStatus($s['quantity'], $s['min_stock']);
        $label  = $s['blood_type'] . $s['rhesus'];
        $class  = str_replace('badge-','',$status['class']);
        $class  = $class === 'success' ? 'safe' : ($class === 'warning' ? 'low' : 'critical');
      ?>
        <div class="blood-card <?= $class ?>">
          <div class="blood-type-label"><?= $label ?></div>
          <div class="blood-qty" data-qty="<?= $s['quantity'] ?>" data-min="<?= $s['min_stock'] ?>"><?= $s['quantity'] ?></div>
          <div class="blood-status"><?= $status['label'] ?></div>
          <div class="blood-component">kantong</div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="card">
  <div class="card-header"><h3>⚡ Aksi Cepat</h3></div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
    <a href="/ebloodbank/index.php?page=admin_event_create" class="btn btn-primary btn-block">📅 Buat Event Donor</a>
    <a href="/ebloodbank/index.php?page=admin_screening" class="btn btn-info btn-block">🧪 Input Screening</a>
    <a href="/ebloodbank/index.php?page=admin_requests" class="btn btn-warning btn-block">🏥 Proses Request RS</a>
    <a href="/ebloodbank/index.php?page=admin_stock_update" class="btn btn-success btn-block">🩸 Update Stok</a>
    <a href="/ebloodbank/index.php?page=reports" class="btn btn-secondary btn-block">📊 Laporan</a>
  </div>
</div>

</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<!-- Recent Requests -->
<div class="card">
  <div class="card-header">
    <h3>🏥 Request Terbaru</h3>
    <a href="/ebloodbank/index.php?page=admin_requests" class="btn btn-secondary btn-sm">Semua</a>
  </div>
  <div class="card-body" style="padding:0">
    <?php if (empty($recentReq)): ?>
      <div class="empty-state"><div class="icon">📭</div><h3>Belum ada request</h3></div>
    <?php else: ?>
      <div class="table-wrap" style="border:none">
        <table id="data-table">
          <thead><tr><th>RS</th><th>Darah</th><th>Qty</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($recentReq as $r): ?>
            <tr>
              <td><?= sanitize($r['hospital_name']) ?></td>
              <td><?= $r['blood_type'].$r['rhesus'] ?> <?= getUrgencyBadge($r['urgency']) ?></td>
              <td><strong><?= $r['quantity'] ?></strong> ktg</td>
              <td><?= getStatusBadge($r['status']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Bookings Today -->
<div class="card">
  <div class="card-header">
    <h3>🎫 Datang Hari Ini</h3>
    <a href="/ebloodbank/index.php?page=admin_screening" class="btn btn-info btn-sm">Proses</a>
  </div>
  <div class="card-body" style="padding:0">
    <?php if (empty($todayBook)): ?>
      <div class="empty-state"><div class="icon">📭</div><h3>Belum ada kehadiran</h3></div>
    <?php else: ?>
      <div class="table-wrap" style="border:none">
        <table>
          <thead><tr><th>Donor</th><th>Event</th><th>QR</th></tr></thead>
          <tbody>
          <?php foreach ($todayBook as $b): ?>
            <tr>
              <td><?= sanitize($b['donor_name']) ?></td>
              <td style="font-size:12px"><?= sanitize($b['event_title']) ?></td>
              <td><code style="font-size:11px"><?= $b['qr_code'] ?></code></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
