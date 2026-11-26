{{-- PRD v2.1 FR-0.7: peringatan celah data cuaca / observasi pada jendela terakhir. --}}
@if(!empty($peringatanKualitasData))
  @php
    $celahPerSumber = collect($peringatanKualitasData)->groupBy('sumber');
    $labelSumber = [
      \App\Services\DataQuality\DataQualityService::SOURCE_WEATHER => 'Cuaca',
      \App\Services\DataQuality\DataQualityService::SOURCE_OBSERVATION => 'Observasi',
    ];
  @endphp
  <section id="dataQualityAlert" data-signature="{{ md5(collect($peringatanKualitasData)->pluck('pesan')->implode('|')) }}" class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 shadow-sm" role="alert" aria-labelledby="dataQualityTitle">
    <div class="flex items-start gap-3">
      <span class="material-symbols-outlined text-amber-600 text-xl shrink-0">warning</span>
      <div class="min-w-0 flex-1">
        <p id="dataQualityTitle" class="text-sm font-bold text-amber-900">Peringatan kualitas data</p>
        <p class="text-xs text-amber-800 mt-0.5">
          Ada data kosong lebih dari {{ config('whms_risk.kualitas_data.maks_interval_kosong') }} interval berturut-turut dalam {{ config('whms_risk.kualitas_data.jendela_jam') }} jam terakhir. Perhitungan risiko pada rentang ini kurang akurat.
        </p>

        <ul class="mt-2 space-y-1">
          @foreach($celahPerSumber as $sumber => $celah)
            @php $terakhir = $celah->last(); @endphp
            <li class="text-xs text-amber-900 flex flex-wrap items-baseline gap-x-2">
              <span class="font-bold">{{ $labelSumber[$sumber] ?? ucfirst($sumber) }}:</span>
              <span>{{ $terakhir['pesan'] }}</span>
              @if($celah->count() > 1)
                <span class="text-amber-700">(+{{ $celah->count() - 1 }} celah lain)</span>
              @endif
            </li>
          @endforeach
        </ul>

        @if(count($peringatanKualitasData) > count($celahPerSumber))
          <details class="mt-2 text-xs text-amber-900">
            <summary class="cursor-pointer font-semibold text-amber-800 hover:text-amber-900">Lihat semua celah ({{ count($peringatanKualitasData) }})</summary>
            <ul class="mt-1.5 pl-4 list-disc space-y-0.5">
              @foreach($peringatanKualitasData as $celah)
                <li>{{ $celah['pesan'] }}</li>
              @endforeach
            </ul>
          </details>
        @endif
      </div>
      <button type="button" data-dismiss-data-quality class="shrink-0 -mt-1 -mr-1 p-1 rounded text-amber-700 hover:text-amber-900 hover:bg-amber-100 transition-colors cursor-pointer" aria-label="Tutup peringatan kualitas data" title="Tutup">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>
  </section>

  <script>
    (function () {
      var alertBox = document.getElementById('dataQualityAlert');
      if (!alertBox) return;
      // Ditutup per kumpulan celah: bila muncul celah baru, peringatan tampil lagi.
      var storageKey = 'whms_data_quality_dismissed';
      var signature = alertBox.dataset.signature;
      try {
        if (localStorage.getItem(storageKey) === signature) alertBox.remove();
      } catch (e) {}
      var closeButton = alertBox.querySelector('[data-dismiss-data-quality]');
      if (closeButton) {
        closeButton.addEventListener('click', function () {
          try { localStorage.setItem(storageKey, signature); } catch (e) {}
          alertBox.remove();
        });
      }
    })();
  </script>
@endif
