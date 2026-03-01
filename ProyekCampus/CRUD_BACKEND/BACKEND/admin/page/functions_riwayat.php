<?php
function tambahRiwayat($koneksi, $nama, $role, $aktivitas) {
    $sql = "INSERT INTO riwayat (nama, role, aktivitas) VALUES (?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("sss", $nama, $role, $aktivitas);
    $stmt->execute();
}