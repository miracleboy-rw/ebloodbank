<?php
requireRole('pmi');
$id=$db=(int)($_GET['id']??0); $db=getDB(); $user=currentUser();
$stmt=$db->prepare("SELECT * FROM requests WHERE id=? AND status='pending'");
$stmt->execute([$id]); $req=$stmt->fetch();
if(!$req){flashMessage('error','Request tidak dapat ditolak.');redirect('/ebloodbank/index.php?page=admin_requests');}
$db->prepare("UPDATE requests SET status='rejected', approved_by=?, approved_at=NOW() WHERE id=?")->execute([$user['id'],$id]);
logActivity($user['id'],'REQUEST_REJECT',"Request #$id ditolak");
flashMessage('success','Request telah ditolak.');
redirect('/ebloodbank/index.php?page=admin_requests');
