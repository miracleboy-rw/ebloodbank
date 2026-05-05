<?php
// ============================================
// Router — E-BloodBank
// ============================================

require_once __DIR__ . '/core/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$page = preg_replace('/[^a-zA-Z0-9_\/\-]/', '', $page);

// ============================================
// Public Routes (no auth needed)
// ============================================
$publicRoutes = ['home', 'login', 'register', 'do_login', 'do_register'];

if (!in_array($page, $publicRoutes) && !isLoggedIn()) {
    flashMessage('error', 'Silakan login terlebih dahulu.');
    redirect('/ebloodbank/index.php?page=login');
}

// ============================================
// Route Map
// ============================================
$routes = [
    // Public
    'home'            => 'modules/auth/home.php',
    'login'           => 'modules/auth/login.php',
    'register'        => 'modules/auth/register.php',
    'do_login'        => 'modules/auth/do_login.php',
    'do_register'     => 'modules/auth/do_register.php',
    'logout'          => 'modules/auth/logout.php',

    // Donor
    'dashboard'       => 'modules/donor/dashboard.php',
    'profile'         => 'modules/donor/profile.php',
    'do_profile'      => 'modules/donor/do_profile.php',
    'history'         => 'modules/donor/history.php',

    // Events (public + PMI manage)
    'events'          => 'modules/event/list.php',
    'event_detail'    => 'modules/event/detail.php',
    'event_detail_id' => 'modules/event/detail.php',

    // Booking
    'booking'         => 'modules/booking/create.php',
    'my_bookings'     => 'modules/booking/list.php',
    'booking_cancel'  => 'modules/booking/cancel.php',

    // PMI Admin — Events
    'admin_events'    => 'modules/event/admin_list.php',
    'admin_event_create' => 'modules/event/create.php',
    'admin_event_edit'   => 'modules/event/edit.php',
    'admin_event_delete' => 'modules/event/delete.php',
    'admin_event_save'   => 'modules/event/save.php',

    // PMI Admin — Stock
    'admin_stock'     => 'modules/stock/list.php',
    'admin_stock_update' => 'modules/stock/update.php',

    // PMI Admin — Screening
    'admin_screening' => 'modules/screening/list.php',
    'screening_input' => 'modules/screening/input.php',
    'do_screening'    => 'modules/screening/do_screening.php',

    // PMI Admin — Requests
    'admin_requests'  => 'modules/request/admin_list.php',
    'request_approve' => 'modules/request/approve.php',
    'request_reject'  => 'modules/request/reject.php',

    // PMI Admin — Users
    'admin_users'     => 'modules/admin/users.php',
    'admin_dashboard' => 'modules/admin/dashboard.php',

    // PMI Admin — Reports
    'reports'         => 'modules/report/index.php',
    'export_csv'      => 'modules/report/export_csv.php',
    'export_pdf'      => 'modules/report/export_pdf.php',

    // RS Role
    'rs_dashboard'    => 'modules/rs/dashboard.php',
    'rs_stock'        => 'modules/rs/stock.php',
    'rs_request'      => 'modules/rs/request.php',
    'do_rs_request'   => 'modules/rs/do_request.php',
    'rs_my_requests'  => 'modules/rs/my_requests.php',
];

// Role-based redirect after login
if ($page === 'dashboard') {
    if (hasRole('pmi')) {
        redirect('/ebloodbank/index.php?page=admin_dashboard');
    } elseif (hasRole('rs')) {
        redirect('/ebloodbank/index.php?page=rs_dashboard');
    }
}

// Resolve route file
if (isset($routes[$page])) {
    $file = __DIR__ . '/' . $routes[$page];
    if (file_exists($file)) {
        include $file;
        exit;
    }
}

// 404 fallback
http_response_code(404);
include __DIR__ . '/views/layouts/404.php';
