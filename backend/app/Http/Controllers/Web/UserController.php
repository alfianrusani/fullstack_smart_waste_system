<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * 1. Tampilkan Dashboard Khusus User Aktif
     */
    public function index()
    {
        $userId = Auth::id();

        $totalReports = Report::where('user_id', $userId)->count();
        $pendingReports = Report::where('user_id', $userId)->where('status', 'Pending')->count();
        $inProgressReports = Report::where('user_id', $userId)->where('status', 'In Progress')->count();
        $completedReports = Report::where('user_id', $userId)->where('status', 'Completed')->count();
        $rejectedReports = Report::where('user_id', $userId)->where('status', 'Rejected')->count();

        $reportsByCategory = Report::where('user_id', $userId)
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->get();

        $statusLabels = ['Pending', 'In Progress', 'Completed', 'Rejected'];
        $statusValues = [$pendingReports, $inProgressReports, $completedReports, $rejectedReports];

        $chartLabels = $reportsByCategory->pluck('category');
        $chartValues = $reportsByCategory->pluck('total');

        return view('users.dashboard-users', compact(
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
     * 2. Tampilkan Halaman Data Laporan Saya
     */
    public function reports(Request $request)
    {
        $query = Report::where('user_id', Auth::id());

        // Logika Pencarian Teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%")
                ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(5);

        $reports->appends($request->query()); 

        return view('users.reports-users', compact('reports')); // Sesuaikan dengan nama view Blade Anda
    }

    /**
     * 3. Simpan Data Laporan Sampah ke Database
     */
    public function storeReport(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        Report::create([
            'user_id' => Auth::id(),
            'category' => $request->category,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'Pending',
        ]);

        return redirect()->route('user.reports')->with('success', 'Laporan penjemputan sampah berhasil dibuat!');
    }

    /**
     * 4. Tampilkan Halaman Pengaturan Akun
     */
    public function profile()
    {
        $user = Auth::user();
        return view('users.profile-users', compact('user'));
    }

    /**
     * 5. Update Data Akun User via Modal Edit
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'address.required' => 'Alamat lengkap penjemputan wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Cek jika request dari Aplikasi Mobile (mengharapkan JSON)
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Data akun berhasil diperbarui!',
                'data' => $user
            ], 200);
        }

        // Jika diakses dari website, kembalikan dengan redirect seperti biasa
        return redirect()->back()->with('success', 'Data akun berhasil diperbarui!');
    }
}