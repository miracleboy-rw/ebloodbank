# desain.md

# Panduan Revisi UI E-BloodBank

Dokumen ini digunakan sebagai acuan revisi tampilan UI/UX website E-BloodBank agar memiliki gaya visual modern seperti referensi dashboard Donezo, tanpa mengubah fungsi tombol, alur fitur, struktur data, endpoint, query parameter, dan logic backend yang sudah berjalan.

## 1. Tujuan Revisi

Revisi UI dilakukan untuk membuat tampilan E-BloodBank lebih bersih, modern, ringan, dan profesional. Fokus perubahan hanya berada pada sisi antarmuka, yaitu layout, warna, kartu statistik, sidebar, header, dashboard card, button style, spacing, dan responsivitas.

Fungsi yang sudah ada tidak boleh diubah. Seluruh tombol, link, form, proses submit, redirect halaman, session login, role user, validasi, dan integrasi data tetap menggunakan logic lama.

## 2. Prinsip Utama Desain

1. UI baru harus mengikuti gaya clean dashboard seperti referensi: dominan putih, abu-abu muda, card rounded, shadow halus, sidebar minimalis, dan aksen warna hijau.
2. Identitas E-BloodBank tetap dipertahankan melalui elemen darah, PMI, donor darah, rumah sakit, dan pendonor.
3. Warna merah tetap boleh dipakai sebagai aksen identitas donor darah, tetapi tidak mendominasi seluruh dashboard.
4. Dashboard tidak boleh terasa terlalu penuh. Setiap bagian harus memiliki ruang kosong yang cukup.
5. Semua fitur lama tetap ada dan tetap bekerja seperti sebelumnya.
6. Perubahan hanya bersifat visual dan perapihan struktur tampilan.

## 3. Arah Visual yang Diinginkan

Referensi UI yang digunakan memiliki ciri:

- Background utama putih ke abu-abu sangat muda.
- Sidebar kiri dengan warna terang atau hijau gelap minimalis.
- Card statistik besar dengan angka dominan.
- Rounded corner besar pada card dan tombol.
- Shadow tipis, tidak terlalu gelap.
- Ikon kecil di setiap menu dan card.
- Header sederhana berisi judul halaman, pencarian atau informasi user.
- Button utama berbentuk pill atau rounded rectangle.
- Warna utama hijau tua, hijau medium, putih, abu-abu muda, dan sedikit merah sebagai aksen darah.

## 4. Palet Warna Rekomendasi

Gunakan palet berikut agar tampilan mendekati referensi, tetapi tetap sesuai tema E-BloodBank.

### Warna utama

- Hijau tua: #0F4D2E
- Hijau utama: #137A43
- Hijau medium: #1B9A59
- Hijau muda: #EAF7EF
- Putih: #FFFFFF
- Abu-abu background: #F5F7F6
- Abu-abu border: #E5E7EB
- Abu-abu teks sekunder: #6B7280
- Teks utama: #111827

### Warna aksen darah

- Merah utama: #D62828
- Merah soft: #FDECEC
- Pink darah: #E83E6F

### Warna status

- Aman: #16A34A
- Rendah: #F97316
- Menunggu: #D97706
- Ditolak / kritis: #DC2626
- Informasi: #0284C7

## 5. Tipografi

Gunakan font modern yang mudah dibaca. Rekomendasi:

- Inter
- Poppins
- Plus Jakarta Sans
- Manrope

Jika tidak ingin menambah dependensi, gunakan fallback:

```css
font-family: 'Inter', 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
```

Ukuran teks rekomendasi:

- Judul halaman: 28–34 px
- Subjudul: 14–16 px
- Angka statistik: 36–48 px
- Label card: 14–16 px
- Menu sidebar: 14–15 px
- Teks kecil: 12–13 px

## 6. Struktur Layout Umum Dashboard

Gunakan struktur dasar berikut untuk seluruh dashboard role, yaitu PMI, Rumah Sakit, dan Pendonor.

