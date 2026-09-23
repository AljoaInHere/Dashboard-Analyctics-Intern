# Master Project Context: Sistem Informasi Eksekutif (SIE) - Dashboard Analitik SMS A2P

- **Klien / Instansi:** PT Triple One Global (Proyek Internship).
- **Target Deadline:** 27 November 2026.
- **Tech Stack:** Laravel 11, Livewire 3 (Class-based), Tailwind CSS, SQLite, Spatie Laravel Permission (`eksekutif` role), Chart.js / ApexCharts.
- **Project Lead:** Andre (PM & Backend Lead).
- **Architecture Guidelines:**
  - Route `/dashboard` protected by `['auth', 'verified', 'role:eksekutif']`.
  - Class-based Livewire components (`App\Livewire\*`).
  - Strict preservation of Spatie Roles & Permissions.
  - Data contract backend support for metrics, trend charts, doughnut operator breakdown, and paginated detail records.
