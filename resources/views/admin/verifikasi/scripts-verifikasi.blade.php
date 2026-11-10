<script>
  let activeModalLaporanId = null;

  // Tab switching in Three-Page Verification Modal
  function switchModalPage(pageNumber) {
    const page1 = document.getElementById('modalPage1');
    const page2 = document.getElementById('modalPage2');
    const page3 = document.getElementById('modalPage3');
    const btn1 = document.getElementById('tabBtnPage1');
    const btn2 = document.getElementById('tabBtnPage2');
    const btn3 = document.getElementById('tabBtnPage3');

    const activeClass = 'px-3 py-1 rounded text-xs font-label-md font-bold bg-white text-injourney-teal shadow-xs cursor-pointer flex items-center gap-1.5';
    const inactiveClass = 'px-3 py-1 rounded text-xs font-label-md text-secondary hover:text-on-surface cursor-pointer flex items-center gap-1.5';

    if (pageNumber === 1) {
      page1.classList.remove('hidden');
      page2.classList.add('hidden');
      if (page3) page3.classList.add('hidden');
      btn1.className = activeClass;
      btn2.className = inactiveClass;
      if (btn3) btn3.className = inactiveClass;
    } else if (pageNumber === 2) {
      page1.classList.add('hidden');
      page2.classList.remove('hidden');
      if (page3) page3.classList.add('hidden');
      btn1.className = inactiveClass;
      btn2.className = activeClass;
      if (btn3) btn3.className = inactiveClass;
      setTimeout(initSignaturePad, 60);
    } else if (pageNumber === 3) {
      page1.classList.add('hidden');
      page2.classList.add('hidden');
      if (page3) page3.classList.remove('hidden');
      btn1.className = inactiveClass;
      btn2.className = inactiveClass;
      if (btn3) btn3.className = activeClass;
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

        // Update ICAO Risk Score Badge
        const riskBadge = document.getElementById('modalRiskScoreBadge');
        if (riskBadge) {
          const rLvl = (data.risk_level || 'rendah').toLowerCase();
          const rScore = data.risk_score || 1;
          riskBadge.classList.remove('hidden');
          if (rLvl === 'kritis') {
            riskBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-rose-600 text-white shadow-xs ring-2 ring-rose-300 animate-pulse';
            riskBadge.innerHTML = `<span class="material-symbols-outlined text-xs">warning</span> ICAO: Kritis (${rScore}/12)`;
          } else if (rLvl === 'tinggi') {
            riskBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-600 text-white shadow-xs ring-1 ring-amber-300';
            riskBadge.innerHTML = `<span class="material-symbols-outlined text-xs">priority_high</span> ICAO: Tinggi (${rScore}/12)`;
          } else if (rLvl === 'sedang') {
            riskBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300';
            riskBadge.innerHTML = `ICAO: Sedang (${rScore}/12)`;
          } else {
            riskBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
            riskBadge.innerHTML = `ICAO: Rendah (${rScore}/12)`;
          }
        }

        // Render Riwayat Tanggapan Thread
        renderAdminThread(data.tanggapan || []);

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
    url.searchParams.delete('detail_page');
    url.searchParams.delete('backup_page');

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

  function changePerPage(val) {
    document.querySelectorAll('select[id^="perPage"]').forEach(s => s.value = val);

    const url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    url.searchParams.delete('page');
    url.searchParams.delete('detail_page');
    url.searchParams.delete('backup_page');
    url.searchParams.delete('titik_page');
    url.searchParams.delete('jenis_page');
    url.searchParams.delete('preview_page');
    window.location.href = url.toString();
  }

  function resetFilters() {
    const url = new URL(window.location.href);
    const hasServerFilters = url.searchParams.has('status') || url.searchParams.has('search') || url.searchParams.has('start_date') || url.searchParams.has('end_date');

    if (hasServerFilters) {
      const sub = url.searchParams.get('sub');
      const tab = url.searchParams.get('tab');
      const perPage = url.searchParams.get('per_page');
      const cleanUrl = new URL(window.location.pathname, window.location.origin);
      if (sub) cleanUrl.searchParams.set('sub', sub);
      if (tab) cleanUrl.searchParams.set('tab', tab);
      if (perPage) cleanUrl.searchParams.set('per_page', perPage);
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

  // Render Riwayat Thread Tanggapan Dinas di Modal Admin
  function renderAdminThread(threadList) {
    const container = document.getElementById('adminThreadContainer');
    const countText = document.getElementById('threadCountText');
    const badge = document.getElementById('tabTanggapanBadge');
    if (!container) return;

    container.innerHTML = '';
    const count = threadList ? threadList.length : 0;
    if (countText) countText.innerText = `${count} Catatan`;
    if (badge) {
      if (count > 0) {
        badge.innerText = count;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }

    if (!threadList || threadList.length === 0) {
      container.innerHTML = `
        <div class="h-full flex flex-col items-center justify-center text-center p-6 text-secondary space-y-2">
          <span class="material-symbols-outlined text-3xl text-slate-300">chat</span>
          <p class="text-xs font-medium">Belum ada tanggapan atau instruksi dinas pada berita acara ini.</p>
          <p class="text-[11px] text-secondary/70">Gunakan form di samping untuk memberikan arahan kepada petugas pelapor.</p>
        </div>
      `;
      return;
    }

    threadList.forEach(t => {
      const card = document.createElement('div');
      const isAdmin = t.is_admin;
      const kategori = t.kategori_tanggapan || 'instruksi';

      let kategoriBadge = '';
      if (kategori === 'penutupan') {
        kategoriBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Penutupan Laporan</span>';
      } else if (kategori === 'evaluasi') {
        kategoriBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Evaluasi Tindakan</span>';
      } else if (kategori === 'klarifikasi') {
        kategoriBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Klarifikasi Lapangan</span>';
      } else {
        kategoriBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Instruksi Lanjutan</span>';
      }

      card.className = `p-3.5 rounded-xl border ${isAdmin ? 'bg-white border-border-subtle shadow-xs' : 'bg-cyan-50/60 border-cyan-100'} space-y-2`;
      card.innerHTML = `
        <div class="flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full ${isAdmin ? 'bg-injourney-teal text-white' : 'bg-slate-200 text-slate-700'} text-[10px] font-bold flex items-center justify-center shrink-0">
              ${isAdmin ? 'SM' : 'FL'}
            </div>
            <div>
              <span class="font-bold text-xs text-on-surface">${t.author_name}</span>
              <span class="text-[10px] text-secondary">(${t.author_role})</span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            ${kategoriBadge}
            <span class="text-[10px] text-secondary font-mono">${t.created_at_human || t.created_at || '-'}</span>
          </div>
        </div>
        <p class="text-xs text-on-surface leading-relaxed whitespace-pre-line pl-8">${t.isi}</p>
      `;
      container.appendChild(card);
    });

    container.scrollTop = container.scrollHeight;
  }

  // Kirim Tanggapan / Instruksi Dinas dari Admin via AJAX
  function submitAdminTanggapan(e) {
    e.preventDefault();
    if (!activeModalLaporanId) return;

    const submitBtn = document.getElementById('btnSubmitAdminTanggapan');
    const kategori = document.getElementById('adminKategoriTanggapan').value;
    const isi = document.getElementById('adminIsiTanggapan').value.trim();

    if (!isi) {
      showToast('Harap tuliskan instruksi atau catatan dinas.', 'error');
      return;
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-2 border-white border-t-transparent"></span> <span>Mengirim...</span>';

    fetch(`/laporan/${activeModalLaporanId}/tanggapan`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        kategori_tanggapan: kategori,
        isi: isi
      })
    })
    .then(res => {
      if (!res.ok) throw new Error('Gagal mengirim tanggapan');
      return res.json();
    })
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">send</span> <span>Kirim Instruksi Dinas</span>';

      if (data.success) {
        document.getElementById('adminIsiTanggapan').value = '';
        showToast(data.message || 'Instruksi dinas berhasil dikirimkan!');

        // Ambil ulang detail thread untuk me-refresh tampilan
        fetch(`/admin/laporan/${activeModalLaporanId}/detail`)
          .then(r => r.json())
          .then(freshData => {
            renderAdminThread(freshData.tanggapan || []);
            if (freshData.status === 'sudah') {
              const badge = document.getElementById('modalStatusBadge');
              const resolveBtn = document.getElementById('modalResolveBtn');
              badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200';
              badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-resolved-emerald"></span> Sudah Ditangani';
              if (resolveBtn) resolveBtn.classList.add('hidden');

              const statusCell = document.getElementById(`status-cell-${activeModalLaporanId}`);
              if (statusCell) {
                statusCell.innerHTML = `
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] xl:text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-resolved-emerald"></span>
                    Ditangani
                  </span>
                `;
              }
            }
          });
      }
    })
    .catch(err => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">send</span> <span>Kirim Instruksi Dinas</span>';
      showToast(err.message || 'Terjadi kesalahan saat mengirim instruksi.', 'error');
    });
  }
</script>
