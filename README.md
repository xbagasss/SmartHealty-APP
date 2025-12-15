# SmartHealthy – Web Sistem Manajemen Nutrisi

Dokumentasi resmi untuk struktur project, alur kerja, instalasi, dan hubungan antar komponen dalam aplikasi **SmartHealthy**.

---

# � Daftar Isi

1. [Tentang Aplikasi](#-tentang-aplikasi)
2. [Fitur Utama](#-fitur-utama)
3. [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
4. [Instalasi & Konfigurasi](#-instalasi--konfigurasi)
5. [Struktur Direktori](#-struktur-direktori)
6. [Skema Database](#-skema-database)
7. [Penjelasan Komponen](#-penjelasan-komponen)
8. [Alur Kerja](#-alur-kerja)

---

# � Tentang Aplikasi

**SmartHealthy** adalah aplikasi berbasis web untuk memantau asupan nutrisi harian. Aplikasi ini memungkinkan pengguna untuk mencatat makanan harian, mendapatkan rekomendasi menu berdasarkan target kalori, dan melihat statistik konsumsi nutrisi mereka.

---

# 🚀 Fitur Utama

*   **Manajemen Akun User**: Registrasi, Login, dan Pengaturan Profil (Berat Badan, Tinggi Badan, Target Kalori).
*   **Pencarian Nutrisi Makanan**: Integrasi dengan **Edamam API** untuk mencari kandungan nutrisi makanan secara realtime.
*   **Pencatatan Makanan Harian (Food Logging)**: Mencatat apa yang dimakan hari ini dan menghitung total kalori otomatis.
*   **Analisis Nutrisi**: Menampilkan grafik asupan kalori harian dan mingguan.
*   **Rekomendasi Menu (Meal Plan)**: Sistem cerdas yang merekomendasikan menu makanan harian sesuai dengan TDEE (Total Daily Energy Expenditure) pengguna.
*   **Notifikasi Email**: Pengingat otomatis untuk mencatat makanan (menggunakan PHPMailer).

---

# 🛠 Teknologi yang Digunakan

*   **Bahasa Pemrograman**: PHP 8.x (Native)
*   **Database**: MySQL / MariaDB
*   **Frontend**: HTML5, CSS3, JavaScript (Vanilla), Chart.js (untuk grafik)
*   **Dependencies (Composer)**:
    *   `phpmailer/phpmailer`: Pengiriman email.
    *   `vlucas/phpdotenv`: Manajemen environment variable.

---

# ⚙ Instalasi & Konfigurasi

Ikuti langkah berikut untuk menjalankan aplikasi di lokal (XAMPP/Laragon):

### 1. Clone Repository

```bash
git clone https://github.com/xbagasss/Back-end-Final-project.git
cd Back-end-Final-project
```

### 2. Install Dependencies

Pastikan **Composer** sudah terinstall, lalu jalankan:

```bash
composer install
```

### 3. Konfigurasi Database

1.  Buat database baru di MySQL, misalnya `nutrition_db`.
2.  Import file database (jika ada) atau pastikan tabel dibuat sesuai [Skema Database](#-skema-database) di bawah.

### 4. Konfigurasi Environment

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Lalu edit file `.env` sesuaikan dengan konfigurasi lokal Anda:

```env
DB_HOST=localhost
DB_NAME=nutrition_db
DB_USER=root
DB_PASS=

# Konfigurasi API Edamam (Untuk fitur search nutrition)
EDAMAM_APP_ID=your_app_id
EDAMAM_APP_KEY=your_app_key

# Konfigurasi SMTP (Untuk notifikasi email)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=email@anda.com
SMTP_PASS=password_aplikasi
```

### 5. Jalankan Aplikasi

Buka browser dan akses:

```
http://localhost/Back-end-Final-project/public/index.php
```

---

# 📁 Struktur Direktori

```
SmartHealthy/
├── src/
│   ├── Config/       # Konfigurasi Database
│   ├── Models/       # Representasi Tabel Database (CRUD)
│   ├── Services/     # Business Logic & Integrasi API
│
├── public/           # File yang diakses User (Frontend/Views)
│   ├── index.php     # Landing Page
│   ├── dashboard.php # Halaman Utama User
│   ├── api/          # (Opsional) Endpoint AJAX
│
├── vendor/           # Library Composer
├── .env              # Konfigurasi Sensitif
└── .gitignore        # Daftar file yang diabaikan Git
```

---

# 🗄 Skema Database

Berikut adalah tabel utama yang digunakan dalam aplikasi:

### 1. `users`
Menyimpan data pengguna dan target kesehatan mereka.
*   `id` (INT, PK, AI)
*   `name` (VARCHAR)
*   `email` (VARCHAR, Unique)
*   `password` (VARCHAR)
*   `height`, `weight`, `age` (INT)
*   `daily_calorie_goal` (INT)

### 2. `foods`
Database makanan lokal yang bisa dipilih user.
*   `id` (INT, PK, AI)
*   `name` (VARCHAR)
*   `calories`, `protein`, `carbs`, `fat` (INT)
*   `created_by` (INT, FK -> users.id)

### 3. `nutrition_logs`
Catatan riwayat makan user per hari.
*   `id` (INT, PK, AI)
*   `user_id` (INT, FK -> users.id)
*   `food_name` (VARCHAR)
*   `calories` (INT)
*   `date` (DATE)

---

# 🔍 Penjelasan Komponen

## **1. src/Models/**

Model bertanggung jawab untuk komunikasi langsung dengan database.
*   **User.php**: Menangani autentikasi dan profil user.
*   **Food.php**: CRUD untuk data makanan.
*   **NutritionLog.php**: Mencatat dan merekap kalori harian.

## **2. src/Services/**

Layer logic yang memisahkan kode kompleks dari Controller/View.
*   **AuthService**: Validasi login/register.
*   **NutritionApiClient**: Mengambil data dari **Edamam API**.
*   **MealRecommendationService**: Algoritma penghitung kebutuhan kalori (TDEE).
*   **NotificationService**: Mengirim email pengingat makan.

## **3. public/**

Bagian Interface yang berinteraksi dengan user.
*   **`search_nutrition.php`**: Menggunakan Javascript untuk memanggil API internal yang kemudian meneruskan request ke Edamam.
*   **`dashboard.php`**: Menampilkan grafik chart.js berdasarkan data dari `AnalyticsService`.

---

# 🔗 Alur Kerja

**Contoh: User Mencari Makanan**

```
User (Browser)
   │
   ▼
public/search_nutrition.php (Input Query)
   │
   ▼
src/Services/NutritionApiClient.php
   │
   │ (Request HTTP)
   ▼
Edamam API (External)
   │
   │ (JSON Response)
   ▼
NutritionApiClient.php (Parsing Data)
   │
   ▼
public/search_nutrition.php (Tampil Hasil)
```

**Contoh: User Menyimpan Log Makanan**

```
User Klik "Simpan"
   │
   ▼
public/save_food.php
   │
   ▼
src/Models/NutritionLog.php (Insert DB)
   │
   ▼
Database (Tabel nutrition_logs)
```
