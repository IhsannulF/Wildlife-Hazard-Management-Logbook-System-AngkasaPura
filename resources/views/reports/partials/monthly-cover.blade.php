@php
  /** @var array<string, mixed> $bundle */
  $kpi = $bundle['stats']['kpi'];
@endphp

<div class="bundle-cover">
  <!-- Banner Kop Utama -->
  <table class="bundle-cover-banner">
    <tr>
      <td colspan="2" class="bundle-cover-accent">&nbsp;</td>
    </tr>
    <tr>
      <td class="bundle-cover-logo-cell">
        @if(!empty($bundle['logo_data_uri']))
          <img src="{{ $bundle['logo_data_uri'] }}" alt="InJourney Airports" class="bundle-cover-logo-img">
        @else
          <strong style="font-size: 10pt; color: #003e56;">INJOURNEY AIRPORTS</strong>
        @endif
      </td>
      <td class="bundle-cover-inst-cell">
        <div class="bundle-cover-inst-eyebrow">PT Angkasa Pura Indonesia &bull; InJourney Airports</div>
        <div class="bundle-cover-inst-title">BANDAR UDARA INTERNASIONAL JUANDA — SURABAYA</div>
        <div class="bundle-cover-inst-sub">
          Wildlife Hazard Management System (WHMS) &bull; Airside Safety &amp; Risk Operation
        </div>
      </td>
    </tr>
  </table>

  <!-- Kartu Judul Resmi -->
  <div class="bundle-cover-hero">
    <div class="bundle-cover-code">ARSIP RESMI: {{ $bundle['bundle_code'] }}</div>
    <div class="bundle-cover-main-title">
      BUKU LOG BULANAN &amp; KOMPILASI BERITA ACARA PENGENDALIAN SATWA LIAR
    </div>
    <div class="bundle-cover-subtitle">
      Kompilasi Resmi Kejadian, Penilaian Risiko ICAO Doc 9137, Dokumentasi Lapangan, dan Pengesahan Safety Unit
    </div>

    <table class="bundle-cover-period-box">
      <tr>
        <td style="width: 55%; vertical-align: top;">
          <div class="bundle-cover-period-label">Periode Pelaporan</div>
          <div class="bundle-cover-period-value">{{ $bundle['periode_label'] }}</div>
          <div style="font-size: 7.8pt; color: #475569; margin-top: 2pt;">{{ $bundle['date_range_label'] }}</div>
        </td>
        <td style="width: 45%; vertical-align: top; text-align: right;">
          <div class="bundle-cover-period-label">Rujukan Regulasi</div>
          <div style="font-size: 8.2pt; font-weight: bold; color: #0f172a; margin-top: 3pt;">
            ICAO Annex 14 Bab 9<br>
            ICAO Doc 9137 Part 3<br>
            DKPPU Ditjen Hubud
          </div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Ringkasan Eksekutif Cover -->
  <table class="bundle-cover-kpi-table">
    <tr>
      <td class="bundle-cover-kpi-cell">
        <div class="bundle-cover-kpi-card">
          <div class="bundle-cover-kpi-num">{{ $kpi['total_laporan'] }}</div>
          <div class="bundle-cover-kpi-label">Berita Acara</div>
          <div class="bundle-cover-kpi-sub">Total Dokumen BA</div>
        </div>
      </td>
      <td class="bundle-cover-kpi-cell">
        <div class="bundle-cover-kpi-card" style="border-top-color: #16a34a;">
          <div class="bundle-cover-kpi-num">{{ number_format((float) $kpi['persen_tervalidasi'], 1) }}%</div>
          <div class="bundle-cover-kpi-label">Tervalidasi</div>
          <div class="bundle-cover-kpi-sub">{{ $kpi['total_tervalidasi'] }} Disahkan SM</div>
        </div>
      </td>
      <td class="bundle-cover-kpi-cell">
        <div class="bundle-cover-kpi-card" style="border-top-color: #d97706;">
          <div class="bundle-cover-kpi-num">{{ $kpi['total_ekor'] }}</div>
          <div class="bundle-cover-kpi-label">Total Satwa</div>
          <div class="bundle-cover-kpi-sub">Individu Terpantau</div>
        </div>
      </td>
      <td class="bundle-cover-kpi-cell">
        <div class="bundle-cover-kpi-card" style="border-top-color: #003e56;">
          <div class="bundle-cover-kpi-num">{{ $kpi['total_amunisi'] }}</div>
          <div class="bundle-cover-kpi-label">Amunisi</div>
          <div class="bundle-cover-kpi-sub">Butir / Dentuman</div>
        </div>
      </td>
    </tr>
  </table>

  <!-- Metadata Arsip & Filter Cakupan -->
  <table class="bundle-cover-info-table">
    <tr>
      <td class="bundle-cover-info-label">Kode Registrasi Bundel</td>
      <td class="mono font-bold">{{ $bundle['bundle_code'] }}</td>
    </tr>
    <tr>
      <td class="bundle-cover-info-label">Cakupan Unit Kerja</td>
      <td>{{ $bundle['filter_summary']['unit_label'] }}</td>
    </tr>
    <tr>
      <td class="bundle-cover-info-label">Filter Status Penanganan</td>
      <td>{{ $bundle['filter_summary']['status_label'] }}</td>
    </tr>
    <tr>
      <td class="bundle-cover-info-label">Hotspot &amp; Spesies Dominan</td>
      <td>Grid <strong>{{ $kpi['hotspot_grid'] }}</strong> ({{ $kpi['hotspot_area'] }}) &bull; {{ $kpi['dominant_species'] }}</td>
    </tr>
    <tr>
      <td class="bundle-cover-info-label">Diterbitkan Oleh</td>
      <td><strong>{{ $bundle['issuer']['nama'] }}</strong> ({{ $bundle['issuer']['jabatan'] }})</td>
    </tr>
    <tr>
      <td class="bundle-cover-info-label">Stempel Waktu Penerbitan</td>
      <td>{{ $bundle['printed_at_wib'] }}</td>
    </tr>
  </table>

  <!-- Struktur Dokumen Bundel -->
  <div class="bundle-cover-structure">
    <div class="bundle-cover-structure-title">Sistematika Isi Buku Log Bulanan</div>
    <ol class="bundle-cover-structure-list">
      <li><strong>Bagian I — Halaman Judul Resmi:</strong> Identitas arsip, periode pelaporan, dan ringkasan kepatuhan regulasi.</li>
      <li><strong>Bagian II — Kata Pengantar &amp; Indeks Kronologis:</strong> Ringkasan eksekutif Safety Unit serta daftar isi seluruh lembar Berita Acara.</li>
      <li><strong>Bagian III — Rekapitulasi Statistik &amp; Grafik Temporal:</strong> Tren mingguan/harian, jam kritis WIB, sebaran risiko ICAO, taksonomi, dan hotspot grid.</li>
      <li><strong>Bagian IV — Kompilasi Lembar Berita Acara Resmi:</strong> Lampiran utuh seluruh Berita Acara bulan {{ $bundle['periode_label'] }} secara kronologis dengan penomoran halaman berkelanjutan.</li>
    </ol>
  </div>
</div>

