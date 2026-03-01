<?php
$nim_session = $_SESSION['nim']; 

// Ambil data mahasiswa dari database
$query = "SELECT nama_lengkap, nim FROM mahasiswa WHERE nim = '$nim_session' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    $nama = $data['nama_lengkap'];
    $nim  = $data['nim'];
} else {
    $nama = "User";
    $nim  = "-";
}
?>
<!-- Sidebar -->
<aside class="sidebar">
  <div>
    <div class="logo">Campus Care</div>
    <p class="campus">Politeknik Negeri Indramayu</p>
  </div>

  <div class="profile">
    <div class="profile-avatar">
      <?= strtoupper(substr($nama, 0, 1)); ?>
    </div>
    <div class="profile-info">
      <p class="name"><?= $nama ?></p>
      <p class="nim"><?= $nim ?></p>
    </div>
  </div>

  <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
  <ul class="menu">
  <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a></li>
  <li><a href="profil.php" class="<?= $current_page == 'profil.php' ? 'active' : '' ?>">👤 Profil Saya</a></li>
  <li><a href="laporan.php" class="<?= $current_page == 'laporan.php' ? 'active' : '' ?>">📝 Laporan & Pengaduan</a></li>
  <li><a href="booking_konseling.php" class="<?= $current_page == 'booking_konseling.php' ? 'active' : '' ?>">💬 Booking Konseling</a></li>
  <li><a href="booking_fasilitas.php" class="<?= $current_page == 'booking_fasilitas.php' ? 'active' : '' ?>">🏢 Booking Fasilitas</a></li>
  <li><a href="event.php" class="<?= $current_page == 'event.php' ? 'active' : '' ?>">📅 Event & Kegiatan</a></li>
  <li><a href="artikel.php" class="<?= $current_page == 'artikel.php' ? 'active' : '' ?>">📰 Artikel & Informasi</a></li>
  <li><a href="riwayat_konseling.php" class="<?= $current_page == 'riwayat_konseling.php' ? 'active' : '' ?>">📚 Riwayat Konseling</a></li>
  <li><a href="riwayat.php" class="<?= $current_page == 'riwayat.php' ? 'active' : '' ?>">📂 Riwayat Layanan</a></li>
 <li><a href="#" onclick="confirmLogout()">🚪 Logout</a></li>

</ul>
</aside>

  