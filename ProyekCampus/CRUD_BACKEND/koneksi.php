<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "campus_care2";
$port = 3306;

// --- Koneksi database MySQLi (Object-Oriented) ---
$koneksi = new mysqli($host, $user, $pass, $db, $port);

// Cek koneksi
if ($koneksi->connect_error) {
    // Tampilkan error dan hentikan skrip
    die("Koneksi database gagal (Port: $port): " . $koneksi->connect_error);
}


// --- Include Helper (Asumsi riwayat_helper.php sudah didefinisikan) ---
// Pastikan path ini benar dari lokasi file koneksi.php
$helperPath = __DIR__ . '/BACKEND/assets/riwayat_helper.php'; 

if (file_exists($helperPath)) {
    include_once($helperPath);
} else {
    // Ini hanya warning, koneksi tetap berjalan
    error_log("File riwayat_helper.php tidak ditemukan di: " . $helperPath);
    // echo "<p style='color:red'>⚠️ File riwayat_helper.php tidak ditemukan.</p>"; 
}

