import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

// Sesuaikan dengan nama file utama di project Anda
import 'package:ble_absen/main.dart';

void main() {
  testWidgets('Memastikan halaman login memiliki input Username dan Password', (WidgetTester tester) async {
    // 1. Render halaman LoginPage ke dalam environment testing
    await tester.pumpWidget(const MaterialApp(
      home: LoginPage(),
    ));

    // 2. Pastikan tulisan judul muncul
    expect(find.text('Sistem Absensi BLE'), findsWidgets);

    // 3. Pastikan input field untuk Username dan Password tersedia
    expect(find.text('Username'), findsOneWidget);
    expect(find.text('Password'), findsOneWidget);
    
    // 4. Pastikan tombol Login ada
    expect(find.text('Login'), findsOneWidget);
  });
}
