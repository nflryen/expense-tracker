# 💰 Dompet Sesat - Pencatat Keuangan Anak Kost

Aplikasi web sederhana untuk membantu mahasiswa dan anak kost mengelola keuangan pribadi dengan mudah dan efisien.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

## 🎯 **Fitur Utama**

### 👤 **Untuk User Biasa:**
- ✅ **Pencatatan Transaksi** - Input pemasukan dan pengeluaran dengan mudah
- ✅ **Quick Add** - Tombol cepat untuk transaksi umum (Makan, Jajan, Transport)
- ✅ **Budget Tracking** - Monitor pengeluaran bulanan dengan alert otomatis
- ✅ **Kategori Otomatis** - Sistem otomatis membuat kategori baru jika belum ada
- ✅ **Dashboard Statistik** - Ringkasan keuangan dengan visualisasi yang jelas
- ✅ **Filter & Search** - Cari transaksi berdasarkan kategori, tanggal, atau keyword
- ✅ **Laporan Keuangan** - Analisis pengeluaran per kategori dan periode
- ✅ **Responsive Design** - Bisa diakses dari desktop dan mobile

### 👨‍💼 **Untuk Admin:**
- ✅ **Kelola Users** - Tambah, edit, hapus, dan ubah role user
- ✅ **Monitor Global** - Lihat semua transaksi dari seluruh user
- ✅ **Statistik Sistem** - Dashboard dengan data agregat seluruh sistem
- ✅ **User Management** - Kontrol akses dan permission user

## 🚀 **Teknologi yang Digunakan**

- **Backend**: PHP 8+ dengan MySQLi
- **Frontend**: HTML5, CSS3, Bootstrap 5, Vanilla JavaScript
- **Database**: MySQL dengan relational design
- **Icons**: Bootstrap Icons
- **Security**: Session management, input validation, role-based access

## 📦 **Instalasi**

### **Requirements:**
- PHP 8.0 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache/Nginx)
- Browser modern dengan JavaScript enabled

### **Langkah Instalasi:**

1. **Clone repository:**
   ```bash
   git clone https://github.com/username/dompet-sesat.git
   cd dompet-sesat
   ```

2. **Setup database:**
   - Buat database MySQL baru
   - Import file `database.sql`
   ```sql
   CREATE DATABASE dompet_sesat;
   USE dompet_sesat;
   SOURCE database.sql;
   ```

3. **Konfigurasi database:**
   Edit file `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'dompet_sesat');
   define('DB_PORT', 3306);
   ```

4. **Setup web server:**
   - Arahkan document root ke folder project
   - Pastikan PHP dan MySQL service berjalan
   - Akses melalui browser: `http://localhost/dompet-sesat`

5. **Login default:**
   - **Admin**: username `admin`, password `admin123`
   - **User**: Daftar akun baru melalui halaman register

## 🎮 **Cara Penggunaan**

### **Untuk User Baru:**
1. **Daftar akun** di halaman register
2. **Login** dengan akun yang sudah dibuat
3. **Set budget bulanan** di halaman profile
4. **Mulai input transaksi** melalui dashboard atau halaman transaksi
5. **Gunakan Quick Add** untuk transaksi rutin (Makan, Jajan, Transport)
6. **Monitor budget** melalui alert di dashboard

### **Quick Add Feature:**
- Klik tombol **"🍚 Makan"** → otomatis isi Rp 20.000
- Klik tombol **"🍔 Jajan"** → otomatis isi Rp 15.000  
- Klik tombol **"🚗 Transport"** → otomatis isi Rp 10.000
- Semua field terisi otomatis, tinggal klik "Simpan"

### **Budget Alert System:**
- 🟢 **Hijau**: Pengeluaran < 75% budget
- 🟡 **Kuning**: Pengeluaran 75-90% budget  
- 🔴 **Merah**: Pengeluaran > 90% budget

## 📊 **Struktur Database**

### **Tabel Utama:**
- **`users`** - Data pengguna dan role
- **`transactions`** - Record semua transaksi
- **`categories`** - Kategori transaksi (default + custom user)

### **Relasi:**
- 1 User → Many Transactions
- 1 Category → Many Transactions
- Cascade delete untuk data integrity

## 🔧 **Fitur Teknis Unggulan**

### **Smart Redirect System:**
- Otomatis kembali ke halaman asal setelah input transaksi
- 3-layer fallback: hidden field → HTTP_REFERER → default

### **Intelligent Category Matching:**
- 3-stage algorithm: exact match → partial match → mapping match
- Auto-select kategori yang sesuai di Quick Add

### **Enhanced User Experience:**
- Toast notification untuk feedback
- Modal yang reliable (semua cara close berfungsi)
- Form validation dengan visual feedback
- Responsive design untuk mobile

### **Security Features:**
- Password hashing dengan MD5
- Session-based authentication
- Role-based access control
- Input validation & sanitization
- SQL injection prevention

## 📱 **Screenshots**

### Dashboard User
![Dashboard](https://via.placeholder.com/800x400/4361ee/ffffff?text=Dashboard+User)

### Quick Add Feature  
![Quick Add](https://via.placeholder.com/800x400/f72585/ffffff?text=Quick+Add+Feature)

### Budget Alert
![Budget Alert](https://via.placeholder.com/800x400/fca311/ffffff?text=Budget+Alert+System)

## 🤝 **Kontribusi**

Project ini dibuat untuk tugas akhir mata kuliah. Kontribusi dan saran sangat diterima!

### **Tim Pengembang:**
- **Mara** - Authentication & Security
- **Syaharani** - User Interface & Frontend  
- **Aura** - Database Design & Backend
- **Rayan** - System Integration & Testing

## 📄 **Lisensi**

Project ini dibuat untuk keperluan edukasi. Silakan gunakan dan modifikasi sesuai kebutuhan.

## 🐛 **Known Issues & Future Improvements**

### **Current Limitations:**
- Chart visualization belum diimplementasi
- Export data masih manual
- Notification system masih basic

### **Planned Features:**
- 📊 Interactive charts dengan Chart.js
- 📤 Export data ke Excel/PDF
- 🔔 Email notification untuk budget alert
- 📱 Progressive Web App (PWA)
- 🌙 Dark mode theme

## 📞 **Support**

Jika ada pertanyaan atau masalah, silakan buat issue di repository ini atau hubungi tim pengembang.

---

**Dibuat dengan ❤️ untuk membantu anak kost mengelola keuangan dengan lebih baik!**

*"Dompet Sesat - Karena uang kost sering hilang entah kemana"* 😄