<?php
requireRole('pmi');
$pageTitle    = 'Input Screening';
$currentPage  = 'admin_screening';
$db   = getDB();
$user = currentUser();
$bid  = (int)($_GET['booking_id']??0);
$markDonated  = isset($_GET['mark_donated']);

$stmt = $db->prepare("SELECT b.*, u.name as donor_name, u.blood_type, u.rhesus, u.id as donor_id, e.title as event_title, e.date as event_date FROM bookings b JOIN users u ON b.user_id=u.id JOIN events e ON b.event_id=e.id WHERE b.id=?");
$stmt->execute([$bid]); $booking=$stmt->fetch();
if(!$booking){flashMessage('error','Booking tidak ditemukan.');redirect('/ebloodbank/index.php?page=admin_screening');}

// Mark as donated
if($markDonated && $booking['status']==='screened'){
    $db->prepare("UPDATE bookings SET status='donated' WHERE id=?")->execute([$bid]);
    $db->prepare("UPDATE users SET total_donations=total_donations+1, last_donation=CURDATE() WHERE id=?")->execute([$booking['donor_id']]);
    // Auto-increment stock
    $bt = $booking['blood_type']; $rh = $booking['rhesus'];
    $db->prepare("UPDATE blood_stock SET quantity=quantity+1 WHERE blood_type=? AND rhesus=? AND component='Whole Blood'")->execute([$bt,$rh]);
    logActivity($user['id'],'DONOR_COMPLETE',"Donor selesai booking#$bid, stok $bt$rh +1");
    flashMessage('success','Donor selesai! Stok darah '.$bt.$rh.' bertambah 1 kantong.');
    redirect('/ebloodbank/index.php?page=admin_screening');
}

include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><div class="breadcrumb"><a href="/ebloodbank/index.php?page=admin_screening">Screening</a> / <span>Input</span></div><h2>🧪 Input Hasil Screening</h2></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<!-- Donor Info -->
<div class="card">
  <div class="card-header"><h3>👤 Data Donor</h3></div>
  <div class="card-body">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
      <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:700;flex-shrink:0"><?=strtoupper(substr($booking['donor_name'],0,1))?></div>
      <div>
        <div style="font-weight:700;font-size:18px"><?=sanitize($booking['donor_name'])?></div>
        <div style="font-size:13px;color:var(--text-muted)">Golongan Darah: <strong><?=$booking['blood_type'].$booking['rhesus']?></strong></div>
      </div>
    </div>
    <div style="display:grid;gap:10px;font-size:14px">
      <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Event</span><strong><?=sanitize($booking['event_title'])?></strong></div>
      <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Tanggal Event</span><strong><?=formatDate($booking['event_date'])?></strong></div>
      <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">QR Code</span><code><?=$booking['qr_code']?></code></div>
      <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Status</span><?=getStatusBadge($booking['status'])?></div>
    </div>
  </div>
</div>
<!-- Screening Form -->
<div class="card">
  <div class="card-header"><h3>🔬 Hasil Screening</h3></div>
  <div class="card-body">
    <?php
    $existingScreen = $db->prepare("SELECT * FROM screenings WHERE booking_id=?");
    $existingScreen->execute([$bid]); $existing=$existingScreen->fetch();
    ?>
    <?php if($existing): ?>
      <div class="alert alert-info">ℹ️ Screening sudah dilakukan</div>
      <div style="display:grid;gap:10px;font-size:14px">
        <div style="display:flex;justify-content:space-between"><span>HB</span><strong><?=$existing['hb']?> g/dL</strong></div>
        <div style="display:flex;justify-content:space-between"><span>Tensi</span><strong><?=$existing['tensi_sistolik']?>/<?=$existing['tensi_diastolik']?> mmHg</strong></div>
        <div style="display:flex;justify-content:space-between"><span>Berat Badan</span><strong><?=$existing['weight']??'-'?> kg</strong></div>
        <div style="display:flex;justify-content:space-between"><span>Suhu</span><strong><?=$existing['temperature']??'-'?> °C</strong></div>
        <div style="display:flex;justify-content:space-between"><span>Nadi</span><strong><?=$existing['pulse']??'-'?> bpm</strong></div>
        <div style="display:flex;justify-content:space-between"><span>Hasil</span><?=getStatusBadge($existing['status'])?></div>
        <?php if($existing['fail_reason']): ?><div class="alert alert-error">❌ <?=sanitize($existing['fail_reason'])?></div><?php endif; ?>
      </div>
    <?php else: ?>
      <form action="/ebloodbank/index.php?page=do_screening" method="POST">
        <input type="hidden" name="booking_id" value="<?=$bid?>">
        <div class="form-row">
          <div class="form-group"><label class="form-label">HB (g/dL) <span class="req">*</span></label><input type="number" name="hb" step="0.1" min="5" max="25" class="form-control" placeholder="12.5" required></div>
          <div class="form-group"><label class="form-label">Berat (kg)</label><input type="number" name="weight" step="0.1" min="30" max="200" class="form-control" placeholder="60"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Sistolik (mmHg) <span class="req">*</span></label><input type="number" name="tensi_sistolik" min="60" max="220" class="form-control" placeholder="120" required></div>
          <div class="form-group"><label class="form-label">Diastolik (mmHg) <span class="req">*</span></label><input type="number" name="tensi_diastolik" min="40" max="140" class="form-control" placeholder="80" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Suhu (°C)</label><input type="number" name="temperature" step="0.1" min="35" max="42" class="form-control" placeholder="36.5"></div>
          <div class="form-group"><label class="form-label">Nadi (bpm)</label><input type="number" name="pulse" min="40" max="180" class="form-control" placeholder="80"></div>
        </div>
        <div class="form-group"><label class="form-label">Catatan / Alasan Gagal</label><input type="text" name="fail_reason" class="form-control" placeholder="Kosongkan jika lolos screening"></div>
        <div class="alert alert-info" style="font-size:13px">
          <strong>Standar Lolos:</strong> HB ≥ 12.5 g/dL · Tensi 90–160/60–100 mmHg · Suhu ≤ 37.5°C
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">✅ Simpan Hasil Screening</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
