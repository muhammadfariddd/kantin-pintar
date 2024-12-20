# 🍽️ Kantin Pintar

Kantin Pintar adalah sistem manajemen kantin modern yang memudahkan mahasiswa untuk memesan makanan dan minuman di kampus. Dengan antarmuka yang intuitif dan fitur yang lengkap, Kantin Pintar membuat pengalaman memesan makanan menjadi lebih efisien dan menyenangkan.

## ✨ Fitur Utama

- 🔐 Autentikasi multi-user (Mahasiswa & Admin)
- 🍳 Manajemen menu dengan kategori
- 🛒 Sistem pemesanan real-time
- 📱 Antarmuka responsif
- 🔔 Notifikasi status pesanan
- 📊 Dashboard admin untuk monitoring

## 🚀 Teknologi yang Digunakan

- PHP 8.3
- MySQL 8.0
- Bootstrap 5.3
- JavaScript (Vanilla)
- HTML5 & CSS3

## 📋 Prasyarat

Sebelum menginstal, pastikan sistem Anda memenuhi persyaratan berikut:

- PHP >= 8.3
- MySQL >= 8.0
- Web Server (Apache/Nginx)
- Composer (optional)

## 💻 Instalasi

1. Clone repositori

```bash
git clone https://github.com/muhammadfariddd/kantin-pintar.git
```

2. Install dependencies

```bash
composer install
```

3. Import database

```bash
mysql -u root -p kantin_pintar < kantin_pintar.sql
```

4. Run server

```bash
php -S localhost:8080
```

5. Akses aplikasi

```bash
http://localhost:8080
```

## 🎯 Penggunaan

### Akun Demo

- Admin:
  - Username: admin
  - Password: password

### Panduan Singkat

1. Login menggunakan akun yang sesuai
2. Mahasiswa dapat:
   - Melihat menu
   - Melakukan pemesanan
   - Melihat status pesanan
3. Admin dapat:
   - Mengelola menu
   - Memproses pesanan
   - Melihat laporan

## 📸 Screenshot

<table>
  <tr>
    <td><img src="assets/img/screenshot/Screenshot 2024-12-20 191128.png" alt="Login Page" width="200"/></td>
    <td><img src="assets/img/screenshot/Screenshot 2024-12-21 023545.png" alt="User Dashboard" width="200"/></td> 
    <td><img src="assets/img/screenshot/Screenshot 2024-12-21 024405.png" alt="Menu Page" width="200"/></td> 
    <td><img src="assets/img/screenshot/Screenshot 2024-12-21 023639.png" alt="Admin Dashboard" width="200"/></td>
  </tr>
</table>

## 🤝 Kontribusi

Kami sangat menghargai setiap kontribusi yang diberikan. Jika Anda ingin berkontribusi, silakan buka issue atau pull request.

## 📝 Lisensi

Didistribusikan di bawah Lisensi MIT. Lihat `LICENSE` untuk informasi lebih lanjut.

## 📞 Kontak

Jika Anda memiliki pertanyaan atau memerlukan bantuan, silakan hubungi kami di [ilhamfaridjepara@gmail.com](mailto:ilhamfaridjepara@gmail.com).

---

Terima kasih telah menggunakan Kantin Pintar. 😊
