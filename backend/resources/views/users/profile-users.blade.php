<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - Smart Waste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Leaflet OpenStreetMap (Solusi Maps Gratis & Mudah tanpa API Key) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 flex font-sans min-h-screen" 
      x-data="profileApp({
          name: '{{ addslashes($user->name) }}',
          email: '{{ addslashes($user->email) }}',
          phone: '{{ addslashes($user->phone ?? '') }}',
          address: '{{ addslashes($user->address ?? '') }}',
          lat: '{{ $user->latitude ?? '' }}',
          lng: '{{ $user->longitude ?? '' }}'
      })">

    <!-- Sidebar User -->
    <aside id="sidebar" class="w-64 bg-green-800 text-white min-h-screen flex flex-col shadow-lg hidden md:flex">
        <div class="p-6 text-center border-b border-green-700">
            <h2 class="text-2xl font-bold"><i class="fa-solid fa-leaf mr-2"></i>Smart Waste</h2>
        </div>
        <nav class="flex-1 p-4 space-y-2">
    
            <!-- Dashboard -->
            <a href="{{ url('/user/dashboard') }}" class="flex items-center p-3 rounded-lg transition {{ request()->is('user/dashboard') ? 'bg-green-700 text-white shadow-inner font-semibold' : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie w-6"></i> Dashboard Saya
            </a>

            <!-- Laporan Saya -->
            <a href="{{ url('/user/reports') }}" class="flex items-center p-3 rounded-lg transition {{ request()->is('user/reports') ? 'bg-green-700 text-white shadow-inner font-semibold' : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
                <i class="fa-solid fa-clipboard-list w-6"></i> Laporan Saya
            </a>

            <!-- Pengaturan Akun (Menu Baru) -->
            <a href="{{ url('/user/profile') }}" class="flex items-center p-3 rounded-lg transition {{ request()->is('user/profile') ? 'bg-green-700 text-white shadow-inner font-semibold' : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
                <i class="fa-solid fa-user-gear w-6"></i> Pengaturan Akun
            </a>
        </nav>
        <div class="p-4 border-t border-green-700">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center p-2 bg-red-500 hover:bg-red-600 rounded text-white transition font-medium">
                    <i class="fa-solid fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        
        <!-- Header Page -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Pengaturan Akun</h1>
            <p class="text-gray-500 mt-1">Kelola data pribadi dan alamat utama penjemputan sampah Anda.</p>
        </div>

        <!-- Notification Success -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Banner Peringatan Merah (Jika Nomor Telepon atau Alamat Belum Lengkap) -->
        @if(!$user->phone || !$user->address)
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-xl mt-0.5"></i>
                <div>
                    <h4 class="font-bold">Lengkapi Profil Anda!</h4>
                    <p class="text-sm mt-0.5">
                        *Harap lengkapi <strong>Nomor Telepon/WhatsApp</strong> dan <strong>Alamat Lengkap</strong> sebelum melakukan pelaporan. Sistem tidak dapat memproses laporan tanpa data kontak dan titik jemput yang valid.
                    </p>
                </div>
            </div>
        @endif

        <!-- Card Informasi Profil Utama -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 text-green-700 rounded-full flex items-center justify-center text-xl font-bold">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Informasi Pribadi</h2>
                        <p class="text-xs text-gray-500">Detail identitas Anda yang terdaftar pada sistem</p>
                    </div>
                </div>

                <!-- Tombol Edit Akun (Membuka Modal) -->
                <button type="button" @click="openEditModal = true" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Akun
                </button>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <!-- Nama Lengkap -->
                <div>
                    <p class="text-gray-500 font-semibold mb-1">Nama Lengkap</p>
                    <p class="text-gray-800 font-bold text-base">{{ $user->name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <p class="text-gray-500 font-semibold mb-1">Alamat Email</p>
                    <p class="text-gray-800 font-bold text-base">{{ $user->email }}</p>
                </div>

                <!-- Nomor Telepon -->
                <div>
                    <p class="text-gray-500 font-semibold mb-1">Nomor Telepon / WhatsApp</p>
                    @if($user->phone)
                        <p class="text-gray-800 font-bold text-base"><i class="fa-brands fa-whatsapp text-green-600 mr-1"></i> {{ $user->phone }}</p>
                    @else
                        <span class="text-red-500 font-semibold text-xs bg-red-50 border border-red-200 px-2.5 py-1 rounded-full">* Belum diisi</span>
                    @endif
                </div>

                <!-- Koordinat GPS -->
                <div>
                    <p class="text-gray-500 font-semibold mb-1">Titik GPS Terdaftar</p>
                    @if($user->latitude && $user->longitude)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $user->latitude }},{{ $user->longitude }}" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                            <i class="fa-solid fa-location-dot text-red-500"></i> {{ $user->latitude }}, {{ $user->longitude }}
                        </a>
                    @else
                        <span class="text-gray-400 text-xs italic">Belum ditandai di peta</span>
                    @endif
                </div>

                <!-- Alamat Lengkap -->
                <div class="md:col-span-2">
                    <p class="text-gray-500 font-semibold mb-1">Alamat Penjemputan</p>
                    @if($user->address)
                        <p class="text-gray-800 bg-gray-50 p-4 rounded-lg border border-gray-200 leading-relaxed font-medium">{{ $user->address }}</p>
                    @else
                        <span class="text-red-500 font-semibold text-xs bg-red-50 border border-red-200 px-2.5 py-1 rounded-full">* Belum diisi</span>
                    @endif
                </div>
            </div>
        </div>

    </main>

    <!-- ======================================================== -->
    <!-- MODAL EDIT AKUN -->
    <!-- ======================================================== -->
    <!-- PERUBAHAN: z-index diubah ke 40 -->
    <div x-show="openEditModal" class="fixed inset-0 z-40 flex items-center justify-center p-4" x-cloak>
        
        <!-- 1. BACKDROP GELAP: Klik area luar untuk menutup Modal Edit -->
        <div class="absolute inset-0 bg-black/50" @click="openEditModal = false"></div>
        
        <!-- 2. KONTEN MODAL EDIT (Hapus @click.away, tambahkan relative) -->
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden transform transition-all">
            
            <div class="bg-green-800 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold flex items-center"><i class="fa-solid fa-user-pen mr-2"></i> Edit Data Akun</h3>
                <button type="button" @click="openEditModal = false" class="text-green-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form action="{{ route('user.profile.update') }}" method="POST" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                @csrf
                @method('PUT')

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="form.name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-green-500" required>
                </div>

                <!-- Email & Telepon -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email</label>
                        <input type="email" name="email" x-model="form.email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-green-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">No. Telepon / WhatsApp <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" x-model="form.phone" placeholder="Contoh: 081234567890" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-green-500" required>
                    </div>
                </div>

                <!-- Alamat Lengkap + Tombol Pinpoint Maps -->
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase">Alamat Penjemputan Lengkap <span class="text-red-500">*</span></label>
                        <span class="text-xs text-gray-400 italic">Ketik manual atau gunakan Pinpoint</span>
                    </div>

                    <div class="relative">
                        <textarea name="address" x-model="form.address" rows="3" placeholder="Masukkan nama jalan, nomor rumah, RT/RW, atau patokan lokasi..." class="w-full pl-3 pr-12 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-green-500" required></textarea>
                        
                        <!-- Icon Pinpoint di Ujung Textarea -->
                        <button type="button" @click="openMapPicker()" class="absolute right-2 top-2 bg-green-100 hover:bg-green-200 text-green-700 p-2 rounded-lg transition" title="Tandai di Peta Google Maps">
                            <i class="fa-solid fa-location-dot text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Hidden Input Koordinat GPS -->
                <input type="hidden" name="latitude" x-model="form.lat">
                <input type="hidden" name="longitude" x-model="form.lng">

                <!-- KONDISI 1: Jika Koordinat Sudah Dipilih -->
                <div x-show="form.lat && form.lng" class="text-xs text-gray-600 bg-green-50 p-2.5 rounded-lg border border-green-200 mt-2 flex items-center shadow-sm" x-cloak>
                    <i class="fa-solid fa-check-circle text-green-600 mr-2 text-sm"></i> 
                    <span>Koordinat Terpilih: <span class="font-mono font-bold text-gray-800" x-text="form.lat + ', ' + form.lng"></span></span>
                </div>

                <!-- KONDISI 2: Jika Koordinat Masih Kosong -->
                <div x-show="!form.lat || !form.lng" class="text-xs text-red-600 bg-red-50 p-2.5 rounded-lg border border-red-200 mt-2 flex items-center shadow-sm" x-cloak>
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-sm"></i> 
                    <span class="font-semibold">Titik koordinat GPS belum dipilih! Silakan gunakan ikon Pinpoint di atas.</span>
                </div>

                <hr class="border-gray-200 my-2">

                <!-- Password (Opsional) -->
                <div>
                    <p class="text-xs font-bold text-gray-700 uppercase mb-2">Ganti Kata Sandi (Kosongkan jika tidak diubah)</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="password" name="password" placeholder="Kata sandi baru" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-green-500">
                        <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi baru" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-green-500">
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" @click="openEditModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2 rounded-lg transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL MAPS PINPOINT PICKER -->
    <!-- ======================================================== -->
    <!-- PERUBAHAN: z-index diubah ke 50 (Berada 1 tingkat di atas Modal Edit) -->
    <div x-show="openMapModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        
        <!-- BACKDROP GELAP PETA: Klik area luar untuk menutup Modal Peta -->
        <div class="absolute inset-0 bg-black/60" @click="openMapModal = false"></div>

        <!-- KONTEN MODAL PETA -->
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden transform transition-all">
            <div class="bg-green-800 text-white px-6 py-3 flex justify-between items-center">
                <h3 class="text-sm font-bold flex items-center"><i class="fa-solid fa-map-location-dot mr-2"></i> Pilih Lokasi Penjemputan</h3>
                <button type="button" @click="openMapModal = false" class="text-green-200 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <div class="p-4">
                <div class="flex justify-between items-center mb-3">
                    <p class="text-xs text-gray-600">Geser penanda <strong>(Pinpoint)</strong> pada peta ke lokasi rumah/titik penjemputan Anda.</p>
                    <button type="button" @click="getCurrentLocation()" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-lg transition font-semibold flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saat Ini
                    </button>
                </div>

                <!-- Element Container Peta -->
                <div id="mapContainer" class="w-full h-80 rounded-lg border border-gray-300 shadow-inner"></div>
            </div>

            <div class="bg-gray-50 px-6 py-3 flex justify-between items-center border-t border-gray-200">
                <span class="text-xs text-gray-500 font-mono" x-text="'Lat: ' + form.lat + ' | Lng: ' + form.lng"></span>
                <!-- Tombol Gunakan Titik Ini -->
                <button type="button" @click="confirmLocation()" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-5 py-2 rounded-lg transition shadow-sm">
                    Gunakan Titik Ini
                </button>
            </div>
        </div>
    </div>
    

    <!-- Logika JavaScript (Alpine + Peta Interactive) -->
    <script>
        function profileApp(initialData) {
            return {
                openEditModal: false,
                openMapModal: false,
                form: {
                    name: initialData.name,
                    email: initialData.email,
                    phone: initialData.phone,
                    address: initialData.address,
                    lat: initialData.lat,
                    lng: initialData.lng
                },
                map: null,
                marker: null,

                openMapPicker() {
                    this.openMapModal = true;
                    this.$nextTick(() => {
                        this.initMap();
                    });
                },

                initMap() {
                    const lat = parseFloat(this.form.lat) || -5.147665;
                    const lng = parseFloat(this.form.lng) || 119.432731;

                    if (!this.map) {
                        this.map = L.map('mapContainer').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);

                        this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);

                        this.marker.on('dragend', (e) => {
                            const position = e.target.getLatLng();
                            this.form.lat = position.lat.toFixed(6);
                            this.form.lng = position.lng.toFixed(6);
                            this.reverseGeocode(position.lat, position.lng);
                        });

                        this.map.on('click', (e) => {
                            this.marker.setLatLng(e.latlng);
                            this.form.lat = e.latlng.lat.toFixed(6);
                            this.form.lng = e.latlng.lng.toFixed(6);
                            this.reverseGeocode(e.latlng.lat, e.latlng.lng);
                        });
                    } else {
                        this.map.invalidateSize();
                        this.map.setView([lat, lng], 15);
                        this.marker.setLatLng([lat, lng]);
                    }
                },

                getCurrentLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition((position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.form.lat = lat.toFixed(6);
                            this.form.lng = lng.toFixed(6);
                            this.map.setView([lat, lng], 17);
                            this.marker.setLatLng([lat, lng]);
                            this.reverseGeocode(lat, lng);
                        }, () => {
                            alert('Gagal mendapatkan lokasi saat ini. Pastikan GPS aktif.');
                        });
                    } else {
                        alert('Browser Anda tidak mendukung deteksi lokasi (Geolocation).');
                    }
                },

                reverseGeocode(lat, lng) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.display_name) {
                                this.form.address = data.display_name;
                            }
                        })
                        .catch(() => {});
                },

                confirmLocation() {
                    this.openMapModal = false;
                }
            };
        }
    </script>
</body>
</html>