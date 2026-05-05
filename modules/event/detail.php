<?php
// Event Detail Page
requireLogin();

$eventId = (int)($_GET['id'] ?? 0);
if (!$eventId) {
    flashMessage('error', 'Event tidak ditemukan.');
    redirect('/ebloodbank/index.php?page=events');
}

$db = getDB();

// Fetch event
$stmt = $db->prepare("
    SELECT e.*, u.name AS organizer
    FROM events e
    JOIN users u ON e.created_by = u.id
    WHERE e.id = ?
    LIMIT 1
");
$stmt->execute([$eventId]);
$ev = $stmt->fetch();

if (!$ev) {
    http_response_code(404);
    include __DIR__ . '/../../views/layouts/404.php';
    exit;
}

$user     = currentUser();
$eligible = isEligibleDonor($user['last_donation']);
$sisa     = $ev['quota'] - $ev['booked'];
$pct      = ($ev['booked'] / max(1, $ev['quota'])) * 100;

// Cek booking existing
$bChk = $db->prepare("SELECT id, status FROM bookings WHERE user_id=? AND event_id=? AND status NOT IN ('cancelled','failed') LIMIT 1");
$bChk->execute([$user['id'], $eventId]);
$existingBooking = $bChk->fetch();

$pageTitle   = sanitize($ev['title']);
$currentPage = 'events';

include __DIR__ . '/../../views/layouts/header.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px;font-size:13px;color:var(--text-muted)">
  <a href="/ebloodbank/index.php?page=events" style="color:var(--primary)">← Kembali ke Daftar Event</a>
</div>

<!-- Event Hero -->
<div class="card" style="overflow:hidden;margin-bottom:20px">
  <div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));padding:32px;color:#fff">
    <div style="font-size:12px;opacity:.7;margin-bottom:8px">
      <?= ucfirst($ev['status']) ?> · Dibuat oleh <?= sanitize($ev['organizer']) ?>
    </div>
    <h2 style="font-size:22px;font-weight:800;margin-bottom:10px;color:#fff"><?= sanitize($ev['title']) ?></h2>
    <div style="display:flex;flex-wrap:wrap;gap:20px;margin-top:12px">
      <div><span style="opacity:.7;font-size:12px">📅 Tanggal</span><br><strong><?= formatDate($ev['date'], 'l, d F Y') ?></strong></div>
      <div><span style="opacity:.7;font-size:12px">⏰ Waktu</span><br><strong><?= substr($ev['start_time'],0,5) ?> – <?= substr($ev['end_time'],0,5) ?> WIB</strong></div>
      <div><span style="opacity:.7;font-size:12px">📍 Lokasi</span><br><strong><?= sanitize($ev['location']) ?></strong></div>
    </div>
  </div>
  <?php if ($ev['description']): ?>
  <div class="card-body" style="border-top:1px solid var(--border)">
    <p style="color:var(--text-secondary);line-height:1.7;margin:0"><?= nl2br(sanitize($ev['description'])) ?></p>
  </div>
  <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

<!-- Quota Card -->
<div class="card">
  <div class="card-header"><h3>📊 Kapasitas Event</h3></div>
  <div class="card-body">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <div>
        <div style="font-size:36px;font-weight:800;color:var(--primary)"><?= $sisa ?></div>
        <div style="color:var(--text-muted);font-size:13px">slot tersisa dari <?= $ev['quota'] ?></div>
      </div>
      <div style="text-align:right">
        <div style="font-size:28px;font-weight:700"><?= $ev['booked'] ?></div>
        <div style="color:var(--text-muted);font-size:13px">sudah terdaftar</div>
      </div>
    </div>
    <div class="bar-track" style="height:12px">
      <div class="bar-fill" data-width="<?= round($pct) ?>" style="width:<?= round($pct) ?>%;height:12px;background:<?= $pct>80?'#dc2626':($pct>50?'#d97706':'#16a34a') ?>"></div>
    </div>
    <div style="margin-top:8px;font-size:12px;color:var(--text-muted);text-align:right"><?= round($pct) ?>% terisi</div>
  </div>
</div>

<!-- Booking Action Card -->
<div class="card">
  <div class="card-header"><h3>🎫 Booking</h3></div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:14px">

    <?php if ($existingBooking): ?>
      <div class="alert alert-success" style="margin:0">
        ✅ Anda sudah booking event ini.<br>
        Status: <?= getStatusBadge($existingBooking['status']) ?>
      </div>
      <a href="/ebloodbank/index.php?page=my_bookings" class="btn btn-secondary btn-block">Lihat Booking Saya</a>

    <?php elseif ($ev['status'] !== 'active'): ?>
      <div class="alert alert-error" style="margin:0">Event ini tidak aktif lagi.</div>

    <?php elseif (strtotime($ev['date']) < strtotime('today')): ?>
      <div class="alert alert-error" style="margin:0">Event ini sudah berlalu.</div>

    <?php elseif ($sisa <= 0): ?>
      <div class="alert alert-error" style="margin:0">🔴 Kuota event penuh.</div>

    <?php elseif (!$eligible && hasRole('donor')): ?>
      <div class="alert" style="margin:0;background:var(--surface2)">
        ⏳ Anda belum eligible donor.<br>
        Harap tunggu <strong><?= daysUntilEligible($user['last_donation']) ?> hari lagi</strong>
        (donor terakhir: <?= formatDate($user['last_donation']) ?>).
      </div>

    <?php elseif (hasRole('donor')): ?>
      <div class="alert alert-success" style="margin:0">✅ Anda eligible untuk donor!</div>
      <a href="/ebloodbank/index.php?page=booking&event_id=<?= $eventId ?>"
         class="btn btn-primary btn-block btn-lg">
        🎫 Booking Sekarang
      </a>

    <?php else: ?>
      <div style="color:var(--text-muted);font-size:13px">Login sebagai donor untuk booking.</div>
    <?php endif; ?>

    <div style="font-size:12px;color:var(--text-muted);border-top:1px solid var(--border);padding-top:12px">
      <div>📅 Dibuka hingga: <strong><?= formatDate($ev['date']) ?></strong></div>
      <div style="margin-top:4px">👤 Organizer: <strong><?= sanitize($ev['organizer']) ?></strong></div>
    </div>
  </div>
</div>

</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
