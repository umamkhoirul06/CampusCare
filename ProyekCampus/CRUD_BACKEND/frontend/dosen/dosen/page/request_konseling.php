<?php
session_start();
include '../../koneksi.php';

if (!isset($_SESSION['dosen'])) {
    header("Location: ../../login.php");
    exit;
}

$dosen = $_SESSION['dosen'];
$nama_dosen = $dosen['nama_lengkap'] ?? 'Dosen Konselor';
$id_konselor = $dosen['id_konselor'];

/* ===========================================================
   =============== PROSES SETUJU / TOLAK ======================
   ===========================================================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_request = $_POST['id_request'];
    $aksi = $_POST['aksi'];

    // Ambil detail request
    $get = mysqli_query($conn, "SELECT * FROM request_konseling WHERE id_request='$id_request'");
    $req = mysqli_fetch_assoc($get);

    if ($aksi === "setuju") {

        // Insert ke schedule_konseling
        mysqli_query($conn, "
            INSERT INTO schedule_konseling 
            (id_konselor, nama_mahasiswa, nim, tanggal_konseling, waktu, topik, status)
            VALUES (
                '$id_konselor',
                '{$req['nama_mahasiswa']}',
                '{$req['nim']}',
                '{$req['tanggal_konseling']}',
                '{$req['waktu']}',
                '{$req['topik']}',
                'Menunggu'
            )
        ");

        // Hapus request
        mysqli_query($conn, "DELETE FROM request_konseling WHERE id_request='$id_request'");

        header("Location: request_konseling.php?status=setuju");
        exit;

    } elseif ($aksi === "tolak") {

        mysqli_query($conn, "DELETE FROM request_konseling WHERE id_request='$id_request'");
        header("Location: request_konseling.php?status=tolak");
        exit;
    }
}

/* ===========================================================
   =============== LOAD DATA REQUEST ==========================
   ===========================================================*/

$query = mysqli_query($conn, "
    SELECT 
        id_request,
        tanggal_request,
        tanggal_konseling,
        waktu,
        topik,
        nama_mahasiswa,
        nim
    FROM request_konseling
    WHERE id_konselor = '$id_konselor'
    ORDER BY tanggal_request DESC
");

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Request Konseling - Campus Care</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
/* ————— (CSS tetap sama seperti punya kamu) ————— */
body {
    margin: 0;
    display: flex;
    font-family: 'Poppins', sans-serif;
    background: #f7f8fa;
    color: #333;
}

.logo {
    text-align: center;
    font-weight: 600;
    font-size: 18px;
    color: #2563eb;
    margin-bottom: 20px;
}
.profile {
    display: flex;
    align-items: center;
    padding: 0 20px;
    margin-bottom: 15px;
}
.profile .initial {
    background: #2563eb;
    color: white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 10px;
}
.sidebar a {
    display: block;
    color: #555;
    text-decoration: none;
    padding: 10px 25px;
    margin: 4px 0;
    border-radius: 8px;
    font-size: 14px;
    transition: 0.2s;
}
.sidebar a i { margin-right: 10px; }
.sidebar a:hover, .sidebar a.active {
    background: #2563eb;
    color: white;
}

.main-content {
    flex: 1;
    margin-left: 250px;
    padding: 30px;
}
.header h2 {
    font-size: 22px;
    color: #2563eb;
    margin: 0;
}
.header p {
    color: #666;
    font-size: 14px;
}
.table-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 20px;
}
.table-box h4 {
    color: #2563eb;
    margin-bottom: 15px;
    font-weight: 600;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}
thead {
    background: #f3f6ff;
}
.btn-aksi {
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 13px;
    cursor: pointer;
    color: #fff;
}
.btn-danger { background: #dc2626; }
.btn-success { background: #2563eb; }
.btn-danger:hover { background: #b91c1c; }
.btn-success:hover { background: #1d4ed8; }
</style>
</head>
<body>

<?php include '../../sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h2>Request Konseling</h2>
        <p>Review dan kelola permintaan konseling mahasiswa.</p>
    </div>

    <div class="table-box">
        <h4>Daftar Request Konseling</h4>
        <table>
            <thead>
                <tr>
                    <th>Tanggal Request</th>
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>Tanggal Konseling</th>
                    <th>Waktu</th>
                    <th>Topik</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>

                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['tanggal_request'])) ?></td>
                        <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                        <td><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal_konseling'])) ?></td>
                        <td><?= htmlspecialchars($row['waktu']) ?></td>
                        <td><?= htmlspecialchars($row['topik']) ?></td>
                        <td style="text-align:center;">
                            
                            <form method="POST" action="" style="display:inline-block;">
                                <input type="hidden" name="id_request" value="<?= $row['id_request'] ?>">
                                <button type="submit" name="aksi" value="tolak" class="btn-aksi btn-danger">Tolak</button>
                                <button type="submit" name="aksi" value="setuju" class="btn-aksi btn-success">Setuju</button>
                            </form>

                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px; color:#777;">
                            Belum ada request konseling.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php mysqli_close($conn); ?>
