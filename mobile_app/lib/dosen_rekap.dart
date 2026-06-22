import 'package:flutter/material.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Live Absensi Kellas',
      theme: ThemeData(
        fontFamily: 'Arial',
      ),
      home: const LiveAbsensiPage(),
    );
  }
}

// 1. MODEL DATA UNTUK DAFTAR MAHASISWA
class MahasiswaItem {
  final String nama;
  final bool isHadir;

  const MahasiswaItem({required this.nama, required this.isHadir});
}

class LiveAbsensiPage extends StatefulWidget {
  const LiveAbsensiPage({super.key});

  @override
  State<LiveAbsensiPage> createState() => _LiveAbsensiPageState();
}

class _LiveAbsensiPageState extends State<LiveAbsensiPage> {
  // State untuk dropdown kelas
  String _selectedKelas = 'IF4C Pagi';

  // Data Dummy Mahasiswa (Sama dengan HTML Anda)
  final List<MahasiswaItem> _mahasiswaList = [
    const MahasiswaItem(nama: 'Andi Pratama', isHadir: true),
    const MahasiswaItem(nama: 'Siti Rahma', isHadir: true),
    const MahasiswaItem(nama: 'Budi Santoso', isHadir: false),
    const MahasiswaItem(nama: 'Rudi Hartono', isHadir: false),
  ];

  // FUNGSI UNTUK MENAMPILKAN MODAL REKAP ABSENSI
  void _openRekapModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(20),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(
              top: Radius.circular(15),
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header Modal + Tombol Close
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Rekap Absensi',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: const Text(
                      '✖',
                      style: TextStyle(fontSize: 18, color: Colors.grey),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // BOXES REKAP (.boxes)
              Row(
                children: [
                  // Box Hadir
                  _buildRekapBox(value: '2', label: 'Hadir'),
                  // Box Terlambat
                  _buildRekapBox(value: '0', label: 'Terlambat'),
                  // Box Belum
                  _buildRekapBox(value: '2', label: 'Belum'),
                ],
              ),
              const SizedBox(height: 10),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FB), // #f5f7fb
      body: SafeArea(
        child: Column(
          children: [
            // HEADER
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(15),
              color: const Color(0xFF007BFF), // #007bff
              child: const Text(
                'Live Monitoring Absensi',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),

            // CONTAINER UTAMA
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(15),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [

                    // CARD 1: PILIH KELAS
                    _buildCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Pilih Kelas',
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 10),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFDDDDDD)),
                              color: Colors.white,
                            ),
                            child: DropdownButtonHideUnderline(
                              child: DropdownButton<String>(
                                value: _selectedKelas,
                                isExpanded: true,
                                items: const [
                                  DropdownMenuItem(value: 'IF4C Pagi', child: Text('IF4C Pagi')),
                                  DropdownMenuItem(value: 'IF2C Pagi', child: Text('IF2C Pagi')),
                                  DropdownMenuItem(value: 'IF3A Sore', child: Text('IF3A Sore')),
                                ],
                                onChanged: (value) {
                                  setState(() {
                                    if (value != null) _selectedKelas = value;
                                  });
                                },
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // CARD 2: LIST MAHASISWA
                    _buildCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Daftar Mahasiswa',
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 5),

                          // Mengulang Item List Mahasiswa
                          ..._mahasiswaList.map((mhs) => _buildMahasiswaItem(mhs)),
                        ],
                      ),
                    ),

                    const SizedBox(height: 10),

                    // BUTTON REKAP ABSENSI (.summary-btn)
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF007BFF),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                        elevation: 0,
                      ),
                      onPressed: _openRekapModal,
                      child: const Text(
                        'Lihat Rekap Absensi',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                      ),
                    ),

                  ],
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
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: child,
    );
  }

  // --- REUSABLE WIDGET UNTUK ITEM LIST MAHASISWA (.item) ---
  Widget _buildMahasiswaItem(MahasiswaItem mhs) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: const BoxDecoration(
        border: Border(
          bottom: BorderSide(color: Color(0xFFEEEEEE), width: 1),
        ),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween, // justify-content: space-between
        children: [
          Text(
            mhs.nama,
            style: const TextStyle(fontSize: 14),
          ),
          Container(
            padding: const EdgeInsets.symmetric(vertical: 5, horizontal: 8),
            decoration: BoxDecoration(
              color: mhs.isHadir ? const Color(0xFFE6FFED) : const Color(0xFFFFE5E5),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              mhs.isHadir ? 'Hadir' : 'Belum',
              style: TextStyle(
                color: mhs.isHadir ? const Color(0xFF1A7F37) : const Color(0xFFC62828),
                fontWeight: FontWeight.bold,
                fontSize: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // --- REUSABLE WIDGET UNTUK BOX REKAP (flex: 1) ---
  Widget _buildRekapBox({required String value, required String label}) {
    return Expanded( // Berfungsi sebagai 'flex: 1' agar membagi ukuran Row sama rata
      child: Container(
        margin: const EdgeInsets.all(3),
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: const Color(0xFFE0F0FF), // #e0f0ff
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: const TextStyle(fontSize: 14, color: Colors.black87),
            ),
          ],
        ),
      ),
    );
  }
}