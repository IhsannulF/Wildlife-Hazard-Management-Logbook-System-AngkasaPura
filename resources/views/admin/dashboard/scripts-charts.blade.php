<script type="application/json" id="whmsStatistikData">@json($statistik)</script>
<script>
  // ================= STATISTIK & INFOGRAFIS POPULASI BURUNG (CHART.JS) =================
  const WHMS_STATISTIK = JSON.parse(document.getElementById('whmsStatistikData').textContent);

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
      return;
    }

    const COLOR = {
      teal: '#00A3B4',
      indigo: '#6250D6',
      rose: '#E34948',
      amber: '#EDA100',
      text: '#475569',
      grid: 'rgba(226, 232, 240, 0.7)',
      surface: '#FFFFFF',
    };
    const ACTIVITY_COLORS = ['#16A34A', '#EAB308', '#F97316', '#DC2626'];
    const ACTIVITY_LABELS = ['Rendah', 'Sedang', 'Tinggi', 'Sangat Tinggi'];
    const nf = new Intl.NumberFormat('id-ID');

    Chart.defaults.font.family = 'DM Sans, Inter, system-ui, sans-serif';
    Chart.defaults.font.size = 11;
    Chart.defaults.color = COLOR.text;

    const tooltip = {
      backgroundColor: '#0F172A',
      titleFont: { weight: 'bold' },
      padding: 8,
      cornerRadius: 8,
      displayColors: false,
    };
    const valueAxis = (title) => ({
      beginAtZero: true,
      grid: { color: COLOR.grid },
      border: { display: false },
      ticks: { precision: 0, callback: (v) => nf.format(v) },
      title: title ? { display: true, text: title, font: { size: 10 } } : undefined,
    });
    const categoryAxis = { grid: { display: false }, border: { display: false } };
    const barStyle = { borderRadius: 4, borderSkipped: 'start', maxBarThickness: 22 };

    function draw(id, config) {
      const el = document.getElementById(id);
      if (el) {
        new Chart(el, config);
      }
    }

    // 1. Tren populasi burung bulanan (satu sumbu: ekor burung; observasi di tooltip)
    const tren = WHMS_STATISTIK.tren;
    draw('trendChart', {
      type: 'line',
      data: {
        labels: tren.labels,
        datasets: [{
          label: 'Ekor burung',
          data: tren.burung,
          borderColor: COLOR.teal,
          backgroundColor: 'rgba(0, 163, 180, 0.10)',
          fill: true,
          tension: 0.3,
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointBackgroundColor: COLOR.teal,
          pointBorderColor: COLOR.surface,
          pointBorderWidth: 2,
        }],
      },
      options: {
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            ...tooltip,
            callbacks: {
              label: (ctx) => `${nf.format(ctx.parsed.y)} ekor burung`,
              afterLabel: (ctx) => `${nf.format(tren.observasi[ctx.dataIndex])} laporan observasi`,
            },
          },
        },
        scales: { x: categoryAxis, y: valueAxis('Ekor') },
      },
    });

    // 2. Top 10 jenis burung (bar horizontal)
    const top = WHMS_STATISTIK.top_jenis;
    draw('speciesBarChart', {
      type: 'bar',
      data: {
        labels: top.map((row) => row.nama.replace(/^Burung\s+/i, '')),
        datasets: [{ data: top.map((row) => row.jumlah), backgroundColor: COLOR.teal, ...barStyle }],
      },
      options: {
        indexAxis: 'y',
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            ...tooltip,
            callbacks: {
              title: (items) => top[items[0].dataIndex].nama,
              label: (ctx) => `${nf.format(top[ctx.dataIndex].jumlah)} ekor (${top[ctx.dataIndex].persen.toLocaleString('id-ID')}%)`,
            },
          },
        },
        scales: { x: valueAxis('Ekor'), y: categoryAxis },
      },
    });

    // 3. Indeks Shannon per bulan
    draw('diversityChart', {
      type: 'line',
      data: {
        labels: tren.labels,
        datasets: [{
          data: tren.shannon,
          borderColor: COLOR.indigo,
          backgroundColor: COLOR.indigo,
          borderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 5,
          spanGaps: false,
        }],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { ...tooltip, callbacks: { label: (ctx) => `H' = ${ctx.parsed.y.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` } },
        },
        scales: {
          x: { ...categoryAxis, ticks: { font: { size: 9 } } },
          y: { beginAtZero: true, grid: { color: COLOR.grid }, border: { display: false }, ticks: { maxTicksLimit: 3, font: { size: 9 } } },
        },
      },
    });

    // 4. Distribusi kelas aktivitas grid (donut)
    const distribusi = WHMS_STATISTIK.grid.distribusi;
    draw('gridDonutChart', {
      type: 'doughnut',
      data: {
        labels: ACTIVITY_LABELS,
        datasets: [{
          data: [distribusi.rendah, distribusi.sedang, distribusi.tinggi, distribusi.sangat_tinggi],
          backgroundColor: ACTIVITY_COLORS,
          borderColor: COLOR.surface,
          borderWidth: 2,
          hoverOffset: 4,
        }],
      },
      options: {
        cutout: '68%',
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { ...tooltip, callbacks: { label: (ctx) => `${ctx.label}: ${nf.format(ctx.parsed)} grid` } },
        },
      },
    });

    // 5. Jam temuan burung
    const jam = WHMS_STATISTIK.jam;
    draw('hourlyBarChart', {
      type: 'bar',
      data: {
        labels: jam.labels,
        datasets: [{ data: jam.burung, backgroundColor: COLOR.amber, ...barStyle, maxBarThickness: 14 }],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { ...tooltip, callbacks: { title: (items) => `Pukul ${items[0].label}:00–${items[0].label}:59`, label: (ctx) => `${nf.format(ctx.parsed.y)} ekor` } },
        },
        scales: { x: { ...categoryAxis, ticks: { autoSkip: true, maxTicksLimit: 12 } }, y: valueAxis() },
      },
    });

    // 6. Kejadian bird strike per fase & waktu
    const kejadian = WHMS_STATISTIK.kejadian;
    const eventBar = (id, source, title) => draw(id, {
      type: 'bar',
      data: {
        labels: Object.keys(source),
        datasets: [{ data: Object.values(source), backgroundColor: COLOR.rose, ...barStyle }],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          title: { display: true, text: title, align: 'start', font: { size: 11, weight: 'bold' }, color: '#0F172A' },
          tooltip: { ...tooltip, callbacks: { label: (ctx) => `${nf.format(ctx.parsed.y)} insiden` } },
        },
        scales: { x: { ...categoryAxis, ticks: { font: { size: 9.5 } } }, y: valueAxis() },
      },
    });
    eventBar('phaseBarChart', kejadian.fase, 'Fase penerbangan');
    eventBar('timeBarChart', kejadian.waktu, 'Waktu kejadian');

    // 7. Korelasi traffic vs burung (scatter)
    const korelasi = WHMS_STATISTIK.korelasi;
    draw('correlationChart', {
      type: 'scatter',
      data: {
        datasets: [{
          data: korelasi.titik,
          backgroundColor: 'rgba(0, 163, 180, 0.75)',
          borderColor: COLOR.surface,
          borderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7,
        }],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            ...tooltip,
            callbacks: {
              title: (items) => new Date(items[0].raw.tanggal + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
              label: (ctx) => [`${nf.format(ctx.raw.x)} pergerakan pesawat`, `${nf.format(ctx.raw.y)} ekor burung`],
            },
          },
        },
        scales: { x: valueAxis('Pergerakan pesawat / hari'), y: valueAxis('Ekor burung / hari') },
      },
    });
  });
</script>
