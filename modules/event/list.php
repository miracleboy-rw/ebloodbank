<?php
// Public event list (donor view)
requireLogin();
$pageTitle   = 'Event Donor';
$currentPage = 'events';
$db = getDB();

$page   = max(1, (int)($_GET['p'] ?? 1));
$search = sanitize($_GET['q'] ?? '');
$sql    = "SELECT e.*, u.name as organizer FROM events e JOIN users u ON e.created_by=u.id WHERE e.status='active' AND e.date >= CURDATE()";
$params = [];
if ($search) {
    $sql .= " AND (e.title LIKE ? OR e.location LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= " ORDER BY e.date ASC";
$result = paginateQuery($sql, $params, $page, 6);

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="page-header">
  <div><h2>📅 Event Donor Darah</h2><p>Temukan dan booking jadwal donor terdekat</p></div>
</div>

<!-- Search -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body">
    <form method="GET" action="">
      <input type="hidden" name="page" value="events">
      <div style="display:flex;gap:12px">
        <input type="text" name="q" value="<?= sanitize($search) ?>" class="form-control" placeholder="🔍 Cari event atau lokasi..." style="flex:1">
        <button type="submit" class="btn btn-primary">Cari</button>
        <?php if ($search): ?><a href="?page=events" class="btn btn-secondary">Reset</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Events Grid -->
<?php if (empty($result['data'])): ?>
  <div class="empty-state"><div class="icon">📅</div><h3>Tidak ada event tersedia</h3><p>Coba ubah kata kunci pencarian</p></div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px">
    <?php
    $user = currentUser();
    $eligible = isEligibleDonor($user['last_donation']);
    foreach ($result['data'] as $ev):
      $sisa = $ev['quota'] - $ev['booked'];
      $pct  = ($ev['booked'] / max(1,$ev['quota'])) * 100;
      // Check if already booked
      $booked = getDB()->prepare("SELECT id FROM bookings WHERE user_id=? AND event_id=? AND status NOT IN ('cancelled','failed')");
      $booked->execute([$user['id'], $ev['id']]);
      $alreadyBooked = $booked->fetch();
    ?>
      <div class="card" style="transition:.25s" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
        <div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));padding:16px 18px;border-radius:12px 12px 0 0;color:#fff">
          <div style="font-size:11px;opacity:.7;margin-bottom:4px">📅 <?= formatDate($ev['date'], 'l, d F Y') ?></div>
          <div style="font-weight:700;font-size:16px;line-height:1.3"><?= sanitize($ev['title']) ?></div>
        </div>
        <div class="card-body">
          <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:12px">
            <div style="font-size:13px">📍 <?= sanitize($ev['location']) ?></div>
            <div style="font-size:13px">⏰ <?= substr($ev['start_time'],0,5) ?> – <?= substr($ev['end_time'],0,5) ?> WIB</div>
            <?php if ($ev['description']): ?>
              <div style="font-size:12px;color:var(--text-muted);margin-top:4px"><?= sanitize(substr($ev['description'],0,100)) ?>...</div>
            <?php endif; ?>
          </div>
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
              <span>Kuota Terisi</span>
              <span><strong><?= $ev['booked'] ?></strong>/<?= $ev['quota'] ?> (sisa: <?= $sisa ?>)</span>
            </div>
            <div class="bar-track"><div class="bar-fill" data-width="<?= round($pct) ?>" style="width:<?= round($pct) ?>%;background:<?= $pct>80?'#dc2626':($pct>50?'#d97706':'#16a34a') ?>"></div></div>
          </div>
          <?php if ($alreadyBooked): ?>
            <div class="btn btn-success btn-block" style="cursor:default">✅ Sudah Booking</div>
          <?php elseif ($sisa <= 0): ?>
            <div class="btn btn-secondary btn-block" style="cursor:not-allowed">🔴 Kuota Penuh</div>
          <?php elseif (!$eligible): ?>
            <div class="btn btn-secondary btn-block" style="cursor:not-allowed">⏳ Belum Eligible (<?= daysUntilEligible($user['last_donation']) ?> hari lagi)</div>
          <?php else: ?>
            <a href="/ebloodbank/index.php?page=booking&event_id=<?= $ev['id'] ?>" class="btn btn-primary btn-block">🎫 Booking Sekarang</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($result['totalPages'] > 1): ?>
    <div class="pagination">
      <?php for ($i=1; $i<=$result['totalPages']; $i++): ?>
        <a href="?page=events&p=<?= $i ?>&q=<?= urlencode($search) ?>" class="page-btn <?= $i==$result['page']?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
