<?php
// Screening list — PMI Admin
requireRole('pmi');
$pageTitle   = 'Screening Donor';
$currentPage = 'admin_screening';
$db = getDB();
$page   = max(1,(int)($_GET['p']??1));
$search = sanitize($_GET['q']??'');
$status = sanitize($_GET['status']??'confirmed');

$sql = "SELECT b.*, u.name as donor_name, u.blood_type, u.rhesus, e.title as event_title, e.date as event_date
        FROM bookings b
        JOIN users u ON b.user_id=u.id
        JOIN events e ON b.event_id=e.id
        WHERE b.status=?";
$params = [$status];
if($search){ $sql.=" AND (u.name LIKE ? OR b.qr_code LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; }
$sql.=" ORDER BY b.booking_time DESC";
$result = paginateQuery($sql,$params,$page,10);
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>🧪 Screening Donor</h2><p>Input hasil screening HB & tensi darah</p></div>
</div>
<!-- Filter -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap">
      <input type="hidden" name="page" value="admin_screening">
      <input type="text" name="q" value="<?=sanitize($search)?>" class="form-control" placeholder="🔍 Cari nama/QR..." style="flex:1;min-width:200px">
      <select name="status" class="form-control" style="width:160px">
        <option value="confirmed" <?=$status==='confirmed'?'selected':''?>>Menunggu Screening</option>
        <option value="screened" <?=$status==='screened'?'selected':''?>>Sudah Screening</option>
        <option value="donated" <?=$status==='donated'?'selected':''?>>Selesai Donor</option>
      </select>
      <button class="btn btn-primary">Filter</button>
    </form>
  </div>
</div>
<div class="card">
  <div class="table-wrap" style="border:none">
    <table id="data-table">
      <thead><tr><th>Donor</th><th>QR Code</th><th>Event</th><th>Gol. Darah</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if(empty($result['data'])): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">Tidak ada data</td></tr>
      <?php else: foreach($result['data'] as $b): ?>
        <tr>
          <td><strong><?=sanitize($b['donor_name'])?></strong></td>
          <td><code style="font-size:12px;background:var(--surface2);padding:3px 8px;border-radius:4px"><?=$b['qr_code']?></code></td>
          <td style="font-size:12px"><?=sanitize($b['event_title'])?><br><span style="color:var(--text-muted)"><?=formatDate($b['event_date'])?></span></td>
          <td><strong><?=$b['blood_type'].$b['rhesus']?></strong></td>
          <td><?=getStatusBadge($b['status'])?></td>
          <td>
            <?php if($b['status']==='confirmed'): ?>
              <a href="/ebloodbank/index.php?page=screening_input&booking_id=<?=$b['id']?>" class="btn btn-primary btn-sm">🧪 Input Screening</a>
            <?php elseif($b['status']==='screened'): ?>
              <a href="/ebloodbank/index.php?page=screening_input&booking_id=<?=$b['id']?>&mark_donated=1" class="btn btn-success btn-sm" data-confirm="Tandai donor selesai?">✅ Tandai Donor</a>
            <?php else: ?>
              <span class="badge badge-success">Selesai</span>
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
        <a href="?page=admin_screening&p=<?=$i?>&q=<?=urlencode($search)?>&status=<?=$status?>" class="page-btn <?=$i==$result['page']?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div></div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
