import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

import 'main.dart';
import 'mhs_dashboard.dart';
import 'mhs_riwayat.dart';

// ═══════════════════════════════════════════════════════════════════════
// MODEL: Item daftar hadir mahasiswa (dipakai dosen)
// ═══════════════════════════════════════════════════════════════════════
class DaftarHadirItem {
  final String nim;
  final String nama;
  final String kelas;
  final AbsensiStatus status;
  final int? idAbsensi;

  DaftarHadirItem({
    required this.nim,
    required this.nama,
    required this.kelas,
    required this.status,
    this.idAbsensi,
  });

  factory DaftarHadirItem.fromJson(Map<String, dynamic> json) {
    return DaftarHadirItem(
      nim: json['nim'],
      nama: json['nama'],
      kelas: json['kelas'] ?? '',
      status: parseAbsensiStatus(json['status']),
      idAbsensi: json['id_absensi'],
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════
// LIVE ABSENSI PAGE — Dosen pilih jadwal, lihat daftar hadir real-time
// ═══════════════════════════════════════════════════════════════════════
class LiveAbsensiPage extends StatefulWidget {
  const LiveAbsensiPage({super.key});

  @override
  State<LiveAbsensiPage> createState() => _LiveAbsensiPageState();
}

class _LiveAbsensiPageState extends State<LiveAbsensiPage> {
  final Dio _dio = ApiClient.instance;

  List<JadwalModel> _jadwalList = [];
  JadwalModel? _selectedJadwal;
  List<DaftarHadirItem> _mahasiswaList = [];

  bool _isLoadingJadwal = true;
  bool _isLoadingDaftar = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadJadwal();
  }

  Future<void> _loadJadwal() async {
    setState(() => _isLoadingJadwal = true);
    try {
      final response = await _dio.get('/dosen/jadwal');
      final List items = response.data;
      final jadwals = items.map((e) => JadwalModel.fromJson(e)).toList();

      setState(() {
        _jadwalList = jadwals;
        _selectedJadwal = jadwals.isNotEmpty ? jadwals.first : null;
        _isLoadingJadwal = false;
      });

      if (_selectedJadwal != null) _loadDaftarHadir();
    } on DioException catch (e) {
      setState(() {
        _errorMessage = extractErrorMessage(e);
        _isLoadingJadwal = false;
      });
    }
  }

  Future<void> _loadDaftarHadir() async {
    if (_selectedJadwal == null) return;
    setState(() => _isLoadingDaftar = true);

    try {
      final response = await _dio.get('/dosen/absensi/${_selectedJadwal!.idJadwal}');
      final List items = response.data['mahasiswas'] ?? [];

      setState(() {
        _mahasiswaList = items.map((e) => DaftarHadirItem.fromJson(e)).toList();
        _isLoadingDaftar = false;
      });
    } on DioException catch (e) {
      setState(() => _isLoadingDaftar = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(extractErrorMessage(e))));
      }
    }
  }

