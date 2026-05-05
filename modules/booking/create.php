<?php
// Create Booking
requireRole('donor');
$pageTitle   = 'Booking Donor';
$currentPage = 'events';
$db   = getDB();
$user = currentUser();

$eventId = (int)($_GET['event_id'] ?? 0);
if (!$eventId) redirect('/ebloodbank/index.php?page=events');

$stmt = $db->prepare("SELECT * FROM events WHERE id=? AND status='active' AND date >= CURDATE()");
$stmt->execute([$eventId]);
$event = $stmt->fetch();
if (!$event) { flashMessage('error','Event tidak ditemukan atau sudah berakhir.'); redirect('/ebloodbank/index.php?page=events'); }

$sisa = $event['quota'] - $event['booked'];
if ($sisa <= 0) { flashMessage('error','Kuota event ini sudah penuh.'); redirect('/ebloodbank/index.php?page=events'); }

if (!isEligibleDonor($user['last_donation'])) {
    flashMessage('error','Anda belum memenuhi syarat donor. Tunggu '.daysUntilEligible($user['last_donation']).' hari lagi.');
    redirect('/ebloodbank/index.php?page=events');
}

// Check duplicate booking
$dup = $db->prepare("SELECT id FROM bookings WHERE user_id=? AND event_id=? AND status NOT IN ('cancelled','failed')");
$dup->execute([$user['id'], $eventId]);
if ($dup->fetch()) { flashMessage('error','Anda sudah booking event ini.'); redirect('/ebloodbank/index.php?page=events'); }

// POST — Save booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qr = generateQRCode($user['id'] . '-' . $eventId . '-' . time());
    $ins = $db->prepare("INSERT INTO bookings (user_id, event_id, qr_code, status) VALUES (?,?,?,'confirmed')");
    $ins->execute([$user['id'], $eventId, $qr]);
    $bookingId = $db->lastInsertId();
    $db->prepare("UPDATE events SET booked = booked + 1 WHERE id=?")->execute([$eventId]);
    logActivity($user['id'], 'BOOKING', "Booking event #$eventId, QR: $qr");
    flashMessage('success','Booking berhasil! QR Code: '.$qr);
    redirect('/ebloodbank/index.php?page=my_bookings');
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="page-header">
  <div>
    <div class="breadcrumb"><a href="/ebloodbank/index.php?page=events">Event</a> / <span>Booking</span></div>
    <h2>🎫 Konfirmasi Booking</h2>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

<div class="card">
  <div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));padding:24px;border-radius:12px 12px 0 0;color:#fff">
    <h3 style="font-size:20px;font-weight:700"><?= sanitize($event['title']) ?></h3>
    <p style="opacity:.8;margin-top:4px"><?= sanitize($event['description'] ?? '') ?></p>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
      <div><div class="section-title">📍 Lokasi</div><div style="font-weight:600"><?= sanitize($event['location']) ?></div></div>
      <div><div class="section-title">📅 Tanggal</div><div style="font-weight:600"><?= formatDate($event['date'], 'l, d F Y') ?></div></div>
      <div><div class="section-title">⏰ Waktu</div><div style="font-weight:600"><?= substr($event['start_time'],0,5) ?> – <?= substr($event['end_time'],0,5) ?> WIB</div></div>
      <div><div class="section-title">👥 Sisa Kuota</div><div style="font-weight:600"><?= $sisa ?> dari <?= $event['quota'] ?> tempat</div></div>
    </div>

    <hr class="divider">
    <h4 style="margin-bottom:12px">Data Anda</h4>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      <div><div class="section-title">👤 Nama</div><div style="font-weight:600"><?= sanitize($user['name']) ?></div></div>
      <div><div class="section-title">🩸 Gol. Darah</div><div style="font-weight:600"><?= $user['blood_type'].$user['rhesus'] ?></div></div>
      <div><div class="section-title">📱 Telepon</div><div style="font-weight:600"><?= sanitize($user['phone'] ?? '-') ?></div></div>
      <div><div class="section-title">🏅 Total Donor</div><div style="font-weight:600"><?= $user['total_donations'] ?> kali</div></div>
    </div>

    <div class="alert alert-info" style="margin-top:20px">
      ℹ️ Setelah booking dikonfirmasi, Anda akan mendapatkan <strong>QR Code</strong> sebagai tiket masuk. Harap hadir 15 menit sebelum waktu mulai.
    </div>

    <form method="POST">
      <div style="display:flex;gap:12px;margin-top:4px">
        <button type="submit" class="btn btn-primary btn-lg">✅ Konfirmasi Booking</button>
        <a href="/ebloodbank/index.php?page=events" class="btn btn-secondary btn-lg">Batal</a>
      </div>
    </form>
  </div>
</div>

<div style="display:flex;flex-direction:column;gap:16px">
  <div class="card">
    <div class="card-header"><h3>ℹ️ Syarat Donor</h3></div>
    <div class="card-body">
      <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;font-size:13px">
        <li>✅ Usia 17–60 tahun</li>
        <li>✅ Berat ≥ 45 kg</li>
        <li>✅ HB: 12,5–17 g/dL</li>
        <li>✅ Tekanan darah normal</li>
        <li>✅ Tidak sedang sakit</li>
        <li>✅ Jarak donor ≥ 60 hari</li>
      </ul>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h3>📊 Status Kelayakan</h3></div>
    <div class="card-body" style="text-align:center">
      <div style="font-size:40px;margin-bottom:8px">✅</div>
      <div style="font-weight:700;color:var(--success);font-size:16px">LAYAK DONOR</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Donor terakhir: <?= formatDate($user['last_donation']) ?></div>
    </div>
  </div>
</div>

</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
