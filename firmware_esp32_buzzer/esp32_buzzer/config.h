/**
 * config.h — Konfigurasi ESP32 Buzzer Listener v1.0
 *
 * Edit file ini untuk menyesuaikan dengan environment.
 * Tidak perlu edit esp32_buzzer.ino.
 */
#pragma once

// ─────────────────────────────────────────────
//  WiFi
// ─────────────────────────────────────────────
#define WIFI_SSID  "harun"
#define WIFI_PASS  "harun3211"

// ─────────────────────────────────────────────
//  Laravel API
//  Production  : "https://mdpower.io"
//  Development : "http://192.168.1.xx"  (ganti IP laptop)
// ─────────────────────────────────────────────
#define LARAVEL_BASE_URL  "https://nhmedia.pro"
#define DEVICE_CODE       "MDUnit-250AFC1D"

// ─────────────────────────────────────────────
//  Endpoint yang digunakan
//  (auto-dibangun runtime, tidak perlu diubah)
//
//  Threshold : GET {LARAVEL_BASE_URL}/api/sensor-data/range/{DEVICE_CODE}
//  Sensor    : GET {LARAVEL_BASE_URL}/api/sensor-data/getData/{DEVICE_CODE}
// ─────────────────────────────────────────────

// ─────────────────────────────────────────────
//  Pin Mapping (ESP32)
// ─────────────────────────────────────────────
//  GPIO18 → Buzzer / Relay Alert (Active-LOW)
//  GPIO19 → LED Builtin / Status  (Active-LOW)
//  GPIO0  → Button BOOT           (Active-LOW, ada internal pullup)
#define BUZZER_PIN   27
#define LED_PIN       2   // LED builtin ESP32 ada di GPIO2
#define BTN_PIN       0   // Button BOOT

// ─────────────────────────────────────────────
//  Timing (ms)
// ─────────────────────────────────────────────
#define POLL_INTERVAL_MS    5000   // Interval fetch sensor data
#define TH_REFRESH_MS      30000   // Interval refresh threshold dari Laravel
#define SENSOR_TIMEOUT_MS  20000   // Anggap offline jika fetch gagal > N ms
#define BLINK_FAST_MS        200   // Kecepatan kedip saat ALERT
#define BLINK_SLOW_MS       1000   // Kecepatan kedip saat NORMAL
#define HTTP_TIMEOUT_MS     8000   // Timeout per HTTP request
