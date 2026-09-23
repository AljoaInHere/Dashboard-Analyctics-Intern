# 📄 PRODUCT REQUIREMENTS DOCUMENT (PRD)

**Nama Proyek:** Sistem Informasi Eksekutif (SIE) - Dashboard Analitik SMS A2P
**Klien / Organisasi:** PT Triple One Global (Proyek Internship)
**Manajer Proyek:** Andre Luhut
**Tanggal Dokumen:** 23 September 2026
**Target Selesai:** 27 November 2026
**Versi:** 2.0 (Revisi Hasil Evaluasi)

---

## 1. Ringkasan Eksekutif (Executive Summary)

Proyek ini bertujuan untuk membangun sebuah Sistem Informasi Eksekutif (SIE) berbasis web yang menyediakan dasbor analitik interaktif untuk memantau lalu lintas (trafik) pengiriman SMS A2P (Application-to-Person). Sistem ini dirancang untuk menggantikan pelaporan manual dengan visualisasi data otomatis, memungkinkan pimpinan PT Triple One Global untuk memantau metrik kunci seperti volume pengiriman, tingkat keberhasilan (*delivery rate*), dan distribusi penggunaan operator telekomunikasi secara cepat dan akurat.

*(Catatan Lingkup: Sesuai penyesuaian evaluasi proyek, fitur Chatbot telah resmi ditiadakan dari ruang lingkup proyek ini agar tim dapat fokus 100% pada keandalan visualisasi data SIE).*

---

## 2. Tujuan dan Sasaran (Goals & Objectives)

* **Tujuan Bisnis:** Memberikan visibilitas data harian yang akurat kepada level eksekutif untuk pengambilan keputusan strategis terkait vendor operator dan efisiensi pengiriman SMS.
* **Tujuan Teknis:** Membangun arsitektur aplikasi yang ringan, aman, dan mudah dikembangkan menggunakan arsitektur *Class-based component* (Livewire) dengan agregasi data yang optimal.

---

## 3. Pengguna Sasaran (User Personas & Roles)

| Peran (Role) | Deskripsi | Hak Akses (Permissions) |
| --- | --- | --- |
| **Eksekutif** | Pimpinan/Manajemen PT Triple One Global yang membutuhkan laporan cepat. | Akses penuh (*read & write*): melihat seluruh dasbor, metrik, grafik, tabel rincian, **serta** melakukan input data, import CSV/Excel, dan ekspor laporan (PDF/Excel). |
| **Admin IT** | Tim teknis yang mengelola sistem dan akun pengguna. | Akses penuh, termasuk semua hak Eksekutif ditambah: modifikasi data `sms_traffics`, penugasan *role* pengguna, dan manajemen akun. |
| **Pengguna Umum** | Akun yang terdaftar dan terverifikasi, namun bukan level eksekutif atau admin. | **View-only**: dapat mengakses dan melihat seluruh dasbor (kartu metrik, grafik, tabel rincian, filter), tetapi **tidak dapat** melakukan input data manual, import CSV/Excel, ekspor laporan (PDF/Excel), atau modifikasi data apapun. |

### Alur Registrasi & Akses
1. Pengguna melakukan **registrasi** melalui halaman publik.
2. Setelah login, pengguna langsung mendapat role **Pengguna Umum** (view-only access ke dashboard).
3. **Admin IT** dapat mempromosikan Pengguna Umum menjadi **Eksekutif** melalui panel manajemen user.

---

## 4. Spesifikasi Teknologi (Tech Stack)

| Komponen | Teknologi | Versi |
| --- | --- | --- |
| **Backend Framework** | Laravel | ^13.17 |
| **PHP** | PHP | ^8.3 |
| **Reaktivitas / Komponen** | Livewire (Class-based) | ^4.1 |
| **UI Component Library** | Flux UI | ^2.13.1 |
| **Client-side Interactivity** | Alpine.js | (bundled via Livewire) |
| **Styling** | Tailwind CSS | v4 |
| **Visualisasi Data** | Chart.js atau ApexCharts | TBD |
| **Database** | SQLite | File-based (dev/lokal) |
| **Autentikasi** | Laravel Fortify | ^1.37.2 |
| **Otorisasi / Role** | Spatie Laravel Permission | Latest |
| **Livewire SFC** | Livewire Blaze | ^1.0 |

