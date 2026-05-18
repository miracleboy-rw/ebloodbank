# 📋 Dokumentasi Sistem E-BloodBank
**Sistem Manajemen Donor Darah Digital**

---

## A. Arsitektur Sistem

### Tipe Arsitektur: Monolitik (Monolithic)
E-BloodBank menggunakan arsitektur **monolitik** — seluruh komponen (routing, logika bisnis, akses data, dan tampilan) berada dalam satu codebase PHP yang sama.

### Teknologi yang Digunakan

| Layer | Teknologi |
|-------|-----------|
| **Frontend** | HTML5, Vanilla CSS, Vanilla JavaScript |
| **Backend** | PHP 8.x (Native, tanpa framework) |
| **Database** | MySQL/MariaDB via PDO |
| **Web Server** | Apache (XAMPP) |
| **Styling** | Custom CSS (~16.6KB) dengan design system sendiri |

### Alur Data: Input Pengguna → Database

```mermaid
flowchart LR
    A[Browser / User Input] -->|HTTP Request| B[index.php - Entry Point]
    B --> C[routes.php - Router]
    C --> D[core/middleware.php - Auth Check]
    D --> E[modules/*.php - Business Logic]
    E -->|PDO Prepared Statement| F[(MySQL Database)]
    F -->|Result Set| E
    E --> G[views/layouts/*.php - Render HTML]
    G -->|HTTP Response| A
```

**Alur detail:**
1. **User** mengisi form dan menekan submit di browser
2. **`index.php`** menerima request, memulai session, memuat `core/auth.php` dan `core/helper.php`
3. **`routes.php`** membaca parameter `?page=xxx`, memvalidasi akses (public vs protected), lalu me-route ke file module yang sesuai
4. **Middleware** (`core/middleware.php`) memverifikasi role pengguna via `guardRole()`, `guardPMI()`, `guardRS()`, atau `guardDonor()`
5. **Module** (misal `modules/booking/create.php`) memproses logika bisnis, menggunakan **PDO Prepared Statements** untuk query ke database
6. **Database** menyimpan data dan mengembalikan hasil
7. **View** (`views/layouts/header.php` + `footer.php`) membungkus output HTML yang dikirim kembali ke browser
8. Setiap aksi penting dicatat ke tabel `activity_logs` via fungsi `logActivity()`

---

## B. Fitur dan Model Utama

### Role 1: Admin PMI (`role = 'pmi'`)

| No | Fitur | Deskripsi |
|----|-------|-----------|
| 1 | Dashboard PMI | Ringkasan statistik: total donor, kantong darah, request menunggu, perlu screening |
| 2 | Kelola Event Donor | CRUD event donor: buat, edit, hapus, lihat daftar event |
| 3 | Manajemen Stok Darah | Lihat stok per golongan/komponen, update stok (tambah/kurangi/set langsung) |
| 4 | Input Screening | Input hasil pemeriksaan kesehatan donor (HB, tensi, suhu, nadi, berat badan) |
| 5 | Verifikasi Donor Selesai | Tandai booking sebagai "donated", otomatis tambah stok darah |
| 6 | Proses Request RS | Approve/reject permintaan darah dari RS, cek ketersediaan stok |
| 7 | Fulfill Request | Tandai request yang sudah terpenuhi |
| 8 | Manajemen User | Lihat, cari, filter semua pengguna dengan pagination |
| 9 | Laporan & Statistik | Grafik donasi bulanan, top donor, ringkasan request, stok |
| 10 | Export Data | Export CSV (donasi, stok, request) dan Export PDF |

### Role 2: Rumah Sakit (`role = 'rs'`)

| No | Fitur | Deskripsi |
|----|-------|-----------|
| 1 | Dashboard RS | Statistik request (menunggu, disetujui, terpenuhi), stok darah real-time |
| 2 | Lihat Stok Darah PMI | Melihat ketersediaan stok darah PMI secara real-time |
| 3 | Request Darah Normal | Mengajukan permintaan darah dengan data pasien (nama, umur, diagnosa) |
| 4 | Request Darah Emergency | Mengajukan permintaan darurat yang diprioritaskan PMI |
| 5 | Riwayat Request | Melihat semua histori request beserta statusnya |

