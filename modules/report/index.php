<?php
requireRole('pmi');
$pageTitle   = 'Laporan & Statistik';
$currentPage = 'reports';
$db = getDB();

// Stats
$totalDonors   = $db->query("SELECT COUNT(*) FROM users WHERE role='donor'")->fetchColumn();
$totalDonations= $db->query("SELECT SUM(total_donations) FROM users WHERE role='donor'")->fetchColumn()??0;
$totalEvents   = $db->query("SELECT COUNT(*) FROM events")->fetchColumn();
$totalBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE status='donated'")->fetchColumn();
$totalRequests = $db->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$totalStock    = $db->query("SELECT SUM(quantity) FROM blood_stock WHERE component='Whole Blood'")->fetchColumn()??0;

// Monthly donations (last 6 months)
$monthly = $db->query("
    SELECT DATE_FORMAT(e.date,'%b %Y') as month, COUNT(*) as cnt
    FROM bookings b JOIN events e ON b.event_id=e.id
    WHERE b.status='donated' AND e.date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month ORDER BY e.date ASC
")->fetchAll();

// Stock by blood type
$stocks = $db->query("SELECT blood_type, rhesus, quantity, min_stock FROM blood_stock WHERE component='Whole Blood' ORDER BY blood_type, rhesus")->fetchAll();

// Top donors
$topDonors = $db->query("SELECT name, blood_type, rhesus, total_donations FROM users WHERE role='donor' AND total_donations>0 ORDER BY total_donations DESC LIMIT 5")->fetchAll();

// Recent requests summary
$reqSummary = $db->query("SELECT status, COUNT(*) as cnt FROM requests GROUP BY status")->fetchAll();
$reqByStatus = [];
foreach($reqSummary as $r) $reqByStatus[$r['status']]=$r['cnt'];

include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>📊 Laporan & Statistik</h2><p>Ringkasan data E-BloodBank</p></div>
  <a href="/ebloodbank/index.php?page=export_csv" class="btn btn-success">📥 Export CSV</a>
  <a href="/ebloodbank/index.php?page=export_pdf&from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-danger" target="_blank">🖨️ Export PDF</a>
</div>


<!-- Main Stats -->
<div class="stats-grid">
  <div class="stat-card red"><div class="stat-icon">👥</div><div class="stat-value"><?=$totalDonors?></div><div class="stat-label">Total Donor</div></div>
  <div class="stat-card green"><div class="stat-icon">🩸</div><div class="stat-value"><?=$totalDonations?></div><div class="stat-label">Total Donasi</div></div>
  <div class="stat-card blue"><div class="stat-icon">📅</div><div class="stat-value"><?=$totalEvents?></div><div class="stat-label">Total Event</div></div>
  <div class="stat-card orange"><div class="stat-icon">🏥</div><div class="stat-value"><?=$totalRequests?></div><div class="stat-label">Total Request RS</div></div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">

<!-- Monthly Chart -->
<div class="card">
  <div class="card-header"><h3>📈 Donasi 6 Bulan Terakhir</h3></div>
  <div class="card-body">
    <?php if(empty($monthly)): ?>
      <div class="empty-state"><div class="icon">📊</div><h3>Belum ada data</h3></div>
    <?php else:
      $maxVal = max(array_column($monthly,'cnt')); ?>
      <div class="bar-chart">
        <?php foreach($monthly as $m):
          $pct = round($m['cnt']/max(1,$maxVal)*100);
        ?>
          <div class="bar-item">
            <div class="bar-label" style="width:90px;font-size:12px"><?=$m['month']?></div>
            <div class="bar-track"><div class="bar-fill" data-width="<?=$pct?>" style="width:<?=$pct?>%"></div></div>
            <div class="bar-val"><?=$m['cnt']?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Request Summary -->
<div class="card">
  <div class="card-header"><h3>🏥 Ringkasan Request</h3></div>
  <div class="card-body">
    <?php foreach(['pending'=>'⏳ Pending','approved'=>'✅ Disetujui','rejected'=>'❌ Ditolak','fulfilled'=>'🩸 Terpenuhi'] as $s=>$lbl): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)">
        <span style="font-size:14px"><?=$lbl?></span>
        <strong style="font-size:18px"><?=$reqByStatus[$s]??0?></strong>
      </div>
    <?php endforeach; ?>
  </div>
</div>

</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<!-- Top Donors -->
<div class="card">
  <div class="card-header"><h3>🏅 Top 5 Donor Terbanyak</h3></div>
  <div class="card-body" style="padding:0">
    <table>
      <thead><tr><th>#</th><th>Nama</th><th>Gol. Darah</th><th>Donasi</th></tr></thead>
      <tbody>
      <?php if(empty($topDonors)): ?>
        <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted)">Belum ada data</td></tr>
      <?php else: foreach($topDonors as $i=>$d): ?>
        <tr>
          <td><?=['🥇','🥈','🥉','4️⃣','5️⃣'][$i]?></td>
          <td><strong><?=sanitize($d['name'])?></strong></td>
          <td><?=$d['blood_type'].$d['rhesus']?></td>
          <td><strong style="color:var(--primary)"><?=$d['total_donations']?></strong> kali</td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Blood Stock Summary -->
<div class="card">
  <div class="card-header"><h3>🩸 Stok Whole Blood</h3></div>
  <div class="card-body">
    <div class="bar-chart">
      <?php
      $maxStock = max(array_column($stocks,'quantity')+[0]);
      foreach($stocks as $s):
        $pct=round($s['quantity']/max(1,$maxStock)*100);
        $color=$s['quantity']<=$s['min_stock']?'#dc2626':($s['quantity']<=$s['min_stock']*2?'#d97706':'#16a34a');
      ?>
        <div class="bar-item">
          <div class="bar-label" style="font-size:13px;width:50px"><?=$s['blood_type'].$s['rhesus']?></div>
          <div class="bar-track"><div class="bar-fill" data-width="<?=$pct?>" style="width:<?=$pct?>%;background:<?=$color?>"></div></div>
          <div class="bar-val"><?=$s['quantity']?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>

<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
