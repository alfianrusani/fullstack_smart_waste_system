<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya - Smart Waste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js untuk kontrol Modal Detail -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 flex font-sans min-h-screen" x-data="{ openDetailModal: false, selectedReport: {} }">

    <!-- Sidebar User -->
    <aside id="sidebar" class="w-64 bg-green-800 text-white min-h-screen flex flex-col shadow-lg transition-all duration-300 z-40 hidden md:flex">
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
            <h1 class="text-3xl font-bold text-gray-800">Laporan Saya</h1>
            <p class="text-gray-500 mt-1">Daftar seluruh laporan limbah sampah yang telah Anda kirimkan beserta status penanganannya.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="bg-white overflow-hidden mb-6 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 rounded-t-lg border-b border-gray-200">
            <form action="{{ route('user.reports') }}" method="GET" class="flex flex-col md:flex-row w-full gap-3 p-5">
                <div class="relative flex-1">
                    <i class="fa-solid fa-search absolute left-3 top-3.5 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori limbah atau lokasi..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 transition">
                </div>
                
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 bg-white text-gray-700">
                    <option value="">Semua Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>Diproses</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
                
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg transition shadow-sm">
                    Filter
                </button>
                <a href="{{ route('user.reports') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg transition flex items-center justify-center border border-gray-300" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </form>
        </div>

        <!-- Tabel Data Laporan User -->
        <div class="bg-white rounded-b-lg shadow-sm overflow-hidden mb-6 border border-t-0 border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wide border-b border-gray-200">
                            <th class="p-4 w-20">Visual</th>
                            <th class="p-4">Waktu Laporan</th>
                            <th class="p-4">Lokasi (GPS)</th>
                            <th class="p-4">Kategori & Detail</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center w-36">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reports as $report)
                            <tr class="hover:bg-green-50/50 transition">
                                <!-- Visual Image -->
                                <td class="p-4">
                                    @if($report->image_url)
                                        <img src="{{ $report->image_url }}" alt="Limbah" class="w-14 h-14 object-cover rounded-md shadow-sm border border-gray-200">
                                    @else
                                        <div class="w-14 h-14 bg-gray-100 rounded-md flex items-center justify-center text-gray-400 border border-gray-200">
                                            <i class="fa-solid fa-image text-xl"></i>
                                        </div>
                                    @endif
                                </td>

                                <!-- Waktu -->
                                <td class="p-4">
                                    <div class="font-semibold text-gray-800 text-sm">
                                        {{ $report->created_at->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <i class="fa-regular fa-clock mr-1"></i> {{ $report->created_at->format('H:i') }} WIB
                                    </div>
                                </td>

                                <!-- Lokasi -->
                                <td class="p-4 max-w-xs">
                                    <div class="flex items-start gap-2">
                                        <i class="fa-solid fa-location-dot text-red-500 mt-1 shrink-0"></i>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-700 line-clamp-2" title="{{ $report->address }}">
                                                {{ $report->address ?? 'Gagal memuat lokasi' }}
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
                                        // Mengubah status ke huruf kecil untuk pengecekan, lalu menerjemahkan & memberi warna
                                        $statusData = match(strtolower($report->status)) {
                                            'pending', 'menunggu' => ['label' => 'Menunggu', 'style' => 'bg-yellow-50 text-yellow-700 border-yellow-200'],
                                            'in progress', 'in_progress', 'proses', 'diproses' => ['label' => 'Diproses', 'style' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                            'completed', 'selesai' => ['label' => 'Selesai', 'style' => 'bg-green-50 text-green-700 border-green-200'],
                                            'rejected', 'ditolak' => ['label' => 'Ditolak', 'style' => 'bg-red-50 text-red-700 border-red-200'],
                                            default => ['label' => $report->status, 'style' => 'bg-gray-50 text-gray-700 border-gray-200'],
                                        };
                                    @endphp
                                    <span class="border {{ $statusData['style'] }} px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $statusData['label'] }}
                                    </span>
                                </td>

                                <!-- Tombol READ / DETAIL -->
                                <td class="p-4 text-center">
                                    <button type="button" 
                                        @click="selectedReport = { 
                                            id: '{{ $report->id }}',
                                            date: '{{ $report->created_at->format('d M Y, H:i') }}',
                                            category: '{{ $report->category }}',
                                            status: '{{ $report->status }}',
                                            location: '{{ $report->address ?? 'Gagal memuat lokasi' }}',
                                            description: '{{ addslashes($report->description) }}',
                                            image: '{{ $report->image_url }}',
                                            lat: '{{ $report->latitude }}',
                                            lng: '{{ $report->longitude }}'
                                        }; openDetailModal = true"
                                        class="bg-sky-500 hover:bg-sky-600 text-white text-xs p-2 rounded transition shadow-sm" title="Lihat Detail Laporan">
                                        <i class="fa-solid fa-eye mr-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-3 block"></i>
                                    Belum ada data laporan sampah yang Anda buat.
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

    <!-- MODAL DETAIL LAPORAN (READ ONLY) -->
    <div x-show="openDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all" @click.away="openDetailModal = false">
            
            <!-- Header Modal -->
            <div class="bg-green-800 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold flex items-center"><i class="fa-solid fa-file-lines mr-2"></i> Rincian Laporan Saya</h3>
                <button @click="openDetailModal = false" class="text-green-200 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <!-- Isi Modal -->
            <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                <template x-if="selectedReport.image">
                    <img :src="selectedReport.image" alt="Foto Laporan" class="w-full aspect-square max-h-80 object-cover rounded-lg border border-gray-200 mx-auto shadow-sm">
                </template>
                
                <div class="grid grid-cols-2 gap-4 text-sm">

                    <!-- Waktu Pelaporan -->
                    <div>
                        <p class="text-gray-500 font-medium">Waktu Pelaporan</p>
                        <p class="font-bold text-gray-800" x-text="selectedReport.date"></p>
                    </div>

                    <!-- Kategori Limbah -->
                    <div>
                        <p class="text-gray-500 font-medium">Kategori Limbah</p>
                        <p class="font-bold" 
                        :class="{
                            'text-orange-500': selectedReport.category === 'Sampah Plastik' || selectedReport.category === 'Plastic Waste',
                            'text-sky-500': selectedReport.category === 'Sampah Kertas' || selectedReport.category === 'Paper Waste',
                            'text-green-500': selectedReport.category === 'Sampah Organik' || selectedReport.category === 'Organic Waste',
                            'text-gray-600': !['Sampah Plastik', 'Plastic Waste', 'Sampah Kertas', 'Paper Waste', 'Sampah Organik', 'Organic Waste'].includes(selectedReport.category)
                        }" 
                        x-text="selectedReport.category"></p>
                    </div>

                    <!-- Status Penanganan -->
                    <div>
                        <p class="text-gray-500 font-medium">Status Penanganan</p>
                        <!-- Logika ini menjamin teksnya selalu Bahasa Indonesia dan warnanya pas, tak peduli data aslinya Inggris atau Indonesia -->
                        <p class="font-bold" 
                        :class="{
                            'text-yellow-600': selectedReport.status === 'Pending' || selectedReport.status === 'Menunggu',
                            'text-blue-600': selectedReport.status === 'In Progress' || selectedReport.status === 'Diproses',
                            'text-green-600': selectedReport.status === 'Completed' || selectedReport.status === 'Selesai',
                            'text-red-600': selectedReport.status === 'Rejected' || selectedReport.status === 'Ditolak'
                        }" 
                        x-text="
                            (selectedReport.status === 'Pending' || selectedReport.status === 'Menunggu') ? 'Menunggu' :
                            (selectedReport.status === 'In Progress' || selectedReport.status === 'Diproses') ? 'Diproses' :
                            (selectedReport.status === 'Completed' || selectedReport.status === 'Selesai') ? 'Selesai' :
                            (selectedReport.status === 'Rejected' || selectedReport.status === 'Ditolak') ? 'Ditolak' : 
                            selectedReport.status
                        "></p>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <p class="text-gray-500 font-medium text-sm">Deskripsi</p>
                        <p class="font-semibold text-gray-700 text-sm mt-0.5" x-text="selectedReport.description"></p>
                    </div>
                </div>
                
                <!-- Lokasi Pelaporan -->
                <div>
                    <p class="text-gray-500 font-medium text-sm">Lokasi Penjemputan</p>
                    <p class="font-semibold text-gray-700 text-sm mt-0.5" x-text="selectedReport.location"></p>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="bg-gray-50 px-6 py-3 text-right border-t border-gray-200">
                <button @click="openDetailModal = false" class="bg-gray-600 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg transition font-medium">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</body>
</html>