---

## 5. Kebutuhan Fungsional (Functional Requirements)

### 5.1. Modul Autentikasi & Otorisasi

| ID | Kebutuhan |
| --- | --- |
| **FR-01.1** | Sistem harus menyediakan fitur Login dan Register standar. |
| **FR-01.2** | Sistem harus mengenkripsi kata sandi pengguna (Bcrypt/Hash via Laravel Fortify). |
| **FR-01.3** | Sistem harus memiliki *middleware* yang mengecek role pengguna menggunakan Spatie sebelum merender aksi tertentu (input/import/ekspor). |
| **FR-01.4** | Pengguna yang baru mendaftar otomatis mendapat role **Pengguna Umum** (view-only). |
| **FR-01.5** | Admin IT harus memiliki panel manajemen user untuk melihat daftar pengguna dan meng-assign/mengubah role. |

---

### 5.2. Modul Dasbor Analitik (Komponen Utama)

Komponen `DashboardAnalytics` (Livewire) harus menyajikan antarmuka *single-page* yang memuat:

#### 5.2.1. Kartu Metrik (Key Performance Indicators)

| ID | Kebutuhan |
| --- | --- |
| **FR-02.1** | Menampilkan **Total SMS Dikirim** pada periode terpilih (default: bulan berjalan). |
| **FR-02.2** | Menampilkan **Delivery Rate (%)** pada periode terpilih (Total Sukses / Total Dikirim). |

> **Justifikasi:** Berdasarkan kebutuhan stakeholder, dua metrik ini adalah KPI utama yang paling sering dipantau oleh level eksekutif. Metrik tambahan (Total Gagal, Failure Rate) dapat diturunkan dari data yang sudah tersedia di tabel rincian dan grafik distribusi.

#### 5.2.2. Filter Data Interaktif

| ID | Kebutuhan |
| --- | --- |
| **FR-03.1** | Filter **Preset Cepat**: Hari Ini, 7 Hari Terakhir, 30 Hari Terakhir, Bulan Ini. |
| **FR-03.2** | Filter **Rentang Tanggal Kustom** (Date Range Picker): pengguna dapat memilih tanggal mulai dan tanggal selesai secara manual. |
| **FR-03.3** | Filter **Operator**: Dropdown multi-select untuk memfilter berdasarkan operator tertentu (Semua / Telkomsel / Indosat / XL / Smartfren / dll.). |
| **FR-03.4** | Semua kartu metrik, grafik, dan tabel rincian harus **bereaksi secara real-time** saat filter diubah (via Livewire, tanpa full page reload). |

#### 5.2.3. Grafik Tren (Line/Area Chart)

| ID | Kebutuhan |
| --- | --- |
| **FR-04.1** | Menampilkan fluktuasi total SMS terkirim sesuai rentang filter yang dipilih (default: 7 hari terakhir). |
| **FR-04.2** | Sumbu X: Tanggal (Format: YYYY-MM-DD). Sumbu Y: Jumlah SMS. |
| **FR-04.3** | Grafik harus terintegrasi dengan pustaka Chart.js atau ApexCharts dan menerima data dari komponen Livewire. |

#### 5.2.4. Grafik Distribusi Operator (Doughnut/Pie Chart)

| ID | Kebutuhan |
| --- | --- |
| **FR-05.1** | Menampilkan pangsa penggunaan operator telekomunikasi sesuai filter periode yang aktif (default: bulan berjalan). |
| **FR-05.2** | Visualisasi *hover* untuk melihat detail angka absolut dan persentase. |

#### 5.2.5. Tabel Rincian Harian (Detail Table)

