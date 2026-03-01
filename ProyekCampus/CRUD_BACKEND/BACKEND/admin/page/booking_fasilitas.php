<?php
include_once(__DIR__ . '/../../../koneksi.php');

$action = $_GET['action'] ?? 'list'; 

// >>> PENTING: ID INI WAJIB ADA DI TABEL dosen_konselor AGAR FOREIGN KEY TIDAK ERROR <<<
$admin_id = 412; 


// ----------------------
// LOGIKA VERIFIKASI (Setujui/Tolak) - FUNGSI INI TETAP BERJALAN DENGAN ID 412
// ----------------------
if (isset($_GET['action'], $_GET['id']) && in_array($_GET['action'], ['setujui', 'tolak'])) {
    $id = (int)$_GET['id'];
    $new_status = ($_GET['action'] == 'setujui') ? 'Disetujui' : 'Ditolak';
    $new_status_safe = mysqli_real_escape_string($koneksi, $new_status);
    $alert_msg = ($_GET['action'] == 'setujui') ? 'disetujui' : 'ditolak';

    $update_query = "UPDATE booking_fasilitas 
                    SET status='$new_status_safe', admin_verifikasi='$admin_id' 
                    WHERE id_booking=$id AND status='Pending'";
                    
    if (mysqli_query($koneksi, $update_query)) {
        if (mysqli_affected_rows($koneksi) > 0) {
            echo "<script>alert('Booking berhasil $alert_msg!'); window.location='index.php?page=booking_fasilitas';</script>";
        } else {
            echo "<script>alert('Gagal! Booking sudah diverifikasi atau ID tidak ditemukan.'); window.location='index.php?page=booking_fasilitas';</script>";
        }
    } else {
        die("QUERY FAILED! Error saat memproses Setujui/Tolak. Detail: " . mysqli_error($koneksi) . " | Query: " . $update_query);
    }
    exit;
}


// ----------------------
// LOGIKA DELETE
// ----------------------
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM booking_fasilitas WHERE id_booking=$id");
    echo "<script>alert('Data berhasil dihapus!'); window.location='index.php?page=booking_fasilitas';</script>";
    exit;
}

$action = 'list'; 

$mahasiswa = mysqli_query($koneksi, "SELECT * FROM mahasiswa ORDER BY nama_lengkap ASC");
$fasilitas = mysqli_query($koneksi, "SELECT * FROM fasilitas ORDER BY nama_fasilitas ASC");
?>

<h2 class="mb-4">Manajemen Booking Fasilitas</h2>

<?php if ($action == 'list'): ?>

<?php
$fasilitas_filter = $_GET['fasilitas'] ?? '';
$where = "1=1";

if (!empty($fasilitas_filter)) {
    $fasilitas_filter = (int)$fasilitas_filter;
    $where .= " AND b.id_fasilitas = $fasilitas_filter";
}

