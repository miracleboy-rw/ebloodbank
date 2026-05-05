<?php
// Save event (create or edit)
requireRole('pmi');
if($_SERVER['REQUEST_METHOD']!=='POST'){redirect('/ebloodbank/index.php?page=admin_events');}
$db     = getDB();
$action = $_POST['action']??'create';
$user   = currentUser();

$data = [
    sanitize($_POST['title']??''),
    sanitize($_POST['description']??''),
    sanitize($_POST['location']??''),
    $_POST['date']??'',
    $_POST['start_time']??'',
    $_POST['end_time']??'',
    (int)($_POST['quota']??50),
];

if(empty($data[0])||empty($data[2])||empty($data[3])){
    flashMessage('error','Judul, lokasi, dan tanggal wajib diisi.');
    redirect('/ebloodbank/index.php?page='.($action==='edit'?'admin_event_edit&id='.((int)$_POST['id']):'admin_event_create'));
}

if($action==='edit'){
    $id = (int)($_POST['id']??0);
    $status = sanitize($_POST['status']??'active');
    $stmt = $db->prepare("UPDATE events SET title=?,description=?,location=?,date=?,start_time=?,end_time=?,quota=?,status=? WHERE id=?");
    $stmt->execute(array_merge($data,[$status,$id]));
    logActivity($user['id'],'EVENT_UPDATE',"Event #$id diupdate");
    flashMessage('success','Event berhasil diperbarui.');
} else {
    $stmt = $db->prepare("INSERT INTO events (title,description,location,date,start_time,end_time,quota,created_by) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute(array_merge($data,[$user['id']]));
    logActivity($user['id'],'EVENT_CREATE','Event baru: '.$data[0]);
    flashMessage('success','Event berhasil dibuat.');
}
redirect('/ebloodbank/index.php?page=admin_events');
