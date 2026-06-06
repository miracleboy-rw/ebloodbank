<?php
requireRole('rs');
$pageTitle   = 'Stok Darah';
$currentPage = 'rs_stock';
$db = getDB();
$stocks = $db->query("SELECT * FROM blood_stock ORDER BY blood_type, rhesus, component")->fetchAll();
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>🩸 Ketersediaan Stok Darah PMI</h2><p>Data stok real-time dari PMI</p></div>
  <span class="badge badge-success" style="font-size:13px;padding:8px 14px">● Live</span>
</div>
<?php
$components = ['Whole Blood','PRC','Trombosit','FFP'];
foreach($components as $comp):
  $cs = array_filter($stocks,fn($s)=>$s['component']===$comp);
  if(empty($cs)) continue;
?>
<div class="card" style="margin-bottom:16px">
  <div class="card-header"><h3>🩸 <?=$comp?></h3></div>
  <div class="card-body">
    <div class="blood-grid">
      <?php foreach($cs as $s):
        $status=getStockStatus($s['quantity'],$s['min_stock']);
        $label=$s['blood_type'].$s['rhesus'];
        $class=str_replace(['badge-success','badge-warning','badge-danger','badge-info'],['safe','low','critical','low'],$status['class']);
      ?>
        <div class="blood-card <?=$class?>">
          <div class="blood-type-label"><?=$label?></div>
          <div class="blood-qty"><?=$s['quantity']?></div>
          <div class="blood-status"><?=$status['label']?></div>
          <div class="blood-component"><?=$s['quantity']?> kantong</div>
          <?php if($s['quantity']>0): ?>
            <a href="/ebloodbank/index.php?page=rs_request&blood_type=<?=$s['blood_type']?>&rhesus=<?=urlencode($s['rhesus'])?>" class="btn btn-primary btn-sm" style="margin-top:8px;font-size:11px">Request</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