### Role 3: Pendonor (`role = 'donor'`)

| No | Fitur | Deskripsi |
|----|-------|-----------|
| 1 | Dashboard Donor | Status eligibilitas, statistik donor pribadi, level donor (Bronze/Silver/Gold) |
| 2 | Lihat Event Donor | Browse event donor yang tersedia beserta kuota |
| 3 | Booking Event | Pesan tempat di event donor, mendapat QR Code unik |
| 4 | Booking Saya | Lihat daftar booking aktif dan statusnya |
| 5 | Batalkan Booking | Membatalkan booking yang masih pending/confirmed |
| 6 | Riwayat Donor | Melihat histori seluruh donasi yang pernah dilakukan |
| 7 | Profil | Edit data diri (nama, telepon, alamat, golongan darah) |
| 8 | Cek Ketersediaan Darah | Melihat ringkasan stok darah yang tersedia |

---

## C. Manajemen dan Keamanan Data

### Struktur Database

Sistem menggunakan **7 tabel utama** dengan relasi foreign key:

```mermaid
erDiagram
    users ||--o{ bookings : "has many"
    users ||--o{ events : "creates"
    users ||--o{ requests : "submits (RS)"
    users ||--o{ requests : "approves (PMI)"
    users ||--o{ screenings : "performs"
    users ||--o{ activity_logs : "generates"
    events ||--o{ bookings : "has many"
    bookings ||--o| screenings : "has one"

    users {
        int id PK
        varchar nik UK
        varchar name
        varchar email UK
        varchar password
        enum role "donor/pmi/rs"
        enum blood_type "A/B/AB/O"
        enum rhesus "+/-"
        varchar phone
        text address
        date last_donation
        int total_donations
        varchar hospital_name
        tinyint is_active
    }

    events {
        int id PK
        varchar title
        text description
        varchar location
        date date
        time start_time
        time end_time
        int quota
        int booked
        int created_by FK
        enum status "active/completed/cancelled"
    }

    bookings {
        int id PK
        int user_id FK
        int event_id FK
        datetime booking_time
        varchar qr_code UK
        enum status "pending/confirmed/screened/donated/cancelled/failed"
    }

    blood_stock {
        int id PK
        enum blood_type
        enum rhesus
        enum component "Whole Blood/PRC/Trombosit/FFP/WB"
        int quantity
        int min_stock
    }

    requests {
        int id PK
        int hospital_id FK
        enum blood_type
        enum rhesus
        enum component
        int quantity
        enum urgency "normal/emergency"
        enum status "pending/approved/rejected/fulfilled/cancelled"
        varchar patient_name
        int patient_age
        text diagnosis
        int approved_by FK
        datetime approved_at
    }

    screenings {
        int id PK
        int booking_id FK
        decimal hb
        int tensi_sistolik
        int tensi_diastolik
        decimal weight
        decimal temperature
        int pulse
        enum status "pass/fail"
        text fail_reason
        int screened_by FK
    }

    activity_logs {
        int id PK
        int user_id FK
        varchar action
        text description
        varchar ip_address
        timestamp created_at
    }
```

### Mekanisme Keamanan

| Aspek | Implementasi |
|-------|-------------|
| **Autentikasi** | Session-based authentication. Login via email + password. Password di-hash menggunakan `password_hash()` (bcrypt, `PASSWORD_DEFAULT`) |
| **Enkripsi Password** | Bcrypt hashing via PHP `password_hash()` / `password_verify()` |
| **RBAC** | Role-Based Access Control dengan 3 role (`donor`, `pmi`, `rs`). Setiap route dilindungi oleh `requireRole()` / `guardRole()` |
| **CSRF Protection** | Token CSRF menggunakan `bin2hex(random_bytes(32))`, diverifikasi via `verifyCSRF()` |
| **Input Sanitization** | Fungsi `sanitize()` menggunakan `htmlspecialchars()` + `strip_tags()` + `trim()` untuk mencegah XSS |
| **SQL Injection Prevention** | Semua query menggunakan PDO Prepared Statements |
| **Rate Limiting** | Session-based rate limiter (`rateLimit()`) untuk mencegah brute-force |
| **Audit Trail** | Semua aksi penting dicatat di tabel `activity_logs` (user_id, action, description, IP address, timestamp) |
| **Route Protection** | Route non-publik otomatis redirect ke login jika user belum terautentikasi |
| **Validasi Unik** | Email dan NIK divalidasi unik saat registrasi |
| **Account Status** | Field `is_active` untuk menonaktifkan akun tanpa menghapus data |

