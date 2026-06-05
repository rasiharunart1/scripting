/**
 * config.h — Konfigurasi ESP8266 Listener v2.0
 *
 * Edit file ini untuk menyesuaikan dengan environment.
 * Tidak perlu edit esp8266_listener.ino.
 */
#pragma once

// ─────────────────────────────────────────────
//  WiFi
// ─────────────────────────────────────────────
#define WIFI_SSID  "MEGADATA"
#define WIFI_PASS  "MEGADATA"

// ─────────────────────────────────────────────
//  Laravel API
//  Production  : "https://nhmedia.pro"
//  Development : "http://192.168.1.xx"  (ganti IP laptop)
// ─────────────────────────────────────────────
#define LARAVEL_BASE_URL  "https://nhmedia.pro"
#define DEVICE_CODE       "MDUnit-2BC1C478"

// ─────────────────────────────────────────────
//  Endpoint yang digunakan
//  (auto-dibangun runtime, tidak perlu diubah)
//
//  Threshold : GET {LARAVEL_BASE_URL}/api/sensor-data/range/{DEVICE_CODE}
//  Sensor    : GET {LARAVEL_BASE_URL}/api/sensor-data/getData/{DEVICE_CODE}
// ─────────────────────────────────────────────

// ─────────────────────────────────────────────
//  Pin Mapping (NodeMCU / ESP8266)
// ─────────────────────────────────────────────
//  D5 → GPIO14 → Buzzer / Relay Alert (Active-LOW)
//  D4 → GPIO2  → LED Builtin          (Active-LOW)
//  D3 → GPIO0  → Button FLASH         (Active-LOW, ada internal pullup)
#define BUZZER_PIN   14
#define LED_PIN       2
#define BTN_PIN       0

// ─────────────────────────────────────────────
//  Timing (ms)
// ─────────────────────────────────────────────
#define POLL_INTERVAL_MS    5000   // Interval fetch sensor data
#define TH_REFRESH_MS      30000   // Interval refresh threshold dari Laravel
#define SENSOR_TIMEOUT_MS  20000   // Anggap offline jika fetch gagal > N ms
#define BLINK_FAST_MS        200   // Kecepatan kedip saat ALERT
#define BLINK_SLOW_MS       1000   // Kecepatan kedip saat NORMAL
#define HTTP_TIMEOUT_MS     8000   // Timeout per HTTP request
