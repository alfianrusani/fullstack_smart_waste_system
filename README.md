# 🌿 Smart Waste Management System (Full-Stack & Mobile App)

Platform pengelolaan dan pelaporan limbah terpadu yang dirancang untuk memenuhi standar penilaian **Technical Coding Test – Fullstack Developer (Bilcode Technology)**. Sistem ini menghubungkan masyarakat (*warga*) melalui aplikasi *mobile* lintas platform dengan administrator melalui *dashboard web* untuk menciptakan pengelolaan dan pemilahan sampah yang transparan serta efisien.

---

## 📋 Daftar Isi
1. [Tentang Proyek](#-tentang-proyek)
2. [Arsitektur & Komponen Utama](#-arsitektur--komponen-utama)
3. [Fitur Sistem](#-fitur-sistem)
4. [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
5. [Struktur Repositori](#-struktur-repositori)
6. [Panduan Instalasi & Menjalankan Proyek](#-panduan-instalasi--menjalankan-proyek)
7. [Dokumentasi API & Basis Data](#-dokumentasi-api--basis-data)
8. [Machine Learning & Model Evaluation](#-machine-learning--model-evaluation)
9. [Author](#-author)

---

## 📌 Tentang Proyek
Smart Waste Management System dikembangkan untuk menjawab tantangan tata kelola kebersihan lingkungan perkotaan secara digital. Aplikasi ini mengintegrasikan 3 pilar utama pengembangan perangkat lunak sesuai standar industri:
* **Website Dashboard (Admin & User):** Manajemen data terpusat, analitik laporan, dan validasi status oleh administrator.
* **Mobile Application (Warga):** Perekaman laporan mandiri oleh warga dengan integrasi kamera dan titik koordinat GPS langsung.
* **Machine Learning API:** Layanan kecerdasan buatan untuk klasifikasi jenis sampah secara otomatis.

---

## 🏗️ Arsitektur & Komponen Utama

### A. Website Development (Admin & User Dashboard)
* **Autentikasi Berbasis Role:** Hak akses terpisah secara ketat antara `Admin` dan `User`.
* **Dashboard Statistik:** Visualisasi data laporan menggunakan grafik interaktif (Chart.js / Doughnut Chart) dengan pemetaan warna kategori yang konsisten.
* **Manajemen Laporan (CRUD):** Memantau, menyaring, dan mengubah status penanganan laporan secara *real-time*.
* **Fitur Filter & Pencarian:** Memudahkan pencarian berdasarkan kata kunci lokasi/kategori serta pemilahan status laporan.
* **Desain Responsif:** Dibangun menggunakan Tailwind CSS yang optimal di berbagai ukuran layar.

### B. Mobile Application (Warga / Pengguna)
* **Autentikasi Aman:** Sistem masuk dan pendaftaran akun yang terhubung langsung dengan token autentikasi API.
* **Laporan Berbasis Lokasi & Foto:** Pengambilan gambar limbah secara langsung disertai pencatatan titik koordinat GPS otomatis.
* **Riwayat Laporan:** Pemantauan status laporan personal lengkap dengan terjemahan bahasa Indonesia dan indikator visual status.
* **Pengaturan Profil:** Pengelolaan data pribadi, nomor WhatsApp, alamat penjemputan, hingga pembaruan koordinat GPS mandiri.

### C. Machine Learning & AI Detection
* **Model Klasifikasi Gambar:** Mampu mengidentifikasi minimal 3 kategori utama limbah: **Organic Waste**, **Plastic Waste**, dan **Paper Waste**.
* **Preprocessing & Training:** Melibatkan augmentasi data, normalisasi, dan pembagian dataset rasio optimal untuk menghindari *overfitting*.
* **Evaluasi Performa:** Pengujian model menggunakan metrik *Accuracy*, *Confusion Matrix*, dan *Classification Report* dengan target akurasi di atas 80%.

---

## 🛠️ Teknologi yang Digunakan

* **Backend / API:** Laravel (PHP Framework)
* **Database:** Supabase (PostgreSQL)
* **Cloud Storage:** Cloudinary Service (untuk penyimpanan media gambar laporan)
* **Mobile App:** React Native dengan Expo Go
* **Frontend Web:** Blade Templates, Tailwind CSS, Alpine.js, Chart.js
* **Machine Learning:** Python, TensorFlow / Keras atau PyTorch

---

## 📂 Struktur Repositori

```text
fullstack_smart_waste_system/
│
├── backend/              # Web Dashboard & RESTful API (Laravel)
│   ├── app/Http/Controllers/
│   ├── routes/web.php
│   └── routes/api.php
│
├── mobile_app/           # Aplikasi Mobile Warga (React Native / Expo)
│   ├── app/              # Halaman & Navigasi (Expo Router)
│   └── assets/
│
├── ml-api/               # Layanan REST API Machine Learning (Python / FastAPI)
│   ├── model/            # File bobot model (.h5 / .pkl)
│   └── main.py
│
└── dataset-ml/           # Kumpulan dataset gambar & skrip pelatihan jupyter notebook

⚙️ Panduan Instalasi & Menjalankan Proyek
1. Menjalankan Backend (Laravel Web & API)
Pastikan komputer Anda telah terinstal PHP dan Composer.

cd backend
composer install
cp .env.example .env
php artisan key:generate
# Atur konfigurasi database (Supabase/PostgreSQL) pada file .env
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000

2. Menjalankan Aplikasi Mobile (React Native / Expo)
Pastikan Node.js dan aplikasi Expo Go di ponsel Anda sudah siap.

cd mobile_app
npm install
npx expo start

3. Menjalankan Machine Learning API (Python)

cd ml-api
pip install -r requirements.txt
uvicorn main:app --reload --port=8000

📊 Dokumentasi API & Basis Data
Autentikasi API: Menggunakan Bearer Token (Laravel Sanctum).

Endpoint Utama:

POST /api/login - Autentikasi pengguna mobile.

GET /api/reports - Mengambil daftar laporan sampah (berdasarkan role user/admin).

POST /api/reports - Mengirim laporan baru (dengan unggahan foto & GPS).

PUT /api/reports/{id}/status - Memperbarui status laporan (khusus admin).

PUT /api/profile/update - Memperbarui profil dan koordinat GPS pengguna.

Database Schema (ERD): Relasi utama terpusat pada tabel users (memiliki relasi one-to-many dengan tabel reports yang menyimpan koordinat latitude, longitude, category, image_url, dan status).

🔬 Hasil Evaluasi Machine Learning
Kategori Kelas: Organic Waste, Plastic Waste, Paper Waste.

Metrik Evaluasi:

Akurasi Model pada Data Testing: > 97%

Confusion Matrix & Classification Report terlampir di dalam direktori dataset-ml/evaluation/.

REST API Integration: Model dikemas menggunakan layanan mikro (FastAPI) yang siap merespons permintaan klasifikasi gambar secara asynchronous.

👨‍💻 Author & Acknowledgement
Nama: Andi Alfian Rusani.

Institusi: Mahasiswa Sistem Informasi, Makassar.

Program/Kandidat: Fullstack Developer Assessment – Bilcode Technology.

Dibuat dengan penuh dedikasi untuk memenuhi standar Technical Coding Test serta mewujudkan solusi pengelolaan sampah berbasis teknologi yang berkelanjutan.
