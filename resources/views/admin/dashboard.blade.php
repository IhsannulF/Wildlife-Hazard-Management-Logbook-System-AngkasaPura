@extends('layouts.admin', ['activePage' => 'dashboard'])

@section('title', 'Dashboard Admin — Portal Satwa Liar')

@section('content')
  <!-- Breadcrumbs -->
  <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-6">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-600 transition-colors">Dashboard</a>
    <span>&rsaquo;</span>
    <span class="text-slate-700">Ringkasan Laporan</span>
  </div>

  <!-- STAT CARDS -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="relative overflow-hidden bg-gradient-to-tr from-[#00C4DF] via-[#00A9C1] to-[#007fa3] rounded-3xl p-7 text-white shadow-xl shadow-cyan-500/20 transition-all duration-200 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-extrabold uppercase tracking-wider text-white/80">Statistik Utama</span>
        <div class="p-2.5 rounded-2xl bg-white/15 backdrop-blur-xs">
          <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
        </div>
      </div>
      <div class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-1 leading-none">{{ $totalLaporan }}</div>
      <div class="text-base font-bold">Total Laporan</div>
      <div class="text-xs text-white/80 mt-1">Keseluruhan laporan satwa liar masuk</div>
    </div>

    <div class="relative overflow-hidden bg-gradient-to-tr from-rose-400 via-rose-500 to-rose-600 rounded-3xl p-7 text-white shadow-xl shadow-rose-500/20 transition-all duration-200 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-extrabold uppercase tracking-wider text-white/80">Perlu Tindakan</span>
        <div class="p-2.5 rounded-2xl bg-white/15 backdrop-blur-xs">
          <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
      </div>
      <div class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-1 leading-none">{{ $belum }}</div>
      <div class="text-base font-bold">Belum Ditangani</div>
      <div class="text-xs text-white/80 mt-1">Menunggu tindakan / verifikasi petugas</div>
    </div>

    <div class="relative overflow-hidden bg-gradient-to-tr from-emerald-400 via-emerald-500 to-emerald-600 rounded-3xl p-7 text-white shadow-xl shadow-emerald-500/20 transition-all duration-200 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-extrabold uppercase tracking-wider text-white/80">Selesai Ditangani</span>
        <div class="p-2.5 rounded-2xl bg-white/15 backdrop-blur-xs">
          <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
      </div>
      <div class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-1 leading-none">{{ $ditangani }}</div>
      <div class="text-base font-bold">Telah Ditangani</div>
      <div class="text-xs text-white/80 mt-1">Pengusiran satwa telah divalidasi</div>
    </div>
  </div>

  <!-- TABLE SECTION -->
  <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden mb-10">
    <!-- Table Header Toolbar -->
    <div class="p-6 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap bg-gradient-to-r from-slate-50/80 via-white to-teal-50/40">
      <div class="flex items-center gap-3">
        <div class="p-2.5 rounded-xl bg-teal-50 text-teal-600 border border-teal-200/80 shadow-2xs">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
        </div>
        <div>
          <h2 class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight">Data Master Laporan Satwa Liar</h2>
          <p class="text-xs text-slate-400">Kelola dan pantau seluruh catatan logbook dari petugas lapangan</p>
        </div>
      </div>

      <!-- Action & Filter Group -->
      <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route('admin.export.excel') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow transition-all cursor-pointer">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
          <span>Export Excel</span>
        </a>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 m-0 flex-wrap">
          <select name="status" onchange="this.form.submit()" class="px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-teal-500 shadow-2xs">
            <option value="">Semua Status</option>
            <option value="belum" {{ $statusFilter === 'belum' ? 'selected' : '' }}>Belum Ditangani</option>
            <option value="sudah" {{ $statusFilter === 'sudah' ? 'selected' : '' }}>Sudah Ditangani</option>
          </select>

          <div class="relative">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari petugas, area, grid..." class="w-48 sm:w-64 pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-teal-500 shadow-2xs">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>

          <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white text-xs font-bold rounded-xl shadow-xs hover:opacity-95 cursor-pointer transition-all">
            Cari
          </button>

          @if($statusFilter || $search)
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-rose-500 hover:text-rose-700 px-2 py-1">
              Reset
            </a>
          @endif
        </form>
      </div>
    </div>

    <!-- Table Responsive -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
            <th class="py-4 px-5">#ID</th>
            <th class="py-4 px-5">Tanggal</th>
            <th class="py-4 px-5">Nama Petugas</th>
            <th class="py-4 px-5">Unit Kerja</th>
            <th class="py-4 px-5">Area & Grid</th>
            <th class="py-4 px-5">Status</th>
            <th class="py-4 px-5 text-center min-w-[240px]">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs">
          @forelse($laporans as $lap)
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="py-4 px-5 font-extrabold text-teal-600">#{{ $lap->id }}</td>
              <td class="py-4 px-5 text-slate-600 font-medium whitespace-nowrap">{{ $lap->tanggal ? $lap->tanggal->format('d/m/Y') : '-' }}</td>
              <td class="py-4 px-5 font-bold text-slate-800">{{ $lap->nama_petugas }}</td>
              <td class="py-4 px-5 text-slate-500 font-medium">{{ $lap->unit_kerja }}</td>
              <td class="py-4 px-5">
                <span class="font-semibold text-slate-700">{{ $lap->area_inspeksi }}</span>
                @if($lap->grid_lokasi)
                  <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-md bg-teal-50 text-teal-700 font-bold text-[10px] border border-teal-200">{{ $lap->grid_lokasi }}</span>
                @endif
              </td>
              <td class="py-4 px-5 whitespace-nowrap">
                @if($lap->status === 'sudah')
                  <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Telah Ditangani
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Belum Ditangani
                  </span>
                @endif
              </td>
              <td class="py-4 px-5 text-center whitespace-nowrap">
                <div class="inline-flex items-center justify-center gap-2">
                  <button type="button" onclick="openDetailModal({{ $lap->id }})" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 text-[#007fa3] border border-cyan-200 font-bold text-xs shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#00A9C1]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Detail</span>
                  </button>

                  <button type="button" onclick="openEditModal({{ $lap->id }}, '{{ $lap->status }}', '{{ addslashes($lap->nama_petugas) }}', '{{ $lap->tanggal ? $lap->tanggal->format('Y-m-d') : '' }}', '{{ addslashes($lap->kondisi_cuaca) }}', '{{ addslashes($lap->unit_kerja) }}', '{{ addslashes($lap->area_inspeksi) }}', '{{ $lap->grid_lokasi }}', '{{ addslashes($lap->tindak_lanjut) }}')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Edit</span>
                  </button>

                  <button type="button" onclick="confirmDelete({{ $lap->id }})" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    <span>Hapus</span>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-16 text-slate-400">
                <div class="flex flex-col items-center justify-center gap-2">
                  <svg class="w-10 h-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
                  <span class="font-semibold text-sm text-slate-500">Belum ada data laporan satwa liar.</span>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($laporans->hasPages())
      <div class="p-5 border-t border-slate-100 bg-slate-50/50">
        {{ $laporans->links() }}
      </div>
    @endif
  </div>

  <!-- MODAL DETAIL (2-PAGE) -->
  <div id="detailModalBg" style="display:none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-100">
      <div class="bg-gradient-to-r from-[#00A9C1] to-[#007fa3] p-6 rounded-t-3xl flex items-center justify-between text-white">
        <div>
          <h3 id="modalDetailTitle" class="text-lg font-bold">Detail Laporan Satwa Liar</h3>
          <p class="text-xs text-white/80 mt-0.5">Rincian data pengamatan sisi udara bandara</p>
        </div>
        <button type="button" onclick="closeDetailModal()" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white text-xl font-bold cursor-pointer transition-all">&times;</button>
      </div>

      <!-- Page 1: Info Petugas & Lapangan -->
      <div id="detailPage1" class="p-6 sm:p-8 space-y-5">
        <h4 class="text-xs font-extrabold uppercase text-teal-600 tracking-wider pb-2 border-b border-slate-100">Halaman 1 — Informasi Pelapor & Lokasi</h4>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Petugas</label>
            <div id="dNamaPetugas" class="p-3 bg-slate-50 rounded-xl border border-slate-200 font-bold text-slate-800 text-sm">-</div>
          </div>
          <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Pemantauan</label>
            <div id="dTanggal" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 text-sm">-</div>
          </div>
          <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Unit Kerja</label>
            <div id="dUnitKerja" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 text-sm">-</div>
          </div>
          <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Kondisi Cuaca</label>
            <div id="dCuaca" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 text-sm">-</div>
          </div>
          <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Area Inspeksi</label>
            <div id="dArea" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 text-sm">-</div>
          </div>
          <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Gridmap Lokasi</label>
            <div id="dGrid" class="p-3 bg-teal-50 text-teal-700 border border-teal-200 rounded-xl font-extrabold text-sm">-</div>
          </div>
        </div>

        <div class="pt-4 flex justify-end">
          <button type="button" onclick="showDetailPage(2)" class="px-6 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white rounded-xl text-xs font-bold hover:opacity-95 cursor-pointer shadow-sm">
            Lanjut ke Data Satwa &rsaquo;
          </button>
        </div>
      </div>

      <!-- Page 2: Satwa, Foto, Tindak Lanjut, TTD -->
      <div id="detailPage2" style="display:none;" class="p-6 sm:p-8 space-y-5">
        <h4 class="text-xs font-extrabold uppercase text-teal-600 tracking-wider pb-2 border-b border-slate-100">Halaman 2 — Temuan Satwa & Bukti Penanganan</h4>

        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Satwa Yang Ditemukan</label>
          <div id="dSatwaGrid" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <!-- diisi via JS -->
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Kondisi Saat Ditemukan</label>
            <div id="dKondisiApron" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 text-sm">-</div>
          </div>
          <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tindak Lanjut</label>
            <div id="dTindakLanjut" class="p-3 bg-slate-50 rounded-xl border border-slate-200 font-bold text-slate-800 text-sm">-</div>
          </div>
        </div>

        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Detail Pengusiran</label>
          <div id="dDetailPengusiran" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 text-sm leading-relaxed">-</div>
        </div>

        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tanda Tangan Petugas</label>
          <div id="dTtdBox" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-center">
            <img id="dTtdImg" src="" alt="Tanda Tangan" class="max-h-24 mx-auto object-contain">
          </div>
        </div>

        <div class="pt-4 flex items-center justify-between gap-3">
          <button type="button" onclick="showDetailPage(1)" class="px-5 py-2.5 bg-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-300 cursor-pointer">
            &lsaquo; Kembali
          </button>
          <div class="flex items-center gap-3">
            <a id="btnPdfCetak" href="#" target="_blank" class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition-all">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
              <span>Cetak PDF</span>
            </a>
            <button type="button" onclick="closeDetailModal()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold cursor-pointer transition-all">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL EDIT / VALIDASI -->
  <div id="editModalBg" style="display:none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-100 overflow-hidden">
      <div class="bg-gradient-to-r from-[#00A9C1] to-[#007fa3] p-6 flex items-center justify-between text-white">
        <div>
          <h3 class="text-base font-bold">Validasi & Edit Laporan</h3>
          <p class="text-xs text-white/80 mt-0.5">Perbarui status tindak lanjut temuan satwa</p>
        </div>
        <button type="button" onclick="closeEditModal()" class="text-white text-xl font-bold cursor-pointer">&times;</button>
      </div>

      <form id="editForm" method="POST" action="" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Verifikasi</label>
          <select name="status_validasi" id="eStatus" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="belum">Belum Ditangani</option>
            <option value="sudah">Sudah Ditangani</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Petugas</label>
          <input type="text" name="nama_petugas" id="eNamaPetugas" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal</label>
            <input type="date" name="tanggal" id="eTanggal" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Grid Lokasi</label>
            <input type="text" name="grid_lokasi" id="eGrid" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase focus:outline-none focus:ring-2 focus:ring-teal-500">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Tindak Lanjut</label>
          <input type="text" name="tindak_lanjut" id="eTindakLanjut" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>

        <div class="pt-3 flex justify-end gap-3">
          <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 cursor-pointer">
            Batal
          </button>
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white rounded-xl text-xs font-bold hover:opacity-95 cursor-pointer shadow-sm">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL DELETE CONFIRMATION -->
  <form id="deleteForm" method="POST" action="" style="display:none;">
    @csrf
  </form>
@endsection

@section('scripts')
<script>
  function openDetailModal(id) {
    fetch(`/admin/laporan/${id}/detail`)
      .then(res => res.json())
      .then(data => {
        document.getElementById('modalDetailTitle').textContent = `Detail Laporan Satwa Liar #${data.id}`;
        document.getElementById('dNamaPetugas').textContent = data.nama_petugas || '-';
        document.getElementById('dTanggal').textContent = data.tanggal || '-';
        document.getElementById('dUnitKerja').textContent = data.unit_kerja || '-';
        document.getElementById('dCuaca').textContent = data.kondisi_cuaca || '-';
        document.getElementById('dArea').textContent = data.area_inspeksi || '-';
        document.getElementById('dGrid').textContent = data.grid_lokasi || '-';
        document.getElementById('dKondisiApron').textContent = data.kondisi_apron || '-';
        document.getElementById('dTindakLanjut').textContent = data.tindak_lanjut || '-';
        document.getElementById('dDetailPengusiran').textContent = data.detail_pengusiran || '-';

        if (data.tanda_tangan) {
          document.getElementById('dTtdImg').src = data.tanda_tangan;
          document.getElementById('dTtdBox').style.display = 'block';
        } else {
          document.getElementById('dTtdBox').style.display = 'none';
        }

        const gridWrap = document.getElementById('dSatwaGrid');
        gridWrap.innerHTML = '';
        if (data.satwa && data.satwa.length > 0) {
          data.satwa.forEach(s => {
            const card = document.createElement('div');
            card.className = 'bg-slate-50 border border-slate-200 rounded-2xl p-2.5 text-center text-xs';
            card.innerHTML = `
              <img src="${s.foto_path}" class="w-full h-18 object-cover rounded-xl mb-2" onerror="this.src='/images/biawak.jpeg'">
              <strong class="block font-bold text-slate-800 truncate mb-0.5">${s.nama}</strong>
              <span class="text-slate-500 font-medium text-[11px]">${s.jumlah} ekor (${s.grid || '-'})</span>
            `;
            gridWrap.appendChild(card);
          });
        } else {
          gridWrap.innerHTML = '<span class="text-slate-400 text-xs col-span-4 py-3 text-center">Tidak ada satwa terdaftar.</span>';
        }

        document.getElementById('btnPdfCetak').href = `/admin/laporan/${data.id}/cetak`;

        showDetailPage(1);
        document.getElementById('detailModalBg').style.display = 'flex';
      })
      .catch(err => alert('Gagal memuat detail laporan.'));
  }

  function showDetailPage(page) {
    document.getElementById('detailPage1').style.display = page === 1 ? 'block' : 'none';
    document.getElementById('detailPage2').style.display = page === 2 ? 'block' : 'none';
  }

  function closeDetailModal() {
    document.getElementById('detailModalBg').style.display = 'none';
  }

  function openEditModal(id, status, nama, tanggal, cuaca, unit, area, grid, tindak) {
    document.getElementById('editForm').action = `/admin/laporan/${id}/update`;
    document.getElementById('eStatus').value = status || 'belum';
    document.getElementById('eNamaPetugas').value = nama || '';
    document.getElementById('eTanggal').value = tanggal || '';
    document.getElementById('eGrid').value = grid || '';
    document.getElementById('eTindakLanjut').value = tindak || '';
    document.getElementById('editModalBg').style.display = 'flex';
  }

  function closeEditModal() {
    document.getElementById('editModalBg').style.display = 'none';
  }

  function confirmDelete(id) {
    if (confirm('Yakin ingin menghapus laporan #' + id + '? Data tidak dapat dikembalikan.')) {
      const form = document.getElementById('deleteForm');
      form.action = `/admin/laporan/${id}/delete`;
      form.submit();
    }
  }
</script>
@endsection
