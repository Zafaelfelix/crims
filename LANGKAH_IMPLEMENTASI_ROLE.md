# Langkah-Langkah Implementasi Role Mahasiswa dan Dosen

## 📋 Daftar Langkah

### **LANGKAH 1: Update Database (WAJIB DILAKUKAN PERTAMA)**

1. Buka phpMyAdmin di browser (http://localhost/phpmyadmin)
2. Pilih database `crims_db`
3. Klik tab **SQL** di bagian atas
4. Copy semua isi dari file `add_user_roles.sql` yang sudah dibuat
5. Paste ke textarea SQL di phpMyAdmin
6. Klik tombol **Go** atau **Jalankan**
7. Pastikan tidak ada error (harus muncul "Query OK")

**Yang dilakukan:**
- Menambahkan kolom `role`, `full_name`, `email` di tabel `users`
- Menambahkan kolom `created_by` di tabel `news_items`, `project_items`, `achievement_items`
- Update user yang sudah ada menjadi role 'admin'

---

### **LANGKAH 2: Update Login Process**

File yang akan diupdate: `login/process_login.php`

**Perubahan:**
- Query SELECT harus mengambil kolom `role` dan `full_name`
- Simpan `role` ke session: `$_SESSION['role']`
- Redirect sesuai role:
  - `admin` → `/crims/admin/dashboard.php`
  - `dosen` → `/crims/dosen/dashboard.php`
  - `mahasiswa` → `/crims/mahasiswa/dashboard.php`

---

### **LANGKAH 3: Buat Halaman Admin untuk Manage Users**

File baru: `admin/users.php`

**Fitur:**
- Tampilkan daftar semua users (admin, dosen, mahasiswa)
- Form untuk tambah user baru (username, password, role, full_name, email)
- Form untuk edit user
- Tombol delete user
- Validasi: admin tidak bisa dihapus, minimal 1 admin harus ada

**Akses:** Hanya role `admin` yang bisa akses halaman ini

---

### **LANGKAH 4: Buat Dashboard untuk Dosen**

File baru: `dosen/dashboard.php`, `dosen/dosen_layout.php`

**Fitur:**
- Layout mirip admin tapi warna berbeda (misal: hijau/biru untuk dosen)
- Menu: Dashboard, Prestasi, Berita, Proyek
- Statistik: jumlah prestasi, berita, proyek yang dibuat oleh dosen tersebut
- List prestasi, berita, proyek yang dibuat oleh dosen tersebut

**Warna tema:** Bisa menggunakan hijau (#10b981) atau biru (#3b82f6)

---

### **LANGKAH 5: Buat Dashboard untuk Mahasiswa**

File baru: `mahasiswa/dashboard.php`, `mahasiswa/mahasiswa_layout.php`

**Fitur:**
- Layout mirip admin tapi warna berbeda (misal: orange/kuning untuk mahasiswa)
- Menu: Dashboard, Prestasi, Berita, Proyek
- Statistik: jumlah prestasi, berita, proyek yang dibuat oleh mahasiswa tersebut
- List prestasi, berita, proyek yang dibuat oleh mahasiswa tersebut

**Warna tema:** Bisa menggunakan orange (#f59e0b) atau kuning (#eab308)

---

### **LANGKAH 6: Update Admin Pages untuk Menyimpan created_by**

File yang akan diupdate:
- `admin/news.php` - saat insert/update, simpan `created_by = $_SESSION['user_id']`
- `admin/projects.php` - saat insert/update, simpan `created_by = $_SESSION['user_id']`
- `admin/achievements.php` - saat insert/update, simpan `created_by = $_SESSION['user_id']`

**Perubahan di query INSERT/UPDATE:**
```php
// Sebelumnya
INSERT INTO news_items (title, summary, ...) VALUES (?, ?, ...)

// Sesudahnya
INSERT INTO news_items (title, summary, ..., created_by) VALUES (?, ?, ..., ?)
```

---

### **LANGKAH 7: Buat Halaman untuk Dosen (Prestasi, Berita, Proyek)**

File baru:
- `dosen/achievements.php` - CRUD prestasi (hanya yang dibuat dosen tersebut)
- `dosen/news.php` - CRUD berita (hanya yang dibuat dosen tersebut)
- `dosen/projects.php` - CRUD proyek (hanya yang dibuat dosen tersebut)

**Fitur:**
- Hanya bisa lihat/edit/hapus data yang dibuat oleh dosen tersebut
- Bisa tambah data baru (otomatis `created_by` = user_id dosen tersebut)

---

### **LANGKAH 8: Buat Halaman untuk Mahasiswa (Prestasi, Berita, Proyek)**

File baru:
- `mahasiswa/achievements.php` - CRUD prestasi (hanya yang dibuat mahasiswa tersebut)
- `mahasiswa/news.php` - CRUD berita (hanya yang dibuat mahasiswa tersebut)
- `mahasiswa/projects.php` - CRUD proyek (hanya yang dibuat mahasiswa tersebut)

**Fitur:**
- Hanya bisa lihat/edit/hapus data yang dibuat oleh mahasiswa tersebut
- Bisa tambah data baru (otomatis `created_by` = user_id mahasiswa tersebut)

---

### **LANGKAH 9: Update Halaman Detail untuk Menampilkan Uploader**

File yang akan diupdate:
- `news_detail.php` - ambil data user dari `created_by`, tampilkan nama dan role
- `project_detail.php` - ambil data user dari `created_by`, tampilkan nama dan role
- `achievement_detail.php` - ambil data user dari `created_by`, tampilkan nama dan role

**Query:**
```php
// Ambil data user yang membuat
$uploaderQuery = $mysqli->prepare('SELECT username, full_name, role FROM users WHERE id = ?');
$uploaderQuery->bind_param('i', $item['created_by']);
$uploaderQuery->execute();
$uploader = $uploaderQuery->get_result()->fetch_assoc();
```

**Tampilan:**
- Jika `created_by` ada: tampilkan nama dan role (Dosen/Mahasiswa/Admin)
- Jika `created_by` NULL: tampilkan "Admin" (default untuk data lama)

---

### **LANGKAH 10: Update Admin Layout untuk Check Role**

File yang akan diupdate: `admin/admin_layout.php`

**Perubahan:**
- Check session role di awal
- Jika role bukan 'admin', redirect ke dashboard sesuai role
- Atau bisa buat layout terpisah untuk admin

---

## 🎨 Warna Tema yang Disarankan

- **Admin:** Biru (#1e5ba8) - sudah ada
- **Dosen:** Hijau (#10b981) atau Biru (#3b82f6)
- **Mahasiswa:** Orange (#f59e0b) atau Kuning (#eab308)

---

## ✅ Checklist Implementasi

- [ ] Langkah 1: Update database (jalankan SQL)
- [ ] Langkah 2: Update login process
- [ ] Langkah 3: Buat halaman admin/users.php
- [ ] Langkah 4: Buat dashboard dosen
- [ ] Langkah 5: Buat dashboard mahasiswa
- [ ] Langkah 6: Update admin pages untuk created_by
- [ ] Langkah 7: Buat halaman CRUD untuk dosen
- [ ] Langkah 8: Buat halaman CRUD untuk mahasiswa
- [ ] Langkah 9: Update halaman detail
- [ ] Langkah 10: Update admin layout untuk check role

---

## 📝 Catatan Penting

1. **Backup database dulu** sebelum menjalankan SQL
2. **Test setiap langkah** sebelum lanjut ke langkah berikutnya
3. **Password default** untuk testing bisa menggunakan: `password123` (hash dengan `password_hash()`)
4. **Session security:** Pastikan setiap halaman check session dan role
5. **Access control:** Dosen dan mahasiswa hanya bisa akses data mereka sendiri

---

## 🚀 Urutan Pengerjaan yang Disarankan

1. **Langkah 1** (Database) - WAJIB PERTAMA
2. **Langkah 2** (Login) - Setelah database
3. **Langkah 3** (Admin Users) - Untuk bisa buat user baru
4. **Langkah 4 & 5** (Dashboard) - Buat layout dan dashboard
5. **Langkah 6** (Update Admin) - Update untuk simpan created_by
6. **Langkah 7 & 8** (CRUD Pages) - Buat halaman untuk dosen dan mahasiswa
7. **Langkah 9** (Detail Pages) - Update tampilan uploader
8. **Langkah 10** (Security) - Final check dan security

---

**Siap untuk mulai? Mulai dari Langkah 1 dulu ya!** 🎯


