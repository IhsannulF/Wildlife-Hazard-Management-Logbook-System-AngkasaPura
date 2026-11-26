<!-- RAW DATA STORE (JSON) -->
<script id="rawGridDataJson" type="application/json">@json($gridAgg)</script>

<!-- CLIENT-SIDE SCRIPT FOR INTERACTIVE MAP & DETAILS -->
<script>
  // Grid Data Aggregation Dictionary
  let rawGridData = JSON.parse(document.getElementById('rawGridDataJson').textContent);
  
  // Normalization dictionary for instant O(1) lookup
  const gridData = {};
  const rowLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
  const X0 = 41.0;
  const Y0 = 121.0;
  const CW = 941.0 / 29.0;
  const RH = 395.0 / 12.0;

  function rebuildGridData(newRaw) {
    rawGridData = newRaw;
    Object.keys(gridData).forEach(k => delete gridData[k]);
    Object.keys(rawGridData).forEach(k => {
      gridData[k] = rawGridData[k];
      const clean = k.replace(/[-_ =]/g, '').toUpperCase();
      gridData[clean] = rawGridData[k];
      const m = clean.match(/^([A-L])(\d+)$/);
      if (m) {
        gridData[`${m[1]}-${parseInt(m[2], 10)}`] = rawGridData[k];
        gridData[`${m[1]}${parseInt(m[2], 10)}`] = rawGridData[k];
      }
    });
  }

  rebuildGridData(rawGridData);

  // Zoom and pan state
  let currentZoom = 1.0;
  let panX = 0;
  let panY = 0;
  let isPanning = false;
  let startX = 0;
  let startY = 0;
  let activeSelectedCode = '{{ $selectedGrid['grid'] ?? 'K-10' }}';

  function setMapMode(mode) {
    const gisWrapper = document.getElementById('gisMapLayerWrapper');
    const tacLayer = document.getElementById('mapTacticalLayer');
    const togglesBar = document.getElementById('gisLayerToggles');

    const btnGis = document.getElementById('btnModeGis');
    const btnTac = document.getElementById('btnModeTactical');

    const activeCls = 'text-[11px] px-2.5 py-1 rounded-md bg-injourney-teal text-white font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer';
    const inactiveCls = 'text-[11px] px-2.5 py-1 rounded-md bg-surface-container-low text-secondary hover:text-slate-navy hover:bg-surface-container font-medium flex items-center gap-1 transition-all cursor-pointer';

    if (btnGis) btnGis.className = (mode === 'gis') ? activeCls : inactiveCls;
    if (btnTac) btnTac.className = (mode === 'tactical') ? activeCls : inactiveCls;

    if (mode === 'gis') {
      if (gisWrapper) gisWrapper.classList.remove('hidden');
      if (tacLayer) tacLayer.classList.add('hidden');
      if (togglesBar) togglesBar.classList.remove('hidden');
    } else {
      if (gisWrapper) gisWrapper.classList.add('hidden');
      if (tacLayer) tacLayer.classList.remove('hidden');
      if (togglesBar) togglesBar.classList.add('hidden');
    }
    resetMap();
  }

  function toggleGisLayer(layerId, isVisible) {
    const el = document.getElementById(layerId);
    if (el) {
      el.style.display = isVisible ? '' : 'none';
    }
  }

  function setOverlayOpacity(val) {
    const group = document.getElementById('mainOverlayGroup');
    const text = document.getElementById('opacityValText');
    if (group) group.style.opacity = (val / 100);
    if (text) text.innerText = val + '%';
  }

  function applyTransform() {
    const canvas = document.getElementById('gisCanvasInner');
    if (canvas) {
      canvas.style.transform = `scale(${currentZoom}) translate(${panX}px, ${panY}px)`;
    }
  }

  function zoomMap(delta) {
    currentZoom = Math.min(Math.max(0.75, currentZoom + delta), 2.2);
    applyTransform();
  }

  function resetMap() {
    currentZoom = 1.0;
    panX = 0;
    panY = 0;
    applyTransform();
  }

  // Pan interaction
  const mapWrapper = document.getElementById('gisMapLayerWrapper');
  if (mapWrapper) {
    mapWrapper.addEventListener('mousedown', (e) => {
      if (e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'button') return;
      isPanning = true;
      startX = e.clientX - panX;
      startY = e.clientY - panY;
    });

    window.addEventListener('mousemove', (e) => {
      if (!isPanning || currentZoom <= 1.0) return;
      panX = e.clientX - startX;
      panY = e.clientY - startY;
      applyTransform();
    });

    window.addEventListener('mouseup', () => {
      isPanning = false;
    });
  }

  // Real-Time HUD Hover Handler
  function onHoverGrid(code, row, col) {
    const clean = code.replace(/[-_ =]/g, '').toUpperCase();
    const compact = row + col;
    const data = gridData[code] || gridData[compact];

    const hudCoord = document.getElementById('gisHudCoord');
    const hudInfo  = document.getElementById('gisHudInfo');

    if (hudCoord) hudCoord.innerText = `KOORDINAT: ${compact} (${row}-${col})`;
    if (hudInfo) {
      if (data && data.count > 0) {
        if (data.bird_stats && data.bird_stats.total_birds > 0) {
          hudInfo.innerText = `${data.zone_name} • ${data.count} Lap. • ${data.bird_stats.total_birds} Ekor Burung (${data.bird_stats.dominant_bird})`;
        } else {
          hudInfo.innerText = `${data.zone_name} • ${data.count} Laporan (${data.dominant_species})`;
        }
      } else {
        hudInfo.innerText = `Titik Grid Steril Sisi Udara • Sumbu Horisontal ${col}, Sumbu Vertikal ${row}`;
      }
    }

    // Highlight hit cell
    const hitEl = document.getElementById(`hitCell_${row}_${col}`);
    if (hitEl) {
      hitEl.setAttribute('stroke', '#00A3B4');
      hitEl.setAttribute('stroke-width', '1.5');
      hitEl.setAttribute('fill', 'rgba(0, 163, 180, 0.15)');
    }
  }

  function onLeaveGrid() {
    // Clear all hover fills on hit cells
    document.querySelectorAll('.grid-hit-cell').forEach(el => {
      el.setAttribute('stroke', 'transparent');
      el.setAttribute('fill', 'transparent');
    });

    // Restore HUD to active selected grid
    selectGrid(activeSelectedCode, false);
  }

  // Dynamic Grid Selection on Click
  function selectGrid(code, updateActive = true) {
    if (updateActive) {
      activeSelectedCode = code;
    }

    const clean = code.replace(/[-_ =]/g, '').toUpperCase();
    const m = clean.match(/^([A-L])(\d+)$/);
    let rowChar = 'D';
    let colNum  = 9;
    let compactCode = code;
    let standardCode = code;

    if (m) {
      rowChar = m[1];
      colNum  = parseInt(m[2], 10);
      compactCode = rowChar + colNum;
      standardCode = rowChar + '-' + colNum;
    }

    const data = gridData[standardCode] || gridData[compactCode] || {
      grid: standardCode,
      zone_name: 'Zona ' + compactCode + ' (Area Sisi Udara)',
      desc: 'Area sisi udara steril tanpa laporan satwa liar dalam periode pengamatan terpilih.',
      count: 0,
      dominant_species: 'Tidak Ada',
      status: 'Steril Aman',
      risk_category: 'Steril / Terkendali',
      peak_hours: 'Kondisi steril terpantau normal 24 jam.',
      mitigation_sop: [
        'Pertahankan inspeksi patroli berkala regu AMC.',
        'Pemotongan rumput berkala sesuai batas standar ICAO 35m.',
        'Pastikan tidak ada genangan air terbuka.'
      ],
      bird_stats: {
        total_birds: 0,
        bird_reports_count: 0,
        dominant_bird: 'Tidak Ada',
        species_breakdown: [],
        recent_reports: []
      }
    };

    // Position Active Selection Box & Center Reticle Point in SVG
    const rIdx = rowLetters.indexOf(rowChar);
    const cIdx = colNum - 1;
    if (rIdx >= 0 && cIdx >= 0 && cIdx < 29) {
      const boxX = (X0 + cIdx * CW).toFixed(2);
      const boxY = (Y0 + rIdx * RH).toFixed(2);
      const boxW = CW.toFixed(2);
      const boxH = RH.toFixed(2);
      const centerX = (X0 + (cIdx + 0.5) * CW).toFixed(2);
      const centerY = (Y0 + (rIdx + 0.5) * RH).toFixed(2);

      const selBox = document.getElementById('activeSelectionBox');
      if (selBox) {
        selBox.setAttribute('x', boxX);
        selBox.setAttribute('y', boxY);
        selBox.setAttribute('width', boxW);
        selBox.setAttribute('height', boxH);
        selBox.classList.remove('hidden');
      }

      const selPulse = document.getElementById('activeSelectionPulse');
      if (selPulse) {
        selPulse.setAttribute('cx', centerX);
        selPulse.setAttribute('cy', centerY);
        selPulse.classList.remove('hidden');
      }
    }

    // Update HUD
    const hudCoord = document.getElementById('gisHudCoord');
    const hudInfo  = document.getElementById('gisHudInfo');
    if (hudCoord) hudCoord.innerText = `TERPILIH: ${compactCode} (${standardCode})`;
    if (hudInfo) {
      if (data.bird_stats && data.bird_stats.total_birds > 0) {
        hudInfo.innerText = `${data.zone_name} • ${data.count} Lap. • ${data.bird_stats.total_birds} Ekor Burung (${data.bird_stats.dominant_bird})`;
      } else {
        hudInfo.innerText = `${data.zone_name} • ${data.count} Laporan (${data.dominant_species})`;
      }
    }

    // Update Sidebar Card Details
    const badgeEl = document.getElementById('detailGridBadge');
    if (badgeEl) badgeEl.innerText = 'GRID ' + compactCode;
    
    const riskEl = document.getElementById('detailRiskBadge');
    if (riskEl) riskEl.innerText = data.risk_category || 'Kategori Terkendali';
    
    const zoneEl = document.getElementById('detailZoneName');
    if (zoneEl) zoneEl.innerText = data.zone_name || ('Zona ' + compactCode);
    
    const descEl = document.getElementById('detailDesc');
    if (descEl) descEl.innerText = data.desc || 'Area terpantau aman.';
    
    const countEl = document.getElementById('detailCount');
    if (countEl) countEl.innerText = (data.count || 0) + ' Kejadian';
    
    const speciesEl = document.getElementById('detailSpecies');
    if (speciesEl) speciesEl.innerText = data.dominant_species || 'Tidak Ada';
    
    const statusEl = document.getElementById('detailStatus');
    if (statusEl) statusEl.innerText = data.status || 'Telah Ditangani';

    // Update Dynamic Bird Statistics Section (Left Sub-Panel)
    const bStats = data.bird_stats || { total_birds: 0, bird_reports_count: 0, dominant_bird: 'Tidak Ada', species_breakdown: [], recent_reports: [] };
    const birdBadge = document.getElementById('detailBirdTotalBadge');
    if (birdBadge) {
      birdBadge.innerText = (bStats.total_birds || 0) + ' Ekor Terkumpul';
    }

    const birdEmptyState = document.getElementById('detailBirdEmptyState');
    const birdList = document.getElementById('detailBirdList');
    const birdBarsContainer = document.getElementById('detailBirdSpeciesBars');

    if (bStats.species_breakdown && bStats.species_breakdown.length > 0) {
      if (birdEmptyState) birdEmptyState.classList.add('hidden');
      if (birdList) birdList.classList.remove('hidden');

      if (birdBarsContainer) {
        let barsHtml = '';
        bStats.species_breakdown.forEach((item, idx) => {
          barsHtml += `
            <div class="bg-white/80 p-2 rounded-lg border border-cyan-100">
              <div class="flex items-center justify-between text-[11px] mb-1">
                <span class="font-bold text-slate-800 flex items-center gap-1">
                  <span class="w-3.5 h-3.5 rounded-full bg-cyan-100 text-[#007fa3] text-[9px] font-black inline-flex items-center justify-center">
                    ${idx + 1}
                  </span>
                  ${item.species}
                </span>
                <span class="font-code-coordinate font-bold text-slate-700">${item.count} Ekor (${item.percentage}%)</span>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-gradient-to-r from-[#00A9C1] to-blue-500 h-1.5 rounded-full transition-all duration-300" style="width: ${item.percentage}%"></div>
              </div>
            </div>
          `;
        });
        birdBarsContainer.innerHTML = barsHtml;
      }
    } else {
      if (birdEmptyState) birdEmptyState.classList.remove('hidden');
      if (birdList) birdList.classList.add('hidden');
    }

    // Update Riwayat Laporan Berita Acara (BA) Burung Terkini (Right Sub-Panel)
    const birdReportsContainer = document.getElementById('detailBirdReportsList');
    if (birdReportsContainer) {
      if (bStats.recent_reports && bStats.recent_reports.length > 0) {
        let repHtml = '';
        bStats.recent_reports.forEach(r => {
          repHtml += `
            <div class="text-[11px] bg-white p-2 rounded-lg border border-cyan-100 flex items-center justify-between shadow-2xs hover:border-cyan-300 transition-colors">
              <div class="truncate mr-2">
                <div class="flex items-center gap-1.5">
                  <span class="font-code-coordinate font-bold text-slate-800">${r.nomor_ba}</span>
                  <span class="text-secondary text-[10px]">• ${r.tanggal} (${r.jam})</span>
                </div>
                <div class="text-slate-600 truncate text-[10px] mt-0.5">${r.spesies} — ${r.jumlah} Ekor • ${r.tindak_lanjut || 'Pengusiran Sirene'}</div>
              </div>
              <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 whitespace-nowrap">${r.status || 'Telah Ditangani'}</span>
            </div>
          `;
        });
        birdReportsContainer.innerHTML = repHtml;
      } else {
        birdReportsContainer.innerHTML = `
          <div class="py-3 px-2 text-center text-secondary text-[11px] bg-white/60 rounded-lg border border-dashed border-slate-200">
            Tidak ada catatan riwayat BA pada grid ini.
          </div>
        `;
      }
    }

    // Update View Log Link
    const btnViewLog = document.getElementById('btnViewLog');
    if (btnViewLog) {
      btnViewLog.href = "{{ route('admin.dashboard') }}?search=" + encodeURIComponent(standardCode);
    }

    // Smooth highlight effect on sidebar card
    const card = document.getElementById('detailCard');
    if (card && updateActive) {
      card.classList.add('ring-4', 'ring-injourney-teal/30');
      setTimeout(() => {
        card.classList.remove('ring-4', 'ring-injourney-teal/30');
      }, 500);
    }
  }

  // Switcher Tab untuk Top 5 Hotspot vs Top 5 Burung Terbanyak
  function switchRankingTab(tabName) {
    const tabHotspot = document.getElementById('tabContentHotspot');
    const tabBurung = document.getElementById('tabContentBurung');
    const btnHotspot = document.getElementById('btnTabHotspot');
    const btnBurung = document.getElementById('btnTabBurung');

    if (tabName === 'burung') {
      if (tabHotspot) tabHotspot.classList.add('hidden');
      if (tabBurung) tabBurung.classList.remove('hidden');
      if (btnBurung) {
        btnBurung.className = 'text-[11px] px-2.5 py-1 rounded-md bg-injourney-teal text-white font-bold transition-all cursor-pointer shadow-2xs flex items-center gap-1';
      }
      if (btnHotspot) {
        btnHotspot.className = 'text-[11px] px-2.5 py-1 rounded-md text-secondary hover:text-slate-navy font-semibold transition-all cursor-pointer';
      }
    } else {
      if (tabBurung) tabBurung.classList.add('hidden');
      if (tabHotspot) tabHotspot.classList.remove('hidden');
      if (btnHotspot) {
        btnHotspot.className = 'text-[11px] px-2.5 py-1 rounded-md bg-injourney-teal text-white font-bold transition-all cursor-pointer shadow-2xs';
      }
      if (btnBurung) {
        btnBurung.className = 'text-[11px] px-2.5 py-1 rounded-md text-secondary hover:text-slate-navy font-semibold transition-all cursor-pointer flex items-center gap-1';
      }
    }
  }

  let currentAuditPage = 1;
  const AUDIT_PAGE_SIZE = 10;

  function renderAuditTablePagination() {
    const searchInput = document.getElementById('gridTableSearch');
    const filterQuery = (searchInput ? searchInput.value : '').toLowerCase().trim();
    const tbody = document.getElementById('tblGridAuditBody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr.audit-row'));
    if (rows.length === 0) {
      const pagBar = document.getElementById('tablePaginationBar');
      if (pagBar) pagBar.style.display = 'none';
      return;
    }

    // Filter rows based on search input
    const matchedRows = rows.filter(r => {
      if (!filterQuery) return true;
      const text = r.innerText.toLowerCase();
      return text.includes(filterQuery);
    });

    const totalMatches = matchedRows.length;
    const totalPages = Math.max(1, Math.ceil(totalMatches / AUDIT_PAGE_SIZE));

    if (currentAuditPage > totalPages) {
      currentAuditPage = totalPages;
    }
    if (currentAuditPage < 1) {
      currentAuditPage = 1;
    }

    const startIdx = (currentAuditPage - 1) * AUDIT_PAGE_SIZE;
    const endIdx = Math.min(startIdx + AUDIT_PAGE_SIZE, totalMatches);

    // Hide all rows first
    rows.forEach(r => r.style.display = 'none');

    // Show only rows in current page
    for (let i = startIdx; i < endIdx; i++) {
      matchedRows[i].style.display = '';
    }

    // Handle empty search result message
    let emptyRow = document.getElementById('auditEmptySearchRow');
    if (totalMatches === 0) {
      if (!emptyRow) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'auditEmptySearchRow';
        emptyRow.innerHTML = '<td colspan="8" class="text-center py-6 text-secondary">Tidak ada data grid yang cocok dengan pencarian.</td>';
        tbody.appendChild(emptyRow);
      }
      emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }

    // Update counters
    const startEl = document.getElementById('auditPageStart');
    const endEl = document.getElementById('auditPageEnd');
    const totalEl = document.getElementById('auditTotalRows');
    if (startEl) startEl.innerText = totalMatches > 0 ? (startIdx + 1) : 0;
    if (endEl) endEl.innerText = endIdx;
    if (totalEl) totalEl.innerText = totalMatches;

    // Update buttons
    const btnPrev = document.getElementById('btnAuditPrev');
    const btnNext = document.getElementById('btnAuditNext');
    if (btnPrev) btnPrev.disabled = (currentAuditPage <= 1);
    if (btnNext) btnNext.disabled = (currentAuditPage >= totalPages || totalMatches === 0);

    // Render page numbers
    const pageNumbersContainer = document.getElementById('auditPageNumbers');
    if (pageNumbersContainer) {
      pageNumbersContainer.innerHTML = '';
      if (totalPages > 1) {
        for (let p = 1; p <= totalPages; p++) {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.innerText = p;
          if (p === currentAuditPage) {
            btn.className = 'w-7 h-7 flex items-center justify-center rounded-lg bg-injourney-teal text-white font-bold text-xs shadow-xs';
          } else {
            btn.className = 'w-7 h-7 flex items-center justify-center rounded-lg border border-border-subtle bg-card-bg hover:bg-surface-container-low text-secondary hover:text-slate-navy font-semibold text-xs transition-colors cursor-pointer';
            btn.onclick = () => goToAuditPage(p);
          }
          pageNumbersContainer.appendChild(btn);
        }
      }
    }

    const pagBar = document.getElementById('tablePaginationBar');
    if (pagBar) {
      pagBar.style.display = totalMatches === 0 && !filterQuery ? 'none' : 'flex';
    }
  }

  function changeAuditPage(delta) {
    currentAuditPage += delta;
    renderAuditTablePagination();
  }

  function goToAuditPage(page) {
    currentAuditPage = page;
    renderAuditTablePagination();
  }

  function searchGridTable() {
    currentAuditPage = 1;
    renderAuditTablePagination();
  }

  function triggerDispersalAction() {
    const activeGrid = document.getElementById('detailGridBadge').innerText;
    showToast(`Perintah unit dispersal lapangan diterbitkan untuk ${activeGrid}. Regu jaga AMC bergerak.`);
  }

  function showToast(message) {
    const toast = document.getElementById('gridToast');
    const toastMsg = document.getElementById('gridToastMsg');
    if (!toast || !toastMsg) return;
    toastMsg.innerText = message;
    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(() => {
      toast.classList.add('translate-y-20', 'opacity-0');
    }, 3500);
  }

  // Auto-initialize selection on load
  document.addEventListener('DOMContentLoaded', () => {
    selectGrid(activeSelectedCode, false);
    renderAuditTablePagination();
  });

  // Realtime Filter Handler (Prevents page refresh, maintains exact scroll position)
  async function filterRealtime(url) {
    const scrollPos = window.scrollY; // Preserve exact scroll position
    
    const indicator = document.getElementById('filterLoadingIndicator');
    if (indicator) {
      indicator.classList.remove('hidden');
      indicator.classList.add('inline-flex');
    }

    try {
      const response = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!response.ok) throw new Error('Network response not ok');
      const html = await response.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      // 1. Update KPI Summary Grid
      const newKpi = doc.getElementById('kpiSummaryGrid');
      const currKpi = document.getElementById('kpiSummaryGrid');
      if (newKpi && currKpi) currKpi.innerHTML = newKpi.innerHTML;

      // 2. Update Hotspots in SVG Layer
      const newHotspots = doc.getElementById('layerHotspots');
      const currHotspots = document.getElementById('layerHotspots');
      if (newHotspots && currHotspots) currHotspots.innerHTML = newHotspots.innerHTML;

      // 3. Update Tactical Matrix Layer
      const newTac = doc.getElementById('mapTacticalLayer');
      const currTac = document.getElementById('mapTacticalLayer');
      if (newTac && currTac) currTac.innerHTML = newTac.innerHTML;

      // 4. Update Top 5 Card
      const newTop5 = doc.getElementById('top5Card');
      const currTop5 = document.getElementById('top5Card');
      if (newTop5 && currTop5) currTop5.innerHTML = newTop5.innerHTML;

      // 5. Update Quick Filter Card
      const newFilter = doc.getElementById('quickFilterCard');
      const currFilter = document.getElementById('quickFilterCard');
      if (newFilter && currFilter) currFilter.innerHTML = newFilter.innerHTML;

      // 6. Update Time Filter Pills
      const newTime = doc.getElementById('timeFilterPills');
      const currTime = document.getElementById('timeFilterPills');
      if (newTime && currTime) currTime.innerHTML = newTime.innerHTML;

      // 7. Update Table
      const newTable = doc.getElementById('tblGridAuditContainer');
      const currTable = document.getElementById('tblGridAuditContainer');
      if (newTable && currTable) {
        currTable.innerHTML = newTable.innerHTML;
        currentAuditPage = 1;
        renderAuditTablePagination();
      }

      // 8. Update gridData dictionary from new JSON store
      const newJsonEl = doc.getElementById('rawGridDataJson');
      if (newJsonEl) {
        try {
          const parsed = JSON.parse(newJsonEl.textContent);
          rebuildGridData(parsed);
        } catch(e) {
          console.error('Failed parsing new grid JSON', e);
        }
      }

      // 9. Update Active Selected Grid Card
      if (gridData[activeSelectedCode]) {
        selectGrid(activeSelectedCode, false);
      } else {
        const firstKey = Object.keys(gridData)[0] || 'K-10';
        selectGrid(firstKey, true);
      }

      // 10. Push URL state without page reload
      history.pushState(null, '', url);

    } catch (err) {
      console.error('Error applying realtime filter:', err);
      window.location.href = url;
    } finally {
      const ind = document.getElementById('filterLoadingIndicator');
      if (ind) {
        ind.classList.remove('inline-flex');
        ind.classList.add('hidden');
      }
      // Force scroll position preservation immediately and in next frame
      window.scrollTo({ top: scrollPos, behavior: 'instant' });
      requestAnimationFrame(() => {
        window.scrollTo({ top: scrollPos, behavior: 'instant' });
      });
    }
  }

  // Intercept clicks on quick filter links & time filter links
  document.addEventListener('click', function(e) {
    const link = e.target.closest('.quick-filter-link, .time-filter-link');
    if (link && link.href) {
      e.preventDefault();
      filterRealtime(link.href);
    }
  });

  // Browser navigation (Back / Forward)
  window.addEventListener('popstate', function() {
    filterRealtime(window.location.href);
  });

  // ================= PILAR 4: LAYER PREDIKSI AI (GEMINI) =================
  let cachedAiPrediction = null;

  async function toggleAiPredictionLayer(checked) {
    const aiGroup = document.getElementById('layerAiPrediksi');
    if (!aiGroup) return;

    if (!checked) {
      aiGroup.style.display = 'none';
      return;
    }

    aiGroup.style.display = 'block';

    if (!cachedAiPrediction) {
      try {
        const resp = await fetch("{{ route('admin.ai.prediksi.latest') }}", {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await resp.json();
        if (json.success && json.prediksi) {
          cachedAiPrediction = json.prediksi;
        }
      } catch (e) {
        console.error('Gagal mengambil data prediksi AI:', e);
      }
    }

    if (!cachedAiPrediction || !cachedAiPrediction.grid_kritis) {
      alert('Belum ada data prediksi AI aktif untuk ditampilkan pada peta.');
      document.getElementById('toggleLayerAiPrediksi').checked = false;
      aiGroup.style.display = 'none';
      return;
    }

    renderAiPredictionOnMap(cachedAiPrediction);
  }

  function renderAiPredictionOnMap(prediksi) {
    const aiGroup = document.getElementById('layerAiPrediksi');
    if (!aiGroup) return;
    aiGroup.innerHTML = '';

    const colorMap = {
      'kritis': { stroke: '#ef4444', fill: 'rgba(239, 68, 68, 0.45)', text: '#ffffff' },
      'tinggi': { stroke: '#f59e0b', fill: 'rgba(245, 158, 11, 0.45)', text: '#ffffff' },
      'sedang': { stroke: '#00A9C1', fill: 'rgba(0, 169, 193, 0.40)', text: '#ffffff' },
      'rendah': { stroke: '#10b981', fill: 'rgba(16, 185, 129, 0.35)', text: '#ffffff' }
    };
    const c = colorMap[prediksi.level_bahaya] || colorMap['tinggi'];

    const grids = prediksi.grid_kritis || [];
    grids.forEach(grid => {
      const parts = grid.split('-');
      if (parts.length !== 2) return;
      const rowChar = parts[0].toUpperCase();
      const colNum = parseInt(parts[1], 10);

      const rIndex = rowLetters.indexOf(rowChar);
      if (rIndex === -1 || colNum < 1 || colNum > 29) return;

      const x = X0 + (colNum - 1) * CW;
      const y = Y0 + rIndex * RH;

      // Create SVG Rect for AI zone
      const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
      rect.setAttribute('x', x.toFixed(2));
      rect.setAttribute('y', y.toFixed(2));
      rect.setAttribute('width', CW.toFixed(2));
      rect.setAttribute('height', RH.toFixed(2));
      rect.setAttribute('fill', c.fill);
      rect.setAttribute('stroke', c.stroke);
      rect.setAttribute('stroke-width', '2.5');
      rect.setAttribute('stroke-dasharray', '4,2');
      rect.setAttribute('rx', '2');
      rect.setAttribute('class', 'grid-cell-pulse');

      const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
      title.textContent = `[PREDIKSI AI: ${prediksi.level_bahaya.toUpperCase()}] Grid ${grid}\nJam Rawan: ${prediksi.jam_rawan_mulai || ''} - ${prediksi.jam_rawan_selesai || ''} WIB\nProbabilitas: ${Math.round(prediksi.skor_probabilitas * 100)}%`;
      rect.appendChild(title);

      aiGroup.appendChild(rect);

      // Label text AI
      const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
      text.setAttribute('x', (x + CW / 2).toFixed(2));
      text.setAttribute('y', (y + RH / 2 + 3).toFixed(2));
      text.setAttribute('font-size', '7');
      text.setAttribute('font-weight', '900');
      text.setAttribute('fill', '#FFFFFF');
      text.setAttribute('text-anchor', 'middle');
      text.setAttribute('font-family', "'DM Sans', monospace");
      text.textContent = '★ AI';
      aiGroup.appendChild(text);
    });
  }
</script>
