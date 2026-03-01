<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "campus_care2";
$port = 3307;

// --- Koneksi database MySQLi (Object-Oriented) ---
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$action = $_GET['action'] ?? 'list';
$id     = $_GET['id'] ?? '';

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_konselor = $_POST['id_konselor'];
    $hari = $_POST['hari'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $kuota_harian = $_POST['kuota_harian'];

    mysqli_query($conn, "INSERT INTO jadwal_konseling (id_konselor,hari,waktu_mulai,waktu_selesai,kuota_harian)
                        VALUES ('$id_konselor','$hari','$waktu_mulai','$waktu_selesai','$kuota_harian')");
    echo "<script>alert('Jadwal berhasil ditambahkan!'); window.location='index.php?page=jadwal_konseling';</script>";
    exit;
}

if ($action == 'delete' && $id) {
    mysqli_query($conn, "DELETE FROM jadwal_konseling WHERE id_jadwal='$id'");
    echo "<script>alert('Jadwal berhasil dihapus!'); window.location='index.php?page=jadwal_konseling';</script>";
    exit;
}

$konselor = mysqli_query($conn, "SELECT * FROM dosen_konselor WHERE role IN ('dosen','konselor')");
$jadwal = mysqli_query($conn, "
    SELECT j.*, k.nama_lengkap 
    FROM jadwal_konseling j 
    JOIN dosen_konselor k ON j.id_konselor = k.id_konselor
    ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat'), j.waktu_mulai
");
?>

<h2 class="mb-4">Manajemen Jadwal Konseling</h2>

<?php if ($action == 'list'): ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Jadwal</h5>
    <a href="index.php?page=jadwal_konseling&action=create" class="btn btn-success">Tambah Jadwal</a>
</div>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="5%">No</th>
            <th>Konselor</th>
            <th>Hari</th>
            <th>Waktu Mulai</th>
            <th>Waktu Selesai</th>
            <th>Kuota Harian</th>
            <th width="10%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($d=mysqli_fetch_assoc($jadwal)): ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($d['nama_lengkap']); ?></td>
            <td class="text-center"><?= $d['hari']; ?></td>
            <td class="text-center"><?= $d['waktu_mulai']; ?></td>
            <td class="text-center"><?= $d['waktu_selesai']; ?></td>
            <td class="text-center"><?= $d['kuota_harian']; ?></td>
            <td class="text-center">
                <a href="index.php?page=jadwal_konseling&action=delete&id=<?= $d['id_jadwal']; ?>" 
                onclick="return confirm('Yakin ingin menghapus jadwal ini?')" 
                class="btn btn-sm btn-danger">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($jadwal) == 0): ?>
        <tr>
            <td colspan="7" class="text-center">Belum ada jadwal konseling.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php elseif ($action == 'create'): ?>

<div class="mb-3">
    <a href="index.php?page=jadwal_konseling" class="btn btn-secondary">Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <strong>Form Tambah Jadwal Konseling</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?page=jadwal_konseling&action=create">
            <div class="mb-3">
                <label>Konselor</label>
                <select name="id_konselor" class="form-select" required>
                    <option value="">-- Pilih Konselor --</option>
                    <?php while($k=mysqli_fetch_assoc($konselor)): ?>
                        <option value="<?= $k['id_konselor']; ?>"><?= htmlspecialchars($k['nama_lengkap']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Hari</label>
                <select name="hari" class="form-select" required>
                    <option value="">-- Pilih Hari --</option>
                    <option>Senin</option>
                    <option>Selasa</option>
                    <option>Rabu</option>
                    <option>Kamis</option>
                    <option>Jumat</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Waktu Mulai</label>
                <input type="time" name="waktu_mulai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Waktu Selesai</label>
                <input type="time" name="waktu_selesai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Kuota Harian</label>
                <input type="number" name="kuota_harian" class="form-control" min="1" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php?page=jadwal_konseling" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php endif; ?>
