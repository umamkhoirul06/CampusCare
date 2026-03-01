<?php
include_once(__DIR__ . '/../../../koneksi.php');

$action = $_GET['action'] ?? 'list';

if ($action == 'hadir' && isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($koneksi, "UPDATE pendaftaran_event SET status_hadir=1 WHERE id_pendaftaran=$id");
    echo "<script>alert('Status kehadiran berhasil dikonfirmasi!'); window.location='index.php?page=pendaftaran_event';</script>";
    exit;
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM pendaftaran_event WHERE id_pendaftaran=$id");
    echo "<script>alert('Data berhasil dihapus!'); window.location='index.php?page=pendaftaran_event';</script>";
    exit;
}
?>

<h2 class="mb-4">Manajemen Pendaftaran Event</h2>

<?php if ($action == 'list'): ?>

<?php
$data = mysqli_query($koneksi, "
    SELECT p.*, m.nama_lengkap AS nama_mahasiswa, e.judul AS nama_event
    FROM pendaftaran_event p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    JOIN event e ON p.id_event = e.id_event
    ORDER BY p.registered_at DESC
");
?>
<div class="card mb-4">
    <div class="card-body">

        <form method="GET" action="page/report_event.php" class="row g-3">

            <div class="col-md-3">
                <label class="form-label fw-bold">Rentang Waktu</label>
                <select name="range" id="select-range" class="form-select">
                    <option value="">Custom</option>
                    <option value="1">1 Bulan Terakhir</option>
                    <option value="3">3 Bulan Terakhir</option>
                    <option value="6">6 Bulan Terakhir</option>
                </select>
            </div>

            <!-- TANGGAL MULAI -->
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Mulai</label>
                <input type="date" name="start" id="startDate" class="form-control">
            </div>

            <!-- TANGGAL SELESAI -->
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Selesai</label>
                <input type="date" name="end" id="endDate" class="form-control">
            </div>

            <div class="col-md-3">
    <label class="form-label fw-semibold">Event</label>
    <select name="event" class="form-select">
        <option value="">Semua Event</option>

        <?php
        $eventList = mysqli_query($koneksi, "SELECT id_event, judul FROM event ORDER BY judul ASC");
        while ($ev = mysqli_fetch_assoc($eventList)) {
            echo "<option value='{$ev['id_event']}'>{$ev['judul']}</option>";
        }
        ?>
    </select>
</div>


            <!-- FORMAT FILE -->
            <div class="col-md-3">
                <label class="form-label fw-semibold">Format File</label>
                <select name="format" class="form-select">
                   <option value="csv">Spreadsheet (.csv)</option>
                    <option value="excel">Excel (.xlsx)</option>
                    <option value="word">Word (.docx)</option>
                </select>
            </div>

            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Download Laporan</button>
            </div>

        </form>

    </div>
</div>
<script>
document.getElementById('select-range').addEventListener('change', function () {
    const rangeVal = this.value;
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');

    if (rangeVal === "1" || rangeVal === "3" || rangeVal === "6") {
        // Disable input tanggal
        startInput.value = "";
        endInput.value = "";
        startInput.setAttribute("disabled", true);
        endInput.setAttribute("disabled", true);
    } else {
        // Custom → tanggal aktif
        startInput.removeAttribute("disabled");
        endInput.removeAttribute("disabled");
    }
});
</script>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Pendaftaran</h5>
</div>

<script>
document.getElementById("rangeSelect").addEventListener("change", function() {
    let val = this.value;
    if(val === "1" || val === "3") {
        document.getElementById("startDate").value = "";
        document.getElementById("endDate").value = "";
    }
});
</script>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="5%">No</th>
            <th>Mahasiswa</th>
            <th>Event</th>
            <th>Status Hadir</th>
            <th width="15%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($d=mysqli_fetch_assoc($data)): ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($d['nama_mahasiswa']); ?></td>
            <td><?= htmlspecialchars($d['nama_event']); ?></td>
            <td class="text-center">
                <?php if ($d['status_hadir']): ?>
                    <span class="badge bg-success">Hadir</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Belum Hadir</span>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <?php if (!$d['status_hadir']): ?>
                    <a href="index.php?page=pendaftaran_event&action=hadir&id=<?= $d['id_pendaftaran']; ?>" 
                    onclick="return confirm('Konfirmasi kehadiran mahasiswa ini?')" 
                    class="btn btn-sm btn-success">Hadir</a>
                <?php endif; ?>
                <a href="index.php?page=pendaftaran_event&action=delete&id=<?= $d['id_pendaftaran']; ?>" 
                onclick="return confirm('Yakin ingin menghapus data ini?')" 
                class="btn btn-sm btn-danger">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($data) == 0): ?>
        <tr>
            <td colspan="5" class="text-center">Belum ada data pendaftaran event.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php endif; ?>
