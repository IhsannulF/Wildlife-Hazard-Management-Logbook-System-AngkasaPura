<!-- CORE UTILITY SCRIPTS (TOAST & REFRESH) -->
<script>
  function showToast(message, type = 'success') {
    const toast = document.getElementById('toastNotification');
    const msg = document.getElementById('toastMessage');
    const icon = document.getElementById('toastIcon');
    if (!toast) return;

    msg.innerText = message;
    if (type === 'success') {
      toast.className = 'fixed bottom-5 right-5 z-50 transform translate-y-0 opacity-100 transition-all duration-300 pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-sm font-medium bg-emerald-50 text-emerald-800 border-emerald-200';
      icon.innerText = 'check_circle';
      icon.className = 'material-symbols-outlined text-lg text-emerald-600';
    } else if (type === 'error') {
      toast.className = 'fixed bottom-5 right-5 z-50 transform translate-y-0 opacity-100 transition-all duration-300 pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-sm font-medium bg-rose-50 text-rose-800 border-rose-200';
      icon.innerText = 'error';
      icon.className = 'material-symbols-outlined text-lg text-rose-600';
    } else {
      toast.className = 'fixed bottom-5 right-5 z-50 transform translate-y-0 opacity-100 transition-all duration-300 pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-sm font-medium bg-cyan-50 text-[#007fa3] border-cyan-200';
      icon.innerText = 'info';
      icon.className = 'material-symbols-outlined text-lg text-[#00A9C1]';
    }

    setTimeout(() => {
      toast.classList.remove('translate-y-0', 'opacity-100');
      toast.classList.add('translate-y-24', 'opacity-0');
    }, 3800);
  }

  function refreshData() {
    showToast('Sinkronisasi data antrean verifikasi...', 'info');
    setTimeout(() => {
      window.location.reload();
    }, 600);
  }
</script>
