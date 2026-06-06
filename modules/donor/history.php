<?php
requireLogin();
$pageTitle   = 'Riwayat Donor';
$currentPage = 'history';
$db   = getDB();
$user = currentUser();
$page = max(1,(int)($_GET['p']??1));
$sql  = "SELECT b.*, e.title, e.date, e.location, s.hb, s.tensi_sistolik, s.tensi_diastolik, s.status as screen_status, s.fail_reason FROM bookings b JOIN events e ON b.event_id=e.id LEFT JOIN screenings s ON s.booking_id=b.id WHERE b.user_id=? ORDER BY e.date DESC";
$result = paginateQuery($sql,[$user['id']],$page,8);
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>📋 Riwayat Donor Saya</h2><p>Total donor: <strong><?=$user['total_donations']?> kali</strong></p></div>
</div>
<?php if(empty($result['data'])): ?>
  <div class="empty-state"><div class="icon">📋</div><h3>Belum ada riwayat</h3><p>Mulai donor dan riwayatmu akan muncul di sini.</p></div>
<?php else: ?>
<div class="card">
  <div class="table-wrap" style="border:none">
    <table id="data-table">
      <thead><tr><th>Event</th><th>Tanggal</th><th>Lokasi</th><th>HB</th><th>Tensi</th><th>Screening</th><th>Status Donor</th></tr></thead>
      <tbody>
      <?php foreach($result['data'] as $b): ?>
        <tr>
          <td><strong><?=sanitize($b['title'])?></strong></td>
          <td><?=formatDate($b['date'])?></td>
          <td style="font-size:12px;max-width:150px"><?=sanitize($b['location'])?></td>
          <td><?=$b['hb']?$b['hb'].' g/dL':'-'?></td>
          <td><?=$b['tensi_sistolik']?$b['tensi_sistolik'].'/'.$b['tensi_diastolik'].' mmHg':'-'?></td>
          <td><?=$b['screen_status']?getStatusBadge($b['screen_status']):'-'?></td>
          <td><?=getStatusBadge($b['status'])?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if($result['totalPages']>1): ?>
    <div class="card-footer"><div class="pagination">
      <?php for($i=1;$i<=$result['totalPages'];$i++): ?>
        <a href="?page=history&p=<?=$i?>" class="page-btn <?=$i==$result['page']?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div></div>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
