# LAPORAN PROYEK AKHIR
**SmartHealthy - Aplikasi Pencatat Nutrisi dan Kesehatan**

---

## KATA PENGANTAR

Puji syukur kami panjatkan ke hadirat Tuhan Yang Maha Esa, karena atas berkat dan rahmat-Nya, kami dapat menyelesaikan laporan proyek akhir ini dengan judul **"SmartHealthy - Aplikasi Pencatat Nutrisi dan Kesehatan"**. Laporan ini disusun sebagai salah satu syarat untuk memenuhi tugas akhir semester, yang merupakan wujud integrasi dari empat mata kuliah inti: **Analisis dan Desain Berorientasi Objek (ADBO)**, **Pemrograman Berorientasi Objek (PBO)**, **Pemrograman Web**, dan **Basis Data**.

Penyusunan laporan dan pengembangan aplikasi ini merupakan hasil kerja keras tim dan dukungan dari berbagai pihak. Oleh karena itu, kami ingin menyampaikan rasa terima kasih dan penghargaan setinggi-tingginya kepada:
1.  **Bapak/Ibu Dosen Pengampu**, yang telah memberikan ilmu, bimbingan, dan arahan teknis selama proses perkuliahan hingga penyelesaian proyek ini.
2.  **Rekan-rekan Tim**, atas kerjasama yang solid, dedikasi waktu, dan pemikiran yang dicurahkan dalam menyelesaikan setiap tantangan teknis.
3.  **Teman-teman Seperjuangan**, yang telah memberikan semangat dan diskusi yang membangun.

Kami menyadari bahwa laporan ini masih jauh dari kesempurnaan. Oleh karena itu, segala kritik dan saran yang bersifat membangun sangat kami harapkan guna penyempurnaan di masa mendatang. Semoga laporan ini dapat memberikan manfaat bagi pembaca dan menjadi referensi untuk pengembangan sistem serupa.

<br>
<br>

## DAFTAR ISI

1.  **KATA PENGANTAR** .............................................................................. i
2.  **DAFTAR ISI** .......................................................................................... ii
3.  **BAB I: PENDAHULUAN** ........................................................................ 1
    *   3.2.1. Latar Belakang ....................................................................... 1
    *   3.2.2. Rumusan Masalah .................................................................. 2
    *   3.2.3. Tujuan Proyek ........................................................................ 2
    *   3.2.4. Manfaat Proyek ...................................................................... 3
4.  **BAB II: TINJAUAN PUSTAKA** ............................................................... 4
    *   ADBO (Analisis dan Desain Berorientasi Objek) .............................. 4
    *   PBO (Pemrograman Berorientasi Objek) ......................................... 5
    *   Pemrograman Web ......................................................................... 6
    *   Basis Data ...................................................................................... 7
5.  **BAB III: ANALISIS SISTEM** .................................................................. 8
    *   3.1. Analisis Kebutuhan ................................................................... 8
    *   3.2. Desain Sistem ........................................................................... 10
        *   3.2.5. Diagram Use Case .......................................................... 10
        *   3.2.7. Diagram Kelas (Class Diagram) ...................................... 11
        *   3.2.8. Diagram Aktivitas ............................................................. 12
        *   3.2.9. Diagram ERD .................................................................. 13
    *   3.3. Desain Antarmuka .................................................................... 14
6.  **BAB IV: IMPLEMENTASI SISTEM** ........................................................ 15
    *   4.1. PBO dan Pemrograman Web ..................................................... 15
        *   4.1.1. Struktur Kode Program .................................................... 15
        *   4.1.2. Implementasi Fitur Utama ............................................... 16
        *   4.1.3. Integrasi dengan Basis Data ............................................ 19
    *   4.2. Database .................................................................................. 20
        *   4.2.1. Struktur Tabel .................................................................. 20
        *   4.2.2. Query-Query Penting ....................................................... 21
    *   4.3. Antarmuka Pengguna (UI) ......................................................... 22
7.  **BAB V: PENGUJIAN SISTEM** ............................................................... 24
    *   5.1. Metode Pengujian ..................................................................... 24
    *   5.2. Hasil Pengujian ......................................................................... 24
    *   5.3. Evaluasi Sistem ......................................................................... 26
