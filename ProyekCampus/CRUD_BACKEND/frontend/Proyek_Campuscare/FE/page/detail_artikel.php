<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

// Pastikan ID artikel ada di URL
if (!isset($_GET['id'])) {
    die("Artikel tidak ditemukan.");
}

$id = intval($_GET['id']); // keamanan URL

// Ambil artikel berdasarkan ID
$query = "SELECT * FROM artikel WHERE id_artikel = $id";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$artikel = mysqli_fetch_assoc($result);

if (!$artikel) {
    die("Artikel tidak ditemukan!");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artikel['judul']); ?> - Campus Care</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
        }
        .content h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
        }
        .content img {
            display: block; /* Supaya bisa di-center */
            margin: 15px auto; /* Tengah secara horizontal */
            max-width: 80%; /* Responsif */
            height: auto;
            border-radius: 10px;
        }
        .content .tanggal {
            font-size: 13px;
            color: #666;
            text-align: center;
            margin-bottom: 15px;
        }
        .content p {
            font-size: 15px;
            line-height: 1.5; /* Sedikit lebih rapat */
            color: #333;
            text-align: justify;
            white-space: pre-line;
            margin-bottom: 10px; /* Kurangi jarak antar paragraf */
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 14px;
            background-color: #0066ff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: 0.3s;
        }
        .back-btn:hover {
            background-color: #004bb5;
        }
    </style>
</head>
<body>

<div class="content">
    <h2><?= htmlspecialchars($artikel['judul']); ?></h2>
    <p class="tanggal">Dipublikasikan pada: <?= date('d M Y', strtotime($artikel['tanggal_publikasi'])); ?></p>

    <?php
    $baseURL = "http://localhost/CAMPUSCARE1/ProyekCampus/CRUD_BACKEND/uploads/artikel/";
    $gambarArtikel = !empty($artikel['gambar']) ? $baseURL . $artikel['gambar'] : "https://via.placeholder.com/300x200?text=No+Image";
    ?>
    <img src="<?= $gambarArtikel ?>" alt="Gambar Artikel">

    <p><?= nl2br(htmlspecialchars($artikel['konten'])); ?></p>

    <a href="artikel.php" class="back-btn">← Kembali ke Artikel</a>
</div>

</body>
</html>
