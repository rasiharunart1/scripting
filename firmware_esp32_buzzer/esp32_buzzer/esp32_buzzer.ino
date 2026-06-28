/***************************************************
 *  esp32_buzzer.ino  — v1.0
 *
 *  ESP32 STANDALONE SENSOR LISTENER & BUZZER ALERT
 *  ──────────────────────────────────────────────────
 *  Clone dari esp8266_listener v2.0, diadaptasi untuk ESP32.
 *  Fungsi: hanya menyalakan BUZZER jika ada sensor OOR.
 *
 *  Tidak butuh MQTT. Bekerja murni via HTTP polling
 *  ke Laravel API (mdpower.io / scripting local).
 *
 *  ALUR KERJA:
 *  1. Boot → connectWiFi()
 *  2. Fetch threshold dari GET /api/sensor-data/range/{code}
 *  3. Loop tiap POLL_INTERVAL_MS:
 *       a. GET /api/sensor-data/getData/{code} → baca nilai sensor
 *       b. Bandingkan semua nilai vs threshold
 *       c. Aktifkan buzzer jika ada yang OOR
 *
 *  SENSOR KEYS yang dipantau (sesuai SensorRangeController):
 *   battery_a, battery_b, battery_c, battery_d
 *   pln_volt, pln_current, pln_power
 *   temperature_1, temperature_2
 *   server_voltage
 *
 *  Hardware (ESP32):
 *  GPIO18 → Buzzer/Relay Alert  (Active-LOW)
 *  GPIO2  → LED builtin         (Active-LOW)
 *  GPIO0  → Button BOOT         (silence / restart)
 *
 ***************************************************/

#include <ArduinoJson.h>
#include <HTTPClient.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>

// ════════════════════════════════════════════════════════════
//  KONFIGURASI — edit config.h, jangan ubah file ini
// ════════════════════════════════════════════════════════════
#include "config.h"

// ════════════════════════════════════════════════════════════
//  DEFINISI THRESHOLD — 10 SENSOR KEY
// ════════════════════════════════════════════════════════════
#define NUM_SENSORS 10

struct Threshold {
  const char* key;    // sensor_key dari Laravel
  float lo, hi;       // min / max
  bool  valid;        // true jika sudah berhasil di-fetch
};

// Default values disamakan dengan SensorRangeController::DEFAULTS
Threshold th[NUM_SENSORS] = {
  {"battery_a",      10.5f, 14.4f, false},
  {"battery_b",      10.5f, 14.4f, false},
  {"battery_c",      10.5f, 14.4f, false},
  {"battery_d",      10.5f, 14.4f, false},
  {"pln_volt",      180.0f,240.0f, false},
  {"pln_current",     0.0f, 32.0f, false},
  {"pln_power",       0.0f,100.0f, false},
  {"temperature_1",   0.0f, 60.0f, false},
  {"temperature_2",   0.0f, 60.0f, false},
  {"server_voltage", 170.0f,260.0f,false},
};

// ════════════════════════════════════════════════════════════
//  NILAI SENSOR TERKINI
// ════════════════════════════════════════════════════════════
struct SensorValues {
  float battery_a     = NAN;
  float battery_b     = NAN;
  float battery_c     = NAN;
  float battery_d     = NAN;
  float pln_volt      = NAN;
  float pln_current   = NAN;
  float pln_power     = NAN;
  float temperature_1 = NAN;
  float temperature_2 = NAN;
  float server_voltage= NAN;
  bool  valid         = false;
} sv;

// ════════════════════════════════════════════════════════════
//  ALERT STATE
// ════════════════════════════════════════════════════════════
bool alertActive   = false;
bool silenced      = false;   // true setelah button ditekan
bool ledState      = false;
bool buzzerState   = false;

// Sensor mana yang OOR (bitmask)
uint16_t oorMask   = 0;

