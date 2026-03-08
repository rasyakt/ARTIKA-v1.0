# 🚀 Panduan Deployment ARTIKA POS ke VPS/Server Production

Panduan ini berisi tahapan lengkap (_Standard Operating Procedure_) untuk mengunggah dan mengonfigurasi aplikasi ARTIKA POS dari komputer lokal (_Localhost_) ke Server Internet (_Production_) menggunakan kombinasi **WinSCP (Transfer File)** dan **Git Bash SSH (Eksekusi Perintah)**.

---

## Tahap 1: Persiapan di Komputer Lokal (Laptop Anda)

Sebelum mentransfer file, kita harus membersihkan dan "membungkus" aplikasi agar siap jalan di server.

1. Buka Terminal lokal Anda di folder proyek `ARTIKA MySQL`:
2. Jalankan perintah optimasi untuk memastikan tidak ada _cache_ yang tertinggal:
    ```bash
    php artisan optimize:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```
3. Bangun _asset_ _frontend_ Vite ke versi produksi (ini wajib agar CSS & JS terbaca di server):
    ```bash
    npm run build
    ```
4. Buka **File Explorer**, masuk ke folder `ARTIKA MySQL`.
5. Blok semua file proyek, **KECUALI** folder `vendor` dan `node_modules` (Karena kedua folder ini sangat berat dan berisi puluhan ribu file. Kita akan mengunduhnya langsung dari dalam server nanti agar lebih stabil).
6. Klik kanan -> _Compress to ZIP file_. Beri nama `artika-pos-release.zip`.

---

## Tahap 2: Transfer File Menggunakan WinSCP

1. Buka aplikasi **WinSCP** dan Login menggunakan IP Server, _Username_ (biasanya `root` atau `ubuntu`), dan _Password/SSH Key_ Anda.
2. Di panel sebelah kanan (Server), arahkan ke direktori publikasi web Server Anda. (Biasanya lokasinya di `/var/www/html/` atau `/var/www/artika`).
3. Dari panel sebelah kiri (Komputer Anda), **Drag & Drop** file `artika-pos-release.zip` ke panel kanan (Server).
4. Tunggu hingga proses unggah ZIP selesai 100%.

---

## Tahap 3: Eksekusi Konfigurasi Server via SSH (Git Bash)

Buka aplikasi **Git Bash**, ketikkan perintah berikut untuk masuk ke server:
`ssh username_anda@ip_server_anda` (Lalu masukkan pasword server).

1. **Masuk ke folder proyek yang baru saja diunggah:**

    ```bash
    cd /var/www/html/artika
    ```

2. **Ekstrak file ZIP di server:**

    ```bash
    unzip artika-pos-release.zip
    ```

    _(Setelah terekstrak, Anda bisa menghapus zipnya: `rm artika-pos-release.zip`)_

3. **Install Dependensi PHP (Tanpa package Testing):**

    ```bash
    composer install --optimize-autoloader --no-dev
    ```

4. **Kopi & Atur File Konfigurasi (Environment):**

    ```bash
    cp .env.example .env
    ```

    _Buka file `.env` (misal dengan `nano .env`) lalu ubah koneksi database (DB_DATABASE, DB_USERNAME, DB_PASSWORD) sesuai dengan database MySQL yang sudah Anda buat di server._

5. **Generate Kunci Enkripsi Aplikasi:**

    ```bash
    php artisan key:generate
    ```

6. **Migrasi Database & Tambahkan Akun Awal (Seeder):**

    ```bash
    php artisan migrate --force
    ```

    _(Opsional: Jika ini rilis pertama dan butuh akun SuperAdmin bawaan: `php artisan db:seed --force`)_

7. **Tautkan Folder Storage (Untuk Foto/File Upload):**

    ```bash
    php artisan storage:link
    ```

