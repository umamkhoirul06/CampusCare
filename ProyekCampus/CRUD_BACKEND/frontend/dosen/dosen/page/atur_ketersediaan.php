<?php
include '../../koneksi.php';
session_start();

// Cek login dosen
if (!isset($_SESSION['dosen'])) {
    header("Location: ../../login.php");
    exit;
}

// Ambil data dosen
$dosen = $_SESSION['dosen'];
$nama_dosen = $dosen['nama_lengkap'] ?? "Dosen Konselor";
$id_konselor = $dosen['id_konselor'];

// PROSES SIMPAN SLOT KETERSEDIAAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_slot'])) {

    $hari = $_POST['hari'];
    $mulai = $_POST['waktu_mulai'];
    $selesai = $_POST['waktu_selesai'];
    $kuota = $_POST['kuota_harian'];

    $insert = "INSERT INTO jadwal_konseling (id_konselor, hari, waktu_mulai, waktu_selesai, kuota_harian)
               VALUES ('$id_konselor', '$hari', '$mulai', '$selesai', '$kuota')";

    if (mysqli_query($conn, $insert)) {
        echo "<script>alert('Slot berhasil ditambahkan!'); window.location='atur_ketersediaan.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambah slot!');</script>";
    }
}

// AMBIL JADWAL DOSEN
$sql = "SELECT * FROM jadwal_konseling
        WHERE id_konselor = '$id_konselor'
        ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat'), waktu_mulai";

$result = mysqli_query($conn, $sql);

$jadwal = [];
while ($row = mysqli_fetch_assoc($result)) {
    $jadwal[$row['hari']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Atur Ketersediaan - Panel Dosen</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
body { margin:0; padding:0; display:flex; font-family:Poppins,sans-serif; background:#f7f8fa; }

/* MAIN */
.main-content { flex:1; margin-left:250px; padding:30px; }
.box { background:white; padding:25px; border-radius:12px; margin-bottom:25px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }

.btn-add { background:#2563eb; color:white; padding:8px 16px; border-radius:8px; border:none; cursor:pointer; }
.btn-add:hover { background:#1d4ed8; }

.day { margin-bottom:20px; }
.slot {
    padding:8px 14px; background:#dbeafe; border-radius:10px; display:inline-block;
    margin:4px 4px; font-size:13px; color:#1e40af;
}
</style>
</head>
<body>

<?php include '../../sidebar.php'; ?>

<div class="main-content">

    <div class="header">
        <h2>Atur Ketersediaan Konseling</h2>
        <p>Tambahkan slot waktu dan kuota harian Anda.</p>

        <button class="btn-add" onclick="document.getElementById('formSlot').style.display='block'">
            <i class="fa fa-plus"></i> Tambah Slot Waktu
        </button>
    </div>

    <!-- FORM TAMBAH SLOT -->
    <div id="formSlot" class="box" style="display:none;">
        <h4>Tambah Slot Ketersediaan</h4>

        <form method="POST">

            <label>Hari</label>
            <select name="hari" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
                <option value="">-- Pilih Hari --</option>
                <option>Senin</option>
                <option>Selasa</option>
                <option>Rabu</option>
                <option>Kamis</option>
                <option>Jumat</option>
            </select>

            <label style="margin-top:10px;">Waktu Mulai</label>
            <input type="time" name="waktu_mulai" required
                style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">

            <label style="margin-top:10px;">Waktu Selesai</label>
            <input type="time" name="waktu_selesai" required
                style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">

            <label style="margin-top:10px;">Kuota Harian</label>
            <input type="number" name="kuota_harian" min="1" required
                style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">

            <button type="submit" name="tambah_slot" class="btn-add" style="margin-top:15px;">
                <i class="fa fa-save"></i> Simpan Slot
            </button>

        </form>
    </div>

    <!-- LIST JADWAL -->
    <div class="box">
        <h4>Jadwal Ketersediaan</h4>

        <?php
        $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat'];
        foreach ($hariList as $hari):
        ?>
            <div class="day">
                <h4><?= $hari ?></h4>
                <?php if (!empty($jadwal[$hari])): ?>
                    <?php foreach ($jadwal[$hari] as $slot): ?>
                        <div class="slot">
                            <?= date('H:i', strtotime($slot['waktu_mulai'])) ?> -
                            <?= date('H:i', strtotime($slot['waktu_selesai'])) ?>
                            | Kuota: <?= $slot['kuota_harian'] ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#999;font-size:13px;">Belum ada slot.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    </div>

</div>

</body>
</html>

<?php mysqli_close($conn); ?>
