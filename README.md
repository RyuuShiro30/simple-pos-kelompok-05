<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Skenario Pengujian Manual RBAC

### 1. Pengujian sebagai Admin

**Langkah:**

1. Buka halaman login.
2. Masukkan akun admin.
3. Login ke sistem.
4. Buka halaman `/products`.

**Hasil yang diharapkan:**

- Login berhasil.
- Admin dapat mengakses halaman `/products`.
- Halaman produk tampil dengan normal.

### 2. Pengujian sebagai Kasir

**Langkah:**

1. Buka halaman login.
2. Masukkan akun kasir.
3. Login ke sistem.
4. Buka halaman `/products`.

**Hasil yang diharapkan:**

- Login berhasil.
- Kasir tidak memiliki akses ke halaman `/products`.
- Sistem menampilkan halaman **403 Forbidden / akses ditolak**.

### 3. Pengujian sebagai Manager

**Langkah:**

1. Buka halaman login.
2. Masukkan akun manager.
3. Login ke sistem.
4. Buka halaman `/transactions`.
5. Buka halaman `/products`.

**Hasil yang diharapkan:**

- Login berhasil.
- Manager dapat mengakses halaman `/transactions`.
- Manager tidak dapat mengakses halaman `/products`.
- Saat membuka `/products`, sistem menampilkan halaman 403 / akses ditolak.

## Proses yang Layak Dipindah ke Queued Job

**Proses yang dipilih:** Export laporan penjualan ke Excel (SalesReportExport).

**Kondisi sekarang:** Saat admin menekan tombol Export Excel, file dibuat langsung di request itu juga. Browser menunggu sampai file selesai dibuat baru bisa download.

**Alasan layak dipindah ke queue:**
1. Lambat kalau data besar. Semakin banyak transaksi, semakin lama file dibuat, dan bisa melewati batas waktu PHP.
2. Pengguna tidak perlu hasilnya seketika. Admin cukup diberi tahu bahwa laporan sedang dibuat, lalu mengambilnya setelah selesai.
3. Aman diulang. Kalau gagal, job bisa dicoba lagi lewat $tries dan $backoff tanpa merusak data, karena proses ini hanya membaca data dan membuat file.
4. Tidak menahan pengguna lain. Proses berat jalan di worker, jadi server tetap responsif untuk kasir yang sedang transaksi.

**Cara kerja yang diusulkan:**
1. Admin klik Export Excel, aplikasi mengirim job ke queue dan langsung menampilkan pesan "Laporan sedang diproses".
2. Worker membuat file dan menyimpannya di storage.
3. Admin mendapat link download setelah file siap.

**Hal yang perlu diperhatikan:**
- Perlu tempat menyimpan status dan lokasi file (misalnya tabel export_files).
- Perlu cara memberi tahu admin saat file siap (notifikasi atau halaman daftar export).
- File lama perlu dibersihkan secara berkala.
- Worker harus berjalan (php artisan queue:work), dan pakai QUEUE_CONNECTION=database.

**Proses lain yang tidak perlu di-queue:** Menyimpan transaksi di kasir tetap harus langsung (sinkron), karena kasir butuh kepastian transaksi berhasil dan stok langsung berkurang saat itu juga.