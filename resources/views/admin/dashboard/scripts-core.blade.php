<script>
  // Universal Toast Notification Utility
  function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    const toastMsg = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');

    if (toastMsg) toastMsg.innerText = message;
    if (toastIcon) {
      if (type === 'success') {
        toastIcon.innerText = 'check_circle';
        toastIcon.className = 'material-symbols-outlined text-resolved-emerald';
      } else if (type === 'info') {
        toastIcon.innerText = 'info';
        toastIcon.className = 'material-symbols-outlined text-injourney-teal';
      } else if (type === 'error') {
        toastIcon.innerText = 'error';
        toastIcon.className = 'material-symbols-outlined text-warning-rose';
      }
    }

    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(() => {
      toast.classList.add('translate-y-20', 'opacity-0');
    }, 3400);
  }

  function refreshData() {
    showToast('Memperbarui data telemetri satwa sisi udara...', 'info');
    setTimeout(() => {
      window.location.reload();
    }, 700);
  }
</script>
