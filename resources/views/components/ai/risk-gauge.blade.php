@props([
    'score' => null,
    'level' => null,
    'confidence' => null,
    'id' => 'aiRiskGaugeCanvas',
])

<div class="flex flex-col items-center justify-center p-4 bg-white rounded-2xl border border-slate-200 shadow-2xs relative overflow-hidden" id="aiGaugeContainer">
  <div class="w-full flex items-center justify-between pb-2 border-b border-slate-100 mb-2">
    <div class="flex items-center gap-1.5">
      <span class="material-symbols-outlined text-[#00A9C1] text-lg">speed</span>
      <span class="text-xs font-bold text-slate-800">Probabilitas Birdstrike</span>
    </div>
    <span id="aiGaugeConfBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold font-mono">
      Conf: <strong id="aiGaugeConfVal">{{ $confidence !== null ? round($confidence * 100) . '%' : '-' }}</strong>
    </span>
  </div>

  <!-- Gauge Canvas Container -->
  <div class="relative w-44 h-24 flex items-center justify-center">
    <canvas id="{{ $id }}" width="176" height="96"></canvas>
    <!-- Center Value Overlay -->
    <div class="absolute bottom-1 flex flex-col items-center">
      <span id="aiGaugePercent" class="text-2xl font-black text-slate-800 font-mono tracking-tight leading-none">
        {{ $score !== null ? round($score * 100) . '%' : '--%' }}
      </span>
      <span id="aiGaugeLevelText" class="text-[10px] font-extrabold uppercase mt-0.5 px-2 py-0.2 rounded-full border {{ $level === 'kritis' ? 'bg-rose-100 text-rose-800 border-rose-200' : ($level === 'tinggi' ? 'bg-amber-100 text-amber-800 border-amber-200' : ($level === 'sedang' ? 'bg-cyan-100 text-[#007fa3] border-cyan-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200')) }}">
        {{ $level ? strtoupper($level) : 'MEMUAT' }}
      </span>
    </div>
  </div>

  <!-- Gauge Range Legend -->
  <div class="w-full flex items-center justify-between text-[9px] text-slate-400 font-bold px-2 pt-2 border-t border-slate-100 mt-2 font-mono">
    <span class="text-emerald-600">0% RENDAH</span>
    <span class="text-cyan-600">30%</span>
    <span class="text-amber-600">60%</span>
    <span class="text-rose-600">85%+ KRITIS</span>
  </div>
</div>

<script>
  window.AIRiskGauge = (function() {
    let gaugeChart = null;

    function getColor(score) {
      if (score < 0.30) return '#10b981'; // emerald
      if (score < 0.60) return '#00A9C1'; // cyan/blue
      if (score < 0.85) return '#f59e0b'; // amber
      return '#ef4444'; // rose/kritis
    }

    function initGauge(score = 0) {
      const canvas = document.getElementById("{{ $id }}");
      if (!canvas) return;
      const ctx = canvas.getContext('2d');

      const val = Math.min(Math.max(score, 0), 1) * 100;
      const primaryColor = getColor(score);

      if (gaugeChart) {
        gaugeChart.destroy();
      }

      // Check if Chart.js is loaded
      if (typeof Chart === 'undefined') {
        return;
      }

      gaugeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          datasets: [{
            data: [val, 100 - val],
            backgroundColor: [primaryColor, '#e2e8f0'],
            borderWidth: 0,
            circumference: 180,
            rotation: 270,
            borderRadius: 6,
          }]
        },
        options: {
          responsive: false,
          maintainAspectRatio: false,
          cutout: '76%',
          plugins: {
            tooltip: { enabled: false },
            legend: { enabled: false }
          },
          animation: {
            duration: 800,
            easing: 'easeOutQuart'
          }
        }
      });
    }

    function updateGauge(score, level, confidence) {
      const percentEl = document.getElementById('aiGaugePercent');
      const levelEl = document.getElementById('aiGaugeLevelText');
      const confEl = document.getElementById('aiGaugeConfVal');

      const s = parseFloat(score || 0);
      const pct = Math.round(s * 100);

      if (percentEl) percentEl.innerText = pct + '%';
      if (confEl) confEl.innerText = Math.round((confidence || 0) * 100) + '%';

      if (levelEl) {
        levelEl.innerText = (level || 'SEDANG').toUpperCase();
        levelEl.className = 'text-[10px] font-extrabold uppercase mt-0.5 px-2 py-0.2 rounded-full border ';
        if (level === 'kritis') {
          levelEl.className += 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse';
        } else if (level === 'tinggi') {
          levelEl.className += 'bg-amber-100 text-amber-800 border-amber-200';
        } else if (level === 'sedang') {
          levelEl.className += 'bg-cyan-100 text-[#007fa3] border-cyan-200';
        } else {
          levelEl.className += 'bg-emerald-100 text-emerald-800 border-emerald-200';
        }
      }

      initGauge(s);
    }

    return {
      init: initGauge,
      update: updateGauge
    };
  })();

  document.addEventListener('DOMContentLoaded', function() {
    @if($score !== null)
      window.AIRiskGauge.init({{ (float) $score }});
    @endif
  });
</script>