unsigned long lastPollMs     = 0;
unsigned long lastThMs       = 0;
unsigned long lastDataOkMs   = 0;  // last successful data fetch
unsigned long tBlink         = 0;
unsigned long tBuzzerBlink   = 0;
unsigned long tBtn           = 0;

// Simpan pesan alert untuk serial log
String alertMsg = "";

// ════════════════════════════════════════════════════════════
//  HTTP CLIENT (ESP32)
//  Pakai WiFiClientSecure untuk HTTPS, WiFiClient untuk HTTP
// ════════════════════════════════════════════════════════════
WiFiClientSecure secureClient;
WiFiClient       plainClient;

// ── Helper: buat HTTPClient + begin (auto deteksi http/https) ──
bool httpBegin(HTTPClient& http, const String& url) {
  http.setTimeout(HTTP_TIMEOUT_MS);
  if (url.startsWith("https://")) {
    secureClient.setInsecure();  // skip certificate verify
    return http.begin(secureClient, url);
  } else {
    return http.begin(plainClient, url);
  }
}

// ════════════════════════════════════════════════════════════
//  BUZZER HELPERS
// ════════════════════════════════════════════════════════════
void buzzerOn()  { digitalWrite(BUZZER_PIN, HIGH);  buzzerState = true;  }
void buzzerOff() { digitalWrite(BUZZER_PIN, LOW); buzzerState = false; }

// Kedip non-blocking saat alert aktif
void handleBuzzerBlink() {
  if (!alertActive || silenced) {
    buzzerOff();
    return;
  }
  unsigned long now = millis();
  if (now - tBuzzerBlink >= (unsigned long)BLINK_FAST_MS) {
    tBuzzerBlink = now;
    buzzerState ? buzzerOff() : buzzerOn();
  }
}

// ════════════════════════════════════════════════════════════
//  FETCH THRESHOLD
//  GET /api/sensor-data/range/{device_code}
//  Response: { "battery_a": [10.5, 14.4], "pln_volt": [180, 240], ... }
// ════════════════════════════════════════════════════════════
void fetchThreshold() {
  if (WiFi.status() != WL_CONNECTED) return;

  String url = String(LARAVEL_BASE_URL)
               + "/api/sensor-data/range/"
               + DEVICE_CODE;
  Serial.printf("[TH] Fetch: %s\n", url.c_str());

  HTTPClient http;
  if (!httpBegin(http, url)) {
    Serial.println("[TH] httpBegin failed");
    return;
  }

  int code = http.GET();
  if (code != 200) {
    Serial.printf("[TH] HTTP %d", code);
    if (code < 0) Serial.printf(" (%s)", http.errorToString(code).c_str());
    Serial.println();
    http.end();
    return;
  }

  String body = http.getString();
  http.end();

  // Buffer 1536 bytes cukup untuk 10 sensor range
  DynamicJsonDocument doc(1536);
  DeserializationError err = deserializeJson(doc, body);
  if (err) {
    Serial.printf("[TH] JSON error: %s\n", err.c_str());
    return;
  }

  // Parse setiap key
  int loaded = 0;
  for (int i = 0; i < NUM_SENSORS; i++) {
    const char* k = th[i].key;
    if (doc.containsKey(k) && doc[k].is<JsonArray>()) {
      JsonArray arr = doc[k].as<JsonArray>();
      if (arr.size() >= 2) {
        th[i].lo    = arr[0].as<float>();
        th[i].hi    = arr[1].as<float>();
        th[i].valid = true;
        loaded++;
        Serial.printf("[TH]  %-16s [%.2f - %.2f]\n", k, th[i].lo, th[i].hi);
      }
    }
  }
  Serial.printf("[TH] %d/%d threshold dimuat.\n", loaded, NUM_SENSORS);
}

// ════════════════════════════════════════════════════════════
//  FETCH SENSOR DATA
//  GET /api/sensor-data/getData/{device_code}
//  Response: { "data": { "battery_a": 12.3, "pln_volt": 220.0, ... } }
// ════════════════════════════════════════════════════════════

