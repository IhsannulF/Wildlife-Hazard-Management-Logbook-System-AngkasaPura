<?php
// partials/sidebar.php
$activePage = $activePage ?? 'statistic';
$role       = $_SESSION['role']     ?? 'user';
$namaUser   = $_SESSION['namalengkap'] ?? $_SESSION['username'] ?? 'User';
$jabatan    = $_SESSION['jabatan']  ?? ucfirst($role);
$inisial    = strtoupper(substr($namaUser, 0, 2));
?>
<aside class="sidebar">

  <!-- Avatar & info user -->
  <div class="sb-user">
    <div class="sb-avatar"><?= htmlspecialchars($inisial) ?></div>
    <div class="sb-userinfo">
      <div class="sb-name"><?= htmlspecialchars($namaUser) ?></div>
      <div class="sb-role"><?= htmlspecialchars($jabatan) ?></div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="sb-nav">
    <a href="admin_dashboard.php" class="sb-item <?= $activePage==='dashboard'?'active':'' ?>">
      <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3"/>
        <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
      </svg>
      Dashboard
    </a>
    <a href="admin_statistik.php" class="sb-item <?= $activePage==='statistic'?'active':'' ?>">
      <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="20" x2="18" y2="10"/>
        <line x1="12" y1="20" x2="12" y2="4"/>
        <line x1="6"  y1="20" x2="6"  y2="14"/>
        <line x1="2"  y1="20" x2="22" y2="20"/>
      </svg>
      Statistic
    </a>
    <a href="admin_manajemen.php" class="sb-item <?= $activePage==='laporan'?'active':'' ?>">
      <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14,2 14,8 20,8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
      </svg>
      Manage Form
    </a>
  </nav>

  <div class="sb-spacer"></div>

  <!-- Logout -->
  <div class="sb-bottom">
    <a href="logout.php" class="sb-logout" onclick="return confirm('Yakin ingin logout?')">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      Logout
    </a>
  </div>
</aside>