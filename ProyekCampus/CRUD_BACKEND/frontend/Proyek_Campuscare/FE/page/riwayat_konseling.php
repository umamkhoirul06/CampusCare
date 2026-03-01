<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

/* ================== VALIDASI LOGIN ================== */
/* HALAMAN INI KHUSUS MAHASISWA */
if (!isset($_SESSION['id_mahasiswa'])) {
    header("Location: ../../login.php");
    exit;
}

$id_mahasiswa = (int) $_SESSION['id_mahasiswa'];

/* ================== QUERY DATA (KHUSUS MAHASISWA LOGIN) ================== */
$sql = "
    SELECT tanggal, waktu, topik, status, catatan
    FROM riwayat_konseling
    WHERE id_mahasiswa = ?
      AND archived_at IS NULL
    ORDER BY tanggal DESC, waktu DESC
";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Konseling</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
.content{margin-left:260px;padding:30px;width:calc(100% - 260px)}
h2{font-size:22px;font-weight:700}
.subtitle{color:#666;margin-bottom:20px}

.card{
    background:#fff;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,.1);
    padding:20px
}

table{
    width:100%;
    border-collapse:collapse;
    font-size:14px
}

thead{background:#f1f1f1}

th,td{
    border:1px solid #ddd;
    padding:10px;
    vertical-align:top
}

th{font-weight:600;color:#333}
td{color:#555}

.center{text-align:center;color:#888}

.status{
    padding:4px 10px;
    border-radius:6px;
    color:#fff;
    font-size:12px
}
.status.selesai{background:#5cb85c}
.status.pending{background:#f0ad4e}
.status.ditolak{background:#d9534f}

.muted{color:#9ca3af}
</style>
</head>

<body>

<div class="content">
    <h2>Riwayat Konseling</h2>
    <p class="subtitle">Catatan hasil konseling dari dosen</p>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Topik</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($row['waktu']) ?></td>
                    <td><?= htmlspecialchars($row['topik']) ?></td>
                    <td>
                        <span class="status <?= strtolower($row['status']) ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?= $row['catatan']
                            ? nl2br(htmlspecialchars($row['catatan']))
                            : '<span class="muted">Belum ada catatan</span>' ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="center">
                        Belum ada riwayat konseling
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php mysqli_close($koneksi); ?>