```text
<body>
  <div class="app-shell">
    <aside class="sidebar">
      Logo E-BloodBank
      Menu utama sesuai role
      Menu akun
      Logout
    </aside>

    <main class="main-content">
      <header class="topbar">
        Judul halaman
        Search atau shortcut opsional
        Profil user
      </header>

      <section class="page-content">
        Statistik utama
        Konten utama sesuai role
        Aksi cepat
        Tabel atau daftar data
      </section>
    </main>
  </div>
</body>
```

Catatan penting: struktur logic lama tetap dipertahankan. Jika website saat ini menggunakan `index.php?page=...`, maka sistem routing tersebut tidak perlu diubah. Revisi hanya dilakukan pada file layout, CSS, dan markup tampilan.

## 7. Sidebar

Sidebar dibuat lebih ringan dan modern seperti referensi.

### Karakter visual

- Lebar desktop: 260–280 px.
- Background: putih atau hijau sangat gelap.
- Jika ingin sangat mirip referensi, gunakan background putih dengan ikon hijau.
- Jika ingin tetap mempertahankan karakter E-BloodBank saat ini, gunakan hijau gelap dengan card menu yang lebih soft.
- Active menu memakai indikator warna hijau atau merah tipis di sisi kiri.
- Menu tidak perlu terlalu kontras seperti desain lama.
- Jarak antar menu dibuat lebih lega.

### Struktur sidebar PMI

```text
Logo E-BloodBank
Sistem Donor Darah Digital

PMI Admin
- Dashboard
- Event Donor
- Stok Darah
- Screening

Permintaan
- Request RS
- Data User
- Laporan

Akun
- Profil
- Logout
```

### Struktur sidebar Rumah Sakit

```text
Logo E-BloodBank
Sistem Donor Darah Digital

Rumah Sakit
- Dashboard
- Request Darah
- Status Request
- Riwayat Permintaan

Akun
- Profil
- Logout
```

### Struktur sidebar Pendonor

```text
Logo E-BloodBank
Sistem Donor Darah Digital

Pendonor
- Dashboard
- Jadwal Donor
- Riwayat Donor
- Screening

Akun
- Profil
- Logout
```

Jika menu aktual berbeda, gunakan menu aktual website. Jangan menambah atau menghapus menu tanpa kebutuhan.

## 8. Header / Topbar

Topbar pada dashboard dibuat lebih bersih.

### Elemen topbar

- Judul halaman di kiri.
- Deskripsi singkat di bawah judul jika diperlukan.
- Search bar opsional jika memang sudah ada fitur pencarian.
- Ikon notifikasi opsional jika sudah ada fitur notifikasi.
- Profil user di kanan.

Contoh tampilan:

```text
Dashboard PMI
Pantau stok darah, request rumah sakit, screening, dan aktivitas donor.

[Search data...]                    [Notifikasi] [Admin PMI Lampung]
```

Jika fitur search atau notifikasi belum ada, jangan membuat fungsi baru. Boleh hanya menampilkan informasi user.

## 9. Landing Page / Home

Landing page saat ini sudah memiliki hero section gelap dengan aksen merah. Pada revisi, landing page bisa dibuat lebih menyatu dengan gaya referensi tanpa menghilangkan identitas donor darah.

### Arah desain landing page

- Background menggunakan kombinasi putih, hijau muda, dan sedikit merah sebagai aksen.
- Hero section dibuat lebih clean, tidak terlalu gelap penuh.
- Gunakan card statistik seperti referensi.
- Tombol utama tetap terlihat jelas.
- Visual tidak perlu ramai.

### Struktur landing page rekomendasi

