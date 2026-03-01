<?php
// ---------------------------------------------------------
// KONEKSI DATABASE
// ---------------------------------------------------------
$host = "localhost";
$user = "root";
$pass = "";
$db   = "campus_care2";
$port = 3307;

$koneksi = new mysqli($host, $user, $pass, $db, $port);

if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Ambil action & id
$action = $_GET['action'] ?? '';
$id     = $_GET['id'] ?? 0;
$id     = is_numeric($id) ? (int)$id : 0;

// ---------------------------------------------------------
// A. CREATE (TAMBAH DATA)
// ---------------------------------------------------------
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $nim          = $_POST['nim'] ?? '';
    $nama         = $_POST['nama'] ?? '';
    $email        = $_POST['email'] ?? '';
    $password_raw = $_POST['password'] ?? '';
    $prodi        = $_POST['prodi'] ?? '';
    $angkatan     = (int)($_POST['angkatan'] ?? 0);

    $password       = password_hash($password_raw, PASSWORD_DEFAULT);
    $status_default = "pending";

    $stmt = $koneksi->prepare("
        INSERT INTO mahasiswa (nim, nama_lengkap, email, password, prodi, angkatan, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssssis", $nim, $nama, $email, $password, $prodi, $angkatan, $status_default);

    if ($stmt->execute()) {
        echo "<script>alert('Mahasiswa berhasil ditambahkan! Status: Pending.'); window.location='index.php?page=mahasiswa';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan mahasiswa: " . $stmt->error . "'); window.location='index.php?page=mahasiswa';</script>";
    }

    $stmt->close();
    exit;
}

// ---------------------------------------------------------
// B. EDIT (UPDATE DATA)
// ---------------------------------------------------------
if ($action == 'edit' && $id > 0) {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $nim      = $_POST['nim'] ?? '';
        $nama     = $_POST['nama'] ?? '';
        $email    = $_POST['email'] ?? '';
        $prodi    = $_POST['prodi'] ?? '';
        $angkatan = (int)($_POST['angkatan'] ?? 0);

        $stmt = $koneksi->prepare("
            UPDATE mahasiswa 
            SET nim=?, nama_lengkap=?, email=?, prodi=?, angkatan=? 
            WHERE id_mahasiswa=?
        ");

        $stmt->bind_param("sssisi", $nim, $nama, $email, $prodi, $angkatan, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Data mahasiswa berhasil diperbarui!'); window.location='index.php?page=mahasiswa';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui data: " . $stmt->error . "'); window.location='index.php?page=mahasiswa';</script>";
        }
        $stmt->close();
        exit;
    }

    // Ambil data untuk form edit
    $stmt_select = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa=?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();

    $result = $stmt_select->get_result();
    $mhs = $result->fetch_assoc();

    $stmt_select->close();

    if (!$mhs) {
        echo "<script>alert('Data tidak ditemukan!'); window.location='index.php?page=mahasiswa';</script>";
        exit;
    }
}

// ---------------------------------------------------------
// C. DELETE (HAPUS DATA)
// ---------------------------------------------------------
if ($action == 'delete' && $id > 0) {

    $stmt = $koneksi->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Data mahasiswa berhasil dihapus!'); window.location='index.php?page=mahasiswa';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data: " . $stmt->error . "'); window.location='index.php?page=mahasiswa';</script>";
    }

    $stmt->close();
    exit;
}

// ---------------------------------------------------------
// D. CONFIRM (KONFIRMASI AKUN)
// ---------------------------------------------------------
if ($action == 'confirm' && $id > 0) {

    $status_active = "active";

    $stmt = $koneksi->prepare("
        UPDATE mahasiswa 
        SET status=? 
        WHERE id_mahasiswa=? AND status='pending'
    ");

    $stmt->bind_param("si", $status_active, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Akun mahasiswa berhasil dikonfirmasi! Kini dapat login.'); window.location='index.php?page=mahasiswa';</script>";
        } else {
            echo "<script>alert('Akun sudah aktif atau tidak ditemukan.'); window.location='index.php?page=mahasiswa';</script>";
        }
    } else {
        echo "<script>alert('Gagal mengkonfirmasi akun: " . $stmt->error . "'); window.location='index.php?page=mahasiswa';</script>";
    }

    $stmt->close();
    exit;
}
?>

