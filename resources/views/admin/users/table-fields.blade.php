<!-- TAB 2: PENGATURAN FIELD FORMULIR TABLE PANEL -->
<div class="bg-card-bg rounded-xl border border-border-subtle shadow-sm p-4 sm:p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle mb-4">
    <div>
      <h3 class="text-sm sm:text-base font-bold text-slate-navy flex items-center gap-2">
        <span class="material-symbols-outlined text-injourney-teal">tune</span>
        Pengaturan Field Form Pelaporan Dinamis
      </h3>
      <p class="text-[11px] text-secondary mt-0.5">
        Atur parameter input formulir pelaporan petugas lapangan, tipe data, dan validasi isian wajib.
      </p>
    </div>

    <button type="button" onclick="openModal('modalAddField')" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs transition-all active:scale-[0.98] self-start sm:self-center cursor-pointer">
      <span class="material-symbols-outlined text-sm">add</span>
      <span>+ Tambah Field Baru</span>
    </button>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs border-collapse">
      <thead>
        <tr class="bg-surface-container-low text-secondary text-[10px] uppercase font-bold tracking-wider border-b border-border-subtle">
          <th class="py-2.5 px-3 w-16">Urutan</th>
          <th class="py-2.5 px-3 min-w-[180px]">Label Field</th>
          <th class="py-2.5 px-3 min-w-[120px]">Tipe Input</th>
          <th class="py-2.5 px-3 min-w-[130px]">Status Wajib</th>
          <th class="py-2.5 px-3 min-w-[110px]">Status Aktif</th>
          <th class="py-2.5 px-3 text-right min-w-[120px]">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle text-[11px]">
        @foreach($fields as $f)
          <tr class="hover:bg-surface-container-low/60 transition-colors">
            <td class="py-2.5 px-3 font-code-coordinate font-bold text-slate-navy">{{ $f->urutan }}</td>
            <td class="py-2.5 px-3 font-semibold text-slate-navy">{{ $f->label }}</td>
            <td class="py-2.5 px-3">
              <span class="px-2 py-0.5 rounded bg-surface-container font-code-coordinate uppercase text-[10px] font-bold text-slate-700">
                {{ $f->tipe }}
              </span>
            </td>
            <td class="py-2.5 px-3">
              <span class="inline-flex items-center gap-1 text-[10px] font-semibold {{ $f->wajib ? 'text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full' : 'text-slate-500' }}">
                {{ $f->wajib ? 'Wajib (Required)' : 'Opsional' }}
              </span>
            </td>
            <td class="py-2.5 px-3">
              <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $f->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                {{ $f->aktif ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="py-2.5 px-3 text-right whitespace-nowrap">
              <div class="flex items-center justify-end gap-2">
                <form action="{{ route('admin.manajemen.fields.toggle', $f->id) }}" method="POST" class="inline m-0">
                  @csrf
                  <button type="submit" class="text-xs font-semibold {{ $f->aktif ? 'text-amber-600 hover:underline' : 'text-emerald-600 hover:underline' }} cursor-pointer">
                    {{ $f->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </form>
                <form action="{{ route('admin.manajemen.fields.delete', $f->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus field {{ $f->label }}?');">
                  @csrf
                  <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline cursor-pointer">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
