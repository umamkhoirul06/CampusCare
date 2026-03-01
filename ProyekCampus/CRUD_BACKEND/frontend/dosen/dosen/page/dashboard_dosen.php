<?php
session_start();
include '../../koneksi.php';

// Cek login dosen
if (!isset($_SESSION['dosen'])) {
    header("Location: ../../login.php");
    exit;
}

// Ambil data dosen
$dosen = $_SESSION['dosen'];
$nama_dosen = $dosen['nama_lengkap'] ?? "Dosen Konselor";
$id_konselor = $dosen['id_konselor'];

// ===============================================
// QUERY STATISTIK
// ===============================================
// ===============================================
// QUERY STATISTIK — VERSI BARU
// ===============================================
$today = date('Y-m-d');

// 1. Request Konseling (tabel request_konseling)
$request_konseling = $conn->query("
    SELECT COUNT(*) AS total 
    FROM request_konseling
")->fetch_assoc()['total'] ?? 0;

// 2. Jadwal Hari Ini (tabel schedule_konseling)
$jadwal_hari_ini = $conn->query("
    SELECT COUNT(*) AS total 
    FROM schedule_konseling
    WHERE tanggal_konseling = '$today'
")->fetch_assoc()['total'] ?? 0;

// 3. Konseling Selesai (tabel riwayat_konseling)
$konseling_selesai = $conn->query("
    SELECT COUNT(*) AS total 
    FROM riwayat_konseling
")->fetch_assoc()['total'] ?? 0;

// 4. Total Mahasiswa (mahasiswa unik di riwayat_konseling)
$total_mahasiswa = $conn->query("
    SELECT COUNT(DISTINCT id_mahasiswa) AS total
    FROM riwayat_konseling
")->fetch_assoc()['total'] ?? 0;


// ===============================================
// TABLE JADWAL HARI INI – DIBUAT JADI STRING HTML
// ===============================================
$sql_tabel = "
    SELECT 
        s.id_schedule, 
        s.waktu, 
        s.topik, 
        s.status, 
        m.nama_lengkap AS mahasiswa, 
        m.nim
    FROM schedule_konseling s
    JOIN mahasiswa m ON s.nama_mahasiswa = m.id_mahasiswa
    WHERE DATE(s.tanggal_konseling) = '$today'
AND s.id_konselor = '$id_konselor'
    ORDER BY s.waktu ASC
";
$result_tabel = $conn->query($sql_tabel);

$rows_html = "";

if ($result_tabel && $result_tabel->num_rows > 0) {
    while ($row = $result_tabel->fetch_assoc()) {
        $id_konseling = (int)$row['id_konseling'];
        $waktu        = htmlspecialchars($row['waktu']);
        $mahasiswa    = htmlspecialchars($row['mahasiswa']);
        $nim          = htmlspecialchars($row['nim']);
        $topik        = htmlspecialchars($row['topik']);
        $status       = htmlspecialchars($row['status']);

        // badge status
        $status_html = "<span class=\"status {$status}\">{$status}</span>";

        // tombol aksi
        if ($status === 'Menunggu') {
            $aksi_html = "
                <form method=\"POST\" action=\"aksi_konseling.php\" style=\"display:inline-block;\">
                    <input type=\"hidden\" name=\"id_konseling\" value=\"{$id_konseling}\">
                    <button type=\"submit\" name=\"aksi\" value=\"tolak\" class=\"btn-aksi btn-danger\">Tolak</button>
                    <button type=\"submit\" name=\"aksi\" value=\"setuju\" class=\"btn-aksi btn-success\">Setuju</button>
                </form>
            ";
        } else {
            $aksi_html = "<span class=\"text-muted\">-</span>";
        }

        $rows_html .= "
            <tr>
                <td>{$waktu}</td>
                <td>{$mahasiswa}</td>
                <td>{$nim}</td>
                <td>{$topik}</td>
                <td>{$status_html}</td>
                <td style=\"text-align:center;\">{$aksi_html}</td>
            </tr>
        ";
    }
} else {
    $rows_html = "
        <tr>
            <td colspan=\"6\" style=\"text-align:center; padding:20px;\" class=\"text-muted\">
                Tidak ada jadwal konseling hari ini.
            </td>
        </tr>
    ";
}

// SALAM DINAMIS
date_default_timezone_set('Asia/Jakarta');

$hour = date('H');
if ($hour < 12)     $greeting = "Selamat pagi";
elseif ($hour < 18)   $greeting = "Selamat siang";
else                   $greeting = "Selamat malam";

$conn->close();

// Variabel untuk menandai menu aktif di sidebar (diperlukan untuk sidebar_dosen.php)
$current_page = basename($_SERVER['PHP_SELF']); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Dosen - Campus Care</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
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


    /* ========== TOMBOL ABOUT ========== */
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
      z-index: 10;
    }

    .about-btn:hover {
      background: #2563eb;
      color: #fff;
      border-color: #2563eb;
      box-shadow: 0 3px 10px rgba(37,99,235,0.2);
    }

    /* ========== MAIN CONTENT ========== */
    .main-content {
      margin-left: 250px;
      padding: 30px;
      width: calc(100% - 250px);
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

    .card-text p {
      font-size: 13px;
      color: #777;
    }

    .card-text h2 {
      font-size: 20px;
      font-weight: 600;
      color: #111;
    }

    /* Section umum */
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

    /* Tabel Jadwal */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    thead {
      background: #f3f6ff;
    }

    th, td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    th {
      font-weight: 500;
      color: #4b5563;
    }

    .status {
      padding: 5px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 500;
    }

    .status.Menunggu { background: #fff3cd; color: #856404; }
    .status.Disetujui { background: #d1e7dd; color: #0f5132; }
    .status.Selesai  { background: #cfe2ff; color: #084298; }

    .btn-aksi {
      border: none;
      border-radius: 999px;
      padding: 6px 12px;
      font-size: 12px;
      cursor: pointer;
      color: #fff;
      margin: 0 2px;
    }

    .btn-danger { background: #dc2626; }
    .btn-success { background: #2563eb; }

    .btn-danger:hover { background: #b91c1c; }
    .btn-success:hover { background: #1d4ed8; }

    .text-muted { color: #777; }

    @media (max-width: 768px) {
      .sidebar { display:none; }
      .main-content {
        margin-left: 0;
        width: 100%;
      }
      .about-btn {
        right: 16px;
      }
    }
  </style>
</head>
<body>

  <?php 
  include '../../sidebar.php'; 
  ?>

  <a href="about.php" class="about-btn">
    👤 About CampusCare
  </a>

  <main class="main-content">
    <h1>Dashboard</h1>
    <p><?= $greeting; ?>, <?= htmlspecialchars($nama_dosen); ?>. Berikut ringkasan aktivitas Anda hari ini (<?= date('d M Y'); ?>).</p>

    <div class="card-container">
      <div class="card">
        <div class="card-icon">💬</div>
        <div class="card-text">
          <p>Request Konseling</p>
          <h2><?= $request_konseling; ?></h2>
        </div>
      </div>

      <div class="card">
        <div class="card-icon">📅</div>
        <div class="card-text">
          <p>Jadwal Hari Ini</p>
          <h2><?= $jadwal_hari_ini; ?></h2>
        </div>
      </div>

      <div class="card">
        <div class="card-icon">✅</div>
        <div class="card-text">
          <p>Konseling Selesai</p>
          <h2><?= $konseling_selesai; ?></h2>
        </div>
      </div>

      <div class="card">
        <div class="card-icon">🎓</div>
        <div class="card-text">
          <p>Total Mahasiswa</p>
          <h2><?= $total_mahasiswa; ?></h2>
        </div>
      </div>
    </div>

    <div class="section">
      <h2>Aksi Cepat</h2>
      <div class="actions">
        <div class="action" onclick="location.href='request_konseling.php'">💬 Lihat Request Konseling</div>
        <div class="action" onclick="location.href='jadwal_konseling.php'">📅 Jadwal Konseling</div>
        <div class="action" onclick="location.href='atur_ketersediaan.php'">⏰ Atur Ketersediaan</div>
        <div class="action" onclick="location.href='riwayat_konseling.php'">📜 Riwayat Konseling</div>
      </div>
    </div>

    <div class="section">
      <h2>Jadwal Konseling Hari Ini</h2>
      <table>
        <thead>
          <tr>
            <th>Waktu</th>
            <th>Mahasiswa</th>
            <th>NIM</th>
            <th>Topik</th>
            <th>Status</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?= $rows_html; ?>
        </tbody>
      </table>
    </div>
  </main>

</body>
</html>