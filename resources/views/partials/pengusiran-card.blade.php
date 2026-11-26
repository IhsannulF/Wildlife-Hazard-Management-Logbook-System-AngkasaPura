{{-- Log tindakan pengusiran (PRD v2.1 FR-0.3): kartu ringkas + modal pencatatan cepat --}}
@php
  $pengusiranErrorKeys = ['alat_id', 'waktu', 'grid', 'jumlah_digunakan', 'hasil', 'keterangan'];
  $pengusiranHasError = $errors->hasAny($pengusiranErrorKeys);
  $waktuLokalSekarang = now(config('whms_risk.zona_waktu'))->format('Y-m-d\TH:i');
@endphp

<section class="bg-white/95 rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs">
  <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-[#00A9C1] text-lg">campaign</span>
      <div>
        <h2 class="text-xs sm:text-sm font-bold text-slate-900">Log Tindakan Pengusiran</h2>
        <p class="text-[11px] text-slate-500">Catat setiap pengusiran (sirene, pyro, laser, patroli) beserta jam dan grid-nya.</p>
      </div>
    </div>
    <button type="button" onclick="document.getElementById('dialogPengusiran').showModal()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#00A9C1] hover:bg-[#007fa3] text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer">
      <span class="material-symbols-outlined text-base">add</span>
      Catat Pengusiran
    </button>
  </div>

  @if($pengusiranTerakhir->isNotEmpty())
    <ul class="divide-y divide-slate-100 text-xs">
      @foreach($pengusiranTerakhir as $tindakan)
        <li class="py-1.5 flex flex-wrap items-center justify-between gap-x-3 gap-y-0.5">
          <span class="font-semibold text-slate-800">{{ $tindakan->alat?->nama_alat ?? 'Alat dihapus' }}</span>
          <span class="text-slate-500">Grid <strong class="text-slate-700">{{ $tindakan->grid }}</strong> · {{ $tindakan->waktu->translatedFormat('j M Y, H:i') }}</span>
          @if($tindakan->hasil)
            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold">{{ \App\Models\TindakanPengusiran::HASIL[$tindakan->hasil] ?? $tindakan->hasil }}</span>
          @endif
        </li>
      @endforeach
    </ul>
  @else
    <p class="text-xs text-slate-500">Belum ada tindakan pengusiran yang Anda catat.</p>
  @endif
</section>

<dialog id="dialogPengusiran" class="w-[calc(100%-2rem)] max-w-md rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/50">
  <form method="POST" action="{{ route('pengusiran.store') }}" class="p-5 space-y-3.5 text-xs">
    @csrf
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
      <h3 class="text-sm font-bold text-slate-900">Catat Tindakan Pengusiran</h3>
      <button type="button" onclick="this.closest('dialog').close()" class="p-1 text-slate-500 hover:text-slate-800 cursor-pointer" aria-label="Tutup">
        <span class="material-symbols-outlined text-base">close</span>
      </button>
    </div>

    @if($pengusiranHasError)
      <ul class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 list-disc list-inside">
        @foreach($pengusiranErrorKeys as $key)
          @error($key)<li>{{ $message }}</li>@enderror
        @endforeach
      </ul>
    @endif

    <div>
      <label for="pengusiranAlat" class="block font-semibold text-slate-700 mb-1">Alat / metode *</label>
      <select name="alat_id" id="pengusiranAlat" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
        <option value="">-- Pilih alat --</option>
        @foreach($alatDispersal as $alat)
          <option value="{{ $alat->id }}" @selected((string) old('alat_id') === (string) $alat->id)>{{ $alat->nama_alat }}</option>
        @endforeach
      </select>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label for="pengusiranWaktu" class="block font-semibold text-slate-700 mb-1">Waktu *</label>
        <input type="datetime-local" name="waktu" id="pengusiranWaktu" value="{{ old('waktu', $waktuLokalSekarang) }}" max="{{ $waktuLokalSekarang }}" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
      </div>
      <div>
        <label for="pengusiranGrid" class="block font-semibold text-slate-700 mb-1">Grid *</label>
        <input type="text" name="grid" id="pengusiranGrid" value="{{ old('grid') }}" placeholder="K-10" required class="w-full px-3 py-2 rounded-lg border border-slate-200 uppercase focus:outline-none focus:border-[#00A9C1]">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label for="pengusiranJumlah" class="block font-semibold text-slate-700 mb-1">Jumlah pemakaian</label>
        <input type="number" name="jumlah_digunakan" id="pengusiranJumlah" value="{{ old('jumlah_digunakan', 1) }}" min="1" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
      </div>
      <div>
        <label for="pengusiranHasil" class="block font-semibold text-slate-700 mb-1">Hasil</label>
        <select name="hasil" id="pengusiranHasil" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          <option value="">-- Pilih --</option>
          @foreach(\App\Models\TindakanPengusiran::HASIL as $value => $label)
            <option value="{{ $value }}" @selected(old('hasil') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div>
      <label for="pengusiranKeterangan" class="block font-semibold text-slate-700 mb-1">Keterangan</label>
      <input type="text" name="keterangan" id="pengusiranKeterangan" value="{{ old('keterangan') }}" maxlength="255" placeholder="mis. kawanan kuntul 12 ekor bergerak ke timur" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
    </div>

    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold cursor-pointer">Batal</button>
      <button type="submit" class="px-4 py-2 rounded-xl bg-[#00A9C1] hover:bg-[#007fa3] text-white font-bold cursor-pointer">Simpan</button>
    </div>
  </form>
</dialog>

@if($pengusiranHasError)
  <script>
    document.addEventListener('DOMContentLoaded', () => document.getElementById('dialogPengusiran')?.showModal());
  </script>
@endif
