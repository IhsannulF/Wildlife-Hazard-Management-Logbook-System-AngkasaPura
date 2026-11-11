<!-- CLIENT-SIDE SCRIPTS FOR USER MANAGEMENT & FIELD CONFIGURATION -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
  const userSignaturesMap = @json($users->pluck('tanda_tangan', 'id'));
  const userSignaturePads = {
    add: null,
    edit: null,
  };

  function initUserSignaturePad(mode) {
    if (typeof SignaturePad === 'undefined') return;
    const canvasId = mode === 'add' ? 'addUserSignatureCanvas' : 'editUserSignatureCanvas';
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const width = canvas.offsetWidth || 420;
    const height = canvas.offsetHeight || 112;
    canvas.width = width * ratio;
    canvas.height = height * ratio;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

    if (!userSignaturePads[mode]) {
      userSignaturePads[mode] = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: '#0f172a',
      });
    } else {
      userSignaturePads[mode].clear();
    }
  }

  function clearUserSignatureCanvas(mode) {
    if (userSignaturePads[mode]) {
      userSignaturePads[mode].clear();
    }
    const hiddenId = mode === 'add' ? 'addUserSignatureInput' : 'editUserSignatureInput';
    const hiddenEl = document.getElementById(hiddenId);
    if (hiddenEl) hiddenEl.value = '';
  }

  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
      if (modalId === 'modalAddUser') {
        requestAnimationFrame(() => initUserSignaturePad('add'));
      } else if (modalId === 'modalEditUser') {
        requestAnimationFrame(() => initUserSignaturePad('edit'));
      }
    }
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('hidden');
      document.body.style.overflow = '';
    }
  }

  function openEditUserModal(id, nama, username, role, jabatan) {
    document.getElementById('editUserNama').value = nama;
    document.getElementById('editUserUsername').value = username;
    document.getElementById('editUserRole').value = role;
    document.getElementById('editUserJabatan').value = jabatan;

    const removeCheck = document.getElementById('editUserRemoveSignature');
    if (removeCheck) removeCheck.checked = false;
    const editSigInput = document.getElementById('editUserSignatureInput');
    if (editSigInput) editSigInput.value = '';

    const sigWrapper = document.getElementById('editUserCurrentSigWrapper');
    const sigImg = document.getElementById('editUserCurrentSigImg');
    const existingSig = userSignaturesMap[id] || '';
    if (sigWrapper && sigImg) {
      if (existingSig) {
        sigImg.src = existingSig;
        sigWrapper.classList.remove('hidden');
      } else {
        sigImg.src = '';
        sigWrapper.classList.add('hidden');
      }
    }

    const form = document.getElementById('editUserForm');
    form.action = "{{ url('admin/manajemen/users') }}/" + id;

    openModal('modalEditUser');
  }

  document.getElementById('addUserForm')?.addEventListener('submit', function() {
    const input = document.getElementById('addUserSignatureInput');
    if (input && userSignaturePads.add && !userSignaturePads.add.isEmpty()) {
      input.value = userSignaturePads.add.toDataURL('image/png');
    }
  });

  document.getElementById('editUserForm')?.addEventListener('submit', function() {
    const input = document.getElementById('editUserSignatureInput');
    if (input && userSignaturePads.edit && !userSignaturePads.edit.isEmpty()) {
      input.value = userSignaturePads.edit.toDataURL('image/png');
    }
  });

  function toggleAddOpsi(type) {
    const container = document.getElementById('addOpsiContainer');
    if (container) {
      if (type === 'dropdown') {
        container.classList.remove('hidden');
      } else {
        container.classList.add('hidden');
      }
    }
  }

  function filterUsersTable() {
    const search = (document.getElementById('userSearchInput')?.value || '').toLowerCase();
    const roleFilter = document.getElementById('userRoleFilter')?.value || 'all';
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
      const name = (row.querySelector('.user-name')?.innerText || '').toLowerCase();
      const username = (row.querySelector('.user-username')?.innerText || '').toLowerCase();
      const jabatan = (row.querySelector('.user-jabatan')?.innerText || '').toLowerCase();
      const role = row.getAttribute('data-role');

      const matchesSearch = !search || name.includes(search) || username.includes(search) || jabatan.includes(search);
      const matchesRole = (roleFilter === 'all') || (role === roleFilter);

      row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
    });
  }

  // Close modal on escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeModal('modalAddUser');
      closeModal('modalEditUser');
      closeModal('modalAddField');
    }
  });
</script>
