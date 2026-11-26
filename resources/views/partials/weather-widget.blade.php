{{-- Widget cuaca real-time (Geolocation + Open-Meteo). Logika di public/js/whms-weather.js --}}
<section data-whms-weather aria-live="polite" aria-busy="true" class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs {{ $class ?? '' }}">
  <div class="flex items-center justify-between gap-2 mb-3">
    <div class="flex items-center gap-2 min-w-0">
      <span class="material-symbols-outlined text-[#00A9C1] text-lg">partly_cloudy_day</span>
      <h2 class="text-xs sm:text-sm font-bold text-slate-900">Cuaca Saat Ini</h2>
    </div>
    <button type="button" data-weather-action="refresh" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-cyan-50/80 text-slate-500 hover:text-[#00A9C1] border border-slate-200/90 flex items-center justify-center cursor-pointer transition-colors shrink-0" title="Perbarui data cuaca" aria-label="Perbarui data cuaca">
      <span class="material-symbols-outlined text-base">refresh</span>
    </button>
  </div>

  {{-- Loading --}}
  <div data-weather-state="loading" class="flex items-center gap-3 py-2">
    <div class="w-12 h-12 rounded-xl bg-slate-100 animate-pulse shrink-0"></div>
    <div class="flex-1 space-y-2">
      <div class="h-3 w-40 max-w-full bg-slate-100 rounded animate-pulse"></div>
      <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
        Meminta izin lokasi & memuat data cuaca…
      </p>
    </div>
  </div>

  {{-- Ready --}}
  <div data-weather-state="ready" class="hidden">
    <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
      <div class="flex items-center gap-3 min-w-0">
        <div data-weather="icon-wrap" class="w-12 h-12 rounded-xl border flex items-center justify-center shrink-0 bg-slate-100 text-slate-500 border-slate-200">
          <span data-weather="icon" class="material-symbols-outlined text-3xl">cloud</span>
        </div>
        <div class="min-w-0">
          <div class="text-2xl font-bold text-slate-900 leading-none"><span data-weather="temperature">–</span><span class="text-base font-semibold text-slate-500">°C</span></div>
          <div data-weather="condition" class="text-xs font-semibold text-slate-700 mt-1">–</div>
        </div>
      </div>

      <dl class="flex items-center gap-4 sm:gap-6 text-xs">
        <div class="flex items-center gap-1.5">
          <span class="material-symbols-outlined text-base text-sky-500">humidity_percentage</span>
          <div>
            <dt class="text-[10px] text-slate-500">Kelembapan</dt>
            <dd class="font-bold text-slate-800"><span data-weather="humidity">–</span>%</dd>
          </div>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="material-symbols-outlined text-base text-teal-500">air</span>
          <div>
            <dt class="text-[10px] text-slate-500">Angin</dt>
            <dd class="font-bold text-slate-800"><span data-weather="wind">–</span> km/j</dd>
          </div>
        </div>
      </dl>
    </div>

    <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-[10px] text-slate-500">
      <span class="flex items-center gap-1 min-w-0">
        <span class="material-symbols-outlined text-xs">location_on</span>
        <span data-weather="location" class="truncate">–</span>
      </span>
      <span><span data-weather="updated"></span> · Open-Meteo</span>
    </div>
    <p data-weather="notice" class="hidden mt-2 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1.5"></p>
  </div>

  {{-- Error --}}
  <div data-weather-state="error" class="hidden flex items-center justify-between gap-3 py-1">
    <p class="text-xs text-slate-600 flex items-center gap-1.5">
      <span class="material-symbols-outlined text-base text-rose-500">cloud_off</span>
      <span>Cuaca belum tersedia: <span data-weather="error"></span>.</span>
    </p>
    <button type="button" data-weather-action="refresh" class="text-xs font-semibold text-[#00A9C1] hover:underline cursor-pointer shrink-0">Coba lagi</button>
  </div>
</section>

@once
  <script src="{{ asset('js/whms-weather.js') }}?v={{ @filemtime(public_path('js/whms-weather.js')) }}" defer></script>
@endonce
