<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Halaman Dashboard Utama (Overview, Statistik, dan Pie Chart)
     */
    public function index()
    {
        // 1. Data untuk Kartu Statistik (Cards)
        $totalReports = Report::count();
        $pendingReports = Report::where('status', 'Pending')->count();
        $inProgressReports = Report::where('status', 'In Progress')->count();
        $completedReports = Report::where('status', 'Completed')->count();
        $rejectedReports = Report::where('status', 'Rejected')->count();

        // 2. Data untuk Pie Chart (Menghitung jumlah laporan per kategori sampah)
        $reportsByCategory = Report::selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->get();

        $statusLabels = ['Pending', 'In Progress', 'Completed', 'Rejected'];
        $statusValues = [$pendingReports, $inProgressReports, $completedReports, $rejectedReports];

        $chartLabels = $reportsByCategory->pluck('category');
        $chartValues = $reportsByCategory->pluck('total');

        return view('admins.dashboard', compact(
            'totalReports', 
            'pendingReports',
            'inProgressReports', 
            'completedReports',
            'rejectedReports', 
            'chartLabels', 
            'chartValues',
            'statusLabels',
            'statusValues'
        ));
    }

    /**
     * Halaman Khusus Data Laporan (Tabel, Filter, & Paginasi)
     */
    public function reports(Request $request)
    {
        $query = Report::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(5);
        return view('admins.reports', compact('reports'));
    }

    /**
     * Update Status Laporan
     */
    public function updateStatus(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $report->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Status berhasil diperbarui!');
    }

    /**
     * Hapus Data Laporan
     */
    public function deleteReport($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }

    /**
     * Halaman Khusus Manajemen Pengguna
     */
    public function users(Request $request)
    {
        // Mulai query untuk pengguna
        $query = User::query();

        // Logika jika ada input pencarian di form
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Mencari berdasarkan nama atau email (menggunakan ilike agar kebal huruf besar/kecil)
                $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Eksekusi query dengan paginasi (misal 5 atau 10 data per halaman)
        $users = $query->latest()->paginate(5);

        return view('admins.users', compact('users')); // Sesuaikan nama view Blade Anda
    }
        
    /**
     * Tampilkan form Tambah Pengguna (Create)
    */
    public function createUser()
    {
        return view('admins.create');
        }

        /**
     * Simpan Pengguna Baru ke Database (Store)
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,user',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('admins.users')->with('success', 'Pengguna baru berhasil ditambahkan!');
    }

    /**
     * Tampilkan form Edit Pengguna (Update)
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admins.create', compact('user'));
    }

    /**
     * Simpan Perubahan Data Pengguna (Update)
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6', // Password opsional saat diedit
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        
        // Update password hanya jika form password diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        return redirect()->route('admins.users')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    /**
     * Hapus Pengguna
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Pengguna berhasil dihapus!');
    }
}