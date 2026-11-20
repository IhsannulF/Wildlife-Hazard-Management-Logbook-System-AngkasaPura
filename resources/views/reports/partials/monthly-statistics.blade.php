@php
  /** @var array<string, mixed> $bundle */
  $stats = $bundle['stats'];
  $kpi = $stats['kpi'];
  $dailyTrend = $stats['daily_trend'];
  $dayColWidth = round(100 / max(1, count($dailyTrend)), 3);
@endphp

<table class="bundle-part-banner">
  <tr>
    <td>
      <div class="bundle-part-eyebrow">Bagian III &bull; Analitik Operasional &amp; Temporal</div>
      <div class="bundle-part-title">REKAPITULASI STATISTIK &amp; GRAFIK BULANAN ({{ strtoupper($bundle['periode_label']) }})</div>
    </td>
    <td class="bundle-part-code">
      {{ $bundle['bundle_code'] }}<br>{{ $bundle['date_range_label'] }}
    </td>
  </tr>
</table>

<!-- A. KPI RINGKASAN BULANAN -->
<table class="stat-kpi-grid avoid-break">
  <tr>
    <td class="stat-kpi-cell">
      <div class="stat-kpi-card">
        <div class="stat-kpi-title">Total Berita Acara</div>
        <div class="stat-kpi-val">{{ $kpi['total_laporan'] }} Dokumen</div>
        <div class="stat-kpi-sub">{{ $kpi['total_tervalidasi'] }} sah &bull; {{ $kpi['total_pending'] }} menunggu</div>
      </div>
    </td>
    <td class="stat-kpi-cell">
      <div class="stat-kpi-card" style="border-left-color: #16a34a;">
        <div class="stat-kpi-title">Tingkat Validasi Safety</div>
        <div class="stat-kpi-val">{{ number_format((float) $kpi['persen_tervalidasi'], 1) }}%</div>
        <div class="stat-kpi-sub">Pengesahan Safety Manager</div>
      </div>
    </td>
    <td class="stat-kpi-cell stat-kpi-cell-last">
      <div class="stat-kpi-card" style="border-left-color: #d97706;">
        <div class="stat-kpi-title">Total Populasi Satwa</div>
        <div class="stat-kpi-val">{{ $kpi['total_ekor'] }} Ekor</div>
        <div class="stat-kpi-sub">{{ $kpi['total_foto'] }} foto ber-watermark</div>
      </div>
    </td>
  </tr>
  <tr>
    <td class="stat-kpi-cell">
      <div class="stat-kpi-card" style="border-left-color: #be123c;">
        <div class="stat-kpi-title">Rata-Rata / Puncak Risiko ICAO</div>
        <div class="stat-kpi-val">{{ number_format((float) $kpi['avg_risk_score'], 1) }} <span style="font-size: 7.5pt; font-weight: normal;">/ Maks {{ $kpi['max_risk_score'] }}/12</span></div>
        <div class="stat-kpi-sub">Mengacu ICAO Doc 9137 Part 3</div>
      </div>
    </td>
    <td class="stat-kpi-cell">
      <div class="stat-kpi-card" style="border-left-color: #0284c7;">
        <div class="stat-kpi-title">Konsumsi Amunisi Dispersal</div>
        <div class="stat-kpi-val">{{ $kpi['total_amunisi'] }} Butir</div>
        <div class="stat-kpi-sub">Petasan / Flare Gun / Dentuman</div>
      </div>
    </td>
    <td class="stat-kpi-cell stat-kpi-cell-last">
      <div class="stat-kpi-card" style="border-left-color: #003e56;">
        <div class="stat-kpi-title">Puncak Temporal &amp; Hotspot</div>
        <div class="stat-kpi-val">
          {{ $kpi['peak_day'] !== null ? 'Tgl '.$kpi['peak_day'].' ('.$kpi['peak_day_count'].' BA)' : 'Nihil' }}
        </div>
        <div class="stat-kpi-sub">Hotspot Utama: Grid {{ $kpi['hotspot_grid'] }}</div>
      </div>
    </td>
  </tr>
</table>

