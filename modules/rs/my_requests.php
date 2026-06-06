<?php
requireRole('rs');
$pageTitle   = 'Riwayat Request';
$currentPage = 'rs_my_requests';
$db   = getDB();
$user = currentUser();
$page   = max(1,(int)($_GET['p']??1));
$status = sanitize($_GET['status']??'');
$sql    = "SELECT * FROM requests WHERE hospital_id=?";
$params = [$user['id']];
if($status){ $sql.=" AND status=?"; $params[]=$status; }
$sql.=" ORDER BY created_at DESC";
$result = paginateQuery($sql,$params,$page,10);
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>📜 Riwayat Request Darah</h2></div>
  <div class="d-flex gap-2">
    <a href="?page=rs_my_requests" class="btn <?=!$status?'btn-primary':'btn-secondary'?> btn-sm">Semua</a>
    <?php foreach(['pending'=>'⏳','approved'=>'✅','rejected'=>'❌','fulfilled'=>'🩸'] as $v=>$ic): ?>
      <a href="?page=rs_my_requests&status=<?=$v?>" class="btn <?=$status===$v?'btn-primary':'btn-secondary'?> btn-sm"><?=$ic?> <?=ucfirst($v)?></a>
    <?php endforeach; ?>
  </div>
</div>
<?php if(empty($result['data'])): ?>
  <div class="empty-state"><div class="icon">📭</div><h3>Belum ada request</h3><a href="/ebloodbank/index.php?page=rs_request" class="btn btn-primary" style="margin-top:16px">Buat Request Baru</a></div>
<?php else: ?>
<div class="card">
  <div class="table-wrap" style="border:none">
    <table id="data-table">
      <thead><tr><th>Golongan</th><th>Komponen</th><th>Jumlah</th><th>Urgensi</th><th>Pasien</th><th>Status</th><th>Tanggal</th></tr></thead>
      <tbody>
      <?php foreach($result['data'] as $r): ?>
        <tr <?=$r['urgency']==='emergency'?'style="background:#fff5f5"':''?>>
          <td><strong style="font-size:18px"><?=$r['blood_type'].$r['rhesus']?></strong></td>
          <td><?=$r['component']?></td>
          <td><strong><?=$r['quantity']?></strong> kantong</td>
          <td><?=getUrgencyBadge($r['urgency'])?></td>
          <td style="font-size:13px"><?=sanitize($r['patient_name']??'-')?><?=$r['patient_age']?' ('.$r['patient_age'].' th)':''?><br><span style="color:var(--text-muted)"><?=sanitize($r['diagnosis']??'')?></span></td>
          <td><?=getStatusBadge($r['status'])?></td>
          <td style="font-size:12px"><?=formatDateTime($r['created_at'])?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if($result['totalPages']>1): ?>
    <div class="card-footer"><div class="pagination">
      <?php for($i=1;$i<=$result['totalPages'];$i++): ?>
        <a href="?page=rs_my_requests&p=<?=$i?>&status=<?=$status?>" class="page-btn <?=$i==$result['page']?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div></div>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
