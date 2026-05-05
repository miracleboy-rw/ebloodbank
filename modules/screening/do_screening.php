<?php
requireRole('pmi');
if($_SERVER['REQUEST_METHOD']!=='POST'){redirect('/ebloodbank/index.php?page=admin_screening');}
$db   = getDB();
$user = currentUser();
$bid  = (int)($_POST['booking_id']??0);
$hb   = (float)($_POST['hb']??0);
$sis  = (int)($_POST['tensi_sistolik']??0);
$dia  = (int)($_POST['tensi_diastolik']??0);
$wgt  = $_POST['weight']   ? (float)$_POST['weight']  : null;
$tmp  = $_POST['temperature'] ? (float)$_POST['temperature'] : null;
$pls  = $_POST['pulse']    ? (int)$_POST['pulse']     : null;
$fail = sanitize($_POST['fail_reason']??'');

// Auto eligibility check
$pass = ($hb>=12.5) && ($sis>=90&&$sis<=160) && ($dia>=60&&$dia<=100);
if($fail) $pass=false;
$status = $pass?'pass':'fail';
if(!$pass && !$fail) $fail='Tidak memenuhi standar HB atau tekanan darah';

$ins = $db->prepare("INSERT INTO screenings (booking_id,hb,tensi_sistolik,tensi_diastolik,weight,temperature,pulse,status,fail_reason,screened_by) VALUES (?,?,?,?,?,?,?,?,?,?)");
$ins->execute([$bid,$hb,$sis,$dia,$wgt,$tmp,$pls,$status,$fail,$user['id']]);

$newStatus = $pass?'screened':'failed';
$db->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$newStatus,$bid]);

logActivity($user['id'],'SCREENING',"Booking#$bid screening: $status, HB=$hb, Tensi=$sis/$dia");
$msg = $pass ? '✅ Screening LOLOS! Donor dapat dilanjutkan.' : '❌ Screening GAGAL: '.$fail;
flashMessage($pass?'success':'error',$msg);
redirect('/ebloodbank/index.php?page=admin_screening');
