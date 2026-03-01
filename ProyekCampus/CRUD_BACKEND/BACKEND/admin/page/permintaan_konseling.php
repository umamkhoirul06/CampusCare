<?php
include_once(__DIR__ . '/../../../koneksi.php');

$action = $_GET['action'] ?? 'list';

if (isset($_GET['id'])) {

    $id = (int)$_GET['id'];

    $res = mysqli_query($koneksi, "SELECT id_mahasiswa, topik_masalah, tanggal_konseling, waktu_mulai FROM permintaan_konseling WHERE id_permintaan=$id");
    if(!$res) die("Query error: " . mysqli_error($koneksi));

    if(mysqli_num_rows($res) == 0){
        echo "<script>alert('Data permintaan konseling tidak ditemukan.'); window.location='index.php?page=permintaan_konseling';</script>";
        exit;
    }

    $data_permintaan = mysqli_fetch_assoc($res);
    $id_mahasiswa = $data_permintaan['id_mahasiswa'];
    
    $detail_riwayat = "Topik: " . $data_permintaan['topik_masalah'] . ", Tanggal: " . $data_permintaan['tanggal_konseling'] . " " . $data_permintaan['waktu_mulai'];

    if ($action == 'delete') {
        $del1 = mysqli_query($koneksi, "DELETE FROM permintaan_konseling WHERE id_permintaan=$id");
        if(!$del1) die("Delete error: " . mysqli_error($koneksi));

        $del2 = mysqli_query($koneksi, "DELETE FROM riwayat_layanan WHERE id_user=$id_mahasiswa AND jenis='Konseling' AND detail = '" . mysqli_real_escape_string($koneksi, $detail_riwayat) . "' LIMIT 1");
        if(!$del2) die("Delete error riwayat: " . mysqli_error($koneksi));

        echo "<script>alert('Data berhasil dihapus!'); window.location='index.php?page=permintaan_konseling';</script>";
        exit;
    }

    if ($action == 'setujui') {
        $status_admin = 'disetujui';
        $status_pk = 'Disetujui';
        $alert_msg = 'disetujui';
    } elseif ($action == 'tolak') {
        $status_admin = 'ditolak';
        $status_pk = 'Ditolak';
        $alert_msg = 'ditolak';
    }

    if (isset($status_pk)) {

    // Update status di permintaan konseling
    $upd1 = mysqli_query($koneksi, "UPDATE permintaan_konseling SET status='$status_pk' WHERE id_permintaan=$id");
    if(!$upd1) die("Update error: " . mysqli_error($koneksi));

    // Update riwayat layanan
    $upd2 = mysqli_query($koneksi, "
        UPDATE riwayat_layanan 
        SET status='$status' 
        WHERE id_user=$id_mahasiswa 
        AND jenis='Konseling'
        AND detail = '" . mysqli_real_escape_string($koneksi, $detail_riwayat) . "'
        LIMIT 1
    ");

    // ⬇⬇ TAMBAHKAN BLOK INI UNTUK INSERT KE TABEL request_konseling_dosen
    if ($action == 'setujui') {

        // Ambil data permintaan lengkap
        $p = mysqli_fetch_assoc(mysqli_query($koneksi, "
            SELECT *
            FROM permintaan_konseling
            WHERE id_permintaan = $id
        "));

        // Insert ke tabel request_konseling_dosen
$insert_dosen = mysqli_query($koneksi, "
    INSERT INTO request_konseling 
    (id_mahasiswa, id_konselor, nama_mahasiswa, nim, tanggal_request, tanggal_konseling, waktu, topik, status)
    VALUES (
        '".$p['id_mahasiswa']."',
        '".$p['id_konselor']."',
        (SELECT nama_lengkap FROM mahasiswa WHERE id_mahasiswa = ".$p['id_mahasiswa']." LIMIT 1),
        (SELECT nim FROM mahasiswa WHERE id_mahasiswa = ".$p['id_mahasiswa']." LIMIT 1),
        NOW(),
        '".$p['tanggal_konseling']."',
        '".$p['waktu_mulai']."',
        '".mysqli_real_escape_string($koneksi, $p['topik_masalah'])."',
        'Menunggu'
    )
");


        if (!$insert_dosen) {
            error_log('Gagal insert ke request_konseling: ' . mysqli_error($koneksi));
        }
    }
    // ⬆⬆ BLOK SAMPAI SINI

    echo "<script>alert('Permintaan konseling telah $alert_msg!'); window.location='index.php?page=permintaan_konseling';</script>";
    exit;
}

}

$data = mysqli_query($koneksi, "
    SELECT p.*, m.nama_lengkap AS mahasiswa, k.nama_lengkap AS konselor
    FROM permintaan_konseling p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    JOIN dosen_konselor k ON p.id_konselor = k.id_konselor
    ORDER BY p.tanggal_konseling DESC
");
?>

<h2 class="mb-4">Manajemen Permintaan Konseling</h2>
<?php
// Ambil daftar konselor
$listKonselor = mysqli_query($koneksi, "SELECT id_konselor, nama_lengkap FROM dosen_konselor ORDER BY nama_lengkap ASC");
?>

<form action="page/report_konseling.php" method="GET" class="row g-3 mb-4">

    <div class="col-md-3">
        <label class="form-label fw-bold">Rentang Waktu</label>
       <select name="range" id="select-range" class="form-select">
    <option value="">-- Custom --</option>
    <option value="1">1 Bulan Terakhir</option>
    <option value="3">3 Bulan Terakhir</option>
    <option value="6">6 Bulan Terakhir</option>
</select>

    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Tanggal Mulai</label>
        <input type="date" name="start" id="startDate" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Tanggal Selesai</label>
        <input type="date" name="end" id="endDate" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Filter Konselor</label>
        <select name="konselor" class="form-select">
            <option value="">Semua Konselor</option>
            <?php while($k = mysqli_fetch_assoc($listKonselor)): ?>
                <option value="<?= $k['id_konselor']; ?>"><?= $k['nama_lengkap']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Format Laporan</label>
        <select name="format" class="form-select">
            <option value="csv">Spreadsheet (.csv)</option>
            <option value="excel">Excel (.xlsx)</option>
            <option value="word">Word (.docx)</option>
        </select>
    </div>

    <div class="col-md-12 text-end">
        <button class="btn btn-primary mt-2">Download Laporan</button>
    </div>

</form>


<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="5%">No</th>
            <th>Mahasiswa</th>
            <th>Konselor</th>
            <th>Topik Masalah</th>
            <th>Tanggal Konseling</th>
            <th>Status</th>
            <th width="25%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($p=mysqli_fetch_assoc($data)): ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($p['mahasiswa']); ?></td>
            <td><?= htmlspecialchars($p['konselor']); ?></td>
            <td><?= htmlspecialchars($p['topik_masalah']); ?></td>
            <td><?= htmlspecialchars($p['tanggal_konseling']); ?></td>
            <td class="text-center">
                <?php
                $status = strtolower(trim($p['status']));

                if ($status == 'disetujui'):
                ?>
                    <span class="badge bg-success">Disetujui</span>
                <?php elseif ($status == 'ditolak'): ?>
                    <span class="badge bg-danger">Ditolak</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <?php if ($status == 'pending'): ?>
                    <a href="index.php?page=permintaan_konseling&action=setujui&id=<?= $p['id_permintaan']; ?>" 
                    onclick="return confirm('Setujui permintaan ini?')" 
                    class="btn btn-sm btn-success">Disetujui</a>
                    <a href="index.php?page=permintaan_konseling&action=tolak&id=<?= $p['id_permintaan']; ?>" 
                    onclick="return confirm('Tolak permintaan ini?')" 
                    class="btn btn-sm btn-warning">Ditolak</a>
                <?php endif; ?>
                <a href="index.php?page=permintaan_konseling&action=delete&id=<?= $p['id_permintaan']; ?>" 
                onclick="return confirm('Yakin ingin menghapus data ini?')" 
                class="btn btn-sm btn-danger">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($data) == 0): ?>
        <tr>
            <td colspan="7" class="text-center">Belum ada permintaan konseling.</td>
        </tr>
        <?php endif; ?>
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
        // Custom Range → tanggal aktif lagi
        startInput.removeAttribute("disabled");
        endInput.removeAttribute("disabled");
    }
});
</script>

    </tbody>
</table>
</div>