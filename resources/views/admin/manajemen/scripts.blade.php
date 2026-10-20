<!-- CLIENT-SIDE INTERACTIVITY & DYNAMIC PAGINATION SCRIPTS -->
<script>
  // Tab & View Switching (Grid vs Table)
  function setViewMode(mode) {
    const gridView = document.getElementById('catalogGridView');
    const tableView = document.getElementById('catalogTableView');
    const gridBtn = document.getElementById('viewGridBtn');
    const tableBtn = document.getElementById('viewTableBtn');

    if (!gridView || !tableView) return;

    if (mode === 'grid') {
      gridView.classList.remove('hidden');
      tableView.classList.add('hidden');
      gridBtn.className = 'p-1.5 rounded-md bg-white text-injourney-teal shadow-xs transition-all cursor-pointer';
      tableBtn.className = 'p-1.5 rounded-md text-secondary hover:text-slate-navy transition-all cursor-pointer';
    } else {
      gridView.classList.add('hidden');
      tableView.classList.remove('hidden');
      tableBtn.className = 'p-1.5 rounded-md bg-white text-injourney-teal shadow-xs transition-all cursor-pointer';
      gridBtn.className = 'p-1.5 rounded-md text-secondary hover:text-slate-navy transition-all cursor-pointer';
    }
  }

  // ================= KATALOG SATWA DYNAMIC PAGINATION & FILTER (2 BARIS MAKSIMAL) =================
  let currentCategory = 'all';
  let currentCatalogPage = 1;
  let filteredCards = [];
  let filteredTableRows = [];

  // Hitung jumlah kolom aktif pada CSS Grid agar tepat 2 baris ditampilkan
  function getCardsPerRow() {
    const gridView = document.getElementById('catalogGridView');
    if (!gridView) return 3;
    
    // Jika grid sedang hidden atau display: none, gunakan deteksi lebar layar
    if (gridView.classList.contains('hidden') || gridView.offsetParent === null) {
      const w = window.innerWidth;
      if (w >= 1536) return 4;
      if (w >= 1280) return 3;
      if (w >= 640) return 2;
      return 1;
    }

    // Deteksi computed CSS grid-template-columns aktual
    try {
      const computedCols = window.getComputedStyle(gridView).getPropertyValue('grid-template-columns');
      if (computedCols && computedCols !== 'none') {
        const colCount = computedCols.split(' ').filter(Boolean).length;
        if (colCount > 0) return colCount;
      }
    } catch (e) {}

    const w = window.innerWidth;
    if (w >= 1536) return 4;
    if (w >= 1280) return 3;
    if (w >= 640) return 2;
    return 1;
  }

  // Tampilkan MAKSIMAL 2 ROW saja sesuai kebutuhan standar operasi
  function getItemsPerPage() {
    const cardsPerRow = getCardsPerRow();
    return Math.max(2, cardsPerRow * 2);
  }

  // Category Filter Chips handler
  function setCategoryFilter(category, buttonEl) {
    currentCategory = category;
    document.querySelectorAll('.category-chip').forEach(btn => {
      btn.className = 'category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5 cursor-pointer';
    });
    buttonEl.className = 'category-chip px-3 py-1.5 rounded-lg bg-injourney-teal text-white transition-all shadow-xs flex items-center gap-1.5 cursor-pointer';
    filterCatalog(false);
  }

  // Filter katalog dan terapkan pagination 2 baris
  function filterCatalog(keepCurrentPage = false) {
    const searchInput = document.getElementById('speciesSearch');
    if (!searchInput) return;

    const searchTerm = searchInput.value.toLowerCase().trim();
    const riskValue = document.getElementById('riskFilter').value;
    const habitatValue = document.getElementById('habitatFilter').value;

    const cards = Array.from(document.querySelectorAll('.species-card'));
    const tableRows = Array.from(document.querySelectorAll('.species-table-row'));

    filteredCards = [];
    filteredTableRows = [];

    cards.forEach((card, index) => {
      const name = (card.getAttribute('data-name') || '').toLowerCase();
      const cat = card.getAttribute('data-category') || '';
      const risk = card.getAttribute('data-risk') || '';
      const habitat = card.getAttribute('data-habitat') || '';

      const matchesSearch = name.includes(searchTerm);
      const matchesCategory = (currentCategory === 'all' || cat === currentCategory);
      const matchesRisk = (riskValue === 'all' || risk === riskValue);
      const matchesHabitat = (habitatValue === 'all' || habitat.includes(habitatValue));

      if (matchesSearch && matchesCategory && matchesRisk && matchesHabitat) {
        filteredCards.push(card);
        if (tableRows[index]) filteredTableRows.push(tableRows[index]);
      }
    });

    if (!keepCurrentPage) {
      currentCatalogPage = 1;
    }

    renderCatalogPage();
  }

  // Render halaman aktif dengan pemotongan 2 baris
  function renderCatalogPage() {
    const itemsPerPage = getItemsPerPage();
    const totalItems = filteredCards.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

    if (currentCatalogPage > totalPages) {
      currentCatalogPage = totalPages;
    }
    if (currentCatalogPage < 1) {
      currentCatalogPage = 1;
    }

    const allCards = document.querySelectorAll('.species-card');
    const allRows = document.querySelectorAll('.species-table-row');

    // Sembunyikan semua item terlebih dahulu
    allCards.forEach(c => c.style.display = 'none');
    allRows.forEach(r => r.style.display = 'none');

    const emptyState = document.getElementById('catalogEmptyState');
    const paginationContainer = document.getElementById('catalogPaginationContainer');

    if (totalItems === 0) {
      if (emptyState) emptyState.classList.remove('hidden');
      if (paginationContainer) paginationContainer.classList.add('hidden');
      return;
    }

    if (emptyState) emptyState.classList.add('hidden');
    if (paginationContainer) paginationContainer.classList.remove('hidden');

    const startIndex = (currentCatalogPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

    // Tampilkan kartu & baris hanya yang masuk dalam halaman saat ini (maks 2 baris)
    for (let i = startIndex; i < endIndex; i++) {
      if (filteredCards[i]) filteredCards[i].style.display = 'flex';
      if (filteredTableRows[i]) filteredTableRows[i].style.display = '';
    }

    // Update teks rentang & total
    const rangeText = document.getElementById('paginationRangeText');
    const totalText = document.getElementById('paginationTotalText');
    if (rangeText) rangeText.innerText = `${startIndex + 1} - ${endIndex}`;
    if (totalText) totalText.innerText = totalItems;

    // Update tombol Prev & Next
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    if (prevBtn) prevBtn.disabled = (currentCatalogPage <= 1);
    if (nextBtn) nextBtn.disabled = (currentCatalogPage >= totalPages);

    // Render nomor halaman
    const pageNumbersContainer = document.getElementById('pageNumberButtons');
    if (pageNumbersContainer) {
      pageNumbersContainer.innerHTML = '';
      for (let p = 1; p <= totalPages; p++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerText = p;
        if (p === currentCatalogPage) {
          btn.className = 'w-8 h-8 rounded-lg bg-injourney-teal text-white text-xs font-bold shadow-xs cursor-pointer';
        } else {
          btn.className = 'w-8 h-8 rounded-lg bg-white border border-border-subtle text-slate-navy hover:bg-surface-container hover:text-injourney-teal text-xs font-semibold transition-all cursor-pointer';
        }
        btn.onclick = () => goToCatalogPage(p);
        pageNumbersContainer.appendChild(btn);
      }
    }
  }

  function changeCatalogPage(delta) {
    goToCatalogPage(currentCatalogPage + delta);
  }

  function goToCatalogPage(page) {
    const itemsPerPage = getItemsPerPage();
    const totalPages = Math.max(1, Math.ceil(filteredCards.length / itemsPerPage));
    if (page < 1 || page > totalPages) return;
    currentCatalogPage = page;
    renderCatalogPage();

    // Smooth scroll kembali ke toolbar katalog jika scrolled ke bawah
    const searchBox = document.getElementById('speciesSearch');
    if (searchBox && window.scrollY > 400) {
      searchBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function resetCatalogFilters() {
    const searchInput = document.getElementById('speciesSearch');
    if (searchInput) searchInput.value = '';
    const riskFilter = document.getElementById('riskFilter');
    if (riskFilter) riskFilter.value = 'all';
    const habitatFilter = document.getElementById('habitatFilter');
    if (habitatFilter) habitatFilter.value = 'all';

    currentCategory = 'all';
    document.querySelectorAll('.category-chip').forEach((btn, idx) => {
      if (idx === 0) {
        btn.className = 'category-chip px-3 py-1.5 rounded-lg bg-injourney-teal text-white transition-all shadow-xs flex items-center gap-1.5 cursor-pointer';
      } else {
        btn.className = 'category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5 cursor-pointer';
      }
    });

    filterCatalog(false);
  }

  // Listener resize dengan debounce agar konsisten 2 baris saat ukuran viewport berubah
  let catalogResizeTimeout;
  window.addEventListener('resize', function() {
    clearTimeout(catalogResizeTimeout);
    catalogResizeTimeout = setTimeout(() => {
      filterCatalog(true);
    }, 150);
  });

  // Jalankan inisialisasi pagination saat load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => filterCatalog(false));
  } else {
    filterCatalog(false);
  }

  // Modal Handlers
  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('hidden');
      document.body.style.overflow = '';
    }
  }

  // Detailed Species Profiling Modal
  function openSpeciesDetail(name, cat, risk, grid, desc, hours, weight) {
    document.getElementById('modalSpeciesName').innerText = name;
    document.getElementById('modalSpeciesCategory').innerText = cat;
    document.getElementById('modalSpeciesRisk').innerText = risk;
    document.getElementById('modalSpeciesGrid').innerText = 'Grid ' + grid;
    document.getElementById('modalSpeciesDesc').innerText = desc || 'Karakteristik lapangan terpantau normal.';
    document.getElementById('modalSpeciesHours').innerText = hours || '08:00 - 15:00 WIB';
    document.getElementById('modalSpeciesWeight').innerText = weight || 'FOD Ringan - Sedang';
    
    const btnLog = document.getElementById('modalBtnViewLog');
    if (btnLog) {
      btnLog.href = "{{ route('admin.dashboard') }}?search=" + encodeURIComponent(name);
    }

    openModal('speciesDetailModal');
  }

  function openEditSpeciesModal(id, nama, kategori, risiko, grid, deskripsi, sop) {
    document.getElementById('editNama').value = nama;
    document.getElementById('editKategori').value = kategori;
    document.getElementById('editRisiko').value = risiko;
    document.getElementById('editGrid').value = grid;
    document.getElementById('editDeskripsi').value = deskripsi;
    document.getElementById('editSop').value = sop;

    const form = document.getElementById('editSpeciesForm');
    form.action = "{{ url('admin/manajemen/satwa') }}/" + id;

    openModal('editSpeciesModal');
  }

  // Close modal on escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeModal('speciesDetailModal');
      closeModal('addSpeciesModal');
      closeModal('editSpeciesModal');
    }
  });
</script>
