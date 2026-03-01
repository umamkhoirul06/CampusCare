<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

// Cek login
if (!isset($_SESSION['id_mahasiswa'])) {
    header("Location: ../../login.php");
    exit;
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];
$nama = $_SESSION['nama'] ?? 'User';

// Hitung total laporan
$total_laporan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM laporan_pengaduan WHERE id_mahasiswa = '$id_mahasiswa'
"))['total'];

// Hitung total booking konseling
$total_konseling = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM permintaan_konseling WHERE id_mahasiswa = '$id_mahasiswa'
"))['total'];

// Hitung total booking fasilitas
$total_fasilitas = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM booking_fasilitas WHERE id_mahasiswa = '$id_mahasiswa'
"))['total'];

// Hitung total event yang diikuti
$total_event = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM pendaftaran_event WHERE id_mahasiswa = '$id_mahasiswa'
"))['total'];
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Campus Care</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      display: flex;
      background-color: #f4f6f9;
      color: #333;
    }

    /* === Tombol About (pojok kanan atas) === */
    .about-btn {
      position: fixed;
      top: 25px;
      right: 40px;
      background: #fff;
      border: 1px solid #ccc;
      padding: 10px 16px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: all 0.2s ease;
      box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }

    .about-btn:hover {
      background: #2563eb;
      color: #fff;
      border-color: #2563eb;
      box-shadow: 0 3px 10px rgba(37,99,235,0.2);
    }

    .about-btn svg {
      width: 16px;
      height: 16px;
    }

    /* === Main Content === */
    .main-content {
      margin-left: 270px;
      padding: 30px;
      width: calc(100% - 270px);
    }

    .main-content h1 {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 5px;
    }

    .main-content p {
      font-size: 14px;
      color: #666;
      margin-bottom: 20px;
    }

    /* Cards Top */
    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 20px;
      margin-bottom: 25px;
    }

    .card {
      background: #fff;
      border-radius: 14px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 15px;
      transition: 0.2s;
    }

    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .card-icon {
      font-size: 28px;
    }

    .card-text {
      line-height: 1.3;
    }

    .card-text p {
      font-size: 13px;
      color: #777;
    }

    .card-text h2 {
      font-size: 20px;
      font-weight: 600;
      color: #111;
    }

    /* Section */
    .section {
      background: #fff;
      border-radius: 14px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .section h2 {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 15px;
      color: #111;
    }

    /* Aksi Cepat */
    .actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 15px;
    }

    .action {
      background: #f5f8ff;
      border-radius: 14px;
      padding: 18px;
      text-align: center;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: 0.2s;
    }

    .action:hover {
      background: #2563eb;
      color: #fff;
    }

    /* Aktivitas */
    .no-activity {
      text-align: center;
      color: #777;
      padding: 30px;
    }

    .no-activity img {
      width: 60px;
      opacity: 0.8;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <!-- Tombol About (pojok kanan atas) -->
  <a href="about.php" class="about-btn">
    👤 About CampusCare
  </a>

  <!-- Konten Utama -->
  <main class="main-content">
    <h1>Dashboard</h1>
    <p>Selamat datang di Campus Care <?= htmlspecialchars($nama); ?>!</p>

    <!-- Cards -->
    <!-- Cards -->
<div class="card-container">
  <div class="card">
    <div class="card-icon">📝</div>
    <div class="card-text">
      <p>Total Laporan</p>
      <h2><?= $total_laporan; ?></h2>
    </div>
  </div>

  <div class="card">
    <div class="card-icon">💬</div>
    <div class="card-text">
      <p>Booking Konseling</p>
      <h2><?= $total_konseling; ?></h2>
    </div>
  </div>

  <div class="card">
    <div class="card-icon">🏢</div>
    <div class="card-text">
      <p>Booking Fasilitas</p>
      <h2><?= $total_fasilitas; ?></h2>
    </div>
  </div>

  <div class="card">
    <div class="card-icon">📅</div>
    <div class="card-text">
      <p>Event Diikuti</p>
      <h2><?= $total_event; ?></h2>
    </div>
  </div>
</div>


    <!-- Aksi Cepat -->
    <div class="section">
      <h2>Aksi Cepat</h2>
      <div class="actions">
        <div class="action" onclick="location.href='laporan.php'">📝 Buat Laporan</div>
        <div class="action" onclick="location.href='booking_konseling.php'">💬 Booking Konseling</div>
        <div class="action" onclick="location.href='booking_fasilitas.php'">🏢 Booking Fasilitas</div>
        <div class="action" onclick="location.href='event.php'">📅 Lihat Event</div>
      </div>
    </div>

    <!-- Aktivitas Terbaru -->
    <div class="section">
      <h2>Aktivitas Terbaru</h2>
      <div class="no-activity">
        <img src="images/123.png" alt="no data" style="width:120px; height:200px; object-fit:contain;">
        <p>Belum ada aktivitas</p>
      </div>
    </div>
  </main>

  <script>
  function confirmLogout() {
    if (confirm("Yakin ingin logout?")) {
      window.location.href = "../../logout.php";
    }
  }
  </script>

</body>
</html>
