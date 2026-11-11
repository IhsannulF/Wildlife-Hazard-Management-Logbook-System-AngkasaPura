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
@if($errors->any())
  <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-start justify-between shadow-2xs gap-3">
    <div class="space-y-1">
      <span class="flex items-center gap-2 font-bold">
        <span class="material-symbols-outlined text-sm text-warning-rose">warning</span>
        Validasi input gagal:
      </span>
      <ul class="list-disc pl-5 font-medium space-y-0.5">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
    <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold cursor-pointer">&times;</button>
  </div>
@endif
