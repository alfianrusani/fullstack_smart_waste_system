<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Laporan - Smart Waste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js untuk kontrol Modal Detail dan Modal Hapus -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 flex font-sans min-h-screen" x-data="{ openDetailModal: false, selectedReport: {}, openDeleteModal: false, deleteUrl: '' }">
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
            <a href="{{ route('admins.reports') }}" class="flex items-center p-3 bg-green-700 rounded-lg text-white shadow-inner transition">
                <i class="fa-solid fa-clipboard-list w-6"></i> Data Laporan
            </a>

            <!-- Link Pengguna -->
            <a href="{{ route('admins.users') }}" class="flex items-center p-3 rounded-lg text-green-200 hover:bg-green-700 hover:text-white transition">
                <i class="fa-solid fa-users w-6"></i> Pengguna
            </a>
        </nav>
        <div class="p-4 border-t border-green-700">
            <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center p-2 bg-red-500 hover:bg-red-600 rounded text-white transition">
                    <i class="fa-solid fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        
        <!-- Header Page -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Laporan Sampah</h1>
            <p class="text-gray-500 mt-1">Kelola laporan, perbarui status penjemputan, lihat rincian, dan hapus data.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="bg-white overflow-hidden mb-6 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 rounded-t-lg border-b border-gray-200">
            <form action="{{ route('admins.reports') }}" method="GET" class="flex flex-col md:flex-row w-full gap-3 p-5">
                <div class="relative flex-1">
                    <i class="fa-solid fa-search absolute left-3 top-3.5 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori limbah atau nama pelapor..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 transition">
                </div>
                
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 bg-white text-gray-700">
                    <option value="">Semua Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg transition shadow-sm">
                    Filter
                </button>
                <a href="{{ route('admins.reports') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg transition flex items-center justify-center border border-gray-300" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-b-lg shadow-sm overflow-hidden mb-6 border border-t-0 border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wide border-b border-gray-200">
                            <th class="p-4 w-20">Visual</th>
                            <th class="p-4">Info Pelapor</th>
                            <th class="p-4">Lokasi Penjemputan</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Deskripsi</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center w-48">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reports as $report)
                            <tr class="hover:bg-green-50/50 transition">
                                <!-- Visual -->
                                <td class="p-4">
                                    @if($report->image_url)
                                        <img src="{{ $report->image_url }}" alt="Limbah" class="w-14 h-14 object-cover rounded-md shadow-sm border border-gray-200">
                                    @else
                                        <div class="w-14 h-14 bg-gray-100 rounded-md flex items-center justify-center text-gray-400 border border-gray-200">
                                            <i class="fa-solid fa-image text-xl"></i>
                                        </div>
                                    @endif
                                </td>

                                <!-- Pelapor -->
                                <td class="p-4">
                                    <div class="font-semibold text-gray-800">{{ $report->user->name ?? 'Pengguna Anonim' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <i class="fa-regular fa-clock mr-1"></i> {{ $report->created_at->format('d M Y, H:i') }}
                                    </div>
                                </td>

                                <!-- Lokasi -->
                                <td class="p-4 max-w-xs">
                                    <div class="flex items-start gap-2">
                                        <!-- Tambahkan shrink-0 agar ikon tidak ikut mengecil atau gepeng -->
                                        <i class="fa-solid fa-location-dot text-red-500 mt-1 shrink-0"></i>
                                        
                                        <!-- Tambahkan min-w-0 agar pembatasan teks berfungsi di dalam flexbox -->
                                        <div class="min-w-0">
                                            <!-- Tambahkan line-clamp-2 untuk membatasi maksimal 2 baris dan title untuk memunculkan teks lengkap saat di-hover -->
                                            <p class="text-sm font-semibold text-gray-700 line-clamp-2" title="{{ $report->address }}">
                                                {{ $report->address ?? 'Alamat tidak tersedia' }}
                                            </p>
                                            @if($report->latitude && $report->longitude)
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }}" target="_blank" class="text-xs text-blue-600 hover:underline block mt-1">
                                                    Buka Maps &rarr;
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Kategori -->
                                <td class="p-4">
                                    @php
                                        $catStyle = match($report->category) {
                                            'Sampah Plastik' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'Sampah Kertas' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'Sampah Organik' => 'bg-green-100 text-green-800 border-green-200',
                                            default => 'bg-blue-50 text-blue-700 border-blue-200',
                                        };
                                    @endphp
                                    <span class="{{ $catStyle }} border text-xs font-bold px-2.5 py-1 rounded-full">
                                        {{ $report->category }}
                                    </span>
                                </td>

                                <!-- Detail / Deskripsi -->
                                <td class="p-4">
                                    <p class="text-sm text-gray-600 line-clamp-2" title="{{ $report->description }}">
                                        {{ $report->description ?: 'Tidak ada deskripsi' }}
                                    </p>
                                </td>

                                <!-- Status -->
                                <td class="p-4 text-center">
                                    @php
                                        $badgeStyle = match($report->status) {
                                            'Pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                            'In Progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Completed' => 'bg-green-50 text-green-700 border-green-200',
                                            'Rejected' => 'bg-red-50 text-red-700 border-red-200',
                                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="border {{ $badgeStyle }} px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $report->status }}
                                    </span>
                                </td>

                                <!-- KOLOM TINDAKAN (READ, UPDATE STATUS, DELETE) -->
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">

                                        <!-- Form UPDATE STATUS -->
                                        <form action="{{ route('admins.reports.update', $report->id) }}" method="POST" class="flex items-center gap-1">
                                            @csrf
                                            <!-- @method('PUT') dihapus karena rute di web.php menggunakan Route::post -->
                                            
                                            <select name="status" class="text-xs border border-gray-300 rounded p-1.5 focus:outline-none focus:border-blue-500 bg-white">
                                                <option value="Pending" {{ $report->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="In Progress" {{ $report->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="Completed" {{ $report->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="Rejected" {{ $report->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                            </select>
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs p-2 rounded transition shadow-sm" title="Simpan Status">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>

                                        <!-- Tombol READ / DETAIL -->
                                        <button type="button" 
                                            @click="selectedReport = { 
                                                id: '{{ $report->id }}',
                                                user: '{{ $report->user->name ?? 'Pengguna Anonim' }}',
                                                date: '{{ $report->created_at->format('d M Y, H:i') }}',
                                                category: '{{ $report->category }}',
                                                status: '{{ $report->status }}',
                                                location: '{{ $report->address ?? 'Lokasi tidak tersedia' }}',
                                                description: '{{ addslashes($report->description) }}',
                                                image: '{{ $report->image_url }}',
                                                lat: '{{ $report->latitude }}',
                                                lng: '{{ $report->longitude }}'
                                            }; openDetailModal = true"
                                            class="bg-sky-500 hover:bg-sky-600 text-white text-xs p-2 rounded transition shadow-sm" title="Lihat Detail Laporan">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <!-- Tombol DELETE (Pemicu Modal Pop-up) -->
                                        <button type="button" 
                                            @click="deleteUrl = '{{ route('admins.reports.delete', $report->id) }}'; openDeleteModal = true"
                                            class="bg-red-500 hover:bg-red-600 text-white text-xs p-2 rounded transition shadow-sm" title="Hapus Laporan">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-3 block"></i>
                                    Tidak ada data laporan yang sesuai dengan pencarian Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginasi -->
        <div class="mt-4">
            {{ $reports->appends(request()->query())->links('pagination::tailwind') }}
        </div>

    </main>

    <!-- MODAL DETAIL LAPORAN (READ) -->
    <div x-show="openDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden" @click.away="openDetailModal = false">
            <div class="bg-green-800 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold"><i class="fa-solid fa-file-lines mr-2"></i> Rincian Laporan</h3>
                <button @click="openDetailModal = false" class="text-green-200 hover:text-white text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                <template x-if="selectedReport.image">
                    <img :src="selectedReport.image" alt="Foto Laporan" class="w-full aspect-square max-h-80 object-cover rounded-lg border border-gray-200 mx-auto">
                </template>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 font-medium">Pelapor</p>
                        <p class="font-bold text-gray-800" x-text="selectedReport.user"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-medium">Waktu</p>
                        <p class="font-bold text-gray-800" x-text="selectedReport.date"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-medium">Kategori</p>
                        <p class="font-bold" 
                        :class="{
                            'text-yellow-600': selectedReport.category === 'Sampah Plastik',
                            'text-blue-600': selectedReport.category === 'Sampah Kertas',
                            'text-green-600': selectedReport.category === 'Sampah Organik',
                            'text-gray-600': selectedReport.category !== 'Sampah Plastik' && selectedReport.category !== 'Sampah Kertas' && selectedReport.category !== 'Sampah Organik'}" 
                        x-text="selectedReport.category"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-medium">Status Operasional</p>
                        <p class="font-bold" 
                        :class="{
                            'text-yellow-600': selectedReport.status === 'Pending',
                            'text-blue-600': selectedReport.status === 'In Progress',
                            'text-green-600': selectedReport.status === 'Completed',
                            'text-red-600': selectedReport.status === 'Rejected'
                        }" 
                        x-text="selectedReport.status"></p>
                    </div>
                </div>
                <div>
                    <p class="text-gray-500 font-medium text-sm">Lokasi Terdeteksi</p>
                    <p class="font-semibold text-gray-700 text-sm" x-text="selectedReport.location"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium text-sm">Deskripsi</p>
                    <p class="font-semibold text-gray-700 text-sm mt-0.5" x-text="selectedReport.description"></p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 text-right border-t border-gray-200">
                <button @click="openDetailModal = false" class="bg-gray-600 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS (DELETE) -->
    <div x-show="openDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden transform transition-all" @click.away="openDeleteModal = false">
            <div class="p-6 text-center">
                <!-- Ikon Peringatan -->
                <div class="w-14 h-14 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                
                <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Data Laporan</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Apakah Anda yakin ingin menghapus laporan ini secara permanen?
                </p>
                
                <!-- Tombol Aksi Konfirmasi -->
                <div class="flex justify-center gap-3">
                    <button type="button" @click="openDeleteModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-lg transition">
                        Batal
                    </button>
                    
                    <form :action="deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition shadow-sm">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>