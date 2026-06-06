<?php
// Report — Export PDF (browser print)
requireRole('pmi');

$db = getDB();

$from = sanitize($_GET['from'] ?? date('Y-m-01'));
$to   = sanitize($_GET['to']   ?? date('Y-m-d'));
$type = sanitize($_GET['type'] ?? 'donations');

// Data queries
$donations = $db->prepare("
    SELECT b.*, u.name AS donor_name, u.blood_type, u.rhesus, u.nik,
           e.title AS event_title, e.date AS event_date, e.location,
           s.hb, s.status AS screening_status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN events e ON b.event_id = e.id
    LEFT JOIN screenings s ON s.booking_id = b.id
    WHERE b.status = 'donated'
      AND e.date BETWEEN ? AND ?
    ORDER BY e.date DESC
");
$donations->execute([$from, $to]);
$donationData = $donations->fetchAll();

$stocks = $db->query("SELECT * FROM blood_stock ORDER BY blood_type, rhesus, component")->fetchAll();

$requests = $db->prepare("
    SELECT r.*, u.name AS hospital_name, u.hospital_name AS hospital_full
    FROM requests r
    JOIN users u ON r.hospital_id = u.id
    WHERE DATE(r.created_at) BETWEEN ? AND ?
    ORDER BY r.created_at DESC
");
$requests->execute([$from, $to]);
$requestData = $requests->fetchAll();

$totalDonors    = count($donationData);
$totalStock     = array_sum(array_column($stocks, 'quantity'));
$totalRequests  = count($requestData);
$emergencyCount = count(array_filter($requestData, fn($r) => $r['urgency'] === 'emergency'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan E-BloodBank — <?= $from ?> s/d <?= $to ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; padding: 30px; }
  .header { text-align: center; border-bottom: 3px solid #dc2626; padding-bottom: 16px; margin-bottom: 24px; }
  .header h1 { font-size: 22px; color: #dc2626; }
  .header p { color: #666; margin-top: 4px; }
  .meta { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11px; color: #555; }
  .section-title { font-size: 14px; font-weight: bold; background: #dc2626; color: #fff; padding: 6px 12px; margin: 20px 0 10px; border-radius: 4px; }
  .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
  .summary-card { border: 1px solid #ddd; border-radius: 6px; padding: 12px; text-align: center; }
  .summary-card .value { font-size: 24px; font-weight: bold; color: #dc2626; }
  .summary-card .label { font-size: 10px; color: #666; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  th { background: #f3f4f6; padding: 6px 8px; text-align: left; border: 1px solid #ddd; font-size: 10px; }
  td { padding: 5px 8px; border: 1px solid #ddd; vertical-align: top; }
  tr:nth-child(even) { background: #f9f9f9; }
  .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
  .badge-success { background: #d1fae5; color: #065f46; }
  .badge-danger  { background: #fee2e2; color: #991b1b; }
  .badge-warning { background: #fef3c7; color: #92400e; }
  .badge-info    { background: #dbeafe; color: #1e40af; }
  .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 12px; font-size: 10px; color: #888; display: flex; justify-content: space-between; }
  @media print {
    body { padding: 15px; }
    .no-print { display: none !important; }
    @page { margin: 1cm; }
  }
</style>
</head>
<body>

<div class="no-print" style="margin-bottom:20px;display:flex;gap:10px">
  <button onclick="window.print()" style="background:#dc2626;color:#fff;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:13px">🖨️ Cetak / Simpan PDF</button>
  <a href="/ebloodbank/index.php?page=reports" style="background:#6b7280;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;font-size:13px">← Kembali</a>
</div>

<div class="header">
  <h1>🩸 E-BloodBank — Laporan Sistem</h1>
  <p>PMI Jakarta · Sistem Manajemen Donor Darah Digital</p>
  <p style="margin-top:6px;font-weight:bold">Periode: <?= formatDate($from) ?> s/d <?= formatDate($to) ?></p>
</div>

<div class="meta">
  <span>Dicetak oleh: <?php $u=currentUser(); echo sanitize($u['name']); ?></span>
  <span>Tanggal cetak: <?= date('d M Y, H:i') ?> WIB</span>
</div>

<!-- Summary -->
<div class="section-title">📊 Ringkasan</div>
<div class="summary-grid">
  <div class="summary-card">
    <div class="value"><?= $totalDonors ?></div>
    <div class="label">Total Donor</div>
  </div>
  <div class="summary-card">
    <div class="value"><?= $totalStock ?></div>
    <div class="label">Total Stok (kantong)</div>
  </div>
  <div class="summary-card">
    <div class="value"><?= $totalRequests ?></div>
    <div class="label">Total Request RS</div>
  </div>
  <div class="summary-card">
    <div class="value" style="color:#f59e0b"><?= $emergencyCount ?></div>
    <div class="label">Request Emergency</div>
  </div>
</div>

<!-- Donor List -->
<div class="section-title">🩸 Data Donor (<?= $totalDonors ?> donor)</div>
<?php if (empty($donationData)): ?>
  <p style="color:#888;font-style:italic;padding:8px">Tidak ada data donor pada periode ini.</p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Nama Donor</th>
      <th>NIK</th>
      <th>Gol. Darah</th>
      <th>Event</th>
      <th>Tanggal</th>
      <th>Lokasi</th>
      <th>HB</th>
      <th>QR Code</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($donationData as $i => $d): ?>
    <tr>
      <td><?= $i+1 ?></td>
      <td><?= sanitize($d['donor_name']) ?></td>
      <td><?= sanitize($d['nik'] ?? '-') ?></td>
      <td><strong><?= $d['blood_type'] . $d['rhesus'] ?></strong></td>
      <td><?= sanitize($d['event_title']) ?></td>
      <td><?= formatDate($d['event_date']) ?></td>
      <td><?= sanitize($d['location']) ?></td>
      <td><?= $d['hb'] ?? '-' ?></td>
      <td style="font-family:monospace;font-size:10px"><?= $d['qr_code'] ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<!-- Blood Stock -->
<div class="section-title">📦 Status Stok Darah (Saat Ini)</div>
<table>
  <thead>
    <tr>
      <th>Gol. Darah</th>
      <th>Rhesus</th>
      <th>Komponen</th>
      <th>Jumlah</th>
      <th>Min. Stok</th>
      <th>Status</th>
      <th>Update Terakhir</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($stocks as $s):
      $status = getStockStatus($s['quantity'], $s['min_stock']);
      $badgeClass = str_replace('badge-','badge-',$status['class']);
    ?>
    <tr>
      <td><strong><?= $s['blood_type'] ?></strong></td>
      <td><?= $s['rhesus'] ?></td>
      <td><?= $s['component'] ?></td>
      <td><strong><?= $s['quantity'] ?></strong></td>
      <td><?= $s['min_stock'] ?></td>
      <td><span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
      <td><?= formatDateTime($s['updated_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Requests -->
<div class="section-title">🏥 Request Darah RS (<?= $totalRequests ?> request)</div>
<?php if (empty($requestData)): ?>
  <p style="color:#888;font-style:italic;padding:8px">Tidak ada request pada periode ini.</p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Rumah Sakit</th>
      <th>Gol. Darah</th>
      <th>Komponen</th>
      <th>Jumlah</th>
      <th>Urgensi</th>
      <th>Status</th>
      <th>Tanggal</th>
      <th>Pasien</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($requestData as $i => $r): ?>
    <tr>
      <td><?= $i+1 ?></td>
      <td><?= sanitize($r['hospital_name']) ?></td>
      <td><strong><?= $r['blood_type'] . $r['rhesus'] ?></strong></td>
      <td><?= $r['component'] ?></td>
      <td><?= $r['quantity'] ?></td>
      <td>
        <?php if ($r['urgency'] === 'emergency'): ?>
          <span class="badge badge-danger">🚨 Emergency</span>
        <?php else: ?>
          <span class="badge badge-info">Normal</span>
        <?php endif; ?>
      </td>
      <td><?= getStatusBadge($r['status']) ?></td>
      <td><?= formatDate($r['created_at']) ?></td>
      <td><?= sanitize($r['patient_name'] ?? '-') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<div class="footer">
  <span>E-BloodBank — Sistem Manajemen Donor Darah Digital</span>
  <span>Dicetak: <?= date('d/m/Y H:i') ?></span>
</div>

</body>
</html>
<?php
logActivity(currentUser()['id'] ?? 0, 'EXPORT_PDF', "Export PDF periode $from s/d $to");
