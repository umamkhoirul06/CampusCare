<?php
include "../../koneksi.php"; 
if (!isset($koneksi) || !$koneksi) {
    die("Koneksi database gagal!");
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Folder upload
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/CRUD_BACKEND/uploads/";
if(!is_dir($upload_dir)){
    mkdir($upload_dir, 0777, true);
}

if ($action == 'create') {
    if (isset($_POST['simpan'])) {
        $judul = $koneksi->real_escape_string($_POST['judul']);
        $konten = $koneksi->real_escape_string($_POST['konten']);
        $id_kategori = (int) $_POST['id_kategori'];
        $status = $_POST['status'];
        $tanggal_publikasi = ($status == 'published') ? date('Y-m-d H:i:s') : NULL;

        // Upload gambar
        $gambar = NULL;
        if (!empty($_FILES['gambar']['name'])) {
            $filename = time() . "_" . basename($_FILES['gambar']['name']);
            $target_file = $upload_dir . $filename;
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg','jpeg','png','gif'];

            if (in_array($fileType, $allowed_types)) {
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    $gambar = $filename;
                } else {
                    echo "<div class='alert alert-danger'>Gagal mengunggah gambar.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Tipe gambar tidak diperbolehkan!</div>";
            }
        }

        $sql = "INSERT INTO artikel (judul, konten, id_kategori, author_id, status, tanggal_publikasi, gambar)
                VALUES ('$judul', '$konten', $id_kategori, NULL, '$status', " . 
                ($tanggal_publikasi ? "'$tanggal_publikasi'" : "NULL") . ", " .
                ($gambar ? "'$gambar'" : "NULL") . ")";

        if ($koneksi->query($sql)) {
            echo "<div class='alert alert-success'>Artikel berhasil ditambahkan.</div>";
            echo "<meta http-equiv='refresh' content='1;url=index.php?page=artikel'>";
            exit;
        } else {
            echo "<div class='alert alert-danger'>Gagal menambah artikel: {$koneksi->error}</div>";
        }
    }
    ?>

    <h3>Tambah Artikel Baru</h3>
    <hr>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group mb-3">
            <label>Judul Artikel</label>
            <input type="text" name="judul" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Kategori</label>
            <select name="id_kategori" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php
                $kategori = $koneksi->query("SELECT * FROM kategori_artikel ORDER BY nama_kategori ASC");
                while ($k = $kategori->fetch_assoc()):
                ?>
                    <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group mb-3">
            <label>Konten Artikel</label>
            <textarea name="konten" class="form-control" rows="6" required></textarea>
        </div>
        <div class="form-group mb-3">
            <label>Gambar Artikel</label>
            <input type="file" name="gambar" class="form-control">
        </div>
        <div class="form-group mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </div>
        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
        <a href="index.php?page=artikel" class="btn btn-secondary">Kembali</a>
    </form>

<?php
} else {
    if (isset($_GET['hapus'])) {
        $id = (int) $_GET['hapus'];
        $file = $koneksi->query("SELECT gambar FROM artikel WHERE id_artikel=$id")->fetch_assoc();
        if ($file['gambar']) @unlink($upload_dir . $file['gambar']);

        $koneksi->query("DELETE FROM artikel WHERE id_artikel=$id");
        echo "<div class='alert alert-success'>Artikel berhasil dihapus.</div>";
        echo "<meta http-equiv='refresh' content='1;url=index.php?page=artikel'>";
    }

    $artikel = $koneksi->query("
        SELECT a.*, k.nama_kategori
        FROM artikel a
        LEFT JOIN kategori_artikel k ON a.id_kategori = k.id_kategori
        ORDER BY a.created_at DESC
    ");
    ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Manajemen Artikel</h3>
        <a href="index.php?page=artikel&action=create" class="btn btn-success">Tambah Artikel</a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th width="5%">No</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Tanggal Publikasi</th>
                <th>Gambar</th>
                <th width="15%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            if ($artikel->num_rows > 0):
                while ($row = $artikel->fetch_assoc()): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td><?= htmlspecialchars($row['nama_kategori'] ?: '-') ?></td>
                    <td class="text-center"><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['tanggal_publikasi'] ?: '-' ?></td>
                    <td class="text-center">
                        <?php if($row['gambar']): ?>
                            <img src="/CRUD_BACKEND/uploads/<?= $row['gambar'] ?>" width="80" alt="Gambar Artikel">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="index.php?page=artikel&hapus=<?= $row['id_artikel'] ?>" 
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Yakin ingin menghapus artikel ini?')">
                        Hapus
                        </a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr>
                    <td colspan="7" class="text-center">Belum ada artikel.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php } ?>
