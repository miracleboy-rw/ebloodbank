<?php
// ============================================
// Helper Functions — E-BloodBank
// ============================================

require_once __DIR__ . '/../config/database.php';

function logActivity($userId, $action, $description = '') {
    try {
        $db = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $description, $ip]);
    } catch (Exception $e) {
        // Fail silently
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateQRCode($data) {
    // Unique QR token
    return strtoupper(substr(md5($data . time() . rand(1000, 9999)), 0, 12));
}

function isEligibleDonor($lastDonation) {
    if (!$lastDonation) return true;
    $last = new DateTime($lastDonation);
    $now  = new DateTime();
    $diff = $last->diff($now);
    return $diff->days >= 60;
}

function daysUntilEligible($lastDonation) {
    if (!$lastDonation) return 0;
    $last = new DateTime($lastDonation);
    $eligible = clone $last;
    $eligible->modify('+60 days');
    $now = new DateTime();
    if ($now >= $eligible) return 0;
    $diff = $now->diff($eligible);
    return $diff->days;
}

function getBloodTypeLabel($type, $rhesus) {
    return $type . $rhesus;
}

function getStockStatus($qty, $min) {
    if ($qty <= 0)          return ['label' => 'Habis', 'class' => 'badge-danger'];
    if ($qty <= $min)       return ['label' => 'Kritis', 'class' => 'badge-warning'];
    if ($qty <= $min * 2)   return ['label' => 'Rendah', 'class' => 'badge-info'];
    return                         ['label' => 'Aman', 'class' => 'badge-success'];
}

function formatDate($date, $format = 'd M Y') {
    if (!$date) return '-';
    return (new DateTime($date))->format($format);
}

function formatDateTime($dt) {
    if (!$dt) return '-';
    return (new DateTime($dt))->format('d M Y, H:i');
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash() {
    $flash = getFlash();
    if (!$flash) return '';
    $icon = $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'error' ? '❌' : 'ℹ️');
    return "<div class='alert alert-{$flash['type']}'>{$icon} {$flash['message']}</div>";
}

function getUrgencyBadge($urgency) {
    return $urgency === 'emergency'
        ? "<span class='badge badge-danger'>🚨 Emergency</span>"
        : "<span class='badge badge-info'>📋 Normal</span>";
}

function getStatusBadge($status) {
    $map = [
        'pending'   => ['label' => 'Menunggu',   'class' => 'badge-warning'],
        'approved'  => ['label' => 'Disetujui',  'class' => 'badge-success'],
        'rejected'  => ['label' => 'Ditolak',    'class' => 'badge-danger'],
        'fulfilled' => ['label' => 'Terpenuhi',  'class' => 'badge-success'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'badge-secondary'],
        'confirmed' => ['label' => 'Dikonfirmasi','class' => 'badge-info'],
        'screened'  => ['label' => 'Screening',  'class' => 'badge-primary'],
        'donated'   => ['label' => 'Selesai Donor','class' => 'badge-success'],
        'failed'    => ['label' => 'Gagal',      'class' => 'badge-danger'],
        'active'    => ['label' => 'Aktif',      'class' => 'badge-success'],
        'completed' => ['label' => 'Selesai',    'class' => 'badge-secondary'],
        'pass'      => ['label' => 'Lolos',      'class' => 'badge-success'],
        'fail'      => ['label' => 'Tidak Lolos','class' => 'badge-danger'],
    ];
    $info = $map[$status] ?? ['label' => $status, 'class' => 'badge-secondary'];
    return "<span class='badge {$info['class']}'>{$info['label']}</span>";
}

function paginateQuery($sql, $params, $page, $perPage = 10) {
    $db = getDB();
    $countSql = "SELECT COUNT(*) FROM ($sql) AS t";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("$sql LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    return [
        'data'       => $stmt->fetchAll(),
        'total'      => $total,
        'page'       => $page,
        'perPage'    => $perPage,
        'totalPages' => $totalPages,
    ];
}
