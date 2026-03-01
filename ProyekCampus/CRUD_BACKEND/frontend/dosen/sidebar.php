<?php
include 'koneksi.php'; 

$current_page = basename($_SERVER['PHP_SELF']);

// ===== ambil nama dosen dari session / DB =====
$nama_dosen_safe = 'Dosen';

// 1) kalau session dosen sudah simpan nama
if (isset($_SESSION['dosen']['nama_lengkap']) && $_SESSION['dosen']['nama_lengkap'] !== '') {
    $nama_dosen_safe = $_SESSION['dosen']['nama_lengkap'];
} else {
    // 2) fallback: ambil dari DB pakai id_konselor / id_dosen
    $id_konselor = $_SESSION['dosen']['id_konselor'] ?? null;

    if ($id_konselor) {
        // coba tabel konselor dulu
        $stmt = $conn->prepare("SELECT nama_lengkap FROM konselor WHERE id_konselor = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $id_konselor);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!empty($res['nama_lengkap'])) {
                $nama_dosen_safe = $res['nama_lengkap'];
            }
        }

        // kalau tabel konselor gak ada / kosong, coba tabel dosen
        if ($nama_dosen_safe === 'Dosen') {
            $stmt2 = $conn->prepare("SELECT nama_lengkap FROM dosen WHERE id_konselor = ? OR id_dosen = ? LIMIT 1");
            if ($stmt2) {
                $stmt2->bind_param("ii", $id_konselor, $id_konselor);
                $stmt2->execute();
                $res2 = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();
                if (!empty($res2['nama_lengkap'])) {
                    $nama_dosen_safe = $res2['nama_lengkap'];
                }
            }
        }
    }
}

$initial = strtoupper(substr($nama_dosen_safe, 0, 1));
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<style>
.sidebar {
  width: 250px;
  background: #fff;
  border-right: 1px solid #eee;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  padding-top: 20px;
  overflow-y: auto;
  z-index: 5;
}
.logo {
  text-align: center;
  font-weight: 600;
  font-size: 20px;
  color: #2563eb;
  margin-bottom: 20px;
}
.profile {
  display: flex;
  align-items: center;
  padding: 0 20px;
  margin-bottom: 15px;
}
.profile .initial {
  background: #2563eb;
  color: white;
  border-radius: 50%;
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  margin-right: 10px;
  flex: 0 0 auto;
}
.sidebar a {
  display: block;
  color: #555;
  text-decoration: none;
  padding: 10px 25px;
  margin: 4px 10px;
  border-radius: 8px;
  font-size: 14px;
  transition: 0.2s;
}
.sidebar a i { margin-right: 10px; width: 18px; }
.sidebar a:hover, .sidebar a.active {
  background: #2563eb;
  color: white;
}
.sidebar a.text-danger { color: #b91c1c; }
.sidebar a.text-danger:hover { background: #fee2e2; color: #b91c1c; }
</style>

<div class="sidebar">
  <div class="logo">Campus Care</div>

  <div class="profile">
    <div class="initial"><?= htmlspecialchars($initial) ?></div>
    <div>
      <strong><?= htmlspecialchars($nama_dosen_safe) ?></strong><br>
      <small>Dosen Konselor</small>
    </div>
  </div>

  <a href="dashboard_dosen.php" class="<?= $current_page === 'dashboard_dosen.php' ? 'active' : '' ?>">
    <i class="fa fa-home"></i> Dashboard
  </a>
  <a href="jadwal_konseling.php" class="<?= $current_page === 'jadwal_konseling.php' ? 'active' : '' ?>">
    <i class="fa fa-calendar-alt"></i> Jadwal Konseling
  </a>
  <a href="request_konseling.php" class="<?= $current_page === 'request_konseling.php' ? 'active' : '' ?>">
    <i class="fa fa-plus-circle"></i> Request Konseling
  </a>
  <a href="riwayat_konseling.php" class="<?= $current_page === 'riwayat_konseling.php' ? 'active' : '' ?>">
    <i class="fa fa-history"></i> Riwayat Konseling
  </a>
  <a href="atur_ketersediaan.php" class="<?= $current_page === 'atur_ketersediaan.php' ? 'active' : '' ?>">
    <i class="fa fa-clock"></i> Atur Ketersediaan
  </a>
  <a href="profil_saya.php" class="<?= $current_page === 'profil_saya.php' ? 'active' : '' ?>">
    <i class="fa fa-user"></i> Profil Saya
  </a>
  <a href="../../logout.php" class="text-danger">
    <i class="fa fa-sign-out-alt"></i> Logout
  </a>
</div>
