# TASK.md — Breakdown Pengerjaan Sistem Digital Signage

Referensi: `PRD.md`, `DESIGN_STYLE.md`

Checklist ini disusun bertahap (fase demi fase) supaya bisa dikerjakan incremental dan dites tiap tahap sebelum lanjut.

---

## Fase 0 — Setup Awal

- [x] Setup project CodeIgniter 3 di Laragon
- [x] Setup database MySQL (`digital_signage`) — schema + seed sudah dieksekusi via `sql/database.sql`
- [x] Buat struktur folder (`application/`, `assets/uploads/media/`, dll)
- [x] Setup `.htaccess` / base URL config — `http://localhost/SigBG/`
- [x] Install SortableJS (lokal `assets/vendor/Sortable.min.js`)
- [x] Buat repo Git + `.gitignore` (exclude folder upload & config sensitif) — initial commit `5f2b4a3`

---

## Fase 1 — Database

- [x] Buat tabel `media`
- [x] Buat tabel `playlists`
- [x] Buat tabel `playlist_items`
- [x] Buat tabel `schedules`
- [x] Buat tabel `ticker_settings`
- [x] Buat tabel `admin_users`
- [x] Buat seeder awal (1 admin user, 1 playlist default kosong)

---

## Fase 2 — Autentikasi Admin

- [x] Halaman login admin (`/auth`, `Auth` controller + `auth/login_view`)
- [x] Hashing password (bcrypt) — `password_verify()` di `Admin_model`
- [x] CSRF token protection — enabled di config + token di form login
- [x] Session handling + regenerasi session ID — `sess_regenerate(TRUE)` on success
- [x] Brute-force protection (lockout 5 attempts / 15 menit per IP) — `Login_attempts_model`
- [x] Middleware/filter cek login — `MY_Controller` redirect ke `auth` jika belum login

---

## Fase 3 — Manajemen Media

- [ ] Form upload media (image & video)
- [ ] Validasi tipe file & ukuran maksimal
- [ ] Input durasi tayang (khusus image), validasi min 3 detik
- [ ] Simpan file ke folder + insert record ke DB
- [ ] List media (tabel/grid dengan thumbnail)
- [ ] Fitur edit media (ganti judul/durasi, tanpa perlu re-upload)
- [ ] Fitur hapus media (soft delete)
- [ ] Preview media langsung dari list (modal image/video)

---

## Fase 4 — Manajemen Playlist

- [ ] CRUD playlist (create, rename, delete)
- [ ] Halaman detail playlist: tambah media dari daftar media
- [ ] Drag & drop reorder urutan media dalam playlist (SortableJS)
- [ ] Hapus media dari playlist (tanpa hapus media aslinya)
- [ ] Tandai satu playlist sebagai "default/fallback"

---

## Fase 5 — Manajemen Jadwal (Schedule)

- [ ] CRUD jadwal (pilih playlist, jam mulai-selesai, hari aktif)
- [ ] Validasi bentrok jadwal (overlap check) di hari & jam yang sama
- [ ] Toggle aktif/nonaktif jadwal
- [ ] Endpoint/API untuk player: "apa playlist yang aktif sekarang?"
  - Logic: cek jadwal yang match jam & hari saat ini → kalau tidak ada, pakai playlist default

---

## Fase 6 — Running Text (Ticker)

- [ ] Form pengaturan teks ticker (isi teks, kecepatan, aktif/nonaktif)
- [ ] Endpoint API untuk player ambil setting ticker terbaru
- [ ] Implementasi animasi scroll teks di sisi player (CSS/JS)

---

## Fase 7 — Player (Bagian Paling Krusial)

- [ ] Struktur halaman player (fullscreen, dual-layer container)
- [ ] Fetch playlist aktif dari API saat pertama load
- [ ] Render media sesuai urutan playlist
- [ ] Implementasi crossfade dual-layer (image & video)
- [ ] Logic durasi:
  - [ ] Image → pakai `duration` dari DB
  - [ ] Video → pakai event `ended`/`timeupdate`
- [ ] Preload media selanjutnya sebelum transisi
- [ ] Polling berkala (30–60 detik) untuk cek perubahan jadwal/playlist
- [ ] Handle perpindahan playlist saat jadwal berganti (smooth, tanpa reload halaman)
- [ ] Render running text overlay di bawah layar
- [ ] Fallback: kalau fetch API gagal, lanjutkan playlist terakhir yang berhasil diambil (jangan macet/blank)
- [ ] Background hitam solid sebagai base (anti flash putih)
- [ ] Testing looping jangka panjang (cek memory leak di browser, terutama untuk video)

---

## Fase 8 — Dashboard Tambahan

- [ ] Tombol "Preview Player" (buka player di tab baru dari dashboard)
- [ ] Halaman dashboard ringkas (jumlah media, playlist, jadwal aktif hari ini)

---

## Fase 9 — Testing & QA

- [ ] Test upload berbagai format & ukuran file (termasuk file gagal/reject)
- [ ] Test transisi fade di berbagai kombinasi image→image, image→video, video→image
- [ ] Test jadwal bentrok & fallback playlist default
- [ ] Test player saat koneksi internet/server terputus sementara
- [ ] Test player dibiarkan menyala berjam-jam (cek stabilitas)
- [ ] Test responsif di resolusi layar TV yang akan dipakai

---

## Fase 10 — Deployment

- [ ] Finalisasi konfigurasi Laragon/production environment
- [ ] Setup shortcut/manual-start untuk player (karena auto-start via Task Scheduler tidak dipakai)
- [ ] Dokumentasi singkat cara pakai untuk admin (SOP upload media & atur jadwal)

---

## Backlog / Ide Fase Selanjutnya (v2, opsional)

- [ ] Statistik/log tayang per media
- [ ] Multi-device/multi-screen support
- [ ] Role admin bertingkat (super admin vs admin biasa)
- [ ] Notifikasi kalau ada jadwal yang belum di-set untuk suatu rentang waktu
