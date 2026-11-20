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
      <a href="' . (auth()->user()->role === 'admin' ? route('admin.dashboard') : route('user.dashboard')) . '" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200/90 bg-white text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
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
      <input type="hidden" name="gps_lat" id="formGpsLat" value="{{ old('gps_lat', old('latitude', '-7.383750')) }}">
      <input type="hidden" name="gps_lng" id="formGpsLng" value="{{ old('gps_lng', old('longitude', '112.784828')) }}">

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
                <div class="w-full max-h-[340px] rounded-xl overflow-hidden bg-slate-950 border border-slate-200 flex items-center justify-center shadow-inner relative group">
                  <img id="fotoPreviewImg" src="" alt="Preview Foto Watermark Dinas" class="w-full max-h-[340px] object-contain">
                  <div class="absolute top-2 left-2 bg-slate-900/80 backdrop-blur-md px-2.5 py-1 rounded-lg border border-teal-500/40 text-[10px] font-bold text-teal-300 flex items-center gap-1 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                    <span>Watermark Dinas Permanen</span>
                  </div>
                </div>
                
                <!-- Metadata Card Bawah Foto -->
                <div class="w-full mt-3 pt-2.5 border-t border-slate-200 flex flex-col gap-2 text-left">
                  <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div class="min-w-0 flex-1">
                      <div id="fotoNamaFile" class="font-bold text-xs text-slate-800 truncate">foto_dinas.jpg</div>
                      <div id="fotoUkuranFile" class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5 flex-wrap">
                        <span id="fotoUkuranText" class="font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Ukuran: - KB (&lt; 1 MB)</span>
                        <span id="fotoDimensiText" class="font-mono text-slate-500">Resolusi: -</span>
                      </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                      <button type="button" onclick="klikAmbilFoto()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold cursor-pointer transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                        <span>Ulangi / Ganti Foto</span>
                      </button>
                      <button type="button" onclick="hapusFotoBurung()" class="px-2.5 py-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">delete</span>
                        <span>Hapus</span>
                      </button>
                    </div>
                  </div>

                  <!-- Ringkasan Stempel Dinas Terverifikasi (Waktu, GPS, Unit) -->
                  <div id="fotoWatermarkSummaryPill" class="bg-slate-50 rounded-xl p-2.5 border border-slate-200 text-[11px] text-slate-600 grid grid-cols-1 sm:grid-cols-3 gap-1.5 font-medium">
                    <div class="flex items-center gap-1 truncate">
                      <span class="text-slate-400">🕒</span>
                      <span id="fotoSummaryWaktu" class="truncate">-</span>
                    </div>
                    <div class="flex items-center gap-1 truncate">
                      <span class="text-teal-600 font-bold">📍</span>
                      <span id="fotoSummaryGps" class="truncate font-mono">GPS: -</span>
                    </div>
                    <div class="flex items-center gap-1 truncate">
                      <span class="text-slate-400">🏢</span>
                      <span id="fotoSummaryUnit" class="truncate">{{ $unitKerjaUser ?: ($user->namalengkap ?: $user->username) }}</span>
                    </div>
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

            <!-- Tombol Identifikasi Satwa via AI (Gemini Vision) -->
            <div class="mt-2.5">
              <button type="button" 
                      id="btnAiIdentifySpecies" 
                      onclick="runAiSpeciesIdentification()" 
                      class="w-full py-2.5 px-3 rounded-xl border border-cyan-300 bg-cyan-50/80 hover:bg-cyan-100 text-[#007fa3] font-bold text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer shadow-2xs transition-all active:scale-[0.98] select-none"
                      title="Identifikasi spesies satwa otomatis dan dapatkan rekomendasi SOP pengusiran ICAO menggunakan AI Gemini Vision">
                <span class="material-symbols-outlined text-lg text-[#00A9C1]" id="iconAiIdentify">auto_awesome</span>
                <span id="textAiIdentify">Identifikasi Satwa via AI (Gemini Vision)</span>
              </button>
            </div>

            <!-- Panel Hasil Identifikasi AI -->
            <x-ai.species-result />

            <!-- Hidden Inputs untuk AI Identifikasi -->
            <input type="hidden" name="ai_identifikasi_id" id="aiIdentifikasiId" value="">
            <input type="hidden" name="satwa[0][nama_latin]" id="aiInputNamaLatin" value="">
            <input type="hidden" name="satwa[0][sumber_identifikasi]" id="aiInputSumberIdentifikasi" value="manual">

            <!-- Hidden File Inputs (Tersembunyi secara aman tanpa display:none agar kompatibel di iOS/Android & Desktop) -->
            <input type="file" id="inputKameraBelakang" accept="image/*" capture="environment" class="sr-only" style="position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; left: -9999px;" onchange="handleFileSelected(this)">
            <input type="file" id="inputGaleriFoto" accept="image/*" class="sr-only" style="position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; left: -9999px;" onchange="handleFileSelected(this)">
            <input type="file" name="satwa[0][foto]" id="formSatwaFotoInput" class="sr-only" style="position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; left: -9999px;" accept="image/*">
            <input type="file" name="foto_lainnya[]" id="formFotoLainnyaInput" class="sr-only" style="position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; left: -9999px;" accept="image/*">

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

          <!-- 4. Pilihan Grid (Kolom Input Teks Kosong) -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label for="selectGridBurung" class="block text-xs font-bold text-slate-700">Pilihan Grid <span class="text-rose-500">*</span></label>
              <button type="button" onclick="scrollKePetaBagian3()" class="text-xs font-semibold text-[#00A9C1] hover:underline flex items-center gap-1 cursor-pointer">
                <span>🗺 Lihat / Pilih di Peta</span>
              </button>
            </div>
            
            <input type="text" 
                   name="satwa[0][lokasi][0][grid]" 
                   id="selectGridBurung" 
                   value="{{ old('satwa.0.lokasi.0.grid', '') }}" 
                   placeholder="Ketik nomor grid (misal: K-10, D-9) atau klik peta di bawah" 
                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:outline-none focus:ring-2 focus:ring-[#00A9C1] focus:border-[#00A9C1] transition-all uppercase" 
                   oninput="onGridTextInput(this.value)" 
                   required>

            <div id="gridPillSummary" class="text-xs text-slate-500 mt-1.5 flex items-center gap-1">
              <span>Grid Terpilih: <strong id="gridPillText" class="text-slate-800 font-semibold">{{ old('satwa.0.lokasi.0.grid') ? ('Grid ' . old('satwa.0.lokasi.0.grid')) : '-' }}</strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Camera Viewfinder & Watermark Modal (REQ-FIELD-004) -->
      <div id="liveCameraModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col">
          
          <!-- STATE 1: VIEWFINDER (Live Streaming Kamera & Pengambilan Foto) -->
          <div id="cameraViewfinderState" class="flex flex-col w-full">
            <div class="px-4 py-3 bg-slate-800/90 border-b border-slate-700 flex items-center justify-between text-white">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                <span class="font-bold text-xs sm:text-sm">Kamera Pengamatan Dinas - Sisi Udara</span>
              </div>
              <button type="button" onclick="tutupLiveKameraModal()" class="text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer" title="Tutup Kamera">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            
            <div class="relative bg-black flex items-center justify-center min-h-[300px] sm:min-h-[360px] max-h-[65vh] overflow-hidden">
              <video id="liveCameraVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
              
              <!-- Reticle Target Viewfinder (Panduan Bidik Tengah) -->
              <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div class="w-48 h-48 border border-white/30 rounded-2xl relative flex items-center justify-center">
                  <div class="w-2.5 h-2.5 rounded-full bg-teal-400/80"></div>
                  <!-- Corner ticks -->
                  <div class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2 border-teal-400"></div>
                  <div class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2 border-teal-400"></div>
                  <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2 border-teal-400"></div>
                  <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2 border-teal-400"></div>
                </div>
              </div>

              <!-- Top Floating Badges (GPS & Unit Info Live) -->
              <div class="absolute top-3 inset-x-3 flex items-center justify-between gap-2 pointer-events-none">
                <div class="pointer-events-auto flex items-center gap-1.5 bg-slate-900/80 backdrop-blur-md px-2.5 py-1 rounded-lg border border-teal-500/40 text-[10px] sm:text-xs text-white">
                  <span id="cameraGpsDot" class="w-2 h-2 rounded-full bg-amber-400 shrink-0 animate-pulse"></span>
                  <span id="cameraGpsText" class="font-mono truncate max-w-[170px] sm:max-w-xs">GPS: Mengambil lokasi...</span>
                  <button type="button" onclick="requestGpsLocation(true)" class="p-0.5 hover:text-teal-300 cursor-pointer" title="Perbarui Koordinat GPS">
                    <span class="material-symbols-outlined text-xs">autorenew</span>
                  </button>
                </div>
                <div class="bg-slate-900/80 backdrop-blur-md px-2.5 py-1 rounded-lg border border-slate-700 text-[10px] sm:text-xs text-slate-300 font-semibold truncate max-w-[140px]">
                  {{ $unitKerjaUser ?: ($user->namalengkap ?: $user->username) }}
                </div>
              </div>

              <!-- Loading Indicator -->
              <div id="cameraLoadingHint" class="absolute inset-0 flex flex-col items-center justify-center text-white text-xs bg-slate-900 gap-2">
                <svg class="w-8 h-8 animate-spin text-teal-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Mengaktifkan kamera live...</span>
              </div>

              <!-- Error / Permission Denied Fallback State -->
              <div id="cameraErrorHint" class="hidden absolute inset-0 flex flex-col items-center justify-center text-white text-xs bg-slate-900 p-6 text-center gap-3">
                <span class="material-symbols-outlined text-rose-400 text-3xl">videocam_off</span>
                <div class="font-bold text-sm text-slate-100">Kamera Live Tidak Dapat Diakses</div>
                <p class="text-xs text-slate-400 max-w-xs leading-relaxed">Izin kamera belum aktif atau browser membatasi WebRTC. Anda tetap bisa langsung membuka kamera bawaan perangkat.</p>
                <div class="flex items-center gap-2 mt-2">
                  <button type="button" onclick="bukaKameraBawaan()" class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs flex items-center gap-1.5 cursor-pointer shadow-md">
                    <span class="material-symbols-outlined text-sm">photo_camera</span>
                    <span>Buka Kamera HP / Perangkat</span>
                  </button>
                  <button type="button" onclick="tutupLiveKameraModal()" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs cursor-pointer">
                    Tutup
                  </button>
                </div>
              </div>
            </div>

            <div class="p-4 bg-slate-900 border-t border-slate-800 flex flex-col gap-3">
              <div class="flex items-center justify-around w-full">
                <!-- Switch Front/Back Camera -->
                <button type="button" onclick="switchKameraLive()" id="btnSwitchCamera" class="p-3 rounded-full bg-slate-800 hover:bg-slate-700 text-white text-xs flex items-center justify-center cursor-pointer transition-colors" title="Putar Kamera Depan / Belakang">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>

                <!-- Capture Shutter Button -->
                <button type="button" onclick="ambilFotoDariLiveVideo()" class="w-16 h-16 rounded-full bg-white border-4 border-teal-500 shadow-xl active:scale-90 transition-transform flex items-center justify-center text-teal-600 cursor-pointer" title="Ambil Foto">
                  <div class="w-12 h-12 rounded-full bg-teal-500 hover:bg-teal-600 transition-colors flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-2xl">photo_camera</span>
                  </div>
                </button>

                <!-- Close Button -->
                <button type="button" onclick="tutupLiveKameraModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold cursor-pointer transition-colors">
                  Tutup
                </button>
              </div>

              <!-- Alternate link for Mobile: Use native phone camera app directly -->
              <div class="text-center pt-1 border-t border-slate-800/80">
                <button type="button" onclick="bukaKameraBawaan()" class="text-[11px] text-cyan-400 hover:text-cyan-300 underline font-medium cursor-pointer">
                  Atau gunakan aplikasi kamera bawaan HP ›
                </button>
              </div>
            </div>
          </div>

          <!-- STATE 2: REVIEW (Preview Watermark Dinas Permanen & Kompresi < 1 MB) -->
          <div id="cameraReviewState" class="hidden flex flex-col w-full">
            <div class="px-4 py-3 bg-slate-800/90 border-b border-slate-700 flex items-center justify-between text-white">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-teal-400 text-lg">verified</span>
                <span class="font-bold text-xs sm:text-sm">Preview Foto Dinas & Watermark</span>
              </div>
              <button type="button" onclick="tutupLiveKameraModal()" class="text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer" title="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>

            <!-- Preview Image with Burned-in Watermark -->
            <div class="relative bg-slate-950 flex items-center justify-center min-h-[260px] sm:min-h-[320px] max-h-[55vh] p-2 overflow-hidden">
              <img id="cameraReviewImg" src="" alt="Hasil Foto Dinas" class="max-h-[50vh] w-auto max-w-full object-contain rounded-lg shadow-2xl border border-slate-800">
            </div>

            <!-- Inspection & Verification Card -->
            <div class="p-3.5 bg-slate-900 border-t border-slate-800 flex flex-col gap-2.5">
              <div class="bg-slate-800/90 rounded-xl p-3 border border-slate-700/80 text-xs space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-slate-400">Ukuran Akhir:</span>
                  <span id="reviewFileSizeBadge" class="font-bold text-emerald-400 bg-emerald-950/70 border border-emerald-500/40 px-2 py-0.5 rounded-md">- KB (&lt; 1 MB OK)</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400">Format & Resolusi:</span>
                  <span id="reviewResolutionBadge" class="font-mono text-slate-200 text-[11px]">- px • JPEG</span>
                </div>
                <div class="pt-2 border-t border-slate-700/60 grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px]">
                  <div>
                    <span class="text-slate-400 block text-[10px]">Waktu Pengambilan:</span>
                    <span id="reviewWaktuText" class="text-slate-200 font-semibold truncate block">-</span>
                  </div>
                  <div>
                    <span class="text-slate-400 block text-[10px]">Koordinat GPS:</span>
                    <span id="reviewGpsText" class="text-teal-400 font-mono font-semibold block truncate">-</span>
                  </div>
                  <div>
                    <span class="text-slate-400 block text-[10px]">Unit Pelapor:</span>
                    <span id="reviewUnitText" class="text-slate-200 font-medium block truncate">{{ $unitKerjaUser ?: ($user->namalengkap ?: $user->username) }}</span>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-between gap-3 pt-1">
                <button type="button" onclick="ulangiFotoKamera()" class="flex-1 py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 cursor-pointer transition-colors border border-slate-700">
                  <span class="material-symbols-outlined text-base">refresh</span>
                  <span>Ulangi Foto</span>
                </button>
                <button type="button" onclick="gunakanFotoKamera()" style="background: linear-gradient(135deg, #00A9C1 0%, #007fa3 100%) !important; color: #ffffff !important;" class="flex-1 py-3 px-4 rounded-xl text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 cursor-pointer shadow-lg active:scale-[0.98] transition-all">
                  <span class="material-symbols-outlined text-base" style="color: #ffffff !important;">check_circle</span>
                  <span style="color: #ffffff !important;">Gunakan Foto</span>
                </button>
              </div>
            </div>
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

      <!-- Tombol Submit Utama di Bawah -->
      <div class="pt-4 pb-16">
        <button type="submit" id="btnSubmitLaporan" style="background: linear-gradient(135deg, #00A9C1 0%, #007fa3 100%) !important; color: #ffffff !important; box-shadow: 0 10px 25px -5px rgba(0, 169, 193, 0.4), 0 8px 10px -6px rgba(0, 169, 193, 0.3) !important;" class="w-full py-4 px-6 text-white font-bold text-base rounded-2xl active:scale-[0.99] hover:opacity-95 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 select-none">
          <span class="material-symbols-outlined text-xl" style="color: #ffffff !important;">send</span>
          <span style="color: #ffffff !important;">Kirim Laporan Logbook</span>
        </button>
        <p class="text-center text-xs text-slate-400 mt-2">Pastikan seluruh data pengamatan dan tanda tangan digital telah terisi dengan benar sebelum mengirim.</p>
      </div>
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

  // ── Bagian 2: FITUR KAMERA WATERMARK DINAS OTOMATIS (REQ-FIELD-004) ──
  const AUTH_UNIT_PELAPOR = @json($unitKerjaUser ?: ($user->namalengkap ?: $user->username));
  let hasBirdPhoto = false;
  let liveCameraStream = null;
  let activeFacingMode = "environment"; // Prioritaskan kamera belakang di smartphone
  let currentGpsData = {
    lat: '-7.383750',
    lng: '112.784828',
    isRealDevice: false,
    statusText: 'Standar Juanda'
  };
  let pendingProcessedPhoto = null; // Menyimpan { blob, file, metadata, width, height }

  // 1. Geolocation Integration (Mengambil koordinat nyata gawai dengan akurasi tinggi)
  function requestGpsLocation(force = false) {
    const dot = document.getElementById('cameraGpsDot');
    const txt = document.getElementById('cameraGpsText');

    if (!navigator.geolocation) {
      syncWithFormCoordinates('GPS tidak didukung browser');
      return;
    }

    if (dot) {
      dot.className = 'w-2 h-2 rounded-full bg-amber-400 shrink-0 animate-pulse';
    }
    if (txt) {
      txt.textContent = 'GPS: Mendeteksi sinyal satelit...';
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude.toFixed(6);
        const lng = position.coords.longitude.toFixed(6);
        currentGpsData = {
          lat: lat,
          lng: lng,
          isRealDevice: true,
          statusText: 'GPS Aktif'
        };

        // Sinkronisasi ke hidden form
        const fLat = document.getElementById('formGpsLat');
        const fLng = document.getElementById('formGpsLng');
        if (fLat) fLat.value = lat;
        if (fLng) fLng.value = lng;

        const iLat = document.getElementById('inputLatitude');
        const iLng = document.getElementById('inputLongitude');
        if (iLat) iLat.value = lat;
        if (iLng) iLng.value = lng;

        if (dot) {
          dot.className = 'w-2 h-2 rounded-full bg-emerald-400 shrink-0';
        }
        if (txt) {
          txt.textContent = `GPS: ${lat}, ${lng}`;
        }
      },
      (error) => {
        console.warn('Geolocation gawai belum tersedia / izin ditolak:', error.message);
        syncWithFormCoordinates('Menggunakan Koordinat Grid');
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 15000
      }
    );
  }

  function syncWithFormCoordinates(statusNote) {
    const fLat = document.getElementById('formGpsLat')?.value || document.getElementById('inputLatitude')?.value || '-7.383750';
    const fLng = document.getElementById('formGpsLng')?.value || document.getElementById('inputLongitude')?.value || '112.784828';
    currentGpsData = {
      lat: fLat,
      lng: fLng,
      isRealDevice: false,
      statusText: statusNote || 'Koordinat Grid'
    };

    const dot = document.getElementById('cameraGpsDot');
    const txt = document.getElementById('cameraGpsText');
    if (dot) {
      dot.className = 'w-2 h-2 rounded-full bg-teal-400 shrink-0';
    }
    if (txt) {
      txt.textContent = `GPS: ${fLat}, ${fLng}`;
    }
  }

  // Panggil deteksi lokasi saat halaman dimuat
  document.addEventListener('DOMContentLoaded', () => {
    requestGpsLocation(false);
  });

  // 2. Format Timestamp Resmi WIB (Asia/Jakarta)
  function getWibTimestamp(date = new Date()) {
    const bulanIndo = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    // Menghitung waktu zona WIB (UTC+7)
    const utcMs = date.getTime() + (date.getTimezoneOffset() * 60000);
    const wibDate = new Date(utcMs + (3600000 * 7));

    const tgl = wibDate.getDate();
    const bln = bulanIndo[wibDate.getMonth()];
    const thn = wibDate.getFullYear();
    const jam = String(wibDate.getHours()).padStart(2, '0');
    const mnt = String(wibDate.getMinutes()).padStart(2, '0');
    const dtk = String(wibDate.getSeconds()).padStart(2, '0');

    return `${tgl} ${bln} ${thn} | ${jam}:${mnt}:${dtk} WIB`;
  }

  // 3. Mengambil Data Metadata Lengkap untuk Stempel Dinas (Hanya Waktu, GPS Aktual, dan Unit Pelapor - TANPA KODE GRID)
  function getWatermarkMetadata(customDate = new Date()) {
    // GPS: Ambil Latitude dan Longitude aktual (format 6 angka di belakang koma)
    let lat = currentGpsData.lat || document.getElementById('formGpsLat')?.value || '-7.383750';
    let lng = currentGpsData.lng || document.getElementById('formGpsLng')?.value || '112.784828';

    if (!isNaN(parseFloat(lat))) lat = parseFloat(lat).toFixed(6);
    if (!isNaN(parseFloat(lng))) lng = parseFloat(lng).toFixed(6);

    const gpsDisplay = `${lat}, ${lng}`;

    // Unit Pelapor: Diambil otomatis dari profil pengguna yang login / unit kerja
    const unitSelect = document.querySelector('select[name="unit_kerja"]');
    const unit = (unitSelect && unitSelect.value) ? unitSelect.value : AUTH_UNIT_PELAPOR;

    return {
      waktu: getWibTimestamp(customDate),
      gps: gpsDisplay,
      lat: lat,
      lng: lng,
      unit: unit
    };
  }

  // 4. Render Stempel Watermark Dinas Resmi ke HTML5 Canvas (Waktu, GPS, Unit - TANPA KODE GRID)
  function drawDinasWatermark(canvas, ctx, width, height, meta) {
    // Hitung skala teks proporsional sesuai resolusi gambar foto
    const scale = Math.max(0.65, Math.min(2.5, width / 1200));
    const titleSize = Math.max(15, Math.round(20 * scale));
    const textSize = Math.max(12, Math.round(16 * scale));
    const lineSpacing = Math.round(textSize * 1.50);
    const padX = Math.round(26 * scale);
    const padY = Math.round(20 * scale);

    const titleText = "DOKUMENTASI LAPORAN DINAS";
    const lineWaktu = `Waktu : ${meta.waktu}`;
    const lineGps   = `GPS   : ${meta.gps}`;
    const lineUnit  = `Unit  : ${meta.unit}`;

    // Total tinggi banner watermark hitam transparan (3 baris informasi dinas: Waktu, GPS, Unit)
    const bannerH = padY * 2 + titleSize + (lineSpacing * 3) + Math.round(10 * scale);
    const bannerTop = height - bannerH;

    ctx.save();

    // Background hitam transparan (65–75% opacity)
    ctx.fillStyle = 'rgba(15, 23, 42, 0.72)';
    ctx.fillRect(0, bannerTop, width, bannerH);

    // Garis aksen cyan InJourney di tepi atas banner watermark
    ctx.fillStyle = '#00A9C1';
    ctx.fillRect(0, bannerTop, width, Math.max(2, Math.round(3.5 * scale)));

    // Judul Dokumentasi Dinas (Bold)
    let curY = bannerTop + padY + titleSize;
    ctx.font = `bold ${titleSize}px "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, sans-serif`;
    ctx.fillStyle = '#FFFFFF';
    ctx.shadowColor = 'rgba(0, 0, 0, 0.75)';
    ctx.shadowBlur = 4;
    ctx.shadowOffsetX = 1;
    ctx.shadowOffsetY = 1;
    ctx.fillText(titleText, padX, curY);

    // Garis pemisah visual tipis di bawah judul
    curY += Math.round(8 * scale);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.28)';
    ctx.lineWidth = Math.max(1, Math.round(1.2 * scale));
    ctx.beginPath();
    ctx.moveTo(padX, curY);
    ctx.lineTo(Math.min(width - padX, padX + Math.round(360 * scale)), curY);
    ctx.stroke();

    // Rincian 3 Butir Wajib: Waktu, GPS, Unit (TANPA GRID)
    ctx.font = `500 ${textSize}px "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, monospace, sans-serif`;
    ctx.fillStyle = '#F8FAFC'; // Putih terang (Slate-50)
    ctx.shadowBlur = 3;

    curY += lineSpacing - Math.round(2 * scale);
    ctx.fillText(lineWaktu, padX, curY);

    curY += lineSpacing;
    ctx.fillText(lineGps, padX, curY);

    curY += lineSpacing;
    ctx.fillText(lineUnit, padX, curY);

    ctx.restore();
  }

  // 5. Kompresi Bertahap HTML5 Canvas (< 1 MB / < 1.000.000 byte)
  async function processAndCompressWatermark(sourceMedia, meta) {
    const origW = sourceMedia.videoWidth || sourceMedia.naturalWidth || sourceMedia.width;
    const origH = sourceMedia.videoHeight || sourceMedia.naturalHeight || sourceMedia.height;

    let scale = 1.0;
    let quality = 0.85; // Mulai dari 0.85 sesuai ketentuan REQ-FIELD-004
    const TARGET_MAX_BYTES = 1000000; // < 1 MB
    let resultBlob = null;
    let finalCanvas = null;

    const offscreenCanvas = document.createElement('canvas');
    const ctx = offscreenCanvas.getContext('2d');

    for (let attempt = 0; attempt < 8; attempt++) {
      const curW = Math.round(origW * scale);
      const curH = Math.round(origH * scale);

      offscreenCanvas.width = curW;
      offscreenCanvas.height = curH;

      // Render citra sumber (video atau image)
      ctx.drawImage(sourceMedia, 0, 0, curW, curH);

      // Bubuhkan watermark dinas resmi permanen
      drawDinasWatermark(offscreenCanvas, ctx, curW, curH, meta);

      // Konversi Canvas menjadi Blob JPEG
      resultBlob = await new Promise((resolve) => {
        offscreenCanvas.toBlob(resolve, 'image/jpeg', quality);
      });

      finalCanvas = offscreenCanvas;

      if (resultBlob && resultBlob.size < TARGET_MAX_BYTES) {
        // Berhasil di bawah 1 MB!
        break;
      }

      // Jika masih >= 1 MB, turunkan kualitas bertahap
      if (quality > 0.65) {
        quality -= 0.08;
      } else {
        // Jika kualitas sudah 0.65 masih > 1 MB, kurangi resolusi bertahap
        scale *= 0.85;
        quality = 0.80; // Reset kualitas sedikit saat resolusi diturunkan
      }
    }

    if (!resultBlob || resultBlob.size >= TARGET_MAX_BYTES) {
      alert('Peringatan: Foto melebihi batas 1 MB setelah kompresi. Silakan ambil foto ulang.');
      return null;
    }

    return {
      blob: resultBlob,
      width: finalCanvas.width,
      height: finalCanvas.height,
      size: resultBlob.size,
      quality: quality
    };
  }

  // 6. Alur Buka & Kontrol Kamera
  function klikAmbilFoto() {
    const hasMediaDevices = !!(navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function');
    if (hasMediaDevices) {
      bukaLiveKameraModal();
    } else {
      bukaKameraBawaan();
    }
  }

  function bukaKameraBawaan() {
    tutupLiveKameraModal();
    const camInput = document.getElementById('inputKameraBelakang');
    if (camInput) {
      camInput.value = '';
      camInput.click();
    }
  }

  async function bukaLiveKameraModal() {
    const modal = document.getElementById('liveCameraModal');
    const video = document.getElementById('liveCameraVideo');
    const loading = document.getElementById('cameraLoadingHint');
    const errorHint = document.getElementById('cameraErrorHint');
    if (!modal || !video) return;

    // Reset view states: Tampilkan Viewfinder, sembunyikan Review
    setCameraModalState('viewfinder');

    modal.classList.remove('hidden');
    if (loading) loading.classList.remove('hidden');
    if (errorHint) errorHint.classList.add('hidden');

    // Update GPS live
    requestGpsLocation(false);

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      bukaKameraBawaan();
      return;
    }

    if (liveCameraStream) {
      try { liveCameraStream.getTracks().forEach(t => t.stop()); } catch (e) {}
      liveCameraStream = null;
    }

    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    let stream = null;

    // 1. Percobaan Pertama: Resolusi ideal & orientasi kamera belakang (environment)
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: isMobile ? { ideal: activeFacingMode } : { ideal: 'user' },
          width: { ideal: 1920, min: 640 },
          height: { ideal: 1080, min: 480 }
        },
        audio: false
      });
    } catch (err1) {
      console.warn('Percobaan kamera HD gagal, mencoba fallback standar:', err1);
    }

    // 2. Percobaan Kedua: Fallback ke video: true murni
    if (!stream) {
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: true,
          audio: false
        });
      } catch (err2) {
        console.warn('Percobaan kamera standar gagal:', err2);
      }
    }

    if (stream) {
      liveCameraStream = stream;
      video.srcObject = stream;
      video.muted = true;
      video.onloadedmetadata = () => {
        video.play().catch(e => console.warn('Autoplay video dicegah:', e));
        if (loading) loading.classList.add('hidden');
      };
    } else {
      if (loading) loading.classList.add('hidden');
      if (errorHint) {
        errorHint.classList.remove('hidden');
      } else {
        bukaKameraBawaan();
      }
    }
  }

  function tutupLiveKameraModal() {
    const modal = document.getElementById('liveCameraModal');
    if (modal) modal.classList.add('hidden');
    if (liveCameraStream) {
      try { liveCameraStream.getTracks().forEach(t => t.stop()); } catch (e) {}
      liveCameraStream = null;
    }
    pendingProcessedPhoto = null;
  }

  async function switchKameraLive() {
    activeFacingMode = (activeFacingMode === "environment") ? "user" : "environment";
    await bukaLiveKameraModal();
  }

  function setCameraModalState(state) {
    const viewState = document.getElementById('cameraViewfinderState');
    const reviewState = document.getElementById('cameraReviewState');
    if (state === 'review') {
      if (viewState) viewState.classList.add('hidden');
      if (reviewState) reviewState.classList.remove('hidden');
    } else {
      if (viewState) viewState.classList.remove('hidden');
      if (reviewState) reviewState.classList.add('hidden');
    }
  }

  // 7. Ambil Foto dari Live Video, Buat Watermark & Kompresi
  async function ambilFotoDariLiveVideo() {
    const video = document.getElementById('liveCameraVideo');
    if (!video || !video.videoWidth) {
      alert('Kamera sedang memuat frame video, silakan tunggu sesaat.');
      return;
    }

    // Waktu pengambilan foto (timestamp seketika jepret)
    const meta = getWatermarkMetadata(new Date());

    // Proses watermark & kompresi < 1 MB
    const processed = await processAndCompressWatermark(video, meta);
    if (!processed) return;

    // Hentikan stream video kamera untuk hemat baterai
    if (liveCameraStream) {
      try { liveCameraStream.getTracks().forEach(t => t.stop()); } catch (e) {}
      liveCameraStream = null;
    }

    // Tampilkan di Mode Review Modal
    showPhotoInReviewModal(processed, meta);
  }

  function showPhotoInReviewModal(processed, meta) {
    const reviewImg = document.getElementById('cameraReviewImg');
    const sizeBadge = document.getElementById('reviewFileSizeBadge');
    const resBadge  = document.getElementById('reviewResolutionBadge');
    const waktuTxt  = document.getElementById('reviewWaktuText');
    const gpsTxt    = document.getElementById('reviewGpsText');
    const unitTxt   = document.getElementById('reviewUnitText');

    const previewUrl = URL.createObjectURL(processed.blob);
    if (reviewImg) reviewImg.src = previewUrl;

    const sizeKb = (processed.size / 1024).toFixed(1);
    if (sizeBadge) {
      sizeBadge.textContent = `${sizeKb} KB (< 1 MB OK)`;
    }
    if (resBadge) {
      resBadge.textContent = `${processed.width} × ${processed.height} px • JPEG`;
    }
    if (waktuTxt) waktuTxt.textContent = meta.waktu;
    if (gpsTxt)   gpsTxt.textContent   = meta.gps;
    if (unitTxt)  unitTxt.textContent  = meta.unit;

    // Simpan data foto yang menunggu konfirmasi "Gunakan Foto"
    const finalFile = new File([processed.blob], `foto_dinas_${Date.now()}.jpg`, { type: 'image/jpeg' });
    pendingProcessedPhoto = {
      blob: processed.blob,
      file: finalFile,
      previewUrl: previewUrl,
      meta: meta,
      sizeKb: sizeKb,
      width: processed.width,
      height: processed.height
    };

    setCameraModalState('review');
  }

  function ulangiFotoKamera() {
    pendingProcessedPhoto = null;
    bukaLiveKameraModal();
  }

  function gunakanFotoKamera() {
    if (!pendingProcessedPhoto) return;

    applyWatermarkedPhotoToForm(pendingProcessedPhoto);
    tutupLiveKameraModal();
  }

  // 8. Terapkan Foto Terwatermark ke Input Form & Tampilan Bagian 2
  function applyWatermarkedPhotoToForm(photoData) {
    hasBirdPhoto = true;

    // Update Tampilan Preview di Bagian 2
    const img    = document.getElementById('fotoPreviewImg');
    const ph     = document.getElementById('fotoPlaceholder');
    const filled = document.getElementById('fotoPreviewFilled');
    const nama   = document.getElementById('fotoNamaFile');
    const ukText = document.getElementById('fotoUkuranText');
    const dimTxt = document.getElementById('fotoDimensiText');
    const badge  = document.getElementById('fotoStatusBadge');

    const sumWaktu = document.getElementById('fotoSummaryWaktu');
    const sumGps   = document.getElementById('fotoSummaryGps') || document.getElementById('fotoSummaryGridGps');
    const sumUnit  = document.getElementById('fotoSummaryUnit');

    if (img) img.src = photoData.previewUrl;
    if (ph)  ph.classList.add('hidden');
    if (filled) {
      filled.classList.remove('hidden');
      filled.classList.add('flex');
    }

    if (nama)   nama.textContent = photoData.file.name;
    if (ukText) ukText.textContent = `Ukuran: ${photoData.sizeKb} KB (< 1 MB)`;
    if (dimTxt) dimTxt.textContent = `Resolusi: ${photoData.width} × ${photoData.height} px`;
    if (badge) {
      badge.innerHTML = '<span class="text-teal-600 font-bold flex items-center gap-1">✓ Foto Dinas Terverifikasi</span>';
    }

    if (sumWaktu) sumWaktu.textContent = photoData.meta.waktu;
    if (sumGps)   sumGps.textContent   = `GPS: ${photoData.meta.gps}`;
    if (sumUnit)  sumUnit.textContent  = photoData.meta.unit;

    // Masukkan file JPEG ke input form satwa[0][foto] via DataTransfer
    const satwaFotoInput = document.getElementById('formSatwaFotoInput');
    const camInput = document.getElementById('inputKameraBelakang');
    const galInput = document.getElementById('inputGaleriFoto');

    if (satwaFotoInput && window.DataTransfer) {
      try {
        const dt = new DataTransfer();
        dt.items.add(photoData.file);
        satwaFotoInput.files = dt.files;
        satwaFotoInput.name = 'satwa[0][foto]';
      } catch (e) {
        console.warn('DataTransfer error:', e);
      }
    }

    // Pastikan input cadangan tidak menimpa name
    if (camInput) camInput.removeAttribute('name');
    if (galInput) galInput.removeAttribute('name');
  }

  // 9. Penanganan jika Foto Dipilih dari Galeri atau Kamera Bawaan (Tetap diberi Watermark & Kompresi)
  async function handleFileSelected(input) {
    if (!input.files || !input.files[0]) return;
    const rawFile = input.files[0];

    const imgEl = new Image();
    const reader = new FileReader();

    reader.onload = function(e) {
      imgEl.src = e.target.result;
      imgEl.onload = async function() {
        const meta = getWatermarkMetadata(new Date());
        const processed = await processAndCompressWatermark(imgEl, meta);
        if (!processed) return;

        const finalFile = new File([processed.blob], `foto_dinas_${Date.now()}.jpg`, { type: 'image/jpeg' });
        const previewUrl = URL.createObjectURL(processed.blob);

        applyWatermarkedPhotoToForm({
          blob: processed.blob,
          file: finalFile,
          previewUrl: previewUrl,
          meta: meta,
          sizeKb: (processed.size / 1024).toFixed(1),
          width: processed.width,
          height: processed.height
        });
      };
    };
    reader.readAsDataURL(rawFile);
  }

  function hapusFotoBurung() {
    hasBirdPhoto = false;
    pendingProcessedPhoto = null;

    const img    = document.getElementById('fotoPreviewImg');
    const ph     = document.getElementById('fotoPlaceholder');
    const filled = document.getElementById('fotoPreviewFilled');
    const badge  = document.getElementById('fotoStatusBadge');
    const cam    = document.getElementById('inputKameraBelakang');
    const gal    = document.getElementById('inputGaleriFoto');
    const satwa  = document.getElementById('formSatwaFotoInput');

    if (img) img.src = '';
    if (ph)  ph.classList.remove('hidden');
    if (filled) {
      filled.classList.add('hidden');
      filled.classList.remove('flex');
    }
    if (badge) {
      badge.innerHTML = '<span class="text-slate-400 font-medium">Wajib terlampir</span>';
    }

    if (cam)   { cam.value = ''; cam.removeAttribute('name'); }
    if (gal)   { gal.value = ''; gal.removeAttribute('name'); }
    if (satwa) { satwa.value = ''; }
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
    updateAiVisionButtonState();
  });

  // ── Bagian 2: AI Species Vision & SOP Assistant (Pilar 4) ──
  let currentAiVisionResult = null;

  function updateAiVisionButtonState() {
    const btn = document.getElementById('btnAiIdentifySpecies');
    if (!btn) return;
    if (!navigator.onLine) {
      btn.disabled = true;
      btn.classList.add('opacity-60', 'cursor-not-allowed');
      btn.title = 'Butuh koneksi internet (Offline PWA)';
      document.getElementById('textAiIdentify').innerText = 'Identifikasi AI (Butuh Koneksi Internet)';
      document.getElementById('iconAiIdentify').innerText = 'wifi_off';
    } else {
      btn.disabled = false;
      btn.classList.remove('opacity-60', 'cursor-not-allowed');
      btn.title = 'Identifikasi spesies satwa otomatis via AI Gemini Vision';
      document.getElementById('textAiIdentify').innerText = 'Identifikasi Satwa via AI (Gemini Vision)';
      document.getElementById('iconAiIdentify').innerText = 'auto_awesome';
    }
  }

  window.addEventListener('online', updateAiVisionButtonState);
  window.addEventListener('offline', updateAiVisionButtonState);

  async function runAiSpeciesIdentification() {
    if (!navigator.onLine) {
      alert('Identifikasi Satwa via AI membutuhkan koneksi internet (PWA online).');
      return;
    }

    const fileInput = document.getElementById('inputKameraBelakang') || document.getElementById('inputGaleriFoto');
    const fotoPreviewImg = document.getElementById('fotoPreviewImg');

    let fileToSend = null;

    if (fileInput && fileInput.files && fileInput.files[0]) {
      fileToSend = fileInput.files[0];
    } else if (fotoPreviewImg && fotoPreviewImg.src && fotoPreviewImg.src.startsWith('data:')) {
      const res = await fetch(fotoPreviewImg.src);
      fileToSend = await res.blob();
    }

    if (!fileToSend) {
      alert('Harap ambil foto atau unggah gambar burung terlebih dahulu sebelum menjalankan identifikasi AI.');
      return;
    }

    const btn = document.getElementById('btnAiIdentifySpecies');
    const icon = document.getElementById('iconAiIdentify');
    const text = document.getElementById('textAiIdentify');

    btn.disabled = true;
    icon.classList.add('animate-spin');
    text.innerText = 'Mengidentifikasi satwa via Gemini Vision...';

    const formData = new FormData();
    formData.append('foto', fileToSend, 'satwa_lapangan.jpg');

    try {
      const response = await fetch("{{ route('laporan.identifikasi') }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: formData
      });

      const res = await response.json();

      if (!res.success) {
        alert('Galat identifikasi AI: ' + (res.message || 'Gagal memproses gambar.'));
        return;
      }

      currentAiVisionResult = res;
      renderAiVisionResult(res);

    } catch (err) {
      alert('Gagal menghubungi layanan AI: ' + err.message);
    } finally {
      btn.disabled = false;
      icon.classList.remove('animate-spin');
      text.innerText = 'Identifikasi Satwa via AI (Gemini Vision)';
    }
  }

  function renderAiVisionResult(data) {
    const panel = document.getElementById('aiSpeciesResultPanel');
    const foundSec = document.getElementById('aiSpeciesFoundSection');
    const noSpeciesSec = document.getElementById('aiNoSpeciesSection');
    const warnSec = document.getElementById('aiConfidenceWarning');

    if (!panel) return;
    panel.classList.remove('hidden');

    if (!data.terdeteksi_satwa || !data.kandidat || data.kandidat.length === 0) {
      foundSec.classList.add('hidden');
      noSpeciesSec.classList.remove('hidden');
      warnSec.classList.add('hidden');
      document.getElementById('btnApplyAiToForm').classList.add('hidden');
      return;
    }

    foundSec.classList.remove('hidden');
    noSpeciesSec.classList.add('hidden');
    document.getElementById('btnApplyAiToForm').classList.remove('hidden');

    const top = data.kandidat[0];
    document.getElementById('aiCandidateLocalName').innerText = top.nama_lokal || 'Satwa Liar';
    document.getElementById('aiCandidateLatinName').innerText = top.nama_latin ? `(${top.nama_latin})` : '';

    const confPct = Math.round((top.confidence || 0) * 100);
    const badgeConf = document.getElementById('aiCandidateConfidenceBadge');
    badgeConf.innerText = `${confPct}% Match`;

    if (data.confidence_low) {
      warnSec.classList.remove('hidden');
      badgeConf.className = 'px-2 py-0.5 rounded-full font-bold font-mono text-[10px] bg-amber-100 text-amber-800 border border-amber-200';
    } else {
      warnSec.classList.add('hidden');
      badgeConf.className = 'px-2 py-0.5 rounded-full font-bold font-mono text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-200';
    }

    const matchedBadge = document.getElementById('aiCatalogMatchedBadge');
    if (data.matched_satwa_id) {
      matchedBadge.classList.remove('hidden');
      matchedBadge.innerText = `Sesuai Katalog: ${data.matched_satwa_nama}`;
    } else {
      matchedBadge.classList.add('hidden');
    }

    // Weight & Risk category
    const weightBadge = document.getElementById('aiCandidateWeightBadge');
    if (top.estimasi_bobot_kg) {
      weightBadge.innerText = `Bobot: ~${top.estimasi_bobot_kg.min || 0} - ${top.estimasi_bobot_kg.max || 1} kg`;
    } else {
      weightBadge.innerText = 'Bobot: < 1 kg';
    }

    const riskCatBadge = document.getElementById('aiCandidateRiskCatBadge');
    const riskCat = top.kategori_bobot_risiko || 1;
    riskCatBadge.innerText = `Kategori Risiko ICAO: ${riskCat} (${riskCat === 1 ? '< 1 kg' : (riskCat === 2 ? '1-3 kg' : '> 3 kg')})`;

    // Taxonomy & Behavior
    const tax = top.taksonomi;
    const taxEl = document.getElementById('aiTaxonomyDetails');
    if (tax) {
      taxEl.innerText = `Kelas: ${tax.kelas || '-'} • Ordo: ${tax.ordo || '-'} • Famili: ${tax.famili || '-'}`;
    } else {
      taxEl.innerText = 'Taksonomi tidak teridentifikasi';
    }

    const behEl = document.getElementById('aiBehaviorDetails');
    behEl.innerText = top.perilaku_terlihat || 'Aktivitas normal di area sisi udara';

    // SOP Recommendations
    const sopContainer = document.getElementById('aiSopRecommendationsContainer');
    sopContainer.innerHTML = '';
    const sops = data.rekomendasi_sop || [];
    if (sops.length > 0) {
      sops.forEach(s => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1';
        itemDiv.innerHTML = `
          <div class="flex items-center justify-between">
            <span class="font-bold uppercase text-[10px] px-2 py-0.2 rounded bg-cyan-100 text-[#007fa3] font-mono">${s.metode}</span>
          </div>
          <p class="text-slate-700 font-medium leading-relaxed">${s.langkah}</p>
          ${s.catatan_keselamatan ? `<p class="text-[10px] text-amber-700 font-medium mt-1">⚠ ${s.catatan_keselamatan}</p>` : ''}
        `;
        sopContainer.appendChild(itemDiv);
      });
    } else {
      sopContainer.innerHTML = '<div class="text-slate-400 italic">Tidak ada rekomendasi SOP spesifik.</div>';
    }

    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function applyAiIdentificationToForm() {
    if (!currentAiVisionResult) return;

    const candidate = currentAiVisionResult.kandidat[0] || null;
    if (!candidate) return;

    if (currentAiVisionResult.confidence_low) {
      const ok = confirm(`Tingkat keyakinan identifikasi AI adalah ${Math.round(candidate.confidence * 100)}%. Apakah Anda yakin ingin menerapkan hasil ini ke formulir?`);
      if (!ok) return;
    }

    const localName = candidate.nama_lokal;
    const latinName = candidate.nama_latin;

    // 1. Check matching item in listBurungMaster
    const matchedItem = listBurungMaster.find(b => b.nama.toLowerCase() === localName.toLowerCase()) || 
                        listBurungMaster.find(b => b.nama.toLowerCase().includes(localName.toLowerCase()));

    if (matchedItem) {
      pilihBurung(matchedItem);
    } else {
      // Unlisted animal: set direct value
      document.getElementById('selectJenisBurung').value = localName;
      document.getElementById('triggerBurungText').innerText = localName + ' (AI)';
      document.getElementById('triggerBurungText').classList.remove('text-slate-500');
      document.getElementById('triggerBurungText').classList.add('text-slate-800', 'font-bold');
      const dot = document.getElementById('triggerBurungDot');
      if (dot) {
        dot.classList.remove('bg-slate-300');
        dot.classList.add('bg-[#00A9C1]');
      }
    }

    // 2. Set hidden inputs
    document.getElementById('aiIdentifikasiId').value = currentAiVisionResult.id;
    document.getElementById('aiInputNamaLatin').value = latinName || '';
    document.getElementById('aiInputSumberIdentifikasi').value = 'ai';

    // 3. Fill ciri_ukuran textarea
    const ciriTa = document.querySelector('textarea[name="ciri_ukuran"]');
    if (ciriTa) {
      let weightStr = '';
      if (candidate.estimasi_bobot_kg) {
        weightStr = ` (Estimasi bobot: ${candidate.estimasi_bobot_kg.min || ''} - ${candidate.estimasi_bobot_kg.max || ''} kg)`;
      }
      const behaviorStr = candidate.perilaku_terlihat ? `. Perilaku: ${candidate.perilaku_terlihat}` : '';
      ciriTa.value = `Identifikasi AI: ${localName} [${latinName}]${weightStr}${behaviorStr}.`;
    }

    // 4. Append SOP to detail_pengusiran
    const sopTa = document.querySelector('textarea[name="detail_pengusiran"]');
    if (sopTa && currentAiVisionResult.rekomendasi_sop && currentAiVisionResult.rekomendasi_sop.length > 0) {
      const sops = currentAiVisionResult.rekomendasi_sop.map(s => `[${s.metode.toUpperCase()}]: ${s.langkah}${s.catatan_keselamatan ? ' (Catatan: ' + s.catatan_keselamatan + ')' : ''}`).join('\n');
      const existing = (sopTa.value && sopTa.value !== '-') ? (sopTa.value + '\n\n') : '';
      sopTa.value = existing + 'Saran SOP (AI):\n' + sops;
    }

    // 5. Notify server of acceptance
    fetch(`/laporan/identifikasi-satwa/${currentAiVisionResult.id}/hasil`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        diterima: true,
        nama_final: localName
      })
    }).catch(e => console.warn('Gagal mencatat penerimaan AI:', e));

    closeSpeciesResultPanel(true);
    alert(`Hasil identifikasi satwa "${localName}" dan rekomendasi SOP berhasil diterapkan ke formulir.`);
  }

  function closeSpeciesResultPanel(accepted = false) {
    const panel = document.getElementById('aiSpeciesResultPanel');
    if (panel) panel.classList.add('hidden');

    if (!accepted && currentAiVisionResult) {
      fetch(`/laporan/identifikasi-satwa/${currentAiVisionResult.id}/hasil`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          diterima: false,
          nama_final: null
        })
      }).catch(e => console.warn('Gagal mencatat penolakan AI:', e));
    }
  }

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

    // Sync input hidden grid_lokasi
    const inputLokasi = document.getElementById('inputGridLokasi');
    if (inputLokasi) inputLokasi.value = val;

    // Trigger select on Map in Bagian 3 tanpa callback loop
    if (val && typeof selectGrid === 'function') {
      selectGrid(val, true, false);
    }
  }

  // Callback saat peta di Bagian 3 disentuh / dipilih interaktif
  function onMapGridSelected(gridCode, zoneName) {
    const inp = document.getElementById('selectGridBurung');
    if (inp) {
      inp.value = gridCode;
    }
    const fullText = `Grid ${gridCode}` + (zoneName ? ` — ${zoneName}` : '');
    const txt = document.getElementById('gridPillText');
    if (txt) {
      txt.textContent = fullText;
    }
  }

  // Pastikan input Pilihan Grid Bagian 2 selalu kosong sebelum diisi pengguna
  document.addEventListener('DOMContentLoaded', () => {
    const inp = document.getElementById('selectGridBurung');
    const oldVal = @json(old('satwa.0.lokasi.0.grid', ''));
    if (inp && !oldVal) {
      inp.value = '';
      const txt = document.getElementById('gridPillText');
      if (txt) txt.textContent = '-';
    }
  });

  function scrollKePetaBagian3() {
    const sec = document.getElementById('petaKoordinatSection');
    if (sec) {
      sec.scrollIntoView({ behavior: 'smooth' });
    }
  }

  // ── Mekanisme Simpan Draf Offline (IndexedDB) ──
  async function simpanKeDrafOffline(e) {
    if (e) e.preventDefault();

    const jenisBurung = document.getElementById('selectJenisBurung');
    if (jenisBurung && !jenisBurung.value.trim()) {
      alert('Silakan pilih jenis burung terlebih dahulu.');
      const btn = document.getElementById('btnTriggerBurung');
      if (btn) {
        btn.focus();
        btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }

    const gridBurung = document.getElementById('selectGridBurung');
    if (gridBurung && !gridBurung.value.trim()) {
      alert('Silakan tentukan Grid lokasi pengamatan.');
      gridBurung.focus();
      gridBurung.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    if (signaturePad.isEmpty()) {
      alert('Mohon bubuhkan tanda tangan digital Anda terlebih dahulu.');
      document.getElementById('signature-pad').scrollIntoView({ behavior: 'smooth' });
      return;
    }

    const signatureData = signaturePad.toDataURL('image/png');
    document.getElementById('tanda_tangan').value = signatureData;

    const photos = [];
    const fotoPreviewImg = document.getElementById('fotoPreviewImg');
    if (fotoPreviewImg && fotoPreviewImg.src && fotoPreviewImg.src.startsWith('data:')) {
      photos.push({
        field_name: 'satwa[0][foto]',
        name: 'foto_satwa.jpg',
        type: 'image/jpeg',
        data: fotoPreviewImg.src
      });
    } else {
      const fileInput = document.getElementById('inputKameraBelakang') || document.getElementById('inputGaleriFoto');
      if (fileInput && fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        const base64 = await new Promise((res) => {
          const r = new FileReader();
          r.onload = () => res(r.result);
          r.readAsDataURL(file);
        });
        photos.push({
          field_name: 'satwa[0][foto]',
          name: file.name,
          type: file.type || 'image/jpeg',
          data: base64
        });
      }
    }

    const form = document.getElementById('mainForm');
    const formData = new FormData(form);
    const payload = {};
    for (const [key, val] of formData.entries()) {
      if (!(val instanceof File)) {
        payload[key] = val;
      }
    }

    // Default ciri morfologi jika kosong
    if (!payload['ciri_ukuran'] && jenisBurung && jenisBurung.value) {
      payload['ciri_ukuran'] = 'Pengamatan burung ' + jenisBurung.value + ' di ' + (gridBurung ? ('Grid ' + gridBurung.value) : 'area sisi udara.');
    }

    const tgl = payload['tanggal'] || new Date().toISOString().split('T')[0];
    const summary = `Pelaporan Offline (${tgl}) — ${jenisBurung ? jenisBurung.value : 'Satwa'} di Grid ${gridBurung ? gridBurung.value : '-'}`;

    try {
      if (window.WHMS_OFFLINE && typeof window.WHMS_OFFLINE.saveDraft === 'function') {
        await window.WHMS_OFFLINE.saveDraft({
          summary: summary,
          payload: payload,
          photos: photos,
          signature: signatureData
        });
      }

      alert('Draf offline berhasil disimpan di IndexedDB!\nSaat Anda kembali terhubung dengan jaringan internet bandara, Anda dapat menekan tombol "Sinkronkan Sekarang" di navbar/dashboard.');

      // Reset form & signature
      form.reset();
      signaturePad.clear();
      document.getElementById('tanda_tangan').value = '';
      if (typeof hapusFotoBurung === 'function') hapusFotoBurung();
    } catch (err) {
      console.error('Gagal menyimpan draf offline:', err);
      alert('Gagal menyimpan draf: ' + err.message);
    }
  }

  // ── Form Validation & Pre-submit Sync ──
  document.getElementById('mainForm').addEventListener('submit', function(e) {
    if (!navigator.onLine) {
      e.preventDefault();
      simpanKeDrafOffline(e);
      return;
    }

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