8.  **BAB VI: PENUTUP** ............................................................................... 27
    *   6.1. Kesimpulan ............................................................................... 27
    *   6.2. Saran ........................................................................................ 27
9.  **DAFTAR PUSTAKA** ............................................................................... 28
10. **LAMPIRAN** ............................................................................................ 29

---

## BAB I: PENDAHULUAN

### 3.2.1. Latar Belakang
Kesehatan dan kebugaran tubuh kini menjadi prioritas utama bagi masyarakat modern. Namun, kesadaran ini seringkali tidak diimbangi dengan pengetahuan yang cukup mengenai kebutuhan nutrisi harian. Banyak individu menetapkan tujuan kesehatan—baik itu menurunkan berat badan (*fat loss*), menjaga berat badan (*maintain*), atau menambah massa otot (*muscle gain*)—tanpa mengetahui secara pasti berapa jumlah kalori dan makronutrisi yang dibutuhkan tubuh mereka (TDEE - *Total Daily Energy Expenditure*).

Akibatnya, pola diet yang dijalankan seringkali tidak efektif atau bahkan berbahaya. Masalah obesitas maupun kekurangan gizi (*underweight*) masih menjadi isu kesehatan global yang serius. Diperlukan sebuah alat bantu yang tidak hanya sekadar mencatat makanan, tetapi juga bertindak sebagai "asisten kesehatan cerdas" yang mampu memberikan peringatan dini jika berat badan pengguna memasuki kategori berisiko, serta memberikan rekomendasi yang dipersonalisasi.

Proyek **SmartHealthy** ini hadir sebagai solusi teknologi web terintegrasi. Dengan memanfaatkan konsep pemrograman berorientasi objek dan analisis sistem yang matang, aplikasi ini dirancang untuk menjadi pendamping harian pengguna dalam mencapai target kesehatan mereka secara ilmiah dan terukur.

### 3.2.2. Rumusan Masalah
Berdasarkan latar belakang di atas, rumusan masalah dalam proyek ini adalah:
1.  Bagaimana merancang sistem yang mampu menghitung kebutuhan kalori (TDEE) secara otomatis dan dinamis berdasarkan perubahan data fisik pengguna?
2.  Bagaimana mengimplementasikan fitur pelacakan berat badan yang cerdas, yang mampu mendeteksi kondisi kritis seperti Obesitas atau Underweight dan memberikan peringatan yang sesuai?
3.  Bagaimana mengintegrasikan manajemen data nutrisi yang kompleks ke dalam antarmuka yang sederhana dan *user-friendly*?
4.  Bagaimana menerepkan arsitektur kode yang bersih dan modular menggunakan konsep OOP untuk memudahkan pengembangan jangka panjang?

### 3.2.3. Tujuan Proyek
Tujuan utama dari pengembangan sistem ini adalah:
1.  **Implementasi ADBO**: Menghasilkan dokumen perancangan sistem yang lengkap (UML) sebagai panduan pengembangan yang valid.
2.  **Penerapan PBO**: Membangun *backend* aplikasi yang tangguh dengan struktur Class, Object, dan Encapsulation untuk logika bisnis (seperti perhitungan BMR dan manajemen sesi).
3.  **Pengembangan Web**: Menciptakan antarmuka web interaktif yang responsif terhadap berbagai perangkat pengguna.
4.  **Manajemen Basis Data**: Merancang skema database yang efisien untuk menyimpan dan merelasikan data pengguna, log makanan, dan riwayat kesehatan.

### 3.2.4. Manfaat Proyek
**Bagi Pengguna:**
*   Dapat mengetahui kebutuhan kalori harian yang akurat tanpa perhitungan manual.
*   Mendapatkan visualisasi kemajuan diet melalui grafik dan indikator warna.
*   Menerima peringatan dini (*early warning system*) terkait status berat badan berdasarkan BMI.

**Bagi Akademisi & Pengembang:**
*   Sebagai studi kasus penerapan *full-stack development* dengan PHP native.
*   Mendemonstrasikan bagaimana teori normalisasi database diterapkan pada kasus nyata pencatatan nutrisi.

---

## BAB II: TINJAUAN PUSTAKA

