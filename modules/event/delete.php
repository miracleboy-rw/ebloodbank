<?php
requireRole('pmi');
$id = (int)($_GET['id']??0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM events WHERE id=?");
$stmt->execute([$id]); $event=$stmt->fetch();
if(!$event){flashMessage('error','Event tidak ditemukan.');redirect('/ebloodbank/index.php?page=admin_events');}
// Delete cascade bookings first (FK), then event
$db->prepare("DELETE FROM bookings WHERE event_id=?")->execute([$id]);
$db->prepare("DELETE FROM events WHERE id=?")->execute([$id]);
logActivity(currentUser()['id'],'EVENT_DELETE',"Event #$id dihapus: ".$event['title']);
flashMessage('success','Event berhasil dihapus.');
redirect('/ebloodbank/index.php?page=admin_events');
