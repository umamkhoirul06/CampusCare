<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

if (!isset($_SESSION['nim'])) {
    header("Location: ../../login.php");
    exit;
}

// Ambil data profil kampus
$profil = mysqli_query($koneksi, "SELECT * FROM profil_kampus LIMIT 1");
$data_profil = mysqli_fetch_assoc($profil);

// Ambil kontak darurat
$kontak = mysqli_query($koneksi, "SELECT * FROM kontak_darurat");

// Ambil berita kampus
$berita = mysqli_query($koneksi, "SELECT * FROM berita ORDER BY tanggal DESC LIMIT 3");

// Base URL untuk gambar
$base_upload_url = "http://localhost/CAMPUSCARE1/ProyekCampus/CRUD_BACKEND/uploads/";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>About Campus Care</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body {
    background: #f4f6f9;
    font-family: 'Poppins', sans-serif;
    display: flex;
    color: #333;
  }
  .main-content {
    margin-left: 270px;
    margin-top: 80px;
    padding: 40px;
    width: calc(100% - 270px);
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .section {
    background: #fff;
    border-radius: 14px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 25px;
    width: 90%;
    max-width: 900px;
  }
  .section h1, .section h2 {
    color: #2563eb;
    margin-bottom: 9px;
    text-align: center;
  }
  .section p {
    font-size: 13px;
    color: #555;
    line-height: 1.8;
    text-align: justify;
  }
  .foto-kampus {
    display: block;
    margin: 0 auto 18px auto;
    border-radius: 12px;
    width: 300px;
    height: auto;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }
  .kontak-list {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
  }
  .kontak-item {
    background: #f5f8ff;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    flex: 1 1 250px;
    text-align: center;
  }
 .berita-item {
  background: #f9fafc;
  border-radius: 10px;
  padding: 15px;
  display: flex;
  gap: 15px;
  align-items: flex-start;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  margin-bottom: 15px;
}

.berita-item img {
  width: 100px;        /* ukuran kecil */
  height: 90px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
}

.berita-item div {
  display: flex;
  flex-direction: column;  /* ✅ Biar teksnya vertikal ke bawah */
}

.berita-item h3 {
  margin: 0 0 6px 0;
  font-size: 14px;
  color: #2563eb;
}

.berita-item p {
  font-size: 13px;
  color: #555;
  line-height: 1.6;          /* ✅ Lebih nyaman dibaca */
  text-align: justify;       /* ✅ Paragraf rapi rata kiri-kanan */
  margin: 0 0 8px 0;
  white-space: normal;       /* ✅ Biar teks bisa turun ke bawah */
  word-wrap: break-word;     /* ✅ Pecah kata panjang */
}

.berita-item small {
  font-size: 12px;
  color: #888;
}

</style>
</head>
<body>

<nav style="
    position: fixed;
    top: 0;
    left: 250px;
    right: 0;
    height: 60px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    z-index: 10;
">
    <h2 style="font-size: 18px; color: #2563eb;">Campus Care</h2>
    <div>
        <a href="dashboard.php" style="margin-right: 20px; text-decoration: none; color: #333; font-weight: 500;">Dashboard</a>
        <a href="about.php" style="text-decoration: none; color: #2563eb; font-weight: 600;">About Campus Care</a>
    </div>
</nav>

<main class="main-content">

  <!-- PROFIL KAMPUS -->
  <div class="section">
    <?php if ($data_profil): ?>
      <h1><?= htmlspecialchars($data_profil['nama_kampus']) ?></h1>
<?php 
$baseURL = "http://localhost/CAMPUSCARE1/ProyekCampus/CRUD_BACKEND/";
$fotoKampus = $baseURL . $data_profil['foto'];
?>
<img src="<?= $fotoKampus ?>" alt="Foto Kampus" class="foto-kampus">


      <p><?= nl2br(htmlspecialchars($data_profil['deskripsi'])) ?></p>
      <p><strong>Alamat:</strong> <?= htmlspecialchars($data_profil['alamat']) ?></p>
      <p><strong>Tahun Berdiri:</strong> <?= htmlspecialchars($data_profil['tahun_berdiri']) ?></p>
    <?php else: ?>
      <p><em>Data profil kampus belum tersedia.</em></p>
    <?php endif; ?>
  </div>

  <!-- KONTAK DARURAT -->
  <div class="section">
    <h2>Kontak Darurat Kampus</h2>
    <div class="kontak-list">
      <?php if (mysqli_num_rows($kontak) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($kontak)): ?>
          <div class="kontak-item">
            <strong><?= htmlspecialchars($row['nama_kontak']) ?>:</strong><br>
            <?= htmlspecialchars($row['nilai']) ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p><em>Tidak ada kontak darurat yang tersedia.</em></p>
      <?php endif; ?>
    </div>
  </div>

  <!-- BERITA -->
<div class="section">
  <h2>Berita Kampus</h2>
  <?php if (mysqli_num_rows($berita) > 0): ?>
    <?php while ($b = mysqli_fetch_assoc($berita)): ?>
      <?php 
$base_upload_url = "http://localhost/CAMPUSCARE1/ProyekCampus/CRUD_BACKEND/";
$pathFotoBerita = $base_upload_url . $b['foto'];
?>


      <div class="berita-item">
    <img src="<?= $pathFotoBerita ?>" alt="Foto Berita">
    <div>
        <h3><?= htmlspecialchars($b['judul']) ?></h3>
        <p><?= htmlspecialchars(substr($b['isi'], 0, 200)) ?>...</p>
        <small><i><?= htmlspecialchars($b['tanggal']) ?></i></small>
    </div>
</div>

    <?php endwhile; ?>
  <?php else: ?>
    <p><em>Belum ada berita terbaru.</em></p>
  <?php endif; ?>
</div>

</main>

</body>
</html>
