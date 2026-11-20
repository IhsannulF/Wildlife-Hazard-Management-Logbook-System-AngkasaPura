<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-4 relative overflow-hidden" id="aiBriefingSection">

  <!-- Header Strip -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#00A9C1] to-[#007fa3] text-white flex items-center justify-center shadow-xs shrink-0">
        <span class="material-symbols-outlined text-xl">auto_awesome</span>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-sm sm:text-base font-bold text-slate-800">
            Executive Safety Briefing &amp; Mitigasi Birdstrike
          </h2>
        </div>
        <p class="text-[11px] text-slate-500 mt-0.5">
          Rekomendasi taktis proaktif pencegahan birdstrike berbasis histori, cuaca, logbook spasial, dan pergerakan pesawat.
        </p>
      </div>
    </div>

    <!-- Top Status Badges & Quick Action -->
    <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
      <span id="aiAdvisoryStatusBadge" class="px-2.5 py-1 rounded-full text-xs font-bold border bg-emerald-50 text-emerald-700 border-emerald-200 flex items-center gap-1">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span id="aiAdvisoryStatusText">Memuat...</span>
      </span>

      <!-- Run Manual Prediction Button -->
      <button type="button" 
              id="btnOpenRunAiModal" 
              onclick="openModal('modalRunAiPrediction')" 
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
        <span class="material-symbols-outlined text-base">psychology</span>
        <span>Jalankan Analisis AI</span>
      </button>
    </div>
  </div>

  <!-- Loading State Indicator -->
  <div id="aiBriefingLoading" class="py-10 flex flex-col items-center justify-center gap-2 text-slate-400">
    <div class="w-8 h-8 border-3 border-cyan-500 border-t-transparent rounded-full animate-spin"></div>
    <span class="text-xs font-medium">Memuat data analisis AI terkini...</span>
  </div>

  <!-- Alert Banners Container -->
  <div id="aiAlertContainer" class="space-y-2 hidden">
    <!-- 1. Alert Gagal -->
    <div id="aiAlertFailed" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-rose-600 text-lg">error</span>
        <span>Eksekusi inferensi terakhir mengalami kendala: <strong id="aiAlertFailedMsg"></strong>. Menampilkan data validasi sukses sebelumnya.</span>
      </div>
    </div>

    <!-- 2. Alert Kedaluwarsa -->
    <div id="aiAlertExpired" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
      <span class="material-symbols-outlined text-amber-600 text-lg">history</span>
      <span>Advisory keselamatan ini berstatus <strong>kedaluwarsa</strong> (> 24 jam). Klik "Jalankan Analisis AI" untuk memperbarui data target hari ini.</span>
    </div>

    <!-- 3. Alert Data Minim -->
    <div id="aiAlertDataMinim" class="hidden p-3 rounded-xl bg-cyan-50 border border-cyan-200 text-[#007fa3] text-xs flex items-center gap-2">
      <span class="material-symbols-outlined text-cyan-600 text-lg">info</span>
      <span>Data logbook / histori pendukung relatif minim (&lt; 14 hari). Model otomatis menurunkan <em>confidence score</em>.</span>
    </div>
  </div>

  <!-- Main AI Briefing Content (Hidden while loading) -->
  <div id="aiBriefingContent" class="hidden grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
    
    <!-- Left Column: Risk Gauge & Critical Operational Metrics (4 Cols) -->
    <div class="lg:col-span-4 flex flex-col justify-between space-y-3">
      <!-- Gauge Component -->
      <x-ai.risk-gauge id="aiDashboardGaugeCanvas" />

      <!-- Critical Time Window & Critical Zones Info Box -->
      <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5 text-xs">
        <!-- Jam Rawan -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-1.5 text-slate-500 font-semibold">
            <span class="material-symbols-outlined text-amber-600 text-base">alarm</span>
            <span>Jam Rawan Kritis:</span>
          </div>
          <span id="aiJamRawan" class="font-bold font-mono text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200">
            --:-- - --:-- WIB
          </span>
        </div>

        <!-- Grid Kritis -->
        <div class="flex items-start justify-between gap-2">
          <div class="flex items-center gap-1.5 text-slate-500 font-semibold shrink-0">
            <span class="material-symbols-outlined text-rose-600 text-base">grid_view</span>
            <span>Grid Prioritas:</span>
          </div>
          <div id="aiGridKritisList" class="flex flex-wrap gap-1 justify-end">
            <span class="px-1.5 py-0.5 rounded bg-white font-mono font-bold text-[10px] text-slate-700 border border-slate-200">-</span>
          </div>
        </div>

        <!-- Spesies Sasaran -->
        <div class="pt-2 border-t border-slate-200/80">
          <div class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wider">Spesies Target Ancaman</div>
          <div id="aiSpesiesList" class="space-y-1">
            <div class="text-[11px] text-slate-700">-</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Executive Summary, Contributing Factors & Recommendations (8 Cols) -->
    <div class="lg:col-span-8 flex flex-col justify-between space-y-3.5">
      
      <!-- Executive Summary -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <h3 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[#00A9C1] text-base">summarize</span>
            <span>Ringkasan Eksekutif Situasi Sisi Udara</span>
          </h3>
          <span id="aiWaktuAnalisisText" class="text-[10px] text-slate-400 font-mono">
            Analisis: --
          </span>
        </div>
        <p id="aiSummaryText" class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200 leading-relaxed font-normal">
          Memuat ringkasan eksekutif...
        </p>
      </div>

      <!-- Actionable Tactical Recommendations -->
      <div>
        <h3 class="text-xs font-bold text-slate-700 flex items-center gap-1.5 mb-1.5">
          <span class="material-symbols-outlined text-emerald-600 text-base">checklist</span>
          <span>Rekomendasi Taktis Tindakan Unit AMC / Dispersal</span>
        </h3>
        <ul id="aiRecommendationsList" class="space-y-1.5 text-xs text-slate-700 bg-emerald-50/40 p-3 rounded-xl border border-emerald-100/90 list-decimal list-inside">
          <li>Memuat butir rekomendasi...</li>
        </ul>
      </div>

      <!-- Contributing Factors Tags -->
      <div>
        <div class="text-[10px] uppercase font-bold text-slate-400 mb-1.5 tracking-wider">Faktor Kontributor Risiko</div>
        <div id="aiContributingFactors" class="flex flex-wrap gap-1.5 text-[11px]">
          <!-- Dynamic tags -->
        </div>
      </div>

      <!-- Footer Action & Disclaimer -->
      <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="text-[10px] text-slate-400 flex items-center gap-1 italic">
          <span class="material-symbols-outlined text-xs">verified_user</span>
          <span>Dihasilkan oleh AI — Advisory only, keputusan akhir pada Safety Manager &amp; AMC.</span>
        </div>

        <div class="flex items-center gap-2">
          <!-- Acknowledge Button -->
          <button type="button" 
                  id="btnAcknowledgeAdvisory" 
                  onclick="acknowledgeAdvisory()" 
                  class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-2xs transition-colors flex items-center gap-1 cursor-pointer">
            <span class="material-symbols-outlined text-base text-emerald-400">task_alt</span>
            <span id="btnAcknowledgeText">Akui Advisory</span>
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- Empty State when no prediction ever created -->
  <div id="aiBriefingEmpty" class="hidden py-8 text-center text-slate-400 space-y-2">
    <span class="material-symbols-outlined text-4xl text-slate-300">query_stats</span>
    <p class="text-xs">Belum ada hasil analisis prediksi birdstrike yang tersimpan.</p>
    <button type="button" onclick="openModal('modalRunAiPrediction')" class="px-4 py-2 rounded-xl bg-[#00A9C1] hover:bg-[#007fa3] text-white text-xs font-bold">
      Jalankan Analisis Sekarang
    </button>
  </div>
</div>

<!-- ================= MODAL JALANKAN ANALISIS AI ================= -->
<div id="modalRunAiPrediction" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
  <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all p-5 text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
      <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
        <span class="material-symbols-outlined text-[#00A9C1]">psychology</span>
        Jalankan Analisis AI On-Demand
      </h3>
      <button type="button" onclick="closeModal('modalRunAiPrediction')" class="text-slate-400 hover:text-slate-600">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>

    <form id="formRunAiPrediction" onsubmit="submitRunAiPrediction(event)" class="space-y-3.5">
      <p class="text-slate-500 leading-relaxed">
        Sistem akan mengagregasikan data logbook satwa liar 30 hari terakhir, histori insiden, prakiraan cuaca terkini, dan jadwal pergerakan pesawat untuk memprediksi risiko birdstrike hari ini.
      </p>

      <div>
        <label class="block font-semibold text-slate-700 mb-1">
          Catatan Kondisi Habitat Sisi Udara (Opsional, Maks 500 Karakter)
        </label>
        <textarea name="catatan_habitat" 
                  id="modalCatatanHabitat" 
                  rows="3" 
                  maxlength="500" 
                  placeholder="Contoh: Rumput perimeter baru dipotong, ada genangan air di dekat threshold runway 10 akibat hujan lebat..."
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00A9C1] bg-slate-50/50"></textarea>
        <div class="text-[10px] text-slate-400 text-right mt-1">Maksimal 500 karakter</div>
      </div>

      <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200 text-amber-800 text-[11px] flex items-center gap-2">
        <span class="material-symbols-outlined text-amber-600 text-base">alarm</span>
        <span>Analisis manual dibatasi cooldown 10 menit dan kuota maks 10 kali/hari demi efisiensi kuota API.</span>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeModal('modalRunAiPrediction')" class="px-3.5 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
          Batal
        </button>
        <button type="submit" id="btnSubmitRunAi" class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] text-white font-bold flex items-center gap-1.5 shadow-xs">
          <span class="material-symbols-outlined text-base">auto_awesome</span>
          <span>Mulai Analisis</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  let currentAiPredictionId = null;

  async function loadLatestAiPrediction() {
    const loadingEl = document.getElementById('aiBriefingLoading');
    const contentEl = document.getElementById('aiBriefingContent');
    const emptyEl = document.getElementById('aiBriefingEmpty');
    const alertBox = document.getElementById('aiAlertContainer');

    try {
      const response = await fetch("{{ route('admin.ai.prediksi.latest') }}", {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      const data = await response.json();

      if (!data.success || !data.has_prediction) {
        if (loadingEl) loadingEl.classList.add('hidden');
        if (emptyEl) emptyEl.classList.remove('hidden');
        return;
      }

      const p = data.prediksi;
      currentAiPredictionId = p.id;

      // Update Gauge
      if (window.AIRiskGauge) {
        window.AIRiskGauge.update(p.skor_probabilitas, p.level_bahaya, p.confidence_score);
      }

      // Update Model badge & Status
      const statusBadge = document.getElementById('aiAdvisoryStatusBadge');
      const statusText = document.getElementById('aiAdvisoryStatusText');
      if (statusText) statusText.innerText = (p.status_advisory || 'Aktif').toUpperCase();

      const btnAck = document.getElementById('btnAcknowledgeAdvisory');
      if (btnAck) {
        if (p.status_advisory === 'diakui') {
          btnAck.classList.add('opacity-50', 'pointer-events-none');
          document.getElementById('btnAcknowledgeText').innerText = 'Telah Diakui';
        } else {
          btnAck.classList.remove('opacity-50', 'pointer-events-none');
          document.getElementById('btnAcknowledgeText').innerText = 'Akui Advisory';
        }
      }

      // Time & Zones
      const jamEl = document.getElementById('aiJamRawan');
      if (jamEl) {
        jamEl.innerText = (p.jam_rawan_mulai && p.jam_rawan_selesai)
          ? `${p.jam_rawan_mulai} - ${p.jam_rawan_selesai} WIB`
          : 'Pagi Hari (06:00 - 08:30 WIB)';
      }

      const gridList = document.getElementById('aiGridKritisList');
      if (gridList && p.grid_kritis && p.grid_kritis.length > 0) {
        gridList.innerHTML = '';
        p.grid_kritis.forEach(g => {
          const span = document.createElement('span');
          span.className = 'px-1.5 py-0.5 rounded bg-white font-mono font-bold text-[10px] text-slate-800 border border-slate-200';
          span.innerText = g;
          gridList.appendChild(span);
        });
      }

      // Target Species
      const speciesList = document.getElementById('aiSpesiesList');
      if (speciesList && p.spesies_target && p.spesies_target.length > 0) {
        speciesList.innerHTML = '';
        p.spesies_target.slice(0, 3).forEach(s => {
          const div = document.createElement('div');
          div.className = 'text-[11px] text-slate-700 flex items-center justify-between';
          div.innerHTML = `<span class="font-bold text-slate-800">${s.nama_lokal} <em class="text-slate-400 font-normal">(${s.nama_latin || ''})</em></span>`;
          speciesList.appendChild(div);
        });
      }

      // Executive Summary
      const summaryEl = document.getElementById('aiSummaryText');
      if (summaryEl) summaryEl.innerText = p.ringkasan_eksekutif || 'Tidak ada ringkasan.';

      const waktuEl = document.getElementById('aiWaktuAnalisisText');
      if (waktuEl && p.waktu_analisis) {
        waktuEl.innerText = 'Waktu Analisis: ' + new Date(p.waktu_analisis).toLocaleString('id-ID');
      }

      // Recommendations
      const recList = document.getElementById('aiRecommendationsList');
      if (recList && p.rekomendasi_taktis && p.rekomendasi_taktis.length > 0) {
        recList.innerHTML = '';
        p.rekomendasi_taktis.forEach(r => {
          const li = document.createElement('li');
          li.innerText = r;
          recList.appendChild(li);
        });
      }

      // Contributing Factors
      const factorsEl = document.getElementById('aiContributingFactors');
      if (factorsEl && p.faktor_penyebab && p.faktor_penyebab.length > 0) {
        factorsEl.innerHTML = '';
        p.faktor_penyebab.forEach(f => {
          const span = document.createElement('span');
          span.className = 'px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200';
          span.innerText = f;
          factorsEl.appendChild(span);
        });
      }

      // Alert conditions
      if (alertBox) {
        let showAlert = false;
        if (data.is_expired) {
          document.getElementById('aiAlertExpired')?.classList.remove('hidden');
          showAlert = true;
        }
        if (data.data_minim) {
          document.getElementById('aiAlertDataMinim')?.classList.remove('hidden');
          showAlert = true;
        }
        if (data.last_execution && data.last_execution.status === 'gagal') {
          const failAlert = document.getElementById('aiAlertFailed');
          const failMsg = document.getElementById('aiAlertFailedMsg');
          if (failAlert && failMsg) {
            failMsg.innerText = data.last_execution.error_message || 'Timeout jaringan';
            failAlert.classList.remove('hidden');
            showAlert = true;
          }
        }
        if (showAlert) alertBox.classList.remove('hidden');
      }

      // Show content
      if (loadingEl) loadingEl.classList.add('hidden');
      if (contentEl) contentEl.classList.remove('hidden');

    } catch (err) {
      console.error('Failed to load AI prediction widget:', err);
      if (loadingEl) loadingEl.classList.add('hidden');
      if (emptyEl) emptyEl.classList.remove('hidden');
    }
  }

  async function acknowledgeAdvisory() {
    if (!currentAiPredictionId) return;

    const btn = document.getElementById('btnAcknowledgeAdvisory');
    const originalText = document.getElementById('btnAcknowledgeText').innerText;
    document.getElementById('btnAcknowledgeText').innerText = 'Memproses...';
    btn.disabled = true;

    try {
      const response = await fetch(`/admin/ai/prediksi/${currentAiPredictionId}/akui`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        }
      });
      const data = await response.json();

      if (data.success) {
        document.getElementById('aiAdvisoryStatusText').innerText = 'DIAKUI';
        document.getElementById('btnAcknowledgeText').innerText = 'Telah Diakui';
        btn.classList.add('opacity-50', 'pointer-events-none');
        alert('Advisory keselamatan AI berhasil diakui oleh Safety Manager.');
      }
    } catch (err) {
      alert('Gagal mengakui advisory: ' + err.message);
      btn.disabled = false;
      document.getElementById('btnAcknowledgeText').innerText = originalText;
    }
  }

  async function submitRunAiPrediction(event) {
    event.preventDefault();
    const btn = document.getElementById('btnSubmitRunAi');
    const note = document.getElementById('modalCatatanHabitat').value;

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">refresh</span> <span>Menganalisis...</span>';

    try {
      const response = await fetch("{{ route('admin.ai.prediksi.run') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: JSON.stringify({ catatan_habitat: note })
      });

      const res = await response.json();

      if (response.status === 429) {
        alert(res.message);
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-base">auto_awesome</span> <span>Mulai Analisis</span>';
        return;
      }

      if (!res.success) {
        alert('Gagal: ' + res.message);
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-base">auto_awesome</span> <span>Mulai Analisis</span>';
        return;
      }

      closeModal('modalRunAiPrediction');
      alert('Analisis prediksi AI berhasil!');
      loadLatestAiPrediction();

    } catch (err) {
      alert('Kesalahan jaringan: ' + err.message);
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-base">auto_awesome</span> <span>Mulai Analisis</span>';
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    loadLatestAiPrediction();
  });
</script>
