<?php
// PMI Admin — Event List
requireRole('pmi');
$pageTitle   = 'Kelola Event Donor';
$currentPage = 'admin_events';
$db = getDB();
$page   = max(1,(int)($_GET['p']??1));
$search = sanitize($_GET['q']??'');
$sql    = "SELECT e.*, u.name as organizer, (SELECT COUNT(*) FROM bookings b WHERE b.event_id=e.id AND b.status NOT IN ('cancelled','failed')) as confirmed_count FROM events e JOIN users u ON e.created_by=u.id WHERE 1=1";
$params = [];
if ($search) { $sql.=" AND (e.title LIKE ? OR e.location LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; }
$sql .= " ORDER BY e.date DESC";
$result = paginateQuery($sql, $params, $page, 8);
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>📅 Kelola Event Donor</h2><p>Buat dan manage jadwal event donor darah</p></div>
  <a href="/ebloodbank/index.php?page=admin_event_create" class="btn btn-primary">+ Buat Event</a>
</div>
<div class="card" style="margin-bottom:16px">
  <div class="card-body">
    <form method="GET" style="display:flex;gap:10px">
      <input type="hidden" name="page" value="admin_events">
      <input type="text" name="q" value="<?=sanitize($search)?>" class="form-control" placeholder="🔍 Cari event..." style="flex:1">
      <button class="btn btn-primary">Cari</button>
      <?php if($search):?><a href="?page=admin_events" class="btn btn-secondary">Reset</a><?php endif;?>
    </form>
  </div>
</div>
<div class="card">
  <div class="table-wrap" style="border:none">
    <table id="data-table">
      <thead><tr><th>#</th><th>Event</th><th>Tanggal</th><th>Lokasi</th><th>Kuota</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if(empty($result['data'])): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Tidak ada event ditemukan</td></tr>
      <?php else: foreach($result['data'] as $i=>$ev): $sisa=$ev['quota']-$ev['booked']; ?>
        <tr>
          <td style="color:var(--text-muted);font-size:13px"><?=($page-1)*8+$i+1?></td>
          <td>
            <div style="font-weight:600"><?=sanitize($ev['title'])?></div>
            <div style="font-size:12px;color:var(--text-muted)"><?=sanitize(substr($ev['description']??'',0,60))?></div>
          </td>
          <td style="font-size:13px"><?=formatDate($ev['date'])?><br><span style="color:var(--text-muted)"><?=substr($ev['start_time'],0,5)?>–<?=substr($ev['end_time'],0,5)?></span></td>
          <td style="font-size:13px;max-width:180px"><?=sanitize($ev['location'])?></td>
          <td>
            <div style="font-size:13px"><strong><?=$ev['confirmed_count']?></strong>/<?=$ev['quota']?></div>
            <div class="bar-track" style="margin-top:4px"><div class="bar-fill" data-width="<?=round($ev['confirmed_count']/$ev['quota']*100)?>" style="width:<?=round($ev['confirmed_count']/$ev['quota']*100)?>%"></div></div>
          </td>
          <td><?=getStatusBadge($ev['status'])?></td>
          <td>
            <div class="d-flex gap-2">
              <a href="/ebloodbank/index.php?page=admin_event_edit&id=<?=$ev['id']?>" class="btn btn-warning btn-sm">✏️</a>
              <a href="/ebloodbank/index.php?page=admin_event_delete&id=<?=$ev['id']?>" class="btn btn-danger btn-sm" data-confirm="Hapus event '<?=sanitize($ev['title'])?>'?">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($result['totalPages']>1): ?>
    <div class="card-footer"><div class="pagination">
      <?php for($i=1;$i<=$result['totalPages'];$i++): ?>
        <a href="?page=admin_events&p=<?=$i?>&q=<?=urlencode($search)?>" class="page-btn <?=$i==$result['page']?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div></div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
