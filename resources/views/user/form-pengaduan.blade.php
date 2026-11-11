@extends('layouts.app')

@section('title', 'Form Pengaduan Satwa Liar — InJourney Airports')

@section('styles')
<style>
  .satwa-check {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #00A9C1;
    color: white;
    font-size: 11px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.6);
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    border: 2px solid #ffffff;
    z-index: 10;
  }
  .satwa-card {
    transition: all 0.2s ease-in-out;
  }
  .satwa-card:hover {
    transform: translateY(-2px);
  }
  .satwa-card.selected {
    border-color: #00A9C1 !important;
    box-shadow: 0 0 0 2px rgba(0,169,193,0.35), 0 4px 12px rgba(0,169,193,0.1) !important;
    background: #ffffff !important;
  }
  .satwa-card.selected .satwa-check {
    opacity: 1;
    transform: scale(1);
  }
  .grid-cell-pulse {
    animation: cellPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }
  @keyframes cellPulse {
    0%, 100% { opacity: 0.95; }
    50% { opacity: 0.65; }
  }
  .juanda-grid-row {
    display: grid;
    grid-template-columns: 32px repeat(29, minmax(32px, 1fr));
    gap: 2px;
    align-items: center;
  }
  .grid-hit-cell:hover {
    fill: rgba(0, 169, 193, 0.25) !important;
    stroke: #00A9C1 !important;
    stroke-width: 1.8 !important;
  }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-slate-50/80 font-sans" style="background-image: url('{{ asset('images/watermark_injourney.jpeg') }}'); background-repeat: repeat; background-size: 280px auto; background-attachment: fixed;">

  <!-- Shared Unified Side Navigation Bar -->
  @include('partials.navbar', ['activePage' => 'create'])

  <!-- TOP UNIFIED PAGE HEADER -->
  @include('partials.page-header', [
    'title' => 'Form Pengaduan Satwa Liar',
    'subtitle' => 'Lengkapi formulir pengamatan satwa liar di area sisi udara bandara secara teliti.',
    'icon' => 'edit_note',
    'maxWidth' => 'max-w-5xl',
    'actions' => '
      <a href="' . ((($user->role ?? (auth()->user()?->role ?? 'pegawai')) === 'admin') ? route('admin.dashboard') : route('user.dashboard')) . '" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200/90 bg-white text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        Kembali
      </a>
    '
  ])

  <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6">

    @if($errors->any())
      <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-2xl mb-6 text-sm">
        <strong class="font-bold flex items-center gap-2 mb-2">
          <svg class="w-4 h-4 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Mohon periksa kembali isian Anda:
        </strong>
        <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm pl-1">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="mainForm" action="{{ route('laporan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
      @csrf
      <input type="hidden" name="tanda_tangan" id="tanda_tangan">

      <!-- SECTION 1: Identitas -->
      <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm">
        <div class="border-b border-slate-100 pb-4 mb-6">
          <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs flex items-center justify-center border border-teal-200">1</span>
            Bagian 1 — Identitas & Kondisi Pemantauan
          </h2>
          <p class="text-xs text-slate-500 mt-1 pl-9">Informasi umum jadwal dan lokasi inspeksi petugas</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Nama Petugas <span class="text-rose-500">*</span></label>
            <input type="text" name="nama_petugas" value="{{ old('nama_petugas', $user->namalengkap ?: $user->username) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Tanggal Pemantauan <span class="text-rose-500">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Kondisi Cuaca <span class="text-rose-500">*</span></label>
            <select name="kondisi_cuaca" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
              <option value="">-- Pilih Kondisi Cuaca --</option>
              @foreach($kondisi_cuaca_opt as $c)
                <option value="{{ $c }}" {{ old('kondisi_cuaca') == $c ? 'selected' : '' }}>{{ $c }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Unit Kerja <span class="text-rose-500">*</span></label>
            <select name="unit_kerja" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
              <option value="">-- Pilih Unit Kerja --</option>
              @foreach($unit_kerja_opt as $u)
                <option value="{{ $u }}" {{ old('unit_kerja', $unitKerjaUser) == $u ? 'selected' : '' }}>{{ $u }}</option>
              @endforeach
            </select>
          </div>

          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-700 mb-2">Area Inspeksi <span class="text-rose-500">*</span></label>
            <select name="area_inspeksi" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
              <option value="">-- Pilih Area Inspeksi --</option>
              @foreach($area_opt as $a)
                <option value="{{ $a }}" {{ old('area_inspeksi') == $a ? 'selected' : '' }}>{{ $a }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <!-- SECTION 2: Pengamatan Satwa & Foto Burung -->
      <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm relative z-30">
        <div class="border-b border-slate-100 pb-4 mb-6">
          <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs flex items-center justify-center border border-teal-200">2</span>
            Bagian 2 — Pengamatan Satwa & Foto Burung
          </h2>
          <p class="text-xs text-slate-500 mt-1 pl-9">Ambil foto burung dan tentukan jenis burung, jumlah, dan grid lokasi</p>
        </div>

        <div class="space-y-5">
          <!-- 1. Foto Burung -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-xs font-bold text-slate-700">Foto Burung <span class="text-rose-500">*</span></label>
              <span id="fotoStatusBadge" class="text-xs text-slate-400">Wajib terlampir</span>
            </div>

            <!-- Preview Foto -->
            <div id="fotoPreviewContainer" class="w-full rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-4 flex flex-col items-center justify-center text-center transition-all min-h-[190px]">
              
              <!-- Placeholder State -->
              <div id="fotoPlaceholder" class="flex flex-col items-center justify-center py-4">
                <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600 mb-2">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <circle cx="12" cy="13" r="3" />
                  </svg>
                </div>
                <div class="font-bold text-slate-800 text-sm mb-0.5">Belum Ada Foto Burung</div>
                <p class="text-xs text-slate-500">Ambil foto langsung dengan kamera HP atau unggah dari galeri.</p>
              </div>

              <!-- Filled State -->
              <div id="fotoPreviewFilled" class="hidden w-full flex-col items-center">
                <div class="w-full max-h-[320px] rounded-lg overflow-hidden bg-slate-900 border border-slate-200 flex items-center justify-center">
                  <img id="fotoPreviewImg" src="" alt="Preview Foto" class="w-full max-h-[320px] object-contain">
                </div>
                <div class="w-full mt-2.5 pt-2 border-t border-slate-200 flex items-center justify-between gap-2 flex-wrap text-left">
                  <div class="min-w-0">
                    <div id="fotoNamaFile" class="font-bold text-xs text-slate-800 truncate">foto_burung.jpg</div>
                    <div id="fotoUkuranFile" class="text-[11px] text-slate-500">Ukuran: -</div>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" onclick="klikAmbilFoto()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold cursor-pointer transition-colors">
                      Ganti Foto
                    </button>
                    <button type="button" onclick="hapusFotoBurung()" class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition-colors cursor-pointer">
                      Hapus
                    </button>
                  </div>
                </div>
              </div>

            </div>

            <!-- Tombol Kamera & Galeri (Kompak, Rapi & Jelas Terlihat) -->
            <div class="grid grid-cols-2 gap-2.5 mt-2.5">
              <!-- Tombol Kamera Cerdas (HP: Kamera HP, Laptop: Webcam di Layar) -->
              <button type="button" onclick="klikAmbilFoto()" style="background-color: #00A9C1 !important; color: #ffffff !important;" class="py-2.5 px-3 rounded-lg hover:opacity-90 active:scale-[0.98] font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 cursor-pointer shadow-xs transition-all text-center select-none">
                <svg class="w-4 h-4 shrink-0" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  <circle cx="12" cy="13" r="3" />
                </svg>
                <span style="color: #ffffff !important;">Ambil Foto (Kamera)</span>
              </button>

              <!-- Tombol Galeri via direct label touch -->
              <label for="inputGaleriFoto" style="background-color: #ffffff !important; color: #334155 !important; border: 1px solid #cbd5e1 !important;" class="py-2.5 px-3 rounded-lg hover:bg-slate-50 active:bg-slate-100 font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-[0.98] transition-all text-center select-none">
                <svg class="w-4 h-4 shrink-0" style="color: #64748b !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span style="color: #334155 !important;">Upload Galeri</span>
              </label>
            </div>

            <!-- Hidden File Inputs (Tersembunyi total tanpa teks inline default browser) -->
            <input type="file" id="inputKameraBelakang" name="satwa[0][foto]" accept="image/*" capture="environment" style="display: none !important;" class="hidden" onchange="handleFileSelected(this)">
            <input type="file" id="inputGaleriFoto" accept="image/*" style="display: none !important;" class="hidden" onchange="handleFileSelected(this)">
            <input type="file" name="satwa[0][foto]" id="formSatwaFotoInput" style="display: none !important;" class="hidden" accept="image/*">
            <input type="file" name="foto_lainnya[]" id="formFotoLainnyaInput" style="display: none !important;" class="hidden" accept="image/*">

          </div>

          <!-- 2. Pilihan Jenis Burung (Dropdown Interaktif dengan Search & Pagination 8 Item per Slide) -->
          <div class="relative z-40" id="dropdownBurungContainer">
            <label class="block text-xs font-bold text-slate-700 mb-2">
              Jenis Burung <span class="text-rose-500">*</span>
            </label>

            <!-- Hidden input untuk pengiriman form & validasi submit -->
            <input type="hidden" name="satwa[0][nama]" id="selectJenisBurung" value="{{ old('satwa.0.nama', '') }}" required>

            <!-- Tombol Trigger Dropdown (Desain serasi dengan dropdown form lainnya) -->
            <button type="button" 
                    id="btnTriggerBurung" 
                    onclick="toggleBurungDropdown()" 
                    class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white border border-slate-200 rounded-xl text-sm flex items-center justify-between text-left cursor-pointer transition-all focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white shadow-2xs">
              <div class="flex items-center gap-2.5 min-w-0 pr-2">
                <span id="triggerBurungDot" class="w-2 h-2 rounded-full {{ old('satwa.0.nama') ? 'bg-teal-500' : 'bg-slate-300' }} shrink-0"></span>
                <span id="triggerBurungText" class="truncate font-medium {{ old('satwa.0.nama') ? 'text-slate-800' : 'text-slate-500' }}">
                  {{ old('satwa.0.nama', '-- Pilih Jenis Burung --') }}
                </span>
              </div>
              <svg id="chevronBurung" class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Panel Dropdown Menu (Membuka ke bawah seperti dropdown lainnya, z-50 melayang di atas bagian form lainnya) -->
            <div id="menuBurungDropdown" 
                 onclick="event.stopPropagation()"
                 class="hidden absolute left-0 right-0 top-full mt-1.5 z-50 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
              
              <!-- Kolom Search Real-Time -->
              <div class="p-2.5 border-b border-slate-100 bg-slate-50/80 shrink-0">
                <div class="relative">
                  <input type="text" 
                         id="inputSearchBurung" 
                         oninput="onSearchBurungRealtime(this.value)" 
                         placeholder="Cari nama burung atau nama ilmiah..." 
                         autocomplete="off"
                         class="w-full pl-8 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                  <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <button type="button" 
                          id="btnResetSearchBurung" 
                          onclick="resetSearchBurung()" 
                          class="hidden absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Daftar Jenis Burung (Maksimal 5 item per slide/halaman) -->
              <div id="listBurungContainer" class="p-1 divide-y divide-slate-50 overflow-y-auto max-h-[260px]">
                <!-- Item burung dirender dinamis via renderBurungList() -->
              </div>

              <!-- Navigasi Pagination di bagian bawah -->
              <div id="paginationBurungFooter" class="px-3 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600 shrink-0">
                <span id="burungPageInfo" class="text-[11px] font-medium text-slate-500 truncate">
                  Memuat data...
                </span>
                <div class="flex items-center gap-1.5 shrink-0">
                  <button type="button" 
                          id="btnBurungPrev" 
                          onclick="changeBurungPage(-1, event)" 
                          class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 active:bg-slate-200 font-semibold text-slate-700 disabled:opacity-35 disabled:cursor-not-allowed cursor-pointer transition-colors flex items-center gap-1">
                    <span id="btnBurungPrevText">‹ Prev</span>
                  </button>
                  <span id="burungCurrentPageBadge" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 font-bold text-[11px]">
                    1 / 1
                  </span>
                  <button type="button" 
                          id="btnBurungNext" 
                          onclick="changeBurungPage(1, event)" 
                          class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 active:bg-slate-200 font-semibold text-teal-600 hover:text-teal-700 disabled:opacity-35 disabled:cursor-not-allowed cursor-pointer transition-colors flex items-center gap-1">
                    <span id="btnBurungNextText">Slide Selanjutnya (Next) ›</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Mini Specimen Info Card (Optional) -->
            <div id="satwaInfoPill" class="hidden mt-2 bg-slate-50 border border-slate-200 rounded-xl p-2.5 flex items-center justify-between gap-3 text-xs">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-200 shrink-0 border border-slate-200">
                  <img id="satwaPillThumb" src="" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0">
                  <div id="satwaPillNama" class="font-bold text-slate-800 truncate"></div>
                  <div id="satwaPillLatin" class="text-slate-500 italic text-[11px] truncate"></div>
                </div>
              </div>
              <div class="shrink-0">
                <span id="satwaPillRisiko" class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md bg-teal-50 text-teal-700 border border-teal-200">
                  Risiko Sedang
                </span>
              </div>
            </div>
          </div>

          <!-- 3. Pilihan Jumlah Burung -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Jumlah Burung <span class="text-rose-500">*</span></label>
            <div class="flex items-center gap-3">
              <div class="inline-flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                <button type="button" onclick="stepJumlahBurung(-1)" class="w-10 h-10 bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-700 font-bold text-base flex items-center justify-center transition-colors border-r border-slate-200 select-none cursor-pointer">
                  −
                </button>
                <input type="number" name="satwa[0][lokasi][0][jumlah]" id="inputJumlahBurung" value="{{ old('satwa.0.lokasi.0.jumlah', 1) }}" min="1" step="1" class="w-20 h-10 bg-white text-center font-bold text-sm text-slate-800 focus:outline-none select-all" required oninput="if(this.value < 1) this.value = 1;">
                <button type="button" onclick="stepJumlahBurung(1)" class="w-10 h-10 bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-700 font-bold text-base flex items-center justify-center transition-colors border-l border-slate-200 select-none cursor-pointer">
                  +
                </button>
              </div>
              <span class="text-xs text-slate-500">Ekor / Individu</span>
            </div>
          </div>

          <!-- 4. Pilihan Grid & Deteksi Lokasi GPS (REQ-FIELD-003) -->
          <div>
            <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
              <label for="selectGridBurung" class="block text-xs font-bold text-slate-700">
                Pilihan Grid Bandara <span class="text-rose-500">*</span>
              </label>

              <div class="flex items-center gap-2">
                <!-- Tombol Deteksi Lokasi GPS (REQ-FIELD-003) -->
                <button type="button" 
                        id="btnGpsDetect" 
                        onclick="triggerGpsDetection()" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 active:bg-teal-200 text-[#007fa3] border border-teal-200 font-bold text-xs transition-all cursor-pointer shadow-2xs active:scale-95 select-none"
                        title="Deteksi posisi GPS gawai secara otomatis via HTML5 Geolocation API dan petakan ke kode grid Juanda">
                  <span class="material-symbols-outlined text-sm text-[#00A9C1]" id="iconGpsBtn">my_location</span>
                  <span id="textGpsBtn">Deteksi Lokasi GPS</span>
                </button>

                <button type="button" onclick="scrollKePetaBagian3()" class="text-xs font-semibold text-[#00A9C1] hover:underline flex items-center gap-1 cursor-pointer">
                  <span>🗺 Lihat di Peta</span>
                </button>
              </div>
            </div>
            
            <input type="text" 
                   name="satwa[0][lokasi][0][grid]" 
                   id="selectGridBurung" 
                   value="{{ old('satwa.0.lokasi.0.grid', '') }}" 
                   placeholder="Ketik nomor grid (misal: K-10, D-9) atau tekan tombol Deteksi Lokasi GPS" 
                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:outline-none focus:ring-2 focus:ring-[#00A9C1] focus:border-[#00A9C1] transition-all uppercase" 
                   oninput="onGridTextInput(this.value)" 
                   required>

            <!-- Hidden Inputs untuk Pengiriman GPS & Kode Grid Resmi ke Database Laravel (REQ-FIELD-003) -->
            <input type="hidden" name="gps_lat" id="formGpsLat" value="{{ old('gps_lat', old('latitude', '')) }}">
            <input type="hidden" name="gps_lng" id="formGpsLng" value="{{ old('gps_lng', old('longitude', '')) }}">
            <input type="hidden" name="kode_grid" id="formKodeGrid" value="{{ old('kode_grid', old('grid_lokasi', '')) }}">

            <!-- GPS Auto-Detect Status & Feedback Panel (REQ-FIELD-003) -->
            <div id="gpsStatusContainer" class="mt-2 text-xs">
              <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-slate-600 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                  <span class="material-symbols-outlined text-sm text-[#00A9C1] shrink-0">my_location</span>
                  <span class="text-[11px] truncate">Tekan <strong>"Deteksi Lokasi GPS"</strong> untuk auto-detect koordinat perangkat dan pemetaan grid.</span>
                </div>
                <span class="text-[10px] font-mono font-semibold text-slate-400 shrink-0 uppercase">HTML5 GPS</span>
              </div>
            </div>

            <div id="gridPillSummary" class="text-xs text-slate-500 mt-1.5 flex items-center gap-1">
              <span>Grid Terpilih: <strong id="gridPillText" class="text-slate-800 font-semibold">{{ old('satwa.0.lokasi.0.grid') ? ('Grid ' . old('satwa.0.lokasi.0.grid')) : '-' }}</strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Camera Viewfinder Modal -->
      <div id="liveCameraModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col">
          <div class="px-4 py-3 bg-slate-800/90 border-b border-slate-700 flex items-center justify-between text-white">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
              <span class="font-bold text-xs sm:text-sm">Kamera Langsung di Layar</span>
            </div>
            <button type="button" onclick="tutupLiveKameraModal()" class="text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          
          <div class="relative bg-black flex items-center justify-center min-h-[300px] max-h-[65vh] overflow-hidden">
            <video id="liveCameraVideo" autoplay playsinline class="w-full h-full object-cover"></video>
            <canvas id="liveCameraCanvas" class="hidden"></canvas>
            <div id="cameraLoadingHint" class="absolute inset-0 flex flex-col items-center justify-center text-white text-xs bg-slate-900 gap-2">
              <svg class="w-8 h-8 animate-spin text-teal-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
              <span>Mengaktifkan kamera...</span>
            </div>
          </div>

          <div class="p-4 bg-slate-900 border-t border-slate-800 flex items-center justify-around">
            <button type="button" onclick="switchKameraLive()" id="btnSwitchCamera" class="p-3 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs flex items-center justify-center cursor-pointer" title="Putar Kamera Depan / Belakang">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>

            <button type="button" onclick="ambilFotoDariLiveVideo()" class="w-16 h-16 rounded-full bg-white border-4 border-teal-500 shadow-lg active:scale-90 transition-transform flex items-center justify-center text-teal-600 cursor-pointer" title="Jepret Foto">
              <div class="w-12 h-12 rounded-full bg-teal-500"></div>
            </button>

            <button type="button" onclick="tutupLiveKameraModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold cursor-pointer">
              Tutup
            </button>
          </div>
        </div>
      </div>

      <!-- SECTION 3: Denah Gridmap Bandara Interaktif -->
      <div id="petaKoordinatSection" class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm scroll-mt-6 relative z-10">
        <div class="border-b border-slate-100 pb-4 mb-6">
          <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs flex items-center justify-center border border-teal-200">3</span>
            Bagian 3 — Peta Koordinat Grid Sisi Udara Bandara
          </h2>
          <p class="text-xs text-slate-500 mt-1 pl-9">Gunakan peta grid interaktif apron/runway Bandara Juanda berikut untuk menentukan kode grid lokasi secara cepat dan presisi</p>
        </div>

        @include('partials.grid-map-picker')
      </div>

      <!-- SECTION 4: Detail Kondisi & Tindak Lanjut -->
      <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm">
        <div class="border-b border-slate-100 pb-4 mb-6">
          <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs flex items-center justify-center border border-teal-200">4</span>
            Bagian 4 — Kondisi & Tindakan Pengusiran
          </h2>
          <p class="text-xs text-slate-500 mt-1 pl-9">Rincian kondisi satwa dan metode pengendalian yang dilakukan</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Ciri-ciri & Ukuran Satwa Liar <span class="text-rose-500">*</span></label>
            <textarea name="ciri_ukuran" rows="3" placeholder="Deskripsikan morfologi dan ukuran fisik satwa..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>{{ old('ciri_ukuran') }}</textarea>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-2">Kondisi Ditemukan di Area Sisi Udara <span class="text-rose-500">*</span></label>
              <select name="kondisi_apron" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
                <option value="">-- Pilih Kondisi --</option>
                @foreach($kondisi_apron_opt as $kp)
                  <option value="{{ $kp }}" {{ old('kondisi_apron') == $kp ? 'selected' : '' }}>{{ $kp }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-2">Aktivitas Satwa Liar di Lapangan <span class="text-rose-500">*</span></label>
              <input type="text" name="aktivitas_satwa" value="{{ old('aktivitas_satwa') }}" placeholder="cth: Melintas taxiway, mencari makan" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Tindak Lanjut Penanganan <span class="text-rose-500">*</span></label>
            <select name="tindak_lanjut" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
              <option value="">-- Pilih Tindak Lanjut --</option>
              @foreach($tindak_lanjut_opt as $tl)
                <option value="{{ $tl }}" {{ old('tindak_lanjut') == $tl ? 'selected' : '' }}>{{ $tl }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Detail Metode Pengusiran yang Dilakukan</label>
            <textarea name="detail_pengusiran" rows="2" placeholder="Sebutkan alat atau tindakan pengusiran yang diterapkan (isi '-' jika tidak ada)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">{{ old('detail_pengusiran', '-') }}</textarea>
          </div>
        </div>
      </div>

      <!-- SECTION 5: Tanda Tangan Digital -->
      <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm">
        <div class="border-b border-slate-100 pb-4 mb-6">
          <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs flex items-center justify-center border border-teal-200">5</span>
            Bagian 5 — Tanda Tangan Digital Petugas
          </h2>
          <p class="text-xs text-slate-500 mt-1 pl-9">Bubuhkan tanda tangan basah digital Anda pada area kanvas di bawah ini</p>
        </div>

        <div class="border-2 border-dashed border-slate-300 rounded-2xl bg-slate-50/50 overflow-hidden relative">
          <canvas id="signature-pad" class="w-full h-48 block cursor-crosshair"></canvas>
          <div class="flex justify-end p-2.5 bg-slate-100 border-t border-slate-200">
            <button type="button" class="text-xs font-semibold text-slate-500 hover:text-rose-600 underline cursor-pointer transition-all" id="clearSignature">
              Hapus & Tanda Tangan Ulang
            </button>
          </div>
        </div>
      </div>

      <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#005f7a] text-white font-bold text-base rounded-2xl shadow-xl shadow-cyan-500/25 active:scale-[0.99] transition-all duration-200 cursor-pointer">
        Kirim Laporan Logbook
      </button>
    </form>
  </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
  // ── Signature Pad ──
  const canvas = document.getElementById('signature-pad');
  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = 200 * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
  }
  window.addEventListener("resize", resizeCanvas);
  resizeCanvas();

  const signaturePad = new SignaturePad(canvas, {
    backgroundColor: 'rgb(255, 255, 255)'
  });

  document.getElementById('clearSignature').addEventListener('click', () => {
    signaturePad.clear();
  });

  // ── Bagian 2: Kamera & Upload Foto Burung ──
  let hasBirdPhoto = false;
  let liveCameraStream = null;
  let activeFacingMode = "environment"; // Prioritaskan kamera belakang

  function klikAmbilFoto() {
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobile) {
      // Pada HP/Mobile: buka langsung kamera HP
      const camInput = document.getElementById('inputKameraBelakang');
      if (camInput) camInput.click();
    } else {
      // Pada Laptop/Desktop: buka webcam kamera laptop langsung di layar
      bukaLiveKameraModal();
    }
  }

  function handleFileSelected(input) {
    if (!input.files || !input.files[0]) return;
    applyPhotoFile(input.files[0], input);
  }

  function applyPhotoFile(file, sourceInput = null) {
    hasBirdPhoto = true;

    // Create object URL for instant high-quality preview
    const previewUrl = URL.createObjectURL(file);
    const img = document.getElementById('fotoPreviewImg');
    const ph = document.getElementById('fotoPlaceholder');
    const filled = document.getElementById('fotoPreviewFilled');
    const nama = document.getElementById('fotoNamaFile');
    const ukuran = document.getElementById('fotoUkuranFile');
    const badge = document.getElementById('fotoStatusBadge');

    if (img) img.src = previewUrl;
    if (ph) ph.classList.add('hidden');
    if (filled) {
      filled.classList.remove('hidden');
      filled.classList.add('flex');
    }

    const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
    if (nama) nama.textContent = file.name;
    if (ukuran) ukuran.textContent = `Ukuran: ${sizeMb} MB • Format: ${file.type || 'image/jpeg'}`;
    if (badge) {
      badge.innerHTML = '<span class="text-teal-600 font-bold flex items-center gap-1">✓ Foto Terlampir</span>';
    }

    // Assign file to form input
    const cam = document.getElementById('inputKameraBelakang');
    const gal = document.getElementById('inputGaleriFoto');
    const satwaFoto = document.getElementById('formSatwaFotoInput');

    if (sourceInput === cam) {
      cam.name = 'satwa[0][foto]';
      if (gal) gal.removeAttribute('name');
    } else if (sourceInput === gal) {
      gal.name = 'satwa[0][foto]';
      if (cam) cam.removeAttribute('name');
    } else {
      // From live camera or canvas snapshot
      if (satwaFoto && window.DataTransfer) {
        try {
          const dt = new DataTransfer();
          dt.items.add(file);
          satwaFoto.files = dt.files;
          satwaFoto.name = 'satwa[0][foto]';
          if (cam) cam.removeAttribute('name');
          if (gal) gal.removeAttribute('name');
        } catch (e) {}
      }
    }
  }

  function hapusFotoBurung() {
    hasBirdPhoto = false;
    const img = document.getElementById('fotoPreviewImg');
    const ph = document.getElementById('fotoPlaceholder');
    const filled = document.getElementById('fotoPreviewFilled');
    const badge = document.getElementById('fotoStatusBadge');
    const cam = document.getElementById('inputKameraBelakang');
    const gal = document.getElementById('inputGaleriFoto');
    const satwaFoto = document.getElementById('formSatwaFotoInput');

    if (img) img.src = '';
    if (ph) ph.classList.remove('hidden');
    if (filled) {
      filled.classList.add('hidden');
      filled.classList.remove('flex');
    }
    if (badge) {
      badge.innerHTML = '<span class="text-slate-400 font-medium">Wajib terlampir</span>';
    }

    if (cam) { cam.value = ''; cam.removeAttribute('name'); }
    if (gal) { gal.value = ''; gal.removeAttribute('name'); }
    if (satwaFoto) satwaFoto.value = '';
  }

  // ── Live Camera Modal (WebRTC) ──
  async function bukaLiveKameraModal() {
    const modal = document.getElementById('liveCameraModal');
    const video = document.getElementById('liveCameraVideo');
    const loading = document.getElementById('cameraLoadingHint');
    if (!modal || !video) return;

    modal.classList.remove('hidden');
    if (loading) loading.classList.remove('hidden');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('Peramban tidak mendukung live video stream. Menggunakan kamera HP bawaan.');
      tutupLiveKameraModal();
      document.getElementById('inputKameraBelakang').click();
      return;
    }

    try {
      if (liveCameraStream) {
        liveCameraStream.getTracks().forEach(t => t.stop());
      }
      liveCameraStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: activeFacingMode } },
        audio: false
      });
      video.srcObject = liveCameraStream;
      video.onloadedmetadata = () => {
        video.play();
        if (loading) loading.classList.add('hidden');
      };
    } catch (err) {
      console.warn('Live camera error:', err);
      if (loading) loading.classList.add('hidden');
      tutupLiveKameraModal();
      // Fallback langsung ke input kamera bawaan
      document.getElementById('inputKameraBelakang').click();
    }
  }

  function tutupLiveKameraModal() {
    const modal = document.getElementById('liveCameraModal');
    if (modal) modal.classList.add('hidden');
    if (liveCameraStream) {
      liveCameraStream.getTracks().forEach(t => t.stop());
      liveCameraStream = null;
    }
  }

  async function switchKameraLive() {
    activeFacingMode = (activeFacingMode === "environment") ? "user" : "environment";
    bukaLiveKameraModal();
  }

  function ambilFotoDariLiveVideo() {
    const video = document.getElementById('liveCameraVideo');
    const canvas = document.getElementById('liveCameraCanvas');
    if (!video || !canvas) return;

    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob((blob) => {
      if (!blob) return;
      const file = new File([blob], 'foto_kamera_' + Date.now() + '.jpg', { type: 'image/jpeg' });
      applyPhotoFile(file);
      tutupLiveKameraModal();
    }, 'image/jpeg', 0.92);
  }

  // ── Bagian 2: Data & Logika Dropdown Jenis Burung (Search Realtime & Pagination 8 Item) ──
  const listBurungMaster = [
    @foreach($satwa_list as $s)
    {
      nama: @json($s->nama),
      latin: @json($s->nama_ilmiah),
      kategori: @json($s->kategori_label),
      risiko: @json($s->risiko_label),
      foto: @json($s->foto_url ?? asset('images/biawak.jpeg')),
    },
    @endforeach
    {
      nama: "Burung Liar Lainnya (Spesies Baru)",
      latin: "Belum Terdaftar / Perlu Identifikasi",
      kategori: "Spesies Baru",
      risiko: "Spesies Baru",
      foto: "{{ asset('images/biawak.jpeg') }}",
    }
  ];

  let burungFilteredList = [...listBurungMaster];
  let burungCurrentPage = 1;
  const BURUNG_PER_PAGE = 5;

  function toggleBurungDropdown() {
    const menu = document.getElementById('menuBurungDropdown');
    const chevron = document.getElementById('chevronBurung');
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    if (isHidden) {
      menu.classList.remove('hidden');
      if (chevron) chevron.classList.add('rotate-180');
      renderBurungList();
      const searchInput = document.getElementById('inputSearchBurung');
      if (searchInput) {
        setTimeout(() => {
          searchInput.focus();
          menu.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 50);
      }
    } else {
      tutupBurungDropdown();
    }
  }

  function tutupBurungDropdown() {
    const menu = document.getElementById('menuBurungDropdown');
    const chevron = document.getElementById('chevronBurung');
    if (menu) menu.classList.add('hidden');
    if (chevron) chevron.classList.remove('rotate-180');
  }

  function onSearchBurungRealtime(query) {
    const q = (query || '').toLowerCase().trim();
    const btnReset = document.getElementById('btnResetSearchBurung');
    if (btnReset) {
      if (q) btnReset.classList.remove('hidden');
      else btnReset.classList.add('hidden');
    }

    if (!q) {
      burungFilteredList = [...listBurungMaster];
    } else {
      burungFilteredList = listBurungMaster.filter(b => {
        const n = (b.nama || '').toLowerCase();
        const l = (b.latin || '').toLowerCase();
        const k = (b.kategori || '').toLowerCase();
        return n.includes(q) || l.includes(q) || k.includes(q);
      });
    }

    burungCurrentPage = 1;
    renderBurungList();
  }

  function resetSearchBurung() {
    const searchInput = document.getElementById('inputSearchBurung');
    if (searchInput) {
      searchInput.value = '';
      searchInput.focus();
    }
    onSearchBurungRealtime('');
  }

  function changeBurungPage(delta, e) {
    if (e) {
      if (typeof e.preventDefault === 'function') e.preventDefault();
      if (typeof e.stopPropagation === 'function') e.stopPropagation();
    }
    const totalPages = Math.max(1, Math.ceil(burungFilteredList.length / BURUNG_PER_PAGE));
    const nextPage = burungCurrentPage + delta;
    if (nextPage >= 1 && nextPage <= totalPages) {
      burungCurrentPage = nextPage;
      renderBurungList();
      const container = document.getElementById('listBurungContainer');
      if (container) container.scrollTop = 0;
    }
  }

  function renderBurungList() {
    const container = document.getElementById('listBurungContainer');
    if (!container) return;

    const currentVal = document.getElementById('selectJenisBurung')?.value || '';
    const totalItems = burungFilteredList.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / BURUNG_PER_PAGE));

    if (burungCurrentPage > totalPages) {
      burungCurrentPage = totalPages;
    }

    const startIndex = (burungCurrentPage - 1) * BURUNG_PER_PAGE;
    const endIndex = Math.min(startIndex + BURUNG_PER_PAGE, totalItems);
    const pageItems = burungFilteredList.slice(startIndex, endIndex);

    if (totalItems === 0) {
      container.innerHTML = `
        <div class="py-6 px-4 text-center">
          <p class="text-xs text-slate-500 mb-2">Tidak ditemukan jenis burung dengan kata kunci tersebut.</p>
          <button type="button" 
                  onclick="pilihBurungByNama('Burung Liar Lainnya (Spesies Baru)')" 
                  class="px-3 py-1.5 rounded-lg bg-teal-50 border border-teal-200 text-teal-700 font-semibold text-xs hover:bg-teal-100 cursor-pointer transition-colors">
            Pilih sebagai "Burung Liar Lainnya (Spesies Baru)"
          </button>
        </div>
      `;
    } else {
      let html = '';
      pageItems.forEach((item) => {
        const isSelected = (currentVal === item.nama);
        const risikoBadgeColor = item.risiko === 'Tinggi' ? 'bg-rose-50 text-rose-700 border-rose-200' :
                               (item.risiko === 'Sedang' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-teal-50 text-teal-700 border-teal-200');

        const escapedNama = item.nama.replace(/'/g, "\\'");
        html += `
          <button type="button" 
                  onclick="pilihBurungByNama('${escapedNama}')" 
                  class="w-full text-left px-3.5 py-2.5 rounded-xl hover:bg-teal-50/80 active:bg-teal-100 flex items-center justify-between gap-3 transition-colors cursor-pointer ${isSelected ? 'bg-teal-50 border border-teal-300 shadow-2xs' : ''}">
            <div class="min-w-0 flex-1">
              <div class="font-bold text-xs sm:text-sm text-slate-800 truncate ${isSelected ? 'text-[#00A9C1]' : ''}">
                ${item.nama}
              </div>
              <div class="text-[11px] text-slate-500 italic truncate">
                ${item.latin}
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md border ${risikoBadgeColor}">
                ${item.risiko}
              </span>
              ${isSelected ? `
                <svg class="w-4 h-4 text-[#00A9C1]" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              ` : ''}
            </div>
          </button>
        `;
      });
      container.innerHTML = html;
    }

    // Update Footer Pagination (Slide per slide 8 jenis burung)
    const pageInfo = document.getElementById('burungPageInfo');
    const pageBadge = document.getElementById('burungCurrentPageBadge');
    const btnPrev = document.getElementById('btnBurungPrev');
    const btnNext = document.getElementById('btnBurungNext');

    if (pageInfo) {
      if (totalItems === 0) {
        pageInfo.textContent = '0 jenis ditemukan';
      } else {
        pageInfo.textContent = `Slide ${burungCurrentPage} dari ${totalPages} (${totalItems} jenis)`;
      }
    }
    if (pageBadge) {
      pageBadge.textContent = `${burungCurrentPage} / ${totalPages}`;
    }
    if (btnPrev) {
      btnPrev.disabled = (burungCurrentPage <= 1);
    }
    if (btnNext) {
      btnNext.disabled = (burungCurrentPage >= totalPages);
      const textNext = document.getElementById('btnBurungNextText') || btnNext;
      textNext.textContent = (burungCurrentPage < totalPages) 
        ? 'Slide Selanjutnya (Next) ›' 
        : 'Next ›';
    }
  }

  function pilihBurungByNama(nama) {
    const item = listBurungMaster.find(b => b.nama === nama);
    if (!item) return;

    const inputHidden = document.getElementById('selectJenisBurung');
    if (inputHidden) inputHidden.value = item.nama;

    const triggerText = document.getElementById('triggerBurungText');
    const triggerDot = document.getElementById('triggerBurungDot');

    if (triggerText) {
      triggerText.textContent = `${item.nama} (${item.latin})`;
      triggerText.classList.remove('text-slate-500');
      triggerText.classList.add('text-slate-800');
    }
    if (triggerDot) {
      triggerDot.classList.remove('bg-slate-300');
      triggerDot.classList.add('bg-[#00A9C1]');
    }

    // Tampilkan mini info card
    const pill = document.getElementById('satwaInfoPill');
    if (pill) {
      pill.classList.remove('hidden');
      const namaEl = document.getElementById('satwaPillNama');
      const latinEl = document.getElementById('satwaPillLatin');
      const risikoEl = document.getElementById('satwaPillRisiko');
      const thumbEl = document.getElementById('satwaPillThumb');

      if (namaEl) namaEl.textContent = item.nama;
      if (latinEl) latinEl.textContent = item.latin;
      if (risikoEl) risikoEl.textContent = item.risiko;
      if (thumbEl) thumbEl.src = item.foto;
    }

    tutupBurungDropdown();
  }

  // Tutup dropdown saat klik di luar container
  document.addEventListener('click', function(e) {
    // Abaikan jika target element sudah terlepas dari dokumen
    if (!document.contains(e.target)) return;

    const container = document.getElementById('dropdownBurungContainer');
    if (container && !container.contains(e.target)) {
      tutupBurungDropdown();
    }
  });

  // Inisialisasi awal jika ada input nilai yang tersimpan (old input)
  document.addEventListener('DOMContentLoaded', function() {
    const initialVal = document.getElementById('selectJenisBurung')?.value;
    if (initialVal) {
      pilihBurungByNama(initialVal);
    }
  });

  // ── Bagian 2: Stepper Jumlah Burung ──
  function stepJumlahBurung(delta) {
    const inp = document.getElementById('inputJumlahBurung');
    if (!inp) return;
    let cur = parseInt(inp.value, 10) || 1;
    let nextVal = Math.max(1, cur + delta);
    inp.value = nextVal;
  }

  // ── Bagian 2: Input Kolom Teks Grid & Sinkronisasi Peta ──
  function onGridTextInput(gridCode) {
    const val = (gridCode || '').trim().toUpperCase();
    const txt = document.getElementById('gridPillText');
    if (txt) txt.textContent = val ? `Grid ${val}` : '-';

    // Sync input hidden grid_lokasi & kode_grid
    const inputLokasi = document.getElementById('inputGridLokasi');
    if (inputLokasi) inputLokasi.value = val;

    const formKode = document.getElementById('formKodeGrid');
    if (formKode) formKode.value = val;

    const inputKode = document.getElementById('inputKodeGrid');
    if (inputKode) inputKode.value = val;

    // Trigger select on Map in Bagian 3
    if (val && typeof selectGrid === 'function') {
      selectGrid(val, true);
    }
  }

  // Callback saat peta di Bagian 3 disentuh
  function onMapGridSelected(gridCode, zoneName) {
    const inp = document.getElementById('selectGridBurung');
    if (inp) {
      inp.value = gridCode;
    }
    const formKode = document.getElementById('formKodeGrid');
    if (formKode) {
      formKode.value = gridCode;
    }
    const inputKode = document.getElementById('inputKodeGrid');
    if (inputKode) {
      inputKode.value = gridCode;
    }
    const fullText = `Grid ${gridCode}` + (zoneName ? ` — ${zoneName}` : '');
    const txt = document.getElementById('gridPillText');
    if (txt) {
      txt.textContent = fullText;
    }
  }

  function scrollKePetaBagian3() {
    const sec = document.getElementById('petaKoordinatSection');
    if (sec) {
      sec.scrollIntoView({ behavior: 'smooth' });
    }
  }

  // =============================================================================
  // REQ-FIELD-003: GPS AUTO-DETECT KE KODE GRID BANDARA (HTML5 GEOLOCATION API)
  // =============================================================================
  function triggerGpsDetection() {
    const btn = document.getElementById('btnGpsDetect');
    const icon = document.getElementById('iconGpsBtn');
    const text = document.getElementById('textGpsBtn');

    // 1. Validasi dukungan Geolocation API di browser
    if (!navigator || !('geolocation' in navigator)) {
      renderGpsStatus(
        'error',
        'Browser Tidak Mendukung Geolocation',
        'Peramban (browser) yang Anda gunakan tidak mendukung HTML5 Geolocation API. Silakan masukkan kode grid secara manual atau pilih melalui peta.',
        null
      );
      return;
    }

    // 2. Set UI ke status loading
    if (btn) {
      btn.disabled = true;
      btn.classList.add('opacity-75', 'cursor-wait');
    }
    if (icon) {
      icon.classList.add('animate-spin');
      icon.textContent = 'progress_activity';
    }
    if (text) {
      text.textContent = 'Mencari GPS...';
    }

    renderGpsStatus(
      'loading',
      'Mencari Sinyal Satelit GPS...',
      'Mengaktifkan sensor GPS gawai Anda dengan mode presisi tinggi (enableHighAccuracy)... Mohon pastikan fitur Lokasi/GPS aktif.',
      null
    );

    const geoOptions = {
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 0
    };

    navigator.geolocation.getCurrentPosition(
      function(position) {
        resetGpsButton();

        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy ? Math.round(position.coords.accuracy) : null;

        // Simpan nilai koordinat numerik ke form
        setGpsFormCoordinates(lat, lng);

        // Jalankan pemetaan Bounding Box Geofencing
        const result = typeof mapGpsToGridBoundingBox === 'function'
          ? mapGpsToGridBoundingBox(lat, lng)
          : null;

        if (!result || !result.success) {
          if (result && result.reason === 'OUT_OF_BOUNDS') {
            renderGpsStatus(
              'warning',
              'Koordinat di Luar Batas Grid Juanda',
              `Koordinat GPS terdeteksi (Lintang: <strong>${lat.toFixed(6)}</strong>, Bujur: <strong>${lng.toFixed(6)}</strong>${accuracy ? ` • Akurasi: ±${accuracy}m` : ''}), namun berada di luar batas grid sisi udara Bandara Internasional Juanda. Anda dapat memilih nomor grid secara manual atau melalui peta.`,
              { lat, lng, accuracy }
            );
          } else {
            renderGpsStatus(
              'error',
              'Gagal Memetakan Grid',
              `Koordinat GPS terdeteksi (${lat.toFixed(6)}, ${lng.toFixed(6)}), namun terjadi kendala saat pemetaan batas grid. Silakan tentukan nomor grid secara manual.`,
              { lat, lng, accuracy }
            );
          }
          return;
        }

        // Sukses memetakan ke grid bandara
        const matchedGrid = result.code;
        setGpsGridResult(matchedGrid);

        renderGpsStatus(
          'success',
          `✓ GPS Terkunci — Grid ${matchedGrid}`,
          `Lintang: <strong>${lat.toFixed(6)}</strong> • Bujur: <strong>${lng.toFixed(6)}</strong>${accuracy ? ` (Akurasi: ±${accuracy}m)` : ''}. Kode grid <strong>${matchedGrid}</strong> otomatis diterapkan ke form dan peta.`,
          { lat, lng, accuracy, grid: matchedGrid }
        );

        // Sinkronkan input Bagian 2
        const inputBurung = document.getElementById('selectGridBurung');
        if (inputBurung) {
          inputBurung.value = matchedGrid;
          inputBurung.dispatchEvent(new Event('input', { bubbles: true }));
        }

        // Sinkronkan ke Peta Interaktif Bagian 3
        if (typeof selectGrid === 'function') {
          selectGrid(matchedGrid, true);
        }
      },
      function(err) {
        resetGpsButton();

        let title = 'Gagal Mendeteksi GPS';
        let message = 'Terjadi kendala saat mengakses sensor GPS perangkat.';

        switch (err.code) {
          case err.PERMISSION_DENIED:
            title = 'Izin Akses GPS Ditolak';
            message = 'Pengguna menolak izin akses lokasi GPS pada peramban. Silakan aktifkan izin lokasi di pengaturan browser (ikon gembok/pengaturan situs di address bar) untuk mendeteksi posisi otomatis.';
            break;
          case err.POSITION_UNAVAILABLE:
            title = 'Sinyal GPS Tidak Tersedia';
            message = 'Perangkat tidak dapat menentukan posisi. Pastikan fitur GPS/Lokasi perangkat Anda aktif dan memiliki sinyal yang memadai.';
            break;
          case err.TIMEOUT:
            title = 'Deteksi GPS Timeout (Waktu Habis)';
            message = 'Waktu pencarian sinyal satelit GPS melebihi batas waktu (timeout). Pastikan Anda berada di ruang terbuka bebas hambatan dan coba tekan kembali tombol Deteksi Lokasi GPS.';
            break;
        }

        renderGpsStatus('error', title, message, null);
      },
      geoOptions
    );
  }

  function resetGpsButton() {
    const btn = document.getElementById('btnGpsDetect');
    const icon = document.getElementById('iconGpsBtn');
    const text = document.getElementById('textGpsBtn');
    if (btn) {
      btn.disabled = false;
      btn.classList.remove('opacity-75', 'cursor-wait');
    }
    if (icon) {
      icon.classList.remove('animate-spin');
      icon.textContent = 'my_location';
    }
    if (text) {
      text.textContent = 'Deteksi Lokasi GPS';
    }
  }

  function setGpsFormCoordinates(lat, lng) {
    const fLat = parseFloat(lat).toFixed(6);
    const fLng = parseFloat(lng).toFixed(6);

    const formLat = document.getElementById('formGpsLat');
    const formLng = document.getElementById('formGpsLng');
    const pickerLat = document.getElementById('inputLatitude');
    const pickerLng = document.getElementById('inputLongitude');
    const inputGpsLat = document.getElementById('inputGpsLat');
    const inputGpsLng = document.getElementById('inputGpsLng');

    if (formLat) formLat.value = fLat;
    if (formLng) formLng.value = fLng;
    if (pickerLat) pickerLat.value = fLat;
    if (pickerLng) pickerLng.value = fLng;
    if (inputGpsLat) inputGpsLat.value = fLat;
    if (inputGpsLng) inputGpsLng.value = fLng;
  }

  function setGpsGridResult(gridCode) {
    const formKodeGrid = document.getElementById('formKodeGrid');
    const inputKodeGrid = document.getElementById('inputKodeGrid');
    const inputGridLokasi = document.getElementById('inputGridLokasi');
    const selectGridBurung = document.getElementById('selectGridBurung');

    if (formKodeGrid) formKodeGrid.value = gridCode;
    if (inputKodeGrid) inputKodeGrid.value = gridCode;
    if (inputGridLokasi) inputGridLokasi.value = gridCode;
    if (selectGridBurung) selectGridBurung.value = gridCode;
  }

  function renderGpsStatus(type, title, message, details) {
    const container = document.getElementById('gpsStatusContainer');
    if (!container) return;

    let bgClass = 'bg-slate-50 border-slate-200 text-slate-700';
    let iconName = 'info';
    let iconClass = 'text-slate-500';

    if (type === 'loading') {
      bgClass = 'bg-cyan-50/90 border-cyan-200 text-cyan-900';
      iconName = 'progress_activity';
      iconClass = 'text-[#00A9C1] animate-spin';
    } else if (type === 'success') {
      bgClass = 'bg-emerald-50 border-emerald-200 text-emerald-900';
      iconName = 'check_circle';
      iconClass = 'text-emerald-600';
    } else if (type === 'warning') {
      bgClass = 'bg-amber-50 border-amber-200 text-amber-900';
      iconName = 'warning';
      iconClass = 'text-amber-600';
    } else if (type === 'error') {
      bgClass = 'bg-rose-50 border-rose-200 text-rose-900';
      iconName = 'error';
      iconClass = 'text-rose-600';
    }

    container.innerHTML = `
      <div class="p-3 rounded-xl border ${bgClass} transition-all duration-300 flex items-start gap-2.5 shadow-2xs">
        <span class="material-symbols-outlined text-lg ${iconClass} shrink-0 mt-0.5">${iconName}</span>
        <div class="min-w-0 flex-1">
          <div class="font-bold text-xs flex items-center justify-between gap-2 flex-wrap">
            <span>${title}</span>
            ${details && details.grid ? `<span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-mono font-bold text-[11px]">GRID ${details.grid}</span>` : ''}
          </div>
          <div class="text-[11px] mt-0.5 leading-relaxed opacity-90">${message}</div>
        </div>
      </div>
    `;
  }

  // ── Form Validation & Pre-submit Sync ──
  document.getElementById('mainForm').addEventListener('submit', function(e) {
    const jenisBurung = document.getElementById('selectJenisBurung');
    if (jenisBurung && !jenisBurung.value.trim()) {
      e.preventDefault();
      alert('Silakan pilih jenis burung terlebih dahulu.');
      const btn = document.getElementById('btnTriggerBurung');
      if (btn) {
        btn.focus();
        btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }

    const jumlahBurung = document.getElementById('inputJumlahBurung');
    if (jumlahBurung && (parseInt(jumlahBurung.value, 10) < 1 || !jumlahBurung.value)) {
      jumlahBurung.value = 1;
    }

    const gridBurung = document.getElementById('selectGridBurung');
    if (gridBurung && !gridBurung.value.trim()) {
      e.preventDefault();
      alert('Silakan tentukan Grid lokasi pengamatan.');
      gridBurung.focus();
      gridBurung.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    // Peringatan ramah jika user belum mengambil/upload foto burung
    if (!hasBirdPhoto) {
      const confirmNoPhoto = confirm('Anda belum mengambil atau mengunggah foto burung. Apakah Anda yakin ingin melanjutkan tanpa foto?');
      if (!confirmNoPhoto) {
        e.preventDefault();
        document.getElementById('fotoPreviewContainer').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }
    }

    // Default deskripsi ciri morfologi jika kosong
    const ta = document.querySelector('textarea[name="ciri_ukuran"]');
    if (ta && !ta.value.trim() && jenisBurung && jenisBurung.value) {
      ta.value = 'Pengamatan burung ' + jenisBurung.value + ' di ' + (gridBurung ? ('Grid ' + gridBurung.value) : 'area sisi udara.');
    }

    // Signature verification
    if (signaturePad.isEmpty()) {
      e.preventDefault();
      alert('Mohon bubuhkan tanda tangan digital Anda terlebih dahulu.');
      document.getElementById('signature-pad').scrollIntoView({ behavior: 'smooth' });
      return;
    }

    document.getElementById('tanda_tangan').value = signaturePad.toDataURL('image/png');
  });
</script>
@endsection