// Helper: baca float dari JsonObject, return NAN jika null/tidak ada
float jf(JsonObject obj, const char* key) {
  if (!obj.containsKey(key) || obj[key].isNull()) return NAN;
  return obj[key].as<float>();
}

bool fetchSensorData() {
  if (WiFi.status() != WL_CONNECTED) return false;

  String url = String(LARAVEL_BASE_URL)
               + "/api/sensor-data/getData/"
               + DEVICE_CODE;

  HTTPClient http;
  if (!httpBegin(http, url)) return false;

  int code = http.GET();
  if (code != 200) {
    Serial.printf("[DATA] HTTP %d", code);
    if (code < 0) Serial.printf(" (%s)", http.errorToString(code).c_str());
    Serial.println();
    http.end();
    return false;
  }

  String body = http.getString();
  http.end();

  // Buffer 2048 cukup untuk response data sensor
  DynamicJsonDocument doc(2048);
  DeserializationError err = deserializeJson(doc, body);
  if (err) {
    Serial.printf("[DATA] JSON error: %s\n", err.c_str());
    return false;
  }

  // SensorController::getSensorData returns { "data": { ...fields... } }
  // atau langsung { ...fields... } jika response berbeda — kita coba keduanya
  JsonObject obj;
  if (doc.containsKey("data") && doc["data"].is<JsonObject>()) {
    obj = doc["data"].as<JsonObject>();
  } else {
    obj = doc.as<JsonObject>();
  }

  if (obj.isNull()) {
    Serial.println("[DATA] Gagal ambil object sensor.");
    return false;
  }

  // Map field ke SensorValues
  sv.battery_a      = jf(obj, "battery_a");
  sv.battery_b      = jf(obj, "battery_b");
  sv.battery_c      = jf(obj, "battery_c");
  sv.battery_d      = jf(obj, "battery_d");
  sv.pln_volt       = jf(obj, "pln_volt");
  sv.pln_current    = jf(obj, "pln_current");
  sv.pln_power      = jf(obj, "pln_power");
  sv.temperature_1  = jf(obj, "temperature_1");
  sv.temperature_2  = jf(obj, "temperature_2");
  sv.server_voltage = jf(obj, "server_voltage");
  sv.valid          = true;

  Serial.printf("[DATA] BatA=%.2f BatB=%.2f BatC=%.2f BatD=%.2f\n",
                sv.battery_a, sv.battery_b, sv.battery_c, sv.battery_d);
  Serial.printf("[DATA] Volt=%.1fV Curr=%.2fA Pow=%.0fW SrvV=%.1fV\n",
                sv.pln_volt, sv.pln_current, sv.pln_power, sv.server_voltage);
  Serial.printf("[DATA] Temp1=%.1f C Temp2=%.1f C\n",
                sv.temperature_1, sv.temperature_2);

  lastDataOkMs = millis();
  return true;
}

// ════════════════════════════════════════════════════════════
//  CEK ALERT — bandingkan semua sensor vs threshold
// ════════════════════════════════════════════════════════════

// Helper: ambil nilai sensor berdasarkan index (sesuai urutan th[])
float svByIndex(int i) {
  switch (i) {
    case 0: return sv.battery_a;
    case 1: return sv.battery_b;
    case 2: return sv.battery_c;
    case 3: return sv.battery_d;
    case 4: return sv.pln_volt;
    case 5: return sv.pln_current;
    case 6: return sv.pln_power;
    case 7: return sv.temperature_1;
    case 8: return sv.temperature_2;
    case 9: return sv.server_voltage;
    default: return NAN;
  }
}