$data = mysqli_query($koneksi, "
    SELECT b.*, m.nama_lengkap AS nama_mahasiswa, f.nama_fasilitas, k.nama_lengkap AS admin
    FROM booking_fasilitas b
    JOIN mahasiswa m ON b.id_mahasiswa = m.id_mahasiswa
    JOIN fasilitas f ON b.id_fasilitas = f.id_fasilitas
    LEFT JOIN dosen_konselor k ON b.admin_verifikasi = k.id_konselor
    WHERE $where
    ORDER BY b.created_at DESC
");
?>

<div class="table-responsive">
    <div class="card mb-4">
    <div class="card-body">

        <form method="GET" action="page/report_fasilitas.php" class="row g-3">

    <div class="col-md-3">
        <label class="form-label fw-bold">Rentang Waktu</label>
        <select name="range" id="select-range" class="form-select">
            <option value="">Custom</option>
            <option value="1">1 Bulan Terakhir</option>
            <option value="3">3 Bulan Terakhir</option>
            <option value="6">6 Bulan Terakhir</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Tanggal Mulai</label>
        <input type="date" name="start" id="input-start" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Tanggal Selesai</label>
        <input type="date" name="end" id="input-end" class="form-control">
    </div>
<div class="col-md-3">
    <label class="form-label fw-bold">Fasilitas</label>
    <select name="fasilitas" class="form-select">
        <option value="">Semua Fasilitas</option>
        <?php while ($f = mysqli_fetch_assoc($fasilitas)): ?>
            <option value="<?= $f['id_fasilitas']; ?>"
                <?= (($_GET['fasilitas'] ?? '') == $f['id_fasilitas']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($f['nama_fasilitas']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Format Laporan</label>
        <select name="format" class="form-select" required>
            <option value="csv">Spreadsheet (.csv)</option>
            <option value="excel">Excel (.xlsx)</option>
            <option value="word">Word (.docx)</option>
        </select>
    </div>

    <div class="col-md-12 text-end">
        <button type="submit" class="btn btn-primary">
            Download Laporan
        </button>
    </div>

</form>

<script>
document.getElementById("select-range").addEventListener("change", function () {
    let range = this.value;
    let start = document.getElementById("input-start");
    let end = document.getElementById("input-end");

    if (range !== "") {
        start.disabled = true;
        end.disabled = true;
        start.value = "";
        end.value = "";
    } else {
        start.disabled = false;
        end.disabled = false;
    }
});
</script>

    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Booking</h5>
    </div>
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="5%">No</th>
            <th>Mahasiswa</th>
            <th>Fasilitas</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Keperluan</th>
            <th>Status</th>
            <th>Admin Verifikasi</th>
            <th width="20%">Aksi</th> </tr>
    </thead>
    <tbody>
        <?php $no=1; while($d=mysqli_fetch_assoc($data)): 
            $status = strtolower(trim($d['status'] ?? 'pending'));
            
            $badge_class = 'bg-warning text-dark';
            if ($status == 'disetujui') {
                $badge_class = 'bg-success';
            } elseif ($status == 'ditolak') {
                $badge_class = 'bg-danger';
            }
        ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($d['nama_mahasiswa']); ?></td>
            <td><?= htmlspecialchars($d['nama_fasilitas']); ?></td>
            <td><?= htmlspecialchars($d['tanggal_mulai']); ?></td>
            <td><?= htmlspecialchars($d['tanggal_selesai']); ?></td>
            <td><?= htmlspecialchars($d['keperluan']); ?></td>
            <td class="text-center">
                <span class="badge <?= $badge_class; ?>"><?= ucfirst($status); ?></span>
            </td>
            
            <td>
                <?php 
                if ($status != 'pending' && !empty($d['admin'])) {
                    // Jika sudah disetujui/ditolak, tampilkan pesan generik
                    echo 'Telah Diverifikasi';
                } else {
                    // Jika masih pending atau admin belum tercatat, tampilkan '-'
                    echo '-';
                }
                ?>
            </td>
            <td class="text-center d-flex flex-column gap-2"> 
                <?php if ($status == 'pending'): ?>
                    <div class="d-flex justify-content-center">
                        <a href="index.php?page=booking_fasilitas&action=setujui&id=<?= $d['id_booking']; ?>" 
                        onclick="return confirm('Setujui booking fasilitas ini?')" 
                        class="btn btn-sm btn-success me-1">Setujui</a>
                        
                        <a href="index.php?page=booking_fasilitas&action=tolak&id=<?= $d['id_booking']; ?>" 
                        onclick="return confirm('Tolak booking fasilitas ini?')" 
                        class="btn btn-sm btn-warning">Tolak</a>
                    </div>
                <?php else: ?>
                    <span class="text-muted">Verifikasi Selesai</span>
                <?php endif; ?>
                
                <hr class="my-0"> <a href="index.php?page=booking_fasilitas&action=delete&id=<?= $d['id_booking']; ?>" 
                onclick="return confirm('Yakin ingin menghapus data ini?')" 
                class="btn btn-sm btn-danger w-100">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($data) == 0): ?>
        <tr>
            <td colspan="9" class="text-center">Belum ada data booking.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php endif; ?>