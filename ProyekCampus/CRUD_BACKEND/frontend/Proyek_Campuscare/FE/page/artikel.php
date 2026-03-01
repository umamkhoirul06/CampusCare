<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

// Ambil data artikel dari database
$query = "SELECT id_artikel, judul, konten, gambar, tanggal_publikasi FROM artikel WHERE status='published' ORDER BY tanggal_publikasi DESC";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($koneksi));
}

// Base URL untuk gambar artikel
$baseURL = "http://localhost/CAMPUSCARE1/ProyekCampus/CRUD_BACKEND/uploads/artikel/";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Artikel & Informasi - Campus Care</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .content {
        margin-left: 260px;
        padding: 30px;
        width: calc(100% - 260px);
    }
    .content h2 {
        font-size: 22px;
        font-weight: 700;
        color: #222;
    }
    .content p.subtitle {
        color: #666;
        margin-top: 5px;
        margin-bottom: 20px;
    }
    .artikel-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 15px;
        display: flex;
        gap: 20px;
        align-items: center;
    }
    .artikel-card img {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }
    .artikel-card h3 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .artikel-card p {
        font-size: 13px;
        color: #555;
        margin-bottom: 8px;
    }
    .artikel-card a {
        text-decoration: none;
        color: #0066ff;
        font-weight: 500;
        font-size: 13px;
        transition: 0.2s;
    }
    .artikel-card a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="content">
    <h2>Artikel & Informasi</h2>
    <p class="subtitle">Baca artikel dan tips berguna</p>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="artikel-card">
            <?php
            // Ambil nama file dari database dan buat path lengkap
            if (!empty($row['gambar'])) {
                $gambarArtikel = $baseURL . $row['gambar'];
            } else {
                // Jika tidak ada gambar, tampilkan placeholder
                $gambarArtikel = "https://via.placeholder.com/120x80?text=No+Image";
            }
            ?>
            <img src="<?= $gambarArtikel ?>" alt="Gambar Artikel" width="120" height="80">

            <div>
                <h3><?= htmlspecialchars($row['judul']); ?></h3>
                <p><?= htmlspecialchars(substr($row['konten'], 0, 100)); ?>...</p>
                <a href="detail_artikel.php?id=<?= $row['id_artikel']; ?>">Baca Selengkapnya →</a>
            </div>
        </div>
    <?php endwhile; ?>

    <?php if (mysqli_num_rows($result) == 0): ?>
        <p>Tidak ada artikel yang tersedia saat ini.</p>
    <?php endif; ?>
</div>

<script>
function confirmLogout() {
  if (confirm("Yakin ingin logout?")) {
    window.location.href = "../../logout.php";
  }
}
</script>

</body>
</html>