### ADBO (Analisis dan Desain Berorientasi Objek)
Pendekatan ADBO digunakan untuk membedah kompleksitas sistem menjadi komponen-komponen objek yang lebih kecil dan terkelola.
*   **Unified Modeling Language (UML)**: Standar visualisasi yang digunakan.
    *   **Use Case Diagram**: Memodelkan fungsionalitas sistem dari sudut pandang pengguna (User vs Admin).
    *   **Class Diagram**: Memodelkan struktur data statis dan relasi antar kelas dalam kode program.
    *   **Sequence Diagram**: (Opsional) Menggambarkan interaksi objek dalam urutan waktu tertentu.

### PBO (Pemrograman Berorientasi Objek)
Proyek ini sepenuhnya dibangun di atas paradigma OOP.
*   **Class & Object**: Logika aplikasi dibungkus dalam kelas-kelas seperti `User`, `Food`, `Database`. Contoh: Object `$user` memiliki properti `$weight` dan method `calculateTDEE()`.
*   **Constructor**: Digunakan untuk inisialisasi koneksi database saat objek dibuat.
*   **Visibility (Public/Private/Protected)**: Diterapkan untuk melindungi data sensitif (seperti koneksi DB) agar tidak diakses sembarangan dari luar kelas.

### Pemrograman Web
Teknologi web yang digunakan mencakup sisi *Client* dan *Server*.
*   **Backend**: Menggunakan **PHP 8.x**. PHP dipilih karena kemampuannya dalam pemrosesan data form dan interaksi database yang cepat.
*   **Frontend**: Menggunakan **HTML5** untuk semantik, **CSS3** (dengan variabel CSS untuk tema warna) untuk desain responsif, dan **JavaScript** (Chart.js) untuk visualisasi data grafik.
*   **HTTP Protocol**: Pemahaman tentang metode request GET (mengambil data) dan POST (mengirim data) diterapkan pada setiap formulir.

### Basis Data
Sistem menggunakan **MySQL** sebagai RDBMS.
*   **Relational Model**: Data disimpan dalam tabel-tabel yang saling berhubungan. Contoh: Tabel `nutrition_logs` memiliki *Foreign Key* ke tabel `users` dan `foods`.
*   **ACID Properties**: Transaksi database (Atomic, Consistent, Isolated, Durable) dijaga, terutama saat proses registrasi awal yang melibatkan multiple insert.

---

## BAB III: ANALISIS SISTEM

### 3.1. Analisis Kebutuhan
**A. Kebutuhan Fungsional (Functional Requirements)**
1.  **Manajemen Akun**:
    *   User dapat mendaftar dan login.
    *   User wajib melengkapi profil fisik (Gender, Umur, Tinggi, Berat, Aktivitas, Tujuan).
2.  **Perhitungan Cerdas**:
    *   Sistem menghitung BMR menggunakan rumus *Mifflin-St Jeor*.
    *   Sistem menghitung TDEE dan menyesuaikan target kalori berdasarkan Goal (Diet: -500kcal, Muscle: +400kcal).
3.  **Pencatatan Nutrisi (Food Logging)**:
    *   User dapat mencari makanan dari database.
    *   User dapat mencatat makanan untuk sarapan, makan siang, makan malam, atau camilan.
    *   Sistem otomatis menjumlahkan kalori dan makronutrisi harian.
4.  **Pelacakan Berat Badan (Weight Tracker)**:
    *   User dapat mencatat berat badan secara berkala.
    *   Sistem mendeteksi BMI (Body Mass Index) secara otomatis.
    *   **Fitur Warning**: Sistem memberi peringatan merah jika BMI >= 30 (Obesitas) dan peringatan kuning jika BMI < 18.5 (Underweight).
5.  **Admin Panel**:
    *   Admin dapat mengelola data makanan (CRUD).
    *   Admin dapat melihat daftar pengguna.

**B. Kebutuhan Non-Fungsional**
1.  **Usability**: Desain antarmuka harus intuitif dengan penggunaan ikon dan warna yang jelas.
2.  **Reliability**: Perhitungan matematika (kalori) harus presisi.
3.  **Security**: Password pengguna harus dienkripsi (hashing).

### 3.2. Desain Sistem

#### 3.2.5. Diagram Use Case
*   **Actor**: User
    *   *Login/Logout*
    *   *Manage Profile (Set Goal)*
    *   *Log Food*
    *   *View Dashboard*
    *   *Track Weight*
