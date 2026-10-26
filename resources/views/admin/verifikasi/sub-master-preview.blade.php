<!-- SUB-VIEW: MASTER PREVIEW (GALERI GAMBAR DOKUMENTASI SATWA) -->
<section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden space-y-4 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle">
    <div>
      <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-[#007fa3]">photo_library</span>
        Master Preview Galeri Visual Dokumentasi Satwa
      </h2>
      <p class="font-body-sm text-body-sm text-secondary">
        Pratinjau visual seluruh foto bukti temuan lapangan dan spesimen spesies satwa sisi udara.
      </p>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs text-secondary font-medium">Menampilkan <strong>{{ $previewList->total() }}</strong> foto spesimen</span>
    </div>
  </div>

  <!-- Image Cards Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse($previewList as $p)
      @php
        $lap = $p->laporan;
        $foto = $p->fotoLaporan->first();
        $fotoUrl = $foto ? asset('storage/uploads/satwa/' . $foto->nama_file) : ($p->foto_path ? asset('images/' . $p->foto_path) : asset('images/biawak.jpeg'));
        $patrolHex = $lap ? substr(md5($lap->id . ($lap->tanggal ? $lap->tanggal->format('Ymd') : '20260101')), 0, 8) : 'FOTO-' . $p->id;
      @endphp
      <div class="group bg-surface-container-lowest rounded-xl border border-border-subtle overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col">
        <!-- Photo Container -->
        <div class="h-44 bg-slate-100 overflow-hidden relative cursor-pointer" onclick="openPhotoPreview('{{ $fotoUrl }}', '{{ $p->nama_satwa }}')">
          <img src="{{ $fotoUrl }}" alt="{{ $p->nama_satwa }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          <div class="absolute bottom-2 left-2 px-2 py-0.5 rounded bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-code-coordinate">
            GRID {{ $p->grid ?: ($lap?->grid_lokasi ?: 'K-10') }}
          </div>
          <div class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-cyan-900/80 text-white text-[10px] font-semibold">
            {{ $p->jumlah }} Ekor
          </div>
        </div>

        <!-- Info Card Body -->
        <div class="p-3.5 space-y-2 flex-1 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between text-[11px] text-secondary">
              <span class="font-code-coordinate font-bold text-injourney-teal">{{ $patrolHex }}</span>
              <span>{{ $lap && $lap->tanggal ? $lap->tanggal->format('d M Y') : '2026-06-01' }}</span>
            </div>
            <h3 class="font-bold text-sm text-on-surface mt-1 group-hover:text-injourney-teal transition-colors">
              {{ $p->nama_satwa }}
            </h3>
            <p class="text-xs text-secondary mt-0.5">
              Petugas: <strong class="text-slate-700">{{ $lap ? $lap->nama_petugas : 'Petugas Sisi Udara' }}</strong>
            </p>
          </div>

          <div class="pt-2 border-t border-border-subtle flex items-center justify-between text-xs">
            <span class="text-secondary text-[11px]">{{ $lap ? $lap->area_inspeksi : 'Sisi Udara' }}</span>
            <button type="button" onclick="openPhotoPreview('{{ $fotoUrl }}', '{{ $p->nama_satwa }}')" class="inline-flex items-center gap-1 text-injourney-teal font-semibold hover:underline cursor-pointer">
              <span class="material-symbols-outlined text-sm">zoom_in</span>
              Perbesar
            </button>
          </div>
        </div>
      </div>
    @empty
      <div class="col-span-full py-12 text-center text-secondary">
        Tidak ada foto pratinjau yang tersedia.
      </div>
    @endforelse
  </div>

  <!-- Pagination -->
  @if($previewList->hasPages())
    <div class="pt-2">
      {{ $previewList->links() }}
    </div>
  @endif
</section>

<!-- Fullscreen Photo Modal -->
<div id="photoLightboxModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4" onclick="closePhotoPreview()">
  <div class="max-w-4xl w-full bg-card-bg rounded-2xl overflow-hidden shadow-2xl border border-slate-700" onclick="event.stopPropagation()">
    <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-injourney-teal">photo_library</span>
        <h3 id="lightboxTitle" class="font-bold text-sm">Pratinjau Foto Dokumentasi</h3>
      </div>
      <button type="button" onclick="closePhotoPreview()" class="p-1 rounded text-slate-400 hover:text-white cursor-pointer">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>
    <div class="p-4 bg-slate-950 flex items-center justify-center max-h-[75vh]">
      <img id="lightboxImg" src="" alt="Spesimen Satwa" class="max-h-[70vh] w-auto object-contain rounded-lg shadow-lg">
    </div>
  </div>
</div>

<script>
  function openPhotoPreview(url, title) {
    const modal = document.getElementById('photoLightboxModal');
    const img = document.getElementById('lightboxImg');
    const titleEl = document.getElementById('lightboxTitle');
    if (!modal || !img) return;
    img.src = url;
    if (titleEl) titleEl.innerText = title || 'Pratinjau Spesimen Satwa';
    modal.classList.remove('hidden');
  }

  function closePhotoPreview() {
    const modal = document.getElementById('photoLightboxModal');
    if (modal) modal.classList.add('hidden');
  }
</script>
