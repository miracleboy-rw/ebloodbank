<?php
requireRole('pmi');
$pageTitle   = 'Manajemen User';
$currentPage = 'admin_users';
$db   = getDB();
$page   = max(1,(int)($_GET['p']??1));
$role   = sanitize($_GET['role']??'');
$search = sanitize($_GET['q']??'');
$sql    = "SELECT * FROM users WHERE 1=1";
$params = [];
if($role)  { $sql.=" AND role=?";          $params[]=$role; }
if($search){ $sql.=" AND (name LIKE ? OR email LIKE ? OR nik LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; $params[]="%$search%"; }
$sql.=" ORDER BY created_at DESC";
$result = paginateQuery($sql,$params,$page,12);
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>👥 Manajemen User</h2><p>Kelola semua pengguna E-BloodBank</p></div>
</div>
<div class="card" style="margin-bottom:16px">
  <div class="card-body">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap">
      <input type="hidden" name="page" value="admin_users">
      <input type="text" name="q" value="<?=sanitize($search)?>" class="form-control" placeholder="🔍 Cari nama/email/NIK..." style="flex:1;min-width:200px">
      <select name="role" class="form-control" style="width:150px">
        <option value="">Semua Role</option>
        <option value="donor" <?=$role==='donor'?'selected':''?>>🩸 Donor</option>
        <option value="pmi"   <?=$role==='pmi'?'selected':''?>>🏛️ PMI</option>
        <option value="rs"    <?=$role==='rs'?'selected':''?>>🏥 RS</option>
      </select>
      <button class="btn btn-primary">Filter</button>
      <?php if($search||$role): ?><a href="?page=admin_users" class="btn btn-secondary">Reset</a><?php endif; ?>
    </form>
  </div>
</div>
<div class="card">
  <div class="table-wrap" style="border:none">
    <table id="data-table">
      <thead><tr><th>#</th><th>Nama</th><th>Email</th><th>NIK</th><th>Role</th><th>Gol. Darah</th><th>Total Donor</th><th>Status</th><th>Terdaftar</th></tr></thead>
      <tbody>
      <?php if(empty($result['data'])): ?>
        <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">Tidak ada data</td></tr>
      <?php else: foreach($result['data'] as $i=>$u): ?>
        <tr>
          <td style="color:var(--text-muted)"><?=($page-1)*12+$i+1?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0"><?=strtoupper(substr($u['name'],0,1))?></div>
              <span style="font-weight:600"><?=sanitize($u['name'])?></span>
            </div>
          </td>
          <td style="font-size:13px"><?=sanitize($u['email'])?></td>
          <td style="font-size:12px;font-family:monospace"><?=sanitize($u['nik']??'-')?></td>
          <td><span class="badge <?=$u['role']==='pmi'?'badge-primary':($u['role']==='rs'?'badge-info':'badge-success')?>"><?=ucfirst($u['role'])?></span></td>
          <td><strong><?=$u['blood_type']&&$u['rhesus']?$u['blood_type'].$u['rhesus']:'-'?></strong></td>
          <td><?=$u['total_donations']?? 0?></td>
          <td><span class="badge <?=$u['is_active']?'badge-success':'badge-danger'?>"><?=$u['is_active']?'Aktif':'Nonaktif'?></span></td>
          <td style="font-size:12px"><?=formatDate($u['created_at'],'d M Y')?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($result['totalPages']>1): ?>
    <div class="card-footer"><div class="pagination">
      <?php for($i=1;$i<=$result['totalPages'];$i++): ?>
        <a href="?page=admin_users&p=<?=$i?>&q=<?=urlencode($search)?>&role=<?=$role?>" class="page-btn <?=$i==$result['page']?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div></div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
