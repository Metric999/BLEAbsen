import 'package:flutter/material.dart';

import 'main.dart';
import 'mhs_dashboard.dart';
import 'mhs_riwayat.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  Map<String, dynamic>? _user;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    // Coba refresh dari server, fallback ke cache lokal jika gagal
    final fresh = await AuthService.me();
    final cached = await StorageService.getUser();

    setState(() {
      _user = fresh ?? cached;
      _isLoading = false;
    });
  }

  // FUNGSI UNTUK MENAMPILKAN MODAL GANTI PASSWORD (terhubung ke API)
  void _openChangePasswordModal() {
    final lamaCtrl = TextEditingController();
    final baruCtrl = TextEditingController();
    final confirmCtrl = TextEditingController();
    bool isSubmitting = false;
    String? errorMsg;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            Future<void> submit() async {
              if (baruCtrl.text.length < 6) {
                setModalState(() => errorMsg = 'Password baru minimal 6 karakter.');
                return;
              }
              if (baruCtrl.text != confirmCtrl.text) {
                setModalState(() => errorMsg = 'Konfirmasi password tidak cocok.');
                return;
              }

              setModalState(() {
                isSubmitting = true;
                errorMsg = null;
              });

              final result = await AuthService.changePassword(
                passwordLama: lamaCtrl.text,
                passwordBaru: baruCtrl.text,
                passwordBaruConfirm: confirmCtrl.text,
              );

              if (!context.mounted) return;

              if (result.success) {
                Navigator.pop(context);
                ScaffoldMessenger.of(context)
                    .showSnackBar(const SnackBar(content: Text('Password berhasil diubah!')));
              } else {
                setModalState(() {
                  isSubmitting = false;
                  errorMsg = result.message;
                });
              }
            }

            return Padding(
              padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(15)),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Ganti Password',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        GestureDetector(
                          onTap: () => Navigator.pop(context),
                          child: const Text('✖', style: TextStyle(fontSize: 18, color: Colors.grey)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    if (errorMsg != null) ...[
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(10),
                        margin: const EdgeInsets.only(bottom: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFE5E5),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(errorMsg!, style: const TextStyle(color: Color(0xFFC62828), fontSize: 13)),
                      ),
                    ],
                    _buildTextField(controller: lamaCtrl, hint: 'Password lama'),
                    const SizedBox(height: 10),
                    _buildTextField(controller: baruCtrl, hint: 'Password baru'),
                    const SizedBox(height: 10),
                    _buildTextField(controller: confirmCtrl, hint: 'Konfirmasi password'),
                    const SizedBox(height: 15),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF007BFF),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        elevation: 0,
                      ),
                      onPressed: isSubmitting ? null : submit,
                      child: isSubmitting
                          ? const SizedBox(
                              height: 18,
                              width: 18,
                              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                            )
                          : const Text('Simpan', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(height: 10),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final nama = _user?['nama'] ?? '-';
    final kelas = _user?['kelas'] ?? '-';
    final nim = _user?['nim'] ?? _user?['nidn'] ?? '-';

    return Scaffold(
      key: _scaffoldKey,

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
                Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (context) => const AbsensiPage()),
                  (route) => false,
                );
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
            ListTile(
              leading: const Icon(Icons.person, color: Color(0xFF007BFF)),
              title: const Text('Profile'),
              onTap: () => Navigator.pop(context),
            ),
          ],
        ),
      ),

      backgroundColor: const Color(0xFFF5F7FB),
      body: SafeArea(
        child: Column(
          children: [
            // HEADER DENGAN TOMBOL KEMBALI
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 15),
              color: const Color(0xFF007BFF),
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.arrow_back, color: Colors.white, size: 24),
                    onPressed: () => Navigator.pop(context),
                  ),
                  const Expanded(
                    child: Text('Profile',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(width: 48),
                ],
              ),
            ),

            // CONTAINER UTAMA
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : SingleChildScrollView(
                      padding: const EdgeInsets.all(15),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          _buildProfileCard(label: 'NIM', value: nim.toString()),
                          _buildProfileCard(label: 'Nama', value: nama.toString()),
                          _buildProfileCard(label: 'Kelas', value: kelas.toString()),
                          const SizedBox(height: 10),
                          ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF007BFF),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              elevation: 0,
                            ),
                            onPressed: _openChangePasswordModal,
                            child: const Text('Ganti Password',
                                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
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

  Widget _buildProfileCard({required String label, required String value}) {
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
          Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          const SizedBox(height: 5),
          Text(value, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Widget _buildTextField({required TextEditingController controller, required String hint}) {
    return TextField(
      controller: controller,
      obscureText: true,
      decoration: InputDecoration(
        hintText: hint,
        contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 15),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFFDDDDDD)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFFDDDDDD)),
        ),
      ),
    );
  }
}