*   **Actor**: Admin (Inherits from User)
    *   *Manage User Data*
    *   *Manage Food Database*

#### 3.2.7. Diagram Kelas (Class Diagram)
Berikut adalah struktur kelas utama yang diimplementasikan:
*   **Class Database**
    *   Properties: `host`, `user`, `pass`, `dbname`, `conn`
    *   Methods: `__construct()`, `getConnection()`
*   **Class AuthService**
    *   Methods: `register($data)`, `login($email, $pass)`, `logout()`
*   **Class RecommendationService**
    *   Methods: `generateMealPlan()`, `analyzeNutrition()`

#### 3.2.9. Diagram ERD (Entity Relationship Diagram)
Desain skema database terdiri dari tabel-tabel berikut:
1.  **users**: Menyimpan atribut profil lengkap (`id`, `name`, `email`, `password`, `gender`, `age`, `height`, `activity_level`, `goal`, `role`).
2.  **foods**: Katalog makanan (`id`, `name`, `calories`, `protein`, `carbs`, `fat`, `image`).
3.  **nutrition_logs**: Log harian (`id`, `user_id` [FK], `food_id` [FK], `qty`, `date`, `total_calories`).
4.  **weight_logs**: Riwayat berat (`id`, `user_id` [FK], `weight`, `date`, `bmi_result`).
5.  **meal_plans**: Rencana makan user (`id`, `user_id` [FK], `food_id` [FK], `plan_date`, `meal_type`).

### 3.3. Desain Antarmuka
Antarmuka dirancang dengan pendekatan *Card-based Design*.
*   **Dashboard**: Menampilkan "Stat Cards" untuk ringkasan Kalori, Protein, Carbs, Fat.
*   **Progress Bar**: Visualisasi asupan kalori hari ini vs Target.
*   **Alert component**: Komponen dinamis yang muncul untuk notifikasi sukses/gagal/peringatan kesehatan.

---

## BAB IV: IMPLEMENTASI SISTEM

### 4.1. PBO dan Pemrograman Web

#### 4.1.1. Struktur Kode Program
Aplikasi disusun dengan struktur modular untuk memudahkan *maintenance*:
```
C:\xampp\htdocs\yourproject\
├── public/                 # Folder akses publik (Frontend)
│   ├── admin/              # Halaman khusus Admin
│   ├── css/                # Stylesheet
│   ├── dashboard.php       # Halaman utama user
│   ├── login.php           # Halaman otentikasi
│   ├── weight_tracker.php  # Fitur pelacakan berat
│   └── ...
├── src/                    # Folder Source Code Backend (Core System)
│   ├── Config/
│   │   └── Database.php    # Koneksi DB Singleton-like
│   ├── Services/
│   │   ├── AuthService.php # Logika Registrasi/Login
│   │   └── NotificationService.php
│   └── Models/
└── bootstrap.php           # Autoloader & inisialisasi global
```

#### 4.1.2. Implementasi Fitur Utama

**a. Logika Koneksi Database (OOP)**
Menggunakan `mysqli` object di dalam class wrapper.
```php
// src/Config/Database.php
namespace App\Config;

class Database {
    public $conn;

    public function __construct() {
        $this->conn = new \mysqli('localhost', 'root', '', 'smarthealthy_db');
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}
```

**b. Logika Peringatan Berat Badan & TDEE (Dashboard)**
Potongan kode berikut menunjukkan implementasi logika cerdas untuk memberikan saran berdasarkan BMI dan TDEE.
```php
// public/dashboard.php
$h_m = ($uStats['height'] ?? 170) / 100;
$bmi = $weightData['current_weight'] / ($h_m * $h_m);

if ($bmi >= 30) {
    // KONDISI OBESITAS
    echo "<div class='alert-danger'>
            🚨 BAHAYA / OBESITAS: Berat naik. BMI: " . number_format($bmi, 1) . ". 
            Batasi kalori maksimal <strong>" . $dailyTarget . " kcal</strong>.
          </div>";
} elseif ($bmi < 18.5) {
    // KONDISI UNDERWEIGHT
    echo "<div class='alert-warning'>
            ⚠️ PERHATIAN: BMI " . number_format($bmi, 1) . " (Kurang Ideal). 
            Targetkan asupan <strong>" . ($tdee + 300) . " kcal</strong>.
          </div>";
}
```