---

## D. Interoperabilitas dan Standar

### Status Saat Ini
Sistem E-BloodBank **belum mengimplementasikan** standar pertukaran data kesehatan seperti:
- **HL7** (Health Level Seven)
- **FHIR** (Fast Healthcare Interoperability Resources)
- **DICOM** (Digital Imaging and Communications in Medicine — tidak relevan untuk sistem bank darah)

### Rencana Integrasi

| Sistem Eksternal | Rencana |
|-----------------|---------|
| **SATUSEHAT** | Integrasi via FHIR API untuk sinkronisasi data donor dan stok darah ke platform nasional Kemenkes |
| **BPJS Kesehatan** | Verifikasi kepesertaan BPJS untuk pasien penerima darah, mempercepat proses administrasi |
| **Faskes Lain** | API REST untuk pertukaran data stok darah antar PMI cabang dan rumah sakit mitra |
| **NIK Verification** | Integrasi dengan Dukcapil untuk validasi NIK pendonor |

### Langkah Pengembangan Interoperabilitas
1. Membangun **REST API layer** untuk expose data stok darah dan request
2. Mengadopsi **FHIR Resources** (Patient, Specimen, ServiceRequest) untuk standarisasi format data
3. Implementasi **OAuth 2.0** untuk autentikasi antar sistem
4. Mapping data internal ke terminologi standar (SNOMED CT, LOINC)

---

## E. Peran Pengguna (User Role) & RBAC

### Daftar Pengguna

| Role | Pengguna | Deskripsi |
|------|----------|-----------|
| `pmi` | Admin PMI | Pengelola utama sistem, mengelola event, stok, screening, dan request |
| `rs` | Admin Rumah Sakit | Pihak RS yang meminta suplai darah dari PMI |
| `donor` | Pendonor | Masyarakat yang ingin mendonorkan darah |

### Matriks Hak Akses RBAC

| Fitur / Halaman | PMI | RS | Donor |
|-----------------|:---:|:--:|:-----:|
| Dashboard sendiri | ✅ | ✅ | ✅ |
| Kelola Event (CRUD) | ✅ | ❌ | ❌ |
| Lihat Event | ✅ | ❌ | ✅ |
| Booking Event | ❌ | ❌ | ✅ |
| Lihat Booking Sendiri | ❌ | ❌ | ✅ |
| Input Screening | ✅ | ❌ | ❌ |
| Kelola Stok Darah | ✅ | ❌ | ❌ |
| Lihat Stok Darah | ✅ | ✅ | ✅ |
| Request Darah | ❌ | ✅ | ❌ |
| Approve/Reject Request | ✅ | ❌ | ❌ |
| Riwayat Request | ✅ | ✅ | ❌ |
| Manajemen User | ✅ | ❌ | ❌ |
| Laporan & Export | ✅ | ❌ | ❌ |
| Edit Profil | ✅ | ❌ | ✅ |
| Riwayat Donor | ❌ | ❌ | ✅ |

### Implementasi RBAC dalam Kode

```php
// Middleware functions (core/middleware.php)
function guardRole(array $allowedRoles) {
    if (!isLoggedIn()) { redirect('/login'); }
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        redirect('/dashboard'); // Access denied
    }
}
function guardPMI()   { guardRole(['pmi']); }
function guardRS()    { guardRole(['rs']); }
function guardDonor() { guardRole(['donor']); }

// Digunakan di setiap module:
requireRole('pmi');   // Hanya PMI yang bisa akses
requireRole('rs');    // Hanya RS yang bisa akses
requireRole('donor'); // Hanya Donor yang bisa akses
```

---

