<!-- CLIENT-SIDE SCRIPTS FOR USER MANAGEMENT & FIELD CONFIGURATION -->
<script>
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

  function openEditUserModal(id, nama, username, role, jabatan) {
    document.getElementById('editUserNama').value = nama;
    document.getElementById('editUserUsername').value = username;
    document.getElementById('editUserRole').value = role;
    document.getElementById('editUserJabatan').value = jabatan;

    const form = document.getElementById('editUserForm');
    form.action = "{{ url('admin/manajemen/users') }}/" + id;

    openModal('modalEditUser');
  }

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
