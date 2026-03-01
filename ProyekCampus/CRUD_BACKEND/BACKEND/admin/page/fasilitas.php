<?php
include_once(__DIR__ . '/../../../koneksi.php');

/* ===== AMBIL ACTION GLOBAL ===== */
$action = $_GET['action'] ?? 'list';

/* ===== HANDLE POST ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* SIMPAN */
    if ($action === 'store') {
        $nama = trim($_POST['nama_fasilitas']);
        $deskripsi = trim($_POST['deskripsi']);
        $kapasitas = (int)$_POST['kapasitas'];

        if ($nama && $kapasitas > 0) {
            $stmt = $koneksi->prepare(
                "INSERT INTO fasilitas (nama_fasilitas, deskripsi, kapasitas)
                 VALUES (?, ?, ?)"
            );
            $stmt->bind_param("ssi", $nama, $deskripsi, $kapasitas);
            $stmt->execute();
        }
    }

    /* TAMBAH / KURANG */
    if (in_array($action, ['add_kapasitas','reduce_kapasitas'])) {
        $id = (int)$_POST['id_fasilitas'];
        $jumlah = (int)$_POST['jumlah'];

        if ($jumlah > 0) {
            if ($action === 'add_kapasitas') {
                $stmt = $koneksi->prepare(
                    "UPDATE fasilitas 
                     SET kapasitas = kapasitas + ? 
                     WHERE id_fasilitas = ?"
                );
                $stmt->bind_param("ii", $jumlah, $id);
            } else {
                $stmt = $koneksi->prepare(
                    "UPDATE fasilitas 
                     SET kapasitas = IF(kapasitas - ? < 0, 0, kapasitas - ?)
                     WHERE id_fasilitas = ?"
                );
                $stmt->bind_param("iii", $jumlah, $jumlah, $id);
            }
            $stmt->execute();
        }
    }

    /* RELOAD */
    echo "<script>location.href='index.php?page=fasilitas';</script>";
    exit;
}

/* ===== DATA ===== */
$fasilitas = $koneksi->query(
    "SELECT * FROM fasilitas ORDER BY nama_fasilitas ASC"
);
?>



<h2 class="mb-4">Manajemen Fasilitas</h2>

<?php if ($action === 'list'): ?>

<div class="d-flex justify-content-between mb-3">
    <h5>Daftar Fasilitas</h5>
    <a href="index.php?page=fasilitas&action=create" class="btn btn-success">
        Tambah Fasilitas
    </a>
</div>

<table class="table table-bordered align-middle">
    <thead class="table-light text-center">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th>Kapasitas</th>
            <th>Status</th>
            <th width="22%">Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php $no=1; while($f=$fasilitas->fetch_assoc()): ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= htmlspecialchars($f['nama_fasilitas']) ?></td>
            <td><?= htmlspecialchars($f['deskripsi']) ?></td>
            <td class="text-center"><?= $f['kapasitas'] ?></td>
            <td class="text-center">
                <?= $f['kapasitas'] > 0 ? 'Tersedia' : 'Tidak Tersedia' ?>
            </td>
            <td>

<!-- TOMBOL -->
<button class="btn btn-success btn-sm w-100 mb-1"
        onclick="toggleForm('add<?= $f['id_fasilitas'] ?>')">
    ➕ Tambah Kapasitas
</button>

<button class="btn btn-warning btn-sm w-100 mb-1"
        onclick="toggleForm('reduce<?= $f['id_fasilitas'] ?>')">
    ➖ Kurangi Kapasitas
</button>

<a href="index.php?page=fasilitas&action=delete&id=<?= $f['id_fasilitas'] ?>"
   onclick="return confirm('Hapus fasilitas ini?')"
   class="btn btn-danger btn-sm w-100 mb-1">
   🗑 Hapus
</a>

<!-- FORM TAMBAH -->
<form method="POST"
      action="index.php?page=fasilitas&action=add_kapasitas"
      id="add<?= $f['id_fasilitas'] ?>"
      style="display:none;">
    <input type="hidden" name="id_fasilitas" value="<?= $f['id_fasilitas'] ?>">
    <input type="number" name="jumlah" min="1"
           class="form-control form-control-sm mb-1"
           placeholder="Jumlah" required>
    <button class="btn btn-success btn-sm w-100">Simpan</button>
</form>

<!-- FORM KURANG -->
<form method="POST"
      action="index.php?page=fasilitas&action=reduce_kapasitas"
      id="reduce<?= $f['id_fasilitas'] ?>"
      style="display:none;">
    <input type="hidden" name="id_fasilitas" value="<?= $f['id_fasilitas'] ?>">
    <input type="number" name="jumlah" min="1"
           class="form-control form-control-sm mb-1"
           placeholder="Jumlah" required>
    <button class="btn btn-warning btn-sm w-100">Simpan</button>
</form>

            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php elseif ($action === 'create'): ?>

<a href="index.php?page=fasilitas" class="btn btn-secondary mb-3">Kembali</a>

<div class="card">
<div class="card-header"><strong>Tambah Fasilitas</strong></div>
<div class="card-body">
<form method="POST" action="index.php?page=fasilitas&action=store">
    <div class="mb-3">
        <label>Nama Fasilitas</label>
        <input type="text" name="nama_fasilitas" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label>Kapasitas</label>
        <input type="number" name="kapasitas" min="1" class="form-control" required>
    </div>
    <button class="btn btn-primary">Simpan</button>
</form>
</div>
</div>

<?php endif; ?>

<script>
function toggleForm(id){
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
