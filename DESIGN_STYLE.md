# DESIGN_STYLE.md — Panduan Visual Sistem Digital Signage

Dokumen ini membagi panduan desain jadi dua konteks yang berbeda tujuan:

1. **Dashboard Admin** — alat kerja, prioritas jelas & efisien dibaca cepat
2. **Player** — media presentasi, prioritas "menghilang" dan membiarkan konten (gambar/video) jadi bintang utama

---

## 1. Dashboard Admin

### 1.1 Filosofi

Admin akan buka dashboard ini berkali-kali sehari untuk tugas berulang: upload media, atur urutan, cek jadwal. Desain harus **cepat dibaca, rendah friksi**, bukan showcase visual. Konsisten dengan pola yang sudah dipakai di project lain (AdminLTE/SB Admin 2 base) supaya familiar dan tidak perlu belajar ulang pola UI.

### 1.2 Palet Warna

| Token | Hex | Penggunaan |
|---|---|---|
| `--primary-navy` | `#1B2A4A` | Sidebar, header, elemen brand utama |
| `--accent-blue` | `#2E6FF2` | Tombol aksi utama, link aktif, highlight |
| `--surface-bg` | `#F5F7FA` | Background halaman |
| `--surface-card` | `#FFFFFF` | Card, table, form container |
| `--text-primary` | `#1A1F2B` | Teks utama |
| `--text-secondary` | `#6B7280` | Label, caption, teks sekunder |
| `--success` | `#1E9E5A` | Status aktif, notifikasi sukses |
| `--warning` | `#E0A527` | Jadwal bentrok, peringatan |
| `--danger` | `#D64545` | Hapus, error, validasi gagal |
| `--border` | `#E2E6ED` | Garis pembatas, table border |

Tema **flat navy/blue** — tanpa gradient berlebihan, tanpa shadow tebal. Konsisten dengan tema yang sudah dipakai di sistem invoice 14Group sebelumnya.

### 1.3 Tipografi

- **Font utama:** Plus Jakarta Sans (konsisten dengan project sebelumnya)
- **Fallback:** `-apple-system, "Segoe UI", sans-serif`
- Skala:
  - Judul halaman: 20px / semibold
  - Judul card/section: 16px / semibold
  - Body/table: 14px / regular
  - Caption/label kecil: 12px / medium, warna `--text-secondary`

### 1.4 Layout

```
┌─────────────┬───────────────────────────────┐
│             │  Topbar (judul halaman, user)  │
│  Sidebar    ├───────────────────────────────┤
│  (navy)     │                               │
│  - Media    │   Content area (card-based)   │
│  - Playlist │                               │
│  - Jadwal   │                               │
│  - Ticker   │                               │
│  - Preview  │                               │
└─────────────┴───────────────────────────────┘
```

- Sidebar fixed kiri, collapsible di layar kecil
- Konten utama pakai card dengan padding cukup lega (16–24px), border-radius kecil (6–8px) — bukan 0 (biar tidak terasa "koran"), bukan besar juga (biar tetap terasa serius/fungsional)
- Table pakai zebra-striping tipis untuk keterbacaan baris panjang (list media/jadwal)

### 1.5 Komponen Kunci

- **Status badge**: pill kecil berwarna (`success`/`warning`/`danger`) untuk status jadwal (Aktif/Nonaktif/Bentrok)
- **Drag handle** (untuk reorder playlist): ikon grip (⋮⋮) di kiri tiap item, cursor `grab`
- **Upload area**: drag-and-drop zone dengan border dashed, berubah warna accent saat file di-drag di atasnya
- **Preview thumbnail**: rasio 16:9 konsisten di seluruh list media, dengan ikon overlay kecil (▶) untuk membedakan video dari gambar
- **Tombol utama**: solid `--accent-blue`, radius 6px, tidak ada bevel/gradient
- **Tombol destruktif** (hapus): outline `--danger`, solid hanya muncul saat konfirmasi

### 1.6 Motion

