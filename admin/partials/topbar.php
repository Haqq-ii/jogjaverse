<header class="topbar">
  <button class="hamburger" onclick="toggleSidebar()">&#9776;</button>
  <div class="topbar-search-wrap">
    <input class="topbar-search" placeholder="Cari menu atau data">
  </div>
  <div class="avatar">
    <?= htmlspecialchars(substr($_SESSION["nama_lengkap"] ?? "A", 0, 1)) ?>
  </div>
</header>
