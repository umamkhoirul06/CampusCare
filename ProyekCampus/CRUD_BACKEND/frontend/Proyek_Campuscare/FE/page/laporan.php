<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

$id_mahasiswa = $_SESSION['id_mahasiswa'] ?? 0;

// Daftar Kategori Laporan
$kategori_laporan = [
    "Fasilitas Kampus",
    "Pelayanan Akademik",
    "Dosen/Staf",
    "Lingkungan Kampus",
    "Lainnya"
];

// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_laporan_marker'])) {

    if ($id_mahasiswa == 0) {
        $error_msg = 'Akses ditolak. Anda harus login untuk mengirim laporan.';
    } else {
        $kategori   = mysqli_real_escape_string($koneksi, $_POST['kategori'] ?? '');
        $judul      = mysqli_real_escape_string($koneksi, $_POST['judul'] ?? '');
        $deskripsi  = mysqli_real_escape_string($koneksi, $_POST['deskripsi'] ?? '');
        $lokasi     = mysqli_real_escape_string($koneksi, $_POST['lokasi'] ?? '');
        $is_anonim  = isset($_POST['is_anonim']) ? '1' : '0';
        $status     = 'Baru'; // 🟢 Perbaikan status default
        $created_at = date('Y-m-d H:i:s');

        if (empty($kategori) || empty($judul) || empty($deskripsi)) {
            $error_msg = "Kategori, Judul, dan Deskripsi harus diisi.";
        } else {
            $query_insert = "
                INSERT INTO laporan_pengaduan (
                    id_mahasiswa, kategori, judul, deskripsi, lokasi, is_anonim, status, created_at
                ) VALUES (
                    '$id_mahasiswa', '$kategori', '$judul', '$deskripsi', '$lokasi', '$is_anonim', '$status', '$created_at'
                )
            ";
            $detail = "Topik: $judul, Dibuat: $created_at";
    $sql_riwayat = "INSERT INTO riwayat_layanan (id_user, nama_user, role_user, jenis, detail)
                    VALUES ('$id_mahasiswa', '$nama', 'mahasiswa', 'Laporan', '$detail')";
    mysqli_query($koneksi, $sql_riwayat);
            if (mysqli_query($koneksi, $query_insert)) {
                echo "<script>
                    Swal.fire({
                        icon:'success',
                        title:'Laporan terkirim!',
                        text:'Terima kasih telah melaporkan. Status laporan saat ini: Baru.',
                        confirmButtonText:'OK'
                    }).then(()=> window.location.href = window.location.href);
                </script>";
            } else {
                $error_msg = 'Gagal menyimpan laporan: ' . mysqli_error($koneksi);
            }
        }
    }

    if (isset($error_msg)) {
        echo "<script>
            Swal.fire({
                icon:'error',
                title:'Gagal',
                text:'" . addslashes($error_msg) . "'
            });
        </script>";
    }
}

