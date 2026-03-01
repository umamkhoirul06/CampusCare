<?php
include '../../koneksi.php';
session_start();

if (!isset($_SESSION['dosen'])) {
    header("Location: ../../login.php");
    exit;
}

// ambil id konselor dari session (sesuaikan kalau nama field beda)
$id_konselor = $_SESSION['dosen']['id_konselor'] ?? null;

// ================== KLIK SELESAI ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_schedule'])) {
    $id_schedule = (int)$_POST['id_schedule'];

    // 1. Ambil data schedule
    $stmt = $conn->prepare("SELECT * FROM schedule_konseling WHERE id_schedule = ?");
    $stmt->bind_param("i", $id_schedule);
    $stmt->execute();
    $schedule = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($schedule) {

        // 2. Ambil id_mahasiswa dari tabel mahasiswa berdasarkan NIM
        $stmtMhs = $conn->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim = ?");
        $stmtMhs->bind_param("s", $schedule['nim']);
        $stmtMhs->execute();
        $mhs = $stmtMhs->get_result()->fetch_assoc();
        $stmtMhs->close();

        if (!$mhs) {
            die("Mahasiswa dengan NIM {$schedule['nim']} tidak ditemukan");
        }

        $id_mahasiswa = $mhs['id_mahasiswa'];

        // 3. Insert ke riwayat_konseling
        // 4) Insert ke riwayat_konseling (PAKAI NAMA & NIM)
$stmt2 = $conn->prepare("
    INSERT INTO riwayat_konseling
    (
        id_schedule,
        id_konselor,
        id_mahasiswa,
        nama_mahasiswa,
        nim,
        tanggal,
        waktu,
        topik,
        status,
        catatan,
        created_at
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Selesai', '', NOW())
");

$tanggal = $schedule['tanggal_konseling'];
$waktu   = $schedule['waktu'];
$topik   = $schedule['topik'];

$stmt2->bind_param(
    "iiisssss",
    $id_schedule,                 // id_schedule
    $id_konselor,                 // id_konselor (dari session)
    $id_mahasiswa,               // id_mahasiswa (hasil lookup)
    $schedule['nama_mahasiswa'], // NAMA MAHASISWA
    $schedule['nim'],            // NIM
    $tanggal,
    $waktu,
    $topik
);

$stmt2->execute();
$stmt2->close();


        // 4. Update status schedule
        $stmt3 = $conn->prepare("UPDATE schedule_konseling SET status = 'Selesai' WHERE id_schedule = ?");
        $stmt3->bind_param("i", $id_schedule);
        $stmt3->execute();
        $stmt3->close();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// ================== AMBIL DATA JADWAL ==================
$stmtList = $conn->prepare("
    SELECT * 
    FROM schedule_konseling
    WHERE id_konselor = ?
    ORDER BY tanggal_konseling DESC, waktu DESC
");
$stmtList->bind_param("i", $id_konselor);
$stmtList->execute();
$result = $stmtList->get_result();

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal Konseling - Campus Care</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
body { margin:0; display:flex; font-family:'Poppins',sans-serif; background:#f7f8fa; color:#333; }
.main-content { flex:1; margin-left:250px; padding:30px; }
.header { margin-bottom:25px; }
.header h2 { font-size:22px; color:#2563eb; margin:0; }
.header p { color:#666; font-size:14px; }
.table-box { background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); padding:20px; }
.table-box h4 { color:#2563eb; margin-bottom:15px; font-weight:600; }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; text-align:left; border-bottom:1px solid #eee; font-size:14px; }
thead { background:#f3f6ff; }
.status { padding:5px 10px; border-radius:6px; font-size:13px; font-weight:500; }
.status.Menunggu { background:#fff3cd; color:#856404; }
.status.Disetujui { background:#d1e7dd; color:#0f5132; }
.status.Selesai { background:#e2e3e5; color:#41464b; }
.btn-aksi { border:none; border-radius:6px; padding:6px 12px; font-size:13px; cursor:pointer; color:#fff; }
.btn-success { background:#2563eb; }
.text-muted { color:#777; }
</style>
</head>
<body>

<?php include '../../sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h2>Jadwal Konseling</h2>
        <p>Berikut daftar seluruh jadwal konseling mahasiswa.</p>
    </div>

    <div class="table-box">
        <h4>Daftar Jadwal Konseling</h4>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Mahasiswa</th>
                    <th>NIM</th>
                    <th>Topik</th>
                    <th>Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['tanggal_konseling'])) ?></td>
                            <td><?= htmlspecialchars($row['waktu']) ?></td>
                            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                            <td><?= htmlspecialchars($row['nim']) ?></td>
                            <td><?= htmlspecialchars($row['topik']) ?></td>
                            <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                            <td style="text-align:center;">
                                <?php if ($row['status'] === 'Menunggu' || $row['status'] === 'Disetujui'): ?>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="id_schedule" value="<?= (int)$row['id_schedule'] ?>">
                                        <button type="submit" class="btn-aksi btn-success">Selesai</button>
                                    </form>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px; color:#777;">
                            Belum ada jadwal konseling.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