  // FUNGSI UNTUK MENAMPILKAN MODAL REKAP ABSENSI
  void _openRekapModal() {
    final hadir = _mahasiswaList.where((m) => m.status == AbsensiStatus.hadir).length;
    final izin = _mahasiswaList.where((m) => m.status == AbsensiStatus.izin).length;
    final alpha = _mahasiswaList.where((m) => m.status == AbsensiStatus.alpha).length;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(20),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(15)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Rekap Absensi', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: const Text('✖', style: TextStyle(fontSize: 18, color: Colors.grey)),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // BOXES REKAP
              Row(
                children: [
                  _buildRekapBox(value: '$hadir', label: 'Hadir'),
                  _buildRekapBox(value: '$izin', label: 'Izin'),
                  _buildRekapBox(value: '$alpha', label: 'Alpha'),
                ],
              ),
              const SizedBox(height: 10),
            ],
          ),
        );
      },
    );
  }

  Future<void> _updateStatus(DaftarHadirItem item, AbsensiStatus newStatus) async {
    if (item.idAbsensi == null) return;

    try {
      await _dio.put('/dosen/absensi/${item.idAbsensi}', data: {'status': newStatus.name});
      _loadDaftarHadir();
    } on DioException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(extractErrorMessage(e))));
      }
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
      backgroundColor: const Color(0xFFF5F7FB),
      body: SafeArea(
        child: Column(
          children: [
            // HEADER
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(15),
              color: const Color(0xFF007BFF),
              child: Row(
                children: [
                  const Expanded(
                    child: Text(
                      'Live Monitoring Absensi',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.logout, color: Colors.white, size: 22),
                    onPressed: _handleLogout,
                  ),
                ],
              ),
            ),

            // CONTAINER UTAMA
            Expanded(
              child: _isLoadingJadwal
                  ? const Center(child: CircularProgressIndicator())
                  : _errorMessage != null
                      ? Center(child: Text(_errorMessage!))
                      : _jadwalList.isEmpty
                          ? const Center(child: Text('Anda belum memiliki jadwal mengajar.'))
                          : RefreshIndicator(
                              onRefresh: _loadDaftarHadir,
                              child: SingleChildScrollView(
                                padding: const EdgeInsets.all(15),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.stretch,
                                  children: [
                                    // CARD 1: PILIH JADWAL
                                    _buildCard(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          const Text('Pilih Jadwal',
                                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                                          const SizedBox(height: 10),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 12),
                                            decoration: BoxDecoration(
                                              borderRadius: BorderRadius.circular(10),
                                              border: Border.all(color: const Color(0xFFDDDDDD)),
                                              color: Colors.white,
                                            ),
                                            child: DropdownButtonHideUnderline(
                                              child: DropdownButton<JadwalModel>(
                                                value: _selectedJadwal,
                                                isExpanded: true,
                                                items: _jadwalList.map((j) {
                                                  final label = j.kelas != null
                                                      ? '${j.kelas} · ${j.hari} ${j.jamRange}'
                                                      : '${j.namaMatkul} · ${j.hari}';
                                                  return DropdownMenuItem(value: j, child: Text(label));
                                                }).toList(),
                                                onChanged: (value) {
                                                  setState(() => _selectedJadwal = value);
                                                  _loadDaftarHadir();
                                                },
                                              ),
                                            ),
                                          ),
                                          if (_selectedJadwal != null) ...[
                                            const SizedBox(height: 10),
                                            Text(_selectedJadwal!.namaMatkul,
                                                style: const TextStyle(fontSize: 13, color: Colors.grey)),
                                          ],
                                        ],
                                      ),
                                    ),

                                    // CARD 2: LIST MAHASISWA
                                    _buildCard(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          const Text('Daftar Mahasiswa',
                                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                                          const SizedBox(height: 5),
                                          if (_isLoadingDaftar)
                                            const Padding(
                                              padding: EdgeInsets.symmetric(vertical: 20),
                                              child: Center(child: CircularProgressIndicator()),
                                            )
                                          else if (_mahasiswaList.isEmpty)
                                            const Padding(
                                              padding: EdgeInsets.symmetric(vertical: 16),
                                              child: Text('Belum ada mahasiswa di kelas ini.',
                                                  style: TextStyle(color: Colors.grey)),
                                            )
                                          else
                                            ..._mahasiswaList.map((mhs) => _buildMahasiswaItem(mhs)),
                                        ],
                                      ),
                                    ),

                                    const SizedBox(height: 10),

                                    // BUTTON REKAP ABSENSI
                                    ElevatedButton(
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xFF007BFF),
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 14),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                        elevation: 0,
                                      ),
                                      onPressed: _mahasiswaList.isEmpty ? null : _openRekapModal,
                                      child: const Text('Lihat Rekap Absensi',
                                          style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                                    ),
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

  // --- REUSABLE WIDGET UNTUK CARD BASE ---
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

  // --- REUSABLE WIDGET UNTUK ITEM LIST MAHASISWA ---
  Widget _buildMahasiswaItem(DaftarHadirItem mhs) {
    final isHadir = mhs.status == AbsensiStatus.hadir;
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Color(0xFFEEEEEE), width: 1)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(child: Text(mhs.nama, style: const TextStyle(fontSize: 14), overflow: TextOverflow.ellipsis)),
          GestureDetector(
            onTap: () => _showStatusPicker(mhs),
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 5, horizontal: 8),
              decoration: BoxDecoration(
                color: isHadir ? const Color(0xFFE6FFED) : const Color(0xFFFFE5E5),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                absensiStatusLabel(mhs.status),
                style: TextStyle(
                  color: isHadir ? const Color(0xFF1A7F37) : const Color(0xFFC62828),
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showStatusPicker(DaftarHadirItem item) {
    showModalBottomSheet(
      context: context,
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                title: const Text('Hadir'),
                onTap: () {
                  Navigator.pop(context);
                  _updateStatus(item, AbsensiStatus.hadir);
                },
              ),
              ListTile(
                title: const Text('Izin'),
                onTap: () {
                  Navigator.pop(context);
                  _updateStatus(item, AbsensiStatus.izin);
                },
              ),
              ListTile(
                title: const Text('Alpha'),
                onTap: () {
                  Navigator.pop(context);
                  _updateStatus(item, AbsensiStatus.alpha);
                },
              ),
            ],
          ),
        );
      },
    );
  }

  // --- REUSABLE WIDGET UNTUK BOX REKAP ---
  Widget _buildRekapBox({required String value, required String label}) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.all(3),
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(color: const Color(0xFFE0F0FF), borderRadius: BorderRadius.circular(10)),
        child: Column(
          children: [
            Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 2),
            Text(label, style: const TextStyle(fontSize: 14, color: Colors.black87)),
          ],
        ),
      ),
    );
  }
}
