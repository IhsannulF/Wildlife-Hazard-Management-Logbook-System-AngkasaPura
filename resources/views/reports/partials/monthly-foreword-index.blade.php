@php
  /** @var array<string, mixed> $bundle */
  $foreword = $bundle['foreword'];
  $issuer = $bundle['issuer'];
@endphp

<table class="bundle-part-banner">
  <tr>
    <td>
      <div class="bundle-part-eyebrow">Bagian II &bull; Pengantar &amp; Indeks Dokumen</div>
      <div class="bundle-part-title">KATA PENGANTAR &amp; DAFTAR ISI BERITA ACARA</div>
    </td>
    <td class="bundle-part-code">
      {{ $bundle['bundle_code'] }}<br>{{ $bundle['periode_label'] }}
    </td>
  </tr>
</table>

<div class="bundle-foreword-box avoid-break">
  <div class="bundle-foreword-heading">{{ $foreword['headline'] }}</div>
  @foreach($foreword['paragraphs'] as $paragraph)
    <p class="bundle-foreword-p">{{ $paragraph }}</p>
  @endforeach
  <div class="bundle-rec-box">
    <strong>Arahan Tindak Lanjut Keselamatan:</strong> {{ $foreword['recommendation'] }}
  </div>
</div>

<!-- Pengesahan Penyusun Bundel -->
<table class="bundle-endorse-table avoid-break">
  <tr>
    <td style="width: 64%;">
      <div style="font-size: 7pt; font-weight: bold; color: #005f80; text-transform: uppercase;">Pengesahan Arsip Logbook Bulanan</div>
      <div style="font-size: 8.2pt; font-weight: bold; color: #0f172a; margin-top: 2pt;">{{ $issuer['nama'] }}</div>
      <div style="font-size: 7.2pt; color: #475569;">{{ $issuer['jabatan'] }} &bull; WHMS InJourney Airports</div>
      <div style="font-size: 6.8pt; color: #64748b; margin-top: 2pt;">Diverifikasi sistem pada {{ $bundle['printed_at_wib'] }}</div>
    </td>
    <td style="width: 36%; text-align: center; border-left: 1pt solid #cbd5e1;">
      @if(!empty($issuer['signature_uri']))
        <img src="{{ $issuer['signature_uri'] }}" alt="Tanda Tangan Penyusun Arsip" style="max-width: 44mm; max-height: 14mm;">
      @else
        <span class="ba-digital-seal">ARSIP DIGITAL RESMI WHMS<br>{{ $bundle['periode_label'] }}</span>
      @endif
    </td>
  </tr>
</table>

<!-- Indeks Kronologis Berita Acara -->
<div class="ba-section-title">Daftar Isi Kronologis Berita Acara ({{ count($bundle['index_rows']) }} Dokumen)</div>
<table class="bundle-index-table">
  <thead>
    <tr>
      <th style="width: 5%;" class="text-center">No</th>
      <th style="width: 20%;">No. Registrasi BA</th>
      <th style="width: 12%;">Waktu (WIB)</th>
      <th style="width: 17%;">Pelapor &amp; Unit</th>
      <th style="width: 15%;">Area / Grid</th>
      <th style="width: 16%;">Temuan Satwa</th>
      <th style="width: 7%;" class="text-center">ICAO</th>
      <th style="width: 8%;" class="text-center">Status</th>
    </tr>
  </thead>
  <tbody>
    @forelse($bundle['index_rows'] as $idx => $row)
      <tr class="{{ $idx % 2 === 1 ? 'alt-row' : '' }}">
        <td class="text-center">{{ $row['no'] }}</td>
        <td class="mono font-bold" style="color: #003e56;">{{ $row['no_laporan'] }}</td>
        <td>
          <strong>{{ $row['tanggal_short'] }}</strong><br>
          <span style="color: #64748b; font-size: 6.5pt;">{{ $row['jam'] }}</span>
        </td>
        <td>
          <strong>{{ $row['petugas'] }}</strong><br>
          <span style="color: #64748b; font-size: 6.5pt;">{{ $row['unit'] }}</span>
        </td>
        <td>
          {{ $row['area'] }}<br>
          <span class="mono font-bold" style="font-size: 6.6pt; color: #007fa3;">Grid {{ $row['grid'] }}</span>
        </td>
        <td>
          {{ $row['spesies_ringkas'] }}<br>
          <span style="color: #475569; font-size: 6.5pt;">{{ $row['ekor'] }} ekor &bull; {{ $row['amunisi'] > 0 ? $row['amunisi'].' amunisi' : 'non-amunisi' }}</span>
        </td>
        <td class="text-center" style="background-color: {{ $row['risk_style']['bg'] }}; color: {{ $row['risk_style']['text'] }}; font-weight: bold;">
          {{ $row['risk_score'] }}/12<br>
          <span style="font-size: 6pt;">{{ $row['risk_level'] }}</span>
        </td>
        <td class="text-center">
          <span class="ba-status-pill {{ $row['is_validated'] ? 'ba-status-verified' : 'ba-status-pending' }}" style="font-size: 6pt; padding: 1.5pt 4pt;">
            {{ $row['is_validated'] ? 'SAH' : 'PROSES' }}
          </span>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="8" class="text-center" style="padding: 10pt; color: #64748b;">
          Tidak ada dokumen Berita Acara yang diterbitkan untuk periode {{ $bundle['periode_label'] }} pada cakupan filter ini.
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