| ID | Kebutuhan |
| --- | --- |
| **FR-06.1** | Menampilkan tabel data rekapitulasi di bawah grafik, dengan kolom: **Tanggal**, **Operator**, **Total Dikirim**, **Total Sukses**, **Total Gagal**. |
| **FR-06.2** | Tabel harus merespons filter yang aktif (tanggal & operator). |
| **FR-06.3** | Mendukung paginasi atau *infinite scroll* jika data melebihi batas tampilan. |

---

### 5.3. Modul Manajemen Data (Data Ingestion)

> **Catatan Akses:** Modul ini hanya dapat diakses oleh role **Eksekutif** dan **Admin IT**. Pengguna Umum tidak memiliki akses ke fitur input, import, atau modifikasi data.

#### 5.3.1. Form Input Manual

| ID | Kebutuhan |
| --- | --- |
| **FR-07.1** | Form entri data trafik SMS harian dengan field: **Tanggal**, **Operator** (dropdown), **Total Dikirim**, **Total Sukses**, **Total Gagal**. |
| **FR-07.2** | Sistem harus menghitung dan menampilkan *preview* Delivery Rate sebelum submit. |

#### 5.3.2. Import Data (CSV/Excel)

| ID | Kebutuhan |
| --- | --- |
| **FR-08.1** | Fitur upload berkas CSV atau Excel (.xlsx) untuk input data rekapitulasi harian secara massal. |
| **FR-08.2** | Sistem harus menampilkan *preview* data yang akan di-import sebelum konfirmasi penyimpanan. |
| **FR-08.3** | Sistem harus menampilkan laporan hasil import (jumlah baris berhasil, gagal, dan alasan kegagalan). |

#### 5.3.3. Aturan Validasi & Integritas Data

| ID | Kebutuhan |
| --- | --- |
| **FR-09.1** | **Constraint Aritmatika:** `total_sukses + total_gagal` **harus sama dengan** `total_dikirim`. Jika tidak, data ditolak dengan pesan error yang jelas. |
| **FR-09.2** | **Unique Constraint:** Kombinasi `tanggal + operator` harus unik. Tidak boleh ada dua baris data untuk operator yang sama pada tanggal yang sama. |
| **FR-09.3** | **Conflict Resolution (Duplikasi):** Jika data dengan kombinasi `tanggal + operator` yang sama sudah ada, sistem harus menampilkan dialog konfirmasi: *"Data untuk [Operator] tanggal [Tanggal] sudah ada. Timpa data lama?"*. Pengguna dapat memilih untuk menimpa atau membatalkan. |
| **FR-09.4** | Validasi tipe data standar: tanggal valid, operator tidak kosong, semua angka integer ≥ 0. |

---

### 5.4. Modul Ekspor Laporan (Export Reporting)

> **Catatan Akses:** Modul ini hanya dapat diakses oleh role **Eksekutif** dan **Admin IT**. Pengguna Umum tidak memiliki akses ekspor.

| ID | Kebutuhan |
| --- | --- |
| **FR-10.1** | Ekspor ringkasan dashboard ke **PDF** untuk keperluan rapat/presentasi eksekutif (menggunakan DomPDF atau Snappy). |
| **FR-10.2** | Ekspor rincian data ke **Excel (.xlsx) atau CSV** untuk analisis lanjutan oleh tim operasional. |
| **FR-10.3** | Data yang diekspor harus sesuai dengan filter yang sedang aktif di dashboard. |

---

## 6. Struktur Data (Data Architecture)

Penyimpanan difokuskan pada agregasi data harian untuk mempercepat *query* eksekutif, menggunakan tabel **`sms_traffics`**.

| Nama Kolom | Tipe Data | Deskripsi | Atribut |
| --- | --- | --- | --- |
| `id` | BigInt | Primary Key | Auto Increment |
| `tanggal` | Date | Tanggal rekapitulasi trafik | Indexed |
| `operator` | String | Nama operator telekomunikasi | - |
| `total_dikirim` | Integer | Total keseluruhan SMS yang dicoba kirim | ≥ 0 |
| `total_sukses` | Integer | Total SMS yang berhasil masuk ke perangkat | ≥ 0 |
| `total_gagal` | Integer | Total SMS yang gagal terkirim (Bounced/Failed) | ≥ 0 |
| `timestamps` | Datetime | Waktu data dibuat dan diperbarui | `created_at`, `updated_at` |

