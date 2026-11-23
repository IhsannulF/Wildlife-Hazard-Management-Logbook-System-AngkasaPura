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
        hudInfo.innerText = `${data.zone_name} • ${data.count} Laporan (${data.dominant_species})`;
      } else {
        hudInfo.innerText = `Area Steril Sisi Udara • Sumbu Horisontal ${col}, Sumbu Vertikal ${row}`;
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
    let rowChar = 'K';
    let colNum  = 10;
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
      ]
    };

    // Position Active Selection Box Reticle in SVG
    const rIdx = rowLetters.indexOf(rowChar);
    const cIdx = colNum - 1;
    if (rIdx >= 0 && cIdx >= 0 && cIdx < 29) {
      const boxX = (X0 + cIdx * CW).toFixed(2);
      const boxY = (Y0 + rIdx * RH).toFixed(2);
      const boxW = CW.toFixed(2);
      const boxH = RH.toFixed(2);

      const selBox = document.getElementById('activeSelectionBox');
      if (selBox) {
        selBox.setAttribute('x', boxX);
        selBox.setAttribute('y', boxY);
        selBox.setAttribute('width', boxW);
        selBox.setAttribute('height', boxH);
        selBox.classList.remove('hidden');
      }
    }

    // Update HUD
    const hudCoord = document.getElementById('gisHudCoord');
    const hudInfo  = document.getElementById('gisHudInfo');
    if (hudCoord) hudCoord.innerText = `TERPILIH: ${compactCode} (${standardCode})`;
    if (hudInfo) hudInfo.innerText = `${data.zone_name} • ${data.count} Laporan (${data.dominant_species})`;

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
    
    const hoursEl = document.getElementById('detailHours');
    if (hoursEl) hoursEl.innerText = data.peak_hours || 'Terpantau normal.';

    // Update SOP list
    const sopContainer = document.getElementById('detailSopList');
    if (sopContainer) {
      sopContainer.innerHTML = '';
      const sops = data.mitigation_sop || ['Inspeksi patroli berkala regu AMC.'];
      sops.forEach(s => {
        const li = document.createElement('li');
        li.innerText = s;
        sopContainer.appendChild(li);
      });
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
</script>
