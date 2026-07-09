import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_blue_plus/flutter_blue_plus.dart';
import 'package:permission_handler/permission_handler.dart';

import 'mhs_dashboard.dart';
import 'dosen_rekap.dart';

// ═══════════════════════════════════════════════════════════════════════
// ENTRY POINT
// ═══════════════════════════════════════════════════════════════════════
void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Sistem Absensi BLE',
      theme: ThemeData(
        fontFamily: 'Arial',
        useMaterial3: true,
        colorSchemeSeed: const Color(0xFF007BFF),
      ),
      home: const SplashGate(),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════
// SPLASH GATE — cek sesi tersimpan, arahkan ke halaman yang sesuai
// ═══════════════════════════════════════════════════════════════════════
class SplashGate extends StatefulWidget {
  const SplashGate({super.key});

  @override
  State<SplashGate> createState() => _SplashGateState();
}

class _SplashGateState extends State<SplashGate> {
  @override
  void initState() {
    super.initState();
    _checkSession();
  }

  Future<void> _checkSession() async {
    final hasSession = await StorageService.hasSession();

    if (!hasSession) {
      _goTo(const LoginPage());
      return;
    }

    final role = await StorageService.getRole();

    if (role == 'mahasiswa') {
      _goTo(const AbsensiPage());
    } else if (role == 'dosen') {
      _goTo(const LiveAbsensiPage());
    } else {
      _goTo(const LoginPage());
    }
  }

  void _goTo(Widget screen) {
    if (!mounted) return;
    Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => screen));
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFF007BFF),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.bluetooth_connected, color: Colors.white, size: 64),
            SizedBox(height: 16),
            Text('BLE ABSEN',
                style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
            SizedBox(height: 24),
            CircularProgressIndicator(color: Colors.white),
          ],
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════
// API CLIENT — Dio + auto-attach Bearer token Sanctum
// ═══════════════════════════════════════════════════════════════════════
class ApiClient {
  /// PENTING: Ganti sesuai environment:
  ///  - Emulator Android         -> http://10.0.2.2:8000/api
  ///  - Device fisik (WiFi sama) -> http://<IP-LAPTOP>:8000/api
  ///  - iOS Simulator            -> http://localhost:8000/api
  static const String baseUrl = 'http://10.177.99.39:8000/api';

  static Dio? _dio;

  static Dio get instance {
    _dio ??= _build();
    return _dio!;
  }

  static Dio _build() {
    final dio = Dio(
      BaseOptions(
        baseUrl: baseUrl,
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 10),
        headers: {'Accept': 'application/json'},
      ),
    );

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await StorageService.getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (DioException e, handler) => handler.next(e),
      ),
    );

    return dio;
  }
}

/// Helper baca pesan error dari response Laravel secara konsisten.
String extractErrorMessage(DioException e) {
  final data = e.response?.data;
  if (data is Map<String, dynamic>) {
    if (data['message'] != null) return data['message'].toString();
    if (data['errors'] != null && data['errors'] is Map) {
      final errors = data['errors'] as Map;
      final first = errors.values.first;
      if (first is List && first.isNotEmpty) return first.first.toString();
    }
  }
  return 'Terjadi kesalahan. Silakan coba lagi.';
}

// ═══════════════════════════════════════════════════════════════════════
// STORAGE SERVICE — simpan token, role, dan data user (secure storage)
// ═══════════════════════════════════════════════════════════════════════
class StorageService {
  static const _storage = FlutterSecureStorage();

  static const _kToken = 'ble_absen_token';
  static const _kRole = 'ble_absen_role';
  static const _kUser = 'ble_absen_user';

  static Future<void> saveToken(String token) => _storage.write(key: _kToken, value: token);
  static Future<String?> getToken() => _storage.read(key: _kToken);

  static Future<void> saveRole(String role) => _storage.write(key: _kRole, value: role);
  static Future<String?> getRole() => _storage.read(key: _kRole);

  static Future<void> saveUser(Map<String, dynamic> user) =>
      _storage.write(key: _kUser, value: jsonEncode(user));

  static Future<Map<String, dynamic>?> getUser() async {
    final raw = await _storage.read(key: _kUser);
    if (raw == null) return null;
    return jsonDecode(raw) as Map<String, dynamic>;
  }

  static Future<void> clearAll() async {
    await _storage.delete(key: _kToken);
    await _storage.delete(key: _kRole);
    await _storage.delete(key: _kUser);
  }

  static Future<bool> hasSession() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
}