## F. Infrastruktur dan Deployment

### Lingkungan Deployment

| Aspek | Detail |
|-------|--------|
| **Tipe Hosting** | Hosting Lokal (Local Development Server) |
| **Platform** | XAMPP on Windows |
| **Web Server** | Apache HTTP Server (bundled XAMPP) |
| **PHP Version** | PHP 8.x |
| **Database Server** | MariaDB/MySQL (bundled XAMPP) |
| **URL Akses** | `http://localhost/ebloodbank/` |

### Teknologi Server

```mermaid
flowchart TB
    subgraph XAMPP Stack
        A[Apache Web Server<br/>Port 80] --> B[PHP 8.x Engine]
        B --> C[PDO MySQL Driver]
        C --> D[(MariaDB/MySQL<br/>Port 3306)]
    end
    E[Browser Client] -->|HTTP| A
```

### Availability & Keandalan

| Mekanisme | Detail |
|-----------|--------|
| **Error Handling** | Try-catch pada koneksi database dengan pesan error yang informatif |
| **Singleton Pattern** | Koneksi database menggunakan static variable untuk menghindari koneksi berulang |
| **PDO Error Mode** | `ERRMODE_EXCEPTION` untuk menangkap semua error database |
| **Session Management** | Pengecekan `session_status()` sebelum `session_start()` untuk menghindari konflik |
| **Graceful Degradation** | Activity logging gagal secara silent (`catch` tanpa die) agar tidak mengganggu operasi utama |

### Rencana Scaling
Untuk production, direkomendasikan:
- Migrasi ke **VPS/Cloud** (AWS EC2, DigitalOcean, atau IDCloudHost)
- Menggunakan **Nginx** sebagai reverse proxy
- Setup **SSL/TLS** certificate untuk HTTPS
- Implementasi **database backup** otomatis
- Monitoring via **uptime checker**

---

## G. Alur Kerja dan Use Case Utama

### 1. Alur Pendaftaran Pendonor

```mermaid
flowchart TD
    A[Buka Halaman Register] --> B[Isi Form:<br/>Nama, Email, NIK, Password,<br/>Golongan Darah, Rhesus, Telepon]
    B --> C{Validasi Data}
    C -->|Email/NIK sudah terdaftar| D[Tampilkan Error]
    D --> B
    C -->|Valid| E[Hash Password dengan bcrypt]
    E --> F[Simpan ke tabel users<br/>role = 'donor']
    F --> G[Catat ke activity_logs]
    G --> H[Redirect ke Login]
    H --> I[Login dengan Email & Password]
    I --> J[Masuk Dashboard Donor]
```

### 2. Alur Pendaftaran Rumah Sakit

```mermaid
flowchart TD
    A[Buka Halaman Register] --> B[Pilih Role: Rumah Sakit]
    B --> C[Isi Form:<br/>Nama RS, Email, Password,<br/>Nama Institusi, Telepon]
    C --> D{Validasi}
    D -->|Valid| E[Simpan ke tabel users<br/>role = 'rs',<br/>hospital_name = nama RS]
    E --> F[Redirect ke Login]
    F --> G[Login → Dashboard RS]
```

### 3. Alur Pembuatan Event Donor oleh Admin PMI

```mermaid
flowchart TD
    A[Login sebagai PMI] --> B[Dashboard PMI]
    B --> C[Klik 'Buat Event Donor']
    C --> D[Isi Form Event:<br/>Judul, Deskripsi, Lokasi,<br/>Tanggal, Jam Mulai/Selesai, Kuota]
    D --> E{Validasi Input}
    E -->|Tidak lengkap| F[Tampilkan Error]
    F --> D
    E -->|Valid| G[Simpan ke tabel events<br/>status = 'active']
    G --> H[Catat activity_logs]
    H --> I[Event muncul di daftar<br/>& tersedia untuk donor]
```

### 4. Alur Donor Darah oleh Pendonor

