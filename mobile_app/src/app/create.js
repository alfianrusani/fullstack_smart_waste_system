import React, { useState, useEffect } from 'react';
import { 
  StyleSheet, 
  Text, 
  View, 
  ScrollView, 
  TextInput, 
  TouchableOpacity, 
  SafeAreaView, 
  Alert, 
  ActivityIndicator,
  Image 
} from 'react-native';
import { Ionicons, FontAwesome5 } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { useRouter } from 'expo-router';
import * as Location from 'expo-location';
import axios from 'axios';

export default function CreateReportScreen() {
  const router = useRouter();
  
  const [facing, setFacing] = useState('back');
  const [permission, requestPermission] = useCameraPermissions();
  const [cameraOpen, setCameraOpen] = useState(false);
  const [capturedImage, setCapturedImage] = useState(null);
  const [scanning, setScanning] = useState(false);

  // Form State & Debugging State
  const [category, setCategory] = useState('');
  const [description, setDescription] = useState('');
  const [address, setAddress] = useState('');
  const [latitude, setLatitude] = useState('');
  const [longitude, setLongitude] = useState('');
  const [debugStatus, setDebugStatus] = useState('Menunggu proses pelacakan GPS...');
  const [loadingLocation, setLoadingLocation] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  // 1. Ambil Lokasi GPS secara Real-Time saat halaman dibuka
  useEffect(() => {
    handleUpdateLocation();
  }, []);

  const handleUpdateLocation = async () => {
    setLoadingLocation(true);
    setDebugStatus('Meminta izin akses lokasi...');
    
    try {
      let { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        setDebugStatus('❌ Izin lokasi ditolak.');
        setLoadingLocation(false);
        return;
      }

      setDebugStatus('Melacak lokasi secara real-time...');
      let location = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.Balanced,
      });
      
      const lat = location.coords.latitude;
      const lng = location.coords.longitude;
      
      setLatitude(lat.toString());
      setLongitude(lng.toString());
      setDebugStatus(`✔ Koordinat: ${lat.toFixed(5)}, ${lng.toFixed(5)}. Mengambil alamat...`);

      const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
        headers: {
          'User-Agent': 'SmartWasteApp/1.0'
        }
      });
      
      const textResponse = await response.text();
      
      if (textResponse.startsWith('{')) {
        const data = JSON.parse(textResponse);
        if (data && data.display_name) {
          setAddress(data.display_name);
          setDebugStatus('✔ Alamat berhasil dimuat!');
        } else {
          setAddress(`Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`);
          setDebugStatus('⚠ Koordinat didapat, nama jalan alternatif digunakan.');
        }
      } else {
        setAddress(`Jl. Koordinat GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)}`);
        setDebugStatus('✔ Koordinat GPS berhasil didapat!');
      }

    } catch (error) {
      setDebugStatus(`❌ Error: ${error.message}`);
      Alert.alert('Gagal Mendapatkan Alamat', 'Koordinat berhasil didapat, namun gagal menerjemahkan ke nama jalan.');
    } finally {
      setLoadingLocation(false);
    }
  };

  // 2. Buka Kamera
  const openCameraScanner = async () => {
    if (!permission || !permission.granted) {
      const permResult = await requestPermission();
      if (!permResult.granted) {
        Alert.alert('Izin Kamera Ditolak', 'Mohon izinkan akses kamera untuk melakukan scan sampah.');
        return;
      }
    }
    setCameraOpen(true);
  };

  // 3. Tangkap Foto & Integrasi ML API FastAPI (Menggunakan base64: true agar terdeteksi AI)
  const takePicture = async (cameraRef) => {
    if (cameraRef) {
      try {
        setScanning(true);
        const photo = await cameraRef.takePictureAsync({ quality: 0.8, base64: true });
        setCapturedImage(photo.uri);
        setCameraOpen(false);

        const mlResponse = await fetch('http://192.168.80.38:8001/predict', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            image: photo.base64,
          }),
        });

        const mlResult = await mlResponse.json();

        if (mlResponse.ok && mlResult.success) {
          setCategory(mlResult.category);
          setScanning(false);
          Alert.alert(
            'AI Scan Berhasil!', 
            `Jenis: ${mlResult.category}\nAkurasi: ${mlResult.confidence}%`
          );
        } else {
          setScanning(false);
          Alert.alert('Gagal Deteksi', mlResult.error || 'Model AI gagal memproses gambar.');
        }

      } catch (error) {
        setScanning(false);
        console.log("Detail Error Upload ML:", error);
        Alert.alert('Koneksi Gagal', 'Gagal mengirim gambar ke ML API FastAPI.');
      }
    }
  };

  // 4. Kirim Data Laporan ke Backend Laravel (`POST /api/reports`) Menggunakan Axios
  const handleSubmit = async () => {
    if (!category || !description || !address.trim() || !capturedImage) {
      Alert.alert('Peringatan', 'Harap lengkapi data laporan dan foto scan sampah!');
      return;
    }

    setSubmitting(true);

    try {
      let userToken = await AsyncStorage.getItem('userToken') || 
                      await AsyncStorage.getItem('token') || 
                      await AsyncStorage.getItem('access_token');

      if (!userToken) {
        Alert.alert('Sesi Berakhir', 'Token login tidak ditemukan. Silakan login ulang.');
        router.replace('/login');
        setSubmitting(false);
        return;
      }

      const formData = new FormData();
      formData.append('image', {
        uri: capturedImage,
        type: 'image/jpeg',
        name: 'report.jpg',
      });
      formData.append('category', category);
      formData.append('description', description);
      formData.append('address', address);
      formData.append('latitude', latitude ? latitude.toString() : '-5.1370843');
      formData.append('longitude', longitude ? longitude.toString() : '119.4765426');

      console.log("Mengirim laporan ke Laravel via Axios...");

      const response = await axios.post('http://192.168.80.38:8000/api/reports', formData, {
        headers: { 
          'Content-Type': 'multipart/form-data', 
          'Accept': 'application/json',
          'Authorization': `Bearer ${userToken}` 
        },
      });

      if (response.status === 201 || response.data.success) {
        Alert.alert('Sukses', 'Laporan penjemputan sampah berhasil dikirim ke sistem!', [
          { text: 'OK', onPress: () => router.replace('/report') }
        ]);
      } else {
        Alert.alert('Gagal', response.data.message || 'Gagal mengirim laporan.');
      }

    } catch (error) {
      console.log("Detail Error Laravel:", error.response?.data || error.message);
      const pesanServer = error.response?.data?.message || JSON.stringify(error.response?.data?.errors) || error.message;
      Alert.alert('Gagal Dikirim', `Alasan dari server:\n${pesanServer}`);
    } finally {
      setSubmitting(false);
    }
  };

  if (cameraOpen) {
    let cameraRef = null;
    return (
      <View style={{ flex: 1, backgroundColor: '#000' }}>
        <CameraView style={{ flex: 1 }} facing={facing} ref={(ref) => (cameraRef = ref)}>
          <View style={styles.cameraOverlay}>
            <TouchableOpacity style={styles.closeCameraButton} onPress={() => setCameraOpen(false)}>
              <Ionicons name="close" size={28} color="#fff" />
            </TouchableOpacity>
            
            <View style={styles.scanBoxFrame}>
              <Text style={styles.scanFrameText}>Arahkan kamera ke tumpukan sampah</Text>
            </View>

            <TouchableOpacity 
              style={styles.captureButton} 
              onPress={() => takePicture(cameraRef)}
            >
              <View style={styles.captureButtonInner} />
            </TouchableOpacity>
          </View>
        </CameraView>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      {/* Header Utama Konsisten */}
      <View style={styles.mainHeader}>
        <View style={styles.headerTitleContainer}>
          <Ionicons name="leaf" size={24} color="#ffffff" style={{ marginRight: 8 }} />
          <Text style={styles.mainHeaderTitle}>Smart Waste</Text>
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        
        {/* Judul Halaman */}
        <View style={styles.pageHeader}>
          <Text style={styles.pageTitle}>Buat Laporan Baru</Text>
          <Text style={styles.pageSubtitle}>Pindai limbah sampah menggunakan AI dan kirimkan lokasi penjemputan Anda.</Text>
        </View>

        <View style={styles.card}>
          
          {/* Header Kartu */}
          <View style={styles.cardHeader}>
            <View style={styles.iconCircle}>
              <FontAwesome5 name="camera-retro" size={18} color="#15803d" />
            </View>
            <View style={{ marginLeft: 12, flex: 1, flexShrink: 1 }}>
              <Text style={styles.cardTitle}>Formulir Pemindaian</Text>
              <Text style={styles.cardSubtitle} numberOfLines={2}>
                Pastikan foto sampah terlihat jelas agar terdeteksi akurat
              </Text>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={styles.cardBody}>
            {/* Sektor Scan / Foto */}
            <View style={styles.scanSection}>
              {capturedImage ? (
                <View style={styles.previewCard}>
                  <Image source={{ uri: capturedImage }} style={styles.previewImage} />
                  <View style={{ flex: 1, marginLeft: 12 }}>
                    <Text style={styles.successScanText}>✔ Sampah Telah Discan</Text>
                    <TouchableOpacity style={styles.rescanButton} onPress={openCameraScanner}>
                      <Ionicons name="camera" size={14} color="#166534" style={{ marginRight: 4 }} />
                      <Text style={styles.rescanText}>Scan Ulang</Text>
                    </TouchableOpacity>
                  </View>
                </View>
              ) : (
                <TouchableOpacity style={styles.scanTriggerButton} onPress={openCameraScanner}>
                  {scanning ? (
                    <ActivityIndicator color="#166534" size="large" />
                  ) : (
                    <>
                      <Ionicons name="scan-outline" size={36} color="#166534" />
                      <Text style={styles.scanTriggerText}>Buka Kamera & Scan Jenis Sampah</Text>
                      <Text style={styles.scanTriggerSubText}>AI akan mendeteksi kategori sampah otomatis</Text>
                    </>
                  )}
                </TouchableOpacity>
              )}
            </View>

            {/* Kategori Sampah */}
            <View style={styles.inputGroup}>
              <Text style={styles.label}>Kategori Sampah (Otomatis AI) *</Text>
              <TextInput 
                style={[styles.input, { backgroundColor: '#f3f4f6', fontWeight: 'bold', color: '#166534' }]} 
                placeholder="Hasil scan akan muncul di sini..." 
                value={category} 
                editable={false} 
              />
            </View>

            {/* Deskripsi */}
            <View style={styles.inputGroup}>
              <Text style={styles.label}>Deskripsi Tambahan *</Text>
              <TextInput 
                style={[styles.input, { height: 80, textAlignVertical: 'top' }]} 
                multiline 
                placeholder="Tambahkan deskripsi terkait sampah yang anda laporkan" 
                value={description}
                onChangeText={setDescription}
                placeholderTextColor="#9ca3af"
              />
            </View>

            {/* Alamat Penjemputan & Panel Debugging Koordinat */}
            <View style={[styles.inputGroup, { gap: 12 }]}>
              <Text style={styles.label}>Alamat Penjemputan Lengkap *</Text>              
              <TextInput 
                style={[styles.input, { height: 75, textAlignVertical: 'top', fontSize: 13, backgroundColor: '#f9fafb', marginBottom: 0 }]} 
                multiline 
                value={address}
                onChangeText={setAddress}
                placeholder="Alamat belum ditambahkan silahkan ketikkan alamat atau klik 'perbarui lokasi'"
                placeholderTextColor="#9ca3af"
              />

              {/* PANEL DEBUGGING KOORDINAT */}
              <View style={[styles.debugBox, { marginBottom: 0 }]}>
                <Text style={styles.debugTitle}>🔍 Melacak Lokasi Penjemputan Sampah:</Text>
                <Text style={styles.debugText}>Status: {debugStatus}</Text>
                <Text style={styles.debugText}>Latitude: {latitude || 'Belum ada data'}</Text>
                <Text style={styles.debugText}>Longitude: {longitude || 'Belum ada data'}</Text>
              </View>

              {/* Tombol Perbarui Lokasi */}
              <TouchableOpacity 
                style={[styles.updateLocationButton, { marginBottom: 0 }]} 
                onPress={handleUpdateLocation}
                disabled={loadingLocation}
              >
                {loadingLocation ? (
                  <ActivityIndicator color="#ffffff" size="small" />
                ) : (
                  <>
                    <Ionicons name="locate" size={16} color="#ffffff" style={{ marginRight: 6 }} />
                    <Text style={styles.updateLocationButtonText}>Perbarui Lokasi Saat Ini</Text>
                  </>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>

        {/* Tombol Laporkan */}
        <TouchableOpacity style={styles.submitButton} onPress={handleSubmit} disabled={submitting}>
          {submitting ? (
            <ActivityIndicator color="#ffffff" />
          ) : (
            <Text style={styles.submitButtonText}>Laporkan ke Sistem</Text>
          )}
        </TouchableOpacity>
      </ScrollView>

      {/* Bottom Navigation Bar */}
      <View style={styles.navBar}>
        <TouchableOpacity style={styles.navItemContainer} onPress={() => router.replace('/home')}>
          <View style={styles.navItem}>
            <Ionicons name="pie-chart" size={20} color="#9ca3af" />
            <Text style={styles.navText}>Dashboard</Text>
          </View>
        </TouchableOpacity>
        
        <TouchableOpacity style={styles.navItemContainer} onPress={() => router.replace('/report')}>
          <View style={styles.navItem}>
            <Ionicons name="list" size={20} color="#9ca3af" />
            <Text style={styles.navText}>Laporan</Text>
          </View>
        </TouchableOpacity>
        
        <TouchableOpacity style={styles.navItemContainer} onPress={() => router.replace('/settings')}>
          <View style={styles.navItem}>
            <Ionicons name="settings" size={20} color="#9ca3af" />
            <Text style={styles.navText}>Akun</Text>
          </View>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f4f6f8' }, 
  mainHeader: { backgroundColor: '#166534', padding: 16, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  headerTitleContainer: { flexDirection: 'row', alignItems: 'center' },
  mainHeaderTitle: { color: '#ffffff', fontSize: 20, fontWeight: 'bold' },
  content: { padding: 20, paddingBottom: 150 },
  
  pageHeader: { marginBottom: 20 },
  pageTitle: { fontSize: 26, fontWeight: 'bold', color: '#111827' },
  pageSubtitle: { fontSize: 14, color: '#6b7280', marginTop: 6, lineHeight: 20 },

  card: { backgroundColor: '#ffffff', borderRadius: 12, borderWidth: 1, borderColor: '#e5e7eb', overflow: 'hidden' },
  cardHeader: { flexDirection: 'row', padding: 16, alignItems: 'center', backgroundColor: '#ffffff' },
  iconCircle: { width: 42, height: 42, borderRadius: 21, backgroundColor: '#dcfce7', justifyContent: 'center', alignItems: 'center' },
  cardTitle: { fontSize: 16, fontWeight: 'bold', color: '#111827' },
  cardSubtitle: { fontSize: 12, color: '#6b7280', marginTop: 2, paddingRight: 10 },
  
  divider: { height: 1, backgroundColor: '#f3f4f6' },
  cardBody: { padding: 20 },

  scanSection: { marginBottom: 16 },
  scanTriggerButton: { backgroundColor: '#f0fdf4', borderWidth: 2, borderColor: '#bbf7d0', borderStyle: 'dashed', borderRadius: 12, padding: 24, alignItems: 'center', justifyContent: 'center' },
  scanTriggerText: { color: '#166534', fontWeight: 'bold', marginTop: 8, fontSize: 14, textAlign: 'center' },
  scanTriggerSubText: { color: '#6b7280', fontSize: 11, marginTop: 2, textAlign: 'center' },
  previewCard: { backgroundColor: '#f0fdf4', padding: 10, borderRadius: 8, flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderColor: '#bbf7d0' },
  previewImage: { width: 60, height: 60, borderRadius: 6 },
  successScanText: { color: '#166534', fontWeight: 'bold', fontSize: 12, marginBottom: 6 },
  rescanButton: { flexDirection: 'row', alignItems: 'center', alignSelf: 'flex-start', backgroundColor: '#ffffff', paddingVertical: 4, paddingHorizontal: 10, borderRadius: 4, borderWidth: 1, borderColor: '#166534' },
  rescanText: { color: '#166534', fontSize: 11, fontWeight: 'bold' },
  
  inputGroup: { marginBottom: 16 },
  label: { fontSize: 13, fontWeight: 'bold', color: '#374151', marginBottom: 6 },
  input: { borderWidth: 1, borderColor: '#d1d5db', borderRadius: 8, padding: 12, fontSize: 14, backgroundColor: '#fff', color: '#1f2937' },
  
  debugBox: { backgroundColor: '#fef3c7', borderWidth: 1, borderColor: '#f59e0b', borderRadius: 8, padding: 10, marginBottom: 10 },
  debugTitle: { fontSize: 11, fontWeight: 'bold', color: '#b45309', marginBottom: 4 },
  debugText: { fontSize: 11, color: '#92400e' },
  
  updateLocationButton: { backgroundColor: '#3b82f6', flexDirection: 'row', justifyContent: 'center', alignItems: 'center', padding: 12, borderRadius: 8 },
  updateLocationButtonText: { color: '#ffffff', fontWeight: 'bold', fontSize: 13 },
  
  submitButton: { backgroundColor: '#166534', padding: 16, borderRadius: 8, alignItems: 'center', marginTop: 10, shadowColor: '#000', shadowOpacity: 0.1, shadowRadius: 2 },
  submitButtonText: { color: '#ffffff', fontWeight: 'bold', fontSize: 15 },
  
  cameraOverlay: { flex: 1, backgroundColor: 'transparent', justifyContent: 'space-between', padding: 30 },
  closeCameraButton: { alignSelf: 'flex-end', backgroundColor: 'rgba(0,0,0,0.5)', padding: 10, borderRadius: 20 },
  scanBoxForm: { alignSelf: 'center', borderWidth: 2, borderColor: '#10b981', width: 250, height: 250, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
  scanBoxFrame: { alignSelf: 'center', borderWidth: 2, borderColor: '#10b981', width: 250, height: 250, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
  scanFrameText: { color: '#fff', backgroundColor: 'rgba(0,0,0,0.6)', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 4, fontSize: 11 },
  captureButton: { alignSelf: 'center', width: 70, height: 70, borderRadius: 35, borderWidth: 4, borderColor: '#fff', justifyContent: 'center', alignItems: 'center', marginBottom: 20 },
  captureButtonInner: { width: 54, height: 54, borderRadius: 27, backgroundColor: '#10b981' },
  
  navBar: { flexDirection: 'row', backgroundColor: '#14532d', position: 'absolute', bottom: 0, left: 0, right: 0, height: 60, alignItems: 'center', borderTopWidth: 1, borderTopColor: '#166534' },
  navItemContainer: { flex: 1, alignItems: 'center' },
  navItem: { alignItems: 'center' },
  navText: { color: '#9ca3af', fontSize: 11, marginTop: 2 }
});