<!-- MODAL: PRATINJAU DETAIL LAPORAN 2-HALAMAN & DIGITAL SIGNATURE -->
<div class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" id="detailModal">
  <div class="bg-card-bg w-full max-w-5xl rounded-2xl border border-border-subtle shadow-2xl overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-200">
    
    <!-- Modal Header -->
    <div class="px-6 py-4 bg-surface-container-lowest border-b border-border-subtle flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-surface-container-low flex items-center justify-center text-injourney-teal">
          <span class="material-symbols-outlined text-xl">assignment</span>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface" id="modalReportId">WHM-2026-0001</h2>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200" id="modalStatusBadge">
              <span class="w-2 h-2 rounded-full bg-alert-amber"></span> Belum Ditangani
            </span>
            <span class="hidden inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold" id="modalRiskScoreBadge"></span>
          </div>
          <p class="font-body-sm text-body-sm text-secondary">Berita Acara Temuan dan Penanganan Bahaya Satwa Liar Sisi Udara (ICAO Doc 9137 Part 3)</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <!-- Page Switcher Tabs -->
        <div class="flex items-center p-1 bg-surface-container-low rounded-lg border border-border-subtle">
          <button class="px-3 py-1 rounded text-xs font-label-md font-bold bg-white text-injourney-teal shadow-xs cursor-pointer" id="tabBtnPage1" onclick="switchModalPage(1)" type="button">
            Hal 1: Konteks &amp; Lokasi
          </button>
          <button class="px-3 py-1 rounded text-xs font-label-md text-secondary hover:text-on-surface cursor-pointer" id="tabBtnPage2" onclick="switchModalPage(2)" type="button">
            Hal 2: Dokumentasi &amp; TTD
          </button>
          <button class="px-3 py-1 rounded text-xs font-label-md text-secondary hover:text-on-surface cursor-pointer flex items-center gap-1.5" id="tabBtnPage3" onclick="switchModalPage(3)" type="button">
            <span>Hal 3: Tanggapan &amp; Instruksi</span>
            <span id="tabTanggapanBadge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-injourney-teal text-white">0</span>
          </button>
        </div>
        <button class="p-2 text-secondary hover:text-on-surface rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer" onclick="closeReportModal()" type="button">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
    </div>

    <!-- Modal Body (Two-Page View) -->
    <div class="p-6 overflow-y-auto flex-1 space-y-6">
      
      <!-- PAGE 1: CONTEXT, LOCATION & RUNWAY GRID SCHEMATIC -->
      <div class="space-y-6" id="modalPage1">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          
          <!-- Metadata Officer -->
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-3">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Identitas Laporan</span>
            <div>
              <p class="text-xs text-secondary">Petugas Pelapor:</p>
              <p class="font-semibold text-on-surface text-sm" id="modalOfficer">-</p>
            </div>
            <div>
              <p class="text-xs text-secondary">Unit Kerja:</p>
              <p class="font-semibold text-on-surface text-sm" id="modalUnitKerja">AMC Airside Operations</p>
            </div>
            <div>
              <p class="text-xs text-secondary">Tanggal &amp; Jam Temuan:</p>
              <p class="font-semibold text-on-surface text-sm" id="modalTimestamp">-</p>
            </div>
            <div>
              <p class="text-xs text-secondary">Kondisi Cuaca &amp; Angin:</p>
              <p class="font-semibold text-on-surface text-sm" id="modalWeather">Berawan 29°C / Angin 8 kts WSW</p>
            </div>
          </div>

          <!-- Wildlife Info -->
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-3">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Karakteristik Satwa</span>
            <div>
              <p class="text-xs text-secondary">Nama Spesies:</p>
              <p class="font-semibold text-on-surface text-sm" id="modalSpecies">-</p>
            </div>
            <div>
              <p class="text-xs text-secondary">Kuantitas &amp; Estimasi Ukuran:</p>
              <p class="font-semibold text-on-surface text-sm" id="modalQuantity">1 Ekor</p>
            </div>
            <div>
              <p class="text-xs text-secondary">Tingkat Risiko Ancaman:</p>
              <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded" id="modalRiskCategory">
                <span class="material-symbols-outlined text-xs">dangerous</span> Kategori A - Runway Hazard
              </span>
            </div>
          </div>

          <!-- Airside Location -->
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-3">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Koordinat &amp; Sisi Udara</span>
            <div>
              <p class="text-xs text-secondary">Zona / Sub-Area:</p>
              <p class="font-semibold text-on-surface text-sm" id="modalLocation">-</p>
            </div>
            <div>
              <p class="text-xs text-secondary">Target Grid Sisi Udara:</p>
              <p class="font-code-coordinate text-code-coordinate text-xs text-injourney-teal font-bold" id="modalGridText">GRID K-10</p>
            </div>
            <div>
              <p class="text-xs text-secondary">Akses Terdekat:</p>
              <p class="text-xs text-on-surface font-medium" id="modalAccess">Service Road Runway Gate 4</p>
            </div>
          </div>

        </div>

        <!-- Full Detailed Observation & Field Findings Note -->
        <div class="space-y-3">
          <!-- Deskripsi & Catatan Pengamatan Lengkap -->
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle shadow-xs space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-injourney-teal">notes</span> Deskripsi &amp; Catatan Pengamatan Lengkap
              </span>
              <span class="text-[11px] text-secondary font-medium">Catatan Logbook Sisi Udara</span>
            </div>
            <p class="text-xs text-on-surface leading-relaxed font-medium bg-canvas-light p-3 rounded-lg border border-border-subtle" id="modalCiriUkuran">
              -
            </p>
          </div>

          <!-- 3-Column Mini Cards: Aktivitas, Kondisi Apron, Tindak Lanjut -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-1">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-alert-amber">directions_run</span> Aktivitas &amp; Perilaku Satwa
              </span>
              <p class="text-xs text-on-surface font-medium" id="modalAktivitas">-</p>
            </div>
            <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-1">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-injourney-teal">terrain</span> Kondisi Apron / Area
              </span>
              <p class="text-xs text-on-surface font-medium" id="modalKondisiApron">-</p>
            </div>
            <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-1">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-resolved-emerald">shield</span> Tindak Lanjut &amp; Mitigasi
              </span>
              <p class="text-xs text-on-surface font-medium" id="modalTindakLanjut">-</p>
            </div>
          </div>
        </div>

        <!-- Runway Spatial Mini Schematic -->
        <div class="p-4 rounded-xl bg-slate-900 text-white space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-injourney-teal text-sm">grid_view</span>
              <span class="font-label-md text-label-md uppercase tracking-wider font-semibold text-slate-300">Sketsa Gridmap Runway 07L/25R</span>
            </div>
            <span class="text-xs text-slate-400">Target Sel Aktif: <strong class="text-warning-rose font-code-coordinate" id="schematicGridTarget">K-10 (Bahu Landasan)</strong></span>
          </div>

          <!-- Mini Grid Visual Representation (Interactive Cells K-01 to K-12) -->
          <div class="grid grid-cols-12 gap-1 text-center font-code-coordinate text-[11px] pt-1" id="miniGridContainer">
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-01">K-01</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-02">K-02</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-03">K-03</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-04">K-04</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-05">K-05</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-06">K-06</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-07">K-07</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-08">K-08</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-09">K-09</div>
            <div class="py-2 bg-warning-rose text-white rounded font-bold shadow-lg ring-2 ring-white/50 animate-pulse grid-cell" data-grid="K-10">K-10 ★</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-11">K-11</div>
            <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-12">K-12</div>
          </div>
          <p class="text-[11px] text-slate-400 italic">Landasan pacu utama operasional. Zona bahu rumput dan strip perimeter berjarak aman.</p>
        </div>
      </div>

      <!-- PAGE 2: SPECIES DOCUMENTATION, DISPERSAL ACTION & DIGITAL WET SIGNATURE -->
      <div class="hidden space-y-6" id="modalPage2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Photo & Action Taken -->
          <div class="space-y-4">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Foto Dokumentasi Sisi Udara</span>
            <div class="h-52 rounded-xl border border-border-subtle bg-slate-100 overflow-hidden relative group">
              <img id="modalPhotoImg" class="w-full h-full object-cover" src="{{ asset('images/biawak.jpeg') }}" alt="Foto Dokumentasi Satwa Liar"/>
              <div class="absolute bottom-2 left-2 px-2 py-1 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] rounded font-code-coordinate" id="modalGeoOverlay">
                GEO: -6.125642, 106.655819 | INJOURNEY AIRSIDE
              </div>
            </div>
            
            <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-2">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Tindakan Pengusiran / Mitigasi</span>
              <div class="flex items-center gap-2 flex-wrap text-xs" id="modalDispersalBadges">
                <span class="px-2 py-1 bg-surface-container text-on-surface font-semibold rounded">Sirene Kendaraan AMC</span>
                <span class="px-2 py-1 bg-surface-container text-on-surface font-semibold rounded">Pyro Dispersal Petasan</span>
                <span class="px-2 py-1 bg-surface-container text-on-surface font-semibold rounded">Pemasangan Trap Net</span>
              </div>
              <p class="text-xs text-secondary mt-1" id="modalDispersalDesc">
                Petugas melakukan penyisiran radius 100m ke arah kanal terbuka perimeter untuk memastikan satwa tidak kembali ke strip runway.
              </p>
            </div>
          </div>

          <!-- Digital Signature Display Box (Read-Only Preview) -->
          <div class="space-y-4 flex flex-col">
            <div class="flex items-center justify-between">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Tanda Tangan Petugas Pelapor</span>
              <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-medium border border-emerald-200">
                <span class="material-symbols-outlined text-xs text-resolved-emerald">verified</span> Terekam di Sistem
              </span>
            </div>

            <!-- Signature Display Box -->
            <div class="flex-1 min-h-[190px] border border-border-subtle rounded-xl bg-white p-4 relative flex flex-col justify-between shadow-xs" id="signaturePad">
              <div class="flex-1 flex items-center justify-center p-2 bg-slate-50/70 rounded-lg border border-dashed border-slate-200 min-h-[120px]">
                <img id="modalSignatureImg" src="" alt="Tanda Tangan Petugas" class="max-h-24 max-w-full object-contain hidden">
                <div id="modalNoSignature" class="text-xs text-secondary italic flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-base text-slate-400">draw</span>
                  <span>Tanda tangan digital tersimpan dalam Berita Acara</span>
                </div>
              </div>
              <div class="border-t border-slate-200 pt-2.5 mt-2 flex items-center justify-between text-[11px] text-secondary">
                <span>Dokumen Berita Acara Resmi ICAO Doc 9137</span>
                <span class="text-injourney-teal font-semibold flex items-center gap-1">
                  <span class="material-symbols-outlined text-xs">lock</span> SHA-256 Secured
                </span>
              </div>
            </div>

            <div class="p-3 bg-surface-container-low rounded-lg border border-border-subtle text-xs text-secondary space-y-1">
              <p><strong class="text-on-surface">Petugas Pelapor:</strong> <span id="modalSigOfficerName">-</span></p>
              <p><strong class="text-on-surface">Unit Kerja:</strong> <span id="modalSigUnitName">-</span></p>
            </div>
          </div>

        </div>
      </div>

      <!-- PAGE 3: THREAD TANGGAPAN & FORM INSTRUKSI SAFETY MANAGER -->
      <div class="hidden space-y-6" id="modalPage3">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          
          <!-- Kolom Kiri: Riwayat Thread Tanggapan (7 Kolom) -->
          <div class="lg:col-span-7 space-y-3 flex flex-col">
            <div class="flex items-center justify-between">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-injourney-teal">forum</span>
                Riwayat Koordinasi &amp; Tanggapan Dinas
              </span>
              <span class="text-[11px] text-secondary font-medium" id="threadCountText">0 Catatan</span>
            </div>

            <div class="flex-1 min-h-[300px] max-h-[420px] overflow-y-auto space-y-3 p-4 rounded-xl border border-border-subtle bg-slate-50/70" id="adminThreadContainer">
              <!-- Dynamic Cards Injection -->
            </div>
          </div>

          <!-- Kolom Kanan: Form Kirim Instruksi Baru (5 Kolom) -->
          <div class="lg:col-span-5 p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-4">
            <div class="flex items-center gap-2 border-b border-border-subtle pb-3">
              <span class="material-symbols-outlined text-injourney-teal text-lg">rate_review</span>
              <div>
                <h4 class="font-bold text-xs text-on-surface">Kirim Tanggapan / Instruksi Dinas</h4>
                <p class="text-[11px] text-secondary">Catatan resmi Safety Manager untuk pelapor lapangan</p>
              </div>
            </div>

            <form id="adminTanggapanForm" onsubmit="submitAdminTanggapan(event)" class="space-y-3.5">
              <!-- Kategori Instruksi -->
              <div class="space-y-1">
                <label for="adminKategoriTanggapan" class="text-xs font-semibold text-on-surface block">
                  Kategori Tindak Lanjut Dinas:
                </label>
                <select id="adminKategoriTanggapan" name="kategori_tanggapan" class="w-full text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface py-2 px-2.5 focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer">
                  <option value="instruksi">🚨 Instruksi Lanjutan / Pengusiran Tambahan</option>
                  <option value="evaluasi">📋 Evaluasi Tindakan Penanganan</option>
                  <option value="klarifikasi">❓ Permintaan Klarifikasi Data Lapangan</option>
                  <option value="penutupan">✅ Penutupan Laporan (Closed &amp; Verified)</option>
                </select>
                <p class="text-[10px] text-secondary italic" id="kategoriHelpText">
                  * Kategori 'Penutupan Laporan' akan otomatis memvalidasi Berita Acara menjadi Selesai.
                </p>
              </div>

              <!-- Isi Tanggapan -->
              <div class="space-y-1">
                <label for="adminIsiTanggapan" class="text-xs font-semibold text-on-surface block">
                  Instruksi / Catatan Safety Unit:
                </label>
                <textarea id="adminIsiTanggapan" name="isi" rows="5" required placeholder="Tuliskan arahan dinas, evaluasi SOP, atau catatan verifikasi untuk petugas..." class="w-full text-xs rounded-lg border border-border-subtle bg-white text-on-surface p-2.5 focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal placeholder:text-secondary/60 leading-relaxed"></textarea>
              </div>

              <div class="pt-1">
                <button type="submit" id="btnSubmitAdminTanggapan" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white font-semibold text-xs shadow-xs transition-all cursor-pointer">
                  <span class="material-symbols-outlined text-sm">send</span>
                  <span>Kirim Instruksi Dinas</span>
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>

    </div>

    <!-- Modal Footer Action Bar -->
    <div class="px-6 py-4 bg-surface-container-lowest border-t border-border-subtle flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <a id="modalPrintBtn" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-border-subtle bg-white text-on-surface hover:bg-surface-container-low font-label-lg text-xs transition-colors">
          <span class="material-symbols-outlined text-sm">print</span>
          <span>Pratinjau Cetak</span>
        </a>
        <a id="modalDownloadPdfBtn" href="#" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-label-lg text-xs font-bold transition-colors">
          <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
          <span>Unduh PDF Resmi</span>
        </a>
      </div>
      <div class="flex items-center gap-2">
        <button class="px-4 py-2 rounded-lg border border-border-subtle bg-white text-on-surface hover:bg-surface-container-low font-label-lg text-xs transition-colors cursor-pointer" onclick="closeReportModal()" type="button">
          Tutup
        </button>
        <button id="modalResolveBtn" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white font-label-lg text-xs transition-all shadow-sm cursor-pointer" onclick="validateAndResolve()" type="button">
          <span class="material-symbols-outlined text-sm">verified</span>
          <span>Validasi &amp; Selesaikan Berita Acara</span>
        </button>
      </div>
    </div>

  </div>
</div>
