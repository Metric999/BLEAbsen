#include <Arduino.h>
#include <BLEDevice.h>
#include <BLEUtils.h>
#include <BLEServer.h>
#include <BLECharacteristic.h>

// ═══════════════════════════════════════════════════════════════
// KONFIGURASI — sesuaikan per ruangan
// ═══════════════════════════════════════════════════════════════
const char* BEACON_NAME  = "BLE-TA10.3";
const char* ROOM_ID      = "TA-10.3";     // ID ruangan di database Laravel

// UUID Service & Characteristic — harus sama di Flutter
#define SERVICE_UUID        "ca3cbe46-f0e0-44e6-88df-c4a74c1cd239"
#define CHARACTERISTIC_UUID "91c1a7d2-5ed8-438c-8a21-2671176cc3ca"
// ═══════════════════════════════════════════════════════════════

BLEServer*         pServer         = nullptr;
BLECharacteristic* pCharacteristic = nullptr;
int                connectedCount  = 0; // Menggunakan integer untuk melacak banyak device

// ── Callback koneksi ──────────────────────────────────────────
class ServerCallbacks : public BLEServerCallbacks {
  void onConnect(BLEServer* pServer) override {
    connectedCount++;
    Serial.print("Device terhubung. Total terhubung: ");
    Serial.println(connectedCount);

    // Tetap advertise agar mahasiswa lain bisa scan dan connect bersamaan
    // (ESP32 mematikan advertising secara default saat 1 device terhubung)
    BLEDevice::startAdvertising();
  }

  void onDisconnect(BLEServer* pServer) override {
    if (connectedCount > 0) connectedCount--;
    Serial.print("Device terputus. Total terhubung: ");
    Serial.println(connectedCount);
    
    // Restart advertising untuk memastikan selalu bisa di-scan
    delay(300); // Beri sedikit waktu sebelum restart advertising
    BLEDevice::startAdvertising();
    Serial.println("Advertising aktif kembali");
  }
};

// ── Callback saat characteristic dibaca atau ditulis Flutter ───────────────
class CharacteristicCallbacks : public BLECharacteristicCallbacks {
  void onRead(BLECharacteristic* pChar) override {
    Serial.print("Flutter membaca room ID: ");
    Serial.println(pChar->getValue().c_str());
  }

  void onWrite(BLECharacteristic* pChar) override {
    std::string value = pChar->getValue();
    if (value.length() > 0) {
      Serial.print("Data Absensi Masuk (Mahasiswa menekan tombol absen): ");
      Serial.println(value.c_str());
    }
  }
};

void setup() {
  Serial.begin(115200);
  Serial.println("Memulai BLE Beacon...");

  // 1. Init BLE
  BLEDevice::init(BEACON_NAME);

  // 2. Buat server
  pServer = BLEDevice::createServer();
  pServer->setCallbacks(new ServerCallbacks());

  // 3. Buat GATT Service
  BLEService* pService = pServer->createService(SERVICE_UUID);

  // 4. Buat Characteristic — READ agar Flutter bisa baca room ID, dan WRITE untuk menerima absensi
  pCharacteristic = pService->createCharacteristic(
    CHARACTERISTIC_UUID,
    BLECharacteristic::PROPERTY_READ | BLECharacteristic::PROPERTY_WRITE
  );
  pCharacteristic->setCallbacks(new CharacteristicCallbacks());

  // 5. Set nilai characteristic = ID ruangan
  //    Flutter akan baca nilai ini setelah konek
  pCharacteristic->setValue(ROOM_ID);

  // 6. Start service
  pService->start();

  // 7. Konfigurasi advertising
  BLEAdvertising* pAdvertising = BLEDevice::getAdvertising();
  pAdvertising->addServiceUUID(SERVICE_UUID); // Flutter filter by UUID ini
  pAdvertising->setScanResponse(true);
  pAdvertising->setMinPreferred(0x06);
  pAdvertising->setMaxPreferred(0x12);

  BLEDevice::startAdvertising();

  Serial.print("📡 Beacon aktif: ");
  Serial.println(BEACON_NAME);
  Serial.print("🏫 Room ID: ");
  Serial.println(ROOM_ID);
  Serial.print("🔑 Service UUID: ");
  Serial.println(SERVICE_UUID);
}

void loop() {
  Serial.print("[");
  Serial.print(millis() / 1000);
  Serial.print("s] Status: ");
  Serial.print(connectedCount);
  Serial.print(" device terhubung");
  Serial.print(" | Beacon: ");
  Serial.println(BEACON_NAME);
  delay(3000);
}