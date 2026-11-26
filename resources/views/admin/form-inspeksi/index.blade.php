<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas-light text-on-surface">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pengaturan Form Inspeksi - InJourney Airports</title>

  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

  <!-- Google Fonts: DM Sans & Material Symbols -->
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <!-- Tailwind CSS CDN with configuration -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#006874",
            "primary-fixed": "#97f0ff",
            "alert-amber": "#F59E0B",
            "tertiary": "#006780",
            "secondary": "#565e74",
            "injourney-teal": "#00A3B4",
            "injourney-dark-teal": "#0891B2",
            "slate-navy": "#0F172A",
            "border-subtle": "#E2E8F0",
            "canvas-light": "#F8FAFC",
            "on-surface": "#0b1c30",
            "card-bg": "#FFFFFF",
            "resolved-emerald": "#10B981",
            "warning-rose": "#EF4444",
          },
          fontFamily: {
            sans: ["DM Sans", "sans-serif"],
          }
        }
      }
    }
  </script>
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    /* Custom scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #F8FAFC; }
    ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

    .logbook-table-wrapper {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    .logbook-table-wrapper table {
      min-width: 960px;
    }
  </style>
</head>
<body class="min-h-full bg-canvas-light text-on-surface font-sans antialiased selection:bg-injourney-teal selection:text-white flex flex-col">

  @include('partials.navbar', ['activePage' => 'form_inspeksi'])

  @include('partials.page-header', [
    'title' => 'Pengaturan Form Inspeksi',
    'subtitle' => 'Atur isian yang muncul di form inspeksi petugas: wajib atau opsional, tampil atau disembunyikan, urutan, dan pilihan jawaban.',
    'icon' => 'tune',
    'actions' => '<a href="#tambahIsian" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white shadow-xs transition-colors"><span class="material-symbols-outlined text-base">add_circle</span><span>Tambah Isian</span></a>',
  ])

  <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    @if(session('success'))
      <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-start gap-2" role="status">
        <span class="material-symbols-outlined text-lg">check_circle</span><span>{{ session('success') }}</span>
      </div>
    @endif
    @if(session('error'))
      <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm flex items-start gap-2" role="alert">
        <span class="material-symbols-outlined text-lg">error</span><span>{{ session('error') }}</span>
      </div>
    @endif
    @if($errors->any())
      <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm" role="alert">
        <p class="font-bold mb-1">Pengaturan belum disimpan:</p>
        <ul class="list-disc list-inside text-xs space-y-0.5">
          @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif

    <!-- Ringkasan -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="bg-white rounded-xl border border-border-subtle p-4 shadow-sm">
        <p class="text-[11px] uppercase tracking-wider font-bold text-secondary">Isian tampil di form</p>
        <p class="text-2xl font-bold text-slate-navy mt-1">{{ $totalAktif }} <span class="text-sm font-medium text-secondary">dari {{ $fields->count() }}</span></p>
      </div>
      <div class="bg-white rounded-xl border border-border-subtle p-4 shadow-sm">
        <p class="text-[11px] uppercase tracking-wider font-bold text-secondary">Isian wajib</p>
        <p class="text-2xl font-bold text-slate-navy mt-1">{{ $totalWajib }}</p>
      </div>
      <div class="bg-white rounded-xl border border-border-subtle p-4 shadow-sm text-xs text-secondary leading-relaxed">
        <p class="flex items-center gap-1.5 font-bold text-slate-navy mb-1"><span class="material-symbols-outlined text-base text-amber-600">lock</span>Selalu ada di form</p>
        Tanggal, gridmap, dan tanda tangan (dipakai Berita Acara dan prediksi), serta jam pengamatan, pengamatan satwa, dan foto.
      </div>
    </section>

    <!-- Pengaturan semua isian (disimpan sekaligus) -->
    <form method="POST" action="{{ route('admin.form-inspeksi.update') }}" class="bg-white rounded-xl border border-border-subtle shadow-sm">
      @csrf
      @method('PUT')

      <div class="px-4 sm:px-5 py-3 border-b border-border-subtle flex flex-wrap items-center justify-between gap-2">
        <div>
          <h2 class="text-sm font-bold text-slate-navy">Isian form inspeksi</h2>
          <p class="text-[11px] text-secondary">Urutan menentukan posisi isian tambahan; isian bawaan tetap di bagiannya masing-masing.</p>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white shadow-xs transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-base">save</span>Simpan perubahan
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs min-w-[860px]">
          <thead>
            <tr class="bg-slate-50 text-secondary text-[10px] uppercase font-bold tracking-wider border-b border-border-subtle">
              <th class="py-2.5 px-3 w-20">Urutan</th>
              <th class="py-2.5 px-3">Label &amp; petunjuk isian</th>
              <th class="py-2.5 px-3 w-36">Jenis</th>
              <th class="py-2.5 px-3 w-24 text-center">Wajib</th>
              <th class="py-2.5 px-3 w-28 text-center">Tampil di form</th>
              <th class="py-2.5 px-3 w-20 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle">
            @foreach($fields as $field)
              @php
                $locked = $field->isLocked();
                $oldValue = fn (string $key, $default) => old("fields.{$field->id}.{$key}", $default);
              @endphp
              <tr class="align-top {{ $field->aktif ? '' : 'bg-slate-50/70 text-slate-500' }}">
                <td class="py-3 px-3">
                  <input type="number" name="fields[{{ $field->id }}][urutan]" value="{{ $oldValue('urutan', $field->urutan) }}" min="1" max="999" required aria-label="Urutan {{ $field->label }}"
                         class="w-16 rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                </td>
                <td class="py-3 px-3 space-y-1.5">
                  <input type="text" name="fields[{{ $field->id }}][label]" value="{{ $oldValue('label', $field->label) }}" maxlength="100" required aria-label="Label isian"
                         class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-navy">
                  {{-- Petunjuk hanya untuk isian yang menampilkannya di form (nama petugas terisi otomatis, tanda tangan berupa kanvas). --}}
                  @if(in_array($field->tipe, ['text', 'textarea', 'number'], true) && (! $field->isStandard() || in_array($field->nama_field, ['ciri_ukuran', 'aktivitas_satwa', 'detail_pengusiran'], true)))
                    <input type="text" name="fields[{{ $field->id }}][placeholder]" value="{{ $oldValue('placeholder', $field->placeholder) }}" maxlength="255" placeholder="Petunjuk isian (opsional)" aria-label="Petunjuk isian"
                           class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-[11px] text-slate-600">
                  @endif
                  @if($field->tipe === 'dropdown')
                    <details class="text-[11px]">
                      <summary class="cursor-pointer text-[#007fa3] font-semibold">Pilihan jawaban ({{ $field->options->count() ?: 'bawaan aplikasi' }})</summary>
                      <textarea name="fields[{{ $field->id }}][opsi]" rows="4" placeholder="Satu pilihan per baris. Kosong = pakai pilihan bawaan aplikasi."
                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-[11px]">{{ $oldValue('opsi', $field->options->pluck('nilai')->implode("\n")) }}</textarea>
                      @if($field->nama_field === 'kondisi_cuaca')
                        <p class="text-amber-700 mt-1">Isi otomatis dari cuaca memakai pilihan: Cerah, Berawan, Hujan Ringan, Hujan Lebat, Berkabut.</p>
                      @endif
                    </details>
                  @endif
                </td>
                <td class="py-3 px-3">
                  <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[10px] uppercase">{{ $customTypes[$field->tipe] ?? $field->tipe }}</span>
                  <p class="mt-1 text-[10px] {{ $locked ? 'text-amber-700' : 'text-secondary' }} flex items-center gap-1">
                    @if($locked)
                      <span class="material-symbols-outlined text-xs">lock</span> Inti, selalu wajib
                    @elseif($field->isStandard())
                      Bawaan
                    @else
                      Isian tambahan
                    @endif
                  </p>
                </td>
                <td class="py-3 px-3 text-center">
                  <input type="hidden" name="fields[{{ $field->id }}][wajib]" value="{{ $locked ? 1 : 0 }}">
                  <input type="checkbox" name="fields[{{ $field->id }}][wajib]" value="1" @checked($oldValue('wajib', $field->wajib)) @disabled($locked) aria-label="Wajib diisi: {{ $field->label }}"
                         class="w-4 h-4 rounded border-slate-300 text-[#00A9C1] focus:ring-[#00A9C1] {{ $locked ? 'opacity-50' : 'cursor-pointer' }}">
                </td>
                <td class="py-3 px-3 text-center">
                  <input type="hidden" name="fields[{{ $field->id }}][aktif]" value="{{ $locked ? 1 : 0 }}">
                  <input type="checkbox" name="fields[{{ $field->id }}][aktif]" value="1" @checked($oldValue('aktif', $field->aktif)) @disabled($locked) aria-label="Tampilkan di form: {{ $field->label }}"
                         class="w-4 h-4 rounded border-slate-300 text-[#00A9C1] focus:ring-[#00A9C1] {{ $locked ? 'opacity-50' : 'cursor-pointer' }}">
                </td>
                <td class="py-3 px-3 text-right">
                  @unless($field->isStandard())
                    <button type="submit" form="hapusIsian{{ $field->id }}" class="text-[11px] font-semibold text-rose-600 hover:underline cursor-pointer">Hapus</button>
                  @endunless
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="px-4 sm:px-5 py-3 border-t border-border-subtle flex justify-end">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white shadow-xs transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-base">save</span>Simpan perubahan
        </button>
      </div>
    </form>

    {{-- Form hapus diletakkan di luar form utama karena form HTML tidak boleh bersarang --}}
    @foreach($fields as $field)
      @unless($field->isStandard())
        <form id="hapusIsian{{ $field->id }}" method="POST" action="{{ route('admin.form-inspeksi.destroy', $field) }}" class="hidden"
              onsubmit="return confirm('Hapus isian ini dari form inspeksi? Data di laporan lama tetap tersimpan.');">
          @csrf
          @method('DELETE')
        </form>
      @endunless
    @endforeach

    <!-- Tambah isian baru -->
    <section id="tambahIsian" class="bg-white rounded-xl border border-border-subtle shadow-sm p-4 sm:p-5">
      <h2 class="text-sm font-bold text-slate-navy">Tambah isian baru</h2>
      <p class="text-[11px] text-secondary mb-4">Muncul di bagian "Isian Tambahan" form inspeksi, sebelum tanda tangan. Jawabannya ikut tampil di detail laporan admin.</p>

      <form method="POST" action="{{ route('admin.form-inspeksi.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        @csrf
        <label class="flex flex-col gap-1 font-semibold text-slate-700">
          Label isian
          <input type="text" name="label" value="{{ old('label') }}" maxlength="100" required placeholder="cth: Nomor kendaraan patroli"
                 class="rounded-lg border border-slate-300 px-3 py-2 font-normal">
        </label>
        <label class="flex flex-col gap-1 font-semibold text-slate-700">
          Jenis isian
          <select name="tipe" id="tipeIsianBaru" class="rounded-lg border border-slate-300 px-3 py-2 font-normal bg-white">
            @foreach($customTypes as $value => $typeLabel)
              <option value="{{ $value }}" @selected(old('tipe') === $value)>{{ $typeLabel }}</option>
            @endforeach
          </select>
        </label>
        <label class="flex flex-col gap-1 font-semibold text-slate-700">
          <span>Petunjuk isian <span class="font-normal text-secondary">(opsional)</span></span>
          <input type="text" name="placeholder" value="{{ old('placeholder') }}" maxlength="255" class="rounded-lg border border-slate-300 px-3 py-2 font-normal">
        </label>
        <label class="flex items-center gap-2 font-semibold text-slate-700 sm:mt-6">
          <input type="hidden" name="wajib" value="0">
          <input type="checkbox" name="wajib" value="1" @checked(old('wajib')) class="w-4 h-4 rounded border-slate-300 text-[#00A9C1] focus:ring-[#00A9C1]">
          Wajib diisi petugas
        </label>
        <label id="opsiIsianBaru" class="sm:col-span-2 flex flex-col gap-1 font-semibold text-slate-700 {{ old('tipe') === 'dropdown' ? '' : 'hidden' }}">
          <span>Pilihan jawaban <span class="font-normal text-secondary">(satu per baris)</span></span>
          <textarea name="opsi" rows="4" class="rounded-lg border border-slate-300 px-3 py-2 font-normal">{{ old('opsi') }}</textarea>
        </label>
        <div class="sm:col-span-2 flex justify-end">
          <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 font-bold rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white shadow-xs transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-base">add</span>Tambahkan ke form
          </button>
        </div>
      </form>
    </section>
  </main>

  <script>
    // Kotak pilihan jawaban hanya tampil untuk jenis dropdown.
    document.getElementById('tipeIsianBaru')?.addEventListener('change', (event) => {
      document.getElementById('opsiIsianBaru')?.classList.toggle('hidden', event.target.value !== 'dropdown');
    });
  </script>
</body>
</html>
