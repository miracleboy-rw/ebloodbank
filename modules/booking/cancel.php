<?php
requireRole('donor');
$id   = (int)($_GET['id'] ?? 0);
$user = currentUser();
$db   = getDB();
$stmt = $db->prepare("SELECT * FROM bookings WHERE id=? AND user_id=? AND status='confirmed'");
$stmt->execute([$id, $user['id']]);
$booking = $stmt->fetch();
if (!$booking) { flashMessage('error','Booking tidak ditemukan.'); redirect('/ebloodbank/index.php?page=my_bookings'); }
$db->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$id]);
$db->prepare("UPDATE events SET booked = booked - 1 WHERE id=?")->execute([$booking['event_id']]);
logActivity($user['id'], 'CANCEL_BOOKING', "Booking #$id dibatalkan");
flashMessage('success','Booking berhasil dibatalkan.');
redirect('/ebloodbank/index.php?page=my_bookings');