<!-- B. TREN TEMPORAL MINGGUAN (GRAFIK + TABEL) -->
<table style="width: 100%; margin-bottom: 5pt;" class="avoid-break">
  <tr>
    <td style="width: 47%; vertical-align: top; padding-right: 4pt;">
      <div class="chart-panel" style="margin-bottom: 0;">
        <div class="chart-panel-title">Grafik Tren Kejadian Mingguan ({{ $bundle['month_name'] }})</div>
        <table class="vbar-table">
          <tr>
            @foreach($stats['weekly_trend'] as $wk)
              <td class="vbar-cell" style="width: {{ round(100 / max(1, count($stats['weekly_trend'])), 2) }}%;">
                <div class="vbar-count">{{ $wk['laporan'] }} BA</div>
                @if($wk['bar_height_pt'] > 0)
                  <div class="vbar-fill" style="height: {{ $wk['bar_height_pt'] }}pt;">&nbsp;</div>
                @else
                  <div class="vbar-zero">&nbsp;</div>
                @endif
              </td>
            @endforeach
          </tr>
          <tr>
            @foreach($stats['weekly_trend'] as $wk)
              <td class="vbar-label-cell">
                {{ $wk['label'] }}<br>
                <span class="vbar-sub-label">Tgl {{ $wk['range'] }}</span>
              </td>
            @endforeach
          </tr>
        </table>
      </div>
    </td>
    <td style="width: 53%; vertical-align: top;">
      <div class="chart-panel" style="margin-bottom: 0;">
        <div class="chart-panel-title">Rincian Temporal Per Minggu</div>
        <table class="ba-matrix-table" style="margin-bottom: 0;">
          <thead>
            <tr>
              <th style="padding: 3pt 4pt; font-size: 7pt;">Periode</th>
              <th class="text-center" style="padding: 3pt 4pt; font-size: 7pt;">BA</th>
              <th class="text-center" style="padding: 3pt 4pt; font-size: 7pt;">Sah</th>
              <th class="text-center" style="padding: 3pt 4pt; font-size: 7pt;">Ekor</th>
              <th class="text-center" style="padding: 3pt 4pt; font-size: 7pt;">Kritis/Tinggi</th>
              <th class="text-center" style="padding: 3pt 4pt; font-size: 7pt;">Amunisi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($stats['weekly_trend'] as $idx => $wk)
              <tr class="{{ $idx % 2 === 1 ? 'alt-row' : '' }}">
                <td style="padding: 2.8pt 4pt; font-size: 7pt;"><strong>{{ $wk['label'] }}</strong> <span style="color: #64748b;">({{ $wk['range'] }})</span></td>
                <td class="text-center font-bold" style="padding: 2.8pt 4pt; font-size: 7pt;">{{ $wk['laporan'] }}</td>
                <td class="text-center" style="padding: 2.8pt 4pt; font-size: 7pt;">{{ $wk['tervalidasi'] }}</td>
                <td class="text-center font-bold" style="padding: 2.8pt 4pt; font-size: 7pt;">{{ $wk['ekor'] }}</td>
                <td class="text-center" style="padding: 2.8pt 4pt; font-size: 7pt; {{ $wk['kritis_tinggi'] > 0 ? 'color: #9f1239; font-weight: bold;' : '' }}">{{ $wk['kritis_tinggi'] }}</td>
                <td class="text-center" style="padding: 2.8pt 4pt; font-size: 7pt;">{{ $wk['amunisi'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </td>
  </tr>
</table>

<!-- C. GRAFIK TREN TEMPORAL HARIAN (1 BARIS LINI MASA TANGGAL 1 S.D. AKHIR BULAN) -->
<div class="chart-panel avoid-break">
  <div class="chart-panel-title">Grafik Lini Masa Kejadian Harian ({{ $bundle['date_range_label'] }})</div>
  <table class="daily-grid-table">
    <tr>
      @foreach($dailyTrend as $dayItem)
        <td class="daily-bar-cell" style="width: {{ $dayColWidth }}%;">
          @if($dayItem['laporan'] > 0)
            <div style="font-size: 5.5pt; font-weight: bold; color: {{ $dayItem['kritis_tinggi'] > 0 ? '#be123c' : '#005f80' }}; margin-bottom: 1pt;">
              {{ $dayItem['laporan'] }}
            </div>
            <div style="width: 72%; margin: 0 auto; height: {{ $dayItem['bar_height_pt'] }}pt; background-color: {{ $dayItem['kritis_tinggi'] > 0 ? '#e11d48' : '#00A9C1' }};">&nbsp;</div>
          @else
            <div style="width: 72%; margin: 0 auto; height: 1.5pt; background-color: #e2e8f0;">&nbsp;</div>
          @endif
        </td>
      @endforeach
    </tr>
    <tr>
      @foreach($dailyTrend as $dayItem)
        <td class="daily-day-cell" style="{{ $dayItem['laporan'] > 0 ? 'color: #003e56; background-color: #eef7fa;' : '' }}">
          {{ $dayItem['day'] }}
        </td>
      @endforeach
    </tr>
  </table>
</div>

<!-- D. JAM KRITIS, RISIKO ICAO & KOMPOSISI TAKSONOMI -->
<table style="width: 100%; margin-bottom: 5pt;" class="avoid-break">
  <tr>
    <td style="width: 34%; vertical-align: top; padding-right: 4pt;">
      <div class="chart-panel" style="margin-bottom: 0;">
        <div class="chart-panel-title">Distribusi Jam Kritis (WIB)</div>
        @foreach($stats['time_slots'] as $slot)
          <table class="hbar-row-table">
            <tr>
              <td style="font-size: 6.7pt; font-weight: bold; color: #1e293b;">{{ $slot['label'] }}</td>
              <td class="text-right" style="font-size: 6.6pt; color: #475569;">{{ $slot['laporan'] }} BA ({{ $slot['pct'] }}%)</td>
            </tr>
            <tr>
              <td colspan="2" style="padding-top: 1pt;">
                <table class="hbar-track">
                  <tr>
                    @if($slot['pct'] > 0)
                      <td class="hbar-fill" style="width: {{ max(4, $slot['pct']) }}%; background-color: {{ $slot['color'] }};">&nbsp;</td>
                    @endif
                    <td></td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        @endforeach
      </div>
    </td>

    <td style="width: 33%; vertical-align: top; padding-right: 4pt;">
      <div class="chart-panel" style="margin-bottom: 0;">
        <div class="chart-panel-title">Sebaran Risiko ICAO</div>
        @foreach($stats['risk_breakdown'] as $rb)
          <table class="hbar-row-table">
            <tr>
              <td style="font-size: 6.7pt; font-weight: bold; color: {{ $rb['style']['text'] }};">{{ $rb['label'] }}</td>
              <td class="text-right" style="font-size: 6.6pt; color: #475569;">{{ $rb['count'] }} BA ({{ $rb['pct'] }}%)</td>
            </tr>
            <tr>
              <td colspan="2" style="padding-top: 1pt;">
                <table class="hbar-track">
                  <tr>
                    @if($rb['pct'] > 0)
                      <td class="hbar-fill" style="width: {{ max(4, $rb['pct']) }}%; background-color: {{ $rb['style']['badge_bg'] }};">&nbsp;</td>
                    @endif
                    <td></td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        @endforeach
      </div>
    </td>

    <td style="width: 33%; vertical-align: top;">
      <div class="chart-panel" style="margin-bottom: 0;">
        <div class="chart-panel-title">Komposisi Taksonomi (Ekor)</div>
        @foreach($stats['taxonomy_breakdown'] as $tb)
          <table class="hbar-row-table">
            <tr>
              <td style="font-size: 6.7pt; font-weight: bold; color: #1e293b;">{{ $tb['label'] }}</td>
              <td class="text-right" style="font-size: 6.6pt; color: #475569;">{{ $tb['ekor'] }} ekor ({{ $tb['pct'] }}%)</td>
            </tr>
            <tr>
              <td colspan="2" style="padding-top: 1pt;">
                <table class="hbar-track">
                  <tr>
                    @if($tb['pct'] > 0)
                      <td class="hbar-fill" style="width: {{ max(4, $tb['pct']) }}%; background-color: {{ $tb['color'] }};">&nbsp;</td>
                    @endif
                    <td></td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        @endforeach
      </div>
    </td>
  </tr>
</table>

<!-- E. TOP SPESIES, TOP HOTSPOT GRID & KONTRIBUSI UNIT KERJA (SEJAJAR 3 KOLOM) -->
<table style="width: 100%;" class="avoid-break">
  <tr>
    <td style="width: 37%; vertical-align: top; padding-right: 4pt;">
      <div class="chart-panel" style="margin-bottom: 0;">
        <div class="chart-panel-title">Top 5 Spesies Dominan</div>
        <table class="ba-matrix-table" style="margin-bottom: 0;">
          <thead>
            <tr>
              <th style="padding: 2.5pt 4pt; font-size: 6.8pt;">Spesies</th>
              <th style="width: 18%; padding: 2.5pt 4pt; font-size: 6.8pt;" class="text-center">Kjd</th>
              <th style="width: 22%; padding: 2.5pt 4pt; font-size: 6.8pt;" class="text-center">Ekor</th>
            </tr>
          </thead>
          <tbody>
            @forelse($stats['top_species'] as $idx => $sp)
              <tr class="{{ $idx % 2 === 1 ? 'alt-row' : '' }}">
                <td style="padding: 2.5pt 4pt; font-size: 6.9pt;">
                  <strong>{{ $sp['nama_satwa'] }}</strong>
                  @if($sp['nama_ilmiah'] !== '-')
                    <br><span class="italic" style="color: #64748b; font-size: 6.2pt;">{{ $sp['nama_ilmiah'] }}</span>
                  @endif
                </td>
                <td class="text-center" style="padding: 2.5pt 4pt; font-size: 6.9pt;">{{ $sp['kejadian'] }}x</td>
                <td class="text-center font-bold" style="padding: 2.5pt 4pt; font-size: 6.9pt;">{{ $sp['ekor'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center" style="color: #64748b; padding: 5pt; font-size: 7pt;">Belum ada data spesies.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </td>

    <td style="width: 32%; vertical-align: top; padding-right: 4pt;">
      <div class="chart-panel" style="margin-bottom: 0;">
        <div class="chart-panel-title">Top 5 Hotspot Grid</div>
        <table class="ba-matrix-table" style="margin-bottom: 0;">
          <thead>
            <tr>
              <th style="width: 22%; padding: 2.5pt 4pt; font-size: 6.8pt;" class="text-center">Grid</th>
              <th style="padding: 2.5pt 4pt; font-size: 6.8pt;">Area Sisi Udara</th>
              <th style="width: 24%; padding: 2.5pt 4pt; font-size: 6.8pt;" class="text-center">Ekor</th>
            </tr>
          </thead>
          <tbody>
            @forelse($stats['top_grids'] as $idx => $gd)
              <tr class="{{ $idx % 2 === 1 ? 'alt-row' : '' }}">
                <td class="text-center mono font-bold" style="padding: 2.5pt 4pt; font-size: 6.9pt;">{{ $gd['grid'] }}</td>
                <td style="padding: 2.5pt 4pt; font-size: 6.8pt;">{{ $gd['area'] }}</td>
                <td class="text-center font-bold" style="padding: 2.5pt 4pt; font-size: 6.9pt;">{{ $gd['ekor'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center" style="color: #64748b; padding: 5pt; font-size: 7pt;">Belum ada data grid.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </td>

    <td style="width: 31%; vertical-align: top;">
      <div class="chart-panel" style="margin-bottom: 0;">
        <div class="chart-panel-title">Kontribusi Unit Kerja</div>
        <table class="ba-matrix-table" style="margin-bottom: 0;">
          <thead>
            <tr>
              <th style="padding: 2.5pt 4pt; font-size: 6.8pt;">Unit</th>
              <th style="width: 18%; padding: 2.5pt 4pt; font-size: 6.8pt;" class="text-center">BA</th>
              <th style="width: 20%; padding: 2.5pt 4pt; font-size: 6.8pt;" class="text-center">Ekor</th>
              <th style="width: 24%; padding: 2.5pt 4pt; font-size: 6.8pt;" class="text-center">Sah</th>
            </tr>
          </thead>
          <tbody>
            @forelse($stats['unit_breakdown'] as $idx => $ut)
              <tr class="{{ $idx % 2 === 1 ? 'alt-row' : '' }}">
                <td class="font-bold" style="padding: 2.5pt 4pt; font-size: 6.8pt;">{{ $ut['unit'] }}</td>
                <td class="text-center" style="padding: 2.5pt 4pt; font-size: 6.9pt;">{{ $ut['laporan'] }}</td>
                <td class="text-center" style="padding: 2.5pt 4pt; font-size: 6.9pt;">{{ $ut['ekor'] }}</td>
                <td class="text-center font-bold" style="padding: 2.5pt 4pt; font-size: 6.9pt;">{{ $ut['pct_validasi'] }}%</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center" style="color: #64748b; padding: 5pt; font-size: 7pt;">Belum ada data unit.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </td>
  </tr>
</table>