```mermaid
flowchart TD
    A[Login sebagai Donor] --> B[Dashboard: Cek Eligibilitas]
    B --> C{Eligible?<br/>Jarak ≥ 60 hari}
    C -->|Tidak| D[Tampilkan countdown<br/>hari tersisa]
    C -->|Ya| E[Browse Event Donor]
    E --> F[Pilih Event & Klik Booking]
    F --> G{Kuota tersedia?}
    G -->|Tidak| H[Tampilkan 'Kuota Penuh']
    G -->|Ya| I{Sudah booking<br/>event ini?}
    I -->|Ya| J[Tampilkan Error]
    I -->|Tidak| K[Konfirmasi Booking]
    K --> L[Generate QR Code unik]
    L --> M[Simpan ke tabel bookings<br/>status = 'confirmed']
    M --> N[Update event.booked + 1]
    N --> O[Donor datang ke event<br/>dengan QR Code]
    O --> P[PMI: Input Screening]
    P --> Q{Hasil Screening}
    Q -->|Gagal: HB < 12.5<br/>atau tensi abnormal| R[Status = 'failed']
    Q -->|Lolos| S[Status = 'screened']
    S --> T[PMI: Tandai 'Donated']
    T --> U[Update user.total_donations + 1]
    U --> V[Update user.last_donation]
    V --> W[Update blood_stock + 1 kantong]
```

### 5. Alur Pengajuan Permintaan Darah oleh RS

```mermaid
flowchart TD
    A[Login sebagai RS] --> B[Dashboard RS]
    B --> C[Klik 'Request Darah']
    C --> D[Isi Form:<br/>Golongan Darah, Rhesus,<br/>Komponen, Jumlah, Urgensi]
    D --> E[Isi Data Pasien opsional:<br/>Nama, Umur, Diagnosa]
    E --> F[Submit Request]
    F --> G[Simpan ke tabel requests<br/>status = 'pending']
    G --> H[Catat activity_logs]
    H --> I[Request muncul di<br/>Dashboard PMI]
```

### 6. Alur Approve Request oleh Admin PMI

```mermaid
flowchart TD
    A[PMI: Lihat Request Masuk] --> B[Pilih Request untuk diproses]
    B --> C{Cek Stok Darah}
    C -->|Stok tidak cukup| D[Tampilkan Error:<br/>'Stok tidak mencukupi']
    C -->|Stok cukup| E[Approve Request]
    E --> F[Update request.status = 'approved']
    F --> G[Kurangi blood_stock.quantity]
    G --> H[Catat activity_logs]
    H --> I[RS melihat status 'Disetujui']
    I --> J[PMI: Tandai 'Fulfilled'<br/>setelah darah dikirim]
```

### 7. Alur Update Stok Darah oleh Admin PMI

```mermaid
flowchart TD
    A[PMI: Halaman Update Stok] --> B[Pilih Golongan & Komponen]
    B --> C[Pilih Operasi:<br/>Tambah / Kurangi / Set Langsung]
    C --> D[Masukkan Jumlah]
    D --> E[Submit]
    E --> F[Hitung stok baru]
    F --> G[Update blood_stock.quantity]
    G --> H[Catat activity_logs<br/>dengan detail perubahan]
```

---

## H. Desain Database

### Tabel-Tabel Utama dan Relasinya

| No | Tabel | Fungsi | Jumlah Kolom |
|----|-------|--------|:------------:|
| 1 | `users` | Menyimpan data semua pengguna (donor, PMI, RS) | 17 |
| 2 | `events` | Menyimpan data event donor darah | 12 |
| 3 | `bookings` | Menyimpan data booking/reservasi donor | 9 |
| 4 | `blood_stock` | Menyimpan stok darah per golongan dan komponen | 7 |
| 5 | `requests` | Menyimpan permintaan darah dari RS | 14 |
| 6 | `screenings` | Menyimpan hasil pemeriksaan kesehatan donor | 11 |
| 7 | `activity_logs` | Menyimpan log aktivitas pengguna (audit trail) | 5 |

### Relasi Antar Tabel