<h2 class="mb-4">Manajemen Mahasiswa</h2>

<?php if ($action == 'create'): ?>

<div class="mb-3">
    <a href="index.php?page=mahasiswa" class="btn btn-secondary">Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light"><strong>Form Tambah Mahasiswa</strong></div>
    <div class="card-body">

        <form method="POST" action="index.php?page=mahasiswa&action=create">

            <div class="mb-3">
                <label>NIM</label>
                <input type="text" name="nim" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Program Studi</label>
                <input type="text" name="prodi" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Angkatan</label>
                <input type="number" name="angkatan" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php?page=mahasiswa" class="btn btn-secondary">Batal</a>
        </form>

    </div>
</div>

<?php elseif ($action == 'edit' && isset($mhs)): ?>

<div class="mb-3">
    <a href="index.php?page=mahasiswa" class="btn btn-secondary">Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light"><strong>Form Edit Mahasiswa</strong></div>
    <div class="card-body">

        <form method="POST" action="index.php?page=mahasiswa&action=edit&id=<?= $mhs['id_mahasiswa']; ?>">

            <div class="mb-3">
                <label>NIM</label>
                <input type="text" name="nim" class="form-control" value="<?= htmlspecialchars($mhs['nim']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($mhs['nama_lengkap']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($mhs['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Program Studi</label>
                <input type="text" name="prodi" class="form-control" value="<?= htmlspecialchars($mhs['prodi']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Angkatan</label>
                <input type="number" name="angkatan" class="form-control" value="<?= htmlspecialchars($mhs['angkatan']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php?page=mahasiswa" class="btn btn-secondary">Batal</a>

        </form>

    </div>
</div>

<?php else: ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Mahasiswa</h5>
    <a href="index.php?page=mahasiswa&action=create" class="btn btn-success">Tambah Mahasiswa</a>
</div>

<?php
$data = $koneksi->query("SELECT * FROM mahasiswa ORDER BY nama_lengkap ASC");
?>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light text-center">
            <tr>
                <th width="5%">No</th>
                <th>NIM</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Program Studi</th>
                <th>Angkatan</th>
                <th width="10%">Status</th>
                <th width="15%">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php $no = 1; while ($row = $data->fetch_assoc()): ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nim']); ?></td>
                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['prodi']); ?></td>
                <td class="text-center"><?= htmlspecialchars($row['angkatan']); ?></td>

                <td class="text-center">
                    <?php 
                        $is_active = $row['status'] == 'active';
                        $status_class = $is_active ? 'bg-success' : 'bg-warning';
                    ?>
                    <span class="badge <?= $status_class; ?>"><?= strtoupper($row['status']); ?></span>
                </td>

                <td class="text-center">
                    <?php if (!$is_active): ?>
                        <a href="index.php?page=mahasiswa&action=confirm&id=<?= $row['id_mahasiswa']; ?>"
                           onclick="return confirm('Yakin ingin MENGKONFIRMASI akun ini? Akun akan langsung aktif dan bisa login.')"
                           class="btn btn-sm btn-info mb-1 w-100">
                           Konfirmasi
                        </a>
                    <?php endif; ?>

                    <a href="index.php?page=mahasiswa&action=edit&id=<?= $row['id_mahasiswa']; ?>" class="btn btn-sm btn-primary w-100 mb-1">Edit</a>

                    <a href="index.php?page=mahasiswa&action=delete&id=<?= $row['id_mahasiswa']; ?>"
                       onclick="return confirm('Yakin ingin menghapus data ini?')"
                       class="btn btn-sm btn-danger w-100">
                       Hapus
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>

            <?php if ($data->num_rows == 0): ?>
            <tr>
                <td colspan="8" class="text-center">Belum ada data mahasiswa.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>
