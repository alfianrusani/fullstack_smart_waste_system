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
  ActivityIndicator 
} from 'react-native';
import { Ionicons, FontAwesome5 } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router';
import * as Location from 'expo-location';

export default function SettingsScreen() {
  const router = useRouter();
  
  // State khusus untuk bagian card informasi profil
  const [cardLoading, setCardLoading] = useState(true);
  const [isEditing, setIsEditing] = useState(false);
  const [updating, setUpdating] = useState(false);
  const [loadingLocation, setLoadingLocation] = useState(false);

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [latitude, setLatitude] = useState('');
  const [longitude, setLongitude] = useState('');

  useEffect(() => {
    fetchUserData();
  }, []);

  const fetchUserData = async () => {
    try {
      setCardLoading(true); // Hanya card saja yang memuat status loading, halaman lain tetap instan
      const userToken = await AsyncStorage.getItem('userToken') || 
                        await AsyncStorage.getItem('token') || 
                        await AsyncStorage.getItem('access_token');

      if (!userToken) {
        Alert.alert('Sesi Berakhir', 'Silakan login ulang.');
        router.replace('/login');
        return;
      }

      const response = await fetch('http://192.168.80.38:8000/api/user', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${userToken}`, 
        },
      });

      const result = await response.json();
      
      if (response.ok) {
        setName(result.name || 'Warga Pengguna');
        setEmail(result.email || 'user@gmail.com');
        setPhone(result.phone || '089876543210');
        setAddress(result.address || 'Jl. Perintis Kemerdekaan No. 10, Makassar');
        setLatitude(result.latitude || '-5.13058700');
        setLongitude(result.longitude || '119.47902200');
      }
    } catch (error) {
      console.log("Error koneksi profil:", error);
    } finally {
      setCardLoading(false); // Selesai memuat data card
    }
  };

  const handleUpdateLocation = async () => {
    setLoadingLocation(true);
    try {
      let { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Izin Ditolak', 'Akses lokasi dibutuhkan untuk mencari alamat otomatis.');
        setLoadingLocation(false);
        return;
      }

      let location = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.Balanced });
      const lat = location.coords.latitude;
      const lng = location.coords.longitude;
      
      setLatitude(lat.toString());
      setLongitude(lng.toString());

      const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
        headers: { 'User-Agent': 'SmartWasteApp/1.0' }
      });
      const data = await response.json();
      
      if (data && data.display_name) {
        setAddress(data.display_name);
      }
    } catch (error) {
      Alert.alert('Error', 'Gagal melacak lokasi.');
    } finally {
      setLoadingLocation(false);
    }
  };

  const handleUpdateProfile = async () => {
    setUpdating(true);
    try {
      const userToken = await AsyncStorage.getItem('userToken') || 
                        await AsyncStorage.getItem('token') || 
                        await AsyncStorage.getItem('access_token');

      // Pastikan URL endpoint API ini sesuai dengan yang ada di routes/api.php Anda
      const response = await fetch('http://192.168.80.38:8000/api/profile/update', {
        method: 'PUT', // Gunakan POST jika backend Anda menolaknya, lalu tambahkan _method: 'PUT' di body
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${userToken}`,
        },
        // Mengirimkan semua state yang sudah diubah di form ke backend
        body: JSON.stringify({
          name: name,
          email: email, 
          phone: phone,
          address: address,
          latitude: latitude,
          longitude: longitude
        })
      });

      const result = await response.json();

      if (response.ok) {
        Alert.alert('Sukses', 'Profil berhasil diperbarui!');
        setIsEditing(false);
      } else {
        Alert.alert('Gagal Memperbarui', result.message || 'Silakan periksa kembali data Anda.');
      }
    } catch (error) {
      console.log("Error update profil:", error);
      Alert.alert('Error', 'Gagal terhubung ke server.');
    } finally {
      setUpdating(false);
    }
  };

  const handleLogout = async () => {
    Alert.alert('Konfirmasi Keluar', 'Yakin ingin keluar?', [
      { text: 'Batal', style: 'cancel' },
      { 
        text: 'Keluar', style: 'destructive',
        onPress: async () => {
          await AsyncStorage.multiRemove(['userToken', 'token', 'access_token']);
          router.replace('/login');
        } 
      }
    ]);
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Header Utama Aplikasi */}
      <View style={styles.mainHeader}>
        <View style={styles.headerTitleContainer}>
          <Ionicons name="leaf" size={24} color="#ffffff" style={{ marginRight: 8 }} />
          <Text style={styles.mainHeaderTitle}>Smart Waste</Text>
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        
        {/* Judul Halaman (Muncul Instan Tanpa Loading) */}
        <View style={styles.pageHeader}>
          <Text style={styles.pageTitle}>{isEditing ? 'Edit Akun' : 'Pengaturan Akun'}</Text>
          <Text style={styles.pageSubtitle}>
            {isEditing ? 'Perbarui data identitas dan titik penjemputan Anda.' : 'Kelola data pribadi dan alamat utama penjemputan sampah Anda.'}
          </Text>
        </View>

        {/* Kartu Profil Utama */}
        <View style={styles.card}>
          
          <View style={styles.cardHeader}>
            <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1 }}>
              <View style={styles.iconCircle}>
                <FontAwesome5 name="id-card" size={20} color="#15803d" />
              </View>
              <View style={{ marginLeft: 12 }}>
                <Text style={styles.cardTitle}>Informasi Pribadi</Text>
                <Text style={styles.cardSubtitle}>Detail identitas Anda yang terdaftar pada sistem</Text>
              </View>
            </View>
          </View>

          <View style={styles.divider} />

          {/* HANYA BAGIAN INI YANG MENAMPILKAN LOADING KECIL JIKA MASIH MENGAMBIL DATA */}
          {cardLoading ? (
            <View style={styles.cardLoadingContainer}>
              <ActivityIndicator size="small" color="#166534" />
              <Text style={styles.cardLoadingText}>Memuat informasi profil...</Text>
            </View>
          ) : !isEditing ? (
            /* ======================= MODE LIHAT PROFIL ======================= */
            <View style={styles.cardBody}>
              <View style={styles.infoRow}>
                <Text style={styles.label}>Nama Lengkap</Text>
                <Text style={styles.value}>{name}</Text>
              </View>

              <View style={styles.infoRow}>
                <Text style={styles.label}>Alamat Email</Text>
                <Text style={styles.value}>{email}</Text>
              </View>

              <View style={styles.infoRow}>
                <Text style={styles.label}>Nomor Telepon / WhatsApp</Text>
                <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: 2 }}>
                  <FontAwesome5 name="whatsapp" size={16} color="#16a34a" style={{ marginRight: 6 }} />
                  <Text style={styles.value}>{phone}</Text>
                </View>
              </View>

              <View style={styles.infoRow}>
                <Text style={styles.label}>Titik GPS Terdaftar</Text>
                <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: 2 }}>
                  <Ionicons name="location" size={18} color="#dc2626" style={{ marginRight: 4 }} />
                  <Text style={[styles.value, { color: '#2563eb' }]}>{latitude}, {longitude}</Text>
                </View>
              </View>

              <View style={styles.infoRow}>
                <Text style={styles.label}>Alamat Penjemputan</Text>
                <View style={styles.addressBox}>
                  <Text style={styles.addressText}>{address}</Text>
                </View>
              </View>

              {/* ===== TOMBOL EDIT DAN KELUAR DI BAWAH FORM ===== */}
              <View style={styles.actionColumn}>
                <TouchableOpacity style={styles.btnEditBottom} onPress={() => setIsEditing(true)}>
                  <FontAwesome5 name="edit" size={16} color="#ffffff" style={{ marginRight: 8 }} />
                  <Text style={styles.btnEditBottomText}>Edit Akun</Text>
                </TouchableOpacity>

                <TouchableOpacity style={styles.btnLogoutBottom} onPress={handleLogout}>
                  <Ionicons name="log-out-outline" size={18} color="#dc2626" style={{ marginRight: 8 }} />
                  <Text style={styles.btnLogoutBottomText}>Keluar dari Akun</Text>
                </TouchableOpacity>
              </View>
            </View>
          ) : (
            /* ======================= MODE EDIT PROFIL ======================= */
            <View style={styles.cardBody}>
              <View style={styles.inputGroup}>
                <Text style={styles.label}>Nama Lengkap</Text>
                <TextInput style={styles.input} value={name} onChangeText={setName} />
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.label}>Alamat Email</Text>
                <TextInput style={styles.input} value={email} onChangeText={setEmail} keyboardType="email-address" />
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.label}>Nomor Telepon / WhatsApp</Text>
                <TextInput style={styles.input} value={phone} onChangeText={setPhone} keyboardType="phone-pad" />
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.label}>Titik GPS Terdaftar</Text>
                <TouchableOpacity style={styles.btnLocation} onPress={handleUpdateLocation} disabled={loadingLocation}>
                  {loadingLocation ? <ActivityIndicator color="#166534" size="small" /> : (
                    <>
                      <Ionicons name="locate" size={18} color="#15803d" style={{ marginRight: 6 }} />
                      <Text style={styles.btnLocationText}>Perbarui Koordinat GPS</Text>
                    </>
                  )}
                </TouchableOpacity>
                <Text style={styles.coordsHelper}>{latitude}, {longitude}</Text>
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.label}>Alamat Penjemputan</Text>
                <TextInput style={[styles.input, { height: 80, textAlignVertical: 'top' }]} multiline value={address} onChangeText={setAddress} />
              </View>

              <View style={styles.actionRow}>
                <TouchableOpacity style={styles.btnCancel} onPress={() => setIsEditing(false)}>
                  <Text style={styles.btnCancelText}>Batal</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.btnSave} onPress={handleUpdateProfile}>
                  {updating ? <ActivityIndicator color="#fff" /> : <Text style={styles.btnSaveText}>Simpan Data</Text>}
                </TouchableOpacity>
              </View>
            </View>
          )}
        </View>
      </ScrollView>

      {/* Bottom Navigation */}
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
          <View style={styles.navItemActive}>
            <Ionicons name="person-circle-outline" size={20} color="#ffffff" />
            <Text style={styles.navTextActive}>Akun</Text>
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
  content: { padding: 20, paddingBottom: 100 },
  
  pageHeader: { marginBottom: 20 },
  pageTitle: { fontSize: 26, fontWeight: 'bold', color: '#111827' },
  pageSubtitle: { fontSize: 14, color: '#6b7280', marginTop: 6, lineHeight: 20 },
  
  card: { backgroundColor: '#ffffff', borderRadius: 12, borderWidth: 1, borderColor: '#e5e7eb', overflow: 'hidden' },
  cardHeader: { flexDirection: 'row', padding: 16, alignItems: 'center', backgroundColor: '#ffffff' },
  iconCircle: { width: 46, height: 46, borderRadius: 23, backgroundColor: '#dcfce7', justifyContent: 'center', alignItems: 'center' },
  cardTitle: { fontSize: 16, fontWeight: 'bold', color: '#111827' },
  cardSubtitle: { fontSize: 12, color: '#6b7280', marginTop: 2, paddingRight: 10 },
  
  divider: { height: 1, backgroundColor: '#f3f4f6' },
  
  cardBody: { padding: 20 },
  cardLoadingContainer: { paddingVertical: 40, alignItems: 'center', justifyContent: 'center', flexDirection: 'row' },
  cardLoadingText: { marginLeft: 10, fontSize: 13, color: '#6b7280', fontWeight: '500' },

  infoRow: { marginBottom: 20 },
  label: { fontSize: 13, color: '#6b7280', fontWeight: '500', marginBottom: 6 },
  value: { fontSize: 15, color: '#111827', fontWeight: 'bold' },
  addressBox: { backgroundColor: '#f9fafb', padding: 14, borderRadius: 8, borderWidth: 1, borderColor: '#f3f4f6', marginTop: 4 },
  addressText: { fontSize: 14, color: '#111827', fontWeight: '500' },
  
  actionColumn: { marginTop: 12 },
  btnEditBottom: { backgroundColor: '#22c55e', flexDirection: 'row', justifyContent: 'center', alignItems: 'center', padding: 14, borderRadius: 8, marginBottom: 12 },
  btnEditBottomText: { color: '#ffffff', fontWeight: 'bold', fontSize: 15 },
  btnLogoutBottom: { backgroundColor: '#fef2f2', flexDirection: 'row', justifyContent: 'center', alignItems: 'center', padding: 14, borderRadius: 8, borderWidth: 1, borderColor: '#fecaca' },
  btnLogoutBottomText: { color: '#dc2626', fontWeight: 'bold', fontSize: 15 },

  inputGroup: { marginBottom: 16 },
  input: { borderWidth: 1, borderColor: '#d1d5db', borderRadius: 8, padding: 12, fontSize: 14, backgroundColor: '#ffffff', color: '#1f2937' },
  btnLocation: { backgroundColor: '#f0fdf4', flexDirection: 'row', alignItems: 'center', padding: 12, borderRadius: 8, borderWidth: 1, borderColor: '#bbf7d0' },
  btnLocationText: { color: '#15803d', fontWeight: 'bold', fontSize: 13 },
  coordsHelper: { fontSize: 11, color: '#9ca3af', marginTop: 6, fontStyle: 'italic' },
  
  actionRow: { flexDirection: 'row', marginTop: 10, gap: 10 },
  btnCancel: { flex: 1, backgroundColor: '#f3f4f6', padding: 14, borderRadius: 8, alignItems: 'center' },
  btnCancelText: { color: '#4b5563', fontWeight: 'bold' },
  btnSave: { flex: 1, backgroundColor: '#22c55e', padding: 14, borderRadius: 8, alignItems: 'center' },
  btnSaveText: { color: '#ffffff', fontWeight: 'bold' },
  
  navBar: { flexDirection: 'row', backgroundColor: '#14532d', position: 'absolute', bottom: 0, left: 0, right: 0, height: 60, alignItems: 'center', borderTopWidth: 1, borderTopColor: '#166534' },
  navItemContainer: { flex: 1, alignItems: 'center' },
  navItem: { alignItems: 'center' },
  navItemActive: { alignItems: 'center', backgroundColor: '#166534', paddingVertical: 6, paddingHorizontal: 16, borderRadius: 8 },
  navText: { color: '#9ca3af', fontSize: 11, marginTop: 2 },
  navTextActive: { color: '#ffffff', fontSize: 11, fontWeight: 'bold', marginTop: 2 }
});