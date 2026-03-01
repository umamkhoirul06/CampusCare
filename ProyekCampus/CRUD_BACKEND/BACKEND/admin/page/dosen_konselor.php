<?php
include_once(__DIR__ . '/../../../koneksi.php');

$action = $_GET['action'] ?? 'list';
$id     = $_GET['id'] ?? '';

if ($action == 'create' && isset($_POST['tambah'])) {
    $nidn = $_POST['nidn'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $spesialisasi = $_POST['spesialisasi'];
    $role = $_POST['role'];
    $is_active = 1;

    mysqli_query($koneksi, "INSERT INTO dosen_konselor (nidn, nama_lengkap, email, password, spesialisasi, role, is_active)
                            VALUES ('$nidn', '$nama', '$email', '$password', '$spesialisasi', '$role', '$is_active')");
    echo "<script>alert('Dosen/Konselor berhasil ditambahkan!'); window.location='index.php?page=dosen_konselor';</script>";
    exit;
}

if ($action == 'edit' && isset($_POST['update'])) {
    $nidn = $_POST['nidn'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $spesialisasi = $_POST['spesialisasi'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $id_konselor = $_POST['id_konselor'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE dosen_konselor 
                                SET nidn='$nidn', nama_lengkap='$nama', email='$email', password='$password', 
                                    spesialisasi='$spesialisasi', role='$role', is_active='$is_active' 
                                WHERE id_konselor='$id_konselor'");
    } else {
        mysqli_query($koneksi, "UPDATE dosen_konselor 
                                SET nidn='$nidn', nama_lengkap='$nama', email='$email', 
                                    spesialisasi='$spesialisasi', role='$role', is_active='$is_active' 
                                WHERE id_konselor='$id_konselor'");
    }

    echo "<script>alert('Data berhasil diperbarui!'); window.location='index.php?page=dosen_konselor';</script>";
    exit;
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM dosen_konselor WHERE id_konselor=$id");
    echo "<script>alert('Data berhasil dihapus!'); window.location='index.php?page=dosen_konselor';</script>";
    exit;
}
?>

<h2 class="mb-4">Manajemen Dosen / Konselor</h2>

<?php if ($action == 'list'): ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Dosen & Konselor</h5>
    <a href="index.php?page=dosen_konselor&action=create" class="btn btn-success">Tambah Dosen/Konselor</a>
</div>

<?php
$data = mysqli_query($koneksi, "SELECT * FROM dosen_konselor ORDER BY nama_lengkap ASC");
?>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="5%">No</th>
            <th>NIDN</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>Spesialisasi</th>
            <th>Role</th>
            <th>Status</th>
            <th width="15%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($d=mysqli_fetch_assoc($data)): ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($d['nidn']); ?></td>
            <td><?= htmlspecialchars($d['nama_lengkap']); ?></td>
            <td><?= htmlspecialchars($d['email']); ?></td>
            <td><?= htmlspecialchars($d['spesialisasi']); ?></td>
            <td class="text-center"><?= ucfirst($d['role']); ?></td>
            <td class="text-center"><?= $d['is_active'] ? 'Aktif' : 'Nonaktif'; ?></td>
            <td class="text-center">
                <a href="index.php?page=dosen_konselor&action=edit&id=<?= $d['id_konselor']; ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="index.php?page=dosen_konselor&action=delete&id=<?= $d['id_konselor']; ?>" 
                onclick="return confirm('Yakin ingin menghapus data ini?')" 
                class="btn btn-sm btn-danger">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($data) == 0): ?>
        <tr>
            <td colspan="8" class="text-center">Belum ada data dosen/konselor.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php elseif ($action == 'create'): ?>

<div class="mb-3">
    <a href="index.php?page=dosen_konselor" class="btn btn-secondary">Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <strong>Form Tambah Dosen / Konselor</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?page=dosen_konselor&action=create">
            <div class="mb-3">
                <label>NIDN</label>
                <input type="text" name="nidn" class="form-control">
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
                <label>Spesialisasi</label>
                <input type="text" name="spesialisasi" class="form-control">
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="dosen">Dosen</option>
                    <option value="konselor" selected>Konselor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
            <a href="index.php?page=dosen_konselor" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php elseif ($action == 'edit'): ?>

<?php
$id = $_GET['id'];
$d = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM dosen_konselor WHERE id_konselor='$id'"));
?>

<div class="mb-3">
    <a href="index.php?page=dosen_konselor" class="btn btn-secondary">Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <strong>Form Edit Dosen / Konselor</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?page=dosen_konselor&action=edit">
            <input type="hidden" name="id_konselor" value="<?= $d['id_konselor']; ?>">

            <div class="mb-3">
                <label>NIDN</label>
                <input type="text" name="nidn" class="form-control" value="<?= htmlspecialchars($d['nidn']); ?>">
            </div>

            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($d['nama_lengkap']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($d['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Password (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="mb-3">
                <label>Spesialisasi</label>
                <input type="text" name="spesialisasi" class="form-control" value="<?= htmlspecialchars($d['spesialisasi']); ?>">
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="dosen" <?= $d['role']=='dosen'?'selected':''; ?>>Dosen</option>
                    <option value="konselor" <?= $d['role']=='konselor'?'selected':''; ?>>Konselor</option>
                    <option value="admin" <?= $d['role']=='admin'?'selected':''; ?>>Admin</option>
                </select>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="aktif" <?= $d['is_active'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="aktif">Aktif</label>
            </div>

            <button type="submit" name="update" class="btn btn-primary">Update</button>
            <a href="index.php?page=dosen_konselor" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php endif; ?>