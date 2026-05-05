<?php
requireRole('pmi');
$type = sanitize($_GET['type']??'donations');
$db   = getDB();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="ebloodbank_'.$type.'_'.date('Ymd').'.csv"');
header('Pragma: no-cache');

$out = fopen('php://output','w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

if($type==='stock'){
    fputcsv($out,['Golongan Darah','Rhesus','Komponen','Stok','Min Stok','Status','Terakhir Update']);
    $rows=$db->query("SELECT * FROM blood_stock ORDER BY blood_type,rhesus,component")->fetchAll();
    foreach($rows as $r){
        $st=getStockStatus($r['quantity'],$r['min_stock']);
        fputcsv($out,[$r['blood_type'],$r['rhesus'],$r['component'],$r['quantity'],$r['min_stock'],$st['label'],date('d/m/Y H:i',strtotime($r['updated_at']))]);
    }
} elseif($type==='requests'){
    fputcsv($out,['Rumah Sakit','Gol. Darah','Komponen','Jumlah','Urgensi','Pasien','Status','Tanggal']);
    $rows=$db->query("SELECT r.*,u.name as rs FROM requests r JOIN users u ON r.hospital_id=u.id ORDER BY r.created_at DESC")->fetchAll();
    foreach($rows as $r){
        fputcsv($out,[$r['rs'],$r['blood_type'].$r['rhesus'],$r['component'],$r['quantity'],$r['urgency'],$r['patient_name']??'-',$r['status'],date('d/m/Y',strtotime($r['created_at']))]);
    }
} else { // donations
    fputcsv($out,['Donor','NIK','Gol. Darah','Event','Tanggal Event','HB','Tensi Sistolik','Tensi Diastolik','Screening','Status Booking']);
    $rows=$db->query("SELECT b.*,u.name as donor,u.nik,u.blood_type,u.rhesus,e.title,e.date as ev_date,s.hb,s.tensi_sistolik,s.tensi_diastolik,s.status as scr FROM bookings b JOIN users u ON b.user_id=u.id JOIN events e ON b.event_id=e.id LEFT JOIN screenings s ON s.booking_id=b.id ORDER BY ev_date DESC")->fetchAll();
    foreach($rows as $r){
        fputcsv($out,[$r['donor'],$r['nik']??'',$r['blood_type'].$r['rhesus'],$r['title'],date('d/m/Y',strtotime($r['ev_date'])),$r['hb']??'-',$r['tensi_sistolik']??'-',$r['tensi_diastolik']??'-',$r['scr']??'-',$r['status']]);
    }
}
fclose($out);
exit;
