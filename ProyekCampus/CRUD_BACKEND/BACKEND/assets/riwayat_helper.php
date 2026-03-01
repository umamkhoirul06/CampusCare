<?php
if (session_status() == PHP_SESSION_NONE) session_start();

function catatOtomatis($koneksi, $sql) {
    if (!isset($_SESSION['nama']) || !isset($_SESSION['role'])) return;

    $nama = $_SESSION['nama'];
    $role = $_SESSION['role'];

    // Ambil jenis aksi dari query
    $aksi = strtoupper(strtok(trim($sql), ' '));
    $aktivitas = '';

    if ($aksi === 'INSERT') {
        $aktivitas = "Menambahkan data baru";
    } elseif ($aksi === 'UPDATE') {
        $aktivitas = "Memperbarui data";
    } elseif ($aksi === 'DELETE') {
        $aktivitas = "Menghapus data";
    }

    if ($aktivitas !== '') {
        $stmt = $koneksi->prepare("INSERT INTO riwayat (nama, role, aktivitas) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $role, $aktivitas);
        $stmt->execute();
        $stmt->close();
    }
}
?>
