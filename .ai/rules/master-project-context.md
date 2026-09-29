# Master Project Context: Sistem Informasi Eksekutif (SIE) - Dashboard Analitik SMS A2P

- **Klien / Instansi:** PT Triple One Global (Proyek Internship).
- **Project Lead:** Andre Luhut (PM & Backend Lead).
- **Tanggal PRD:** 23 September 2026 (Versi 2.0 Revisi Hasil Evaluasi).
- **Target Deadline:** 27 November 2026.

- **Tech Stack:**
  - Laravel ^13.17 (PHP ^8.3)
  - Livewire ^4.1 (Class-based components & Blaze ^1.0)
  - Flux UI ^2.13.1 + Tailwind CSS v4
  - SQLite (dev/lokal)
  - Autentikasi: Laravel Fortify ^1.37.2
  - Otorisasi: Spatie Laravel Permission
  - Visualisasi Data: Chart.js / ApexCharts

- **Role & Access Matrix:**
  1. **Pengguna Umum:** View-only ke dashboard (kartu metrik, grafik, tabel rincian, filter). Tidak bisa input, import, atau ekspor.
  2. **Eksekutif:** Akses penuh dashboard + input data manual, import CSV/Excel, dan ekspor laporan (PDF/Excel).
  3. **Admin IT:** Akses penuh Eksekutif + manajemen user & assignment role.

- **Architecture Guidelines:**
  - Dokumen PRD Publik Tim: [`PRD.md`](file:///c:/Users/andre/Herd/Dashboard-Analyctics-Intern/PRD.md)
  - Class-based Livewire components (`App\Livewire\*`).
  - Strict preservation of Spatie Roles & Permissions.
  - Data contract backend support for metrics (`sms_traffics`), trend charts, doughnut operator breakdown, and paginated detail records.
  - Validation Rules: `total_sukses + total_gagal == total_dikirim` & unique index `tanggal + operator` dengan dialog konfirmasi overwrite.
