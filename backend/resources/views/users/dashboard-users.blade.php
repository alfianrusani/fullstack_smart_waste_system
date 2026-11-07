<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Smart Waste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Pustaka Chart.js untuk menggambar grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 flex font-sans">

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
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Saya</h1>
            <p class="text-gray-500 mt-1">Ringkasan status laporan sampah dan analitik hasil pemindaian limbah Anda.</p>
        </div>

        <!-- Stats Cards (Khusus Data Laporan User) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            
            <!-- Total Laporan -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-600 hover:shadow-lg hover:-translate-y-1 transition duration-200">
                <p class="text-gray-500 text-sm font-semibold">Total Laporan Saya</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $totalReports }}</h3>
            </div>
            
            <!-- Pending -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-600 hover:shadow-lg hover:-translate-y-1 transition duration-200">
                <p class="text-gray-500 text-sm font-semibold">Pending</p>
                <h3 class="text-3xl font-bold text-yellow-400 mt-2">{{ $pendingReports }}</h3>
            </div>
            
            <!-- In Progress -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-600 hover:shadow-lg hover:-translate-y-1 transition duration-200">
                <p class="text-gray-500 text-sm font-semibold">In Progress</p>
                <h3 class="text-3xl font-bold text-blue-400 mt-2">{{ $inProgressReports }}</h3>
            </div>
            
            <!-- Completed -->
            <div class="bg-green-500 p-6 rounded-lg shadow-md border-l-4 border-green-600 hover:shadow-lg hover:-translate-y-1 transition duration-200">
                <p class="text-white text-sm font-semibold">Completed</p>
                <h3 class="text-3xl font-bold text-white mt-2">{{ $completedReports }}</h3>
            </div>
            
            <!-- Rejected -->
            <div class="bg-red-500 p-6 rounded-lg shadow-md border-l-4 border-red-600 hover:shadow-lg hover:-translate-y-1 transition duration-200">
                <p class="text-white text-sm font-semibold">Rejected</p>
                <h3 class="text-3xl font-bold text-white mt-2">{{ $rejectedReports }}</h3>
            </div>

        </div>

        @if($totalReports > 0)
            <!-- Analitik Bar Chart (Status Laporan User) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 flex flex-col md:flex-row items-center gap-12 mb-8">
                <div class="w-full md:w-1/2 flex justify-center">
                    <canvas id="statusChart" class="max-h-72"></canvas>
                </div>
                
                <!-- Deskripsi -->
                <div class="w-full md:w-1/2">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3"><i class="fa-solid fa-chart-column text-blue-600"></i></div>
                        Status Penanganan Laporan Anda
                    </h3>
                    <p class="text-gray-600 leading-relaxed mb-4 text-sm md:text-base">
                        Grafik batang di samping menampilkan rekapitulasi status dari seluruh laporan limbah yang telah Anda kirimkan. Tim operasional kami memproses laporan Anda secara bertahap mulai dari verifikasi hingga penanganan di lapangan.
                    </p>
                    <div class="bg-blue-50 text-blue-800 p-4 rounded-lg text-sm border border-blue-200 shadow-sm">
                        <i class="fa-solid fa-circle-info text-blue-500 mr-2"></i>
                        <strong>Informasi Tambahan:</strong> Laporan berstatus "Completed" menandakan lokasi sampah yang Anda laporkan telah selesai ditangani oleh petugas.
                    </div>
                </div>
            </div>

            <!-- Analitik Pie/Doughnut Chart (Kategori Sampah User) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 flex flex-col md:flex-row items-center gap-12 mb-8">
                
                <!-- Area Canvas Chart -->
                <div class="w-full md:w-1/2 flex justify-center">
                    <canvas id="categoryChart" class="max-h-72"></canvas>
                </div>
                
                <!-- Deskripsi & Keterangan -->
                <div class="w-full md:w-1/2">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fa-solid fa-chart-pie text-green-600 mr-2"></i> Distribusi Kategori Sampah Anda
                    </h3>
                    <p class="text-gray-600 leading-relaxed mb-4 text-sm md:text-base">
                        Grafik ini memperlihatkan persebaran jenis limbah yang telah Anda pindai (<i>scan</i>) dan laporkan melalui aplikasi seluler.
                    </p>
                    <div class="bg-green-50 text-green-800 p-4 rounded-lg text-sm border border-green-200">
                        <i class="fa-solid fa-leaf text-green-600 mr-2"></i>
                        <strong>Terima Kasih!</strong> Kontribusi Anda dalam memilah dan melaporkan sampah sangat membantu menjaga kebersihan lingkungan bersama.
                    </div>
                </div>
            </div>
        @else
            <!-- Tampilan Bersih Ketika Belum Ada Laporan (Empty State) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center mb-8">
                <div class="w-16 h-16 bg-green-50 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-folder-open text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Belum Ada Data Grafik</h3>
                <p class="text-gray-500 text-sm mt-1 max-w-md mx-auto">
                    Grafik statistik penanganan dan distribusi kategori sampah akan otomatis muncul di sini setelah Anda melakukan pemindaian dan mengirimkan laporan pertama.
                </p>
            </div>
        @endif

    </main>

    @if($totalReports > 0)
        <!-- Script Konfigurasi Chart.js hanya dijalankan jika ada data -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Doughnut Chart Kategori Sampah User
                const ctx = document.getElementById('categoryChart').getContext('2d');
                const labels = {!! json_encode($chartLabels) !!};
                const data = {!! json_encode($chartValues) !!};

                // Pemetaan warna otomatis berdasarkan nama label dari database
                const backgroundColors = labels.map(label => {
                    const l = label.toLowerCase();
                    if (l.includes('organik')) return '#22c55e'; // Hijau untuk Organik
                    if (l.includes('kertas')) return '#38bdf8';  // Biru muda untuk Kertas
                    if (l.includes('plastik')) return '#f97316'; // Oranye untuk Plastik
                    return '#9ca3af'; // Warna default cadangan
                });

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels, // Menggunakan label dinamis dari backend
                        datasets: [{
                            data: data, 
                            backgroundColor: backgroundColors, // Menggunakan warna dinamis
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 13
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });

                // Bar Chart Status Laporan User
                const ctxBar = document.getElementById('statusChart').getContext('2d');
                const labelsBar = {!! json_encode($statusLabels) !!};
                const dataBar = {!! json_encode($statusValues) !!};

                new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: labelsBar,
                        datasets: [{
                            label: 'Jumlah Laporan Saya',
                            data: dataBar,
                            backgroundColor: ['#F59E0B', '#3B82F6', '#10B981', '#EF4444'], 
                            borderRadius: 6,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</body>
</html>