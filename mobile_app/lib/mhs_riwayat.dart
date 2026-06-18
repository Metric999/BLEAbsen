import 'package:flutter/material.dart';
import 'mhs_dashboard.dart';
import 'mhs_profile.dart'; // Mengimpor file profile agar bisa dipanggil

// 1. MODEL DATA UNTUK ITEM RIWAYAT
class AbsensiItem {
  final String matkul;
  final String tanggal;
  final RiwayatStatusType status;

  const AbsensiItem({
    required this.matkul,
    required this.tanggal,
    required this.status,
  });
}

enum RiwayatStatusType { hadir, terlambat, tidakHadir }

class RiwayatAbsensiPage extends StatefulWidget {
  const RiwayatAbsensiPage({super.key});

  @override
  State<RiwayatAbsensiPage> createState() => _RiwayatAbsensiPageState();
}

class _RiwayatAbsensiPageState extends State<RiwayatAbsensiPage> {
  // GlobalKey untuk mengontrol buka-tutup Drawer di Halaman Riwayat
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  // 2. DATA DUMMY
  final List<AbsensiItem> _riwayatList = [
    const AbsensiItem(
      matkul: 'Pengujian Perangkat Lunak',
      tanggal: '12 April 2026',
      status: RiwayatStatusType.hadir,
    ),
    const AbsensiItem(
      matkul: 'Basis Data',
      tanggal: '13 April 2026',
      status: RiwayatStatusType.terlambat,
    ),
    const AbsensiItem(
      matkul: 'Jaringan Komputer',
      tanggal: '14 April 2026',
      status: RiwayatStatusType.tidakHadir,
    ),
    const AbsensiItem(
      matkul: 'Pemrograman Web',
      tanggal: '15 April 2026',
      status: RiwayatStatusType.hadir,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey, // Pasangkan key ke Scaffold agar bisa membuka Drawer
      backgroundColor: const Color(0xFFF5F7FB),

      // SIDEBAR (DRAWER) - BERSIH DARI MENU PROFILE, HANYA BERANDA & RIWAYAT
      drawer: Drawer(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const DrawerHeader(
              decoration: BoxDecoration(
                color: Color(0xFF007BFF),
              ),
              child: Align(
                alignment: Alignment.bottomLeft,
                child: Text(
                  'Menu Navigasi',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
            ListTile(
              leading: const Icon(Icons.home, color: Color(0xFF007BFF)),
              title: const Text('Beranda'),
              onTap: () {
                Navigator.pop(context); // Tutup sidebar
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (context) => const AbsensiPage()),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.history, color: Color(0xFF007BFF)),
              title: const Text('Riwayat Absensi'),
              onTap: () {
                Navigator.pop(context); // Cukup tutup sidebar karena sudah di halaman riwayat
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
                  // 1. Tombol Garis Tiga (Kiri)
                  IconButton(
                    icon: const Icon(Icons.menu, color: Colors.white, size: 24),
                    onPressed: () {
                      _scaffoldKey.currentState?.openDrawer(); // Membuka sidebar
                    },
                  ),

                  // 2. Judul Header (Tengah)
                  const Expanded(
                    child: Text(
                      'Riwayat Absensi',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),

                  // 3. Logo Profile dengan Dropdown List (Kanan)
                  PopupMenuButton<String>(
                    onSelected: (value) {
                      if (value == 'profile') {
                        // Membuka halaman mhs_profile.dart menggunakan push biasa
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (context) => const ProfilePage()),
                        );
                      } else if (value == 'logout') {
                        print('Aksi Log Out dipicu dari halaman riwayat');
                        // Tambahkan fungsi logout Anda di sini
                      }
                    },
                    icon: const Icon(
                      Icons.account_circle, // Ikon avatar/profile
                      color: Colors.white,
                      size: 28,
                    ),
                    itemBuilder: (BuildContext context) => [
                      const PopupMenuItem<String>(
                        value: 'profile',
                        child: Row(
                          children: [
                            Icon(Icons.person, color: Colors.black87, size: 20),
                            SizedBox(width: 10),
                            Text('Profile'),
                          ],
                        ),
                      ),
                      const PopupMenuItem<String>(
                        value: 'logout',
                        child: Row(
                          children: [
                            Icon(Icons.logout, color: Colors.red, size: 20),
                            SizedBox(width: 10),
                            Text('Log Out', style: TextStyle(color: Colors.red)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // CONTAINER UTAMA
            Expanded(
              child: ListView(
                padding: const EdgeInsets.all(15),
                children: [
                  // RINGKASAN CARD (.summary)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(15),
                    margin: const EdgeInsets.only(bottom: 15),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE0F0FF),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      children: const [
                        Text(
                          '12',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: Colors.black,
                          ),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Total Kehadiran Bulan Ini',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.black87,
                          ),
                        ),
                      ],
                    ),
                  ),

                  // DAFTAR RIWAYAT
                  ..._riwayatList.map((item) => _buildCardRiwayat(item)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCardRiwayat(AbsensiItem item) {
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            item.matkul,
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 5),
          Text(
            item.tanggal,
            style: const TextStyle(fontSize: 12, color: Colors.grey),
          ),
          const SizedBox(height: 10),
          _buildStatusBadge(item.status),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(RiwayatStatusType type) {
    String text;
    Color backgroundColor;
    Color textColor;

    switch (type) {
      case RiwayatStatusType.hadir:
        text = 'Hadir';
        backgroundColor = const Color(0xFFE6FFED);
        textColor = const Color(0xFF1A7F37);
        break;
      case RiwayatStatusType.terlambat:
        text = 'Terlambat';
        backgroundColor = const Color(0xFFFFF4E5);
        textColor = const Color(0xFFB26A00);
        break;
      case RiwayatStatusType.tidakHadir:
        text = 'Tidak Hadir';
        backgroundColor = const Color(0xFFFFE5E5);
        textColor = const Color(0xFFC62828);
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 10),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: textColor,
          fontWeight: FontWeight.bold,
          fontSize: 12,
        ),
      ),
    );
  }
}