| Relasi | Tipe | Keterangan |
|--------|------|------------|
| `users` → `events` | One-to-Many | Satu PMI admin membuat banyak event (`created_by`) |
| `users` → `bookings` | One-to-Many | Satu donor memiliki banyak booking (`user_id`) |
| `events` → `bookings` | One-to-Many | Satu event memiliki banyak booking (`event_id`) |
| `bookings` → `screenings` | One-to-One | Satu booking memiliki satu hasil screening (`booking_id`) |
| `users` → `requests` | One-to-Many | Satu RS mengajukan banyak request (`hospital_id`) |
| `users` → `requests` | One-to-Many | Satu PMI menyetujui banyak request (`approved_by`) |
| `users` → `screenings` | One-to-Many | Satu PMI melakukan banyak screening (`screened_by`) |
| `users` → `activity_logs` | One-to-Many | Satu user menghasilkan banyak log (`user_id`) |

### Constraint & Index

| Tabel | Constraint |
|-------|-----------|
| `users` | UNIQUE: `email`, `nik` |
| `bookings` | UNIQUE: `qr_code` |
| `blood_stock` | UNIQUE KEY: `(blood_type, rhesus, component)` |
| Semua FK | ON DELETE CASCADE / SET NULL |
| Semua tabel | ENGINE = InnoDB (mendukung transaksi & foreign key) |

---

## I. Kesimpulan dan Pengembangan Lanjut

### ✅ Yang Sudah Dicapai

1. **Sistem Multi-Role Lengkap** — Tiga role (PMI, RS, Donor) dengan dashboard dan fitur masing-masing
2. **Manajemen Event Donor** — CRUD lengkap dengan sistem kuota dan booking
3. **Booking dengan QR Code** — Sistem reservasi dengan kode unik untuk setiap booking
4. **Screening Kesehatan** — Input dan validasi otomatis hasil pemeriksaan (HB, tensi, suhu, nadi)
5. **Manajemen Stok Darah** — Tracking stok per golongan darah dan komponen (Whole Blood, PRC, Trombosit, FFP)
6. **Request Darah RS** — Sistem permintaan dengan level urgensi (normal/emergency) dan workflow approval
7. **Auto Stock Management** — Stok otomatis bertambah saat donor selesai, berkurang saat request disetujui
8. **Audit Trail** — Pencatatan lengkap semua aktivitas pengguna dengan IP address
9. **Laporan & Export** — Dashboard statistik, export CSV dan PDF
10. **Keamanan** — Bcrypt hashing, PDO prepared statements, CSRF protection, RBAC, input sanitization, rate limiting
11. **UI/UX Modern** — Design system dengan sidebar navigation, responsive layout, status badges, progress bars
12. **Eligibility Check** — Pengecekan otomatis kelayakan donor (jarak minimal 60 hari)
13. **Level/Gamifikasi Donor** — Sistem level Bronze/Silver/Gold berdasarkan jumlah donasi

### 🔮 Rencana Fitur ke Depan

| Prioritas | Fitur | Deskripsi |
|:---------:|-------|-----------|
| 🔴 Tinggi | Notifikasi Real-time | Push notification untuk status request, reminder event |
| 🔴 Tinggi | API REST | Membangun API layer untuk integrasi sistem eksternal |
| 🟡 Sedang | Integrasi SATUSEHAT | Koneksi ke platform nasional Kemenkes via FHIR |
| 🟡 Sedang | QR Code Scanner | Scan QR code saat donor datang ke event |
| 🟡 Sedang | Verifikasi Email | OTP/email verification saat registrasi |
| 🟡 Sedang | Two-Factor Auth | Keamanan tambahan untuk akun admin |
| 🟢 Rendah | Mobile App | Aplikasi mobile untuk donor (React Native/Flutter) |
| 🟢 Rendah | Chat/Messaging | Komunikasi langsung antara RS dan PMI |
| 🟢 Rendah | Donor Rewards | Sistem reward point dan achievement |
| 🟢 Rendah | Blood Drive Map | Peta interaktif lokasi event donor |
| 🟢 Rendah | Integrasi BPJS | Verifikasi kepesertaan BPJS pasien |
| 🟢 Rendah | Multi-Cabang PMI | Dukungan untuk banyak cabang PMI |

---

*Dokumentasi ini dibuat berdasarkan analisis kode sumber E-BloodBank.*
*Terakhir diperbarui: 18 Mei 2026*
