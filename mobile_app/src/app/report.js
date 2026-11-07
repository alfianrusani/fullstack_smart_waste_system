import React, { useState, useEffect } from 'react';
import { 
  StyleSheet, 
  Text, 
  View, 
  ScrollView, 
  TextInput, 
  TouchableOpacity, 
  SafeAreaView, 
  ActivityIndicator,
  Image,
  Modal, 
} from 'react-native';
import { Ionicons, FontAwesome5 } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router'; 

// Fungsi untuk mendeteksi warna ikon berdasarkan jenis kategori sampah
const getCategoryColor = (category) => {
  if (!category) return { bg: '#f3f4f6', color: '#4b5563' }; // Default abu-abu
  
  const cat = category.toLowerCase();
  
  if (cat.includes('organik')) {
    return { bg: '#dcfce7', color: '#166534' }; // Hijau
  } else if (cat.includes('kertas')) {
    return { bg: '#e0f2fe', color: '#0284c7' }; // Biru muda
  } else if (cat.includes('plastik')) {
    return { bg: '#ffedd5', color: '#ea580c' }; // Oranye
  }
  
  return { bg: '#f3f4f6', color: '#4b5563' };
};

export default function ReportScreen() {
  const router = useRouter();
  
  const [reports, setReports] = useState([]);
  const [filteredReports, setFilteredReports] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('Semua'); 
  const [refreshing, setRefreshing] = useState(false);
  const [modalVisible, setModalVisible] = useState(false); 

  useEffect(() => {
    fetchReports();
  }, []);

  // Filter pencarian dan status secara real-time
  useEffect(() => {
    let result = reports;

    if (selectedStatus !== 'Semua') {
      result = result.filter(r => {
        const status = (r.status || '').toLowerCase();
        // Cocokkan pilihan dropdown (Indonesia) dengan data dari database (Inggris/Indonesia)
        if (selectedStatus === 'Menunggu') return status === 'pending' || status === 'menunggu';
        if (selectedStatus === 'Diproses') return status === 'in progress' || status === 'in_progress' || status === 'proses' || status === 'diproses';
        if (selectedStatus === 'Selesai') return status === 'completed' || status === 'selesai';
        if (selectedStatus === 'Ditolak') return status === 'rejected' || status === 'ditolak';
        return true;
      });
    }

    if (searchQuery.trim() !== '') {
      const q = searchQuery.toLowerCase();
      result = result.filter(r => 
        (r.category && r.category.toLowerCase().includes(q)) || 
        (r.address && r.address.toLowerCase().includes(q)) ||
        (r.description && r.description.toLowerCase().includes(q))
      );
    }

    setFilteredReports(result);
  }, [searchQuery, selectedStatus, reports]);

  const fetchReports = async () => {
    try {
      setRefreshing(true);
      const userToken = await AsyncStorage.getItem('userToken') || 
                        await AsyncStorage.getItem('token') || 
                        await AsyncStorage.getItem('access_token');

      if (!userToken) {
        router.replace('/login');
        return;
      }

      const response = await fetch('http://192.168.80.38:8000/api/reports', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${userToken}`,
        }
      });

      const result = await response.json();

      if (response.ok) {
        const data = result.data || result || [];
        setReports(data);
        setFilteredReports(data);
      }
    } catch (error) {
      console.log("Error fetching reports:", error);
    } finally {
      setRefreshing(false);
    }
  };

  const getStatusBadge = (status) => {
    const s = (status || '').toLowerCase();
    if (s === 'pending' || s === 'menunggu') return { bg: '#fef9c3', text: '#ca8a04', label: 'Menunggu' };
    if (s === 'in progress' || s === 'diproses') return { bg: '#dbeafe', text: '#2563eb', label: 'Diproses' };   
    if (s === 'rejected' || s === 'ditolak') return { bg: '#fee2e2', text: '#dc2626', label: 'Ditolak' };
    if (s === 'completed' || s === 'selesai') return { bg: '#d1fae5', text: '#16a34a', label: 'Selesai' };
    return { bg: '#f3f4f6', text: '#4b5563', label: status || 'Unknown' };
  };

  // Menentukan warna background tombol filter berdasarkan status yang dipilih
  const getFilterButtonColor = () => {
    switch (selectedStatus) {
      case 'Menunggu': return '#eab308'; // Kuning
      case 'Diproses': return '#3b82f6'; // Biru
      case 'Selesai': return '#22c55e'; // Hijau
      case 'Ditolak': return '#ef4444'; // Merah
      default: return '#4b5563'; // Abu-abu (Semua)
    }
  };

  const statusOptions = ['Semua', 'Menunggu', 'Diproses', 'Selesai', 'Ditolak'];
  
  return (
    <SafeAreaView style={styles.container}>
      {/* Header Utama */}
      <View style={styles.mainHeader}>
        <View style={styles.headerTitleContainer}>
          <Ionicons name="leaf" size={24} color="#ffffff" style={{ marginRight: 8 }} />
          <Text style={styles.mainHeaderTitle}>Smart Waste</Text>
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        
        <View style={styles.pageHeader}>
          <Text style={styles.pageTitle}>Laporan Saya</Text>
          <Text style={styles.pageSubtitle}>Daftar seluruh laporan limbah sampah yang telah Anda kirimkan beserta status penanganannya.</Text>
        </View>

        {/* =========================================
           SEARCH BOX & FILTER DROPDOWN DI UJUNGNYA
           ========================================= */}
        <View style={styles.searchFilterRow}>
          {/* Kotak Pencarian */}
          <View style={styles.searchBox}>
            <Ionicons name="search" size={18} color="#9ca3af" style={{ marginRight: 8 }} />
            <TextInput 
              style={styles.searchInput}
              placeholder="Cari kategori atau lokasi..."
              placeholderTextColor="#9ca3af"
              value={searchQuery}
              onChangeText={setSearchQuery}
            />
            {searchQuery.length > 0 && (
              <TouchableOpacity onPress={() => setSearchQuery('')}>
                <Ionicons name="close-circle" size={18} color="#9ca3af" />
              </TouchableOpacity>
            )}
          </View>

          {/* Tombol Filter Berwarna di Ujung Textbox */}
          <TouchableOpacity 
            style={[styles.filterButton, { backgroundColor: getFilterButtonColor() }]}
            onPress={() => setModalVisible(true)}
          >
            <Ionicons name="options" size={16} color="#ffffff" style={{ marginRight: 4 }} />
            <Text style={styles.filterButtonText} numberOfLines={1}>{selectedStatus}</Text>
            <Ionicons name="chevron-down" size={14} color="#ffffff" style={{ marginLeft: 4 }} />
          </TouchableOpacity>
        </View>

        {/* =========================================
           MODAL PILIHAN FILTER STATUS
           ========================================= */}
        <Modal
          animationType="fade"
          transparent={true}
          visible={modalVisible}
          onRequestClose={() => setModalVisible(false)}
        >
          <TouchableOpacity style={styles.modalOverlay} activeOpacity={1} onPress={() => setModalVisible(false)}>
            <View style={styles.modalContent}>
              <View style={styles.modalHeader}>
                <Text style={styles.modalTitle}>Filter Berdasarkan Status</Text>
                <TouchableOpacity onPress={() => setModalVisible(false)}>
                  <Ionicons name="close" size={20} color="#111827" />
                </TouchableOpacity>
              </View>
              
              {statusOptions.map((status) => (
                <TouchableOpacity 
                  key={status} 
                  style={[styles.modalOption, selectedStatus === status && styles.modalOptionActive]}
                  onPress={() => {
                    setSelectedStatus(status);
                    setModalVisible(false);
                  }}
                >
                  <Text style={[styles.modalOptionText, selectedStatus === status && styles.modalOptionTextActive]}>
                    {status}
                  </Text>
                  {selectedStatus === status && <Ionicons name="checkmark" size={18} color="#166534" />}
                </TouchableOpacity>
              ))}
            </View>
          </TouchableOpacity>
        </Modal>

        {/* =========================================
           DAFTAR LAPORAN / KONDISI KOSONG
           ========================================= */}
        {refreshing && reports.length === 0 ? (
          <View style={styles.emptyContainer}>
            <ActivityIndicator size="large" color="#166534" />
          </View>
        ) : filteredReports.length === 0 ? (
          <View style={styles.emptyCard}>
            <View style={styles.folderIconContainer}>
              <Ionicons name="folder" size={24} color="#15803d" />
            </View>
            <Text style={styles.emptyTitle}>Tidak Ada Laporan</Text>
            <Text style={styles.emptySubtitle}>
              {searchQuery || selectedStatus !== 'Semua' 
                ? 'Tidak ada laporan yang cocok dengan filter pencarian Anda.' 
                : 'Belum ada laporan sampah yang Anda buat.'}
            </Text>
          </View>
        ) : (
          <View style={styles.reportList}>
            {filteredReports.map((item, index) => {
              const badge = getStatusBadge(item.status);
              const catColor = getCategoryColor(item.category);

              return (
                <View key={item.id || index} style={styles.reportCard}>
                  <View style={styles.reportCardHeader}>
                    <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1 }}>
                      <View style={[styles.reportCategoryIcon, { backgroundColor: catColor.bg }]}>
                        <FontAwesome5 name="recycle" size={12} color={catColor.color} />
                      </View>
                      <Text style={[styles.reportCategoryText, { color: catColor.color }]} numberOfLines={1}>
                        {item.category || 'Limbah Umum'}
                      </Text>
                    </View>
                    <View style={[styles.statusBadge, { backgroundColor: badge.bg }]}>
                      <Text style={[styles.statusBadgeText, { color: badge.text }]}>{badge.label}</Text>
                    </View>
                  </View>

                  <View style={styles.divider} />

                  <View style={styles.reportCardBody}>
                    {/* Foto Laporan dari Cloudinary */}
                    {item.image_url ? (
                      <Image source={{ uri: item.image_url }} style={styles.reportImage} resizeMode="cover" />
                    ) : (
                      <View style={styles.reportImagePlaceholder}>
                        <Ionicons name="image" size={24} color="#9ca3af" />
                      </View>
                    )}

                    <View style={{ flex: 1, marginLeft: 12 }}>
                      <Text style={styles.reportDesc} numberOfLines={2}>{item.description || 'Tanpa keterangan deskripsi.'}</Text>
                      
                      {/* Baris Lokasi (Alamat Lengkap Google Maps Style) */}
                      <View style={styles.reportInfoRow}>
                        <Ionicons name="location-sharp" size={14} color="#ef4444" style={{ marginRight: 4 }} />
                        <Text style={styles.reportInfoText} numberOfLines={1}>
                          {item.address && item.address.trim() !== '' 
                            ? item.address 
                            : `Lat: ${item.latitude}, Lng: ${item.longitude}`}
                        </Text>
                      </View>

                      {/* Baris Tanggal/Waktu */}
                      <View style={styles.reportInfoRow}>
                        <Ionicons name="time-outline" size={14} color="#3b82f6" style={{ marginRight: 4 }} />
                        <Text style={styles.reportInfoText}>
                          {item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric'
                          }) : 'Baru saja'}
                        </Text>
                      </View>
                    </View>
                  </View>
                </View>
              );
            })}
          </View>
        )}

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
          <View style={styles.navItemActive}>
            <Ionicons name="list" size={20} color="#ffffff" />
            <Text style={styles.navTextActive}>Laporan</Text>
          </View>
        </TouchableOpacity>
        
        <TouchableOpacity style={styles.navItemContainer} onPress={() => router.replace('/settings')}>
          <View style={styles.navItem}>
            <Ionicons name="person-circle-outline" size={20} color="#9ca3af" />
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
  content: { padding: 20, paddingBottom: 100 },
  
  pageHeader: { marginBottom: 20 },
  pageTitle: { fontSize: 26, fontWeight: 'bold', color: '#111827' },
  pageSubtitle: { fontSize: 14, color: '#6b7280', marginTop: 6, lineHeight: 20 },

  /* Search & Filter Row */
  searchFilterRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 20, gap: 8 },
  searchBox: { flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: '#ffffff', borderWidth: 1, borderColor: '#d1d5db', borderRadius: 8, paddingHorizontal: 12, height: 46, elevation: 1 },
  searchInput: { flex: 1, fontSize: 13, color: '#1f2937' },
  
  filterButton: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', height: 46, paddingHorizontal: 12, borderRadius: 8, elevation: 1, maxWidth: 120 },
  filterButtonText: { color: '#ffffff', fontSize: 12, fontWeight: 'bold' },

  /* Modal Dropdown Style */
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.4)', justifyContent: 'center', alignItems: 'center', padding: 20 },
  modalContent: { backgroundColor: '#ffffff', width: '100%', maxWidth: 320, borderRadius: 12, padding: 16, elevation: 5 },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12, borderBottomWidth: 1, borderBottomColor: '#f3f4f6', paddingBottom: 8 },
  modalTitle: { fontSize: 15, fontWeight: 'bold', color: '#111827' },
  modalOption: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 12, paddingHorizontal: 10, borderRadius: 6 },
  modalOptionActive: { backgroundColor: '#f0fdf4' },
  modalOptionText: { fontSize: 14, color: '#374151', fontWeight: '500' },
  modalOptionTextActive: { color: '#166534', fontWeight: 'bold' },

  /* Kondisi Kosong */
  emptyCard: { backgroundColor: '#ffffff', borderRadius: 12, padding: 30, alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#e5e7eb' },
  folderIconContainer: { width: 60, height: 60, borderRadius: 30, backgroundColor: '#dcfce7', alignItems: 'center', justifyContent: 'center', marginBottom: 16 },
  emptyTitle: { fontSize: 16, fontWeight: 'bold', color: '#111827', marginBottom: 8, textAlign: 'center' },
  emptySubtitle: { fontSize: 13, color: '#6b7280', textAlign: 'center', lineHeight: 20 },
  emptyContainer: { padding: 40, alignItems: 'center' },

  /* Report Card List Style */
  reportList: { gap: 14 },
  reportCard: { backgroundColor: '#ffffff', borderRadius: 12, borderWidth: 1, borderColor: '#e5e7eb', padding: 16, elevation: 1 },
  reportCardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  reportCategoryIcon: { width: 26, height: 26, borderRadius: 13, justifyContent: 'center', alignItems: 'center', marginRight: 8 },
  reportCategoryText: { fontSize: 14, fontWeight: 'bold', flex: 1 },
  statusBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 6 },
  statusBadgeText: { fontSize: 11, fontWeight: 'bold' },
  
  divider: { height: 1, backgroundColor: '#f3f4f6', marginBottom: 12 },

  reportCardBody: { flexDirection: 'row', alignItems: 'center' },
  reportImage: { width: 75, height: 75, borderRadius: 8, backgroundColor: '#e5e7eb' },
  reportImagePlaceholder: { width: 75, height: 75, borderRadius: 8, backgroundColor: '#f3f4f6', justifyContent: 'center', alignItems: 'center' },
  reportDesc: { fontSize: 13, color: '#374151', fontWeight: '500', marginBottom: 6, lineHeight: 18 },
  reportInfoRow: { flexDirection: 'row', alignItems: 'center', marginTop: 3 },
  reportInfoText: { fontSize: 11, color: '#6b7280', flex: 1 },

  /* Navbar */
  navBar: { flexDirection: 'row', backgroundColor: '#14532d', position: 'absolute', bottom: 0, left: 0, right: 0, height: 60, alignItems: 'center', borderTopWidth: 1, borderTopColor: '#166534' },
  navItemContainer: { flex: 1, alignItems: 'center' },
  navItem: { alignItems: 'center' },
  navItemActive: { alignItems: 'center', backgroundColor: '#166534', paddingVertical: 6, paddingHorizontal: 16, borderRadius: 8 },
  navText: { color: '#9ca3af', fontSize: 11, marginTop: 2 },
  navTextActive: { color: '#ffffff', fontSize: 11, fontWeight: 'bold', marginTop: 2 }
});