**Constraint tambahan:**
- **Unique Index:** `tanggal + operator` (mencegah duplikasi data pada level database).
- **Check Constraint (Business Rule):** `total_sukses + total_gagal = total_dikirim` (divalidasi di level aplikasi / Form Request).

---

## 7. Kebutuhan Non-Fungsional (Non-Functional Requirements)

| Aspek | Kebutuhan |
| --- | --- |
| **Performa** | Waktu muat halaman dasbor harus di bawah 2 detik. Data ditarik menggunakan agregasi SQL (`SUM`, `whereMonth`), bukan iterasi di memori PHP. |
| **Keamanan** | Rute analitik dibungkus middleware `auth` dan `verified`. Aksi tulis (input/import/ekspor) dilindungi middleware role tambahan (`role:eksekutif,admin-it`). Proteksi CSRF, sanitasi input file upload, dan parameterized queries untuk mencegah SQL Injection. |
| **Responsivitas** | UI dasbor menggunakan kelas utility Tailwind (`grid-cols-1 md:grid-cols-2`) agar tampilan responsif di desktop, tablet, dan ponsel. |
| **Reaktivitas** | Interaksi filter dan pembaruan grafik berjalan mulus tanpa kedip (*smooth reactivity*) via Livewire. |

---

## 8. Matriks Hak Akses per Fitur (Permission Matrix)

| Fitur | Pengguna Umum | Eksekutif | Admin IT |
| --- | --- | --- | --- |
| Lihat Kartu Metrik | ✅ | ✅ | ✅ |
| Lihat Grafik Tren & Distribusi | ✅ | ✅ | ✅ |
| Lihat Tabel Rincian Harian | ✅ | ✅ | ✅ |
| Gunakan Filter (Tanggal & Operator) | ✅ | ✅ | ✅ |
| Input Data Manual | ❌ | ✅ | ✅ |
| Import Data CSV/Excel | ❌ | ✅ | ✅ |
| Ekspor Laporan (PDF/Excel) | ❌ | ✅ | ✅ |
| Manajemen User & Role Assignment | ❌ | ❌ | ✅ |

---

## 9. Timeline & Milestone (Sprint-Based)

| Sprint | Periode | Fase | Deliverable |
| --- | --- | --- | --- |
| **Sprint 1** | 23 Sep – 6 Okt 2026 | **Fase 1: MVP Dashboard** | Auth (Fortify) + Spatie Role setup, Kartu metrik (Total Dikirim & Delivery Rate), Grafik dasar (Line + Doughnut) dengan data statis/seeder, Halaman dashboard bisa diakses oleh semua role. |
| **Sprint 2** | 7 Okt – 20 Okt 2026 | **Fase 2: Interaktivitas** | Integrasi Chart.js/ApexCharts dengan data Livewire, Filter interaktif (preset cepat + rentang tanggal + operator), Tabel rincian harian di bawah grafik, Grafik & metrik bereaksi real-time terhadap filter. |
| **Sprint 3** | 21 Okt – 3 Nov 2026 | **Fase 3: Manajemen Data** | Form input data manual + validasi (constraint aritmatika + cek duplikasi + konfirmasi overwrite), Import CSV/Excel dengan preview & laporan hasil, Panel admin manajemen user & role assignment, Proteksi middleware role pada fitur tulis. |
| **Sprint 4** | 4 Nov – 17 Nov 2026 | **Fase 4: Ekspor & Polish** | Ekspor PDF (DomPDF/Snappy) + Ekspor Excel/CSV, Polishing UI/UX (responsivitas, dark mode, micro-animations), Optimasi query & performa, Testing (feature test + unit test). |
| **Sprint 5** | 18 Nov – 27 Nov 2026 | **Buffer & Finalisasi** | UAT (User Acceptance Testing), Bug fixing, Dokumentasi teknis & panduan pengguna, Presentasi akhir. |

