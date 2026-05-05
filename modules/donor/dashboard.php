<?php
// Donor Dashboard
requireRole('donor');
$pageTitle    = 'Dashboard Donor';
$pageSubtitle = 'Selamat datang di E-BloodBank';
$currentPage  = 'dashboard';

$db   = getDB();
$user = currentUser();

// Stats
$totalDonations = $user['total_donations'] ?? 0;
$lastDonation   = $user['last_donation'];
$eligible       = isEligibleDonor($lastDonation);
$daysLeft       = daysUntilEligible($lastDonation);

// Upcoming bookings
$stmt = $db->prepare("
    SELECT b.*, e.title, e.date, e.location, e.start_time
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.user_id = ? AND e.date >= CURDATE() AND b.status NOT IN ('cancelled','failed')
    ORDER BY e.date ASC LIMIT 3
");
$stmt->execute([$user['id']]);
$upcomingBookings = $stmt->fetchAll();

// Upcoming events
$eventsStmt = $db->query("
    SELECT * FROM events WHERE status='active' AND date >= CURDATE()
    ORDER BY date ASC LIMIT 4
");
$events = $eventsStmt->fetchAll();

// Blood stock summary
$stockStmt = $db->query("SELECT blood_type, rhesus, SUM(quantity) as total FROM blood_stock GROUP BY blood_type, rhesus ORDER BY blood_type, rhesus");
$stocks = $stockStmt->fetchAll();

include __DIR__ . '/../../views/layouts/header.php';
?>

<!-- Eligibility Status -->
<div class="eligibility-card <?= $eligible ? 'eligible' : 'not-eligible' ?>">
  <div class="eligibility-icon"><?= $eligible ? '✅' : '⏳' ?></div>
  <h3><?= $eligible ? 'Anda Bisa Donor Sekarang!' : 'Belum Bisa Donor' ?></h3>
  <p>
    <?php if ($eligible): ?>
      Kondisi Anda memenuhi syarat. Segera booking event donor terdekat!
    <?php else: ?>
      Harap tunggu <strong id="eligibility-countdown" data-days="<?= $daysLeft ?>"><?= $daysLeft ?> hari lagi</strong>
      (donor terakhir: <?= formatDate($lastDonation) ?>)
    <?php endif; ?>
  </p>
  <?php if ($eligible): ?>
    <a href="/ebloodbank/index.php?page=events" class="btn btn-primary" style="margin-top:14px">Cari Event Donor →</a>
  <?php endif; ?>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card red">
    <div class="stat-icon">🩸</div>
    <div class="stat-value"><?= $totalDonations ?></div>
    <div class="stat-label">Total Donor</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon">💪</div>
    <div class="stat-value"><?= $totalDonations * 3 ?></div>
    <div class="stat-label">Nyawa Diselamatkan</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon">📅</div>
    <div class="stat-value"><?= count($upcomingBookings) ?></div>
    <div class="stat-label">Booking Aktif</div>
  </div>
  <div class="stat-card orange">
    <div class="stat-icon">🏅</div>
    <div class="stat-value"><?= $totalDonations >= 10 ? 'Gold' : ($totalDonations >= 5 ? 'Silver' : 'Bronze') ?></div>
    <div class="stat-label">Level Donor</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<!-- Upcoming Bookings -->
<div class="card">
  <div class="card-header">
    <h3>🎫 Booking Aktif</h3>
    <a href="/ebloodbank/index.php?page=my_bookings" class="btn btn-secondary btn-sm">Lihat Semua</a>
  </div>
  <div class="card-body">
    <?php if (empty($upcomingBookings)): ?>
      <div class="empty-state"><div class="icon">📭</div><h3>Belum ada booking</h3><p>Cari event donor dan mulai booking!</p></div>
    <?php else: ?>
      <?php foreach ($upcomingBookings as $b): ?>
        <div style="display:flex;gap:14px;padding:12px 0;border-bottom:1px solid var(--border)">
          <div style="width:44px;height:44px;background:var(--primary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">📅</div>
          <div style="flex:1">
            <div style="font-weight:600;font-size:14px"><?= sanitize($b['title']) ?></div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
              <?= formatDate($b['date']) ?> · <?= substr($b['start_time'],0,5) ?> · <?= sanitize($b['location']) ?>
            </div>
            <div style="margin-top:4px"><?= getStatusBadge($b['status']) ?></div>
          </div>
          <div class="text-center">
            <div style="font-family:monospace;font-size:11px;font-weight:700;background:var(--surface2);padding:4px 8px;border-radius:6px"><?= $b['qr_code'] ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Blood Stock Summary -->
<div class="card">
  <div class="card-header">
    <h3>🩸 Ketersediaan Darah</h3>
    <span class="badge badge-info">Real-time</span>
  </div>
  <div class="card-body">
    <div class="blood-grid" style="grid-template-columns:repeat(4,1fr)">
      <?php foreach ($stocks as $s):
        $label = $s['blood_type'] . $s['rhesus'];
        $qty   = (int)$s['total'];
        $class = $qty <= 0 ? 'critical' : ($qty <= 10 ? 'critical' : ($qty <= 20 ? 'low' : 'safe'));
      ?>
        <div class="blood-card <?= $class ?>">
          <div class="blood-type-label"><?= $label ?></div>
          <div class="blood-qty" data-qty="<?= $qty ?>" data-min="10"><?= $qty ?></div>
          <div class="blood-status" style="color:<?= $class==='safe'?'var(--success)':($class==='low'?'var(--warning)':'var(--danger)') ?>">
            <?= $class==='safe'?'Aman':($class==='low'?'Rendah':'Kritis') ?>
          </div>
          <div class="blood-component" style="font-size:10px">kantong</div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

</div>

<!-- Available Events -->
<div class="card" style="margin-top:20px">
  <div class="card-header">
    <h3>📅 Event Donor Tersedia</h3>
    <a href="/ebloodbank/index.php?page=events" class="btn btn-primary btn-sm">Lihat Semua Event</a>
  </div>
  <div class="card-body">
    <?php if (empty($events)): ?>
      <div class="empty-state"><div class="icon">📅</div><h3>Belum ada event</h3></div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px">
        <?php foreach ($events as $ev):
          $sisa = $ev['quota'] - $ev['booked'];
          $pct  = ($ev['booked'] / max(1,$ev['quota'])) * 100;
        ?>
          <div class="card" style="box-shadow:none">
            <div class="card-body">
              <div style="font-weight:700;font-size:15px;margin-bottom:6px"><?= sanitize($ev['title']) ?></div>
              <div style="font-size:12px;color:var(--text-muted)">📍 <?= sanitize($ev['location']) ?></div>
              <div style="font-size:12px;color:var(--text-muted);margin-top:2px">📅 <?= formatDate($ev['date']) ?> · ⏰ <?= substr($ev['start_time'],0,5) ?>–<?= substr($ev['end_time'],0,5) ?></div>
              <div style="margin-top:10px">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                  <span>Kuota Tersisa</span><span><strong><?= $sisa ?></strong>/<?= $ev['quota'] ?></span>
                </div>
                <div class="bar-track"><div class="bar-fill" data-width="<?= round($pct) ?>" style="width:<?= round($pct) ?>%"></div></div>
              </div>
              <?php if ($eligible && $sisa > 0): ?>
                <a href="/ebloodbank/index.php?page=booking&event_id=<?= $ev['id'] ?>" class="btn btn-primary btn-block btn-sm" style="margin-top:12px">🎫 Booking Sekarang</a>
              <?php elseif ($sisa <= 0): ?>
                <div class="btn btn-secondary btn-block btn-sm" style="margin-top:12px;cursor:not-allowed">Kuota Penuh</div>
              <?php else: ?>
                <div class="btn btn-secondary btn-block btn-sm" style="margin-top:12px;cursor:not-allowed">Belum Eligible</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
