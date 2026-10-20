<script>
  let activeModalLaporanId = null;

  // Tab switching in Two-Page Verification Modal
  function switchModalPage(pageNumber) {
    const page1 = document.getElementById('modalPage1');
    const page2 = document.getElementById('modalPage2');
    const btn1 = document.getElementById('tabBtnPage1');
    const btn2 = document.getElementById('tabBtnPage2');

    if (pageNumber === 1) {
      page1.classList.remove('hidden');
      page2.classList.add('hidden');
      btn1.className = 'px-3 py-1 rounded text-xs font-label-md font-bold bg-white text-injourney-teal shadow-xs cursor-pointer';
      btn2.className = 'px-3 py-1 rounded text-xs font-label-md text-secondary hover:text-on-surface cursor-pointer';
    } else {
      page1.classList.add('hidden');
      page2.classList.remove('hidden');
      btn2.className = 'px-3 py-1 rounded text-xs font-label-md font-bold bg-white text-injourney-teal shadow-xs cursor-pointer';
      btn1.className = 'px-3 py-1 rounded text-xs font-label-md text-secondary hover:text-on-surface cursor-pointer';
      setTimeout(initSignaturePad, 60);
    }
  }

  // Modal Control Functions
  function openReportDetailAjax(id) {
    activeModalLaporanId = id;
    
    // Update print button link
    const printBtn = document.getElementById('modalPrintBtn');
    if (printBtn) {
      printBtn.href = `/admin/laporan/${id}/cetak`;
    }

    // Fetch dynamic details
    fetch(`/admin/laporan/${id}/detail`)
      .then(res => res.json())
      .then(data => {
        const regId = data.no_laporan || ('WHM-' + (data.tanggal ? data.tanggal.substring(0, 4) : '2026') + '-' + String(data.id).padStart(4, '0'));
        document.getElementById('modalReportId').innerText = regId;
        document.getElementById('modalOfficer').innerText = (data.nama_petugas || data.nama_user) + ' (' + (data.unit_kerja || 'AMC') + ')';
        document.getElementById('modalTimestamp').innerText = (data.tanggal || '-') + ', ' + (data.created_at ? data.created_at.substring(11, 16) + ' WIB' : '08:00 WIB');
        document.getElementById('modalWeather').innerText = data.kondisi_cuaca ? (data.kondisi_cuaca + ' / Angin 8 kts WSW') : 'Berawan 29°C';
        
        let speciesStr = 'Satwa Liar';
        let qtyStr = '1 Ekor';
        let gridStr = data.grid_lokasi || 'K-10';

        if (data.satwa && data.satwa.length > 0) {
          speciesStr = data.satwa[0].nama || speciesStr;
          qtyStr = (data.satwa[0].jumlah || 1) + ' Ekor';
          if (data.satwa[0].grid) gridStr = data.satwa[0].grid;
          if (data.satwa[0].foto_path) {
            document.getElementById('modalPhotoImg').src = data.satwa[0].foto_path;
          }
        }

        document.getElementById('modalSpecies').innerText = speciesStr;
        document.getElementById('modalQuantity').innerText = qtyStr + (data.ciri_ukuran ? ` (${data.ciri_ukuran})` : '');
        document.getElementById('modalLocation').innerText = (data.area_inspeksi || 'Runway 07L Bahu') + ' [' + gridStr + ']';
        document.getElementById('modalGridText').innerText = 'GRID ' + gridStr;
        document.getElementById('schematicGridTarget').innerText = gridStr + ' (' + (data.area_inspeksi || 'Bahu Landasan') + ')';

        // Update mini grid schematic highlight
        updateMiniGridSchematic(gridStr);

        // Update Status Badge
        const badge = document.getElementById('modalStatusBadge');
        const resolveBtn = document.getElementById('modalResolveBtn');
        if (data.status === 'sudah') {
          badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200';
          badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-resolved-emerald"></span> Sudah Ditangani';
          resolveBtn.classList.add('hidden');
        } else {
          badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200';
          badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-alert-amber"></span> Belum Ditangani';
          resolveBtn.classList.remove('hidden');
        }

        if (data.detail_pengusiran) {
          document.getElementById('modalDispersalDesc').innerText = data.detail_pengusiran;
        }

        switchModalPage(1);
        document.getElementById('detailModal').classList.remove('hidden');
      })
      .catch(err => {
        console.error(err);
        showToast('Gagal memuat rincian laporan.', 'error');
      });
  }

  function updateMiniGridSchematic(gridStr) {
    const cells = document.querySelectorAll('.grid-cell');
    const cleanGrid = gridStr.trim().toUpperCase();
    cells.forEach(cell => {
      const cellGrid = cell.getAttribute('data-grid');
      if (cellGrid === cleanGrid) {
        cell.className = 'py-2 bg-warning-rose text-white rounded font-bold shadow-lg ring-2 ring-white/50 animate-pulse grid-cell';
        cell.innerText = cellGrid + ' ★';
      } else {
        cell.className = 'py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell';
        cell.innerText = cellGrid;
      }
    });
  }

  function closeReportModal() {
    const modal = document.getElementById('detailModal');
    if (modal) modal.classList.add('hidden');
  }

  // Interactive HTML5 Signature Canvas
  let canvasInitialized = false;
  let isDrawing = false;
  let sigCanvas, sigCtx;

  function initSignaturePad() {
    sigCanvas = document.getElementById('signaturePad');
    if (!sigCanvas) return;
    
    sigCtx = sigCanvas.getContext('2d');
    sigCanvas.width = sigCanvas.offsetWidth;
    sigCanvas.height = sigCanvas.offsetHeight;
    sigCtx.lineWidth = 2.5;
    sigCtx.lineCap = 'round';
    sigCtx.strokeStyle = '#006874';

    function getPos(e) {
      const rect = sigCanvas.getBoundingClientRect();
      return {
        x: (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left,
        y: (e.clientY || (e.touches && e.touches[0].clientY)) - rect.top
      };
    }

    sigCanvas.onmousedown = (e) => {
      isDrawing = true;
      const p = getPos(e);
      sigCtx.beginPath();
      sigCtx.moveTo(p.x, p.y);
    };

    sigCanvas.onmousemove = (e) => {
      if (!isDrawing) return;
      const p = getPos(e);
      sigCtx.lineTo(p.x, p.y);
      sigCtx.stroke();
    };

    window.onmouseup = () => { isDrawing = false; };

    sigCanvas.ontouchstart = (e) => {
      e.preventDefault();
      isDrawing = true;
      const p = getPos(e);
      sigCtx.beginPath();
      sigCtx.moveTo(p.x, p.y);
    };

    sigCanvas.ontouchmove = (e) => {
      if (!isDrawing) return;
      e.preventDefault();
      const p = getPos(e);
      sigCtx.lineTo(p.x, p.y);
      sigCtx.stroke();
    };

    sigCanvas.ontouchend = () => { isDrawing = false; };
    canvasInitialized = true;
  }

  function clearSignature() {
    if (!sigCanvas || !sigCtx) return;
    sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
    showToast('Canvas tanda tangan telah dibersihkan.', 'info');
  }

  // Validate & Resolve Action via AJAX
  function validateAndResolve() {
    if (!activeModalLaporanId) return;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new FormData();
    formData.append('status_validasi', 'sudah');

    fetch(`/admin/laporan/${activeModalLaporanId}/update`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      closeReportModal();
      showToast('Berita Acara Berhasil Divalidasi & Ditandai Selesai!');

      // Update row in UI table without reload
      const statusCell = document.getElementById(`status-cell-${activeModalLaporanId}`);
      if (statusCell) {
        statusCell.innerHTML = `
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-resolved-emerald"></span>
            Sudah Ditangani
          </span>
        `;
      }

      const resolveBtn = document.getElementById(`btn-resolve-${activeModalLaporanId}`);
      if (resolveBtn) resolveBtn.remove();

      const row = document.getElementById(`row-${activeModalLaporanId}`);
      if (row) row.setAttribute('data-status', 'sudah');
    })
    .catch(err => {
      console.error(err);
      showToast('Gagal memvalidasi laporan.', 'error');
    });
  }

  function quickResolve(id, regId) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new FormData();
    formData.append('status_validasi', 'sudah');

    fetch(`/admin/laporan/${id}/update`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      showToast(`Insiden ${regId} berhasil dimitigasi & divalidasi!`);

      const statusCell = document.getElementById(`status-cell-${id}`);
      if (statusCell) {
        statusCell.innerHTML = `
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-resolved-emerald"></span>
            Sudah Ditangani
          </span>
        `;
      }

      const btn = document.getElementById(`btn-resolve-${id}`);
      if (btn) btn.remove();

      const row = document.getElementById(`row-${id}`);
      if (row) row.setAttribute('data-status', 'sudah');
    })
    .catch(err => {
      console.error(err);
      showToast('Gagal memproses tindakan.', 'error');
    });
  }

  // Search & Filter Utilities
  let currentStatusFilter = 'all';

  function filterStatus(status) {
    currentStatusFilter = status;
    const tabAll = document.getElementById('tab-all');
    const tabPen = document.getElementById('tab-pending');
    const tabRes = document.getElementById('tab-resolved');

    [tabAll, tabPen, tabRes].forEach(t => {
      if (t) t.className = 'px-3.5 py-1.5 rounded-md font-label-lg text-label-lg text-secondary hover:text-on-surface transition-all';
    });

    if (status === 'all' && tabAll) {
      tabAll.className = 'px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all bg-white text-injourney-teal font-bold shadow-sm';
    } else if (status === 'pending' && tabPen) {
      tabPen.className = 'px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all bg-white text-warning-rose font-bold shadow-sm';
    } else if (tabRes) {
      tabRes.className = 'px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all bg-white text-emerald-700 font-bold shadow-sm';
    }

    searchTable();
  }

  function filterByGrid(gridCode) {
    const sInput = document.getElementById('searchInput');
    if (sInput) sInput.value = gridCode;
    searchTable();
    showToast(`Filter grid runway: ${gridCode}`, 'info');
  }

  function resetFilters() {
    @if(!empty($statusFilter) || !empty($search))
      window.location.href = "{{ route('admin.verifikasi') }}";
    @else
      const sInput = document.getElementById('searchInput');
      if (sInput) sInput.value = '';
      const fUnit = document.getElementById('filterUnit');
      if (fUnit) fUnit.value = '';
      const fArea = document.getElementById('filterArea');
      if (fArea) fArea.value = '';
      filterStatus('all');
    @endif
  }

  function searchTable() {
    const searchVal = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase().trim() : '';
    const unitVal = (document.getElementById('filterUnit') ? document.getElementById('filterUnit').value.toLowerCase().trim() : '');
    const areaVal = (document.getElementById('filterArea') ? document.getElementById('filterArea').value.toLowerCase().trim() : '');

    const table = document.getElementById('logbookTable');
    if (!table) return;
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let r of rows) {
      const text = r.innerText.toLowerCase();
      const rStatus = r.getAttribute('data-status');

      let matchStatus = true;
      if (currentStatusFilter === 'pending') {
        matchStatus = (rStatus === 'belum' || text.includes('belum ditangani'));
      } else if (currentStatusFilter === 'resolved') {
        matchStatus = (rStatus === 'sudah' || text.includes('sudah ditangani'));
      }

      let matchSearch = searchVal === '' || text.includes(searchVal);
      let matchUnit = unitVal === '' || text.includes(unitVal);
      let matchArea = areaVal === '' || text.includes(areaVal);

      if (matchStatus && matchSearch && matchUnit && matchArea) {
        r.style.display = '';
      } else {
        r.style.display = 'none';
      }
    }
  }
</script>
