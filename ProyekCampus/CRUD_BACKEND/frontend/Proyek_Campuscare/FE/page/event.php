<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Jika tombol daftar diklik
if (isset($_POST['daftar'])) {
    $id_event = $_POST['id_event'];

    $cek = mysqli_query($koneksi, "SELECT * FROM pendaftaran_event 
                                   WHERE id_event='$id_event' 
                                   AND id_mahasiswa='$id_mahasiswa'");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: event.php?status=duplikat");
        exit;
    }

    // 1. Simpan ke pendaftaran_event
    $sql = "INSERT INTO pendaftaran_event (id_event, id_mahasiswa, status_hadir, registered_at)
            VALUES ('$id_event', '$id_mahasiswa', 0, NOW())";

    if (mysqli_query($koneksi, $sql)) {
        // 2. Ambil data event untuk detail riwayat
        $event = mysqli_fetch_assoc(mysqli_query($koneksi, 
            "SELECT judul, tanggal_mulai, tanggal_selesai 
             FROM event WHERE id_event='$id_event'"));

        $detail = "Event: {$event['judul']}, Pada: {$event['tanggal_mulai']} - {$event['tanggal_selesai']}";

        // 3. Simpan ke riwayat_layanan
        $sql_riwayat = "INSERT INTO riwayat_layanan (id_user, nama_user, role_user, jenis, detail)
                        VALUES ('$id_mahasiswa', '$nama', 'mahasiswa', 'Event', '$detail')";
        mysqli_query($koneksi, $sql_riwayat);

        // 4. Trigger SweetAlert agar muncul di browser
         header("Location: event.php?status=sukses");
        exit;
    }
}

// Ambil semua event untuk ditampilkan
$query = "
SELECT 
    e.*,
    e.kuota - COUNT(p.id_mahasiswa) AS sisa_kuota
FROM event e
LEFT JOIN pendaftaran_event p ON e.id_event = p.id_event
GROUP BY e.id_event
ORDER BY e.tanggal_mulai DESC
";

$result = mysqli_query($koneksi, $query);
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event & Kegiatan - Campus Care</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
    .content h2 { font-size: 22px; font-weight: 700; color: #222; }
    .content p { color: #666; margin-top: 5px; margin-bottom: 20px; }

    /* Grid 3 kolom + scroll vertikal */
    .event-container { 
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3 kolom per baris */
        gap: 20px;
        max-height: 600px; /* tinggi container, bisa disesuaikan */
        overflow-y: auto; /* scroll ke bawah */
        padding-right: 10px;
    }

    .event-card { 
        background: white; 
        border-radius: 10px; 
        box-shadow: 0 2px 6px rgba(0,0,0,0.1); 
        padding: 20px; 
    }
    .event-card h3 { font-size: 16px; font-weight: 600; margin-bottom: 10px; }
    .event-card p { font-size: 13px; color: #555; margin: 5px 0; }
    .btn-primary { background-color: #0066ff; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: 0.2s; margin-top: 10px; font-size: 13px; }
    .btn-primary:hover { background-color: #004cd6; }

    .event-container::-webkit-scrollbar { width: 6px; }
    .event-container::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }

    /* Responsif mobile: 1 kolom */
    @media (max-width: 900px) {
        .event-container { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

<!-- Content -->
<div class="content">
    <h2>Event & Kegiatan</h2>
    <p>Lihat dan daftar event kampus</p>

    <div class="event-container">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="event-card">
            <h3><?= htmlspecialchars($row['judul']); ?></h3>
            <p>🗓️ <?= date('d M Y', strtotime($row['tanggal_mulai'])); ?> - <?= date('d M Y', strtotime($row['tanggal_selesai'])); ?></p>
            <p>📍 <?= htmlspecialchars($row['lokasi']); ?></p>
            <p>👥 Kuota tersisa: 
    <?= max(0, $row['sisa_kuota']); ?> / <?= $row['kuota']; ?>
</p>

            <p>📖 Status: <b><?= htmlspecialchars($row['status']); ?></b></p>

            <!-- ✅ Form yang benar untuk mengirim data -->
            <form method="POST">
    <input type="hidden" name="id_event" value="<?= $row['id_event']; ?>">
    <button type="submit" name="daftar" class="btn-primary"
        <?= ($row['sisa_kuota'] <= 0) ? 'disabled style="background:#ccc;cursor:not-allowed;"' : ''; ?>>
        <?= ($row['sisa_kuota'] <= 0) ? 'Penuh' : 'Daftar Event'; ?>
    </button>
</form>

        </div>
    <?php endwhile; ?>
</div>

</div>
<?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'Kamu berhasil mendaftar dan riwayat tercatat.',
    confirmButtonColor: '#0066ff'
});
</script>
<?php endif; ?>

<?php if (isset($_GET['status']) && $_GET['status'] == 'duplikat'): ?>
<script>
Swal.fire({
    icon: 'warning',
    title: 'Gagal!',
    text: 'Kamu sudah pernah mendaftar event ini.',
    confirmButtonColor: '#f39c12'
});
</script>
<?php endif; ?>

<script>
function confirmLogout() {
    if (confirm("Yakin ingin logout?")) {
        window.location.href = "../../logout.php";
    }
}
</script>

</body>
</html>
