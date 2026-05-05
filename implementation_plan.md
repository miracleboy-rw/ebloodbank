# E-BloodBank — Implementasi Fullstack PHP Native

## Deskripsi

Website manajemen donor darah dengan 3 role pengguna (Donor, PMI, Rumah Sakit). Dibangun menggunakan PHP Native, MySQL, HTML/CSS/JS Vanilla. Tidak menggunakan framework apapun.

---

## Struktur File yang Akan Dibuat

```
d:\E-BLOODBANK\
│── config/
│   └── database.php           ← Koneksi PDO ke MySQL
│
│── core/
│   ├── auth.php               ← Fungsi login/logout/session
│   ├── middleware.php         ← Role guard
│   └── helper.php            ← Fungsi bantu umum
│
│── modules/
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   └── logout.php
│   ├── donor/
│   │   ├── dashboard.php
│   │   ├── profile.php
│   │   └── history.php
│   ├── event/
│   │   ├── list.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── booking/
│   │   ├── create.php
│   │   └── list.php
│   ├── stock/
│   │   ├── list.php
│   │   └── update.php
│   ├── request/
│   │   ├── create.php
│   │   ├── list.php
│   │   └── approve.php
│   ├── screening/
│   │   ├── input.php
│   │   └── result.php
│   └── report/
│       ├── export_csv.php
│       └── export_pdf.php
│
│── views/
│   ├── layouts/
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   ├── donor/
│   ├── admin_pmi/
│   └── admin_rs/
│
│── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── img/
│
│── routes.php                 ← Router sederhana
│── index.php                  ← Entry point
│── database.sql               ← Schema + seed data
```

---

## Database Tables

1. `users` — auth + profil donor
2. `events` — jadwal donor
3. `bookings` — reservasi donor + QR code
4. `blood_stock` — stok per golongan darah
5. `requests` — permintaan darah dari RS
6. `screenings` — hasil screening HB & tensi
7. `activity_logs` — log aktivitas penting

---

## Fitur Per Role

### Donor
- Register/Login
- Cek kelayakan (60 hari sejak donor terakhir)
- Lihat & booking event
- QR Code ticket
- Riwayat donor

### Admin PMI
- Manage event (CRUD)
- Scan QR & input screening
- Manage stok darah (auto update)
- Approve/reject request RS
- Report (CSV/PDF)
- Manage user

### Admin RS
- Lihat stok real-time
- Buat request darah (normal/emergency)
- Tracking status request
- Emergency button

---

## Deployment

- XAMPP (Apache + MySQL lokal)
- Copy folder ke `C:\xampp\htdocs\ebloodbank\`
- Import `database.sql` via phpMyAdmin
- Akses: `http://localhost/ebloodbank/`

---

## Urutan Pengerjaan

1. [x] Baca spesifikasi
2. [ ] Buat `database.sql`
3. [ ] `config/database.php`
4. [ ] `core/` (auth, middleware, helper)
5. [ ] `views/layouts/` (header, sidebar, footer)
6. [ ] `public/css/style.css` — UI premium
7. [ ] `index.php` + `routes.php`
8. [ ] Module Auth (login, register, logout)
9. [ ] Dashboard per role
10. [ ] Module Event (CRUD)
11. [ ] Module Booking + QR Code
12. [ ] Module Stock
13. [ ] Module Request Darah
14. [ ] Module Screening
15. [ ] Module Report
16. [ ] Deploy ke XAMPP
