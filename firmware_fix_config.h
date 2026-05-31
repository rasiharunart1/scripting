// =====================================================================
// GANTI fungsi parseI2CData di config.h kamu dengan yang ini
// MASALAH LAMA: Nano kirim integer x100 (e.g. "1234" = 12.34V)
//               tapi ESP hanya copy string mentah → API terima 1234 bukan 12.34
// FIX: Konversi integer x100 → float desimal sebelum kirim ke API
// =====================================================================

inline void parseI2CData(const char* data, size_t len) {
  char tmp[8];

  // ---- Helper: baca N chars dari offset, konversi int-x100 → "XX.XX" ----
  // offset  : posisi mulai di buffer
  // nChars  : berapa karakter dibaca
  // dest    : char array tujuan
  // destSize: sizeof(dest)
  // fallback: nilai default jika data tidak cukup

  // Battery A  (offset=0,  4 chars) → e.g. "1234" = 12.34V
  if (len >= 4) {
    strncpy(tmp, data + 0, 4); tmp[4] = '\0';
    snprintf(battery_a, sizeof(battery_a), "%.2f", atoi(tmp) / 100.0f);
  } else { strcpy(battery_a, "00.00"); }

  // Battery B  (offset=4,  4 chars)
  if (len >= 8) {
    strncpy(tmp, data + 4, 4); tmp[4] = '\0';
    snprintf(battery_b, sizeof(battery_b), "%.2f", atoi(tmp) / 100.0f);
  } else { strcpy(battery_b, "00.00"); }

  // Battery C  (offset=8,  4 chars)
  if (len >= 12) {
    strncpy(tmp, data + 8, 4); tmp[4] = '\0';
    snprintf(battery_c, sizeof(battery_c), "%.2f", atoi(tmp) / 100.0f);
  } else { strcpy(battery_c, "00.00"); }

  // Battery D  (offset=12, 4 chars)
  if (len >= 16) {
    strncpy(tmp, data + 12, 4); tmp[4] = '\0';
    snprintf(battery_d, sizeof(battery_d), "%.2f", atoi(tmp) / 100.0f);
  } else { strcpy(battery_d, "00.00"); }

  // PLN Volt   (offset=16, 6 chars) → e.g. "022000" = 220.00V  (x100)
  if (len >= 22) {
    strncpy(tmp, data + 16, 6); tmp[6] = '\0';
    snprintf(pln_volt, sizeof(pln_volt), "%.2f", atoi(tmp) / 100.0f);
  } else { strcpy(pln_volt, "000.00"); }

  // PLN Current (offset=22, 5 chars) → e.g. "00150" = 1.50A  (x100)
  if (len >= 27) {
    strncpy(tmp, data + 22, 5); tmp[5] = '\0';
    snprintf(pln_current, sizeof(pln_current), "%.2f", atoi(tmp) / 100.0f);
  } else { strcpy(pln_current, "00.00"); }

  // PLN Power/Watt (offset=27, 5 chars) → e.g. "01000" = 1000W (BUKAN x100, sudah dalam watt)
  if (len >= 32) {
    strncpy(tmp, data + 27, 5); tmp[5] = '\0';
    // PowerAC = (int)(power) — tidak dikali 100, langsung watt
    snprintf(pln_watt, sizeof(pln_watt), "%d", atoi(tmp));
  } else { strcpy(pln_watt, "0"); }
}
