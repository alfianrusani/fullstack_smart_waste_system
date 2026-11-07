import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { useState } from 'react';
import { 
  StyleSheet, 
  Text, 
  View, 
  TextInput, 
  TouchableOpacity, 
  Alert, 
  ActivityIndicator,
  KeyboardAvoidingView, 
  Platform, 
  SafeAreaView 
} from 'react-native';
import { useRouter } from 'expo-router';
import axios from 'axios';
import { FontAwesome5 } from '@expo/vector-icons';

export default function LoginScreen() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Email dan Password wajib diisi!');
      return;
    }

    setLoading(true);
    try {
      const response = await axios.post('http://192.168.80.38:8000/api/login', {
        email,
        password,
      });

      if (response.data.success) {
        // Ambil token dari properti access_token yang dikirim Laravel
        const token = response.data.access_token || response.data.token;
        
        if (token) {
          await AsyncStorage.setItem('userToken', token);
          console.log("Token berhasil disimpan:", token);
        } else {
          console.log("Token tidak ditemukan dalam respons:", response.data);
        }

        Alert.alert('Sukses', 'Login Berhasil!');
        router.replace('/home');
      }
    } catch (error) {
      // Menampilkan pesan error detail langsung dari Laravel jika ada
      const serverMessage = error.response?.data?.message || error.response?.data?.error || JSON.stringify(error.response?.data);
      console.log("Detail Error Laravel:", error.response?.data);
      
      Alert.alert('Gagal Login (Server 500)', serverMessage || 'Terjadi kesalahan pada server Laravel.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.container}
      >
        {/* BAGIAN HEADER (Branding) */}
        <View style={styles.header}>
          <View style={styles.iconWrapper}>
            <FontAwesome5 name="leaf" size={28} color="#15803d" />
          </View>
          <Text style={styles.brandTitle}>Smart Waste</Text>
        </View>

        {/* BAGIAN FORM (Card Putih) */}
        <View style={styles.card}>
          <View style={styles.welcomeSection}>
            <Text style={styles.title}>Selamat Datang 👋</Text>
            <Text style={styles.subtitle}>Silakan masuk ke akun Anda untuk melanjutkan.</Text>
          </View>

          {/* Input Email */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Alamat Email</Text>
            <View style={styles.inputContainer}>
              <FontAwesome5 name="envelope" size={16} color="#9ca3af" style={styles.inputIcon} />
              <TextInput
                style={styles.input}
                placeholder="masukkan email Anda"
                placeholderTextColor="#9ca3af"
                value={email}
                onChangeText={setEmail}
                keyboardType="email-address"
                autoCapitalize="none"
              />
            </View>
          </View>

          {/* Input Password */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Kata Sandi</Text>
            <View style={styles.inputContainer}>
              <FontAwesome5 name="lock" size={16} color="#9ca3af" style={styles.inputIcon} />
              <TextInput
                style={styles.input}
                placeholder="masukkan kata sandi"
                placeholderTextColor="#9ca3af"
                value={password}
                onChangeText={setPassword}
                secureTextEntry
              />
            </View>
          </View>

          {/* Fitur Tambahan */}
          <View style={styles.extraContainer}>
            <TouchableOpacity 
              style={styles.checkboxContainer} 
              onPress={() => setRemember(!remember)}
              activeOpacity={0.7}
            >
              <FontAwesome5 
                name={remember ? "check-square" : "square"} 
                size={18} 
                color={remember ? "#16a34a" : "#9ca3af"} 
              />
              <Text style={styles.checkboxText}>Ingat saya</Text>
            </TouchableOpacity>
            <TouchableOpacity>
              <Text style={styles.forgotText}>Lupa Password?</Text>
            </TouchableOpacity>
          </View>

          {/* Tombol Masuk dengan Indikator Loading */}
          <TouchableOpacity 
            style={[styles.loginButton, loading && styles.loginButtonDisabled]} 
            onPress={handleLogin} 
            disabled={loading}
            activeOpacity={0.8}
          >
            {loading ? (
              <ActivityIndicator color="#ffffff" />
            ) : (
              <>
                <Text style={styles.loginButtonText}>Masuk</Text>
                <FontAwesome5 name="sign-in-alt" size={16} color="#ffffff" />
              </>
            )}
          </TouchableOpacity>

          {/* Link Pendaftaran */}
          <View style={styles.registerContainer}>
            <Text style={styles.registerText}>Belum punya akun? </Text>
            <TouchableOpacity>
              <Text style={styles.registerLink}>Daftar sekarang</Text>
            </TouchableOpacity>
          </View>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

// ------------------------------------------
// PENGATURAN GAYA
// ------------------------------------------
const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#f9fafb', 
  },
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: 24,
  },
  header: {
    alignItems: 'center',
    marginBottom: 24,
  },
  iconWrapper: {
    width: 64,
    height: 64,
    backgroundColor: '#dcfce7', 
    borderRadius: 32,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  brandTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#166534', 
  },
  card: {
    backgroundColor: '#ffffff', 
    padding: 24,
    borderRadius: 24, 
    borderWidth: 1,
    borderColor: '#f3f4f6', 
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.05,
    shadowRadius: 15,
    elevation: 5,
  },
  welcomeSection: {
    marginBottom: 24,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#1f2937', 
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 14,
    color: '#6b7280', 
  },
  inputGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#374151', 
    marginBottom: 8,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#d1d5db', 
    borderRadius: 12, 
    backgroundColor: '#ffffff',
  },
  inputIcon: {
    paddingLeft: 16,
    paddingRight: 10,
    paddingVertical: 14,
  },
  input: {
    flex: 1,
    paddingVertical: 14,
    paddingRight: 16,
    fontSize: 15,
    color: '#1f2937',
  },
  extraContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 8,
    marginBottom: 24,
  },
  checkboxContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  checkboxText: {
    marginLeft: 8,
    fontSize: 14,
    color: '#4b5563', 
  },
  forgotText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#16a34a', 
  },
  loginButton: {
    backgroundColor: '#16a34a', 
    paddingVertical: 16,
    borderRadius: 12,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#16a34a',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 5,
    elevation: 3,
  },
  loginButtonDisabled: {
    backgroundColor: '#86efac', 
    elevation: 0,
    shadowOpacity: 0,
  },
  loginButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: 'bold',
    marginRight: 8,
  },
  registerContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 24,
  },
  registerText: {
    fontSize: 14,
    color: '#6b7280', 
  },
  registerLink: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#16a34a', 
  },
});