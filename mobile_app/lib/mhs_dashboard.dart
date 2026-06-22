import 'package:flutter/material.dart';
import 'mhs_riwayat.dart';
import 'mhs_profile.dart'; // Mengimpor file profile agar bisa dipanggil

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Absensii Mahasiswa',
      theme: ThemeData(
        fontFamily: 'Arial',
      ),
      home: const AbsensiPage(),
    );
  }
}

class AbsensiPage extends StatefulWidget {
  const AbsensiPage({super.key});

  @override
  State<AbsensiPage> createState() => _AbsensiPageState();
}

class _AbsensiPageState extends State<AbsensiPage> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: const Color(0xFFF5F7FB),

      // SIDEBAR (DRAWER) - SEKARANG HANYA BERISI BERANDA & RIWAYAT ABSENSI
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
                Navigator.pop(context);
              },
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
                  // 1. Tombol Garis Tiga (Kiri)
                  IconButton(
                    icon: const Icon(Icons.menu, color: Colors.white, size: 24),
                    onPressed: () {
                      _scaffoldKey.currentState?.openDrawer();
                    },
                  ),

                  // 2. Judul Header (Tengah)
                  const Expanded(
                    child: Text(
                      'Absensi Mahasiswa',
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
                        // Menggunakan push biasa agar halaman profile tahu jalan "pulang" ke dashboard
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (context) => const ProfilePage()),
                        );
                      } else if (value == 'logout') {
                        print('Aksi Log Out dipicu');
                      }
                    },
                    icon: const Icon(
                      Icons.account_circle,
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

            // CONTAINER (Isi Halaman)
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(15),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // 1. STATUS BLE CARD
                    _buildCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildLabel('Status Koneksi'),
                          const SizedBox(height: 5),
                          _buildStatusTag(
                            text: 'BLE Terdeteksi - GU-805',
                            type: PageStatusType.success,
                          ),
                          const SizedBox(height: 10),
                          const Text(
                            'Sinyal ruangan berhasil ditemukan',
                            style: TextStyle(fontSize: 14),
                          ),
                        ],
                      ),
                    ),

                    // 2. RUANG & JADWAL HASIL DETEKSI CARD
                    _buildCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildLabel('Ruangan'),
                          const Text(
                            'GU-805',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 10),
                          _buildLabel('Kelas'),
                          const Text('IF4C Pagi', style: TextStyle(fontSize: 15)),
                          const SizedBox(height: 10),
                          _buildLabel('Mata Kuliah'),
                          const Text('Pengujian Perangkat Lunak', style: TextStyle(fontSize: 15)),
                          const SizedBox(height: 10),
                          _buildLabel('Dosen'),
                          const Text('Pak Budi', style: TextStyle(fontSize: 15)),
                          const SizedBox(height: 10),
                          _buildLabel('Waktu Kuliah'),
                          const Text('08:00 - 10:00', style: TextStyle(fontSize: 15)),
                        ],
                      ),
                    ),

                    // 3. STATUS VALIDASI WAKTU CARD
                    _buildCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildLabel('Status Absensi'),
                          const SizedBox(height: 5),
                          _buildStatusTag(
                            text: 'Silakan Absen (Waktu Sesuai)',
                            type: PageStatusType.success,
                          ),
                          const SizedBox(height: 10),
                          const Text(
                            'Anda berada di waktu yang tepat untuk melakukan absensi',
                            style: TextStyle(fontSize: 14),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 10),

                    // 4. BUTTON ABSEN
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
                      onPressed: () {
                        print('Proses absensi berhasil dikirim!');
                      },
                      child: const Text(
                        'Absen Sekarang',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
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

  Widget _buildLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 5),
      child: Text(
        text,
        style: const TextStyle(fontSize: 12, color: Colors.grey),
      ),
    );
  }

  Widget _buildStatusTag({required String text, required PageStatusType type}) {
    Color backgroundColor;
    Color textColor;

    switch (type) {
      case PageStatusType.success:
        backgroundColor = const Color(0xFFE6FFED);
        textColor = const Color(0xFF1A7F37);
        break;
      case PageStatusType.warning:
        backgroundColor = const Color(0xFFFFF4E5);
        textColor = const Color(0xFFB26A00);
        break;
      case PageStatusType.danger:
        backgroundColor = const Color(0xFFFFE5E5);
        textColor = const Color(0xFFC62828);
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: textColor,
          fontWeight: FontWeight.bold,
          fontSize: 14,
        ),
      ),
    );
  }
}

enum PageStatusType { success, warning, danger }