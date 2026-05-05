<?php
// RS — Create blood request
requireRole('rs');
$pageTitle   = 'Request Darah';
$currentPage = 'rs_request';
$db   = getDB();
$user = currentUser();

// Pre-fill from URL params
$prefillBt  = sanitize($_GET['blood_type']??'');
$prefillRh  = sanitize($_GET['rhesus']??'');
$prefillUrg = sanitize($_GET['urgency']??'normal');

if($_SERVER['REQUEST_METHOD']==='POST'){
    $bt  = $_POST['blood_type']??'';
    $rh  = $_POST['rhesus']??'';
    $cmp = sanitize($_POST['component']??'Whole Blood');
    $qty = (int)($_POST['quantity']??1);
    $urg = in_array($_POST['urgency']??'',['normal','emergency'])?$_POST['urgency']:'normal';
    $pat = sanitize($_POST['patient_name']??'');
    $age = $_POST['patient_age']?(int)$_POST['patient_age']:null;
    $dia = sanitize($_POST['diagnosis']??'');
    $not = sanitize($_POST['notes']??'');

    $ins=$db->prepare("INSERT INTO requests (hospital_id,blood_type,rhesus,component,quantity,urgency,patient_name,patient_age,diagnosis,notes) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $ins->execute([$user['id'],$bt,$rh,$cmp,$qty,$urg,$pat,$age,$dia,$not]);
    logActivity($user['id'],'REQUEST_CREATE',"Request $bt$rh $qty ktg ($urg)");
    $msg = $urg==='emergency'?'🚨 Request emergency terkirim! PMI akan segera merespons.':'✅ Request berhasil dikirim ke PMI.';
    flashMessage('success',$msg);
    redirect('/ebloodbank/index.php?page=rs_my_requests');
}

// Live stock data
$stocks=$db->query("SELECT blood_type,rhesus,SUM(quantity) as total FROM blood_stock GROUP BY blood_type,rhesus ORDER BY blood_type,rhesus")->fetchAll();
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2><?=$prefillUrg==='emergency'?'🚨 Request Darah Emergency':'📋 Request Darah'?></h2><p>Kirim permintaan darah ke PMI</p></div>
</div>
<?php if($prefillUrg==='emergency'): ?>
  <div class="alert alert-error">🚨 <strong>MODE EMERGENCY:</strong> Request ini akan diprioritaskan oleh PMI. Pastikan situasi benar-benar darurat.</div>
<?php endif; ?>
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
<div class="card">
  <div class="card-header"><h3>Detail Permintaan</h3></div>
  <div class="card-body">
    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Golongan Darah <span class="req">*</span></label>
          <select name="blood_type" class="form-control" required>
            <option value="">-- Pilih --</option>
            <?php foreach(['A','B','AB','O'] as $bt): ?>
              <option value="<?=$bt?>" <?=$prefillBt===$bt?'selected':''?>><?=$bt?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Rhesus <span class="req">*</span></label>
          <select name="rhesus" class="form-control" required>
            <option value="">-- Pilih --</option>
            <option value="+" <?=$prefillRh==='+'?'selected':''?>>Positif (+)</option>
            <option value="-" <?=$prefillRh==='-'?'selected':''?>>Negatif (-)</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Komponen Darah <span class="req">*</span></label>
          <select name="component" class="form-control" required>
            <?php foreach(['Whole Blood','PRC','Trombosit','FFP','WB'] as $c): ?><option><?=$c?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Jumlah (kantong) <span class="req">*</span></label>
          <input type="number" name="quantity" class="form-control" min="1" max="50" value="1" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Urgensi <span class="req">*</span></label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <label style="cursor:pointer">
            <input type="radio" name="urgency" value="normal" <?=$prefillUrg!=='emergency'?'checked':''?> style="display:none">
            <div class="btn btn-info btn-block" style="justify-content:center">📋 Normal</div>
          </label>
          <label style="cursor:pointer">
            <input type="radio" name="urgency" value="emergency" <?=$prefillUrg==='emergency'?'checked':''?> style="display:none">
            <div class="btn btn-danger btn-block emergency-btn" style="justify-content:center">🚨 Emergency</div>
          </label>
        </div>
      </div>
      <hr class="divider">
      <h4 style="margin-bottom:12px;font-size:14px">Data Pasien (Opsional)</h4>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Nama Pasien</label><input type="text" name="patient_name" class="form-control" placeholder="Nama pasien"></div>
        <div class="form-group"><label class="form-label">Umur</label><input type="number" name="patient_age" class="form-control" placeholder="Umur" min="0" max="120"></div>
      </div>
      <div class="form-group"><label class="form-label">Diagnosa / Indikasi</label><input type="text" name="diagnosis" class="form-control" placeholder="Contoh: Anemia berat, operasi caesar"></div>
      <div class="form-group"><label class="form-label">Catatan Tambahan</label><textarea name="notes" class="form-control" rows="2" placeholder="Keterangan lain..."></textarea></div>
      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
        <?=$prefillUrg==='emergency'?'🚨 Kirim Request Emergency':'📋 Kirim Request'?>
      </button>
    </form>
  </div>
</div>
<!-- Stock Info -->
<div class="card">
  <div class="card-header"><h3>🩸 Ketersediaan Stok</h3></div>
  <div class="card-body">
    <div class="blood-grid" style="grid-template-columns:repeat(2,1fr)">
      <?php foreach($stocks as $s):
        $qty=(int)$s['total'];
        $class=$qty<=0?'critical':($qty<=10?'critical':($qty<=20?'low':'safe'));
      ?>
        <div class="blood-card <?=$class?>">
          <div class="blood-type-label"><?=$s['blood_type'].$s['rhesus']?></div>
          <div class="blood-qty"><?=$qty?></div>
          <div class="blood-status"><?=$class==='safe'?'Tersedia':($class==='low'?'Terbatas':'Kritis')?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
