<?php
include "../../koneksi.php";
if (!$koneksi) die("Koneksi database gagal!");

$action = $_GET['action'] ?? 'list';

/* ================= UPDATE STATUS ================= */
if ($action === 'update_status' && isset($_GET['id'], $_GET['status'])) {
    $id = (int) $_GET['id'];
    $status = $_GET['status'];

    // SESUAI ENUM DATABASE
    $valid_status = ['diproses', 'selesai', 'ditolak'];

    if (in_array($status, $valid_status)) {
        $koneksi->query("UPDATE laporan_pengaduan SET status='$status' WHERE id_laporan=$id");
        echo "<script>
            alert('Status laporan berhasil diubah');
            window.location='index.php?page=laporan_pengaduan';
        </script>";
        exit;
    }
}

/* ================= HAPUS ================= */
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $koneksi->query("DELETE FROM laporan_pengaduan WHERE id_laporan=$id");
    echo "<script>
        alert('Laporan berhasil dihapus');
        window.location='index.php?page=laporan_pengaduan';
    </script>";
    exit;
}

/* ================= AMBIL DATA ================= */
$laporan = $koneksi->query("
    SELECT l.*, m.nama_lengkap AS mahasiswa
    FROM laporan_pengaduan l
    LEFT JOIN mahasiswa m ON l.id_mahasiswa = m.id_mahasiswa
    ORDER BY l.created_at DESC
");
?>
<div class="card mb-4">
  <div class="card-body">

    <form method="GET" action="page/report_laporan.php" class="row g-3">

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

  </div>
</div>
<script>
document.getElementById("select-range").addEventListener("change", function () {
  let start = document.getElementById("input-start");
  let end = document.getElementById("input-end");

  if (this.value !== "") {
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


<h2 class="mb-4">Manajemen Laporan Pengaduan</h2>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Pelapor</th>
            <th>Anonim</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
<?php $no=1; while($l=$laporan->fetch_assoc()): ?>
<tr>
    <td class="text-center"><?= $no++ ?></td>
    <td><?= htmlspecialchars($l['judul']) ?></td>
    <td><?= $l['mahasiswa'] ?: '-' ?></td>
    <td class="text-center"><?= $l['is_anonim'] ? 'Ya' : 'Tidak' ?></td>

    <td class="text-center">
        <?php
        if ($l['status']=='baru')
            echo "<span class='badge bg-secondary'>Baru</span>";
        elseif ($l['status']=='diproses')
            echo "<span class='badge bg-warning text-dark'>Diproses</span>";
        elseif ($l['status']=='selesai')
            echo "<span class='badge bg-success'>Selesai</span>";
        elseif ($l['status']=='ditolak')
            echo "<span class='badge bg-danger'>Ditolak</span>";
        ?>
    </td>

    <td><?= $l['created_at'] ?></td>

    <td class="text-center">

<?php if ($l['status'] == 'baru'): ?>

    <a href="index.php?page=laporan_pengaduan&action=update_status&id=<?= $l['id_laporan'] ?>&status=diproses"
       class="btn btn-sm btn-warning"
       onclick="return confirm('Ubah ke Diproses?')">Diproses</a>

    <a href="index.php?page=laporan_pengaduan&action=update_status&id=<?= $l['id_laporan'] ?>&status=selesai"
       class="btn btn-sm btn-success"
       onclick="return confirm('Selesaikan laporan?')">Selesai</a>

    <a href="index.php?page=laporan_pengaduan&action=update_status&id=<?= $l['id_laporan'] ?>&status=ditolak"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Tolak laporan?')">Ditolak</a>

    <a href="index.php?page=laporan_pengaduan&action=delete&id=<?= $l['id_laporan'] ?>"
       class="btn btn-sm btn-outline-danger"
       onclick="return confirm('Hapus laporan?')">Hapus</a>

<?php elseif ($l['status'] == 'diproses'): ?>

    <a href="index.php?page=laporan_pengaduan&action=update_status&id=<?= $l['id_laporan'] ?>&status=selesai"
       class="btn btn-sm btn-success"
       onclick="return confirm('Selesaikan laporan?')">Selesai</a>

    <a href="index.php?page=laporan_pengaduan&action=update_status&id=<?= $l['id_laporan'] ?>&status=ditolak"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Tolak laporan?')">Ditolak</a>

    <a href="index.php?page=laporan_pengaduan&action=delete&id=<?= $l['id_laporan'] ?>"
       class="btn btn-sm btn-outline-danger"
       onclick="return confirm('Hapus laporan?')">Hapus</a>

<?php elseif ($l['status'] == 'selesai' || $l['status'] == 'ditolak'): ?>

    <a href="index.php?page=laporan_pengaduan&action=delete&id=<?= $l['id_laporan'] ?>"
       class="btn btn-sm btn-outline-danger"
       onclick="return confirm('Hapus laporan?')">Hapus</a>

<?php endif; ?>

</td>
</tr>
<?php endwhile; ?>

<?php if ($laporan->num_rows==0): ?>
<tr><td colspan="7" class="text-center">Belum ada laporan</td></tr>
<?php endif; ?>
    </tbody>
</table>
</div>
