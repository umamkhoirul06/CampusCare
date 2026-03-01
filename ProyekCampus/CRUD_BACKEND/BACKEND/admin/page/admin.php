<?php
include "../../koneksi.php";

$action = $_GET['action'] ?? 'list';
$id     = $_GET['id'] ?? '';

if ($action == 'create' && isset($_POST['tambah'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    mysqli_query($koneksi, "INSERT INTO admin (username, password) VALUES ('$username', '$password')");
    echo "<script>alert('Admin berhasil ditambahkan!'); window.location='index.php?page=admin';</script>";
    exit;
}

if ($action == 'edit' && isset($_POST['update'])) {
    $id_admin = $_POST['id_admin'];
    $username = $_POST['username'];
    
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        mysqli_query($koneksi, "UPDATE admin SET username='$username', password='$password' WHERE id_admin='$id_admin'");
    } else {
        mysqli_query($koneksi, "UPDATE admin SET username='$username' WHERE id_admin='$id_admin'");
    }
    
    echo "<script>alert('Data admin berhasil diperbarui!'); window.location='index.php?page=admin';</script>";
    exit;
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM admin WHERE id_admin='$id'");
    echo "<script>alert('Data admin berhasil dihapus!'); window.location='index.php?page=admin';</script>";
    exit;
}
?>

<h2 class="mb-4">Manajemen Admin</h2>

<?php if ($action == 'list'): ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Admin</h5>
    <a href="index.php?page=admin&action=create" class="btn btn-success">Tambah Admin</a>
</div>

<?php
$data = mysqli_query($koneksi, "SELECT * FROM admin ORDER BY id_admin ASC");
?>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="5%">No</th>
            <th>Username</th>
            <th>Password (MD5)</th>
            <th width="15%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($d=mysqli_fetch_assoc($data)): ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($d['username']); ?></td>
            <td class="text-muted"><?= htmlspecialchars($d['password']); ?></td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                    <a href="index.php?page=admin&action=edit&id=<?= $d['id_admin']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="index.php?page=admin&action=delete&id=<?= $d['id_admin']; ?>" 
                    onclick="return confirm('Yakin ingin menghapus admin ini?')" 
                    class="btn btn-sm btn-danger">Hapus</a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($data) == 0): ?>
        <tr>
            <td colspan="4" class="text-center">Belum ada data admin.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php elseif ($action == 'create'): ?>

<div class="mb-3">
    <a href="index.php?page=admin" class="btn btn-secondary">Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <strong>Form Tambah Admin</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?page=admin&action=create">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
            <a href="index.php?page=admin" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php elseif ($action == 'edit'): ?>

<?php
$d = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM admin WHERE id_admin='$id'"));
?>

<div class="mb-3">
    <a href="index.php?page=admin" class="btn btn-secondary">Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <strong>Form Edit Admin</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?page=admin&action=edit">
            <input type="hidden" name="id_admin" value="<?= $d['id_admin']; ?>">

            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($d['username']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Password (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <button type="submit" name="update" class="btn btn-primary">Update</button>
            <a href="index.php?page=admin" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php endif; ?>
