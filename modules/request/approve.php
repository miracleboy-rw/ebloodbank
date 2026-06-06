<?php
requireRole('pmi');
$id   = (int)($_GET['id']??0);
$fulfill = isset($_GET['fulfill']);
$db   = getDB();
$user = currentUser();
$stmt = $db->prepare("SELECT * FROM requests WHERE id=?");
$stmt->execute([$id]); $req=$stmt->fetch();
if(!$req){flashMessage('error','Request tidak ditemukan.');redirect('/ebloodbank/index.php?page=admin_requests');}

if($fulfill && $req['status']==='approved'){
    $db->prepare("UPDATE requests SET status='fulfilled' WHERE id=?")->execute([$id]);
    logActivity($user['id'],'REQUEST_FULFILLED',"Request #$id terpenuhi");
    flashMessage('success','Request ditandai sudah terpenuhi.');
    redirect('/ebloodbank/index.php?page=admin_requests&status=approved');
}

// Check stock
$stock = $db->prepare("SELECT * FROM blood_stock WHERE blood_type=? AND rhesus=? AND component=?");
$stock->execute([$req['blood_type'],$req['rhesus'],$req['component']]);
$stockRow=$stock->fetch();
if(!$stockRow||$stockRow['quantity']<$req['quantity']){
    flashMessage('error','Stok tidak mencukupi. Stok tersedia: '.($stockRow['quantity']??0).' kantong.');
    redirect('/ebloodbank/index.php?page=admin_requests');
}
// Approve + deduct stock
$db->prepare("UPDATE requests SET status='approved', approved_by=?, approved_at=NOW() WHERE id=?")->execute([$user['id'],$id]);
$db->prepare("UPDATE blood_stock SET quantity=quantity-? WHERE blood_type=? AND rhesus=? AND component=?")->execute([$req['quantity'],$req['blood_type'],$req['rhesus'],$req['component']]);
logActivity($user['id'],'REQUEST_APPROVE',"Request #$id disetujui, stok -{$req['quantity']} {$req['blood_type']}{$req['rhesus']}");
flashMessage('success',"Request disetujui! Stok berkurang {$req['quantity']} kantong.");
redirect('/ebloodbank/index.php?page=admin_requests');
