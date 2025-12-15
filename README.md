# Nutrition App – Web Sistem Manajemen Nutrisi

Dokumentasi resmi untuk struktur project, alur kerja, dan hubungan antar komponen dalam aplikasi **Nutrition App**.

---

# 📁 Struktur Direktori

```
NutritionApp/
├── src/
│   ├── Config/
│   │   └── Database.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Food.php
│   │   ├── NutritionLog.php
│   │   └── Notification.php
│   │
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── AnalyticsService.php
│   │   ├── MealRecommendationService.php
│   │   ├── NutritionApiClient.php
│   │   └── NotificationService.php
│
├── public/
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── search_nutrition.php
│   ├── meat_plan.php
│   └── calendar.php
│
├── .env
└── vendor/
```

---

# 🔍 Penjelasan Folder & Hubungan Antar Komponen

## **1. src/Config/**

#### `Database.php`

* Menginisialisasi koneksi database menggunakan **PDO**.
* Dipanggil oleh *semua model*.

**Alur:**

```
Service/Controller → Model → Database.php → Database
```

---

## **2. src/Models/**

Model merepresentasikan tabel database dan berisi fungsi CRUD.

### Model yang tersedia:

* **User** → data akun, profile, dan target kalori
* **Food** → data makanan yang tersimpan
* **NutritionLog** → catatan harian konsumsi makanan user
* **Notification** → log notifikasi sistem

**Relasi antar model:**

```
User 1—* NutritionLog *—1 Food
User 1—* Notification
```

---

## **3. src/Services/**

Layer yang menangani *logic aplikasi* dan integrasi eksternal.

### **AuthService**

* Menangani proses Login dan Register
* Session management

### **NutritionApiClient**

* Mengambil data nutrisi dari API eksternal (misal: Edamam)
* Digunakan di halaman `search_nutrition.php`

### **MealRecommendationService**

* Menghitung rekomendasi makanan berdasarkan target kalori user
* Menghitung TDEE dan Macro ratio

### **AnalyticsService**

* Menyediakan data untuk grafik di dashboard
* Menghitung total kalori harian/mingguan

**Flow Service:**

```
Public Page → Service → Model → DB
              ↳ API Eksternal (NutritionApi)
```

---

## **4. public/**

File yang bisa diakses langsung oleh user (Views & Controllers).

### File utama:

* `index.php` — Landing page / Homepage
* `dashboard.php` — Pusat informasi user (Status kalori, Grafik)
* `search_nutrition.php` — Pencarian database makanan
* `meal_plan.php` — Halaman rekomendasi menu

**Flow lengkap request browser:**

```
Browser → public/search_nutrition.php → NutritionApiClient → External API
                                     ↳ Service → DB (Save Log)
```

---

## **5. vendor/**

Folder hasil **Composer**. Berisi library seperti:

* PHPMailer (untuk notifikasi email)
* Dotenv Loader

---

## **6. .env**

Berisi configuration:

* DB_USERNAME, DB_PASSWORD
* EDAMAM_APP_ID, EDAMAM_APP_KEY (API Nutrisi)
* SMTP_SERVER

---

# 🔗 Diagram Alur Kerja

```
              ┌──────────────────────────┐
              │          User            │
              └────────────┬─────────────┘
                           │ HTTP Request
                           ▼
               ┌────────────────────────┐
               │      public/*.php      │
               └────────────┬──────────┘
                           ▼
                   ┌────────────────┐
                   │    Service     │
                   └──────┬────────┘
                          │
          ┌───────────────┼────────────────┐
          ▼                               ▼
   ┌───────────────┐                ┌──────────────┐
   │     Model      │                │ API Eksternal │
   └───────┬────────┘                └──────────────┘
           ▼
   ┌───────────────┐
   │    Database    │
   └───────────────┘
```
