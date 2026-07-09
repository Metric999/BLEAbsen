import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

import 'main.dart';
import 'mhs_riwayat.dart';
import 'mhs_profile.dart';

// ═══════════════════════════════════════════════════════════════════════
// MODEL: Jadwal — dipakai juga oleh mhs_riwayat.dart & dosen_rekap.dart
// ═══════════════════════════════════════════════════════════════════════
class JadwalModel {
  final int idJadwal;
  final String? kelas;
  final String hari;
  final String jamMulai;
  final String jamSelesai;
  final String namaMatkul;
  final String idRuangan;
  final String? namaRuangan;
  final String? namaDosen;

  JadwalModel({
    required this.idJadwal,
    this.kelas,
    required this.hari,
    required this.jamMulai,
    required this.jamSelesai,
    required this.namaMatkul,
    required this.idRuangan,
    this.namaRuangan,
    this.namaDosen,
  });

  factory JadwalModel.fromJson(Map<String, dynamic> json) {
    return JadwalModel(
      idJadwal: json['id_jadwal'],
      kelas: json['kelas'],
      hari: json['hari'] ?? '',
      jamMulai: (json['jam_mulai'] ?? '').toString().substring(0, 5),
      jamSelesai: (json['jam_selesai'] ?? '').toString().substring(0, 5),
      namaMatkul: json['mata_kuliah']?['nama_matkul'] ?? '-',
      idRuangan: json['id_ruangan'] ?? json['ruangan']?['id_ruangan'] ?? '-',
      namaRuangan: json['ruangan']?['nama_ruangan'],
      namaDosen: json['dosen']?['nama'],
    );
  }

  String get jamRange => '$jamMulai - $jamSelesai';
}

enum _DashStatus { scanning, bleNotFound, jadwalInvalid, alreadyAbsen, ready, submitting, done }
enum _StatusType { success, warning, danger }

// ═══════════════════════════════════════════════════════════════════════
// DASHBOARD MAHASISWA
// ═══════════════════════════════════════════════════════════════════════
class AbsensiPage extends StatefulWidget {
  const AbsensiPage({super.key});

  @override
  State<AbsensiPage> createState() => _AbsensiPageState();
}