void checkAlert() {
  if (!sv.valid) return;

  bool newAlert = false;
  uint16_t newMask = 0;
  String reason = "";

  for (int i = 0; i < NUM_SENSORS; i++) {
    if (!th[i].valid) continue;     // threshold belum dimuat → skip
    float val = svByIndex(i);
    if (isnan(val)) continue;       // sensor null dari DB → skip

    if (val < th[i].lo || val > th[i].hi) {
      newAlert = true;
      newMask |= (1 << i);
      // Format: "battery_a=12.30(lo:10.50,hi:14.40) "
      char buf[64];
      snprintf(buf, sizeof(buf), "%s=%.2f[%.2f-%.2f] ", th[i].key, val, th[i].lo, th[i].hi);
      reason += buf;
    }
  }

  // Ubah state hanya jika berbeda (cegah Serial flood)
  if (newAlert && (!alertActive || oorMask != newMask)) {
    alertActive  = true;
    silenced     = false;   // reset silence jika kondisi berubah
    oorMask      = newMask;
    alertMsg     = reason;
    tBuzzerBlink = 0;
    Serial.println("[ALERT] ⚠ ALERT AKTIF!");
    Serial.printf("[ALERT] %s\n", reason.c_str());
  } else if (!newAlert && alertActive) {
    alertActive = false;
    silenced    = false;
    oorMask     = 0;
    alertMsg    = "";
    buzzerOff();
    Serial.println("[ALERT] ✓ Semua sensor normal. Alert OFF.");
  }
}

// ════════════════════════════════════════════════════════════
//  WIFI
// ════════════════════════════════════════════════════════════
void connectWiFi() {
  Serial.printf("[WiFi] Menghubungkan ke %s ", WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  for (int i = 0; i < 40 && WiFi.status() != WL_CONNECTED; i++) {
    delay(500);
    Serial.print('.');
    // Kedip LED saat connecting
    ledState = !ledState;
    digitalWrite(LED_PIN, ledState ? LOW : HIGH);
  }
  Serial.println();
  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("[WiFi] IP: %s  RSSI: %d dBm\n",
                  WiFi.localIP().toString().c_str(), WiFi.RSSI());
    digitalWrite(LED_PIN, HIGH); // LED OFF setelah connect
  } else {
    Serial.println("[WiFi] Gagal konek!");
  }
}

// ════════════════════════════════════════════════════════════
//  BUTTON HANDLER
//  Tekan pendek (>50ms) → silence buzzer sementara
//  Tahan >5s            → restart ESP
// ════════════════════════════════════════════════════════════
void checkBtn() {
  if (digitalRead(BTN_PIN) == LOW) {
    if (!tBtn) tBtn = millis();
  } else {
    if (tBtn > 0) {
      unsigned long held = millis() - tBtn;
      tBtn = 0;
      if (held >= 5000) {
        Serial.println("[BTN] Restart...");
        ESP.restart();
      } else if (held >= 50) {
        if (alertActive && !silenced) {
          silenced = true;
          buzzerOff();
          Serial.println("[BTN] Buzzer di-silence. Alarm akan aktif lagi jika sensor masih OOR.");
        } else if (alertActive && silenced) {
          // Tekan lagi → batalkan silence
          silenced = false;
          tBuzzerBlink = 0;
          Serial.println("[BTN] Silence dibatalkan. Buzzer ON.");
        }
      }
    }
  }
}

// ════════════════════════════════════════════════════════════
//  PRINT STATUS RINGKAS KE SERIAL
// ════════════════════════════════════════════════════════════
void printStatus() {
  Serial.println(F("┌─────────────────────────────────────────┐"));
  Serial.println(F("│          STATUS SENSOR TERKINI          │"));
  Serial.println(F("├─────────────────────────────────────────┤"));
  for (int i = 0; i < NUM_SENSORS; i++) {
    float val = svByIndex(i);
    bool  oor = false;
    if (!isnan(val) && th[i].valid) {
      oor = (val < th[i].lo || val > th[i].hi);
    }
    char line[64];
    if (isnan(val)) {
      snprintf(line, sizeof(line), "│  %-16s = NULL           │", th[i].key);
    } else {
      snprintf(line, sizeof(line), "│  %-16s = %8.2f  %s  │",
               th[i].key, val, oor ? "OOR!" : " OK ");
    }
    Serial.println(line);
  }
  Serial.println(F("├─────────────────────────────────────────┤"));
  Serial.printf( "│  Alert: %-6s  Silenced: %-6s         │\n",
                 alertActive ? "AKTIF" : "OFF",
                 silenced    ? "YA"    : "TIDAK");
  Serial.println(F("└─────────────────────────────────────────┘"));
}

