<div id="aiSpeciesResultPanel" class="hidden bg-white rounded-2xl border border-cyan-200/90 shadow-lg p-4 sm:p-5 space-y-4 my-4 animate-in fade-in duration-200">
  
  <!-- Panel Header -->
  <div class="flex items-center justify-between pb-3 border-b border-slate-100">
    <div class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#00A9C1] to-[#007fa3] text-white flex items-center justify-center shadow-xs shrink-0">
        <span class="material-symbols-outlined text-lg">auto_awesome</span>
      </div>
      <div>
        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
          Hasil Identifikasi Satwa &amp; Rekomendasi SOP (Gemini Vision)
        </h3>
        <p class="text-[11px] text-slate-500">
          Analisis visual otomatis taksonomi satwa liar dan rekomendasi SOP ICAO Doc 9137 Part 3.
        </p>
      </div>
    </div>

    <button type="button" onclick="closeSpeciesResultPanel(false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
      <span class="material-symbols-outlined text-base">close</span>
    </button>
  </div>

  <!-- Low Confidence Alert -->
  <div id="aiConfidenceWarning" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
    <span class="material-symbols-outlined text-amber-600 text-lg">warning</span>
    <span>Tingkat keyakinan identifikasi AI berada di bawah 60%. Mohon verifikasi morfologi visual secara manual sebelum menerapkan.</span>
  </div>

  <!-- State: Satwa Terdeteksi -->
  <div id="aiSpeciesFoundSection" class="space-y-4 text-xs">
    
    <!-- Top Candidate Card -->
    <div class="p-3.5 rounded-xl bg-gradient-to-r from-cyan-50/70 to-slate-50 border border-cyan-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center gap-2">
          <span id="aiCandidateLocalName" class="text-base font-extrabold text-slate-900">-</span>
          <span id="aiCandidateConfidenceBadge" class="px-2 py-0.5 rounded-full font-bold font-mono text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-200">
            94% Match
          </span>
          <span id="aiCatalogMatchedBadge" class="hidden px-2 py-0.5 rounded-full font-semibold text-[10px] bg-cyan-100 text-[#007fa3]">
            Tersedia di Master Satwa
          </span>
        </div>
        <div class="text-xs text-slate-500 italic mt-0.5" id="aiCandidateLatinName">
          Spesies Ilmiah
        </div>
      </div>

      <!-- Quick Biological Metrics -->
      <div class="flex flex-wrap items-center gap-2 font-mono text-[11px]">
        <span id="aiCandidateWeightBadge" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700">
          Bobot: ~0.4 kg
        </span>
        <span id="aiCandidateRiskCatBadge" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700">
          Kategori Risiko ICAO: 1 (&lt; 1 kg)
        </span>
      </div>
    </div>

    <!-- Behavior & Taxonomy Details -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
        <div class="text-[10px] uppercase font-bold text-slate-400">Klasifikasi Taksonomi</div>
        <div id="aiTaxonomyDetails" class="text-slate-700 font-medium">
          Kelas: Aves • Ordo: Pelecaniformes • Famili: Ardeidae
        </div>
      </div>

      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
        <div class="text-[10px] uppercase font-bold text-slate-400">Perilaku Terlihat Pada Foto</div>
        <div id="aiBehaviorDetails" class="text-slate-700 font-medium">
          Berdiri mencari makan di area rumput perimeter
        </div>
      </div>
    </div>

    <!-- SOP Recommendations (ICAO Doc 9137 Part 3) -->
    <div class="space-y-2">
      <div class="flex items-center gap-1.5 font-bold text-slate-800">
        <span class="material-symbols-outlined text-[#00A9C1] text-base">security</span>
        <span>Rekomendasi SOP Pengusiran Satwa (ICAO Doc 9137 Part 3)</span>
      </div>

      <div id="aiSopRecommendationsContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <!-- Dynamic SOP items -->
      </div>
    </div>
  </div>

  <!-- State: Tidak Terdeteksi Satwa -->
  <div id="aiNoSpeciesSection" class="hidden py-6 text-center text-slate-500 space-y-1 text-xs">
    <span class="material-symbols-outlined text-3xl text-slate-400">help_center</span>
    <p class="font-bold text-slate-700">Tidak Terdeteksi Satwa Liar</p>
    <p class="text-[11px] text-slate-400">Foto tidak memuat ciri satwa yang jelas atau objek terhalang. Silakan pilih jenis satwa secara manual dari katalog.</p>
  </div>

  <!-- Footer Actions & Disclaimer -->
  <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
    <div class="text-[10px] text-slate-400 italic">
      * Rekomendasi SOP bersifat panduan umum ICAO dan wajib diselaraskan dengan SOP operasional setempat.
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <button type="button" 
              onclick="closeSpeciesResultPanel(false)" 
              class="px-3.5 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold cursor-pointer">
        Abaikan
      </button>

      <button type="button" 
              id="btnApplyAiToForm" 
              onclick="applyAiIdentificationToForm()" 
              class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] text-white font-bold flex items-center gap-1.5 shadow-xs cursor-pointer">
        <span class="material-symbols-outlined text-base">check_circle</span>
        <span>Terapkan ke Formulir</span>
      </button>
    </div>
  </div>

</div>
