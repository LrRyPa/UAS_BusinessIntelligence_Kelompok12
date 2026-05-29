# ![ToySight](public/assets/img/favicon.svg) ToySight BI
### *Suite Analitik Cerdas untuk Bisnis Retail Mainan*

---

## Identitas Kelompok

| | |
|---|---|
| **Mata Kuliah** | Business Intelligence |
| **Kelas** | A — Sistem Informasi |
| **Kelompok** | 12 |
| **Tugas** | Ujian Akhir Semester (UAS) |

### Anggota Kelompok

| No. | Nama | NIM |
|-----|------|-----|
| 1 | Indah Putri Lestari | 2409116004 |
| 2 | Larry Polin Anugrah | 2409116026 |
| 3 | ZELSYA RIZQITA RAHMADHINI | 2409116022 |

---

> Sistem Business Intelligence berbasis web yang dirancang untuk membantu proses monitoring penjualan, inventaris, dan performa toko pada perusahaan retail mainan secara lebih cepat, terstruktur, dan berbasis data.

---

## Deskripsi Sistem

ToySight BI adalah sistem Business Intelligence berbasis web yang dikembangkan menggunakan dataset **Mexican Toys Sales** dari Kaggle. Sistem ini memproses lebih dari **829.000 transaksi penjualan** dari **50 toko** dan **35 produk** selama periode 2022–2023 melalui pipeline ETL (Extract, Transform, Load) ke dalam Data Warehouse berbasis SQLite dengan model Galaxy Schema.

Sistem ini menerapkan konsep **Decision Support System (DSS)** melalui dashboard analytics interaktif yang membantu pihak manajemen dalam memantau kondisi bisnis dan mengambil keputusan berbasis data.

---

## Fitur Utama

### Dashboard Analytics
- **Dashboard Utama** — KPI ringkasan bisnis: total pendapatan, pesanan, unit terjual, laba kotor, total produk, jumlah toko, stok inventaris, dan rata-rata margin
- **Analitik Penjualan** — Tren penjualan harian, performa toko (Top 15), komposisi kategori, pola penjualan per hari, serta 10 produk teratas dan terbawah
- **Analitik Produk** — Performa per kategori, distribusi pendapatan per tier harga, dan tabel detail margin produk
- **Analitik Inventaris** — Distribusi kondisi stok, stok per kategori, stok per toko (Top 10), dan peringatan stok rendah/habis
- **Performa Toko** — Pendapatan per kota, distribusi tipe lokasi, dan peringkat seluruh toko

### Sistem Login dan Hak Akses (RBAC)
Sistem menerapkan Role-Based Access Control dengan 4 role pengguna:

| Role | Hak Akses |
|------|-----------|
| **Admin** | Akses penuh — semua dashboard, semua CRUD, manajemen pengguna |
| **Manager** | Semua dashboard + laporan (hanya baca, tanpa CRUD) |
| **Sales Staff** | Dashboard utama + analitik penjualan + CRUD penjualan + lihat produk |
| **Warehouse Staff** | Dashboard utama + analitik inventaris + CRUD inventaris |

### Pengelolaan Data (CRUD)
- **Produk** — Tambah, edit, hapus produk dengan klasifikasi tier harga otomatis (Budget / Mid-Range / Premium)
- **Toko** — Kelola data cabang retail di 29 kota Meksiko dengan 4 tipe lokasi
- **Penjualan** — Catat transaksi dengan kalkulasi otomatis revenue, COGS, gross profit, dan margin
- **Inventaris** — Kelola stok per toko dengan peringatan stok rendah
- **Pengguna** — Admin dapat mengelola akun dan role pengguna sistem

### Laporan dan Export
- 4 jenis laporan: Ringkasan Penjualan, Performa Produk, Peringkat Toko, Penjualan Berdasarkan Kategori
- Export data dalam format CSV
- Filter rentang tanggal pada setiap laporan

---

## Teknologi yang Digunakan

| Kategori | Teknologi |
|----------|-----------|
| Backend | PHP 8.0+ (arsitektur MVC) |
| Frontend | HTML, CSS, JavaScript, Bootstrap |
| Visualisasi | Chart.js |
| Database | SQLite |
| Local Server | Laragon |
| ETL Pipeline | Python, Pandas, SQLAlchemy |
| Pengujian | pytest, pytest-cov |

---

## Struktur Data Warehouse

Data Warehouse menggunakan model **Galaxy Schema** dengan prefix tabel `dw__`:

