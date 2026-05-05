<?php
requireRole('pmi');
$pageTitle   = 'Update Stok Darah';
$currentPage = 'admin_stock';
$db   = getDB();
$user = currentUser();
$id   = (int)($_GET['id']??0);
$stock = null;
if($id){ $s=$db->prepare("SELECT * FROM blood_stock WHERE id=?"); $s->execute([$id]); $stock=$s->fetch(); }

if($_SERVER['REQUEST_METHOD']==='POST'){
    $sid    = (int)($_POST['stock_id']??0);
    $action = $_POST['stock_action']??'set';
    $amount = (int)($_POST['amount']??0);
    $notes  = sanitize($_POST['notes']??'');
    if($sid && $amount>=0){
        $cur = $db->prepare("SELECT quantity FROM blood_stock WHERE id=?");
        $cur->execute([$sid]); $cur=$cur->fetchColumn();
        $newQty = $action==='add' ? $cur+$amount : ($action==='subtract' ? max(0,$cur-$amount) : $amount);
        $db->prepare("UPDATE blood_stock SET quantity=? WHERE id=?")->execute([$newQty,$sid]);
        $desc = "Stok ID#$sid: $cur → $newQty ($action $amount). $notes";
        logActivity($user['id'],'STOCK_UPDATE',$desc);
        flashMessage('success',"Stok berhasil diperbarui: $newQty kantong.");
        redirect('/ebloodbank/index.php?page=admin_stock');
    }
}

$stocks = $db->query("SELECT * FROM blood_stock ORDER BY blood_type, rhesus, component")->fetchAll();
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><div class="breadcrumb"><a href="/ebloodbank/index.php?page=admin_stock">Stok Darah</a> / <span>Update</span></div><h2>🩸 Update Stok Darah</h2></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div class="card">
  <div class="card-header"><h3>Update Stok</h3></div>
  <div class="card-body">
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Pilih Golongan & Komponen <span class="req">*</span></label>
        <select name="stock_id" class="form-control" required>
          <option value="">-- Pilih --</option>
          <?php foreach($stocks as $s): ?>
            <option value="<?=$s['id']?>" <?=$stock&&$stock['id']==$s['id']?'selected':''?>>
              <?=$s['blood_type'].$s['rhesus']?> — <?=$s['component']?> (stok: <?=$s['quantity']?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Tipe Operasi <span class="req">*</span></label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
          <?php foreach(['add'=>['➕','Tambah','success'],'subtract'=>['➖','Kurangi','danger'],'set'=>['🔄','Set Langsung','info']] as $v=>[$icon,$lbl,$cls]): ?>
            <label style="cursor:pointer">
              <input type="radio" name="stock_action" value="<?=$v?>" <?=$v==='add'?'checked':''?> style="display:none" class="radio-opt">
              <div class="btn btn-<?=$cls?> btn-block" style="justify-content:center;font-size:13px"><?=$icon?> <?=$lbl?></div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Jumlah (kantong) <span class="req">*</span></label>
        <input type="number" name="amount" class="form-control" min="0" placeholder="0" required>
      </div>
      <div class="form-group">
        <label class="form-label">Catatan</label>
        <input type="text" name="notes" class="form-control" placeholder="Alasan update stok...">
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">✅ Simpan Update Stok</button>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-header"><h3>📊 Kondisi Stok Saat Ini</h3></div>
  <div class="card-body">
    <div class="bar-chart">
      <?php
      $wholeBloods = array_filter($stocks,fn($s)=>$s['component']==='Whole Blood');
      foreach($wholeBloods as $s):
        $pct = min(100, round($s['quantity']/max(1,$s['min_stock']*3)*100));
        $color = $s['quantity']<=$s['min_stock']?'#dc2626':($s['quantity']<=$s['min_stock']*2?'#d97706':'#16a34a');
      ?>
        <div class="bar-item">
          <div class="bar-label" style="font-size:13px"><?=$s['blood_type'].$s['rhesus']?></div>
          <div class="bar-track"><div class="bar-fill" data-width="<?=$pct?>" style="width:<?=$pct?>%;background:<?=$color?>"></div></div>
          <div class="bar-val"><?=$s['quantity']?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