```text
Navbar
- Logo E-BloodBank
- Home
- Tentang
- Fitur
- Masuk
- Daftar Donor

Hero Section
- Badge: Platform Donor Darah Digital
- Headline: Selamatkan Nyawa dengan Setetes Darah
- Deskripsi singkat
- CTA: Daftar Donor
- CTA sekunder: Masuk Akun
- Card statistik: Pendonor aktif, event per tahun, darah tersedia, tingkat kepuasan

Section Fitur
- Manajemen stok darah
- Request darah rumah sakit
- Screening pendonor
- Event donor

Section Role
- Untuk PMI
- Untuk Rumah Sakit
- Untuk Pendonor

Footer
- Informasi sistem
- Kontak
- Hak cipta
```

## 10. Dashboard PMI

Dashboard PMI menjadi pusat kendali utama. Tampilan dibuat seperti dashboard referensi dengan statistik, stok darah, dan aksi cepat.

### Komponen utama

1. Card statistik atas
2. Stok darah whole blood
3. Aksi cepat
4. Request rumah sakit terbaru
5. Event donor terbaru
6. Ringkasan screening
7. Laporan atau ringkasan aktivitas

### Card statistik atas

Gunakan 4 card utama seperti yang sudah ada:

```text
Total Donor Terdaftar
Total Kantong Darah
Request Menunggu
Perlu Screening
```

Desain card:

- Card pertama boleh memakai background hijau tua solid seperti referensi.
- Card lain memakai background putih.
- Angka besar di kiri.
- Ikon kecil di atas atau kanan.
- Teks keterangan kecil di bawah.
- Gunakan rounded 20–24 px.

Contoh visual:

```text
[Total Donor Terdaftar]
1
Total donor aktif dalam sistem

[Total Kantong Darah]
7
Kantong darah tersedia

[Request Menunggu]
1
Permintaan belum diproses

[Perlu Screening]
0
Pendonor perlu pemeriksaan
```

### Stok darah

Stok darah tetap menjadi bagian utama. Card stok darah dibuat lebih rapi.

Desain stok:

- Gunakan grid 4 kolom di desktop.
- Setiap golongan darah menjadi card kecil.
- Card status aman memakai border hijau dan background hijau muda.
- Card status rendah memakai border oranye dan background krem muda.
- Angka kantong dibuat besar.
- Tombol `Kelola Stok` tetap ada dan tetap memakai link/fungsi lama.

Format card stok:

```text
A+
42
Aman
kantong
```

### Aksi cepat

Aksi cepat dibuat menjadi card seperti referensi.

Tombol yang sudah ada tetap dipertahankan:

```text
Buat Event Donor
Input Screening
Proses Request RS
Update Stok
Laporan
```

Desain tombol:

- Button full width dalam card.
- Rounded 12–16 px.
- Warna berbeda sesuai fungsi, tetapi tetap soft.
- Hindari warna terlalu menyala.

Rekomendasi warna:

- Buat Event Donor: merah utama
- Input Screening: biru informasi
- Proses Request RS: oranye
- Update Stok: hijau
- Laporan: abu-abu terang

## 11. Dashboard Rumah Sakit

Dashboard rumah sakit harus fokus pada kebutuhan request darah.

### Komponen utama

1. Statistik request
2. Form atau tombol request darah
3. Status permintaan terbaru
4. Riwayat permintaan
5. Informasi stok darah tersedia jika memang sudah ada

### Card statistik rekomendasi

```text
Total Request
Menunggu Diproses
Request Disetujui
Request Ditolak
```

### Area utama

Gunakan dua kolom desktop:

```text
Kolom kiri:
- Ringkasan request darah
- Status request terbaru

Kolom kanan:
- Aksi cepat
- Informasi akun rumah sakit
```

Fungsi tombol seperti `Ajukan Request`, `Lihat Status`, atau `Riwayat` tetap menggunakan route lama.

## 12. Dashboard Pendonor

Dashboard pendonor harus terasa personal dan mudah dipahami.

### Komponen utama

1. Status donor pengguna
2. Jadwal donor terdekat
3. Riwayat donor
4. Status screening
5. Event donor tersedia
6. Profil singkat

### Card statistik rekomendasi