// ═══════════════════════════════════════════════════════════════════════
// BLE SERVICE — scan beacon ruangan menggunakan flutter_blue_plus
// ═══════════════════════════════════════════════════════════════════════
class BleScanResultData {
  final String idRuangan;
  final String deviceName;
  final int rssi;

  BleScanResultData({required this.idRuangan, required this.deviceName, required this.rssi});
}

/// Konvensi nama beacon: "BLE-<id_ruangan>", contoh "BLE-GU805"
/// untuk ruangan dengan id_ruangan = "GU-805" (strip dihilangkan
/// karena nama lokal BLE tidak mendukung karakter "-" di semua perangkat).
class BleService {
  // UUID harus sama persis dengan di kode ESP32
  static const String serviceUuid        = 'ca3cbe46-f0e0-44e6-88df-c4a74c1cd239';
  static const String characteristicUuid = '91c1a7d2-5ed8-438c-8a21-2671176cc3ca';

  static Future<bool> requestPermissions() async {
    final statuses = await [
      Permission.bluetoothScan,
      Permission.bluetoothConnect,
      Permission.locationWhenInUse,
    ].request();
    return statuses.values.every((s) => s.isGranted || s.isLimited);
  }

  static Future<bool> isBluetoothOn() async {
    final state = await FlutterBluePlus.adapterState.first;
    return state == BluetoothAdapterState.on;
  }

  /// Scan beacon → konek → baca characteristic → disconnect → return room ID
  static Future<BleScanResultData?> scanForRoom(List<String> validRoomIds) async {
    final hasPermission = await requestPermissions();
    if (!hasPermission) return null;

    final btOn = await isBluetoothOn();
    if (!btOn) return null;

    // ── STEP 1: Scan sampai ketemu beacon dengan service UUID kita ──
    BluetoothDevice? targetDevice;

    await FlutterBluePlus.startScan(
      withServices: [Guid(serviceUuid)], // filter hanya beacon kita
      timeout: const Duration(seconds: 8),
    );

    await for (final results in FlutterBluePlus.scanResults) {
      for (final r in results) {
        if (r.device.remoteId.str.isNotEmpty) {
          targetDevice = r.device;
          await FlutterBluePlus.stopScan();
          break;
        }
      }
      if (targetDevice != null) break;
    }

    if (targetDevice == null) return null;

    // ── STEP 2: Konek ke ESP32 ──────────────────────────────────────
    try {
      await targetDevice.connect(timeout: const Duration(seconds: 5));

      // ── STEP 3: Discover services ──────────────────────────────────
      final services = await targetDevice.discoverServices();

      String? roomId;

      for (final service in services) {
        if (service.uuid.toString().toLowerCase() == serviceUuid.toLowerCase()) {
          for (final char in service.characteristics) {
            if (char.uuid.toString().toLowerCase() == characteristicUuid.toLowerCase()) {

              // ── STEP 4: Baca room ID dari characteristic ────────────
              final value = await char.read();
              roomId = String.fromCharCodes(value);
              break;
            }
          }
        }
        if (roomId != null) break;
      }

      // ── STEP 5: Disconnect setelah selesai baca ─────────────────────
      await targetDevice.disconnect();

      if (roomId == null || roomId.isEmpty) return null;

      // ── STEP 6: Validasi room ID ada di daftar Laravel ─────────────
      final matched = validRoomIds.firstWhere(
        (id) => id == roomId,
        orElse: () => '',
      );

      if (matched.isEmpty) return null;

      return BleScanResultData(
        idRuangan:  matched,
        deviceName: targetDevice.platformName,
        rssi:       0,
      );

    } catch (e) {
      // Pastikan disconnect jika terjadi error
      try { await targetDevice.disconnect(); } catch (_) {}
      return null;
    }
  }
}

// ═══════════════════════════════════════════════════════════════════════
// AUTH SERVICE — login, logout, me, ganti password
// ═══════════════════════════════════════════════════════════════════════
class AuthResult {
  final bool success;
  final String? message;
  final String? role;
  final Map<String, dynamic>? user;

  AuthResult({required this.success, this.message, this.role, this.user});
}

class AuthService {
  static final Dio _dio = ApiClient.instance;

  static Future<AuthResult> login({
    required String username,
    required String password,
    required String role,
  }) async {
    try {
      final response = await _dio.post('/auth/login', data: {
        'username': username,
        'password': password,
        'role': role,
      });

      final data = response.data;
      final token = data['token'] as String;
      final userRole = data['role'] as String;
      final user = data['user'] as Map<String, dynamic>;

      await StorageService.saveToken(token);
      await StorageService.saveRole(userRole);
      await StorageService.saveUser(user);

      return AuthResult(success: true, role: userRole, user: user);
    } on DioException catch (e) {
      return AuthResult(success: false, message: extractErrorMessage(e));
    }
  }

