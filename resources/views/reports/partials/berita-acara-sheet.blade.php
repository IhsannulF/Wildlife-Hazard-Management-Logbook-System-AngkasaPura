@php
  /** @var array<string, mixed> $ba */
  $lap = $ba['laporan'];
  $risk = $ba['risk'];
  $pelaporSig = $ba['signatures']['pelapor'];
  $validatorSig = $ba['signatures']['validator'];
@endphp

<div class="ba-sheet">
  <!-- 1. HEADER RESMI INJOURNEY AIRPORTS -->
  <table class="ba-header-table">
    <tr>
      <td colspan="3" class="ba-header-accent">&nbsp;</td>
    </tr>
    <tr>
      <td class="ba-logo-cell">
        @if(!empty($ba['logo_data_uri']))
          <img src="{{ $ba['logo_data_uri'] }}" alt="InJourney Airports" class="ba-logo-img">
        @else
          <strong style="font-size: 9pt; color: #003e56;">INJOURNEY AIRPORTS</strong>
        @endif
      </td>
      <td class="ba-title-cell">
        <div class="ba-instansi">PT Angkasa Pura Indonesia &bull; InJourney Airports</div>
        <div class="ba-doc-title">BERITA ACARA PEMANTAUAN &amp; PENGENDALIAN SATWA LIAR</div>
        <div class="ba-doc-sub">
          Bandar Udara Internasional Juanda &bull; Wildlife Hazard Management System (WHMS)<br>
          Rujukan: ICAO Annex 14 Bab 9 &bull; ICAO Doc 9137 Part 3 &bull; Standar DKPPU Ditjen Hubud
        </div>
      </td>
      <td class="ba-reg-cell">
        <div class="ba-reg-label">Nomor Registrasi Resmi</div>
        <div class="ba-reg-no">{{ $ba['no_laporan'] }}</div>
        <span class="ba-status-pill {{ $ba['is_validated'] ? 'ba-status-verified' : 'ba-status-pending' }}">
          {{ $ba['status_short'] }}
        </span>
      </td>
    </tr>
  </table>

  <table class="ba-subbar">
    <tr>
      <td>
        <strong>Status Dokumen:</strong> {{ $ba['status_label'] }}
      </td>
      <td class="text-right">
        <strong>Waktu Pelaporan:</strong> {{ $ba['tanggal_formatted'] }} ({{ $ba['jam_formatted'] }})
      </td>
    </tr>
  </table>

  <!-- 2. IDENTITAS KEJADIAN & MATRIKS PENILAIAN RISIKO ICAO -->
  <table class="ba-summary-layout avoid-break">
    <tr>
      <td class="ba-summary-left">
        <table class="ba-meta-table">
          <tr>
            <td class="ba-meta-label">Nomor Berita Acara</td>
            <td class="ba-meta-val mono font-bold">{{ $ba['no_laporan'] }}</td>
          </tr>
          <tr>
            <td class="ba-meta-label">Petugas Pelapor</td>
            <td class="ba-meta-val font-bold">{{ $pelaporSig['nama'] }}</td>
          </tr>
          <tr>
            <td class="ba-meta-label">Unit Kerja / Jabatan</td>
            <td class="ba-meta-val">{{ $lap->unit_kerja ?: '-' }} ({{ $pelaporSig['jabatan'] }})</td>
          </tr>
          <tr>
            <td class="ba-meta-label">Area Inspeksi</td>
            <td class="ba-meta-val font-bold">{{ $lap->area_inspeksi ?: '-' }}</td>
          </tr>
          <tr>
            <td class="ba-meta-label">Grid &amp; Koordinat GPS</td>
            <td class="ba-meta-val">
              <span class="mono font-bold">{{ strtoupper((string) ($lap->grid_lokasi ?: '-')) }}</span>
              @if(!empty($ba['gps_coordinates']))
                &nbsp;|&nbsp; <span class="mono" style="font-size: 7.3pt;">{{ $ba['gps_coordinates'] }}</span>
              @endif
            </td>
          </tr>
          <tr>
            <td class="ba-meta-label">Cuaca &amp; Sisi Udara</td>
            <td class="ba-meta-val">
              {{ $lap->kondisi_cuaca ?: '-' }}
              @if(!empty($lap->kondisi_apron))
                &nbsp;&bull;&nbsp; Kondisi: {{ $lap->kondisi_apron }}
              @endif
            </td>
          </tr>
        </table>
      </td>

      <td class="ba-summary-right">
        <table class="ba-risk-card" style="border-color: {{ $risk['border'] }}; background-color: {{ $risk['bg'] }};">
          <tr>
            <td colspan="2" class="ba-risk-header">Penilaian Risiko ICAO Doc 9137</td>
          </tr>
          <tr class="ba-risk-score-row">
            <td style="width: 65%;">
              <span class="ba-risk-badge" style="background-color: {{ $risk['badge_bg'] }};">
                {{ strtoupper($risk['level']) }}
              </span>
              <div style="font-size: 7pt; font-weight: bold; color: {{ $risk['text'] }}; margin-top: 3pt;">
                {{ $risk['label'] }}
              </div>
            </td>
            <td class="text-right" style="width: 35%; color: {{ $risk['text'] }};">
              <div class="ba-risk-number">{{ $risk['score'] }}<span style="font-size: 8.5pt;">/12</span></div>
              <div style="font-size: 6.5pt; color: #475569;">Skor ICAO</div>
            </td>
          </tr>
          <tr>
            <td colspan="2" style="padding: 0; background-color: #ffffff;">
              <table class="ba-risk-factor-table">
                <tr>
                  <td>Probabilitas (P) &times; Keparahan (S)</td>
                  <td class="text-right font-bold">{{ $risk['probability'] }} &times; {{ $risk['severity'] }}</td>
                </tr>
                <tr>
                  <td>Bobot Zona / Spesies</td>
                  <td class="text-right font-bold">Z:{{ $risk['zone_score'] }} / Sp:{{ $risk['species_score'] }}</td>
                </tr>
                <tr>
                  <td>Total Satwa Terpantau</td>
                  <td class="text-right font-bold">{{ $risk['total_satwa'] }} ekor</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- 3. MATRIKS TEMUAN SATWA LIAR -->
  <div class="ba-section-title">I. Matriks Temuan Satwa Liar</div>
  <table class="ba-matrix-table">
    <thead>
      <tr>
        <th style="width: 7%;" class="text-center">No</th>
        <th style="width: 31%;">Jenis Spesies Satwa</th>
        <th style="width: 27%;">Nama Ilmiah (Takson)</th>
        <th style="width: 12%;" class="text-center">Jumlah</th>
        <th style="width: 11%;" class="text-center">Grid</th>
        <th style="width: 12%;" class="text-center">Foto Bukti</th>
      </tr>
    </thead>
    <tbody>
      @forelse($ba['satwa_matrix'] as $idx => $row)
        <tr class="{{ $idx % 2 === 1 ? 'alt-row' : '' }}">
          <td class="text-center">{{ $row['no'] }}</td>
          <td class="font-bold">{{ $row['nama_satwa'] }}</td>
          <td class="italic" style="color: #475569;">{{ $row['nama_ilmiah'] }}</td>
          <td class="text-center font-bold">{{ $row['jumlah'] }} ekor</td>
          <td class="text-center mono font-bold">{{ $row['grid'] }}</td>
          <td class="text-center">{{ $row['has_foto'] ? 'Terlampir' : '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center" style="color: #64748b; padding: 7pt;">
            Temuan satwa tidak tercatat dalam daftar spesies katalog (periksa lampiran dokumentasi foto).
          </td>
        </tr>
      @endforelse
      @if($ba['total_spesies'] > 0)
        <tr class="ba-matrix-total">
          <td colspan="3" class="text-right">Total Individu / Spesies Terpantau:</td>
          <td class="text-center">{{ $ba['total_ekor'] }} ekor</td>
          <td colspan="2" class="text-center">{{ $ba['total_spesies'] }} jenis temuan</td>
        </tr>
      @endif
    </tbody>
  </table>

  <!-- 4. PENGAMATAN LAPANGAN & MITIGASI DISPERSAL -->
  <div class="avoid-break">
    <div class="ba-section-title">II. Pengamatan Lapangan &amp; Tindak Lanjut Mitigasi</div>
    <table class="ba-obs-table">
      <tr>
        <td>
          <div class="ba-obs-label">Ciri-Ciri &amp; Ukuran Satwa</div>
          <div class="ba-obs-text">{{ $lap->ciri_ukuran ?: '-' }}</div>
        </td>
        <td>
          <div class="ba-obs-label">Kondisi Area / Sisi Udara</div>
          <div class="ba-obs-text">{{ $lap->kondisi_apron ?: '-' }}</div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="ba-obs-label">Aktivitas &amp; Perilaku Satwa</div>
          <div class="ba-obs-text">{{ $lap->aktivitas_satwa ?: '-' }}</div>
        </td>
        <td>
          <div class="ba-obs-label">Tindak Lanjut, Metode Pengusiran &amp; Amunisi</div>
          <div class="ba-obs-text">
            <strong>Tindakan:</strong> {{ $lap->tindak_lanjut ?: '-' }}<br>
            <strong>Metode / Alat:</strong> {{ $lap->detail_pengusiran ?: '-' }}<br>
            <strong>Konsumsi Amunisi:</strong> {{ $ba['amunisi_terpakai'] > 0 ? $ba['amunisi_terpakai'].' butir/dentuman' : '-' }}
          </div>
        </td>
      </tr>
    </table>

    @if(!empty($ba['tanggapan_history']))
      <table class="ba-notes-table">
        <thead>
          <tr>
            <th style="width: 18%;">Kategori</th>
            <th style="width: 24%;">Pejabat / Petugas</th>
            <th style="width: 39%;">Catatan &amp; Instruksi Safety Unit</th>
            <th style="width: 19%;" class="text-right">Waktu (WIB)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($ba['tanggapan_history'] as $note)
            <tr>
              <td class="font-bold">{{ $note['kategori'] }}</td>
              <td>{{ $note['author'] }}<br><span style="color: #64748b; font-size: 6.7pt;">{{ $note['role'] }}</span></td>
              <td>{{ $note['isi'] }}</td>
              <td class="text-right" style="color: #475569;">{{ $note['timestamp_wib'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <!-- 5. LAMPIRAN FOTO DOKUMENTASI BER-WATERMARK -->
  <div class="ba-section-title">III. Lampiran Foto Dokumentasi Ber-Watermark</div>
  @if(!empty($ba['watermarked_photos']))
    <table class="ba-photo-grid">
      @foreach(array_chunk($ba['watermarked_photos'], 2) as $photoRow)
        <tr class="avoid-break">
          @foreach($photoRow as $photo)
            <td class="ba-photo-cell" style="{{ count($photoRow) === 1 ? 'width: 100%;' : '' }}">
              <div class="ba-photo-card">
                <img src="{{ $photo['data_uri'] }}" alt="{{ $photo['caption'] }}" class="ba-photo-img">
                <div class="ba-photo-caption">{{ $photo['caption'] }}</div>
                <div class="ba-photo-sub">{{ $photo['subcaption'] }}</div>
              </div>
            </td>
          @endforeach
        </tr>
      @endforeach
    </table>
  @else
    <div class="ba-empty-box">
      Tidak terdapat lampiran berkas foto dokumentasi lapangan pada berita acara ini.
    </div>
  @endif

  <!-- 6. LEMBAR PENGESAHAN & TANDA TANGAN GANDA -->
  <div class="avoid-break">
    <div class="ba-attestation">
      Demikian Berita Acara pemantauan dan pengendalian bahaya satwa liar ini dibuat secara sah melalui sistem WHMS InJourney Airports sesuai prosedur keselamatan operasi penerbangan untuk digunakan sebagai bukti inspeksi dan audit kelaikudaraan.
    </div>

    <table class="ba-sig-table">
      <tr>
        <!-- Kolom Kiri: Pelapor -->
        <td class="ba-sig-cell">
          <div class="ba-sig-role">Petugas Pelapor</div>
          <div class="ba-sig-subtitle">Pelaksana Inspeksi / Pengendalian Lapangan</div>
          <div class="ba-sig-img-box">
            @if(!empty($pelaporSig['signature_uri']))
              <img src="{{ $pelaporSig['signature_uri'] }}" alt="Tanda Tangan Pelapor" class="ba-sig-img">
            @else
              <span class="ba-pending-seal">Spesimen tanda tangan pelapor belum tersedia</span>
            @endif
          </div>
          <div class="ba-sig-name">{{ $pelaporSig['nama'] }}</div>
          <div class="ba-sig-unit">{{ $pelaporSig['jabatan'] }}</div>
          <div class="ba-sig-time">Dikirim: {{ $pelaporSig['timestamp_wib'] }}</div>
        </td>

        <!-- Kolom Kanan: Safety Manager / Validator -->
        <td class="ba-sig-cell">
          <div class="ba-sig-role">Mengetahui &amp; Mengesahkan</div>
          <div class="ba-sig-subtitle">Safety Manager / Pejabat Validator WHMS</div>
          <div class="ba-sig-img-box">
            @if(!empty($validatorSig['signature_uri']))
              <img src="{{ $validatorSig['signature_uri'] }}" alt="Tanda Tangan Safety Manager" class="ba-sig-img">
            @elseif($validatorSig['has_digital_seal'])
              <span class="ba-digital-seal">
                TERVALIDASI DIGITAL SECARA SISTEM<br>
                {{ $validatorSig['nama'] }}
              </span>
            @else
              <span class="ba-pending-seal">MENUNGGU VALIDASI SAFETY MANAGER</span>
            @endif
          </div>
          <div class="ba-sig-name">
            {{ $validatorSig['is_validated'] ? $validatorSig['nama'] : '( .................................................... )' }}
          </div>
          <div class="ba-sig-unit">{{ $validatorSig['jabatan'] }}</div>
          <div class="ba-sig-time">
            @if($validatorSig['is_validated'] && !empty($validatorSig['timestamp_wib']))
              Disahkan: {{ $validatorSig['timestamp_wib'] }}
            @else
              Status: Belum Divalidasi
            @endif
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>

