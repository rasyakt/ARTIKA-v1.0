# BUKU PANDUAN PENGGUNA ARTIKA POS

**Sistem Manajemen Kasir Pintar (Smart Point of Sale System)**

---

## 1. PENGENALAN SISTEM (UNTUK PEMULA)

Selamat datang di ARTIKA POS! Ini adalah aplikasi berbasis web yang akan membantu Anda mencatat penjualan, mengelola barang di gudang, dan melihat keuntungan toko secara otomatis.
Karena berbasis web, Anda hanya perlu membuka aplikasi peramban (seperti Google Chrome atau Mozilla Firefox) lalu mengetikkan alamat website toko Anda untuk mulai bekerja. Tidak perlu menginstal aplikasi rumit di komputer Anda.

---

## 2. CARA LOGIN (MASUK APLIKASI)

Karena ARTIKA POS memisahkan tugas tiap pegawai, pintu masuk (halaman login) juga dibedakan berdasarkan pekerjaan Anda:

1. **Untuk Kasir (Paling Sering Digunakan):**
    - Buka alamat website, contoh: `artika.smkn1ciamis.id/login`
    - Masukkan _Username_ (atau NIS/NIP) dan _Password_ Anda.
    - Klik tombol **LOGIN**. Anda akan langsung dibawa ke layar mesin kasir interaktif.

2. **Untuk Pemilik Toko / Admin / Manager:**
    - Buka alamat website ditambah /admin: `artika.smkn1ciamis.id/login/admin`
    - Ini adalah pintu masuk untuk melihat laporan, mendaftar barang baru, atau mengganti setting.

3. **Untuk Petugas Gudang:**
    - Buka alamat ditambah /warehouse: `artika.smkn1ciamis.id/login/warehouse`
    - Anda hanya akan melihat menu yang berhubungan dengan stok barang.

---

## 3. PANDUAN KASIR (CARA MELAYANI PEMBELI)

Ini adalah fitur utama yang akan dipakai setiap hari di meja kasir.

### Langkah 1: Memilih Barang

1. Setelah login, Anda akan melihat susunan kotak-kotak barang (Katalog Produk).
2. Anda bisa **mengklik gambar barang** tersebut, ATAU **mengetik nama barang** di kotak pencarian, ATAU langsung **mengarahkan alat pemindai (Barcode Scanner)** ke barang fisik.
3. Barang akan otomatis masuk ke "Keranjang Belanja" di sebelah kanan layar.
4. Jika pembeli membeli 3 sabun yang sama, klik saja sabun tersebut 3 kali.

### Langkah 2: Proses Pembayaran

1. Lihat total harga di keranjang sebelah kanan.
2. Klik tombol **Bayar (Pay)** berwarna biru.
3. Sebuah jendela baru (Pop-up) akan muncul.
4. Masukkan **Uang yang Diterima** dari pembeli. Sistem otomatis menghitung Uang Kembalian.
5. (Opsional) Jika pembeli pamer struk transfer/QRIS dari HP-nya, Anda bisa memfotonya dan mengunggah (upload) fotonya di kolom **Bukti Pembayaran (Payment Proof)**.

### Langkah 3: Mencetak Struk

1. Setelah klik **Konfirmasi Pembayaran**, transaksi dinyatakan sukses dan masuk ke laporan pusat.
2. Layar struk akan muncul. Klik tombol **Print Receipt** untuk mulai menge-print.
3. Klik "Selesai / Transaksi Baru" untuk membersihkan layar dan siap melayani pembeli berikutnya.

---

## 4. PANDUAN ADMIN: MENGELOLA BARANG & HARGA

Hanya akun level Admin/Superadmin yang bisa melakukan ini.

### Menambah Barang Baru untuk Dijual

1. Login lewat jalur `/login/admin`
2. Di menu kiri, klik **Manajemen Produk (Product)** -> **Daftar Produk**.
3. Di pojok kanan atas, klik tombol **"Tambah Produk"** (atau ikon + Tambah).
4. Isi formulir yang muncul:
    - **Nama Produk:** Pisang Goreng Keju
    - **Harga Beli (Modal):** Rp2.000
    - **Harga Jual:** Rp5.000 _(Keuntungan dihitung otomatis oleh sistem)_
    - **Stok:** Masukkan jumlah barang yang ada saat ini.
    - **Barcode:** Ketik angka di bawah garis barcode barang, atau klik tombol pemindai acak jika tidak ada barcode fisik.
5. Klik **Simpan (Save)**. Barang kini muncul di layar Kasir!

### Update / Mengecek Stok Menipis

1. Lihat di **Dashboard** (halaman depan admin), sistem akan memberi peringatan **"Low Stock"** (Stok Menipis) berwarna merah muda untuk barang yang hampir habis.
2. Klik menu **Inventory** -> **Manajemen Stok**.
3. Cari nama barangnya, lalu klik tombol "Tambah Stok" untuk mencatat masuknya barang baru dari supplier/pabrik.

---

## 5. PANDUAN SUPERADMIN: MENGATUR PEGAWAI & SISTEM

Ini adalah wewenang tertinggi. Hati-hati dalam mengubah data di area ini.

### Cara Menambah Pegawai/Kasir Baru

1. Di menu kiri (Admin Panel), cari pengaturan **Manajemen User (Users)**.
2. Klik **Tambah User**.
3. Masukkan biodata. Yang paling penting adalah kolom **Role**. Pilih **"Cashier"** jika ia akan berjaga di depan, atau **"Warehouse"** jika hanya di gudang.
4. Jangan lupa tentukan Password awal. Beritahukan password ini ke pegawai tersebut.

---

## 6. PANDUAN MEMBACA LAPORAN (UNTUK MANAGER/PEMILIK)

Jangan biarkan toko berjalan tanpa arah, cek laporannya setiap tutup warung.

1. Di menu kiri, buka **Laporan (Reports)** -> **Laporan Kasir**.
2. Anda akan melihat tabel panjang berisi seluruh kejadian jual-beli hari itu.
3. Anda bisa melihat **Tipe Pembayaran** (Cash / Transfer) dan **Statusnya** (Sukses / Refund).
4. **Mengecek Foto Transfer:** Klik tombol mata / Detail (view) pada transaksi yang pembayarannya non-tunai. Foto mutasi ATM/QRIS yang difoto kasir akan terlihat di sini sebagai bukti.
5. **Membatalkan Transaksi (Refund):** Jika kasir salah ketik, Manager berhak menekan tombol hapus/batal. Uang akan dikurangi dari laba harian, dan barang fisik otomatis kembali utuh di dalam sistem gudang.

---

## 7. INFO TAMBAHAN (PERTANYAAN UMUM / FAQ)

- **Q: Apakah mesin kasir harus di-reload / F5 jika admin baru saja mengubah harga?**
  A: Betul. Demi kecepatan, kasir perlu memuat ulang (refresh) perambannya agar harga baru tertarik dari server.
- **Q: Mesin Kasir di HP Android terlalu kecil?**
  A: Tampilan ARTIKA POS bisa otomatis menyesuaikan dari Tablet Android hingga Komputer Monitor besar. Semuanya mulus dan responsif.
- **Q: Kenapa saat kasir mau hapus menu yang batal tiba-tiba dilarang?**
  A: Keamanan. Hanya Admin/Manager yang berhak membatalkan (membongkar/void) total transaksi setelah nota terbit untuk mencegah kecurangan.

**Pusat Informasi & Dukungan Teknis**
Dikembangkan khusus untuk: ARTIKA SMKN 1 CIAMIS | Versi 2026
