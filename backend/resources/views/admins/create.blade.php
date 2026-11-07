<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Pengguna - Smart Waste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js untuk kontrol Modal Detail dan Modal Hapus -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 flex font-sans min-h-screen">

    <!-- Sidebar Singkat -->
    <aside class="w-64 bg-green-800 text-white flex flex-col shadow-lg z-40 hidden md:flex">
        <div class="p-6 text-center border-b border-green-700">
            <h2 class="text-2xl font-bold"><i class="fa-solid fa-leaf mr-2"></i>Smart Waste</h2>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <!-- Link Dashboard -->
            <a href="{{ route('admins.dashboard') }}" class="flex items-center p-3 rounded-lg text-green-200 hover:bg-green-700 hover:text-white transition">
                <i class="fa-solid fa-chart-pie w-6"></i> Dashboard
            </a>

            <!-- Link Data Laporan -->
            <a href="{{ route('admins.reports') }}" class="flex items-center p-3 rounded-lg text-green-200 hover:bg-green-700 hover:text-white transition">
                <i class="fa-solid fa-clipboard-list w-6"></i> Data Laporan
            </a>

            <!-- Link Pengguna -->
            <a href="{{ route('admins.users') }}" class="flex items-center p-3 bg-green-700 rounded-lg text-white shadow-inner transition">
                <i class="fa-solid fa-users w-6"></i> Pengguna
            </a>
        </nav>
        <div class="p-4 border-t border-green-700">
            <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center p-2 bg-red-500 hover:bg-red-600 rounded text-white transition"><i class="fa-solid fa-sign-out-alt mr-2"></i> Logout</button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">{{ isset($user) ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}</h1>
            <p class="text-gray-500 mt-1">Lengkapi form di bawah ini dengan data yang valid.</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 max-w-2xl">
            <!-- Menentukan Action URL dan Method (POST/PUT) -->
            <form action="{{ isset($user) ? route('admins.users.update', $user->id) : route('admins.users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 @error('email') border-red-500 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if(!isset($user))
                    <!-- Pilihan Role HANYA muncul saat Tambah Pengguna -->
                    <div class="mb-5">
                        <label class="block text-gray-700 font-semibold mb-2">Peran (Role)</label>
                        <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 @error('role') border-red-500 @enderror">
                            <option value="" disabled selected>-- Pilih Peran Pengguna --</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User/Warga</option>
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @else
                    <!-- Saat Edit, Role ditampilkan sebagai info saja (dikunci / disabled) -->
                    <div class="mb-5">
                        <label class="block text-gray-700 font-semibold mb-2">Peran (Role)</label>
                        <input type="text" value="{{ ucfirst($user->role) }}" disabled class="w-full px-4 py-2 border border-gray-200 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed">
                        <p class="text-gray-400 text-xs mt-1"><i class="fa-solid fa-lock mr-1"></i> Role pengguna tidak dapat diubah setelah akun dibuat.</p>
                    </div>
                @endif

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Kata Sandi (Password)</label>
                    <input type="password" name="password" {{ isset($user) ? '' : 'required' }} class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 @error('password') border-red-500 @enderror">
                    @if(isset($user))
                        <p class="text-gray-400 text-xs mt-1">*Kosongkan jika tidak ingin mengubah password.</p>
                    @endif
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-save mr-2"></i> {{ isset($user) ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                    </button>
                    <a href="{{ route('admins.users') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 px-6 rounded-lg transition border border-gray-300">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>