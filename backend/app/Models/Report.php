<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class Report extends Model
{
    use HasFactory;

    // Pastikan latitude dan longitude masuk dalam fillable (sesuaikan dengan kode Anda)
    protected $fillable = ['user_id', 'category', 'description', 'image_url', 'latitude', 'longitude', 'status', 'address'];

    // Relasi ke tabel User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor baru untuk mendapatkan Nama Kelurahan & Kecamatan dari API
    public function getLocationNameAttribute()
    {
        // Pastikan data koordinat tersedia
        if (!$this->latitude || !$this->longitude) {
            return 'Lokasi tidak dilampirkan';
        }

        // Buat kunci cache unik berdasarkan titik koordinat
        $cacheKey = 'location_' . $this->latitude . '_' . $this->longitude;

        // Simpan hasil dari API di Cache selama 7 hari agar loading dashboard tetap super cepat
        return Cache::remember($cacheKey, now()->addDays(7), function () {
            try {
                // Memanggil API OpenStreetMap (Gratis & Tanpa API Key)
                $response = Http::withHeaders([
                    'User-Agent' => 'SmartWasteSystem/1.0' // Diwajibkan oleh Nominatim
                ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $this->latitude,
                    'lon' => $this->longitude,
                    'zoom' => 14 // Zoom level 14 biasanya pas untuk Kelurahan/Kecamatan
                ]);

                if ($response->successful()) {
                    $address = $response->json()['address'] ?? [];
                    
                    // Ambil kelurahan dan kecamatan
                    $kelurahan = $address['village'] ?? $address['suburb'] ?? $address['residential'] ?? '';
                    $kecamatan = $address['county'] ?? $address['city_district'] ?? $address['city'] ?? $address['town'] ?? '';

                    $lokasi = trim($kelurahan . ', ' . $kecamatan, ', ');
                    return $lokasi ?: 'Area tidak diketahui';
                }
            } catch (\Exception $e) {
                return 'Gagal memuat lokasi';
            }

            return 'Sinyal GPS Lemah';
        });
    }
}