// ════════════════════════════════════════════════════════════
//  SETUP
// ════════════════════════════════════════════════════════════
void setup() {
  Serial.begin(115200);
  delay(300);

  Serial.println(F("\n╔══════════════════════════════════════════╗"));
  Serial.println(F("║    ESP32 Buzzer Listener v1.0           ║"));
  Serial.println(F("║    HTTP Polling → Laravel API           ║"));
  Serial.println(F("╠══════════════════════════════════════════╣"));
  Serial.printf( "║  API   : %-32s║\n", LARAVEL_BASE_URL);
  Serial.printf( "║  Device: %-32s║\n", DEVICE_CODE);
  Serial.println(F("╚══════════════════════════════════════════╝\n"));

  // GPIO
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_PIN,    OUTPUT);
  pinMode(BTN_PIN,    INPUT_PULLUP);
  buzzerOff();
  digitalWrite(LED_PIN, HIGH);  // LED OFF (Active-LOW)

  // WiFi
  connectWiFi();

  // Fetch threshold sebelum polling pertama
  if (WiFi.status() == WL_CONNECTED) {
    fetchThreshold();
    // Fetch data awal
    if (fetchSensorData()) {
      checkAlert();
      printStatus();
    }
  }

  Serial.println(F("[Ready] Polling sensor data setiap 5 detik..."));
}

// ════════════════════════════════════════════════════════════
//  LOOP
// ════════════════════════════════════════════════════════════
void loop() {
  unsigned long now = millis();

  // ── Cek Button ──
  checkBtn();

  // ── WiFi reconnect ──
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println(F("[WiFi] Koneksi hilang, reconnecting..."));
    WiFi.reconnect();
    delay(5000);
    return;
  }

  // ── Refresh Threshold tiap TH_REFRESH_MS ──
  if (now - lastThMs >= (unsigned long)TH_REFRESH_MS) {
    lastThMs = now;
    fetchThreshold();
  }

  // ── Poll Sensor Data tiap POLL_INTERVAL_MS ──
  if (now - lastPollMs >= (unsigned long)POLL_INTERVAL_MS) {
    lastPollMs = now;
    Serial.printf("[POLL] Fetch data ... (uptime %lus)\n", now / 1000);

    bool ok = fetchSensorData();
    if (ok) {
      checkAlert();
      printStatus();
    } else {
      // Deteksi timeout: jika sudah lama tidak berhasil fetch
      if (lastDataOkMs > 0 && (now - lastDataOkMs) > (unsigned long)SENSOR_TIMEOUT_MS) {
        if (!alertActive) {
          alertActive = true;
          silenced    = false;
          alertMsg    = "API_TIMEOUT: Data tidak tersedia";
          Serial.println(F("[ALERT] ⚠ API tidak merespons > timeout!"));
        }
      }
    }
  }

  // ── Buzzer Blink (non-blocking) ──
  handleBuzzerBlink();

  // ── LED Heartbeat ──
  // Cepat (BLINK_FAST_MS) saat alert, lambat (BLINK_SLOW_MS) saat normal
  unsigned long blinkMs = alertActive ? BLINK_FAST_MS : BLINK_SLOW_MS;
  if (now - tBlink >= blinkMs) {
    tBlink   = now;
    ledState = !ledState;
    if (!alertActive || silenced) {
      digitalWrite(LED_PIN, ledState ? LOW : HIGH);
    }
  }

  // LED override: ikut buzzer blink saat alert aktif
  if (alertActive && !silenced) {
    digitalWrite(LED_PIN, buzzerState ? LOW : HIGH);
  }
}
