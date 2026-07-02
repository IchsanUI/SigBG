# PRD — Sistem Digital Signage Media Player

**Versi:** 1.0
**Tanggal:** 1 Juli 2026
**Status:** Draft

---

## 1. Ringkasan Produk

Sistem digital signage berbasis web (SSR — PHP + MySQL) untuk menampilkan konten promosi berupa **gambar/video secara slideshow otomatis** pada layar (TV/monitor) di lokasi fisik. Sistem terdiri dari dua bagian utama:

1. **Player** — halaman fullscreen yang menampilkan media secara looping, dengan transisi fade in/out yang mulus, dan running text di bagian bawah layar.
2. **Dashboard Admin** — panel untuk mengelola media, playlist, jadwal tayang, dan pengaturan running text.

---

## 2. Latar Belakang & Tujuan

- Kebutuhan menampilkan konten promosi (produk) secara otomatis pada layar display tanpa perlu campur tangan manual tiap ganti konten.
- Konten perlu bisa dijadwalkan berdasarkan jam tayang (misal: promo pagi beda dengan promo sore).
- Admin non-teknis harus bisa upload media dan mengatur jadwal sendiri tanpa sentuh kode.
- Stack disesuaikan dengan environment yang sudah familiar: PHP native/CodeIgniter + MySQL, hosting lokal (Laragon), tanpa framework JS berat.

---

## 3. Target Pengguna

| Role | Deskripsi |
|---|---|
| **Admin** | Mengelola media, playlist, jadwal, dan running text melalui dashboard. |
| **Viewer (pasif)** | Pengunjung/pelanggan yang melihat layar player — tidak berinteraksi langsung dengan sistem. |

---

## 4. Ruang Lingkup (Scope)

### 4.1 In-Scope (v1.0)

- Upload media (image & video)
- Pengaturan durasi tayang per gambar (dalam detik)
- Video otomatis lanjut berdasarkan durasi asli filenya
- Manajemen Playlist (kumpulan media + urutan tayang, drag & drop reorder)
- Manajemen Jadwal (Schedule) — playlist mana tayang di jam & hari apa
- Running text (ticker) di bagian bawah player, dikelola dari admin
- Player fullscreen dengan transisi fade in/out (dual-layer crossfade)
- Player auto-update playlist/jadwal tanpa perlu refresh manual (polling berkala)
- Fallback playlist default jika tidak ada jadwal aktif pada jam tsb
- Preview media & preview player dari dashboard admin

### 4.2 Out-of-Scope (v1.0 — bisa jadi v2)

- Auto-start player saat PC/device menyala (Task Scheduler) — **tidak digunakan, dijalankan manual**
- Multi-device/multi-screen management (1 sistem = 1 titik tayang dulu)
- Statistik/log tayang mendetail (jumlah putar per media) — opsional, dipertimbangkan di v2
- Aplikasi mobile companion
- Multi-user role granular (cukup 1 level admin dulu)

---

## 5. Fitur Detail

### 5.1 Manajemen Media

- Upload file image (`jpg`, `png`, `webp`) dan video (`mp4`)
- Validasi ukuran file & format saat upload
- Untuk **image**: input durasi tayang (detik), default 10 detik, minimal 3 detik
- Untuk **video**: durasi otomatis mengikuti panjang asli file (tidak perlu input manual)
- List media dengan thumbnail preview, tombol edit & hapus
- Soft delete (media yang dihapus tidak langsung hilang permanen dari storage, agar bisa direstore bila perlu)

### 5.2 Manajemen Playlist

- Buat/edit/hapus playlist (punya nama, misal "Promo Pagi", "Promo Weekend")
- Tambah media ke playlist dari daftar media yang sudah diupload
- Reorder urutan tayang via drag & drop (SortableJS)
- Satu media bisa dipakai di lebih dari satu playlist

### 5.3 Manajemen Jadwal (Schedule)