### Tabel Dimensi
| Tabel | Baris | Deskripsi |
|-------|-------|-----------|
| `dw__dim_product` | 35 | Informasi produk beserta harga dan tier harga |
| `dw__dim_store` | 50 | Informasi toko beserta lokasi dan usia operasional |
| `dw__dim_category` | 5 | Kategori produk beserta pengelompokan bisnis |
| `dw__dim_date` | 638 | Dimensi tanggal dengan atribut kalender lengkap |

### Tabel Fakta
| Tabel | Baris | Deskripsi |
|-------|-------|-----------|
| `dw__fact_sales` | 829.262 | Data transaksi penjualan dengan computed measures |
| `dw__fact_inventory` | 1.593 | Snapshot stok barang per toko |

---

## Cara Menjalankan Sistem

### Persyaratan
- PHP 8.0 atau lebih tinggi
- Laragon (sebagai local server)
- Browser modern (Chrome / Firefox / Edge)

### Langkah Instalasi

1. **Ekstrak file ZIP** ke dalam direktori Laragon (`C:/laragon/www/`)

2. **Pastikan file database** tersedia di:
   ```
   database/mexican_toys_dws.db
   ```

3. **Jalankan Laragon** dan aktifkan Apache

4. **Buka browser** dan akses:
   ```
   http://localhost/toysight
   ```

5. **Login** menggunakan akun demo berikut

---

## Akun Demo

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Admin |
| `manager` | `manager123` | Manager |
| `sales` | `sales123` | Sales Staff |
| `warehouse` | `warehouse123` | Warehouse Staff |

---

## Struktur Folder Proyek

```
toysight/
├── app/
│   ├── controllers/         # Controller untuk setiap fitur
│   ├── models/              # Model database setiap entitas
│   └── views/
│       ├── analytics/       # Halaman analitik penjualan, produk, inventaris, toko
│       ├── auth/            # Halaman login
│       ├── crud/            # Halaman CRUD produk, toko, penjualan, inventaris
│       ├── dashboard/       # Halaman dashboard utama
│       ├── layouts/         # Layout utama dan layout cetak
│       ├── partials/        # Sidebar dan topbar navigasi
│       ├── reports/         # Halaman laporan dan cetak
│       └── users/           # Halaman manajemen pengguna
├── config/
│   ├── app.php              # Konfigurasi nama dan versi aplikasi
│   ├── database.php         # Konfigurasi koneksi database SQLite
│   └── roles.php            # Matriks hak akses per role
├── database/
│   └── mexican_toys_dws.db  # File database SQLite Data Warehouse
├── helpers/
│   ├── Auth.php             # Helper autentikasi dan RBAC
│   ├── Controller.php       # Base controller
│   ├── Database.php         # PDO wrapper
│   └── Router.php           # Router sederhana
├── public/
│   └── assets/
│       ├── css/             # Stylesheet utama dan cetak
│       ├── js/              # JavaScript sidebar, modal, dan navigasi
│       └── img/             # Aset gambar dan ikon
├── routes/
│   └── web.php              # Definisi routing aplikasi
├── storage/
│   └── exports/             # Folder penyimpanan file CSV export
└── index.php                # Entry point aplikasi
```

---

## Dataset

Dataset yang digunakan adalah **Mexican Toys Sales** yang diperoleh dari platform **Kaggle**. Dataset ini terdiri dari 5 file CSV yang saling terhubung:

| File | Baris | Deskripsi |
|------|-------|-----------|
| `sales.csv` | 829.262 | Data transaksi penjualan periode 2022–2023 |
| `products.csv` | 35 | Informasi produk beserta harga dan kategori |
| `stores.csv` | 50 | Informasi toko beserta lokasi dan tanggal buka |
| `inventory.csv` | 1.593 | Data stok barang per toko |
| `calendar.csv` | 638 | Data kalender periode 2022–2023 |

---

## Proses ETL

Pipeline ETL dibangun menggunakan Python dan dijalankan secara terpisah sebelum sistem web dijalankan. Proses ETL terdiri dari 4 tahap:

```
Stage 1 — Extract   : Membaca 5 file CSV dari folder datasets/
Stage 2 — Staging   : Membersihkan data dan menyimpan ke tabel staging__
Stage 3 — Transform : Membangun Galaxy Schema (4 dimensi + 2 fakta)
Stage 4 — Load      : Menyimpan tabel DW ke SQLite (prefix dw__)
```

Hasil pipeline ETL menghasilkan file `mexican_toys_dws.db` yang digunakan langsung oleh sistem web ToySight BI.

---

## Informasi Proyek

- **Nama Sistem** : ToySight BI
- **Versi** : 1.0.0
- **Tujuan** : Laporan Penelitian — Implementasi Sistem Business Intelligence
- **Dataset** : Mexican Toys Sales (Kaggle)
- **Tahun** : 2026
