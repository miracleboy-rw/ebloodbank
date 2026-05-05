<?php
requireRole('pmi');
$id = (int)($_GET['id']??0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM events WHERE id=?");
$stmt->execute([$id]); $event=$stmt->fetch();
if(!$event){flashMessage('error','Event tidak ditemukan.');redirect('/ebloodbank/index.php?page=admin_events');}
$pageTitle   = 'Edit Event';
$currentPage = 'admin_events';
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><div class="breadcrumb"><a href="/ebloodbank/index.php?page=admin_events">Event</a> / <span>Edit</span></div><h2>✏️ Edit Event</h2></div>
</div>
<div class="card" style="max-width:700px">
  <div class="card-header"><h3>Edit Detail Event</h3></div>
  <div class="card-body">
    <form action="/ebloodbank/index.php?page=admin_event_save" method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" value="<?=$event['id']?>">
      <div class="form-group">
        <label class="form-label">Judul Event <span class="req">*</span></label>
        <input type="text" name="title" class="form-control" value="<?=sanitize($event['title'])?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="3"><?=sanitize($event['description']??'')?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Lokasi <span class="req">*</span></label>
        <input type="text" name="location" class="form-control" value="<?=sanitize($event['location'])?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Tanggal <span class="req">*</span></label>
          <input type="date" name="date" class="form-control" value="<?=$event['date']?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kuota <span class="req">*</span></label>
          <input type="number" name="quota" class="form-control" value="<?=$event['quota']?>" min="<?=$event['booked']?>" required>
          <div class="form-text">Sudah terisi: <?=$event['booked']?> orang</div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Waktu Mulai <span class="req">*</span></label>
          <input type="time" name="start_time" class="form-control" value="<?=$event['start_time']?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Waktu Selesai <span class="req">*</span></label>
          <input type="time" name="end_time" class="form-control" value="<?=$event['end_time']?>" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="active" <?=$event['status']==='active'?'selected':''?>>Aktif</option>
          <option value="completed" <?=$event['status']==='completed'?'selected':''?>>Selesai</option>
          <option value="cancelled" <?=$event['status']==='cancelled'?'selected':''?>>Dibatalkan</option>
        </select>
      </div>
      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn btn-primary">✅ Simpan Perubahan</button>
        <a href="/ebloodbank/index.php?page=admin_events" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