class _AbsensiPageState extends State<AbsensiPage> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final Dio _dio = ApiClient.instance;

  _DashStatus _status = _DashStatus.scanning;
  String? _detectedRoomId;
  JadwalModel? _jadwal;
  String _statusMessage = 'Mencari sinyal BLE di sekitar...';

  @override
  void initState() {
    super.initState();
    _runFlow();
  }

  /// Alur lengkap: scan BLE -> cocokkan ruangan -> validasi jadwal ke server.
  Future<void> _runFlow() async {
    setState(() {
      _status = _DashStatus.scanning;
      _statusMessage = 'Mencari sinyal BLE di sekitar...';
    });

    try {
      // 1. Ambil daftar ruangan terdaftar dari server (untuk validasi beacon)
      final roomsResponse = await _dio.get('/ruangan');
      final rooms = (roomsResponse.data as List).map((r) => r['id_ruangan'].toString()).toList();

      // 2. Scan BLE beacon di sekitar
      final scanResult = await BleService.scanForRoom(rooms);

      if (scanResult == null) {
        setState(() {
          _status = _DashStatus.bleNotFound;
          _statusMessage = 'Sinyal BLE ruangan tidak ditemukan. '
              'Pastikan Bluetooth aktif dan Anda berada di dalam kelas.';
        });
        return;
      }

      _detectedRoomId = scanResult.idRuangan;

      // 3. Validasi jadwal ke backend (hari + jam + ruangan + terdaftar di jadwal)
      final checkResponse = await _dio.post('/mahasiswa/absensi/check', data: {
        'id_ruangan': _detectedRoomId,
      });

      final data = checkResponse.data;
      _jadwal = JadwalModel.fromJson(data['jadwal']);

      setState(() => _status = _DashStatus.ready);
    } on DioException catch (e) {
      final status = e.response?.data?['status'];
      final message = extractErrorMessage(e);

      if (status == 'already') {
        _jadwal = JadwalModel.fromJson(e.response?.data['jadwal'] ?? {});
        setState(() {
          _status = _DashStatus.alreadyAbsen;
          _statusMessage = message;
        });
      } else {
        setState(() {
          _status = _DashStatus.jadwalInvalid;
          _statusMessage = message;
        });
      }
    } catch (_) {
      setState(() {
        _status = _DashStatus.jadwalInvalid;
        _statusMessage = 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
      });
    }
  }

  Future<void> _submitAbsensi() async {
    if (_jadwal == null) return;
    setState(() => _status = _DashStatus.submitting);

    try {
      await _dio.post('/mahasiswa/absensi/store', data: {'id_jadwal': _jadwal!.idJadwal});

      setState(() {
        _status = _DashStatus.done;
        _statusMessage = 'Absensi berhasil dicatat!';
      });
    } on DioException catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(extractErrorMessage(e))));
      setState(() => _status = _DashStatus.ready);
    }
  }

  Future<void> _handleLogout() async {
    await AuthService.logout();
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const LoginPage()),
      (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: const Color(0xFFF5F7FB),

      drawer: Drawer(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const DrawerHeader(
              decoration: BoxDecoration(color: Color(0xFF007BFF)),
              child: Align(
                alignment: Alignment.bottomLeft,
                child: Text('Menu Navigasi',
                    style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
              ),
            ),
            ListTile(
              leading: const Icon(Icons.home, color: Color(0xFF007BFF)),
              title: const Text('Beranda'),
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              leading: const Icon(Icons.history, color: Color(0xFF007BFF)),
              title: const Text('Riwayat Absensi'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (context) => const RiwayatAbsensiPage()),
                );
              },
            ),
          ],
        ),
      ),

      body: SafeArea(
        child: Column(
          children: [
            // HEADER DENGAN MENU DI KIRI DAN LOGO PROFILE DI KANAN
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
              color: const Color(0xFF007BFF),
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.menu, color: Colors.white, size: 24),
                    onPressed: () => _scaffoldKey.currentState?.openDrawer(),
                  ),
                  const Expanded(
                    child: Text(
                      'Absensi Mahasiswa',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                  ),
                  PopupMenuButton<String>(
                    onSelected: (value) {
                      if (value == 'profile') {
                        Navigator.push(context, MaterialPageRoute(builder: (context) => const ProfilePage()));
                      } else if (value == 'logout') {
                        _handleLogout();
                      }
                    },
                    icon: const Icon(Icons.account_circle, color: Colors.white, size: 28),
                    itemBuilder: (BuildContext context) => [
                      const PopupMenuItem<String>(
                        value: 'profile',
                        child: Row(children: [
                          Icon(Icons.person, color: Colors.black87, size: 20),
                          SizedBox(width: 10),
                          Text('Profile'),
                        ]),
                      ),
                      const PopupMenuItem<String>(
                        value: 'logout',
                        child: Row(children: [
                          Icon(Icons.logout, color: Colors.red, size: 20),
                          SizedBox(width: 10),
                          Text('Log Out', style: TextStyle(color: Colors.red)),
                        ]),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // CONTAINER (Isi Halaman)
            Expanded(
              child: RefreshIndicator(
                onRefresh: _runFlow,
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(15),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _buildBleStatusCard(),
                      if (_jadwal != null) _buildJadwalCard(),
                      _buildAbsensiStatusCard(),
                      const SizedBox(height: 10),
                      _buildActionButton(),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Cards ────────────────────────────────────────────────────────────
  Widget _buildBleStatusCard() {
    final isScanning = _status == _DashStatus.scanning;
    final isFound = _status != _DashStatus.bleNotFound && _status != _DashStatus.scanning;

    return _buildCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildLabel('Status Koneksi'),
          const SizedBox(height: 5),
          if (isScanning)
            Row(children: const [
              SizedBox(
                width: 14,
                height: 14,
                child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF007BFF)),
              ),
              SizedBox(width: 10),
              Text('Memindai...', style: TextStyle(fontSize: 14)),
            ])
          else
            _buildStatusTag(
              text: isFound ? 'BLE Terdeteksi - $_detectedRoomId' : 'BLE Tidak Terdeteksi',
              type: isFound ? _StatusType.success : _StatusType.danger,
            ),
          const SizedBox(height: 10),
          Text(_statusMessage, style: const TextStyle(fontSize: 14)),
        ],
      ),
    );
  }

  Widget _buildJadwalCard() {
    final j = _jadwal!;
    return _buildCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildLabel('Ruangan'),
          Text(j.idRuangan, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 10),
          if (j.kelas != null) ...[
            _buildLabel('Kelas'),
            Text(j.kelas!, style: const TextStyle(fontSize: 15)),
            const SizedBox(height: 10),
          ],
          _buildLabel('Mata Kuliah'),
          Text(j.namaMatkul, style: const TextStyle(fontSize: 15)),
          const SizedBox(height: 10),
          _buildLabel('Dosen'),
          Text(j.namaDosen ?? '-', style: const TextStyle(fontSize: 15)),
          const SizedBox(height: 10),
          _buildLabel('Waktu Kuliah'),
          Text(j.jamRange, style: const TextStyle(fontSize: 15)),
        ],
      ),
    );
  }

  Widget _buildAbsensiStatusCard() {
    String text;
    String desc;
    _StatusType type;

    switch (_status) {
      case _DashStatus.ready:
        text = 'Silakan Absen (Waktu Sesuai)';
        desc = 'Anda berada di waktu yang tepat untuk melakukan absensi';
        type = _StatusType.success;
        break;
      case _DashStatus.alreadyAbsen:
        text = 'Sudah Absen Hari Ini';
        desc = _statusMessage;
        type = _StatusType.warning;
        break;
      case _DashStatus.done:
        text = 'Absensi Berhasil';
        desc = 'Kehadiran Anda telah tercatat di sistem';
        type = _StatusType.success;
        break;
      case _DashStatus.jadwalInvalid:
      case _DashStatus.bleNotFound:
        text = 'Belum Bisa Absen';
        desc = _statusMessage;
        type = _StatusType.danger;
        break;
      default:
        text = 'Memeriksa...';
        desc = 'Mohon tunggu sebentar';
        type = _StatusType.warning;
    }

    return _buildCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildLabel('Status Absensi'),
          const SizedBox(height: 5),
          _buildStatusTag(text: text, type: type),
          const SizedBox(height: 10),
          Text(desc, style: const TextStyle(fontSize: 14)),
        ],
      ),
    );
  }

  Widget _buildActionButton() {
    final canAbsen = _status == _DashStatus.ready;
    final isSubmitting = _status == _DashStatus.submitting;
    final isDone = _status == _DashStatus.done;

    return ElevatedButton(
      style: ElevatedButton.styleFrom(
        backgroundColor: isDone ? Colors.grey : const Color(0xFF007BFF),
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(vertical: 14),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        elevation: 0,
      ),
      onPressed: canAbsen && !isSubmitting ? _submitAbsensi : (isDone ? null : _runFlow),
      child: isSubmitting
          ? const SizedBox(
              height: 18, width: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
          : Text(
              isDone ? 'Absensi Selesai' : (canAbsen ? 'Absen Sekarang' : 'Coba Lagi'),
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
    );
  }

  // --- REUSABLE WIDGETS ---
  Widget _buildCard({required Widget child}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 6, offset: const Offset(0, 2))],
      ),
      child: child,
    );
  }

  Widget _buildLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 5),
      child: Text(text, style: const TextStyle(fontSize: 12, color: Colors.grey)),
    );
  }

  Widget _buildStatusTag({required String text, required _StatusType type}) {
    Color backgroundColor;
    Color textColor;

    switch (type) {
      case _StatusType.success:
        backgroundColor = const Color(0xFFE6FFED);
        textColor = const Color(0xFF1A7F37);
        break;
      case _StatusType.warning:
        backgroundColor = const Color(0xFFFFF4E5);
        textColor = const Color(0xFFB26A00);
        break;
      case _StatusType.danger:
        backgroundColor = const Color(0xFFFFE5E5);
        textColor = const Color(0xFFC62828);
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
      decoration: BoxDecoration(color: backgroundColor, borderRadius: BorderRadius.circular(8)),
      child: Text(text, style: TextStyle(color: textColor, fontWeight: FontWeight.bold, fontSize: 14)),
    );
  }
}
