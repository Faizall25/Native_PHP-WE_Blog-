Berikut adalah versi terbaru dan menarik dari file **README** untuk proyek **WE Blog!** dalam bahasa **Indonesia**, disesuaikan agar ramah pengguna dan mudah dipahami oleh developer maupun pengguna akhir:

---

# 🌐 WE Blog! — Platform Blogging Dinamis

Selamat datang di **WE Blog!**, platform blogging modern dan responsif yang memudahkan Anda untuk menulis, mengelola, dan membagikan artikel secara fleksibel. Baik Anda seorang penggemar teknologi, penulis gaya hidup, atau blogger pemula, WE Blog! memberikan pengalaman yang bersih dan fungsional. Proyek ini menggunakan database **MySQL (`we_blog`)** dan dikembangkan menggunakan PHP.

📖 README ini akan membantu Anda memahami fitur, cara instalasi, penggunaan, serta berkontribusi dalam proyek ini. Yuk, mulai eksplorasinya!

---

## 📌 Daftar Isi

* [✨ Fitur Unggulan](#-fitur-unggulan)
* [🔧 Prasyarat](#-prasyarat)
* [🚀 Instalasi](#-instalasi)
* [🗄️ Setup Database](#-setup-database)
* [🧪 Cara Menggunakan](#-cara-menggunakan)
* [📁 Struktur Folder](#-struktur-folder)
* [🤝 Kontribusi](#-kontribusi)
* [📄 Lisensi](#-lisensi)
* [📬 Dukungan](#-dukungan)

---

## ✨ Fitur Unggulan

* 🔐 **Autentikasi Pengguna**: Login & register dengan sistem keamanan berbasis hash.
* 📝 **Penulisan Artikel**: Tulis artikel lengkap dengan judul, konten, gambar, dan kategori.
* 👨‍💼 **Manajemen Penulis**: Penulis ditautkan otomatis ke akun pengguna.
* 🗂️ **Kategori Fleksibel**: Artikel bisa diklasifikasikan ke berbagai kategori.
* 👤 **Dashboard Profil**: Kelola artikel Anda dengan fitur edit & hapus langsung.
* 📱 **Desain Responsif**: Tampilan ramah mobile dengan desain berbasis kartu.
* 💾 **Penyimpanan Data**: Seluruh data disimpan di database MySQL `we_blog` dengan relasi yang rapi.

---

## 🔧 Prasyarat

Sebelum menjalankan WE Blog!, pastikan Anda memiliki:

* PHP ≥ 7.4
* MySQL ≥ 5.7
* Web Server (Apache/Nginx atau lokal: XAMPP, Laragon, dll)
* Code editor seperti Visual Studio Code
* Composer (opsional, untuk manajemen dependency)

---

## 🚀 Instalasi

### 1. Clone Repositori

```bash
git clone https://github.com/username/we-blog.git
cd we-blog
```

### 2. Konfigurasi Koneksi Database

Jika tidak ada file `.env`, langsung edit file `includes/db_connect.php`:

```php
$conn = new mysqli("localhost", "root", "password", "we_blog");
```

---

## 🗄️ Setup Database

### 1. Buat Database

Masuk ke MySQL:

```bash
mysql -u root -p
```

Buat database:

```sql
CREATE DATABASE we_blog;
USE we_blog;
```

### 2. Import Struktur Tabel

Gunakan SQL berikut:

```sql
-- Tabel user
CREATE TABLE `user` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel category
CREATE TABLE `category` (
  `category_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL
);

-- Tabel author
CREATE TABLE `author` (
  `author_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `user_id` INT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`) ON DELETE CASCADE
);

-- Tabel article
CREATE TABLE `article` (
  `article_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `publish_date` DATE NOT NULL,
  `image_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`) ON DELETE SET NULL
);

-- Tabel relasi article-author
CREATE TABLE `article_author` (
  `article_id` INT,
  `author_id` INT,
  PRIMARY KEY (`article_id`, `author_id`),
  FOREIGN KEY (`article_id`) REFERENCES `article`(`article_id`) ON DELETE CASCADE,
  FOREIGN KEY (`author_id`) REFERENCES `author`(`author_id`) ON DELETE CASCADE
);

-- Tabel relasi article-category
CREATE TABLE `article_category` (
  `article_id` INT,
  `category_id` INT,
  PRIMARY KEY (`article_id`, `category_id`),
  FOREIGN KEY (`article_id`) REFERENCES `article`(`article_id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `category`(`category_id`) ON DELETE CASCADE
);
```

---

## 🧪 Cara Menggunakan

1. **Jalankan Server**

   * Aktifkan Apache & MySQL (XAMPP, Laragon, dsb).
   * Akses di `http://localhost/we-blog`.

2. **Daftar Akun**

   * Kunjungi `register.php`, isi data lalu submit.

3. **Login**

   * Masuk ke `login.php` dengan akun terdaftar.

4. **Kelola Artikel**

   * Buka `profile.php` untuk melihat artikel.
   * Klik “Tulis Artikel Baru” untuk menambah artikel.
   * Edit atau hapus artikel langsung dari profil.

5. **Jelajahi**

   * Lihat artikel pada `index.php` dan detail di `article.php`.

---

## 📁 Struktur Folder

```
we-blog/
├── assets/
│   └── css/styles.css
├── includes/
│   ├── db_connect.php
│   ├── header.php
│   └── footer.php
├── index.php
├── login.php
├── register.php
├── create.php
├── update.php
├── profile.php
├── article.php
└── README.md
```

---

## 🤝 Kontribusi

Ingin membantu mengembangkan WE Blog? Ikuti langkah berikut:

1. Fork repositori
2. Buat branch:

   ```bash
   git checkout -b fitur/fitur-baru
   ```
3. Lakukan perubahan
4. Commit:

   ```bash
   git commit -m "Tambah fitur A"
   ```
5. Push ke GitHub
6. Buat pull request

### Pedoman

* Ikuti gaya penulisan kode yang konsisten.
* Tambahkan komentar jika perlu.
* Uji fitur sebelum mengirim PR.

---

## 📄 Lisensi

Proyek ini menggunakan lisensi **MIT**. Silakan gunakan, modifikasi, dan distribusikan sesuai ketentuan.

---

## 📬 Dukungan

Jika Anda mengalami kendala atau ingin berdiskusi:

* Buat *issue* di GitHub.
* Kontak langsung via email: **[support@weblog.local](mailto:support@weblog.local)** *(ubah sesuai kebutuhan)*.
* Diskusi di grup komunitas (jika ada).

---

**Selamat menulis dan berbagi bersama WE Blog! 🚀**
Untuk pengalaman blogging yang lebih baik dan personal.

---

Jika Anda ingin README ini juga dilengkapi dengan **badge GitHub**, link ke demo online, atau versi dalam bahasa Inggris, beri tahu saya ya!