---

## 10. Strategi Pengujian (Testing Strategy)

### 10.1. Feature Tests (Pest)

| Area | Yang Diuji |
| --- | --- |
| **Auth & Role** | Guest diarahkan ke login. Pengguna Umum bisa akses dashboard (view). Pengguna Umum **tidak bisa** akses form input/import/ekspor (403). Eksekutif bisa akses semua fitur. Admin IT bisa akses panel manajemen user. |
| **Data Ingestion** | Input manual dengan data valid berhasil tersimpan. Input dengan `total_sukses + total_gagal ≠ total_dikirim` ditolak (422). Upload CSV dengan data duplikat memicu konfirmasi overwrite. |
| **Dashboard Rendering** | Dashboard menampilkan kartu metrik dengan angka yang sesuai data seeder. Filter tanggal mengubah data yang ditampilkan. |

### 10.2. Unit Tests

| Area | Yang Diuji |
| --- | --- |
| **Query Agregasi** | Perhitungan Delivery Rate akurat (edge case: total_dikirim = 0 → rate = 0%). Query tren 7 hari mengembalikan data yang tepat. Query distribusi operator mengelompokkan dengan benar. |
| **Validasi** | Rule `total_sukses + total_gagal == total_dikirim` bekerja. Unique constraint `tanggal + operator` berfungsi. |

### 10.3. Menjalankan Test

```bash
# Menjalankan semua test
php artisan test --compact

# Menjalankan test spesifik
vendor/bin/pest --filter=DashboardTest
```

---

## 11. Struktur Tim dan Alur Kerja (Team & Workflow)

Proyek ini mengadopsi kontrol versi (Git/GitHub) dengan *Branch Protection Rule* pada cabang `main`. Setiap penggabungan fitur wajib melalui *Pull Request* dan *Code Review*.

| Anggota Tim | Tanggung Jawab Utama |
| --- | --- |
| **Andre Luhut (PM/Backend)** | Arsitektur database, Seeder data dummy, Query analitik, integrasi Spatie Role, pengaturan Git & perlindungan repositori. |
| **Member 1 (Backend/Livewire)** | Menangkap variabel dari model, menghubungkan struktur data dengan *controller* Livewire, mengelola *passing data* ke tampilan *Blade*, implementasi form input & import. |
| **Member 2 (Frontend/UI/UX)** | Mendesain tata letak dasbor dengan Tailwind CSS, memadukan pustaka bagan (Chart.js), memastikan antarmuka responsif dan ramah pengguna (*user-friendly*). |

---

## 12. Ruang Lingkup (Scope Summary)

### Dalam Lingkup (In-Scope)
- ✅ Autentikasi pengguna (Login/Register) dengan Laravel Fortify
- ✅ Otorisasi berbasis role (Eksekutif, Admin IT, Pengguna Umum) dengan Spatie Permission
- ✅ Kartu metrik ringkasan (Total Dikirim, Delivery Rate)
- ✅ Grafik tren trafik (Line/Area Chart) — reaktif terhadap filter
- ✅ Grafik distribusi operator (Doughnut Chart) — reaktif terhadap filter
- ✅ Filter interaktif (preset cepat, rentang tanggal kustom, operator)
- ✅ Tabel rincian harian dengan paginasi
- ✅ Form input data manual dengan validasi lengkap
- ✅ Import data CSV/Excel dengan preview & conflict resolution
- ✅ Ekspor laporan ke PDF dan Excel/CSV
- ✅ Panel admin manajemen user & role assignment

### Di Luar Lingkup (Out-of-Scope)
- ❌ Integrasi langsung dengan SMPP engine / SMS gateway (sistem fokus pada analitik, bukan pengiriman SMS)
- ❌ Fitur Chatbot (telah ditiadakan dari scope)
- ❌ Penagihan / billing / invoicing otomatis
- ❌ API endpoint publik untuk push data dari sistem eksternal (bisa jadi pengembangan di masa depan)
- ❌ Notifikasi / alert otomatis saat anomali trafik (bisa jadi pengembangan di masa depan)
