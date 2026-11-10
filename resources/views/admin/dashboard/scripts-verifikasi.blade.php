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
        const officerName = data.nama_petugas || data.nama_user || 'Petugas Sisi Udara';
        const unitName = data.unit_kerja || 'AMC Airside Operations';
        document.getElementById('modalOfficer').innerText = officerName;
        if (document.getElementById('modalUnitKerja')) {
          document.getElementById('modalUnitKerja').innerText = unitName;
        }
        document.getElementById('modalTimestamp').innerText = (data.tanggal || '-') + ', ' + (data.created_at ? data.created_at.substring(11, 16) + ' WIB' : '08:00 WIB');
        document.getElementById('modalWeather').innerText = data.kondisi_cuaca ? (data.kondisi_cuaca + ' / Angin 8 kts WSW') : 'Berawan 29°C / Angin 8 kts WSW';
        
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
        document.getElementById('modalQuantity').innerText = qtyStr;

        // Pop up rincian lengkap catatan lapangan
        if (document.getElementById('modalCiriUkuran')) {
          document.getElementById('modalCiriUkuran').innerText = data.ciri_ukuran || 'Pengamatan kawanan satwa liar aktif di sekitar runway strip dan perimeter Juanda.';
        }

        // Aktivitas satwa
        if (document.getElementById('modalAktivitas')) {
          document.getElementById('modalAktivitas').innerText = data.aktivitas_satwa || data.detail_pengusiran || 'Aktivitas terpantau di kawasan pergerakan sisi udara.';
        }

        // Kondisi Apron / Area
        if (document.getElementById('modalKondisiApron')) {
          document.getElementById('modalKondisiApron').innerText = data.kondisi_apron || 'Kondisi permukaan normal & kering.';
        }

        // Tindak Lanjut
        if (document.getElementById('modalTindakLanjut')) {
          document.getElementById('modalTindakLanjut').innerText = data.tindak_lanjut || data.detail_pengusiran || 'Pengusiran aktif oleh tim gabungan AMC & Bird Hazard Control.';
        }

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

        // Tampilkan tanda tangan asli petugas pelapor
        const sigImg = document.getElementById('modalSignatureImg');
        const noSigText = document.getElementById('modalNoSignature');
        if (data.tanda_tangan && typeof data.tanda_tangan === 'string' && data.tanda_tangan.trim() !== '') {
          if (sigImg) {
            sigImg.src = data.tanda_tangan;
            sigImg.classList.remove('hidden');
          }
          if (noSigText) noSigText.classList.add('hidden');
        } else {
          if (sigImg) sigImg.classList.add('hidden');
          if (noSigText) noSigText.classList.remove('hidden');
        }

        if (document.getElementById('modalSigOfficerName')) {
          document.getElementById('modalSigOfficerName').innerText = officerName;
        }
        if (document.getElementById('modalSigUnitName')) {
          document.getElementById('modalSigUnitName').innerText = unitName;
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

  // Signature Pad Read-Only Handler
  function initSignaturePad() {
    // Read-only signature preview for admin verification
    return;
  }

  function clearSignature() {
    // No-op for admin review mode
    return;
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

      const dispersalCell = document.getElementById(`dispersal-cell-${activeModalLaporanId}`);
      if (dispersalCell) {
        dispersalCell.innerHTML = `
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200">
            <span class="material-symbols-outlined text-xs text-resolved-emerald">check_circle</span>
            Terkendali
          </span>
        `;
      } else {
        const resolveBtn = document.getElementById(`btn-resolve-${activeModalLaporanId}`);
        if (resolveBtn) resolveBtn.remove();
      }

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

      const dispersalCell = document.getElementById(`dispersal-cell-${id}`);
      if (dispersalCell) {
        dispersalCell.innerHTML = `
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200">
            <span class="material-symbols-outlined text-xs text-resolved-emerald">check_circle</span>
            Terkendali
          </span>
        `;
      } else {
        const btn = document.getElementById(`btn-resolve-${id}`);
        if (btn) btn.remove();
      }

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

  function applyDateFilterServer() {
    const sInput = document.getElementById('filterStartDate');
    const eInput = document.getElementById('filterEndDate');
    const searchInput = document.getElementById('searchInput');

    const startVal = sInput ? sInput.value.trim() : '';
    const endVal = eInput ? eInput.value.trim() : '';
    const searchVal = searchInput ? searchInput.value.trim() : '';

    const url = new URL(window.location.href);

    if (startVal) {
      url.searchParams.set('start_date', startVal);
    } else {
      url.searchParams.delete('start_date');
    }

    if (endVal) {
      url.searchParams.set('end_date', endVal);
    } else {
      url.searchParams.delete('end_date');
    }

    if (searchVal) {
      url.searchParams.set('search', searchVal);
    } else {
      url.searchParams.delete('search');
    }

    url.searchParams.delete('page');

    window.location.href = url.toString();
  }

  function clearDateFilter() {
    if (typeof CalendarPicker !== 'undefined') {
      CalendarPicker.clear();
      return;
    }
    const sInput = document.getElementById('filterStartDate');
    const eInput = document.getElementById('filterEndDate');
    if (sInput) sInput.value = '';
    if (eInput) eInput.value = '';

    const btnClearDate = document.getElementById('datePickerClear') || document.getElementById('btnClearDate');
    if (btnClearDate) btnClearDate.classList.add('hidden');

    const url = new URL(window.location.href);
    if (url.searchParams.has('start_date') || url.searchParams.has('end_date') || url.searchParams.has('tanggal')) {
      url.searchParams.delete('start_date');
      url.searchParams.delete('end_date');
      url.searchParams.delete('tanggal');
      url.searchParams.delete('page');
      window.location.href = url.toString();
    } else {
      searchTable();
    }
  }

  function resetFilters() {
    const url = new URL(window.location.href);
    const hasServerFilters = url.searchParams.has('status') || url.searchParams.has('search') || url.searchParams.has('start_date') || url.searchParams.has('end_date');

    if (hasServerFilters) {
      const tab = url.searchParams.get('tab');
      const cleanUrl = new URL(window.location.pathname, window.location.origin);
      if (tab) cleanUrl.searchParams.set('tab', tab);
      window.location.href = cleanUrl.toString();
    } else {
      const sInput = document.getElementById('searchInput');
      if (sInput) sInput.value = '';
      const fUnit = document.getElementById('filterUnit');
      if (fUnit) fUnit.value = '';
      const fArea = document.getElementById('filterArea');
      if (fArea) fArea.value = '';
      if (typeof CalendarPicker !== 'undefined') {
        CalendarPicker.clear();
      } else {
        const sDate = document.getElementById('filterStartDate');
        if (sDate) sDate.value = '';
        const eDate = document.getElementById('filterEndDate');
        if (eDate) eDate.value = '';
      }
      filterStatus('all');
    }
  }

  function searchTable() {
    const searchVal = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase().trim() : '';
    const unitVal = (document.getElementById('filterUnit') ? document.getElementById('filterUnit').value.toLowerCase().trim() : '');
    const areaVal = (document.getElementById('filterArea') ? document.getElementById('filterArea').value.toLowerCase().trim() : '');
    const startDateVal = (document.getElementById('filterStartDate') ? document.getElementById('filterStartDate').value : '');
    const endDateVal = (document.getElementById('filterEndDate') ? document.getElementById('filterEndDate').value : '');

    const btnClearDate = document.getElementById('datePickerClear') || document.getElementById('btnClearDate');
    if (btnClearDate) {
      if (startDateVal || endDateVal) {
        btnClearDate.classList.remove('hidden');
      } else {
        btnClearDate.classList.add('hidden');
      }
    }

    const table = document.getElementById('logbookTable');
    if (!table) return;
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    let visibleCount = 0;

    for (let r of rows) {
      if (r.id === 'noResultsRow') continue;

      const text = r.innerText.toLowerCase();
      const rStatus = r.getAttribute('data-status');
      const rDate = r.getAttribute('data-date') || '';

      let matchStatus = true;
      if (currentStatusFilter === 'pending') {
        matchStatus = (rStatus === 'belum' || text.includes('belum ditangani'));
      } else if (currentStatusFilter === 'resolved') {
        matchStatus = (rStatus === 'sudah' || text.includes('sudah ditangani'));
      }

      let matchSearch = searchVal === '' || text.includes(searchVal);
      let matchUnit = unitVal === '' || text.includes(unitVal);
      let matchArea = areaVal === '' || text.includes(areaVal);

      let matchDate = true;
      if (startDateVal && endDateVal) {
        const from = startDateVal <= endDateVal ? startDateVal : endDateVal;
        const to = startDateVal <= endDateVal ? endDateVal : startDateVal;
        if (from === to) {
          matchDate = (rDate === from);
        } else {
          matchDate = Boolean(rDate && rDate >= from && rDate <= to);
        }
      } else if (startDateVal) {
        matchDate = Boolean(rDate && rDate === startDateVal);
      } else if (endDateVal) {
        matchDate = Boolean(rDate && rDate <= endDateVal);
      }

      if (matchStatus && matchSearch && matchUnit && matchArea && matchDate) {
        r.style.display = '';
        visibleCount++;
      } else {
        r.style.display = 'none';
      }
    }

    const noRes = document.getElementById('noResultsRow');
    if (noRes) {
      noRes.className = (visibleCount === 0 && rows.length > 1) ? '' : 'hidden';
    }
  }
</script>
