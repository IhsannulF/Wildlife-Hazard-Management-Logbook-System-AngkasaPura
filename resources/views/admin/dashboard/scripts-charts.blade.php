<script>
  // ================= AIRSIDE VISUAL ANALYTICS CHARTS (CHART.JS) =================
  // Safely encode chart datasets from controller
  const chartMonthLabels   = {!! json_encode($monthLabels ?? ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']) !!};
  const chartTrendTotal     = {!! json_encode($trendTotal ?? [14, 16, 20, 18, 15, 12, 11, 13, 18, 24, 28, 32]) !!};
  const chartTrendHandled   = {!! json_encode($trendHandled ?? [13, 15, 19, 17, 14, 12, 11, 12, 17, 22, 26, 30]) !!};
  const chartTrendCritical  = {!! json_encode($trendCritical ?? [1, 1, 1, 1, 1, 0, 0, 1, 1, 2, 2, 2]) !!};
  const chartCategoryLabels = {!! json_encode($categoryData['labels'] ?? ['Avian / Burung', 'Reptil', 'Mamalia']) !!};
  const chartCategoryCounts = {!! json_encode($categoryData['counts'] ?? [124, 48, 27]) !!};
  const chartSpeciesLabels  = {!! json_encode($topSpeciesLabels ?? ['Biawak Air', 'Burung Blekok', 'Kuntul Kerbau', 'Layang-layang Api', 'Anjing Liar']) !!};
  const chartSpeciesCounts  = {!! json_encode($topSpeciesCounts ?? [38, 44, 29, 22, 12]) !!};
  const chartHourlyLabels   = {!! json_encode($hourlyLabels ?? ['Pagi (06-10)', 'Siang (10-14)', 'Sore (14-18)', 'Malam (18-06)']) !!};
  const chartHourlyCounts   = {!! json_encode($hourlyCounts ?? [78, 24, 64, 33]) !!};
  const chartZoneLabels     = {!! json_encode($zoneLabels ?? ['Runway 07L/25R', 'Kanal & Rawa', 'Taxiway A & B', 'Apron Komersial', 'Perimeter Luar']) !!};
  const chartZoneCounts     = {!! json_encode($zoneCounts ?? [84, 46, 34, 28, 18]) !!};

  document.addEventListener('DOMContentLoaded', function() {
    // 1. CHART 1: TREN TEMPORAL BULANAN (LINE & AREA)
    const ctxTrend = document.getElementById('trendChart');
    if (ctxTrend && typeof Chart !== 'undefined') {
      const gradientTeal = ctxTrend.getContext('2d').createLinearGradient(0, 0, 0, 240);
      gradientTeal.addColorStop(0, 'rgba(0, 163, 180, 0.28)');
      gradientTeal.addColorStop(1, 'rgba(0, 163, 180, 0.01)');

      const gradientEmerald = ctxTrend.getContext('2d').createLinearGradient(0, 0, 0, 240);
      gradientEmerald.addColorStop(0, 'rgba(16, 185, 129, 0.22)');
      gradientEmerald.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

      new Chart(ctxTrend, {
        type: 'line',
        data: {
          labels: chartMonthLabels,
          datasets: [
            {
              label: 'Total Temuan Satwa',
              data: chartTrendTotal,
              borderColor: '#00A3B4',
              backgroundColor: gradientTeal,
              fill: true,
              tension: 0.38,
              borderWidth: 2.5,
              pointRadius: 3,
              pointHoverRadius: 6,
              pointBackgroundColor: '#00A3B4',
            },
            {
              label: 'Berhasil Ditangani (Dispersal)',
              data: chartTrendHandled,
              borderColor: '#10B981',
              backgroundColor: gradientEmerald,
              fill: true,
              tension: 0.38,
              borderWidth: 2,
              pointRadius: 2.5,
              pointHoverRadius: 5,
              pointBackgroundColor: '#10B981',
            },
            {
              label: 'Kejadian Kritis / Aktif',
              data: chartTrendCritical,
              borderColor: '#EF4444',
              borderDash: [4, 4],
              fill: false,
              tension: 0.35,
              borderWidth: 1.8,
              pointRadius: 2,
              pointHoverRadius: 5,
              pointBackgroundColor: '#EF4444',
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#0F172A',
              titleFont: { family: 'DM Sans', size: 11, weight: 'bold' },
              bodyFont: { family: 'DM Sans', size: 11 },
              padding: 8,
              cornerRadius: 8,
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { font: { family: 'DM Sans', size: 10 }, color: '#64748B' }
            },
            y: {
              grid: { color: 'rgba(226, 232, 240, 0.7)' },
              ticks: { font: { family: 'DM Sans', size: 10 }, color: '#64748B', precision: 0 }
            }
          }
        }
      });
    }

    // 2. CHART 2: KOMPOSISI TAKSONOMI (DOUGHNUT)
    const ctxDonut = document.getElementById('categoryDonutChart');
    if (ctxDonut && typeof Chart !== 'undefined') {
      new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
          labels: chartCategoryLabels,
          datasets: [{
            data: chartCategoryCounts,
            backgroundColor: ['#00A3B4', '#F59E0B', '#6366F1'],
            borderWidth: 3,
            borderColor: '#FFFFFF',
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '72%',
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#0F172A',
              titleFont: { family: 'DM Sans', size: 11, weight: 'bold' },
              bodyFont: { family: 'DM Sans', size: 11 },
              padding: 8,
              cornerRadius: 8,
              callbacks: {
                label: function(context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const val = context.raw;
                  const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                  return ` ${context.label}: ${val} (${pct}%)`;
                }
              }
            }
          }
        }
      });
    }

    // 3. CHART 3: TOP 5 SPESIES (HORIZONTAL BAR)
    const ctxSpecies = document.getElementById('speciesBarChart');
    if (ctxSpecies && typeof Chart !== 'undefined') {
      new Chart(ctxSpecies, {
        type: 'bar',
        data: {
          labels: chartSpeciesLabels,
          datasets: [{
            label: 'Jumlah Temuan',
            data: chartSpeciesCounts,
            backgroundColor: [
              '#EF4444', // Biawak Air (Kritis)
              '#F59E0B', // Blekok (Sedang)
              '#00A3B4', // Kuntul (Teal)
              '#38BDF8', // Layang-layang (Sky)
              '#818CF8'  // Mamalia (Indigo)
            ],
            borderRadius: 6,
            barThickness: 14
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#0F172A',
              titleFont: { family: 'DM Sans', size: 11, weight: 'bold' },
              bodyFont: { family: 'DM Sans', size: 11 },
              padding: 8,
              cornerRadius: 8
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(226, 232, 240, 0.7)' },
              ticks: { font: { family: 'DM Sans', size: 10 }, color: '#64748B', precision: 0 }
            },
            y: {
              grid: { display: false },
              ticks: { font: { family: 'DM Sans', size: 10, weight: '600' }, color: '#1E293B' }
            }
          }
        }
      });
    }

    // 4. CHART 4: DISTRIBUSI JAM AKTIVITAS (VERTICAL BAR)
    const ctxHourly = document.getElementById('hourlyBarChart');
    if (ctxHourly && typeof Chart !== 'undefined') {
      new Chart(ctxHourly, {
        type: 'bar',
        data: {
          labels: chartHourlyLabels,
          datasets: [{
            label: 'Frekuensi Muncul',
            data: chartHourlyCounts,
            backgroundColor: [
              '#00A3B4', // Pagi (Peak 1)
              '#CBD5E1', // Siang (Low)
              '#F59E0B', // Sore (Peak 2)
              '#64748B'  // Malam (Nocturnal)
            ],
            borderRadius: 6,
            barThickness: 24
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#0F172A',
              titleFont: { family: 'DM Sans', size: 11, weight: 'bold' },
              bodyFont: { family: 'DM Sans', size: 11 },
              padding: 8,
              cornerRadius: 8
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { font: { family: 'DM Sans', size: 10, weight: '600' }, color: '#334155' }
            },
            y: {
              grid: { color: 'rgba(226, 232, 240, 0.7)' },
              ticks: { font: { family: 'DM Sans', size: 10 }, color: '#64748B', precision: 0 }
            }
          }
        }
      });
    }

    // 5. CHART 5: SEBARAN ZONA OPERASIONAL (POLAR AREA)
    const ctxZone = document.getElementById('zoneRadarChart');
    if (ctxZone && typeof Chart !== 'undefined') {
      new Chart(ctxZone, {
        type: 'polarArea',
        data: {
          labels: chartZoneLabels,
          datasets: [{
            data: chartZoneCounts,
            backgroundColor: [
              'rgba(239, 68, 68, 0.75)',  // Runway 07L/25R
              'rgba(245, 158, 11, 0.75)', // Kanal & Rawa
              'rgba(0, 163, 180, 0.75)',  // Taxiway
              'rgba(56, 189, 248, 0.75)', // Apron
              'rgba(99, 102, 241, 0.75)'  // Perimeter
            ],
            borderWidth: 1.5,
            borderColor: '#FFFFFF'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'right',
              labels: {
                boxWidth: 9,
                font: { family: 'DM Sans', size: 9 },
                color: '#475569',
                padding: 4
              }
            },
            tooltip: {
              backgroundColor: '#0F172A',
              titleFont: { family: 'DM Sans', size: 11, weight: 'bold' },
              bodyFont: { family: 'DM Sans', size: 11 },
              padding: 8,
              cornerRadius: 8
            }
          },
          scales: {
            r: {
              ticks: { display: false },
              grid: { color: 'rgba(226, 232, 240, 0.7)' }
            }
          }
        }
      });
    }
  });
</script>
