<?php
session_start();
// Pastikan path koneksi dan sidebar benar
include '../../koneksi.php'; 
include '../../sidebar.php'; 


// Hati-hati: $koneksi harus diubah menjadi $conn jika di koneksi.php Anda menggunakan $conn
// Namun, karena di kode Anda menggunakan $koneksi, saya pertahankan $koneksi
$db_koneksi = isset($koneksi) ? $koneksi : (isset($conn) ? $conn : null);

if (!$db_koneksi) {
    die("Koneksi database belum didefinisikan.");
}


// Asumsi ID Mahasiswa diambil dari session
$id_mahasiswa = $_SESSION['id_mahasiswa'] ?? 1;
// Asumsi nama mahasiswa disimpan di session['nama']
$nama_user = $_SESSION['nama'] ?? 'Mahasiswa';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'booking') {
    $id_fasilitas = $_POST['fasilitas'];
    $tanggal      = $_POST['tanggal'];
    $mulai        = $_POST['mulai'];
    $selesai      = $_POST['selesai'];
    $keperluan    = $_POST['keperluan'];

    if ($id_fasilitas && $tanggal && $mulai && $selesai && $keperluan) {
        $tgl_mulai   = $tanggal . " " . $mulai;
        $tgl_selesai = $tanggal . " " . $selesai;

        // SANITASI INPUT (PENTING untuk keamanan SQL Injection)
        $id_fasilitas = mysqli_real_escape_string($db_koneksi, $id_fasilitas);
        $tgl_mulai = mysqli_real_escape_string($db_koneksi, $tgl_mulai);
        $tgl_selesai = mysqli_real_escape_string($db_koneksi, $tgl_selesai);
        $keperluan = mysqli_real_escape_string($db_koneksi, $keperluan);
        $id_mahasiswa = mysqli_real_escape_string($db_koneksi, $id_mahasiswa);
        $nama_user_esc = mysqli_real_escape_string($db_koneksi, $nama_user);
        
        $query = "INSERT INTO booking_fasilitas 
                      (id_mahasiswa, id_fasilitas, tanggal_mulai, tanggal_selesai, keperluan, status, created_at)
                  VALUES 
                      ('$id_mahasiswa', '$id_fasilitas', '$tgl_mulai', '$tgl_selesai', '$keperluan', 'pending', NOW())";

        // Ambil nama fasilitas berdasarkan id_fasilitas
        $getNama = mysqli_query($db_koneksi, "SELECT nama_fasilitas FROM fasilitas WHERE id_fasilitas = '$id_fasilitas'");
        $nama_fasilitas = '';
        if ($getNama && mysqli_num_rows($getNama) > 0) {
            $data_fas = mysqli_fetch_assoc($getNama);
            $nama_fasilitas = $data_fas['nama_fasilitas'];
        }

        // Buat detail riwayat
        $detail = "Fasilitas: " . mysqli_real_escape_string($db_koneksi, $nama_fasilitas) . ", Tanggal: " . mysqli_real_escape_string($db_koneksi, $tanggal);

        // Simpan ke riwayat_layanan
        $sql_riwayat = "INSERT INTO riwayat_layanan (id_user, nama_user, role_user, jenis, detail)
                        VALUES ('$id_mahasiswa', '$nama_user_esc', 'mahasiswa', 'Fasilitas', '$detail')";
        mysqli_query($db_koneksi, $sql_riwayat);

        if (mysqli_query($db_koneksi, $query)) {
            echo "<script>alert('Booking fasilitas berhasil!'); window.location.href='booking_fasilitas.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal menyimpan: " . mysqli_error($db_koneksi) . "');</script>";
        }
    } else {
        echo "<script>alert('Harap lengkapi semua data!');</script>";
    }
}


$booking_fasilitas = [];
$sql = "SELECT bf.id_booking, bf.tanggal_mulai, bf.tanggal_selesai, bf.keperluan, bf.status,
                f.nama_fasilitas
        FROM booking_fasilitas bf
        JOIN fasilitas f ON bf.id_fasilitas = f.id_fasilitas
        WHERE bf.id_mahasiswa = '$id_mahasiswa'
        ORDER BY bf.tanggal_mulai DESC";
$q = mysqli_query($db_koneksi, $sql);
while ($r = mysqli_fetch_assoc($q)) {
    $booking_fasilitas[] = $r;
}


