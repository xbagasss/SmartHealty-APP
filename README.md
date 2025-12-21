# SmartHealthy – Sistem Manajemen & Tracking Nutrisi Terpadu

**SmartHealthy** adalah platform berbasis web yang dirancang untuk membantu pengguna mencapai target kesehatan mereka melalui pemantauan nutrisi yang presisi. Aplikasi ini menggabungkan pencatatan aktivitas makan, perhitungan kebutuhan kalori otomatis, dan analisis data visual untuk memberikan pengalaman pengguna yang holistik.

---

# 📑 Daftar Isi

1.  [Tentang Proyek](#-tentang-proyek)
2.  [Fitur Unggulan](#-fitur-unggulan)
3.  [Panduan Pengguna (User Manual)](#-panduan-pengguna-user-manual)
4.  [Panduan Administrator (Admin Manual)](#-panduan-administrator-admin-manual)
5.  [Arsitektur Teknis](#-arsitektur-teknis)
6.  [Instalasi & Konfigurasi](#-instalasi--konfigurasi)
7.  [Skema Database](#-skema-database)
8.  [Troubleshooting](#-troubleshooting)

---

# 🌟 Tentang Proyek

### Visi
Menciptakan kesadaran pola makan sehat dengan alat yang mudah digunakan, akurat, dan dapat diakses oleh siapa saja.

### Lingkup Sistem
Sistem ini mencakup manajemen pengguna (User/Admin), integrasi API eksternal untuk data nutrisi, sistem rekomendasi berbasis algoritma TDEE, dan otomatisasi notifikasi email.

---

# 🚀 Fitur Unggulan

### 1. Sistem Rekomendasi Menu Cerdas (Meal Plan)
*   **Algoritma**: Menggunakan rumus **Mifflin-St Jeor** untuk menghitung BMR (Basal Metabolic Rate) dan TDEE (Total Daily Energy Expenditure).
*   **Kustomisasi**: Menyesuaikan rekomendasi berdasarkan goal pengguna (Turun Berat Badan/Diet, Pertahankan, atau Tambah Otot/Bulking).
*   **Logika**:
    *   *Diet*: TDEE - 500 kkal.
    *   *Maintain*: TDEE normal.
    *   *Muscle Gain*: TDEE + 300 kkal (Fokus Protein tinggi).

### 2. Pencarian Nutrisi Real-time
*   **Integrasi**: Terhubung langsung dengan **Edamam Nutrition API**.
*   **Data**: Menyajikan detail Kalori, Protein, Karbohidrat, dan Lemak per 100g atau per porsi.
*   **Seeder Otomatis**: Fitur "One-Click Seeding" untuk mengisi database lokal dengan daftar 20+ makanan sehat populer (Buah, Sayur, Protein).

### 3. Dashboard Analitik
*   **Visualisasi Data**: Menggunakan **Chart.js**.
*   **Line Chart**: Grafik tren kalori harian vs target.
*   **Pie Chart (Doughnut)**: Distribusi makronutrisi mingguan (Protein vs Karbo vs Lemak) untuk memantau keseimbangan diet.
*   **Weekly Insights**: Memberikan saran otomatis jika rata-rata kalori terlalu tinggi atau rendah.

### 4. Sistem Pengingat (Daily Reminder)
*   **Otomatisasi**: Script cron job untuk mendeteksi pengguna yang belum mencatat makanan hari ini.
*   **Notifikasi**: Mengirim email motivasi via **PHPMailer** (SMTP Gmail) untuk menjaga konsistensi pengguna.

### 5. Manajemen Role (Admin & User)
*   **User**: Akses fitur tracking, profil, dan meal plan.
*   **Admin**: Akses penuh ke dashboard admin, manajemen data pengguna (Edit/Delete/Promote Role), dan manajemen database makanan global.

---

# 📖 Panduan Pengguna (User Manual)

### Memulai (Getting Started)
1.  **Daftar Akun**: Masuk ke halaman Register, isi Nama, Email, dan Password.
2.  **Setup Profil**: Saat pertama login, isi data diri (Berat, Tinggi, Umur, Gender, Aktivitas). Sistem akan otomatis menghitung target kalori Anda.

### Mencatat Makanan
1.  Buka menu **Search**.
2.  Ketik nama makanan (misal: "Nasi Goreng" atau "Fried Rice").
3.  Pilih makanan dari hasil pencarian API.
4.  Klik **Tambahkan ke Log**. Nutrisi otomatis tersimpan dan grafik Anda akan terupdate.

### Melihat Progres
*   Buka **Dashboard** untuk melihat ringkasan cepat hari ini.
*   Buka **Analytics** untuk melihat grafik detail dan Pie Chart nutrisi mingguan.

---

# 🛠 Panduan Administrator (Admin Manual)

### Mengakses Panel Admin
Login dengan akun yang memiliki role `admin`. Anda akan otomatis diarahkan atau bisa mengakses menu "Admin Panel" di navigasi.

### Manajemen User
1.  Buka menu **Manage Users**.
2.  **Edit Role**: Klik tombol "Edit" pada user untuk mengubah status mereka menjadi Admin atau User biasa.
3.  **Hapus User**: Klik "Delete" untuk menghapus akun yang tidak aktif atau melanggar aturan.

### Manajemen Database Makanan
1.  Buka menu **Manage Foods**.
2.  **Generate Rekomendasi**: Klik tombol "⚡ Generate Rekomendasi" untuk memunculkan daftar makanan sehat default ke database global agar bisa dipilih semua user.

### Menjalankan Reminder Harian
1.  Di Dashboard Admin, cari widget **Daily Maintenance**.
2.  Klik **"🚀 Trigger Daily Email Reminders"**.
3.  Sistem akan mengecek siapa yang belum mengisi log hari ini dan mengirim email masal.

---

# 🏗 Arsitektur Teknis

Project ini dibangun dengan pola arsitektur yang terstruktur dan modular menggunakan PHP Native (OOP).

### Struktur Folder
```
Project/
├── public/                 # Entry point aplikasi (Web Server Root)
│   ├── css/                # Stylesheets
│   ├── js/                 # Client-side scripts (Chart.js init)
│   ├── admin/              # Halaman-halaman khusus Admin
│   ├── foods/              # Halaman CRUD Makanan
│   └── cron/               # Script otomatisasi (Reminder)
│
├── src/                    # Backend Logic
│   ├── Config/             # Koneksi Database Wrapper
│   ├── Models/             # Data Access Objects (User, Food, Log)
│   └── Services/           # Business Logic Layer
│       ├── AnalyticsService.php    # Logika statistik & query kompleks
│       ├── EmailTemplateService.php # Template HTML email
│       ├── NotificationService.php  # Wrapper PHPMailer
│       └── ReminderService.php      # Logika deteksi user inaktif
│
└── vendor/                 # Dependencies (Composer)
```

### Pola Desain (Design Patterns)
*   **Service Layer**: Logika bisnis (seperti hitung kalori, kirim email) dipisah dari File View (`public/`) agar kode lebih bersih dan *reusable*.
*   **Singleton/Dependency Injection**: Digunakan pada koneksi Database untuk efisiensi resource.

---

# ⚙ Instalasi & Konfigurasi

### Persyaratan Sistem
*   PHP >= 8.0
*   MySQL / MariaDB
*   Composer
*   Web Server (Apache/Nginx)

### Langkah Instalasi
1.  **Clone Repository**
    ```bash
    git clone https://github.com/xbagasss/Back-end-Final-project.git
    cd Back-end-Final-project
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    ```

3.  **Setup Environment**
    *   Copy `.env.example` ke `.env`.
    *   Isi konfigurasi database dan email (SMTP).
    *   Lihat file `SETUP_EMAIL.md` untuk panduan detail App Password Gmail.

4.  **Import Database**
    *   Jalankan file SQL yang disediakan (jika ada) atau biarkan aplikasi membuat tabel saat inisialisasi (tergantung konfigurasi).

---

# 🗄 Skema Database

### Tabel: `users`
| Kolom | Tipe | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK) | Auto Increment |
| `name` | VARCHAR | Nama Lengkap |
| `email` | VARCHAR | Unique Email |
| `role` | ENUM | `'user'`, `'admin'` (Default: 'user')|
| `daily_calorie_goal`| INT | Target kalori harian (Hasil Kalkulasi) |

### Tabel: `foods`
| Kolom | Tipe | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK) | Auto Increment |
| `name` | VARCHAR | Nama Makanan |
| `calories, protein...`| INT | Nilai Makro per porsi |
| `created_by` | INT (FK)| ID User pembuat (atau Admin) |

### Tabel: `nutrition_logs`
| Kolom | Tipe | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK) | Auto Increment |
| `user_id` | INT (FK)| Pemilik log |
| `date` | DATE | Tanggal pencatatan |
| `food_name` | VARCHAR | Snapshot nama makanan |

---

# 🔧 Troubleshooting

### Masalah Email
*   **Error**: "SMTP connect() failed"
*   **Solusi**: Pastikan Anda menggunakan **App Password** Gmail (16 digit), bukan password login biasa. Cek juga apakah ekstensi `openssl` aktif di `php.ini`.

### Masalah Grafik
*   **Error**: Grafik tidak muncul di Dashboard/Analytics.
*   **Solusi**: Pastikan Anda memiliki koneksi internet karena library `Chart.js` diload dari CDN.

### Masalah Login Admin
*   **Error**: Tidak bisa akses halaman admin.
*   **Solusi**: Minta admin lain mengubah role Anda, atau edit manual di database (Tabel `users`, kolom `role` ubah jadi `'admin'`).