// ----------------------------------------------------------------
// --- AMBIL DATA LAPORAN ---
// ----------------------------------------------------------------
$query = "SELECT * FROM laporan_pengaduan WHERE id_mahasiswa='$id_mahasiswa' ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan & Pengaduan - Campus Care</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* === GAYA ASLI (JANGAN UBAH) === */
.content {
    margin-left: 260px;
    padding: 30px;
    width: calc(100% - 260px);
}
.content h2 { font-size:22px; font-weight:700; color:#222; margin-bottom:5px; }
.content p.subtitle { color:#666; margin-bottom:20px; }

.card {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
.card h4 { margin:0; }
.card .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }

.btn-primary {
    background:#2563eb; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;
}
.btn-primary:hover { background:#1d4ed8; }

table { width:100%; border-collapse:collapse; font-size:14px; }
th, td { padding:10px; border:1px solid #ddd; text-align:center; }
.status-menunggu { color:#d97706; font-weight:600; }
.status-diproses { color:#2563eb; font-weight:600; }
.status-selesai { color:#16a34a; font-weight:600; }

.modal {
    display:none; position:fixed; left:0; top:0; width:100%; height:100%;
    background:rgba(0,0,0,0.4); justify-content:center; align-items:center; z-index:1000;
}
.modal-content {
    background:white; border-radius:10px; width:450px; padding:25px; position:relative;
}
.modal-content h3 { margin-top: 0; font-size: 18px; font-weight: 600; }
.close-btn { position:absolute; top:12px; right:20px; font-size:22px; cursor:pointer; }
.modal-content form label {
    display:block; margin-top:15px; margin-bottom:5px;
    font-weight:600; color:#444; font-size:14px;
}
.modal-content form select,
.modal-content form input[type=text],
.modal-content form textarea {
    width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; font-size:14px;
}
.modal-content form textarea { resize:vertical; height:80px; }

.checkbox-anonim { display:flex; align-items:center; margin-top:15px; font-weight:400 !important; }
.checkbox-anonim input[type=checkbox] { margin-right:8px; }

.detail-item { margin-bottom:10px; }
.detail-item span { font-weight:600; display:inline-block; width:120px; color:#333; }
</style>
</head>
<body>

<div class="content">
    <h2>Laporan & Pengaduan</h2>
    <p class="subtitle">Sampaikan aspirasi atau keluhan Anda.</p>

    <div class="card">
        <div class="header">
            <h4>Daftar Laporan Saya</h4>
            <button class="btn-primary" id="openModal">+ Buat Laporan</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php 
                        $statusClass = match($row['status']) {
                            'Menunggu' => 'status-menunggu',
                            'Diproses' => 'status-diproses',
                            'Selesai' => 'status-selesai',
                            default => ''
                        };
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td><?= htmlspecialchars($row['judul']); ?></td>
                            <td class="<?= $statusClass; ?>"><?= htmlspecialchars($row['status']); ?></td>
                            <td>
                                <button class="btn-primary btn-detail"
                                    data-judul="<?= htmlspecialchars($row['judul']); ?>"
                                    data-kategori="<?= htmlspecialchars($row['kategori']); ?>"
                                    data-deskripsi="<?= htmlspecialchars($row['deskripsi']); ?>"
                                    data-lokasi="<?= htmlspecialchars($row['lokasi']); ?>"
                                    data-status="<?= htmlspecialchars($row['status']); ?>"
                                    data-waktu="<?= date('d/m/Y H:i', strtotime($row['created_at'])); ?>"
                                    data-anonim="<?= $row['is_anonim'] == '1' ? 'Ya' : 'Tidak'; ?>"
                                >Detail</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Belum ada laporan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal" id="modalLaporan">
    <div class="modal-content">
        <span class="close-btn" id="closeModal">&times;</span>
        <h3>Buat Laporan Baru</h3>

        <form method="POST" action="">
            <label for="kategori">Kategori Laporan</label>
            <select name="kategori" id="kategori" required>
                <option value="">Pilih Kategori</option>
                <?php foreach ($kategori_laporan as $kategori): ?>
                    <option value="<?= htmlspecialchars($kategori); ?>"><?= htmlspecialchars($kategori); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="judul">Judul Laporan</label>
            <input type="text" name="judul" id="judul" placeholder="Judul singkat laporan" required>

            <label for="deskripsi">Deskripsi Laporan</label>
            <textarea name="deskripsi" id="deskripsi" placeholder="Jelaskan laporan secara detail..." required></textarea>

            <label for="lokasi">Lokasi Laporan</label>
            <input type="text" name="lokasi" id="lokasi" placeholder="Contoh: Gedung A Lantai 2">

            <label class="checkbox-anonim">
                <input type="checkbox" name="is_anonim"> Laporkan secara anonim (identitas tidak akan ditampilkan)
            </label>

            <input type="hidden" name="kirim_laporan_marker" value="1">
            <button type="submit" class="btn-primary" style="margin-top:20px;width:100%;">Kirim Laporan</button>
        </form>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal" id="modalDetail">
    <div class="modal-content">
        <span class="close-btn" id="closeDetail">&times;</span>
        <h3>Detail Laporan</h3>
        <div class="detail-item"><span>Judul:</span> <p id="d_judul"></p></div>
        <div class="detail-item"><span>Kategori:</span> <p id="d_kategori"></p></div>
        <div class="detail-item"><span>Deskripsi:</span> <p id="d_deskripsi"></p></div>
        <div class="detail-item"><span>Lokasi:</span> <p id="d_lokasi"></p></div>
        <div class="detail-item"><span>Status:</span> <p id="d_status"></p></div>
        <div class="detail-item"><span>Dibuat:</span> <p id="d_waktu"></p></div>
        <div class="detail-item"><span>Anonim:</span> <p id="d_anonim"></p></div>
    </div>
</div>

<script>
const modal = document.getElementById('modalLaporan');
const modalDetail = document.getElementById('modalDetail');

document.getElementById('openModal').onclick = () => modal.style.display = 'flex';
document.getElementById('closeModal').onclick = () => modal.style.display = 'none';
document.getElementById('closeDetail').onclick = () => modalDetail.style.display = 'none';
window.onclick = e => { if(e.target==modal) modal.style.display='none'; if(e.target==modalDetail) modalDetail.style.display='none'; };

document.querySelectorAll('.btn-detail').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        document.getElementById('d_judul').textContent = btn.dataset.judul;
        document.getElementById('d_kategori').textContent = btn.dataset.kategori;
        document.getElementById('d_deskripsi').textContent = btn.dataset.deskripsi;
        document.getElementById('d_lokasi').textContent = btn.dataset.lokasi || '-';
        document.getElementById('d_status').textContent = btn.dataset.status;
        document.getElementById('d_waktu').textContent = btn.dataset.waktu;
        document.getElementById('d_anonim').textContent = btn.dataset.anonim;
        modalDetail.style.display = 'flex';
    });
});
</script>
<script>
function confirmLogout() {
  if (confirm("Yakin ingin logout?")) {
    window.location.href = "../../logout.php";
  }
}
</script>
</body>
</html>
