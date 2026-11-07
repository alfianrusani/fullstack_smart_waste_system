<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Smart Waste</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    
    <!-- CDN Tailwind, FontAwesome & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<!-- Deklarasi Alpine.js Logic -->
<body class="bg-gray-50 flex min-h-screen font-sans" x-data="authHandler()">

    <!-- BAGIAN KIRI: Branding Visual (Desktop Only) -->
    <div class="hidden md:flex md:w-1/2 bg-green-800 text-white flex-col justify-center items-center p-12 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-64 h-64 bg-green-700 rounded-full mix-blend-multiply filter blur-2xl opacity-50 transform -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-green-900 rounded-full mix-blend-multiply filter blur-2xl opacity-50 transform translate-x-1/2 translate-y-1/2"></div>

        <div class="relative z-10 text-center">
            <div class="w-48 h-48 bg-green-100 rounded-full flex items-center justify-center mb-6 shadow-xl mx-auto">
                <i class="fa-solid fa-leaf text-8xl text-green-700"></i>
            </div>
            <h1 class="text-4xl font-bold leading-tight tracking-wide drop-shadow-md">
                Smart Waste<br>
                Management System
            </h1>
        </div>
    </div>

    <!-- BAGIAN KANAN: Form Login -->
    <div class="w-full md:w-1/2 flex flex-col justify-center items-center px-6 pt-4 pb-8 relative">
        
        <!-- Header Mobile (Hanya tampil di Mobile/HP) -->
        <div class="md:hidden text-center mb-4">
            <div class="w-14 h-14 bg-green-100 text-green-700 rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm">
                <i class="fa-solid fa-leaf text-2xl"></i>
            </div>
            <h1 class="text-xl font-bold text-green-800">Smart Waste</h1>
        </div>
        
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-100">

            <!-- NOTIFIKASI SUKSES (Akan muncul setelah register) -->
            <div x-show="showSuccess" x-transition x-cloak class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl relative flex items-start gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-xl text-green-600 mt-0.5"></i>
                <div>
                    <span class="block font-bold text-sm">Pengguna berhasil ditambahkan!</span>
                    <span class="block text-xs text-green-700 mt-0.5">Kredensial Anda telah diisi otomatis. Klik Masuk untuk melanjutkan.</span>
                </div>
            </div>

            <!-- NOTIFIKASI ERROR PHP (Server-Side: Salah Login / Gagal Validasi Backend) -->
            @if ($errors->any())
                <div x-show="showPhpError" x-transition class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl relative flex items-start gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation text-xl text-red-600 mt-0.5"></i>
                    <div>
                        <span class="block font-bold text-sm">Gagal Masuk!</span>
                        <ul class="text-xs text-red-700 mt-0.5 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- NOTIFIKASI ERROR AJAX (Client-Side Pendaftaran Gagal) -->
            <div x-show="errorMessage !== ''" x-transition x-cloak class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl relative flex items-start gap-3 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-xl text-red-600 mt-0.5"></i>
                <div>
                    <span class="block font-bold text-sm">Pendaftaran Gagal!</span>
                    <span class="block text-xs text-red-700 mt-0.5" x-text="errorMessage"></span>
                </div>
            </div>

            <!-- Teks Sapaan -->
            <div class="text-left mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Selamat Datang 👋</h2>
                <p class="text-gray-500 mt-1 text-sm">Silakan masuk ke akun Anda untuk melanjutkan.</p>
            </div>

            <!-- FORM LOGIN -->
            <form action="{{ url('/login') }}" method="POST" @submit="isLoginLoading = true" class="space-y-4">
                @csrf
                <!-- Input Email -->
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat Email</label>
                    <div class="relative flex items-center">
                        <i class="fa-regular fa-envelope absolute left-4 text-gray-400"></i>
                        <input type="email" name="email" x-model="loginEmail" class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition" placeholder="masukkan email Anda" required>
                    </div>
                </div>
                
                <!-- Input Password -->
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Kata Sandi</label>
                    <div class="relative flex items-center">
                        <i class="fa-solid fa-lock absolute left-4 text-gray-400"></i>
                        <input type="password" name="password" x-model="loginPassword" class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition" placeholder="masukkan kata sandi" required>
                    </div>
                </div>

                <!-- Fitur Tambahan -->
                <div class="flex items-center justify-between text-sm py-1">
                    <label class="flex items-center text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="mr-2 rounded text-green-600 focus:ring-green-500"> Ingat saya
                    </label>
                    <a href="#" @click.prevent="openForgotModal = true" class="text-green-600 hover:text-green-700 font-semibold hover:underline">Lupa Password?</a>
                </div>

                <button type="submit" :disabled="isLoginLoading" class="w-full bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold py-3.5 px-4 rounded-xl transition duration-300 shadow-md flex justify-center items-center gap-2 mt-4">
                    <span x-show="!isLoginLoading">Masuk <i class="fa-solid fa-right-to-bracket ml-1"></i></span>
                    <span x-show="isLoginLoading">Memproses... <i class="fa-solid fa-spinner fa-spin ml-1"></i></span>
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Belum punya akun? <a href="#" @click.prevent="openRegisterModal = true" class="text-green-600 font-semibold hover:underline">Daftar sekarang</a>
            </p>
        </div>
    </div>

    <!-- MODAL BUAT AKUN BARU (REGISTER) -->
    <div x-show="openRegisterModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-100 relative transform transition-all" @click.away="openRegisterModal = false">
            
            <button @click="openRegisterModal = false" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>

            <div class="text-left mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Buat Akun Baru 👤</h2>
                <p class="text-gray-500 mt-1 text-sm">Daftar untuk mulai mengelola laporan sampah Anda.</p>
            </div>

            <!-- Menggunakan fungsi JS 'registerUser' saat disubmit -->
            <form @submit.prevent="registerUser" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap</label>
                    <div class="relative flex items-center">
                        <i class="fa-regular fa-user absolute left-4 text-gray-400"></i>
                        <input type="text" x-model="regName" class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition" placeholder="masukkan nama lengkap" required>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat Email</label>
                    <div class="relative flex items-center">
                        <i class="fa-regular fa-envelope absolute left-4 text-gray-400"></i>
                        <input type="email" x-model="regEmail" class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition" placeholder="masukkan email Anda" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Kata Sandi</label>
                    <div class="relative flex items-center">
                        <i class="fa-solid fa-lock absolute left-4 text-gray-400"></i>
                        <input type="password" x-model="regPassword" class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition" placeholder="minimal 8 karakter" required>
                    </div>
                </div>

                <button type="submit" :disabled="isLoading" class="w-full bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold py-3.5 px-4 rounded-xl transition duration-300 shadow-md flex justify-center items-center gap-2 mt-4">
                    <span x-show="!isLoading">Daftar Akun <i class="fa-solid fa-user-plus ml-1"></i></span>
                    <span x-show="isLoading">Memproses... <i class="fa-solid fa-spinner fa-spin ml-1"></i></span>
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Sudah punya akun? <button type="button" @click="openRegisterModal = false" class="text-green-600 font-semibold hover:underline">Masuk</button>
            </p>
        </div>
    </div>

    <!-- MODAL LUPA PASSWORD -->
    <div x-show="openForgotModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-100 relative transform transition-all" @click.away="openForgotModal = false">
            <button @click="openForgotModal = false" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            <div class="text-left mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Pulihkan Password 🔑</h2>
                <p class="text-gray-500 mt-1 text-sm">Silahkan kirimkan email akun anda untuk proses mengaktifkan kembali password anda.</p>
            </div>
            <form action="{{ url('/forgot-password') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat Email Terdaftar</label>
                    <div class="relative flex items-center">
                        <i class="fa-regular fa-envelope absolute left-4 text-gray-400"></i>
                        <input type="email" name="email" class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition" placeholder="masukkan email Anda" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-xl transition duration-300 shadow-md flex justify-center items-center gap-2 mt-4">
                    Kirim Instruksi <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
            <div class="text-center mt-6">
                <button type="button" @click="openForgotModal = false" class="text-sm text-gray-500 hover:text-green-600 font-semibold">
                    &larr; Kembali ke halaman login
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPT ALPINE.JS UNTUK LOGIKA UX -->
    <script>
        function authHandler() {
            return {
                openRegisterModal: false,
                openForgotModal: false,
                showPhpError: true, // Mengontrol tampilnya error dari Laravel (server-side)

                // Variabel yang terhubung langsung (x-model) dengan form
                loginEmail: '',
                loginPassword: '',
                regName: '',
                regEmail: '',
                regPassword: '',

                showSuccess: false,
                errorMessage: '',
                isLoading: false,
                isLoginLoading: false,

                async registerUser() {
                    this.isLoading = true;
                    this.errorMessage = '';

                    // 1. Ambil CSRF Token dari meta tag HTML
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const response = await fetch('/register', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                name: this.regName,
                                email: this.regEmail,
                                password: this.regPassword
                            })
                        });

                        const data = await response.json();

                        // JIKA PENDAFTARAN GAGAL
                        if (!response.ok || data.success === false) {
                            this.showPhpError = false; // Sembunyikan error PHP lama agar tidak bertumpuk
                            this.showSuccess = false;  
                            
                            // Tangkap pesan error dari Laravel
                            if (data.errors) {
                                this.errorMessage = Object.values(data.errors)[0][0];
                            } else {
                                this.errorMessage = data.message || 'Pendaftaran gagal. Periksa kembali data Anda.';
                            }
                            
                            this.isLoading = false;
                            return; 
                        }

                        // JIKA PENDAFTARAN BERHASIL
                        this.showPhpError = false;
                        this.errorMessage = ''; 
                        
                        // Tutup modal dan pindahkan data ke form login
                        this.openRegisterModal = false;
                        this.loginEmail = this.regEmail;
                        this.loginPassword = this.regPassword;
                        
                        // Tampilkan notifikasi sukses
                        this.showSuccess = true;

                        // Bersihkan form modal
                        this.regName = '';
                        this.regEmail = '';
                        this.regPassword = '';
                        this.isLoading = false;

                    } catch (error) {
                        console.error("Error register:", error);
                        this.showPhpError = false;
                        this.errorMessage = "Terjadi kesalahan sistem atau jaringan.";
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>