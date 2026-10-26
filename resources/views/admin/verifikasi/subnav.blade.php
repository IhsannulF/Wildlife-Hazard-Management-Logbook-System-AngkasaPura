<!-- SUB-NAVIGATION PILL TABS (7 SUB-DATA SAFETY AUTOCRAT) -->
<nav class="bg-card-bg rounded-xl border border-border-subtle p-2 shadow-xs overflow-x-auto">
  <div class="flex items-center gap-1.5 min-w-max">
    
    <!-- 1. Laporan Autocrat -->
    <a href="{{ route('admin.verifikasi.autocrat') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-label-md transition-all {{ ($currentSub === 'autocrat' || empty($currentSub)) ? 'bg-injourney-teal text-white font-bold shadow-sm' : 'text-secondary hover:text-on-surface hover:bg-surface-container-low' }}">
      <span class="material-symbols-outlined text-base">description</span>
      <span>Laporan Autocrat</span>
      <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentSub === 'autocrat' || empty($currentSub)) ? 'bg-white/20 text-white' : 'bg-surface-container-high text-secondary' }}">
        {{ $totalLaporan ?? 0 }}
      </span>
    </a>

    <!-- 2. Patroli -->
    <a href="{{ route('admin.verifikasi.patroli') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-label-md transition-all {{ $currentSub === 'patroli' ? 'bg-injourney-teal text-white font-bold shadow-sm' : 'text-secondary hover:text-on-surface hover:bg-surface-container-low' }}">
      <span class="material-symbols-outlined text-base">commute</span>
      <span>Patroli</span>
      <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $currentSub === 'patroli' ? 'bg-white/20 text-white' : 'bg-surface-container-high text-secondary' }}">
        {{ $totalLaporan ?? 0 }} Sesi
      </span>
    </a>

    <!-- 3. Detail Temuan -->
    <a href="{{ route('admin.verifikasi.detail-temuan') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-label-md transition-all {{ $currentSub === 'detail-temuan' ? 'bg-injourney-teal text-white font-bold shadow-sm' : 'text-secondary hover:text-on-surface hover:bg-surface-container-low' }}">
      <span class="material-symbols-outlined text-base">checklist</span>
      <span>Detail Temuan</span>
      <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $currentSub === 'detail-temuan' ? 'bg-white/20 text-white' : 'bg-surface-container-high text-secondary' }}">
        {{ $totalSatwa ?? 0 }} Ekor
      </span>
    </a>

    <!-- 4. Back Up Detail Temuan -->
    <a href="{{ route('admin.verifikasi.backup-temuan') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-label-md transition-all {{ $currentSub === 'backup-temuan' ? 'bg-injourney-teal text-white font-bold shadow-sm' : 'text-secondary hover:text-on-surface hover:bg-surface-container-low' }}">
      <span class="material-symbols-outlined text-base">backup</span>
      <span>Back Up Detail Temuan</span>
      <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $currentSub === 'backup-temuan' ? 'bg-white/20 text-white' : 'bg-surface-container-high text-secondary' }}">
        Audit Log
      </span>
    </a>

    <!-- 5. Master Titik -->
    <a href="{{ route('admin.verifikasi.master-titik') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-label-md transition-all {{ $currentSub === 'master-titik' ? 'bg-injourney-teal text-white font-bold shadow-sm' : 'text-secondary hover:text-on-surface hover:bg-surface-container-low' }}">
      <span class="material-symbols-outlined text-base">location_on</span>
      <span>Master Titik</span>
      <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $currentSub === 'master-titik' ? 'bg-white/20 text-white' : 'bg-surface-container-high text-secondary' }}">
        {{ count($masterTitikList ?? []) }} Titik
      </span>
    </a>

    <!-- 6. Master Jenis -->
    <a href="{{ route('admin.verifikasi.master-jenis') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-label-md transition-all {{ $currentSub === 'master-jenis' ? 'bg-injourney-teal text-white font-bold shadow-sm' : 'text-secondary hover:text-on-surface hover:bg-surface-container-low' }}">
      <span class="material-symbols-outlined text-base">flutter_dash</span>
      <span>Master Jenis</span>
      <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $currentSub === 'master-jenis' ? 'bg-white/20 text-white' : 'bg-surface-container-high text-secondary' }}">
        {{ count($masterJenisList ?? []) }} Spesies
      </span>
    </a>

    <!-- 7. Master Preview -->
    <a href="{{ route('admin.verifikasi.master-preview') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-label-md transition-all {{ $currentSub === 'master-preview' ? 'bg-injourney-teal text-white font-bold shadow-sm' : 'text-secondary hover:text-on-surface hover:bg-surface-container-low' }}">
      <span class="material-symbols-outlined text-base">photo_library</span>
      <span>Master Preview</span>
      <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $currentSub === 'master-preview' ? 'bg-white/20 text-white' : 'bg-surface-container-high text-secondary' }}">
        Galeri
      </span>
    </a>

  </div>
</nav>