```text
Total Donor Saya
Jadwal Terdekat
Status Screening
Event Tersedia
```

### Tampilan personal

Tambahkan card sambutan:

```text
Halo, [Nama Pendonor]
Terima kasih sudah berkontribusi dalam donor darah. Pantau jadwal, riwayat, dan status screening kamu di sini.
```

Jangan menambah fitur baru jika datanya belum tersedia. Jika data belum ada, tampilkan state kosong yang rapi.

## 13. Komponen UI Global

### Card

Gunakan standar card berikut:

```css
.card-modern {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 22px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  padding: 24px;
}
```

### Button

```css
.btn-modern {
  border-radius: 14px;
  padding: 12px 18px;
  font-weight: 600;
  border: none;
  transition: all 0.2s ease;
}

.btn-modern:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
}
```

### Button utama

```css
.btn-primary-modern {
  background: #0F4D2E;
  color: #ffffff;
}
```

### Button aksen donor darah

```css
.btn-blood {
  background: #D62828;
  color: #ffffff;
}
```

### Badge status

```css
.badge-safe {
  background: #EAF7EF;
  color: #137A43;
}

.badge-low {
  background: #FFF4E6;
  color: #F97316;
}

.badge-pending {
  background: #FEF3C7;
  color: #D97706;
}

.badge-danger {
  background: #FDECEC;
  color: #DC2626;
}
```

## 14. Grid dan Spacing

Gunakan spacing konsisten agar tampilan tidak sesak.

### Rekomendasi

- Padding halaman desktop: 28–32 px
- Gap antar card: 20–24 px
- Padding card: 22–28 px
- Border radius card: 20–24 px
- Border radius button: 12–16 px
- Jarak antar menu sidebar: 8–12 px

### Layout dashboard desktop

```text
Statistik utama: 4 kolom
Konten tengah: 2 kolom
Tabel/data: full width
```

### Layout tablet

```text
Statistik utama: 2 kolom
Konten tengah: 1–2 kolom
Sidebar: bisa tetap atau berubah menjadi drawer
```

### Layout mobile

```text
Sidebar: hamburger/drawer
Statistik utama: 1 kolom
Card stok darah: 2 kolom atau 1 kolom
Aksi cepat: 1 kolom
```

## 15. Tabel dan Data List

Tabel yang lama sebaiknya dirapikan agar sesuai gaya dashboard.

### Aturan tabel

- Header tabel memakai background abu-abu muda.
- Border sangat tipis.
- Gunakan rounded pada wrapper tabel.
- Tombol aksi di dalam tabel dibuat kecil dan konsisten.
- Status memakai badge, bukan teks polos.

Contoh struktur:

```text
Card: Request Rumah Sakit Terbaru
-------------------------------------------------
Tanggal | Rumah Sakit | Golongan | Jumlah | Status | Aksi
```

## 16. Empty State

Jika data kosong, jangan tampilkan area kosong polos. Gunakan empty state.

Contoh:

```text
Belum ada request darah
Permintaan dari rumah sakit akan tampil di sini setelah dibuat.
```

Tambahkan ikon kecil atau ilustrasi sederhana bila diperlukan.

## 17. Responsivitas

Website harus nyaman digunakan pada desktop, tablet, dan smartphone.

### Desktop

- Sidebar selalu tampil.
- Card statistik 4 kolom.
- Konten utama memakai grid.

### Tablet

- Sidebar boleh mengecil.
- Card statistik 2 kolom.
- Konten utama 1–2 kolom.

### Mobile

- Sidebar berubah menjadi drawer atau hamburger menu.
- Topbar lebih ringkas.
- Card menjadi 1 kolom.
- Tombol aksi full width.
- Tabel bisa memakai horizontal scroll.

## 18. Aturan Penting agar Fungsi Tidak Berubah

Saat implementasi, developer atau AI coding wajib mengikuti aturan berikut:

