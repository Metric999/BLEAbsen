import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

import 'main.dart';
import 'mhs_dashboard.dart';
import 'mhs_profile.dart';

// ═══════════════════════════════════════════════════════════════════════
// MODEL & ENUM: Status absensi — disesuaikan dengan enum Laravel
// enum('hadir', 'izin', 'alpha') — juga dipakai oleh dosen_rekap.dart
// ═══════════════════════════════════════════════════════════════════════
enum AbsensiStatus { hadir, izin, alpha }

AbsensiStatus parseAbsensiStatus(String? raw) {
  switch (raw) {
    case 'hadir':
      return AbsensiStatus.hadir;
    case 'izin':
      return AbsensiStatus.izin;
    default:
      return AbsensiStatus.alpha;
  }
}

String absensiStatusLabel(AbsensiStatus status) {
  switch (status) {
    case AbsensiStatus.hadir:
      return 'Hadir';
    case AbsensiStatus.izin:
      return 'Izin';
    case AbsensiStatus.alpha:
      return 'Alpha';
  }
}

class AbsensiModel {
  final int idAbsensi;
  final String tanggal;
  final AbsensiStatus status;
  final String? keterangan;
  final JadwalModel jadwal;

  AbsensiModel({
    required this.idAbsensi,
    required this.tanggal,
    required this.status,
    this.keterangan,
    required this.jadwal,
  });

  factory AbsensiModel.fromJson(Map<String, dynamic> json) {
    return AbsensiModel(
      idAbsensi: json['id_absensi'],
      tanggal: json['tanggal'] ?? '',
      status: parseAbsensiStatus(json['status']),
      keterangan: json['keterangan'],
      jadwal: JadwalModel.fromJson(json['jadwal'] ?? {}),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════
// RIWAYAT ABSENSI PAGE — fetch dari GET /mahasiswa/absensi/riwayat
// ═══════════════════════════════════════════════════════════════════════
class RiwayatAbsensiPage extends StatefulWidget {
  const RiwayatAbsensiPage({super.key});

  @override
  State<RiwayatAbsensiPage> createState() => _RiwayatAbsensiPageState();
}

class _RiwayatAbsensiPageState extends State<RiwayatAbsensiPage> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final Dio _dio = ApiClient.instance;

  List<AbsensiModel> _riwayatList = [];
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadRiwayat();
  }

  Future<void> _loadRiwayat() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _dio.get('/mahasiswa/absensi/riwayat');
      final List items = response.data['data'] ?? [];

      setState(() {
        _riwayatList = items.map((e) => AbsensiModel.fromJson(e)).toList();
        _isLoading = false;
      });
    } on DioException catch (e) {
      setState(() {
        _errorMessage = extractErrorMessage(e);
        _isLoading = false;
      });
    }
  }

  int get _totalHadirBulanIni {
    final now = DateTime.now();
    return _riwayatList.where((a) {
      final date = DateTime.tryParse(a.tanggal);
      return a.status == AbsensiStatus.hadir &&
          date != null &&
          date.month == now.month &&
          date.year == now.year;
    }).length;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: const Color(0xFFF5F7FB),

      // SIDEBAR (DRAWER)
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
              onTap: () {
                Navigator.pop(context);
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (context) => const AbsensiPage()),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.history, color: Color(0xFF007BFF)),
              title: const Text('Riwayat Absensi'),
              onTap: () => Navigator.pop(context),
            ),
          ],
        ),
      ),

      body: SafeArea(
        child: Column(
          children: [
            // HEADER
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
                    child: Text('Riwayat Absensi',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                  ),
                  PopupMenuButton<String>(
                    onSelected: (value) {
                      if (value == 'profile') {
                        Navigator.push(context, MaterialPageRoute(builder: (context) => const ProfilePage()));
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
                    ],
                  ),
                ],
              ),
            ),

            // CONTAINER UTAMA
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _errorMessage != null
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.all(20),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(_errorMessage!, textAlign: TextAlign.center),
                                const SizedBox(height: 12),
                                ElevatedButton(onPressed: _loadRiwayat, child: const Text('Coba Lagi')),
                              ],
                            ),
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _loadRiwayat,
                          child: ListView(
                            padding: const EdgeInsets.all(15),
                            children: [
                              // RINGKASAN CARD
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(15),
                                margin: const EdgeInsets.only(bottom: 15),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFE0F0FF),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Column(
                                  children: [
                                    Text('$_totalHadirBulanIni',
                                        style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
                                    const SizedBox(height: 4),
                                    const Text('Total Kehadiran Bulan Ini',
                                        style: TextStyle(fontSize: 14, color: Colors.black87)),
                                  ],
                                ),
                              ),

                              if (_riwayatList.isEmpty)
                                const Padding(
                                  padding: EdgeInsets.only(top: 40),
                                  child: Center(
                                    child:
                                        Text('Belum ada riwayat absensi.', style: TextStyle(color: Colors.grey)),
                                  ),
                                )
                              else
                                ..._riwayatList.map((item) => _buildCardRiwayat(item)),
                            ],
                          ),
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCardRiwayat(AbsensiModel item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 6, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(item.jadwal.namaMatkul, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
          const SizedBox(height: 5),
          Text(item.tanggal, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          const SizedBox(height: 10),
          _buildStatusBadge(item.status),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(AbsensiStatus type) {
    Color backgroundColor;
    Color textColor;

    switch (type) {
      case AbsensiStatus.hadir:
        backgroundColor = const Color(0xFFE6FFED);
        textColor = const Color(0xFF1A7F37);
        break;
      case AbsensiStatus.izin:
        backgroundColor = const Color(0xFFFFF4E5);
        textColor = const Color(0xFFB26A00);
        break;
      case AbsensiStatus.alpha:
        backgroundColor = const Color(0xFFFFE5E5);
        textColor = const Color(0xFFC62828);
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 10),
      decoration: BoxDecoration(color: backgroundColor, borderRadius: BorderRadius.circular(8)),
      child: Text(absensiStatusLabel(type),
          style: TextStyle(color: textColor, fontWeight: FontWeight.bold, fontSize: 12)),
    );
  }
}
