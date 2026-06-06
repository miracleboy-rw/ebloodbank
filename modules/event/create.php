<?php
requireRole('pmi');
$pageTitle   = 'Buat Event Donor';
$currentPage = 'admin_events';
include __DIR__.'/../../views/layouts/header.php';
?>
<div class="page-header">
  <div><div class="breadcrumb"><a href="/ebloodbank/index.php?page=admin_events">Event</a> / <span>Buat Baru</span></div><h2>📅 Buat Event Donor</h2></div>
</div>
<div class="card" style="max-width:700px">
  <div class="card-header"><h3>Detail Event</h3></div>
  <div class="card-body">
    <form action="/ebloodbank/index.php?page=admin_event_save" method="POST">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label class="form-label">Judul Event <span class="req">*</span></label>
        <input type="text" name="title" class="form-control" placeholder="Contoh: Donor Darah Rutin Mei 2026" required>
      </div>
      <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Informasi tambahan tentang event..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Lokasi <span class="req">*</span></label>
        <input type="text" name="location" class="form-control" placeholder="Alamat lengkap lokasi event" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Tanggal <span class="req">*</span></label>
          <input type="date" name="date" class="form-control" min="<?=date('Y-m-d')?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kuota Peserta <span class="req">*</span></label>
          <input type="number" name="quota" class="form-control" placeholder="50" min="1" max="500" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Waktu Mulai <span class="req">*</span></label>
          <input type="time" name="start_time" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Waktu Selesai <span class="req">*</span></label>
          <input type="time" name="end_time" class="form-control" required>
        </div>
      </div>
      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn btn-primary btn-lg">✅ Simpan Event</button>
        <a href="/ebloodbank/index.php?page=admin_events" class="btn btn-secondary btn-lg">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__.'/../../views/layouts/footer.php'; ?>
