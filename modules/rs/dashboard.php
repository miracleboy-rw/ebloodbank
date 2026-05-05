<?php
// RS Dashboard
requireRole('rs');
$pageTitle   = 'Dashboard Rumah Sakit';
$currentPage = 'rs_dashboard';
$db   = getDB();
$user = currentUser();

$pendingReq   = $db->prepare("SELECT COUNT(*) FROM requests WHERE hospital_id=? AND status='pending'");
$pendingReq->execute([$user['id']]); $pendingReq = $pendingReq->fetchColumn();

$approvedReq  = $db->prepare("SELECT COUNT(*) FROM requests WHERE hospital_id=? AND status='approved'");
$approvedReq->execute([$user['id']]); $approvedReq = $approvedReq->fetchColumn();

$fulfilledReq = $db->prepare("SELECT COUNT(*) FROM requests WHERE hospital_id=? AND status='fulfilled'");
$fulfilledReq->execute([$user['id']]); $fulfilledReq = $fulfilledReq->fetchColumn();

// All blood stock
$stocks = $db->query("SELECT blood_type, rhesus, SUM(quantity) as total, MIN(min_stock) as min_stock FROM blood_stock GROUP BY blood_type, rhesus ORDER BY blood_type, rhesus")->fetchAll();

// My recent requests
$myReq = $db->prepare("SELECT * FROM requests WHERE hospital_id=? ORDER BY created_at DESC LIMIT 5");
$myReq->execute([$user['id']]);
$myRequests = $myReq->fetchAll();

include __DIR__ . '/../../views/layouts/header.php';
?>

<!-- Emergency Banner -->
<div style="background:linear-gradient(135deg,#dc2626,#991b1b);border-radius:14px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;color:#fff">
  <div>
    <h3 style="font-size:16px;font-weight:700;margin-bottom:4px">🚨 Butuh Darah Mendesak?</h3>
    <p style="font-size:13px;opacity:.8">Kirim permintaan emergency langsung ke PMI — diproses prioritas!</p>
  </div>
  <a href="/ebloodbank/index.php?page=rs_request&urgency=emergency" class="btn emergency-btn" style="background:#fff;color:#dc2626;font-weight:700">
    🚨 Request Emergency
  </a>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card orange">
    <div class="stat-icon">⏳</div>
    <div class="stat-value"><?= $pendingReq ?></div>
    <div class="stat-label">Request Menunggu</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon">✅</div>
    <div class="stat-value"><?= $approvedReq ?></div>
    <div class="stat-label">Request Disetujui</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon">🩸</div>
    <div class="stat-value"><?= $fulfilledReq ?></div>
    <div class="stat-label">Total Terpenuhi</div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon">🏥</div>
    <div class="stat-value"><?= sanitize($user['hospital_name'] ?? 'RS') ?></div>
    <div class="stat-label">Institusi</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

<!-- Blood Stock Realtime -->
<div class="card">
  <div class="card-header">
    <h3>🩸 Stok Darah PMI (Real-time)</h3>
    <a href="/ebloodbank/index.php?page=rs_stock" class="btn btn-secondary btn-sm">Detail</a>
  </div>
  <div class="card-body">
    <div class="blood-grid">
      <?php foreach ($stocks as $s):
        $qty   = (int)$s['total'];
        $label = $s['blood_type'] . $s['rhesus'];
        $class = $qty <= 0 ? 'critical' : ($qty <= $s['min_stock'] ? 'critical' : ($qty <= $s['min_stock']*2 ? 'low' : 'safe'));
      ?>
        <div class="blood-card <?= $class ?>">
          <div class="blood-type-label"><?= $label ?></div>
          <div class="blood-qty"><?= $qty ?></div>
          <div class="blood-status"><?= $class==='safe'?'Tersedia':($class==='low'?'Terbatas':'Kritis') ?></div>
          <?php if ($qty > 0): ?>
            <a href="/ebloodbank/index.php?page=rs_request&blood_type=<?= $s['blood_type'] ?>&rhesus=<?= urlencode($s['rhesus']) ?>" style="font-size:10px;color:var(--primary);font-weight:600;margin-top:4px;display:block">Request →</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div style="display:flex;flex-direction:column;gap:16px">
  <div class="card">
    <div class="card-header"><h3>⚡ Aksi Cepat</h3></div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
      <a href="/ebloodbank/index.php?page=rs_request" class="btn btn-primary btn-block">📋 Request Darah Normal</a>
      <a href="/ebloodbank/index.php?page=rs_my_requests" class="btn btn-secondary btn-block">📜 Riwayat Request</a>
      <a href="/ebloodbank/index.php?page=rs_stock" class="btn btn-info btn-block">🩸 Lihat Detail Stok</a>
    </div>
  </div>
</div>

</div>

<!-- Recent Requests -->
<div class="card" style="margin-top:20px">
  <div class="card-header">
    <h3>📜 Request Terakhir</h3>
    <a href="/ebloodbank/index.php?page=rs_my_requests" class="btn btn-secondary btn-sm">Semua</a>
  </div>
  <div class="table-wrap" style="border-radius:0;border:none">
    <?php if (empty($myRequests)): ?>
      <div class="empty-state"><div class="icon">📭</div><h3>Belum ada request</h3></div>
    <?php else: ?>
      <table id="data-table">
        <thead><tr><th>Golongan</th><th>Komponen</th><th>Jumlah</th><th>Urgensi</th><th>Status</th><th>Tanggal</th></tr></thead>
        <tbody>
        <?php foreach ($myRequests as $r): ?>
          <tr>
            <td><strong><?= $r['blood_type'].$r['rhesus'] ?></strong></td>
            <td><?= $r['component'] ?></td>
            <td><?= $r['quantity'] ?> kantong</td>
            <td><?= getUrgencyBadge($r['urgency']) ?></td>
            <td><?= getStatusBadge($r['status']) ?></td>
            <td style="font-size:12px"><?= formatDateTime($r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
