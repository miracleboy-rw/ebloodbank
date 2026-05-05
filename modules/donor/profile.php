<?php
requireLogin();
$pageTitle   = 'Profil Saya';
$currentPage = 'profile';
$db   = getDB();
$user = currentUser();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name  = sanitize($_POST['name']??'');
    $phone = sanitize($_POST['phone']??'');
    $addr  = sanitize($_POST['address']??'');
    $bt    = in_array($_POST['blood_type']??'',['A','B','AB','O',''])?($_POST['blood_type']??null):null;
    $rh    = in_array($_POST['rhesus']??'',['+','-',''])?($_POST['rhesus']??null):null;
    $hname = sanitize($_POST['hospital_name']??'');

    // Password change
    if(!empty($_POST['new_password'])){
        if(!password_verify($_POST['current_password']??'',$user['password'])){
            flashMessage('error','Password saat ini salah.');
            redirect('/ebloodbank/index.php?page=profile');
        }
        if(strlen($_POST['new_password'])<6){
            flashMessage('error','Password baru minimal 6 karakter.');
            redirect('/ebloodbank/index.php?page=profile');
        }
        $hashed = password_hash($_POST['new_password'],PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hashed,$user['id']]);
    }

    $db->prepare("UPDATE users SET name=?,phone=?,address=?,blood_type=?,rhesus=?,hospital_name=? WHERE id=?")->execute([$name,$phone,$addr,$bt,$rh,$hname,$user['id']]);
    $_SESSION['user_name']=$name;
    logActivity($user['id'],'PROFILE_UPDATE','Profil diperbarui');
    flashMessage('success','Profil berhasil diperbarui.');
    redirect('/ebloodbank/index.php?page=profile');
}
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><h2>👤 Profil Saya</h2></div>
</div>
<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">
<!-- Profile Card -->
<div class="card" style="text-align:center">
  <div class="card-body">
    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-size:32px;font-weight:700;margin:0 auto 16px"><?=strtoupper(substr($user['name'],0,1))?></div>
    <h3 style="font-size:18px;font-weight:700"><?=sanitize($user['name'])?></h3>
    <div style="margin-top:6px"><?=getStatusBadge($user['role']==='donor'?'active':'approved')?> <span class="badge badge-info"><?=ucfirst($user['role'])?></span></div>
    <?php if($user['blood_type']): ?>
      <div style="margin-top:16px;font-size:28px;font-weight:800;color:var(--primary)"><?=$user['blood_type'].$user['rhesus']?></div>
      <div style="font-size:12px;color:var(--text-muted)">Golongan Darah</div>
    <?php endif; ?>
    <?php if($user['role']==='donor'): ?>
      <div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:10px;text-align:center">
        <div style="background:var(--primary-light);border-radius:10px;padding:12px">
          <div style="font-size:22px;font-weight:800;color:var(--primary)"><?=$user['total_donations']?></div>
          <div style="font-size:11px;color:var(--text-muted)">Donor</div>
        </div>
        <div style="background:#dcfce7;border-radius:10px;padding:12px">
          <div style="font-size:22px;font-weight:800;color:var(--success)"><?=$user['total_donations']*3?></div>
          <div style="font-size:11px;color:var(--text-muted)">Nyawa</div>
        </div>
      </div>
      <div style="margin-top:10px;font-size:12px;color:var(--text-muted)">Donor terakhir: <strong><?=formatDate($user['last_donation'])?></strong></div>
    <?php endif; ?>
  </div>
</div>

<!-- Edit Form -->
<div class="card">
  <div class="card-header"><h3>Edit Informasi</h3></div>
  <div class="card-body">
    <form method="POST">
      <div class="form-row">
        <div class="form-group"><label class="form-label">Nama Lengkap <span class="req">*</span></label><input type="text" name="name" class="form-control" value="<?=sanitize($user['name'])?>" required></div>
        <div class="form-group"><label class="form-label">No. Telepon</label><input type="text" name="phone" class="form-control" value="<?=sanitize($user['phone']??'')?>"></div>
      </div>
      <div class="form-group"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2"><?=sanitize($user['address']??'')?></textarea></div>
      <?php if($user['role']==='donor'): ?>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Golongan Darah</label><select name="blood_type" class="form-control"><option value="">--</option><?php foreach(['A','B','AB','O'] as $bt): ?><option <?=$user['blood_type']===$bt?'selected':''?>><?=$bt?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Rhesus</label><select name="rhesus" class="form-control"><option value="">--</option><option value="+" <?=$user['rhesus']==='+' ?'selected':''?>>Positif (+)</option><option value="-" <?=$user['rhesus']==='-'?'selected':''?>>Negatif (-)</option></select></div>
      </div>
      <?php elseif($user['role']==='rs'): ?>
      <div class="form-group"><label class="form-label">Nama Rumah Sakit</label><input type="text" name="hospital_name" class="form-control" value="<?=sanitize($user['hospital_name']??'')?>"></div>
      <?php endif; ?>

      <hr class="divider">
      <h4 style="margin-bottom:12px;font-size:14px">🔑 Ganti Password (Opsional)</h4>
      <div class="form-group"><label class="form-label">Password Saat Ini</label><input type="password" name="current_password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti"></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Password Baru</label><input type="password" name="new_password" class="form-control" placeholder="Min. 6 karakter"></div>
        <div class="form-group"><label class="form-label">Konfirmasi Baru</label><input type="password" name="confirm_password" class="form-control"></div>
      </div>
      <button type="submit" class="btn btn-primary btn-lg">✅ Simpan Perubahan</button>
    </form>
  </div>
</div>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