#### 4.1.3. Integrasi dengan Basis Data
Semua input user disanitasi dan diproses menggunakan *Prepared Statements* untuk keamanan maksimal.
```php
// Contoh insert log berat badan
$stmt = $db->conn->prepare("INSERT INTO weight_logs (user_id, weight, date) VALUES (?, ?, ?)");
$stmt->bind_param("ids", $userId, $weight, $date);
$stmt->execute();
```

### 4.2. Database

#### 4.2.1. Struktur Tabel

Berikut adalah detail struktur tabel dalam basis data `smarthealthy_db`:

**1. Tabel `users`**
Menyimpan informasi akun dan profil fisik pengguna.
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT(11) | Primary Key, Auto Increment |
| `name` | VARCHAR(100) | Nama lengkap pengguna |
| `email` | VARCHAR(100) | Email unik untuk login |
| `password` | VARCHAR(255) | Hash password (bcrypt) |
| `gender` | ENUM('male','female') | Jenis kelamin |
| `age` | INT(3) | Umur dalam tahun |
| `height` | INT(3) | Tinggi badan (cm) |
| `activity_level` | VARCHAR(20) | Level aktivitas (sedentary, light, moderate, active, athlete) |
| `goal` | ENUM('diet','maintain','muscle') | Tujuan kesehatan pengguna |
| `role` | VARCHAR(20) | Peran user (admin/user) |

**2. Tabel `foods`**
Menyimpan katalog makanan beserta nilai nutrisinya.
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT(11) | Primary Key, Auto Increment |
| `name` | VARCHAR(100) | Nama makanan |
| `calories` | INT(11) | Total kalori per porsi |
| `protein` | DECIMAL(5,2) | Kandungan protein (gram) |
| `carbs` | DECIMAL(5,2) | Kandungan karbohidrat (gram) |
| `fat` | DECIMAL(5,2) | Kandungan lemak (gram) |
| `image_path` | VARCHAR(255) | Path gambar makanan (opsional) |
| `is_verified` | TINYINT(1) | Status verifikasi makanan (1=Verified) |

**3. Tabel `nutrition_logs`**
Menyimpan riwayat konsumsi makanan harian pengguna.
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT(11) | Primary Key, Auto Increment |
| `user_id` | INT(11) | Foreign Key -> `users.id` |
| `food_name` | VARCHAR(100) | Snapshot nama makanan saat dicatat |
| `calories` | DECIMAL(10,2) | Total kalori yang dikonsumsi |
| `protein` | DECIMAL(10,2) | Total protein yang dikonsumsi |
| `carbs` | DECIMAL(10,2) | Total karbohidrat yang dikonsumsi |
| `fat` | DECIMAL(10,2) | Total lemak yang dikonsumsi |
| `date` | DATE | Tanggal pencatatan |
| `created_at` | TIMESTAMP | Waktu pencatatan |

**4. Tabel `weight_logs`**
Menyimpan riwayat pelacakan berat badan.
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT(11) | Primary Key, Auto Increment |
| `user_id` | INT(11) | Foreign Key -> `users.id` |
| `weight` | DECIMAL(5,2) | Berat badan (kg) |
| `date` | DATE | Tanggal penimbangan |
| `notes` | VARCHAR(255) | Catatan tambahan (opsional) |

**5. Tabel `meal_plans`**
Menyimpan rencana makan pengguna untuk tanggal tertentu.
| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT(11) | Primary Key, Auto Increment |
| `user_id` | INT(11) | Foreign Key -> `users.id` |
| `food_id` | INT(11) | Foreign Key -> `foods.id` |
| `plan_date` | DATE | Tanggal rencana makan |
| `meal_type` | ENUM | Waktu makan (breakfast, lunch, dinner, snack) |
| `servings` | DECIMAL(5,2) | Jumlah porsi |
| `notes` | VARCHAR(500) | Catatan khusus |

#### 4.2.2. Query-Query Penting
**Menghitung Rata-rata Makro Mingguan:**
```sql
SELECT 
    AVG(calories) AS avg_cal, 
    AVG(protein) AS avg_p, 
    AVG(carbs) AS avg_c 
FROM nutrition_logs 
WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
```

