<!-- FLASH ALERT NOTIFICATIONS -->
@if(session('sukses'))
  <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between shadow-2xs">
    <span class="flex items-center gap-2">
      <span class="material-symbols-outlined text-sm text-resolved-emerald">check_circle</span>
      {{ session('sukses') }}
    </span>
    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold cursor-pointer">&times;</button>
  </div>
@endif
@if(session('error'))
  <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between shadow-2xs">
    <span class="flex items-center gap-2">
      <span class="material-symbols-outlined text-sm text-warning-rose">error</span>
      {{ session('error') }}
    </span>
    <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold cursor-pointer">&times;</button>
  </div>
@endif
