<?php
// Blood Stock Management
requireRole('pmi');
$pageTitle   = 'Stok Darah';
$currentPage = 'admin_stock';
$db = getDB();
$stocks = $db->query("SELECT * FROM blood_stock ORDER BY blood_type, rhesus, component")->fetchAll();
$totalStock = array_sum(array_column($stocks,'quantity'));
$criticalCount = count(array_filter($stocks, fn($s)=>$s['quantity']<=$s['min_stock']));
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>🩸 Manajemen Stok Darah</h2><p>Monitor dan update ketersediaan darah real-time</p></div>
  <a href="/ebloodbank/index.php?page=admin_stock_update" class="btn btn-primary">+ Update Stok</a>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="stat-card red"><div class="stat-icon">🩸</div><div class="stat-value"><?=$totalStock?></div><div class="stat-label">Total Kantong</div></div>
  <div class="stat-card orange"><div class="stat-icon">⚠️</div><div class="stat-value"><?=$criticalCount?></div><div class="stat-label">Stok Kritis</div></div>
  <div class="stat-card green"><div class="stat-icon">✅</div><div class="stat-value"><?=count($stocks)-$criticalCount?></div><div class="stat-label">Stok Aman</div></div>
</div>

<!-- Blood Grid by component -->
<?php
$components = ['Whole Blood','PRC','Trombosit','FFP'];
foreach($components as $comp):
  $compStocks = array_filter($stocks,fn($s)=>$s['component']===$comp);
  if(empty($compStocks)) continue;
?>
<div class="card" style="margin-bottom:16px">
  <div class="card-header"><h3>🩸 <?=$comp?></h3></div>
  <div class="card-body">
    <div class="blood-grid">
      <?php foreach($compStocks as $s):
        $status = getStockStatus($s['quantity'],$s['min_stock']);
        $label  = $s['blood_type'].$s['rhesus'];
        $class  = str_replace(['badge-success','badge-warning','badge-danger','badge-info'],['safe','low','critical','low'],$status['class']);
      ?>
        <div class="blood-card <?=$class?>">
          <div class="blood-type-label"><?=$label?></div>
          <div class="blood-qty" data-qty="<?=$s['quantity']?>" data-min="<?=$s['min_stock']?>"><?=$s['quantity']?></div>
          <div class="blood-status" style="font-size:11px"><?=$status['label']?></div>
          <div class="blood-component">min: <?=$s['min_stock']?></div>
          <a href="/ebloodbank/index.php?page=admin_stock_update&id=<?=$s['id']?>" style="font-size:10px;color:var(--primary);font-weight:600;display:block;margin-top:4px">Edit →</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- Full Table -->
<div class="card">
  <div class="card-header"><h3>📋 Detail Stok Lengkap</h3></div>
  <div class="table-wrap" style="border:none">
    <table id="data-table">
      <thead><tr><th>Golongan</th><th>Rhesus</th><th>Komponen</th><th>Stok</th><th>Min. Stok</th><th>Status</th><th>Terakhir Update</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach($stocks as $s): $status=getStockStatus($s['quantity'],$s['min_stock']); ?>
        <tr>
          <td><strong><?=$s['blood_type']?></strong></td>
          <td><?=$s['rhesus']?></td>
          <td><?=$s['component']?></td>
          <td><strong style="font-size:16px"><?=$s['quantity']?></strong> kantong</td>
          <td><?=$s['min_stock']?> kantong</td>
          <td><span class="badge <?=$status['class']?>"><?=$status['label']?></span></td>
          <td style="font-size:12px"><?=formatDateTime($s['updated_at'])?></td>
          <td><a href="/ebloodbank/index.php?page=admin_stock_update&id=<?=$s['id']?>" class="btn btn-warning btn-sm">✏️ Update</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