Minim animasi — dashboard adalah tempat kerja, bukan tempat pamer. Cukup:
- Transisi hover tombol (150ms ease)
- Fade halus saat modal/dropdown muncul (150–200ms)
- Reorder drag pakai animasi bawaan SortableJS (jangan dikustom berlebihan)

---

## 2. Player (Tampilan Utama)

### 2.1 Filosofi

Player harus **netral dan "menghilang"** — layar penuh warna hitam sebagai kanvas, tidak ada chrome/UI yang mengalihkan perhatian dari media yang tayang. Satu-satunya elemen non-media yang boleh tampil permanen adalah **running text** di bawah.

### 2.2 Palet Warna

| Token | Hex | Penggunaan |
|---|---|---|
| `--player-bg` | `#000000` | Base layer, letterbox/pillarbox area |
| `--ticker-bg` | `rgba(10, 14, 22, 0.85)` | Background bar running text (semi-transparan gelap) |
| `--ticker-text` | `#FFFFFF` | Teks running text |
| `--ticker-accent` | `#2E6FF2` | Aksen kecil (misal separator "•" antar kalimat ticker) |

Tidak ada palet lain — player sengaja monokrom + 1 aksen, supaya warna dari konten (foto produk, video) yang tetap jadi fokus visual, bukan bersaing dengan UI.

### 2.3 Tipografi (Ticker)

- Font sama dengan dashboard (Plus Jakarta Sans) untuk konsistensi brand
- Ukuran: 22–28px (disesuaikan resolusi layar target — TV biasanya perlu lebih besar dari asumsi desktop)
- Weight: medium/semibold, cukup tebal supaya terbaca dari jarak (signage biasanya dilihat dari jauh)
- Warna solid putih di atas background gelap semi-transparan, kontras tinggi

### 2.4 Layout

```
┌──────────────────────────────────────────┐
│                                            │
│                                            │
│           MEDIA (fullscreen,              │
│           object-fit: cover/contain)      │
│                                            │
│                                            │
├────────────────────────────────────────────┤
│  ← Running text (scroll horizontal) →      │  ~56–72px tinggi
└──────────────────────────────────────────┘
```

- Media area: 100vw x (100vh − tinggi ticker), atau full 100vh dengan ticker overlay di atas (dengan sedikit gradient shadow di baliknya supaya teks tetap terbaca meski media di baliknya terang)
- Ticker bar: fixed bottom, tinggi konsisten, tidak menutupi konten penting di media (pastikan desain media memperhitungkan safe-area ini)

### 2.5 Motion — Ini yang Paling Penting

- **Crossfade dual-layer**, durasi 0.8–1.2 detik, easing `ease-in-out`
- Tidak ada efek transisi "ramai" (slide, zoom, flip) — cukup fade murni, supaya terasa premium dan tidak norak
- Running text: scroll linear konstan (bukan easing yang naik-turun kecepatan), kecepatan diatur dari admin (pixel/detik)
- Tidak ada animasi loading spinner yang terlihat viewer — preload terjadi di belakang layar (di layer standby yang masih `opacity: 0`)

### 2.6 Prinsip Anti-Distraksi

- Tidak ada watermark/logo permanen kecuali diminta khusus
- Tidak ada border/frame dekoratif di sekitar media
- Background selalu solid hitam — tidak pernah putih/abu-abu, terutama saat momen transisi atau loading, supaya tidak ada "flash" yang mengganggu mata penonton

---

## 3. Konsistensi Lintas Bagian

- Font (Plus Jakarta Sans) dan warna navy/blue (`#1B2A4A` / `#2E6FF2`) jadi benang merah brand antara dashboard admin dan aksen ticker di player — supaya sistem terasa satu kesatuan meski dua tampilan ini punya tujuan sangat berbeda (kerja vs presentasi).
- Ikon: gunakan satu set ikon konsisten (mis. Bootstrap Icons atau Feather Icons) di seluruh dashboard, jangan campur beberapa icon set.
