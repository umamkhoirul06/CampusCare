<?php
$host = "localhost";
$user = "root"; // ganti sesuai XAMPP kamu
$pass = "";
$db   = "campus_care2"; // nama database kamu
$port = 3307; // PORT BARU YANG DITAMBAHKAN

// Menambahkan $port sebagai parameter kelima
$koneksi = mysqli_connect($host, $user, $pass, $db, $port);
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