- Assign satu playlist ke rentang waktu tertentu (`start_time` – `end_time`)
- Pilih hari aktif (checkbox Senin–Minggu, atau "Setiap Hari")
- Bisa lebih dari satu jadwal aktif dalam sehari (berbeda jam)
- Validasi tidak boleh ada jadwal yang bentrok/overlap pada hari & jam yang sama
- Toggle aktif/nonaktif jadwal tanpa perlu hapus
- Playlist default (fallback) yang tayang otomatis jika di luar jam yang terjadwalkan

### 5.4 Running Text (Ticker)

- Admin bisa input/edit teks berjalan
- Toggle aktif/nonaktif ticker
- Pengaturan kecepatan scroll teks
- Tampil fixed di bagian bawah layar player, di atas media (overlay)

### 5.5 Player (Tampilan Utama)

- Fullscreen (memenuhi seluruh layar/window browser)
- Slideshow otomatis sesuai playlist aktif & durasi masing-masing media
- **Transisi crossfade smooth** (dual-layer opacity, durasi ~0.8–1.5 detik)
- Preload media berikutnya sebelum transisi, agar tidak ada jeda/lag
- Polling berkala (misal tiap 30–60 detik) ke server untuk cek:
  - Apakah jadwal aktif berubah (ganti playlist)
  - Apakah ada media/playlist baru di playlist yang sedang tayang
- Background hitam solid sebagai base layer (menghindari flash putih)
- Auto-reconnect / lanjut playlist terakhir jika koneksi ke server terputus sementara
- Running text overlay di bagian bawah, tidak menutupi konten utama

### 5.6 Dashboard Admin

- Login sederhana (username/password, dengan hardening dasar: bcrypt, CSRF token, brute-force protection mengikuti pola yang sudah diterapkan sebelumnya)
- Menu: Media, Playlist, Jadwal, Running Text, Preview Player
- Preview player bisa dibuka di tab baru untuk cek hasil real-time

---

## 6. Arsitektur & Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP native / CodeIgniter 3 (SSR) |
| Database | MySQL / MariaDB |
| Frontend Admin | Bootstrap / AdminLTE (konsisten dengan project lain) |
| Frontend Player | HTML + CSS + Vanilla JS (dual-layer crossfade), polling via AJAX/Fetch |
| Reorder Playlist | SortableJS |
| Environment | Laragon (local development) |

**Alasan SSR + polling (bukan WebSocket dulu):** kebutuhan real-time tidak kritikal (toleransi delay 30–60 detik saat ganti jadwal wajar untuk signage), jadi polling lebih simpel untuk diimplementasi & maintain dibanding WebSocket untuk kasus ini.

---

## 7. Skema Database (Ringkas)

```
media
- id, type (image/video), file_path, title, duration (detik, khusus image),
  is_deleted, created_at

playlists
- id, name, is_default, created_at

playlist_items
- id, playlist_id, media_id, order_index

schedules
- id, playlist_id, start_time, end_time, days_of_week, is_active

ticker_settings
- id, text_content, is_active, speed

admin_users
- id, username, password_hash, created_at
```

---

## 8. Alur Kerja Utama (User Flow)

1. Admin login ke dashboard
2. Admin upload media → set durasi (jika image)
3. Admin buat playlist → masukkan media, atur urutan
4. Admin buat jadwal → pilih playlist, jam, hari aktif
5. Admin atur running text (opsional)
6. Player (dibuka manual di browser, fullscreen) otomatis menampilkan playlist sesuai jadwal yang sedang berjalan
7. Saat jam berganti sesuai jadwal baru, player otomatis switch playlist tanpa refresh manual

---

## 9. Non-Functional Requirements

- Transisi visual halus, tidak ada flicker/flash putih
- Player harus tahan berjalan lama (looping terus-menerus) tanpa memory leak di browser
- Waktu loading media (terutama video) tidak boleh mengganggu alur slideshow (preload wajib)
- Sistem tetap berjalan (fallback) meski koneksi ke server admin sempat putus

---

## 10. Open Questions / Perlu Konfirmasi

- Resolusi/orientasi layar target (landscape/portrait)?
- Berapa banyak titik/layar yang akan pakai sistem ini (1 atau lebih)?
- Apakah perlu proteksi khusus di halaman player (misal tidak bisa diakses publik tanpa token)?