$fasilitas = [];
$q2 = mysqli_query($db_koneksi, "
    SELECT 
        f.id_fasilitas,
        f.nama_fasilitas,
        f.kapasitas,
        (f.kapasitas - COUNT(bf.id_booking)) AS sisa_kapasitas
    FROM fasilitas f
    LEFT JOIN booking_fasilitas bf 
        ON f.id_fasilitas = bf.id_fasilitas 
        AND bf.status IN ('disetujui', 'pending')
    GROUP BY f.id_fasilitas
");
$fasilitas = [];
while ($r2 = mysqli_fetch_assoc($q2)) {
    $fasilitas[] = $r2;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Fasilitas - Campus Care</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    /* CSS Styling */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background-color: #f3f4f6; display: flex; }
    .content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }

    /* CARD & TOMBOL */
    .card { background: white; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 20px; }
    .card-header { display: flex; justify-content: space-between; align-items: center; }
    .btn-primary { background-color: #0066ff; color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; transition: 0.2s; }
    .btn-primary:hover { background-color: #0052cc; }

    /* TABLE */
    table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 15px; }
    thead { background-color: #f1f1f1; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    td.center { text-align: center; color: #888; }
    
    /* Warna status */
    .status-pending { 
    color: #d97706;          /* Kuning / Orange */
    font-weight: 600; 
    }
    .status-disetujui { 
    color: #16a34a;          /* Hijau */
    font-weight: 600; 
    }
    .status-ditolak { 
    color: #dc2626;          /* Merah */
    font-weight: 600; 
    }

    /* MODAL */
    .modal { display: none; position: fixed; z-index: 10; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; }
    .modal-content { background:white; padding:25px 30px; width:480px; border-radius:10px; position:relative; }
    .modal-content h3 { font-size:14px; font-weight:600; margin-bottom:15px; }

    /* CLOSE BUTTON */
    .close { position:absolute; top:12px; right:18px; font-size:22px; cursor:pointer; color:#666; }
    .close:hover { color: red; }

    /* FORM */
    form label { display:block; margin-top:12px; font-weight:500; font-size:14px; }
    form select, form input, form textarea {
        width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;
        margin-top:5px; font-size:14px;
    }
    form textarea { resize: none; height:70px; }

    /* FORM 2 Kolom Untuk Tanggal dan Waktu */
    .form-row { display:flex; gap:10px; }
    .form-row .col { flex:1; }

    /* TOMBOL SUBMIT */
    .btn-submit { width:100%; background:#0066ff; color:white; margin-top:18px;
                  padding:10px; border:none; border-radius:6px; cursor:pointer; }
    .btn-submit:hover { background:#0052cc; }
</style>
</head>
<body>

<div class="content">
    <h2>Booking Fasilitas</h2>
    <p>Booking ruang dan fasilitas kampus</p>

    <div class="card">
        <div class="card-header">
            <h4>Booking Fasilitas Saya</h4>
            <button class="btn-primary" id="openModal">+ Booking Fasilitas</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Fasilitas</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($booking_fasilitas)): ?>
                    <tr><td colspan="5" class="center">Belum ada booking fasilitas</td></tr>
                <?php else: ?>
                    <?php foreach ($booking_fasilitas as $b): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($b['tanggal_mulai'])); ?></td>
                            <td><?= date('H:i', strtotime($b['tanggal_mulai'])) . " - " . date('H:i', strtotime($b['tanggal_selesai'])); ?></td>
                            <td><?= htmlspecialchars($b['nama_fasilitas']); ?></td>
                            <td><?= htmlspecialchars($b['keperluan']); ?></td>
                            <?php
                            $statusClass = '';
                            if ($b['status'] == 'pending') {
                                $statusClass = 'status-pending';
                            } elseif ($b['status'] == 'disetujui') {
                                $statusClass = 'status-disetujui';
                            } elseif ($b['status'] == 'ditolak') {
                                $statusClass = 'status-ditolak';
                            }?>
                            <td class="<?= $statusClass; ?>"><?= ucfirst(htmlspecialchars($b['status'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="modal" id="bookingModal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h3>Ajukan Booking Fasilitas</h3>

        <form method="POST">
            <input type="hidden" name="aksi" value="booking">

            <label>Pilih Fasilitas</label>
            <select name="fasilitas" required>
                <option value="">-- Pilih Fasilitas --</option>
                <?php foreach ($fasilitas as $f): ?>
                    <?php 
                        $sisa = max(0, $f['sisa_kapasitas']); 
                        $nama = $f['nama_fasilitas']; 
                        $label = $sisa > 0 
                            ? "$nama (sisa $sisa)" 
                            : "$nama (penuh)";
                    ?>
                    <option value="<?= $f['id_fasilitas']; ?>" <?= $sisa == 0 ? "disabled" : ""; ?>>
                        <?= $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>


            <label>Tanggal Booking</label>
            <input type="date" name="tanggal" id="tanggalBooking" required>

            <div class="form-row">
                <div class="col">
                    <label>Waktu Mulai</label>
                    <input type="time" name="mulai" required>
                </div>
                <div class="col">
                    <label>Waktu Selesai</label>
                    <input type="time" name="selesai" required>
                </div>
            </div>

            <label>Keperluan</label>
            <textarea name="keperluan" placeholder="Contoh: Rapat organisasi, kegiatan UKM..." required></textarea>

            <button type="submit" class="btn-submit">Ajukan Booking</button>
        </form>
    </div>
</div>

<script>
// Fungsi untuk mendapatkan tanggal hari ini dalam format YYYY-MM-DD
function getTodayDate() {
    const today = new Date();
    const year = today.getFullYear();
    // Bulan dan hari harus memiliki dua digit (misal: 01, 09)
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

document.addEventListener('DOMContentLoaded', function() {
    const inputTanggal = document.getElementById('tanggalBooking');
    // Set atribut 'min' pada input tanggal agar tidak bisa memilih tanggal lalu
    if (inputTanggal) {
        inputTanggal.min = getTodayDate();
    }
});

// Logika Modal
const modal = document.getElementById('bookingModal');
document.getElementById('openModal').onclick = () => modal.style.display = 'flex';
document.getElementById('closeModal').onclick = () => modal.style.display = 'none';
window.onclick = e => { if (e.target == modal) modal.style.display = 'none'; };

function confirmLogout() {
    if (confirm("Yakin ingin logout?")) {
        window.location.href = "../../logout.php";
    }
}
</script>

</body>
</html>