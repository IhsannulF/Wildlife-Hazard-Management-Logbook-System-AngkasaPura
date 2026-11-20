/**
 * WHMS Offline Engine & IndexedDB Sync Manager
 * Wildlife Hazard Management System — InJourney Airports
 * Version: 2.0
 */

const WHMS_OFFLINE = (() => {
  const DB_NAME = 'WHMS_Offline_DB';
  const DB_VERSION = 1;
  const STORE_NAME = 'pending_reports';

  // 1. Inisialisasi IndexedDB
  function openDB() {
    return new Promise((resolve, reject) => {
      if (!('indexedDB' in window)) {
        reject(new Error('Browser ini tidak mendukung IndexedDB.'));
        return;
      }
      const request = indexedDB.open(DB_NAME, DB_VERSION);
      request.onupgradeneeded = (e) => {
        const db = e.target.result;
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          const store = db.createObjectStore(STORE_NAME, { keyPath: 'offline_sync_id' });
          store.createIndex('created_at', 'created_at', { unique: false });
          store.createIndex('status', 'status', { unique: false });
        }
      };
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  // 2. Generate UUID v4
  function generateUUID() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
      return crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      const r = (Math.random() * 16) | 0;
      const v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }

  // 3. Simpan Laporan ke IndexedDB
  async function saveDraft(reportData) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(STORE_NAME, 'readwrite');
      const store = tx.objectStore(STORE_NAME);

      const record = {
        offline_sync_id: reportData.offline_sync_id || generateUUID(),
        created_at: new Date().toISOString(),
        status: 'pending',
        summary: reportData.summary || 'Laporan Satwa Offline',
        payload: reportData.payload || {},
        photos: reportData.photos || [],
        signature: reportData.signature || null
      };

      const request = store.put(record);
      request.onsuccess = () => {
        updateDraftCounterUI();
        resolve(record);
      };
      request.onerror = () => reject(request.error);
    });
  }

  // 4. Ambil Seluruh Draf Tertunda
  async function getAllDrafts() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(STORE_NAME, 'readonly');
      const store = tx.objectStore(STORE_NAME);
      const request = store.getAll();
      request.onsuccess = () => resolve(request.result || []);
      request.onerror = () => reject(request.error);
    });
  }

  // 5. Hapus Draf Berdasarkan offline_sync_id
  async function deleteDraft(offlineSyncId) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(STORE_NAME, 'readwrite');
      const store = tx.objectStore(STORE_NAME);
      const request = store.delete(offlineSyncId);
      request.onsuccess = () => {
        updateDraftCounterUI();
        resolve(true);
      };
      request.onerror = () => reject(request.error);
    });
  }

  // 6. Hitung Jumlah Draf
  async function countDrafts() {
    try {
      const drafts = await getAllDrafts();
      return drafts.length;
    } catch (e) {
      console.warn('Gagal menghitung draf offline:', e);
      return 0;
    }
  }

  // 7. Update UI Counter & Status Indicator
  async function updateDraftCounterUI() {
    const count = await countDrafts();
    const countBadges = document.querySelectorAll('.whms-draft-count');
    countBadges.forEach((badge) => {
      badge.textContent = count;
      if (count > 0) {
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    });

    const syncWidget = document.getElementById('whms-offline-sync-widget');
    if (syncWidget) {
      if (count > 0) {
        syncWidget.classList.remove('hidden');
      } else {
        syncWidget.classList.add('hidden');
      }
    }

    const dashboardBanner = document.getElementById('dashboardOfflineSyncBanner');
    if (dashboardBanner) {
      if (count > 0) {
        dashboardBanner.classList.remove('hidden');
      } else {
        dashboardBanner.classList.add('hidden');
      }
    }
  }

  // 8. Tampilkan Banner Notifikasi Modern
  function showNotification(message, type = 'success') {
    const existing = document.getElementById('whms-toast-notice');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'whms-toast-notice';
    toast.className = `fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl border text-sm font-medium transition-all duration-300 transform translate-y-2 opacity-0 ${
      type === 'success'
        ? 'bg-emerald-900/95 border-emerald-600 text-emerald-100'
        : type === 'warning'
        ? 'bg-amber-900/95 border-amber-600 text-amber-100'
        : 'bg-rose-900/95 border-rose-600 text-rose-100'
    }`;

    const icon = type === 'success' ? 'check_circle' : type === 'warning' ? 'wifi_off' : 'error';
    toast.innerHTML = `
      <span class="material-symbols-outlined text-xl">${icon}</span>
      <div class="flex-1">${message}</div>
      <button type="button" onclick="this.parentElement.remove()" class="text-white/60 hover:text-white ml-2">&times;</button>
    `;

    document.body.appendChild(toast);
    setTimeout(() => {
      toast.classList.remove('translate-y-2', 'opacity-0');
    }, 50);

    setTimeout(() => {
      if (toast.parentElement) {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
      }
    }, 5000);
  }

  // 9. Sinkronisasi Seluruh Draf ke Server API
  async function syncAllDrafts(options = {}) {
    if (!navigator.onLine) {
      showNotification('Gagal sinkronisasi: Perangkat Anda masih dalam mode offline.', 'warning');
      return { success: false, synced: 0 };
    }

    const drafts = await getAllDrafts();
    if (drafts.length === 0) {
      showNotification('Tidak ada antrean draf laporan offline yang perlu disinkronkan.', 'warning');
      return { success: true, synced: 0 };
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      console.error('CSRF token tidak ditemukan!');
    }

    let successCount = 0;
    let failCount = 0;

    const syncBtn = document.getElementById('whms-sync-btn');
    if (syncBtn) {
      syncBtn.disabled = true;
      syncBtn.innerHTML = `
        <span class="inline-block animate-spin material-symbols-outlined text-sm">sync</span>
        Menyinkronkan (${drafts.length})...
      `;
    }

    for (const draft of drafts) {
      try {
        const payload = {
          offline_sync_id: draft.offline_sync_id,
          payload: draft.payload,
          photos: draft.photos,
          signature: draft.signature,
          created_at: draft.created_at
        };

        const response = await fetch('/laporan/sync-offline', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken || ''
          },
          body: JSON.stringify(payload)
        });

        if (response.ok) {
          const result = await response.json();
          if (result.success) {
            await deleteDraft(draft.offline_sync_id);
            successCount++;
          } else {
            failCount++;
          }
        } else {
          failCount++;
        }
      } catch (err) {
        console.error('Gagal mengirim draf offline:', draft.offline_sync_id, err);
        failCount++;
      }
    }

    if (syncBtn) {
      syncBtn.disabled = false;
      syncBtn.innerHTML = `
        <span class="material-symbols-outlined text-sm">cloud_sync</span>
        Sinkronkan Sekarang
      `;
    }

    await updateDraftCounterUI();

    if (successCount > 0) {
      showNotification(`Berhasil menyinkronkan ${successCount} laporan offline ke server!`, 'success');
      // Jika berada di dashboard pegawai, reload setelah 1 detik agar laporan baru langsung muncul
      if (window.location.pathname.includes('/dashboard')) {
        setTimeout(() => window.location.reload(), 1200);
      }
    }

    if (failCount > 0) {
      showNotification(`${failCount} draf gagal disinkronkan. Akan dicoba lagi saat koneksi lebih stabil.`, 'warning');
    }

    return { success: failCount === 0, synced: successCount, failed: failCount };
  }

  // 10. Update Connection Status Badge
  function updateConnectionUI() {
    const isOnline = navigator.onLine;
    const onlineBadges = document.querySelectorAll('.whms-connection-status');
    onlineBadges.forEach((el) => {
      if (isOnline) {
        el.innerHTML = `
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="text-xs font-semibold text-emerald-700">Online</span>
        `;
        el.className = el.className.replace(/bg-amber-\d+|text-amber-\d+|border-amber-\d+/g, '');
        el.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
      } else {
        el.innerHTML = `
          <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
          <span class="text-xs font-semibold text-amber-700">Mode Offline</span>
        `;
        el.className = el.className.replace(/bg-emerald-\d+|text-emerald-\d+|border-emerald-\d+/g, '');
        el.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
      }
    });

    if (!isOnline) {
      showNotification('Perangkat offline. Laporan yang Anda buat akan otomatis disimpan di draf lokal.', 'warning');
    }
  }

  // Inisialisasi Event Listener
  function init() {
    window.addEventListener('online', () => {
      updateConnectionUI();
      showNotification('Koneksi internet terhubung kembali. Anda dapat menyinkronkan draf laporan.', 'success');
      updateDraftCounterUI();
    });

    window.addEventListener('offline', () => {
      updateConnectionUI();
    });

    document.addEventListener('DOMContentLoaded', () => {
      updateConnectionUI();
      updateDraftCounterUI();
      initFormInterceptor();
    });

    // Registrasi Service Worker jika didukung browser
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
          .then((reg) => {
            console.log('[WHMS PWA] Service Worker aktif di scope:', reg.scope);
          })
          .catch((err) => {
            console.warn('[WHMS PWA] Gagal meregistrasi Service Worker:', err);
          });
      });
    }
  }

  // 11. Helper file to Base64
  function fileToBase64(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = (e) => reject(e);
      reader.readAsDataURL(file);
    });
  }

  // 12. Form Interceptor untuk Form Pelaporan (/laporan/create)
  function initFormInterceptor() {
    const form = document.querySelector('form[action*="/laporan"]');
    if (!form) return;

    // Tambahkan tombol simpan draf offline manual jika diperlukan
    const actionContainer = form.querySelector('.flex.justify-end, .flex.gap-3, button[type="submit"]')?.parentElement;
    if (actionContainer && !document.getElementById('btn-save-offline-draft')) {
      const offlineBtn = document.createElement('button');
      offlineBtn.id = 'btn-save-offline-draft';
      offlineBtn.type = 'button';
      offlineBtn.className = 'px-4 py-2.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 text-sm font-semibold transition-all inline-flex items-center gap-1.5 shadow-sm mr-2';
      offlineBtn.innerHTML = `
        <span class="material-symbols-outlined text-base">save</span>
        Simpan Draf Offline
      `;
      offlineBtn.addEventListener('click', () => handleOfflineFormSave(form, true));
      actionContainer.insertBefore(offlineBtn, actionContainer.firstChild);
    }

    form.addEventListener('submit', async function (e) {
      // Jika offline, tangkap submit dan alihkan ke IndexedDB
      if (!navigator.onLine) {
        e.preventDefault();
        await handleOfflineFormSave(form, false);
      }
    });
  }

  // Handler simpan form ke IndexedDB
  async function handleOfflineFormSave(form, isManual = false) {
    try {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      const formData = new FormData(form);
      const payloadObj = {};
      const photos = [];

      // Ekstrak data teks & nested form
      for (const [key, value] of formData.entries()) {
        if (value instanceof File) {
          if (value.size > 0) {
            const base64Data = await fileToBase64(value);
            photos.push({
              field_name: key,
              name: value.name,
              type: value.type,
              size: value.size,
              data: base64Data
            });
          }
        } else {
          payloadObj[key] = value;
        }
      }

      // Ambil tanda tangan dari canvas jika ada
      let signatureData = payloadObj['tanda_tangan'] || null;
      const signatureCanvas = document.querySelector('canvas#signaturePad, canvas.signature-pad, canvas');
      if (!signatureData && signatureCanvas) {
        try {
          signatureData = signatureCanvas.toDataURL('image/png');
        } catch (e) {
          console.warn('Gagal membaca canvas signature:', e);
        }
      }

      const syncId = generateUUID();
      const grid = payloadObj['grid_lokasi'] || payloadObj['lokasi_grid'] || '-';
      const tgl = payloadObj['tanggal'] || new Date().toISOString().split('T')[0];
      const summary = `Pelaporan Offline (${tgl}) — Grid ${grid}`;

      await saveDraft({
        offline_sync_id: syncId,
        summary: summary,
        payload: payloadObj,
        photos: photos,
        signature: signatureData
      });

      showNotification('Laporan berhasil disimpan ke Draf Offline IndexedDB!', 'success');

      // Tampilkan konfirmasi dialog
      alert('Mode Offline: Laporan telah diamankan di penyimpanan lokal perangkat. Begitu sinyal internet kembali tersedia, Anda dapat menekan tombol "Sinkronkan Sekarang" di navbar/dashboard.');

      // Reset form
      form.reset();
      if (signatureCanvas && typeof window.clearSignature === 'function') {
        window.clearSignature();
      }

      if (submitBtn) submitBtn.disabled = false;
      await updateDraftCounterUI();
    } catch (err) {
      console.error('Gagal menyimpan draf offline:', err);
      alert('Terjadi kesalahan saat menyimpan draf offline: ' + err.message);
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = false;
    }
  }

  // Jalankan inisialisasi awal
  init();

  return {
    saveDraft,
    getAllDrafts,
    deleteDraft,
    countDrafts,
    syncAllDrafts,
    updateDraftCounterUI,
    showNotification
  };
})();

window.WHMS_OFFLINE = WHMS_OFFLINE;