  static Future<void> logout() async {
    try {
      await _dio.post('/auth/logout');
    } catch (_) {
      // tetap hapus sesi lokal meskipun request gagal (mis. offline)
    }
    await StorageService.clearAll();
  }

  static Future<Map<String, dynamic>?> me() async {
    try {
      final response = await _dio.get('/auth/me');
      await StorageService.saveUser(response.data as Map<String, dynamic>);
      return response.data as Map<String, dynamic>;
    } on DioException {
      return null;
    }
  }

  static Future<AuthResult> changePassword({
    required String passwordLama,
    required String passwordBaru,
    required String passwordBaruConfirm,
  }) async {
    try {
      final response = await _dio.post('/auth/change-password', data: {
        'password_lama': passwordLama,
        'password_baru': passwordBaru,
        'password_baru_confirm': passwordBaruConfirm,
      });
      return AuthResult(success: true, message: response.data['message']);
    } on DioException catch (e) {
      return AuthResult(success: false, message: extractErrorMessage(e));
    }
  }
}

// ═══════════════════════════════════════════════════════════════════════
// LOGIN PAGE
// ═══════════════════════════════════════════════════════════════════════
class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text;

    if (username.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'Username dan password wajib diisi.');
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final result = await AuthService.login(
      username: username,
      password: password,
      role: 'mahasiswa',
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (!result.success) {
      setState(() => _errorMessage = result.message);
      return;
    }

    if (result.role == 'mahasiswa') {
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const AbsensiPage()));
    } else {
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LiveAbsensiPage()));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FB),
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // HEADER SECTION
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 25, horizontal: 15),
                color: const Color(0xFF1553A5),
                child: const Text(
                  'Sistem Absensi BLE',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                ),
              ),

              // LOGO SECTION
              const SizedBox(height: 35),
              Center(
                child: Column(
                  children: [
                    Stack(
                      alignment: Alignment.center,
                      children: [
                        const Icon(Icons.location_on, size: 90, color: Color(0xFF1553A5)),
                        Positioned(
                          top: 15,
                          child: Container(
                            padding: const EdgeInsets.all(4),
                            decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                            child: const Icon(Icons.bluetooth, size: 28, color: Color(0xFF1553A5)),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      'BLE Absen',
                      style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold, color: Color(0xFF1553A5)),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'SISTEM INFORMASI ABSENSI PERKULIAHAN\nBERBASIS IOT DENGAN BLE BEACON',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 10, color: Color(0xFF1553A5), fontWeight: FontWeight.w600, letterSpacing: 0.5),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 15),

              // CARD SECTION
              Container(
                margin: const EdgeInsets.all(20),
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 6, offset: const Offset(0, 2)),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (_errorMessage != null) ...[
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(12),
                        margin: const EdgeInsets.only(bottom: 14),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFE5E5),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(_errorMessage!,
                            style: const TextStyle(color: Color(0xFFC62828), fontSize: 13)),
                      ),
                    ],

                    // LABEL USERNAME
                    const Text('Username', style: TextStyle(fontSize: 12, color: Colors.grey)),
                    const SizedBox(height: 5),

                    // INPUT USERNAME
                    Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: TextField(
                        controller: _usernameController,
                        decoration: InputDecoration(
                          hintText: 'Masukkan username',
                          hintStyle: const TextStyle(color: Colors.grey, fontSize: 14),
                          contentPadding: const EdgeInsets.all(12),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFFDDDDDD)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFF007BFF), width: 1.5),
                          ),
                        ),
                      ),
                    ),

                    // LABEL PASSWORD
                    const Text('Password', style: TextStyle(fontSize: 12, color: Colors.grey)),
                    const SizedBox(height: 5),

                    // INPUT PASSWORD
                    Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: TextField(
                        controller: _passwordController,
                        obscureText: true,
                        decoration: InputDecoration(
                          hintText: 'Masukkan password',
                          hintStyle: const TextStyle(color: Colors.grey, fontSize: 14),
                          contentPadding: const EdgeInsets.all(12),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFFDDDDDD)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFF007BFF), width: 1.5),
                          ),
                        ),
                        onSubmitted: (_) => _handleLogin(),
                      ),
                    ),

                    const SizedBox(height: 8),

                    // BUTTON LOGIN
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF1553A5),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          elevation: 0,
                        ),
                        onPressed: _isLoading ? null : _handleLogin,
                        child: _isLoading
                            ? const SizedBox(
                                height: 18,
                                width: 18,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                              )
                            : const Text('Login', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
