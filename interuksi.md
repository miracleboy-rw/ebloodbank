# 📄 VIBE CODING DOCUMENT

## E-BloodBank (Sistem Manajemen Donor Darah)

**Fullstack PHP Native (Tanpa Framework)**

---

## 🧠 KONSEP UTAMA

E-BloodBank adalah platform berbasis web untuk menghubungkan:

* Pendonor (User)
* PMI (Admin)
* Rumah Sakit (Admin RS)

### 🎯 Goals:

* Mempermudah donor darah
* Monitoring stok real-time
* Mempercepat request darah (terutama emergency)

---

## ⚙️ TECH STACK

* Backend: PHP Native
* Database: MySQL
* Frontend: HTML, CSS, JavaScript (Vanilla)
* Server: Apache / Nginx

---

## 🏗️ STRUKTUR PROJECT

```
/ebloodbank
│── config/
│   └── database.php
│
│── core/
│   ├── auth.php
│   ├── middleware.php
│   └── helper.php
│
│── modules/
│   ├── auth/
│   ├── donor/
│   ├── event/
│   ├── stock/
│   ├── request/
│   ├── screening/
│   └── report/
│
│── views/
│   ├── donor/
│   ├── admin_pmi/
│   ├── admin_rs/
│   └── layouts/
│
│── public/
│   ├── css/
│   ├── js/
│   └── img/
│
│── routes.php
│── index.php
```

---

## 🗄️ DATABASE DESIGN

### users

* id
* nik
* name
* email
* password
* role (donor | pmi | rs)
* blood_type (A, B, AB, O)
* rhesus (+/-)
* last_donation

---

### events

* id
* title
* location
* date
* start_time
* end_time
* quota

---

### bookings

* id
* user_id
* event_id
* booking_time
* qr_code
* status

---

### blood_stock

* id
* blood_type
* rhesus
* component
* quantity

---

### requests

* id
* hospital_id
* blood_type
* rhesus
* quantity
* urgency (normal/emergency)
* status

---

### screenings

* id
* booking_id
* hb
* tensi
* status (pass/fail)

---

## 🔥 CORE FEATURES

### 1. 🩸 Manajemen Stok Darah

* Real-time update
* Auto:

  * * stok saat donor selesai
  * * stok saat request disetujui

---

### 2. 📅 Event Donor

* Create, Read, Update, Delete
* Field:

  * lokasi
  * tanggal
  * waktu
  * kuota

---

### 3. 📲 Booking Antrean

* Pilih event
* Pilih jam
* Generate QR Code

---

### 4. 🧠 Eligibility System

Logic:

* Minimal jarak donor: 60 hari

Pseudo:

```
if (hari_ini >= last_donation + 60 hari)
    status = "BISA DONOR"
else
    status = "TUNGGU"
```

---

### 5. 🏥 Request Darah

* RS membuat request
* PMI approve/reject
* Stok otomatis berkurang

---

### 6. 🧪 Screening

* Input:

  * HB
  * Tensi
* Output:

  * Lolos / Tidak

---

## 🔄 USER FLOW

### 👤 Pendonor

1. Register/Login
2. Cek kelayakan
3. Cari event
4. Booking
5. Datang (scan QR)
6. Screening
7. Donor
8. Riwayat update

---

### 🩸 PMI

1. Login
2. Buat event
3. Scan QR
4. Input screening
5. Update stok
6. Handle request RS

---

### 🏥 Rumah Sakit

1. Login
2. Lihat stok
3. Request darah
4. Tracking
5. Konfirmasi selesai

---

## 🔐 ROLE & ACCESS

### 👤 Donor

* Lihat event
* Booking
* Riwayat
* Profil

---

### 🏥 Admin RS

* Request darah
* Tracking
* Data instansi

---

### 🩸 Admin PMI

* Full access:

  * Stok
  * Event
  * Request
  * User
  * Report

---

## 🧩 ROUTING SYSTEM

Contoh:

```
/login
/register
/events
/booking
/admin/events
/admin/stock
/rs/request
```

---

## 🔑 AUTH SYSTEM

* Session-based login
* Role checking

---

## 🎨 DASHBOARD

### Donor

* Status kelayakan
* Total donor
* Event list

---

### Admin RS

* Live stok
* Emergency button

---

### PMI

* Grafik stok
* Request masuk
* Event aktif

---

## 📊 REPORTING

* Export:

  * CSV / Excel
  * PDF

---

## 🚀 RULE VIBE CODING

* Fokus cepat jadi (MVP first)
* Jangan over-engineer
* Gunakan:

  * Function sederhana
  * File modular
* Pisahkan:

  * Logic (PHP)
  * View (HTML)

---

## 🧱 PRIORITAS DEVELOPMENT

1. Auth (Login/Register)
2. Dashboard per role
3. Event + Booking
4. Stok darah
5. Request RS
6. Screening
7. Reporting

---

## 💡 CATATAN

* Gunakan QR library sederhana (PHP)
* Gunakan prepared statement (PDO/MySQLi)
* Simpan log aktivitas penting

---

## ✅ OUTPUT TARGET

Website dengan fitur:

* Donor booking online
* Monitoring stok darah
* Request darah real-time
* Dashboard multi-role

---

END OF DOCUMENT