### 4.3. Antarmuka Pengguna (UI)
*   **Halaman Setup Profile**: Formulir wizard yang memandu user mengisi data fisik satu per satu.
*   **Weight Tracker Widget**: Widget interaktif di dashboard yang menampilkan grafik tren berat badan dan angka perubahan (kg) dengan indikator panah naik/turun berwarna.

---

## BAB V: PENGUJIAN SISTEM

### 5.1. Metode Pengujian
Pengujian dilakukan dengan metode **Black Box Testing** untuk memvalidasi fungsionalitas input-output, serta **User Experience (UX) Testing** sederhana untuk memastikan alur aplikasi logis.

### 5.2. Hasil Pengujian

**Tabel 5.1. Hasil Pengujian Fungsional**

| No | Fitur | Skenario Pengujian | Ekspektasi Output | Hasil Aktual | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | **Registrasi** | User mendaftar dengan email baru | Data tersimpan, redirect ke Setup | Sesuai Ekspektasi | **Berhasil** |
| 2 | **Validasi Profile** | User mengisi Tinggi Badan negatif | Muncul pesan error validasi | Pesan error muncul | **Berhasil** |
| 3 | **Hitung TDEE** | User mengubah Goal ke "Muscle Gain" | Target kalori bertambah (+400) | Target berubah otomatis | **Berhasil** |
| 4 | **Log Makanan** | User input makanan ke menu Lunch | Kalori dashboard bertambah real-time | Updated seketika | **Berhasil** |
| 5 | **Warning Obesitas**| User input berat badan 160kg (Tinggi 170cm) | Muncul Alert Merah "BAHAYA" | Alert muncul di Dashboard | **Berhasil** |
| 6 | **Warning Kurus** | User input berat badan 45kg (Tinggi 175cm) | Muncul Alert Kuning + Saran Surplus | Alert muncul sesuai | **Berhasil** |

### 5.3. Evaluasi Sistem
Berdasarkan pengujian:
*   **Kelebihan**: Sistem sangat responsif dalam perhitungan angka. Logika peringatan kesehatan (Obesity/Underweight) berfungsi sangat baik sebagai fitur unggulan.
*   **Kekurangan**: Belum adanya fitur *Social Sharing* atau integrasi ke Google Fit/Apple Health.

---

## BAB VI: PENUTUP

### 6.1. Kesimpulan
Proyek Aplikasi **SmartHealthy** telah berhasil dikembangkan dengan memenuhi seluruh persyaratan integrasi mata kuliah.
1.  Sistem berhasil menerapkan **konsep OOP** secara konsisten dari struktur koneksi database hingga logika bisnis perhitungan TDEE.
2.  Implementasi **Database Relasional** mampu menangani integritas data pengguna dan riwayat kesehatan dengan baik.
3.  Fitur analisis kesehatan cerdas (deteksi Obesitas/Underweight) memberikan nilai tambah edukatif bagi pengguna, lebih dari sekadar aplikasi pencatat biasa.
4.  Aplikasi berjalan stabil pada lingkungan server Apache/MySQL (XAMPP) dan dapat diakses melalui browser modern.

### 6.2. Saran Pengembangan
Untuk pengembangan selanjutnya, disarankan:
1.  Implementasi fitur **Scan Barcode** produk makanan kemasan untuk mempercepat input data.
2.  Penambahan modul **Gamifikasi** (badges/achievements) untuk meningkatkan motivasi pengguna.
3.  Pengembangan API (RESTful) agar backend dapat digunakan juga oleh mobile app (Android/iOS) di masa depan.

---

## DAFTAR PUSTAKA
1.  **Dokumentasi Resmi PHP**. (2024). *PHP Manual & Language Reference*. php.net.
2.  **Mifflin, M. D., et al.** (1990). *A new predictive equation for resting energy expenditure in healthy individuals*. The American Journal of Clinical Nutrition.
3.  **Pressman, Roger S.** (2014). *Software Engineering: A Practitioner's Approach (8th Edition)*. McGraw-Hill Education.
4.  **W3Schools**. (2024). *SQL Tutorial & Database Design*. w3schools.com/sql.

---

## LAMPIRAN
**(Kode Program & Screenshot Aplikasi Terlampir)**
*   *Lampiran 1: Kode `dashboard.php`*
*   *Lampiran 2: Class `NotificationService.php`*
*   *Lampiran 3: Screenshot Dashboard User*