8. **Hak Akses Folder (Sangat Penting untuk Laravel):**
   Server butuh izin menulis ke folder _cache_ dan _storage_. Karena server Anda **tidak mengizinkan `sudo`**, biasanya Anda tidak perlu menjalankan perintah linux chown/chmod jika Anda menggunakan _Shared Hosting / cPanel_, karena _user_ Anda secara _default_ sudah memiliki hak milik.

    Namun jika website terkena error _"Permission Denied"_, Anda dapat mengubah izin akses (_chmod_) secara visual lewat GUI **WinSCP**:
    - Klik kanan folder `storage` di panel kanan WinSCP -> Pilih **Properties** (atau tekan F9) -> Centang kotak _Write_ (W) untuk _Owner_, _Group_ dan _Others_ atau cukup ketik angka oktal `0775` -> OK.
    - Lakukan hal yang sama persis untuk folder `bootstrap/cache`.

---

## Tahap 4: SOP Maintenance & Perbaikan Rutin

Saat aplikasi sudah online, jika Anda menemukan _error_ atau ada perbaikan kode (_update_ fitur) yang selesai Anda kerjakan di lokal, berikut cara menerapkannya ke Server beroperasi:

### A. Memasuki Mode Perawatan (_Down for Maintenance_)

Agar pengguna (Kasir/Admin) tidak melakukan transaksi saat Anda sedang mereparasi web (mencegah data korup), tutup garasi web sementara:

```bash
cd /var/www/html/artika
php artisan down --secret="koderaRahasia123"
```

_(Hanya Anda yang memegang URL `anda.com/koderaRahasia123` yang bisa menembus halaman maintenance untuk mengetes perbaikan)._

### B. Mengunggah Perbaikan (Bugfix)

Apakah Anda harus menghapus file/folder lama di server dulu saat ingin mengunggah file revisi?
**Jawabannya: TIDAK PERLU DIHAPUS.** Ekstraktor ZIP di Server Linux sangat pintar, Anda hanya perlu melakukan _Overwrite_ (Penimpaan) ke file yang sudah ada.

> **Opsi 1 (Skala Kecil - 1 sampai 5 File):**
> Jika perbaikannya cuma beberapa file (Misal `TransactionService.php` yang _error_), cari file yang bersangkutan di laptop Anda lalu **_Drag Drop_** ke panel WinSCP yang tujuannya sama! Tidak men-ZIP dari awal dan tidak perlu dihapus manual, cukup klik _Yes_ saat WinSCP mengkonfirmasi operasi _"Overwrite / Replace"_.

> **Opsi 2 (Skala Besar - Ratusan File):**
> Jika perbaikannya menyentuh banyak tempat folder:
>
> 1. Buat ulang file kompres file `artika-pos-release.zip` baru dari lokal (Tetap abaikan folder `vendor` & `node_modules`).
> 2. Pindahkan/Drag Drop `artika-pos-release.zip` baru tersebut ke server lewat WinSCP, timpa _zip_ lama.
> 3. Jalankan `unzip -o artika-pos-release.zip` di Git Bash SSH.
>    PENTING: Wajib tambahkan lambang `-o`. Huruf `-o` berarti _Overwrite otomatis_, sistem tidak akan error bertabrakan / menolak menumpuk file lama, ia justru akan murni meniban kode yang kadaluarsa dengan kode perbaikan baru. Lepaskan kebiasaan `rm -rf` file lama Anda karena itu berbahaya!

### C. Menjalankan Optimasi Ulang (Wajib Saat Update Berhasil)

Setiap kali Anda meniban/mengubah kode aplikasi yang sudah jalan, perintahkan server untuk menghapus ingatan lamanya:

```bash
php artisan optimize:clear
```

_(Lalu jika ada migrasi database baru/tabel baru, jalankan `php artisan migrate --force`)_.

### D. Mengakhiri Mode Maintenance (Buka Kembali Web)

Setelah Anda mengecek fitur berjalan mulus, buka kembali gerbang ARTIKA POS:

```bash
php artisan up
```
