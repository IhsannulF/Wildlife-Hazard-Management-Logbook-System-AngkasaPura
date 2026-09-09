@extends('layouts.app')

@section('title', 'Form Pengaduan Satwa Liar — InJourney Airports')

@section('styles')
<style>
  .satwa-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 22px;
    height: 22px;
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
    transition: all 0.2s;
  }
  .satwa-card.selected {
    border-color: #00A9C1 !important;
    box-shadow: 0 0 0 2px rgba(0,169,193,0.3) !important;
    background: #f0f9fb !important;
  }
  .satwa-card.selected .satwa-check {
    opacity: 1;
    transform: scale(1);
  }
  .satwa-card.selected .lokasi-rows {
    display: flex !important;
  }
  .satwa-card.selected .btn-tambah-lokasi {
    display: flex !important;
  }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-slate-50/80 font-sans" style="background-image: url('{{ asset('images/watermark_injourney.jpeg') }}'); background-repeat: repeat; background-size: 280px auto; background-attachment: fixed;">

  <!-- Brand Color Bar -->
  <div class="h-1 w-full bg-gradient-to-r from-[#00A9C1] via-[#4FADC9] via-[#88B146] via-[#F0B14B] to-[#D94F4F]"></div>

  <!-- Topbar -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 px-6 sm:px-10 h-16 flex items-center justify-between shadow-xs">
    <div class="flex items-center">
      <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports" class="h-8 object-contain">
    </div>
    <div class="flex items-center gap-3">
      <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs sm:text-sm font-semibold transition-all">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        <span>Kembali</span>
      </a>
      <div class="flex items-center gap-2 bg-teal-50 border border-teal-200/60 rounded-full py-1 px-3">
        <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-[#00A9C1] to-[#00C4DF] text-white text-[11px] font-bold flex items-center justify-center">
          {{ strtoupper(substr($user->namalengkap ?: $user->username, 0, 2)) }}
        </div>
        <span class="text-xs font-semibold text-slate-700">{{ $user->namalengkap ?: $user->username }}</span>
      </div>
    </div>
  </header>

  <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6">
    <!-- Header Title -->
    <div class="mb-8 text-center sm:text-left">
      <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Form Pengaduan Satwa Liar</h1>
      <p class="text-sm text-slate-500">Lengkapi formulir pengamatan satwa liar di area sisi udara bandara secara teliti.</p>
    </div>

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

      <!-- SECTION 2: Inventarisasi Satwa -->
      <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm">
        <div class="border-b border-slate-100 pb-4 mb-6">
          <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs flex items-center justify-center border border-teal-200">2</span>
            Bagian 2 — Inventarisasi Satwa Liar
          </h2>
          <p class="text-xs text-slate-500 mt-1 pl-9">Pilih jenis satwa yang terpantau, masukkan jumlah ekor, dan koordinat grid lokasi temuan</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
          @foreach($satwa_list as $idx => $s)
            <div class="satwa-card relative bg-slate-50 border-2 border-slate-200/80 rounded-2xl p-3 flex flex-col items-center text-center cursor-pointer hover:border-teal-400 transition-all duration-200 select-none overflow-hidden" id="satwaCard{{ $idx }}" onclick="toggleSatwaCard({{ $idx }})">
              <div class="satwa-check">&#10003;</div>
              <div class="w-full h-24 rounded-xl overflow-hidden mb-2 bg-white flex items-center justify-center border border-slate-100">
                <img src="{{ $s->foto_url }}" alt="{{ $s->nama }}" class="w-full h-full object-cover" loading="lazy">
              </div>
              <div class="font-bold text-xs text-slate-800 mb-2 leading-tight">{{ $s->nama }}</div>

              <input type="hidden" name="satwa[{{ $idx }}][nama]" value="{{ $s->nama }}">
              <input type="hidden" name="satwa[{{ $idx }}][aktif]" id="hiddenAktif{{ $idx }}" value="0">

              <div class="lokasi-rows hidden w-full flex-col gap-2 mt-2 pt-2 border-t border-slate-200" id="lokasiRows{{ $idx }}">
                <div class="flex items-center gap-1.5 text-xs">
                  <label class="font-bold text-slate-500">Jml</label>
                  <input type="number" min="1" name="satwa[{{ $idx }}][lokasi][0][jumlah]" placeholder="0" class="w-12 px-1.5 py-1 bg-white border border-slate-200 rounded text-center text-xs" onclick="event.stopPropagation()" oninput="event.stopPropagation(); autoSelectSatwa({{ $idx }})">
                  <label class="font-bold text-slate-500">Grid</label>
                  <input type="text" name="satwa[{{ $idx }}][lokasi][0][grid]" placeholder="K-5" class="w-14 px-1.5 py-1 bg-white border border-slate-200 rounded text-center text-xs uppercase" onclick="event.stopPropagation()" oninput="event.stopPropagation(); autoSelectSatwa({{ $idx }})">
                  <button type="button" onclick="event.stopPropagation(); hapusLokasi({{ $idx }}, this)" class="text-rose-500 font-bold px-1 invisible">&times;</button>
                </div>
              </div>

              <button type="button" class="btn-tambah-lokasi hidden items-center justify-center text-[11px] font-bold text-teal-600 hover:text-teal-800 mt-2 py-1 px-2 rounded-lg bg-teal-50 hover:bg-teal-100 transition-all w-full" onclick="event.stopPropagation(); tambahLokasi({{ $idx }})">
                + Titik Grid
              </button>
            </div>
          @endforeach
        </div>

        <!-- Satwa Tak Terdaftar (Extra) -->
        <div class="bg-slate-50/80 border border-dashed border-slate-300 rounded-xl p-4">
          <label class="text-xs font-bold text-slate-800 block mb-1">
            Foto Satwa Tidak Terdaftar (Opsional)
          </label>
          <p class="text-xs text-slate-500 mb-3">
            Jika Anda menemukan spesies satwa yang tidak ada pada daftar di atas, unggah foto dokumentasinya di sini:
          </p>
          <input type="file" name="foto_lainnya[]" multiple accept="image/*" class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
        </div>
      </div>

      <!-- SECTION 3: Denah Gridmap Bandara -->
      <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm">
        <div class="border-b border-slate-100 pb-4 mb-6">
          <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs flex items-center justify-center border border-teal-200">3</span>
            Bagian 3 — Peta Koordinat Gridmap Bandara
          </h2>
          <p class="text-xs text-slate-500 mt-1 pl-9">Gunakan peta grid apron/runway berikut sebagai referensi penentuan kode grid lokasi</p>
        </div>

        <div class="rounded-xl overflow-hidden border border-slate-200 mb-5 bg-slate-100 p-1">
          <img src="{{ asset('images/' . $gridmapImage) }}" alt="Gridmap InJourney" class="w-full max-h-96 object-contain mx-auto rounded-lg">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-2">Grid Lokasi Pengamatan Utama <span class="text-rose-500">*</span></label>
          <input type="text" name="grid_lokasi" value="{{ old('grid_lokasi') }}" placeholder="Contoh: K-10" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all" required>
        </div>
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

  // ── Satwa Card Selection & Location Handling ──
  const lokasiCounts = {};

  function toggleSatwaCard(idx) {
    const card = document.getElementById('satwaCard' + idx);
    const hidden = document.getElementById('hiddenAktif' + idx);
    card.classList.toggle('selected');
    const isSelected = card.classList.contains('selected');
    hidden.value = isSelected ? '1' : '0';

    if (isSelected && !lokasiCounts[idx]) {
      lokasiCounts[idx] = 1;
    }
  }

  function autoSelectSatwa(idx) {
    const card = document.getElementById('satwaCard' + idx);
    const hidden = document.getElementById('hiddenAktif' + idx);
    if (!card.classList.contains('selected')) {
      card.classList.add('selected');
      hidden.value = '1';
      if (!lokasiCounts[idx]) lokasiCounts[idx] = 1;
    }
  }

  function tambahLokasi(idx) {
    if (!lokasiCounts[idx]) lokasiCounts[idx] = 1;
    const count = lokasiCounts[idx]++;
    const wrap = document.getElementById('lokasiRows' + idx);

    const row = document.createElement('div');
    row.className = 'flex items-center gap-1.5 text-xs mt-1.5';
    row.innerHTML = `
      <label class="font-bold text-slate-500">Jml</label>
      <input type="number" min="1" name="satwa[${idx}][lokasi][${count}][jumlah]" placeholder="0" class="w-12 px-1.5 py-1 bg-white border border-slate-200 rounded text-center text-xs" onclick="event.stopPropagation()" oninput="event.stopPropagation(); autoSelectSatwa(${idx})">
      <label class="font-bold text-slate-500">Grid</label>
      <input type="text" name="satwa[${idx}][lokasi][${count}][grid]" placeholder="K-5" class="w-14 px-1.5 py-1 bg-white border border-slate-200 rounded text-center text-xs uppercase" onclick="event.stopPropagation()" oninput="event.stopPropagation(); autoSelectSatwa(${idx})">
      <button type="button" class="text-rose-500 font-bold px-1 text-sm hover:text-rose-700" onclick="event.stopPropagation(); hapusLokasi(${idx}, this)">&times;</button>
    `;
    wrap.appendChild(row);
  }

  function hapusLokasi(idx, btn) {
    const wrap = document.getElementById('lokasiRows' + idx);
    if (wrap.children.length > 1) {
      btn.closest('div').remove();
    }
  }

  // ── Form Validation ──
  document.getElementById('mainForm').addEventListener('submit', function(e) {
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
