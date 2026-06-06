<?php
// My Bookings list
requireRole('donor');
$pageTitle   = 'Booking Saya';
$currentPage = 'my_bookings';
$db   = getDB();
$user = currentUser();

$bookings = $db->prepare("
    SELECT b.*, e.title, e.date, e.location, e.start_time, e.end_time,
           s.status as screen_status, s.hb, s.tensi_sistolik, s.tensi_diastolik
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    LEFT JOIN screenings s ON s.booking_id = b.id
    WHERE b.user_id = ?
    ORDER BY e.date DESC
");
$bookings->execute([$user['id']]);
$bookings = $bookings->fetchAll();

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="page-header">
  <div><h2>🎫 Booking Saya</h2><p>Riwayat dan status booking event donor</p></div>
  <a href="/ebloodbank/index.php?page=events" class="btn btn-primary">+ Cari Event</a>
</div>

<?php if (empty($bookings)): ?>
  <div class="empty-state">
    <div class="icon">🎫</div>
    <h3>Belum ada booking</h3>
    <p>Mulai dengan mencari event donor terdekat!</p>
    <a href="/ebloodbank/index.php?page=events" class="btn btn-primary" style="margin-top:16px">Lihat Event</a>
  </div>
<?php else: ?>
  <div style="display:flex;flex-direction:column;gap:14px">
    <?php foreach ($bookings as $b):
      $isPast = strtotime($b['date']) < strtotime('today');
    ?>
      <div class="card">
        <div class="card-body" style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:start">
          <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
              <h3 style="font-size:16px;font-weight:700"><?= sanitize($b['title']) ?></h3>
              <?= getStatusBadge($b['status']) ?>
              <?php if ($b['screen_status']): echo getStatusBadge($b['screen_status']); endif; ?>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;font-size:13px">
              <div><div class="section-title">📅 Tanggal</div><?= formatDate($b['date']) ?></div>
              <div><div class="section-title">⏰ Waktu</div><?= substr($b['start_time'],0,5) ?>–<?= substr($b['end_time'],0,5) ?></div>
              <div><div class="section-title">📍 Lokasi</div><?= sanitize($b['location']) ?></div>
            </div>
            <?php if ($b['hb']): ?>
              <div style="margin-top:10px;display:flex;gap:16px;font-size:13px">
                <div><strong>HB:</strong> <?= $b['hb'] ?> g/dL</div>
                <div><strong>Tensi:</strong> <?= $b['tensi_sistolik'] ?>/<?= $b['tensi_diastolik'] ?> mmHg</div>
              </div>
            <?php endif; ?>
          </div>
          <div class="qr-container" style="min-width:130px">
            <div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:6px">QR TICKET</div>
            <div class="qr-code-text" style="font-size:13px;letter-spacing:.1em"><?= $b['qr_code'] ?></div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:6px">Tunjukkan ke panitia</div>
            <?php if (!$isPast && $b['status'] === 'confirmed'): ?>
              <a href="/ebloodbank/index.php?page=booking_cancel&id=<?= $b['id'] ?>" class="btn btn-danger btn-sm btn-block" style="margin-top:8px" data-confirm="Batalkan booking ini?">Batalkan</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
