<?php
// PMI — Requests from Hospitals
requireRole('pmi');
$pageTitle   = 'Request Darah RS';
$currentPage = 'admin_requests';
$db   = getDB();
$page   = max(1,(int)($_GET['p']??1));
$status = sanitize($_GET['status']??'pending');
$sql    = "SELECT r.*, u.name as hospital_name, u.hospital_name as rs_name, a.name as approver_name
           FROM requests r JOIN users u ON r.hospital_id=u.id LEFT JOIN users a ON r.approved_by=a.id
           WHERE r.status=? ORDER BY r.urgency DESC, r.created_at ASC";
$result = paginateQuery($sql,[$status],$page,10);
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>🏥 Request Darah Rumah Sakit</h2><p>Kelola permintaan darah dari RS</p></div>
  <div class="d-flex gap-2">
    <?php foreach(['pending'=>'⏳ Pending','approved'=>'✅ Approved','rejected'=>'❌ Rejected','fulfilled'=>'🩸 Fulfilled'] as $v=>$lbl): ?>
      <a href="?page=admin_requests&status=<?=$v?>" class="btn <?=$status===$v?'btn-primary':'btn-secondary'?> btn-sm"><?=$lbl?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="card">
  <div class="table-wrap" style="border:none">
    <table id="data-table">
      <thead><tr><th>Rumah Sakit</th><th>Kebutuhan Darah</th><th>Qty</th><th>Urgensi</th><th>Pasien</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if(empty($result['data'])): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Tidak ada request dengan status ini</td></tr>
      <?php else: foreach($result['data'] as $r): ?>
        <tr <?=$r['urgency']==='emergency'?'style="background:#fff5f5"':''?>>
          <td>
            <div style="font-weight:600"><?=sanitize($r['hospital_name'])?></div>
            <div style="font-size:12px;color:var(--text-muted)"><?=sanitize($r['rs_name']??'')?></div>
          </td>
          <td><strong style="font-size:16px"><?=$r['blood_type'].$r['rhesus']?></strong><br><span style="font-size:12px;color:var(--text-muted)"><?=$r['component']?></span></td>
          <td><strong><?=$r['quantity']?></strong> ktg</td>
          <td><?=getUrgencyBadge($r['urgency'])?></td>
          <td style="font-size:12px"><?=sanitize($r['patient_name']??'-')?></td>
          <td><?=getStatusBadge($r['status'])?></td>
          <td style="font-size:12px"><?=formatDateTime($r['created_at'])?></td>
          <td>
            <?php if($r['status']==='pending'): ?>
              <div class="d-flex gap-2">
                <a href="/ebloodbank/index.php?page=request_approve&id=<?=$r['id']?>" class="btn btn-success btn-sm" data-confirm="Setujui request ini dan kurangi stok?">✅ Approve</a>
                <a href="/ebloodbank/index.php?page=request_reject&id=<?=$r['id']?>" class="btn btn-danger btn-sm" data-confirm="Tolak request ini?">❌ Tolak</a>
              </div>
            <?php elseif($r['status']==='approved'): ?>
              <a href="/ebloodbank/index.php?page=request_approve&id=<?=$r['id']?>&fulfill=1" class="btn btn-info btn-sm" data-confirm="Tandai sudah diserahkan?">🩸 Fulfilled</a>
            <?php else: ?>
              <?php if($r['approver_name']): ?><span style="font-size:12px;color:var(--text-muted)">oleh <?=sanitize($r['approver_name'])?></span><?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($result['totalPages']>1): ?>
    <div class="card-footer"><div class="pagination">
      <?php for($i=1;$i<=$result['totalPages'];$i++): ?>
        <a href="?page=admin_requests&p=<?=$i?>&status=<?=$status?>" class="page-btn <?=$i==$result['page']?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div></div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
