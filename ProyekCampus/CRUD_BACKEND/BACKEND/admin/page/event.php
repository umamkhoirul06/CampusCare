<?php
include_once(__DIR__ . '/../../../koneksi.php');


$action = $_GET['action'] ?? 'list';

if ($action == 'create' && isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $lokasi = $_POST['lokasi'];
    $kuota = (int)$_POST['kuota'];

// LOGIKA STATUS OTOMATIS
if ($kuota <= 0) {
    $status = 'closed';
} else {
    $status = $_POST['status'];
}

    mysqli_query($koneksi, "INSERT INTO event (judul, deskripsi, tanggal_mulai, tanggal_selesai, lokasi, kuota, penyelenggara_id, status)
                            VALUES ('$judul','$deskripsi','$tanggal_mulai','$tanggal_selesai','$lokasi','$kuota','$penyelenggara_id','$status')");
    echo "<script>alert('Event berhasil ditambahkan!'); window.location='?page=event';</script>";
    exit;
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($koneksi, "DELETE FROM event WHERE id_event=$id");
    echo "<script>alert('Event berhasil dihapus!'); window.location='?page=event';</script>";
    exit;
}

$data = mysqli_query($koneksi, "
    SELECT e.*, k.nama_lengkap AS penyelenggara
    FROM event e
    LEFT JOIN dosen_konselor k ON e.penyelenggara_id = k.id_konselor
    ORDER BY e.tanggal_mulai DESC
");

$konselor = mysqli_query($koneksi, "SELECT * FROM dosen_konselor ORDER BY nama_lengkap ASC");
?>

<h2 class="mb-4">Manajemen Event</h2>

<?php if ($action == 'list'): ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Daftar Event</h5>
    <a href="?page=event&action=create" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Tambah Event
    </a>
</div>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="5%">No</th>
            <th>Judul</th>
            <th>Deskripsi</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Lokasi</th>
            <th>Kuota</th>
            <th>Penyelenggara</th>
            <th>Status</th>
            <th width="10%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($d=mysqli_fetch_assoc($data)): ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($d['judul']); ?></td>
            <td><?= htmlspecialchars($d['deskripsi']); ?></td>
            <td><?= $d['tanggal_mulai']; ?></td>
            <td><?= $d['tanggal_selesai']; ?></td>
            <td><?= htmlspecialchars($d['lokasi']); ?></td>
            <td class="text-center"><?= $d['kuota']; ?></td>
            <td><?= $d['penyelenggara'] ?? '-'; ?></td>
            <td class="text-center">
    <?php
        if ($d['kuota'] <= 0) {
            echo '<span class="badge bg-danger">Closed</span>';
        } else {
            echo '<span class="badge bg-success">'.ucfirst($d['status']).'</span>';
        }
    ?>
</td>

            <td class="text-center">
                <a href="?page=event&action=delete&id=<?= $d['id_event']; ?>" 
                onclick="return confirm('Yakin hapus event ini?')" 
                class="btn btn-sm btn-danger">
                <i class="bi bi-trash"></i>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if(mysqli_num_rows($data) == 0): ?>
        <tr>
            <td colspan="10" class="text-center text-muted fst-italic">Belum ada data event.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php elseif ($action == 'create'): ?>
<div class="mb-3">
    <a href="?page=event" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light"><strong>Form Tambah Event</strong></div>
    <div class="card-body">
        <form method="POST" action="?page=event&action=create">
            <div class="mb-3">
                <label>Judul Event</label>
                <input type="text" name="judul" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label>Tanggal Mulai</label>
                <input type="datetime-local" name="tanggal_mulai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Tanggal Selesai</label>
                <input type="datetime-local" name="tanggal_selesai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Lokasi</label>
                <input type="text" name="lokasi" class="form-control">
            </div>

            <div class="mb-3">
                <label>Kuota</label>
                <input type="number" name="kuota" class="form-control">
            </div>

            <div class="mb-3">
                <label>Penyelenggara</label>
                <select name="penyelenggara_id" class="form-select">
                    <option value="">-- Pilih Penyelenggara --</option>
                    <?php while($k=mysqli_fetch_assoc($konselor)): ?>
                        <option value="<?= $k['id_konselor']; ?>"><?= htmlspecialchars($k['nama_lengkap']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                    <option value="canceled">Canceled</option>
                </select>
            </div>

            <button type="submit" name="tambah" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="?page=event" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
<?php endif; ?>