1. Jangan mengubah nama file PHP utama jika tidak diperlukan.
2. Jangan mengubah `index.php?page=...` jika routing saat ini sudah menggunakan query parameter tersebut.
3. Jangan mengubah name attribute pada form.
4. Jangan mengubah method form, baik GET maupun POST.
5. Jangan mengubah action form kecuali hanya membungkus tampilan.
6. Jangan mengubah id atau class yang dipakai JavaScript lama untuk event handler.
7. Jangan menghapus tombol yang sudah ada.
8. Jangan mengganti link tombol ke route baru.
9. Jangan mengubah query database.
10. Jangan mengubah session role dan validasi akses.
11. Jangan menambah fitur backend baru hanya karena ada di desain referensi.
12. Perubahan hanya pada HTML wrapper, class styling, CSS, layout, dan komponen visual.

## 19. Mapping Tampilan Lama ke Tampilan Baru

### Dashboard lama

- Sidebar gelap dengan menu vertikal.
- Konten utama menggunakan card statistik sederhana.
- Stok darah sudah menggunakan card per golongan darah.
- Aksi cepat sudah tersedia.
- Warna dominan merah, biru, oranye, hijau.

### Dashboard baru

- Sidebar dibuat lebih modern, lebih lega, dan lebih halus.
- Background utama dibuat abu-abu muda.
- Card statistik dibuat rounded besar seperti referensi.
- Salah satu card utama memakai background hijau tua solid.
- Card stok darah dibuat lebih clean dan konsisten.
- Aksi cepat tetap ada, tetapi tampilannya dibuat seperti panel dashboard modern.
- Header dibuat lebih minimalis dengan profil user yang rapi.

## 20. Rekomendasi Implementasi Bertahap

### Tahap 1: Global Style

- Buat file CSS baru, misalnya `assets/css/modern-ui.css`.
- Tambahkan variabel warna global.
- Tambahkan style card, button, badge, sidebar, dan topbar.
- Jangan hapus CSS lama sebelum UI baru aman.

### Tahap 2: Layout Dashboard

- Revisi wrapper utama dashboard.
- Revisi sidebar.
- Revisi topbar.
- Pastikan menu dan link tetap sama.

### Tahap 3: Dashboard PMI

- Revisi card statistik.
- Revisi stok darah.
- Revisi aksi cepat.
- Revisi tabel/list jika ada.

### Tahap 4: Dashboard Rumah Sakit

- Terapkan layout yang sama.
- Sesuaikan komponen dengan kebutuhan request darah.

### Tahap 5: Dashboard Pendonor

- Terapkan layout yang sama.
- Fokus pada status donor, jadwal, riwayat, dan screening.

### Tahap 6: Landing Page

- Revisi hero section.
- Tambahkan card statistik visual.
- Rapikan CTA.
- Sesuaikan warna agar menyatu dengan dashboard.

### Tahap 7: Responsif

- Uji tampilan pada desktop, tablet, dan mobile.
- Pastikan sidebar dan tabel tetap bisa digunakan.

## 21. Prompt Implementasi untuk AI Coding

Gunakan prompt berikut jika ingin meminta AI coding merevisi UI tanpa merusak fungsi:

```text
Saya memiliki website E-BloodBank berbasis PHP dengan routing menggunakan index.php?page=... Website sudah memiliki landing page, dashboard PMI, dashboard rumah sakit, dan dashboard pendonor. Saya ingin merevisi UI/UX agar tampilannya modern seperti dashboard Donezo: clean, rounded card, dominan putih-abu muda, aksen hijau, sidebar minimalis, topbar rapi, statistik card besar, dan shadow halus.

Batasan penting:
1. Jangan mengubah fungsi tombol, route, link, form action, form method, name attribute, session, query database, dan logic backend yang sudah ada.
2. Jangan menghapus fitur yang sudah ada.
3. Jangan membuat fitur backend baru.
4. Perubahan hanya pada HTML layout, class, CSS, spacing, card, button, sidebar, topbar, responsivitas, dan tampilan visual.
5. Jika ada JavaScript lama yang memakai id/class tertentu, jangan ubah id/class tersebut. Tambahkan class baru jika perlu.
6. Pertahankan struktur halaman role: PMI, Rumah Sakit, dan Pendonor.
7. Gunakan identitas E-BloodBank dengan aksen merah darah secukupnya, tetapi gaya utama mengikuti referensi hijau-putih modern.

Tugas:
- Buat ulang tampilan landing page agar lebih clean dan modern.
- Buat ulang dashboard PMI dengan card statistik, stok darah, dan aksi cepat seperti layout dashboard modern.
- Buat ulang dashboard rumah sakit dengan fokus request darah dan status request.
- Buat ulang dashboard pendonor dengan fokus jadwal donor, riwayat donor, dan status screening.
- Buat CSS global modern yang konsisten dan responsif.
- Pastikan semua tombol dan fitur lama tetap berjalan.
```

## 22. Contoh Struktur CSS Global

```css
:root {
  --color-primary: #0F4D2E;
  --color-primary-2: #137A43;
  --color-primary-soft: #EAF7EF;
  --color-blood: #D62828;
  --color-blood-soft: #FDECEC;
  --color-bg: #F5F7F6;
  --color-card: #FFFFFF;
  --color-border: #E5E7EB;
  --color-text: #111827;
  --color-muted: #6B7280;
  --radius-card: 22px;
  --radius-button: 14px;
  --shadow-soft: 0 10px 30px rgba(15, 23, 42, 0.06);
}

body {
  background: var(--color-bg);
  color: var(--color-text);
  font-family: 'Inter', 'Poppins', system-ui, sans-serif;
}

.app-shell {
  min-height: 100vh;
  display: flex;
  background: var(--color-bg);
}

.sidebar {
  width: 270px;
  min-height: 100vh;
  background: #ffffff;
  border-right: 1px solid var(--color-border);
  padding: 24px 18px;
}

.main-content {
  flex: 1;
  min-width: 0;
}

.topbar {
  height: 82px;
  background: #ffffff;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
}

.page-content {
  padding: 28px 32px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 20px;
}

.card-modern {
  background: var(--color-card);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-soft);
  padding: 24px;
}

.card-highlight {
  background: linear-gradient(135deg, #0F4D2E, #137A43);
  color: #ffffff;
}

.btn-modern {
  border-radius: var(--radius-button);
  padding: 12px 18px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .app-shell {
    display: block;
  }

  .sidebar {
    width: 100%;
    min-height: auto;
  }

  .topbar {
    height: auto;
    padding: 18px;
    align-items: flex-start;
    gap: 12px;
  }

  .page-content {
    padding: 18px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
```

## 23. Catatan Khusus untuk Website Saat Ini

Berdasarkan tampilan saat ini, dashboard PMI sudah memiliki struktur fitur yang jelas. Bagian yang paling perlu direvisi adalah gaya visual, bukan alur sistem.

Prioritas revisi:

1. Sidebar dibuat lebih bersih dan modern.
2. Header dashboard dibuat lebih rapi dan tidak terlalu kosong.
3. Card statistik dibuat lebih besar, rounded, dan konsisten.
4. Card stok darah dibuat lebih minimalis.
5. Aksi cepat dibuat seperti panel modern, bukan sekadar kumpulan tombol warna kuat.
6. Landing page dibuat lebih terang dan menyatu dengan dashboard baru.
7. Warna merah tetap dipakai sebagai aksen, bukan warna dominan.

## 24. Hasil Akhir yang Diharapkan

Setelah revisi, website E-BloodBank harus terlihat seperti sistem digital donor darah yang modern, rapi, dan mudah digunakan. Pengguna PMI, rumah sakit, dan pendonor tetap bisa memakai fitur lama seperti biasa, tetapi pengalaman visual menjadi lebih profesional dan konsisten.

