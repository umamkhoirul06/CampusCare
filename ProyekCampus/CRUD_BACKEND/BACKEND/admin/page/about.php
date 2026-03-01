<?php
include_once(__DIR__ . '/../../../koneksi.php');

if (!$koneksi) {
    die("Koneksi database gagal!");
}

$action = $_GET['action'] ?? 'list';
$type   = $_GET['type'] ?? 'profil'; // tipe: profil | kontak | berita

// ------------------------------
// === HANDLE FORM SUBMIT ===
// ------------------------------
if (isset($_POST['save'])) {
    // PROFIL KAMPUS
    if ($type == 'profil') {
        $nama_kampus   = $koneksi->real_escape_string($_POST['nama_kampus']);
        $deskripsi     = $koneksi->real_escape_string($_POST['deskripsi']);
        $alamat        = $koneksi->real_escape_string($_POST['alamat']);
        $tahun_berdiri = $_POST['tahun_berdiri'];

        // Ambil foto lama (kalau sudah ada)
        $profil = $koneksi->query("SELECT * FROM profil_kampus LIMIT 1")->fetch_assoc();
        $foto = $profil['foto'] ?? '';

        if (!empty($_FILES['foto']['name'])) {
            $target_dir = "../../uploads/kampus/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_name = time() . "_" . basename($_FILES["foto"]["name"]);
            $target_file = $target_dir . $file_name;

            move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
            $foto = "uploads/kampus/" . $file_name; // simpan path relatif ke DB
        }

        // cek apakah sudah ada data
        $cek = $koneksi->query("SELECT id FROM profil_kampus LIMIT 1");
        if ($cek->num_rows > 0) {
            $koneksi->query("
                UPDATE profil_kampus 
                SET nama_kampus='$nama_kampus', deskripsi='$deskripsi', alamat='$alamat',
                    foto='$foto', tahun_berdiri='$tahun_berdiri'
            ");
        } else {
            $koneksi->query("
                INSERT INTO profil_kampus (nama_kampus, deskripsi, alamat, foto, tahun_berdiri)
                VALUES ('$nama_kampus', '$deskripsi', '$alamat', '$foto', '$tahun_berdiri')
            ");
        }

        echo "<script>alert('Profil kampus berhasil disimpan!'); window.location='index.php?type=profil';</script>";
        exit;
    }

    // KONTAK DARURAT
    if ($type == 'kontak') {
        $id     = $_POST['id'] ?? '';
        $nama   = $koneksi->real_escape_string($_POST['nama_kontak']);
        $nilai  = $koneksi->real_escape_string($_POST['nilai']);

        if ($id) {
            $koneksi->query("UPDATE kontak_darurat SET nama_kontak='$nama', nilai='$nilai' WHERE id='$id'");
        } else {
            $koneksi->query("INSERT INTO kontak_darurat (nama_kontak, nilai) VALUES ('$nama', '$nilai')");
        }

        echo "<script>alert('Kontak darurat berhasil disimpan!'); window.location='index.php?type=kontak';</script>";
        exit;
    }

    // BERITA
    if ($type == 'berita') {
        $id_berita      = $_POST['id_berita'] ?? '';
        $judul   = $koneksi->real_escape_string($_POST['judul']);
        $isi     = $koneksi->real_escape_string($_POST['isi']);
        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

        // Ambil foto lama (kalau edit)
        $foto_lama = '';
        if (!empty($id_berita)) {
            $cekFoto = $koneksi->query("SELECT foto FROM berita WHERE id_berita=" . intval($id));
            if ($cekFoto && $cekFoto->num_rows > 0) {
                $rowFoto = $cekFoto->fetch_assoc();
                $foto_lama = $rowFoto['foto'];
            }
        }

        $foto = $foto_lama;

        // Upload foto baru (kalau ada)
        if (!empty($_FILES['foto']['name'])) {
            $target_dir = "../../uploads/berita/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_name = time() . "_" . basename($_FILES["foto"]["name"]);
            $target_file = $target_dir . $file_name;

            move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
            $foto = "uploads/berita/" . $file_name;
        }

        if ($id_berita) {
            $koneksi->query("
                UPDATE berita 
                SET judul='$judul', isi='$isi', foto='$foto', tanggal='$tanggal'
                WHERE id_berita='$id_berita'
            ");
        } else {
            $koneksi->query("
                INSERT INTO berita (judul, isi, foto, tanggal)
                VALUES ('$judul', '$isi', '$foto', '$tanggal')
            ");
        }

        echo "<script>alert('Berita berhasil disimpan!'); window.location='index.php?type=berita';</script>";
        exit;
    }
}

// ------------------------------
// === HANDLE DELETE ===
// ------------------------------
if ($action == 'delete' && isset($_GET['id_berita'])) {
    $id_berita = intval($_GET['id_berita']);

    if ($type == 'kontak') {
        $koneksi->query("DELETE FROM kontak_darurat WHERE id = $id");
    } elseif ($type == 'berita') {
        $koneksi->query("DELETE FROM berita WHERE id_berita = $id_berita");
    }

    echo "<script>alert('Data berhasil dihapus!'); window.location='index.php?type=berita';</script>";
    exit;
}

// ------------------------------
// === LOAD DATA ===
// ------------------------------
$profil = $koneksi->query("SELECT * FROM profil_kampus LIMIT 1")->fetch_assoc();
$kontak = $koneksi->query("SELECT * FROM kontak_darurat ORDER BY nama_kontak ASC");
$berita = $koneksi->query("SELECT * FROM berita ORDER BY tanggal DESC");
?>

<h2 class="mb-4">Manajemen Halaman About</h2>

<!-- TABS -->
<div class="mb-3">
    <a href="?type=profil" class="btn btn-primary <?= $type=='profil'?'active':'' ?>">Profil Kampus</a>
    <a href="?type=kontak" class="btn btn-primary <?= $type=='kontak'?'active':'' ?>">Kontak Darurat</a>
    <a href="?type=berita" class="btn btn-primary <?= $type=='berita'?'active':'' ?>">Berita Kampus</a>
</div>

<?php if ($type == 'profil'): ?>
<!-- ========================================================= -->
<!-- PROFIL KAMPUS -->
<!-- ========================================================= -->
<div class="card shadow-sm">
    <div class="card-header bg-light"><strong>Edit Profil Kampus</strong></div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Nama Kampus</label>
                <input type="text" name="nama_kampus" class="form-control" required
                    value="<?= htmlspecialchars($profil['nama_kampus'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($profil['deskripsi'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label>Alamat</label>
                <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($profil['alamat'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label>Foto</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
                <?php if (!empty($profil['foto'])): ?>
                    <img src="<?= htmlspecialchars($profil['foto']) ?>" alt="Foto Kampus" style="max-width:120px;margin-top:8px;border-radius:8px;">
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label>Tahun Berdiri</label>
                <input type="number" name="tahun_berdiri" class="form-control" min="1900" max="<?= date('Y') ?>"
                       value="<?= htmlspecialchars($profil['tahun_berdiri'] ?? ''); ?>">
            </div>
            <button type="submit" name="save" class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>

<?php elseif ($type == 'kontak'): ?>
<!-- ========================================================= -->
<!-- KONTAK DARURAT -->
<!-- ========================================================= -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Kontak Darurat</h5>
    <a href="?type=kontak&action=create" class="btn btn-success">+ Tambah Kontak</a>
</div>

<?php if ($action == 'create' || $action == 'edit'): 
    $edit = null;
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $edit = $koneksi->query("SELECT * FROM kontak_darurat WHERE id=$id")->fetch_assoc();
    }
?>
<form method="POST" action="">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? ''; ?>">
    <div class="mb-3">
        <label>Nama Kontak</label>
        <input type="text" name="nama_kontak" class="form-control" required value="<?= htmlspecialchars($edit['nama_kontak'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label>Nilai (No. Telp / Email / Link)</label>
        <input type="text" name="nilai" class="form-control" required value="<?= htmlspecialchars($edit['nilai'] ?? ''); ?>">
    </div>
    <button type="submit" name="save" class="btn btn-primary">Simpan</button>
    <a href="?type=kontak" class="btn btn-secondary">Batal</a>
</form>

<?php else: ?>
<table class="table table-bordered align-middle">
    <thead class="table-light text-center">
        <tr><th>No</th><th>Nama Kontak</th><th>Nilai</th><th width="15%">Aksi</th></tr>
    </thead>
    <tbody>
        <?php $no=1; while($c = $kontak->fetch_assoc()): ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= htmlspecialchars($c['nama_kontak']) ?></td>
            <td><?= htmlspecialchars($c['nilai']) ?></td>
            <td class="text-center">
                <a href="?type=kontak&action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="?type=kontak&action=delete&id=<?= $c['id'] ?>" onclick="return confirm('Hapus kontak ini?')" class="btn btn-sm btn-danger">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($kontak->num_rows==0): ?>
        <tr><td colspan="4" class="text-center">Belum ada kontak.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php endif; ?>

<?php elseif ($type == 'berita'): ?>
<!-- ========================================================= -->
<!-- BERITA KAMPUS -->
<!-- ========================================================= -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Berita</h5>
    <a href="?type=berita&action=create" class="btn btn-success">+ Tambah Berita</a>
</div>

<?php if ($action == 'create' || $action == 'edit'): 
    $edit = null;
    if ($action == 'edit' && isset($_GET['id_berita'])) {
        $id_berita = intval($_GET['id_berita']);
        $edit = $koneksi->query("SELECT * FROM berita WHERE id_berita=$id_berita")->fetch_assoc();
    }
?>
<form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="id_berita" value="<?= $edit['id_berita'] ?? ''; ?>">
    <div class="mb-3">
        <label>Judul Berita</label>
        <input type="text" name="judul" class="form-control" required value="<?= htmlspecialchars($edit['judul'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label>Isi Berita</label>
        <textarea name="isi" class="form-control" rows="5" required><?= htmlspecialchars($edit['isi'] ?? ''); ?></textarea>
    </div>
    <div class="mb-3">
        <label>Foto</label>
        <input type="file" name="foto" class="form-control" accept="image/*">
        <?php if (!empty($edit['foto'])): ?>
            <img src="<?= htmlspecialchars($edit['foto']) ?>" alt="Foto Berita" style="max-width:120px;margin-top:8px;border-radius:8px;">
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($edit['tanggal'] ?? date('Y-m-d')); ?>">
    </div>
    <button type="submit" name="save" class="btn btn-primary">Simpan</button>
    <a href="?type=berita" class="btn btn-secondary">Batal</a>
</form>

<?php else: ?>
<table class="table table-bordered align-middle">
    <thead class="table-light text-center">
        <tr><th>No</th><th>Judul</th><th>Tanggal</th><th width="20%">Aksi</th></tr>
    </thead>
    <tbody>
        <?php $no=1; while($b = $berita->fetch_assoc()): ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= htmlspecialchars($b['judul']) ?></td>
            <td class="text-center"><?= htmlspecialchars($b['tanggal']) ?></td>
            <td class="text-center">
                <a href="?type=berita&action=edit&id_berita=<?= $b['id_berita'] ?> " class="btn btn-sm btn-warning">Edit</a> 
                <a href="?type=berita&action=delete&id_berita=<?= $b['id_berita'] ?> " onclick="return confirm(\'Hapus berita ini?\')" class="btn btn-sm btn-danger">Hapus</a>

            </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($berita->num_rows==0): ?>
        <tr><td colspan="4" class="text-center">Belum ada berita.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php endif; ?>
<?php endif; ?>
