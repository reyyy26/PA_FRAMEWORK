# Nyxx AgriSupply 📦

<p align="center">
  <img style="margin-right: 8px;" src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img style="margin-right: 8px;" src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img style="margin-right: 8px;" src="https://img.shields.io/badge/MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img style="margin-right: 8px;" src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
</p>

Nyxx AgriSupply adalah aplikasi berbasis Laravel yang dirancang untuk mempercepat pembuatan solusi manajemen rantai pasokan pertanian mulai dari manajemen produk, stok, supplier, hingga pemesanan dan laporan analitik. Cocok digunakan sebagai pondasi untuk aplikasi inventory, order-management, atau marketplace B2B di sektor agrikultur.

## Fitur Utama ✨

*   **Manajemen Produk & Stok** — CRUD produk, SKU, kategori, dan manajemen stok (stok masuk/keluar, penyesuaian).

*   **Manajemen Supplier** — Data supplier, kontak, dan histori pasokan.

*   **Order & Pembelian** — Pembuatan order pembelian, approval, dan pelacakan status pengiriman.

*   **Dashboard & Laporan** — Ringkasan stok, penjualan, dan alert restock (placeholder — sesuaikan implementasi).

*   **Modular & Extensible** — Struktur mengikuti konvensi Laravel; mudah ditambahkan fitur baru atau package.

*   **Frontend Modern** — Vite + asset pipeline untuk build frontend; templates Blade siap dipakai.

*   **Testing & Seeders** — Struktur untuk unit/feature test dan seed data awal (jika tersedia di repo).

## Tech Stack 🛠️

*   Bahasa: PHP 🐘
*   Framework: Laravel 🚀
*   Database: MySQL (kemungkinan, perlu konfigurasi) 💽
*   Lainnya: JavaScript (untuk fungsionalitas front-end interaktif) 🌐

## Prasyarat

*   PHP >= 8.x dan ekstensi umum (pdo, mbstring, openssl, json, xml)
*   Composer
*   MySQL / MariaDB (atau DB lain)�
*   Node.js & NPM / Yarn
*   Git

## Instalasi & Menjalankan 🚀

1.  Clone repositori:
    ```bash
    git clone https://github.com/reyyy26/PA_FRAMEWORK
    ```
2.  Masuk ke direktori:
    ```bash
    cd PA_FRAMEWORK
    ```
3.  Install dependensi:
    ```bash
    composer install
    ```
4.  Salin file environment example dan sesuaikan:
    ```bash
    cp .env.example .env
    ```
    Kemudian sesuaikan pengaturan database di `.env`
5.  Generate key aplikasi:
    ```bash
    php artisan key:generate
    ```
6.  Jalankan migrasi database:
    ```bash
    php artisan migrate
    ```
7.  Jalankan server development:
    ```bash
    php artisan serve
    ```


## Perintah Berguna 🧰

*   composer install — install dependency PHP
*   npm install — install dependency frontend
*   php artisan migrate — menjalankan migration
*   php artisan db:seed — menjalankan seeder
*   php artisan route:list — melihat daftar route
*   php artisan test atau vendor/bin/phpunit — menjalankan test


## Screenshot Fitur Dll

1.  ![Logo](https://github.com/reyyy26/PA_FRAMEWORK/blob/main/public/logo/Nyxx.png)
   *   Ini merupakan logo yang dipakai untuk web ini.
     
3.  ![Logo](https://github.com/reyyy26/PA_FRAMEWORK/blob/main/public/logo/Screenshot%20(67).png)
   *   Ini merupakan tampilan awal web ini dimana user melakukan pengisian email dan password yang telah disediakan oleh admin untuk masuk kedalam webnya.
     
4.  ![Logo](https://github.com/reyyy26/PA_FRAMEWORK/blob/main/public/logo/Screenshot%20(72).png)
   *   Ini merupakan dashboard utama admin yang didalamnya terdapat beberapa informasi mengenai data dari web ini.
     
5.  ![Logo](https://github.com/reyyy26/PA_FRAMEWORK/blob/main/public/logo/Screenshot%20(73).png)
   *   Ini merupakan fitur crud untuk admin dimana admin dapat melakukan penambahan dll untuk akun pengguna.
     
6.  ![Logo](https://github.com/reyyy26/PA_FRAMEWORK/blob/main/public/logo/Screenshot%20(71).png)
   *   Ini merupakan dashboard yang dimiliki oleh kasir dimana kasir yang berisi informasi dari penjualan.
     
7.  ![Logo]([https://github.com/reyyy26/PA_FRAMEWORK/blob/main/public/logo/Nyxx.png](https://github.com/reyyy26/PA_FRAMEWORK/blob/main/public/logo/Screenshot%20(70).png))
   *   Ini merupakan page untuk kasir melakukan proses penjualan.

8.  ![Logo](https://github.com/reyyy26/PA_FRAMEWORK/blob/main/public/logo/Screenshot%20(69).png)
   *   Ini merupakan dashboard utama untuk manager.

    

## Cara Berkontribusi 🤝

1.  Fork repositori ini.
2.  Buat branch untuk fitur Anda (`git checkout -b feature/FiturBaru`).
3.  Lakukan commit perubahan Anda (`git commit -am 'Menambahkan fitur baru'`).
4.  Push ke branch (`git push origin feature/FiturBaru`).
5.  Buat Pull Request.

## Pelaporan Keamanan 🔐

Temukan masalah keamanan? Laporkan ke: mreyhanafi26@gmail.com — mohon sertakan langkah reproduksi dan detail lingkungan.

## Lisensi 📄

MIT License
Copyright (c) 2025 reyyy26
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction...


---
README.md ini dibuat dengan ❤️ oleh reyyy26
