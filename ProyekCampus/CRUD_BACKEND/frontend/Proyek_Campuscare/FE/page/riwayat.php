<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

$user_id = $_SESSION['id_mahasiswa']; // id mahasiswa

// Ambil data riwayat layanan mahasiswa ini saja
$query = "

SELECT 
    'Fasilitas' AS jenis,
    bf.id_fasilitas AS detail,
    bf.status,
    bf.created_at AS dibuat_pada
FROM booking_fasilitas bf
WHERE bf.id_mahasiswa = '$user_id'

UNION ALL

SELECT
    'Event' AS jenis,
    e.id_event AS detail,
    CASE 
        WHEN pe.status_hadir = 1 THEN 'hadir'
        WHEN pe.status_hadir = 0 THEN 'tidakhadir'
        ELSE 'pending'
    END AS status,
    pe.registered_at AS dibuat_pada
FROM pendaftaran_event pe
JOIN event e ON pe.id_event = e.id_event
WHERE pe.id_mahasiswa = '$user_id'

UNION ALL

SELECT
    'Konseling' AS jenis,
    k.topik_masalah AS detail,
    k.status,
    k.tanggal_konseling AS dibuat_pada
FROM permintaan_konseling k
WHERE k.id_mahasiswa = '$user_id'

UNION ALL

SELECT
    'Laporan' AS jenis,
    lp.judul AS detail,
    lp.status,
    lp.created_at AS dibuat_pada
FROM laporan_pengaduan lp
WHERE lp.id_mahasiswa = '$user_id'

ORDER BY dibuat_pada DESC
";


$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Layanan - Campus Care</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.content {
    margin-left: 260px;
    padding: 30px;
    width: calc(100% - 260px);
}

.content h2 {
    font-size: 22px;
    font-weight: 700;
    color: #222;
}

.content p.subtitle {
    color: #666;
    margin-top: 5px;
    margin-bottom: 20px;
}

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    padding: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    margin-top: 10px;
}

thead {
    background-color: #f1f1f1;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

th {
    color: #333;
    font-weight: 600;
}

td {
    color: #555;
    vertical-align: top;
}

td.center {
    text-align: center;
    color: #888;
}

.status {
    padding: 5px 10px;
    border-radius: 6px;
    color: white;
    font-size: 12px;
    text-transform: capitalize;
}
.status.pending, .status.baru { background-color: #f0ad4e; }
.status.diterima, .status.disetujui, .status.hadir { background-color: #5cb85c; }
.status.ditolak, .status.tidakhadir { background-color: #d9534f; }
.status.null { background-color: #6c757d; }
.status.selesai { background:#0d6efd; }

</style>
</head>
<body>

<div class="content">
    <h2>Riwayat Layanan</h2>
    <p class="subtitle">Lihat semua aktivitas dan riwayat layanan Anda</p>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Detail</th>
                    <th>Status</th>
                    <th>Dibuat Pada</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) == 0): ?>
                    <tr><td colspan="5" class="center">Belum ada riwayat layanan.</td></tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['jenis']); ?></td>
                            <td style="max-width:250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($row['detail']); ?></td>
                            <td>
    <?php
    $status = strtolower($row['status'] ?? 'pending');
    ?>
    <span class="status <?= $status ?>">
        <?= ucfirst($status) ?>
    </span>
</td>
                            <td><?= $row['dibuat_pada']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function confirmLogout() {
  if (confirm("Yakin ingin logout?")) {
    window.location.href = "../../logout.php";
  }
}
</script>
</body>
</html>
