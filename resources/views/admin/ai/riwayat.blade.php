<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas-light text-on-surface">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Riwayat Prediksi Birdstrike AI - InJourney Airports</title>

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
      min-width: 1080px;
    }
  </style>
</head>
<body class="min-h-full bg-canvas-light text-on-surface font-sans antialiased selection:bg-injourney-teal selection:text-white flex flex-col">

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'ai_prediksi'])

  <!-- SUB-HEADER & COMMAND STRIP -->
  @include('partials.navbar-ai', [
    'title' => 'Riwayat Prediksi Birdstrike AI',
    'subtitle' => 'Audit transparansi masukan model Gemini, evaluasi probabilitas bahaya, dan validasi kejadian insiden di sisi udara.',
    'icon' => 'query_stats',
  ])

  <!-- ================= MAIN OPERATIONAL CONTENT CONTAINER ================= -->
  <main class="flex-1 max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
      <a href="{{ route('admin.dashboard') }}" class="hover:text-[#00A9C1] transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">home</span>
        Beranda
      </a>
      <span>/</span>
      <span class="text-slate-400">Kecerdasan Buatan (AI)</span>
      <span>/</span>
      <span class="text-slate-800 font-semibold">Riwayat Prediksi Birdstrike</span>
    </nav>

    <!-- Flash Alert Messages -->
    @if(session('success'))
      <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-3.5 flex items-center justify-between text-xs font-medium shadow-2xs">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-emerald-600 text-lg">check_circle</span>
          <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">
          <span class="material-symbols-outlined text-base">close</span>
        </button>
      </div>
    @endif

    @if(session('error'))
      <div class="bg-rose-50 text-rose-800 border border-rose-200 rounded-xl p-3.5 flex items-center justify-between text-xs font-medium shadow-2xs">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-rose-600 text-lg">error</span>
          <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">
          <span class="material-symbols-outlined text-base">close</span>
        </button>
      </div>
    @endif

    <!-- 2. SUMMARY KPI BENTO CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1: Total Prediksi -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-cyan-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-cyan-50 text-[#007fa3] border border-cyan-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">insights</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Prediksi AI</div>
          <div class="text-xl font-extrabold text-slate-800 mt-0.5">
            {{ number_format($metrics['total_prediksi']) }}
            <span class="text-xs font-medium text-slate-400">kali</span>
          </div>
        </div>
      </div>

      <!-- Card 2: Prediksi Tinggi/Kritis -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-rose-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-700 border border-rose-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">warning</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tinggi / Kritis</div>
          <div class="text-xl font-extrabold text-rose-700 mt-0.5">
            {{ number_format($metrics['tinggi_kritis']) }}
            <span class="text-xs font-medium text-rose-400">kejadian</span>
          </div>
        </div>
      </div>

      <!-- Card 3: Akurasi Validasi Insiden -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-emerald-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">fact_check</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Akurasi Validasi</div>
          <div class="text-xl font-extrabold text-emerald-700 mt-0.5">
            {{ $metrics['akurasi_persen'] }}%
            <span class="text-xs font-medium text-emerald-500">({{ $metrics['diikuti_insiden'] }}/{{ $metrics['tinggi_kritis'] }})</span>
          </div>
        </div>
      </div>

      <!-- Card 4: Penggunaan Kuota On-Demand -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-amber-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">api</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kuota Hari Ini</div>
          <div class="text-xl font-extrabold text-slate-800 mt-0.5">
            {{ $metrics['manual_hari_ini'] }} <span class="text-slate-400 font-semibold">/ {{ $metrics['kuota_manual_max'] }}</span>
            <span class="text-xs font-medium text-slate-400">manual</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. TABLE CARD: ARSIP PREDIKSI BIRDSTRIKE -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <!-- Toolbar Filter & Header -->
      <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-cyan-50 text-[#00A9C1] flex items-center justify-center border border-cyan-100 shrink-0">
            <span class="material-symbols-outlined text-xl">history</span>
          </div>
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-800">Daftar Arsip Prediksi Birdstrike</h2>
            <p class="text-[11px] text-slate-500">Log riwayat komputasi probabilistik dan preseden mitigasi.</p>
          </div>
        </div>

        <!-- Filter Controls -->
        <form method="GET" action="{{ route('admin.ai.prediksi.index') }}" class="flex flex-wrap items-center gap-2">
          <select name="level" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200/90 focus:outline-none focus:border-[#00A9C1] bg-slate-50/80 text-slate-700">
            <option value="">Semua Level Bahaya</option>
            <option value="rendah" {{ request('level') == 'rendah' ? 'selected' : '' }}>Rendah</option>
            <option value="sedang" {{ request('level') == 'sedang' ? 'selected' : '' }}>Sedang</option>
            <option value="tinggi" {{ request('level') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
            <option value="kritis" {{ request('level') == 'kritis' ? 'selected' : '' }}>Kritis</option>
          </select>

          <select name="status" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200/90 focus:outline-none focus:border-[#00A9C1] bg-slate-50/80 text-slate-700">
            <option value="">Semua Eksekusi</option>
            <option value="sukses" {{ request('status') == 'sukses' ? 'selected' : '' }}>Sukses</option>
            <option value="gagal" {{ request('status') == 'gagal' ? 'selected' : '' }}>Gagal</option>
          </select>

          <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-colors cursor-pointer shadow-2xs">
            Filter
          </button>
          @if(request()->anyFilled(['level', 'status']))
            <a href="{{ route('admin.ai.prediksi.index') }}" class="px-2.5 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline">
              Reset
            </a>
          @endif
        </form>
      </div>

      <!-- Table Wrapper -->
      <div class="logbook-table-wrapper">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200/80">
              <th class="py-3 px-4 min-w-[140px]">Tanggal &amp; Waktu</th>
              <th class="py-3 px-4 min-w-[90px]">Pemicu</th>
              <th class="py-3 px-4 min-w-[110px]">Probabilitas</th>
              <th class="py-3 px-4 min-w-[110px]">Level Bahaya</th>
              <th class="py-3 px-4 min-w-[130px]">Jam Rawan</th>
              <th class="py-3 px-4 min-w-[120px]">Grid Kritis</th>
              <th class="py-3 px-4 min-w-[120px]">Status Advisory</th>
              <th class="py-3 px-4 min-w-[120px]">Validasi Insiden</th>
              <th class="py-3 px-4 text-center min-w-[80px]">Audit</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            @forelse($predictions as $p)
              @php
                $badgeLevel = match($p->level_bahaya) {
                  'kritis' => 'bg-rose-100 text-rose-800 border-rose-200',
                  'tinggi' => 'bg-amber-100 text-amber-800 border-amber-200',
                  'sedang' => 'bg-cyan-100 text-[#007fa3] border-cyan-200',
                  'rendah' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                  default => 'bg-slate-100 text-slate-600 border-slate-200'
                };
              @endphp
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-800">{{ $p->tanggal_prediksi?->format('d M Y') }}</div>
                  <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $p->waktu_analisis?->format('H:i') }} WIB ({{ $p->durasi_ms ?? 0 }} ms)</div>
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->trigger === 'terjadwal' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                    {{ ucfirst($p->trigger) }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  @if($p->status_eksekusi === 'sukses')
                    <div class="font-bold text-slate-800 font-mono text-sm">{{ round($p->skor_probabilitas * 100) }}%</div>
                    <div class="text-[10px] text-slate-400">Conf: {{ round(($p->confidence_score ?? 0) * 100) }}%</div>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">Gagal</span>
                  @endif
                </td>
                <td class="py-3 px-4">
                  @if($p->level_bahaya)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-bold text-[10px] border {{ $badgeLevel }}">
                      {{ strtoupper($p->level_bahaya) }}
                    </span>
                  @else
                    <span class="text-slate-400">-</span>
                  @endif
                </td>
                <td class="py-3 px-4 font-mono text-slate-700 font-medium">
                  {{ $p->jam_rawan_mulai && $p->jam_rawan_selesai ? "{$p->jam_rawan_mulai} - {$p->jam_rawan_selesai}" : '-' }}
                </td>
                <td class="py-3 px-4">
                  @if(!empty($p->grid_kritis))
                    <div class="flex flex-wrap gap-1">
                      @foreach(array_slice($p->grid_kritis, 0, 3) as $grid)
                        <span class="px-1.5 py-0.5 rounded bg-slate-100 font-bold font-mono text-[10px] text-slate-700 border border-slate-200">{{ $grid }}</span>
                      @endforeach
                    </div>
                  @else
                    <span class="text-slate-400">-</span>
                  @endif
                </td>
                <td class="py-3 px-4">
                  @php
                    $badgeAdvisory = match($p->status_advisory) {
                      'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                      'diakui' => 'bg-blue-50 text-blue-700 border-blue-200',
                      default => 'bg-slate-100 text-slate-500 border-slate-200'
                    };
                  @endphp
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeAdvisory }}">
                    {{ ucfirst($p->status_advisory) }}
                  </span>
                  @if($p->status_advisory === 'diakui' && $p->acknowledgedBy)
                    <div class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $p->acknowledgedBy->namalengkap ?: $p->acknowledgedBy->username }}</div>
                  @endif
                </td>
                <td class="py-3 px-4">
                  @if($p->insiden_terjadi === true)
                    <span class="inline-flex items-center gap-1 text-rose-700 font-bold text-[11px]">
                      <span class="material-symbols-outlined text-sm">report_problem</span>
                      Insiden Terjadi
                    </span>
                  @elseif($p->insiden_terjadi === false)
                    <span class="inline-flex items-center gap-1 text-emerald-700 font-medium text-[11px]">
                      <span class="material-symbols-outlined text-sm">verified</span>
                      Aman / Nihil
                    </span>
                  @else
                    <span class="text-slate-400 text-[11px] italic">Belum dievaluasi</span>
                  @endif
                </td>
                <td class="py-3 px-4 text-center">
                  <button type="button" onclick="showAuditModal({{ json_encode($p) }})" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:text-[#00A9C1] hover:bg-cyan-50 transition-colors cursor-pointer" title="Lihat Rekomendasi & Audit Payload">
                    <span class="material-symbols-outlined text-lg">visibility</span>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="py-12 text-center text-slate-400">
                  <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">analytics</span>
                  <p class="font-medium text-slate-500">Belum ada riwayat inferensi AI yang tersimpan.</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Inferensi otomatis dijalankan setiap pukul 05:00 WIB via Scheduler.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      @if($predictions->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
          {{ $predictions->links() }}
        </div>
      @endif
    </div>

  </main>

  <!-- ================= MODAL AUDIT PREDIKSI ================= -->
  <div id="modalAudit" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all p-5 sm:p-6 max-h-[90vh] flex flex-col">
      <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
        <h3 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
          <span class="material-symbols-outlined text-[#00A9C1]">auto_awesome</span>
          Detail Analisis &amp; Rekomendasi Taktis AI
        </h3>
        <button type="button" onclick="closeModal('modalAudit')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>

      <div class="overflow-y-auto space-y-4 text-xs pr-1">
        <div>
          <h4 class="font-bold text-slate-700 mb-1">Ringkasan Eksekutif</h4>
          <p id="auditSummary" class="text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200 leading-relaxed"></p>
        </div>

        <div>
          <h4 class="font-bold text-slate-700 mb-1">Rekomendasi Taktis Mitigasi</h4>
          <ul id="auditRecs" class="list-decimal list-inside space-y-1 text-slate-700 bg-cyan-50/50 p-3 rounded-xl border border-cyan-100"></ul>
        </div>

        <div>
          <h4 class="font-bold text-slate-700 mb-1">Faktor Kontributor Risiko</h4>
          <ul id="auditFactors" class="list-disc list-inside space-y-1 text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200"></ul>
        </div>

        <div>
          <h4 class="font-bold text-slate-700 mb-1">Audit Snapshot Masukan Payload (Sanitized)</h4>
          <pre id="auditPayload" class="text-[10px] bg-slate-900 text-slate-200 p-3 rounded-xl overflow-x-auto max-h-48"></pre>
        </div>
      </div>

      <div class="pt-4 border-t border-slate-100 flex justify-end">
        <button type="button" onclick="closeModal('modalAudit')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-colors">
          Tutup
        </button>
      </div>
    </div>
  </div>

  <script>
    function openModal(id) {
      const el = document.getElementById(id);
      if (el) {
        el.classList.remove('hidden');
        el.classList.add('flex');
      }
    }

    function closeModal(id) {
      const el = document.getElementById(id);
      if (el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
      }
    }

    function showAuditModal(p) {
      document.getElementById('auditSummary').innerText = p.ringkasan_eksekutif || 'Tidak ada ringkasan.';
      
      const recsList = document.getElementById('auditRecs');
      recsList.innerHTML = '';
      if (p.rekomendasi_taktis && p.rekomendasi_taktis.length > 0) {
        p.rekomendasi_taktis.forEach(r => {
          const li = document.createElement('li');
          li.innerText = r;
          recsList.appendChild(li);
        });
      } else {
        recsList.innerHTML = '<li>Tidak ada rekomendasi.</li>';
      }

      const factorsList = document.getElementById('auditFactors');
      factorsList.innerHTML = '';
      if (p.faktor_penyebab && p.faktor_penyebab.length > 0) {
        p.faktor_penyebab.forEach(f => {
          const li = document.createElement('li');
          li.innerText = f;
          factorsList.appendChild(li);
        });
      } else {
        factorsList.innerHTML = '<li>Tidak ada catatan faktor kontributor.</li>';
      }

      document.getElementById('auditPayload').innerText = JSON.stringify(p.input_payload, null, 2);

      openModal('modalAudit');
    }
  </script>
</body>
</html>
