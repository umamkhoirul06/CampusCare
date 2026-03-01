<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Jakarta');

include "../../koneksi.php";
include "../assets/tgl_indo.php";

if (empty($_SESSION['username']) || empty($_SESSION['password'])) {
    echo "<script> document.location='login.php'; </script>";
    exit;
}

$query_admin = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='" . $_SESSION['username'] . "'");
$data_admin = mysqli_fetch_assoc($query_admin);

// ✅ pindahkan ini ke atas sebelum sidebar
$page = $_GET['page'] ?? 'about';
$pageDir = __DIR__ . "/page/";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../assets/bootstrap-5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="../assets/ckeditor/ckeditor.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>Dashboard Admin | Campus Care</title>

    <style>
        body {
            background-color: #eef1f6;
            color: #1f2937;
            font-family: 'Segoe UI', sans-serif;
        }

        .list-group {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 10px;
        }

        .list-group-item {
            background-color: transparent;
            border: none;
            color: #1f2937;
            font-weight: 500;
            transition: 0.2s;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .list-group-item:hover {
            background-color: #e0e7ff;
            color: #1d4ed8;
        }

        .list-group-item.active {
            background-color: #2563eb !important;
            color: #fff !important;
            font-weight: 600;
        }

        .container {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            padding: 30px;
        }

        h2.text-center {
            color: #1f2937;
            font-weight: 600;
        }

        .table, .card {
            background-color: #fff;
        }

        html {
            scroll-behavior: smooth;
        }

        /* 🌟 tambahan kecil buat header profil */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2563eb;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 8px rgba(37,99,235,0.2);
        }

        .admin-header .welcome {
            font-weight: 500;
            font-size: 16px;
        }

        .admin-header .role {
            font-size: 14px;
            opacity: 0.9;
        }

/* 🌟 Styling tabel agar seragam dan proporsional */
.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 5px;
    font-size: 14px; /* lebih kecil sedikit dari default */
    background-color: transparent;
}

.table thead {
    background-color: #f8fafc;
    font-weight: 600;
    color: #111827;
}

.table thead th {
    border: none;
    padding: 10px 14px; /* sedikit lebih kecil dari sebelumnya */
    text-align: left;
}

.table tbody tr {
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    border-radius: 10px;
    transition: all 0.2s ease;
}

.table tbody td {
    border: none;
    padding: 10px 14px;
    vertical-align: middle;
    font-size: 14px;
}

.table tbody tr:hover {
    background-color: #f1f5ff;
    transform: translateY(-1px);
}

/* 🌟 Style tombol Edit & Hapus */
.btn-edit {
    background-color: #2563eb;
    color: #fff;
    border: none;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    transition: 0.2s;
}
.btn-edit:hover {
    background-color: #1d4ed8;
}

.btn-hapus {
    background-color: #ef4444;
    color: #fff;
    border: none;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    transition: 0.2s;
}
.btn-hapus:hover {
    background-color: #dc2626;
}

/* 🌟 Bungkus tabel biar kelihatan rapi */
.table-responsive {
    background-color: #fff;
    border-radius: 10px;
    padding: 6px 10px;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.03);
}

    </style>
</head>

<body>
<div class="container mt-4">
    <!-- ✅ Tambahan bagian nama admin -->
    <div class="admin-header">
        <div>
            <div class="welcome">👋 Halo, <strong><?= htmlspecialchars($data_admin['nama_admin'] ?? 'Admin') ?></strong></div>
            <div class="role">Dashboard Back-end Web</div>
        </div>
        <div>
            <i class="bi bi-person-circle fs-4"></i>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-sm-3 mb-3">
            <div class="list-group">
                <a class="list-group-item list-group-item-action <?= ($page == 'about') ? 'active' : '' ?>" href="index.php?page=about">About CampusCare</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'admin') ? 'active' : '' ?>" href="index.php?page=admin">Admin</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'artikel') ? 'active' : '' ?>" href="index.php?page=artikel">Artikel</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'booking_fasilitas') ? 'active' : '' ?>" href="index.php?page=booking_fasilitas">Booking Fasilitas</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'dosen_konselor') ? 'active' : '' ?>" href="index.php?page=dosen_konselor">Dosen Konselor</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'event') ? 'active' : '' ?>" href="index.php?page=event">Event</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'fasilitas') ? 'active' : '' ?>" href="index.php?page=fasilitas">Fasilitas</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'jadwal_konseling') ? 'active' : '' ?>" href="index.php?page=jadwal_konseling">Jadwal Konseling</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'laporan_pengaduan') ? 'active' : '' ?>" href="index.php?page=laporan_pengaduan">Laporan Pengaduan</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'mahasiswa') ? 'active' : '' ?>" href="index.php?page=mahasiswa">Mahasiswa</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'pendaftaran_event') ? 'active' : '' ?>" href="index.php?page=pendaftaran_event">Pendaftaran Event</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'permintaan_konseling') ? 'active' : '' ?>" href="index.php?page=permintaan_konseling">Permintaan Konseling</a>
                <a class="list-group-item list-group-item-action <?= ($page == 'riwayat.php') ? 'active' : '' ?>" href="index.php?page=riwayat.php">Riwayat</a>
                <a class="list-group-item list-group-item-action text-danger" href="logout.php">Logout</a>
            </div>
        </div>

        <div class="col-sm-9">
            <?php
            switch ($page) {
                default:
                case 'about': $file = $pageDir . "about.php"; break;
                case 'admin': $file = $pageDir . "admin.php"; break;
                case 'artikel': $file = $pageDir . "artikel.php"; break;
                case 'booking_fasilitas': $file = $pageDir . "booking_fasilitas.php"; break;
                case 'dosen': $file = $pageDir . "dosen.php"; break;
                case 'event': $file = $pageDir . "event.php"; break;
                case 'fasilitas': $file = $pageDir . "fasilitas.php"; break;
                case 'jadwal_konseling': $file = $pageDir . "jadwal_konseling.php"; break;
                case 'laporan_pengaduan': $file = $pageDir . "laporan_pengaduan.php"; break;
                case 'mahasiswa': $file = $pageDir . "mahasiswa.php"; break;
                case 'pendaftaran_event': $file = $pageDir . "pendaftaran_event.php"; break;
                case 'permintaan_konseling': $file = $pageDir . "permintaan_konseling.php"; break;
                case 'dosen_konselor': $file = $pageDir . "dosen_konselor.php"; break;
                case 'riwayat.php': $file = $pageDir . "riwayat.php"; break;
                case 'logout': $file = __DIR__ . "/../logout.php"; break;
            }

            if (file_exists($file)) {
                include $file;
            } else {
                echo "<div class='alert alert-danger text-center'>
                        <i class='bi bi-exclamation-triangle'></i> Halaman <strong>$page</strong> tidak ditemukan.
                      </div>";
            }
            ?>
        </div>
    </div>
</div>

<script src="../assets/jquery-3.6.0.min.js"></script>
<script src="../assets/bootstrap-5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script>
    if (document.getElementById('editor')) {
        CKEDITOR.replace('editor');
    }
</script>
</body>
</html>
