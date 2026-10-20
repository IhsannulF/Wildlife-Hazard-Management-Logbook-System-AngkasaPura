<!-- OPERATIONAL COMPLIANCE FOOTER STRIP -->
<footer class="mt-auto bg-card-bg border-t border-border-subtle py-3.5 px-4 sm:px-6 lg:px-8 text-xs text-secondary">
  <div class="max-w-[1920px] mx-auto flex flex-col md:flex-row items-center justify-between gap-2">
    <div class="flex items-center gap-2">
      <span class="w-2 h-2 rounded-full bg-resolved-emerald"></span>
      <span>Konektivitas Telemetri Grid Sisi Udara: <strong>Terhubung (AOCC Server Node 01)</strong></span>
      <span class="text-border-subtle">|</span>
      <span>Sinkronisasi Terakhir: <strong>{{ now()->format('H:i:s') }} WIB</strong></span>
    </div>
    <div class="text-[11px] font-code-coordinate text-slate-500">
      PT Angkasa Pura Indonesia © {{ date('Y') }} • Wildlife Hazard Management System • Ditjen Perhubungan Udara KP 283 / 2020
    </div>
  </div>
</footer>
