<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\CloudinaryService;

class ReportController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $reports = Report::with('user:id,name,email')->orderBy('created_at', 'desc')->get();
        } else {
            $reports = Report::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        }

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function store(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'description' => 'required|string',
            'category' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            // 2. Unggah foto menggunakan CloudinaryService (atau simpan ke storage local/supabase)
            $uploadedFileUrl = $this->cloudinaryService->uploadImage($request->file('image'));

            // 3. Simpan ke database Supabase melalui model Report
            $report = Report::create([
                'user_id' => $request->user()->id,
                'image_url' => $uploadedFileUrl,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'description' => $request->description,
                'address' => $request->address,
                'category' => $request->category,
                'status' => 'Pending'
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Laporan berhasil dibuat', 
                'data' => $report
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengunggah laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,In Progress,Completed,Rejected'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $report = Report::findOrFail($id);
        $report->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status laporan diperbarui', 'data' => $report]);
    }
}