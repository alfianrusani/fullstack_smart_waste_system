import React, { useState, useEffect, useTransition } from 'react';
import { StyleSheet, Text, View, ScrollView, TouchableOpacity, SafeAreaView, Dimensions, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router'; 
import AsyncStorage from '@react-native-async-storage/async-storage';
import { PieChart } from 'react-native-chart-kit';

const screenWidth = Dimensions.get('window').width;

export default function HomeScreen() {
  const router = useRouter(); 
  
  // 1. Tambahkan state isLoading untuk partial loading
  const [isLoading, setIsLoading] = useState(true);
  const [stats, setStats] = useState({ total: 0, pending: 0, inProgress: 0, completed: 0, rejected: 0 });
  const [chartData, setChartData] = useState([]);
  const [isPending, startTransition] = useTransition();

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      setIsLoading(true); // Nyalakan loading saat mulai fetch
      
      const userToken = await AsyncStorage.getItem('userToken') || 
                        await AsyncStorage.getItem('token') || 
                        await AsyncStorage.getItem('access_token');

      if (!userToken) return;

      const response = await fetch('http://192.168.80.38:8000/api/reports', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${userToken}`,
        }
      });

      const result = await response.json();

      if (response.ok) {
        const reports = result.data || result || [];
        
        const total = reports.length;
        
        const pending = reports.filter(r => {
            const s = (r.status || '').toLowerCase();
            return s === 'pending' || s === 'menunggu';
        }).length;

        const inProgress = reports.filter(r => {
            const s = (r.status || '').toLowerCase();
            return s === 'in progress' || s === 'in_progress' || s === 'proses';
        }).length;

        const completed = reports.filter(r => {
            const s = (r.status || '').toLowerCase();
            return s === 'completed' || s === 'selesai';
        }).length;

        const rejected = reports.filter(r => {
            const s = (r.status || '').toLowerCase();
            return s === 'rejected' || s === 'ditolak';
        }).length;

        const categoryCounts = {};
        reports.forEach(report => {
          const cat = report.category || 'Lainnya';
          categoryCounts[cat] = (categoryCounts[cat] || 0) + 1;
        });

        // 1. FUNGSI BARU: Pemetaan warna otomatis mengikuti website
        const getCategoryChartColor = (categoryName) => {
          const cat = (categoryName || '').toLowerCase();
          if (cat.includes('organik') || cat.includes('organic')) return '#22c55e'; // Hijau
          if (cat.includes('kertas') || cat.includes('paper')) return '#38bdf8';    // Biru Muda
          if (cat.includes('plastik') || cat.includes('plastic')) return '#f97316'; // Oranye
          return '#9ca3af'; // Default Abu-abu
        };
        
        // 2. PERBAIKAN: Gunakan fungsi warna di atas pada chartData
        const formattedChartData = Object.keys(categoryCounts).map((key) => ({
          name: key,
          population: categoryCounts[key],
          color: getCategoryChartColor(key),
          legendFontColor: '#4b5563',
          legendFontSize: 13
        }));

        startTransition(() => {
          setStats({ total, pending, inProgress, completed, rejected });
          setChartData(formattedChartData);
        });
      }
    } catch (error) {
      console.log("Error fetch dashboard:", error);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <View style={styles.headerTitleContainer}>
          <Ionicons name="leaf" size={24} color="#ffffff" style={{ marginRight: 8 }} />
          <Text style={styles.headerTitle}>Smart Waste</Text>
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        
        <View style={styles.pageTitleContainer}>
          <Text style={styles.pageTitle}>Dashboard Saya</Text>
          <Text style={styles.pageSubtitle}>Ringkasan status laporan sampah dan analitik hasil pemindaian limbah Anda.</Text>
        </View>

        {/* 3. TAMPILAN KONDISIONAL: Jika loading, tampilkan spinner. Jika selesai, tampilkan card */}
        {isLoading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#166534" />
            <Text style={styles.loadingText}>Memuat data dashboard...</Text>
          </View>
        ) : (
          <>
            <View style={styles.statsContainer}>
              <View style={[styles.statCard, styles.cardFull, styles.cardLeftBorder]}>
                <Text style={styles.statLabel}>Total Laporan Saya</Text>
                <Text style={styles.statValueDark}>{stats.total}</Text>
              </View>

              <View style={styles.flexRow}>
                <View style={[styles.statCard, styles.cardHalf, styles.cardLeftBorder, { borderLeftColor: '#eab308' }]}>
                  <Text style={styles.statLabel}>Pending</Text>
                  <Text style={styles.statValueYellow}>{stats.pending}</Text>
                </View>
                <View style={[styles.statCard, styles.cardHalf, styles.cardLeftBorder, { borderLeftColor: '#3b82f6' }]}>
                  <Text style={styles.statLabel}>In Progress</Text>
                  <Text style={styles.statValueBlue}>{stats.inProgress}</Text>
                </View>
              </View>

              <View style={styles.flexRow}>
                <View style={[styles.statCard, styles.cardHalf, styles.cardSolidGreen]}>
                  <Text style={styles.statLabelWhite}>Completed</Text>
                  <Text style={styles.statValueWhite}>{stats.completed}</Text>
                </View>
                <View style={[styles.statCard, styles.cardHalf, styles.cardSolidRed]}>
                  <Text style={styles.statLabelWhite}>Rejected</Text>
                  <Text style={styles.statValueWhite}>{stats.rejected}</Text>
                </View>
              </View>
            </View>

            {stats.total === 0 || chartData.length === 0 ? (
                <View style={styles.emptyChartCard}>
                  <View style={styles.folderIconContainer}>
                    <Ionicons name="folder" size={24} color="#15803d" />
                  </View>
                  <Text style={styles.emptyChartTitle}>Belum Ada Data Grafik</Text>
                  <Text style={styles.emptyChartSubtitle}>
                    Grafik statistik penanganan dan distribusi kategori sampah akan otomatis muncul di sini setelah Anda melakukan pemindaian dan mengirimkan laporan pertama.
                  </Text>
                </View>
              ) : (
                <View style={styles.chartCard}>
                  {/* 1. Header Card (Ikon + Teks Rata Tengah) */}
                  <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', marginBottom: 16, width: '100%' }}>
                    <Ionicons name="pie-chart" size={22} color="#111827" style={{ marginRight: 8 }} />
                    <Text style={{ fontSize: 16, fontWeight: 'bold', color: '#111827' }}>Distribusi Kategori Sampah</Text>
                  </View>
                  
                  <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
                    
                    {/* Bagian Kiri: Chart Donat */}
                    <View style={{ position: 'relative', width: 170, height: 170, justifyContent: 'center', alignItems: 'center' }}>
                      <PieChart
                        data={chartData}
                        width={170}
                        height={170}
                        chartConfig={{
                          color: (opacity = 1) => `rgba(0, 0, 0, ${opacity})`,
                        }}
                        accessor={"population"}
                        backgroundColor={"transparent"}
                        paddingLeft={"0"} 
                        center={[42.5, 0]}
                        hasLegend={false}
                        absolute
                      />
                      <View style={{
                        position: 'absolute',
                        width: 85,
                        height: 85,
                        backgroundColor: '#ffffff',
                        borderRadius: 42.5, 
                      }} />
                    </View>

                    {/* Bagian Kanan: Legend Custom */}
                    <View style={{ flex: 1, paddingLeft: 10, justifyContent: 'center' }}>
                      {chartData.map((item, index) => (
                        <View key={index} style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 14 }}>
                          <View style={{ width: 14, height: 14, borderRadius: 7, backgroundColor: item.color, marginRight: 10 }} />
                          <Text style={{ fontSize: 13, color: '#4b5563', flexShrink: 1 }}>
                            <Text style={{ fontWeight: 'bold' }}>{item.population}</Text> {item.name}
                          </Text>
                        </View>
                      ))}
                    </View>

                  </View>

                  {/* 2. Pesan Hijau Terima Kasih (Di Bawah Chart) */}
                  <View style={{ marginTop: 20, backgroundColor: '#dcfce7', padding: 16, borderRadius: 8, flexDirection: 'row', alignItems: 'flex-start', width: '100%' }}>
                    <Ionicons name="leaf" size={18} color="#15803d" style={{ marginRight: 10, marginTop: 2 }} />
                    <Text style={{ flex: 1, fontSize: 12, color: '#166534', lineHeight: 18 }}>
                      <Text style={{ fontWeight: 'bold' }}>Terima Kasih! </Text>
                      Kontribusi Anda dalam memilah dan melaporkan sampah sangat membantu menjaga kebersihan lingkungan bersama.
                    </Text>
                  </View>

                </View>
              )}
          </>
        )}

        <TouchableOpacity style={styles.actionButton} onPress={() => router.push('/create')}>
          <Ionicons name="add-circle" size={20} color="#ffffff" style={{ marginRight: 8 }} />
          <Text style={styles.actionButtonText}>Buat Laporan Baru</Text>
        </TouchableOpacity>

      </ScrollView>

      <View style={styles.navBar}>
        <TouchableOpacity style={styles.navItemContainer} onPress={() => router.replace('/home')}>
          <View style={styles.navItemActive}>
            <Ionicons name="pie-chart" size={20} color="#ffffff" />
            <Text style={styles.navTextActive}>Dashboard</Text>
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
  header: { backgroundColor: '#166534', padding: 16, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  headerTitleContainer: { flexDirection: 'row', alignItems: 'center' },
  headerTitle: { color: '#ffffff', fontSize: 20, fontWeight: 'bold' },
  content: { padding: 20, paddingBottom: 100 },
  
  pageTitleContainer: { marginBottom: 20 },
  pageTitle: { fontSize: 26, fontWeight: 'bold', color: '#111827' },
  pageSubtitle: { fontSize: 14, color: '#6b7280', marginTop: 6, lineHeight: 20 },
  
  // Style baru untuk Loading State
  loadingContainer: { paddingVertical: 50, alignItems: 'center', justifyContent: 'center' },
  loadingText: { marginTop: 12, color: '#6b7280', fontSize: 14 },

  statsContainer: { marginBottom: 16 },
  flexRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 14 },
  
  statCard: { paddingVertical: 18, paddingHorizontal: 20, borderRadius: 8, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.1, shadowRadius: 2 },
  cardFull: { width: '100%', marginBottom: 14 },
  cardHalf: { width: '48%' }, 
  cardLeftBorder: { backgroundColor: '#ffffff', borderLeftWidth: 5, borderLeftColor: '#22c55e' },
  cardSolidGreen: { backgroundColor: '#22c55e' },
  cardSolidRed: { backgroundColor: '#ef4444' },
  
  statLabel: { fontSize: 13, color: '#6b7280', fontWeight: '600' },
  statLabelWhite: { fontSize: 13, color: '#ffffff', fontWeight: '600' },
  statValueDark: { fontSize: 28, fontWeight: 'bold', color: '#111827', marginTop: 8 },
  statValueYellow: { fontSize: 28, fontWeight: 'bold', color: '#eab308', marginTop: 8 }, 
  statValueBlue: { fontSize: 28, fontWeight: 'bold', color: '#3b82f6', marginTop: 8 }, 
  statValueWhite: { fontSize: 28, fontWeight: 'bold', color: '#ffffff', marginTop: 8 },

  emptyChartCard: { 
    backgroundColor: '#ffffff', 
    borderRadius: 8, 
    padding: 30, 
    alignItems: 'center', 
    justifyContent: 'center', 
    marginBottom: 20,
    elevation: 2,
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 2,
  },
  folderIconContainer: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#dcfce7',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  emptyChartTitle: { fontSize: 16, fontWeight: 'bold', color: '#111827', marginBottom: 8, textAlign: 'center' },
  emptyChartSubtitle: { fontSize: 13, color: '#6b7280', textAlign: 'center', lineHeight: 20, paddingHorizontal: 10 },

  chartCard: {
    backgroundColor: '#ffffff',
    borderRadius: 8,
    paddingVertical: 20,
    paddingHorizontal: 10,
    marginBottom: 20,
    elevation: 2,
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 2,
    alignItems: 'center'
  },
  chartTitle: { fontSize: 16, fontWeight: 'bold', color: '#111827', marginBottom: 10, alignSelf: 'flex-start', marginLeft: 10 },

  actionButton: { backgroundColor: '#166534', flexDirection: 'row', justifyContent: 'center', alignItems: 'center', padding: 16, borderRadius: 12, shadowColor: '#000', shadowOpacity: 0.1, shadowRadius: 3, marginBottom: 20 },
  actionButtonText: { color: '#ffffff', fontWeight: 'bold', fontSize: 16 },
  
  navBar: { flexDirection: 'row', backgroundColor: '#14532d', position: 'absolute', bottom: 0, left: 0, right: 0, height: 60, alignItems: 'center', borderTopWidth: 1, borderTopColor: '#166534' },
  navItemContainer: { flex: 1, alignItems: 'center' },
  navItem: { alignItems: 'center' },
  navItemActive: { alignItems: 'center', backgroundColor: '#166534', paddingVertical: 6, paddingHorizontal: 16, borderRadius: 8 },
  navText: { color: '#9ca3af', fontSize: 11, marginTop: 2 },
  navTextActive: { color: '#ffffff', fontSize: 11, fontWeight: 'bold', marginTop: 2 }
});