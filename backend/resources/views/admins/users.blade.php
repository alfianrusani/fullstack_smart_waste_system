<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Smart Waste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js untuk kontrol Modal Detail dan Modal Hapus -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 flex font-sans min-h-screen" x-data="{ openDetailModal: false, selectedUser: {}, openDeleteModal: false, deleteUrl: '' }">

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-green-800 text-white flex flex-col shadow-lg transition-all duration-300 z-40">
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
        
        <div class="mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen Pengguna</h1>
                <p class="text-gray-500 mt-1">Kelola akun pengguna di aplikasi.</p>
            </div>
            <!-- Tombol Tambah Pengguna (Create) -->
            <a href="{{ route('admins.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition shadow-sm flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Pengguna
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Search Bar -->
        <div class="bg-white overflow-hidden mb-6 shadow-sm rounded-t-lg border-b border-gray-200 p-5">
            <!-- UBAH route di action form menjadi admins.users -->
            <form action="{{ route('admins.users') }}" method="GET" class="flex gap-3">
                <div class="relative flex-1">
                    <i class="fa-solid fa-search absolute left-3 top-3.5 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email pengguna..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 transition">
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg transition shadow-sm">Cari</button>
                
                <!-- UBAH route pada tombol reset menjadi admins.users -->
                <a href="{{ route('admins.users') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg transition flex items-center justify-center border border-gray-300">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </form>
        </div>

        <!-- Tabel Pengguna -->
        <div class="bg-white rounded-b-lg shadow-sm overflow-hidden mb-6 border border-t-0 border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wide border-b border-gray-200">
                            <th class="p-4">Nama Lengkap</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Tanggal Bergabung</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $index => $user)
                            <tr class="hover:bg-green-50/50 transition">
                                <td class="p-4">
                                    <div class="font-semibold text-gray-800 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td class="p-4 text-gray-600">{{ $user->email }}</td>
                                <td class="p-4 text-gray-500 text-sm"><i class="fa-regular fa-calendar mr-1"></i> {{ $user->created_at->format('d M Y, H:i') }}</td>
                                
                                <!-- Kolom Aksi -->
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Read (Detail Modal) -->
                                        <button type="button" 
                                            @click="selectedUser = { 
                                                id: '{{ $user->id }}', 
                                                name: '{{ addslashes($user->name) }}', 
                                                email: '{{ $user->email }}',
                                                role: '{{ ucfirst($user->role) }}', 
                                                date: '{{ $user->created_at->format('d M Y, H:i') }}', 
                                                initials: '{{ strtoupper(substr($user->name, 0, 1)) }}' 
                                            }; openDetailModal = true"
                                            class="bg-sky-500 hover:bg-sky-600 text-white text-xs p-2 rounded transition shadow-sm" title="Lihat Profil">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <!-- Update (Edit Page) -->
                                        <a href="{{ route('admins.users.edit', $user->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs p-2 rounded transition shadow-sm" title="Edit Pengguna">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <!-- Delete (Modal Konfirmasi) -->
                                        <button type="button" 
                                            @click="deleteUrl = '{{ route('admins.users.delete', $user->id) }}'; openDeleteModal = true"
                                            class="bg-red-500 hover:bg-red-600 text-white text-xs p-2 rounded transition shadow-sm" title="Hapus Pengguna">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-users-slash text-4xl text-gray-300 mb-3 block"></i>
                                    Tidak ada data pengguna yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4">
            {{ $users->appends(request()->query())->links('pagination::tailwind') }}
        </div>

    </main>

    <!-- 1. MODAL DETAIL PENGGUNA (READ) -->
    <div x-show="openDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm overflow-hidden" @click.away="openDetailModal = false">
            <div class="bg-green-800 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold"><i class="fa-solid fa-id-card mr-2"></i> Profil Pengguna</h3>
                <button @click="openDetailModal = false" class="text-green-200 hover:text-white text-xl">&times;</button>
            </div>
            <div class="p-6 text-center">
                <div class="w-20 h-20 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-3xl mx-auto mb-4" x-text="selectedUser.initials"></div>
                
                <h4 class="text-xl font-bold text-gray-800" x-text="selectedUser.name"></h4>
                <p class="text-gray-500 text-sm mb-2" x-text="selectedUser.email"></p>
                
                <!-- Tambahan Badge Role Pengguna -->
                <div class="mb-4">
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full border" 
                        :class="selectedUser.role === 'Admin' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-blue-50 text-blue-700 border-blue-200'">
                        <i class="fa-solid" :class="selectedUser.role === 'Admin' ? 'fa-user-shield' : 'fa-user'"></i>
                        <span x-text="selectedUser.role" class="ml-1"></span>
                    </span>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-600 border border-gray-200">
                    <p class="font-semibold mb-1">Bergabung Sejak</p>
                    <p x-text="selectedUser.date"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MODAL KONFIRMASI HAPUS (DELETE) -->
    <div x-show="openDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden transform transition-all" @click.away="openDeleteModal = false">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Akun Pengguna</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini akan menghapus semua data terkait pengguna.
                </p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="openDeleteModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-lg transition">Batal</button>
                    <form :action="deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition shadow-sm">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>