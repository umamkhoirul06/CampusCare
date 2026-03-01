<?php
include '../../koneksi.php';
session_start();

if (!isset($_SESSION['dosen'])) {
    header("Location: ../../login.php");
    exit;
}

$id_konselor = $_SESSION['dosen']['id_konselor'] ?? null;
if (!$id_konselor) {
    die("Session dosen tidak memiliki id_konselor.");
}

// ================== SIMPAN / UPDATE CATATAN ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_catatan'])) {
    $id_riwayat = (int)($_POST['id_riwayat'] ?? 0);
    $catatan    = trim($_POST['catatan'] ?? '');

    $stmtUp = $conn->prepare("UPDATE riwayat_konseling SET catatan = ? WHERE id_riwayat = ? AND id_konselor = ?");
    $stmtUp->bind_param("sii", $catatan, $id_riwayat, $id_konselor);
    $stmtUp->execute();
    $stmtUp->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// mode edit (klik tombol Isi/Edit => ?edit=ID)
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// ================== AMBIL RIWAYAT + JOIN MAHASISWA ==================
$sql = "
    SELECT rk.*,
           m.nim AS nim_mhs,
           m.nama_lengkap AS nama_mhs
    FROM riwayat_konseling rk
    LEFT JOIN mahasiswa m ON m.id_mahasiswa = rk.id_mahasiswa
    WHERE rk.id_konselor = ?
    ORDER BY rk.tanggal DESC, rk.waktu DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_konselor);
$stmt->execute();
$riwayat_konseling = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Konseling - Campus Care</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
body { margin:0; display:flex; font-family:'Poppins',sans-serif; background:#f7f8fa; color:#333; }
.main-content { flex: 1; margin-left: 250px; padding: 30px; }
.header { margin-bottom: 25px; }
.header h2 { font-size: 22px; color: #2563eb; margin: 0; }
.header p { color: #666; font-size: 14px; }

.table-box { background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); padding:20px; }
.table-box h4 { color:#2563eb; margin-bottom:15px; font-weight:600; }

table { width:100%; border-collapse:collapse; }
th, td { padding:10px; text-align:left; border-bottom:1px solid #eee; font-size:14px; vertical-align: top; }
thead { background:#f3f6ff; }

.status { padding:5px 10px; border-radius:6px; font-size:13px; font-weight:500; display:inline-block; }
.status.Selesai { background: #cfe2ff; color: #084298; }

textarea{
  width: 100%;
  min-height: 90px;
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  outline: none;
}

.btn{
  border:none;
  border-radius:10px;
  padding:8px 12px;
  font-size:13px;
  cursor:pointer;
  font-family:'Poppins',sans-serif;
}
.btn-primary{ background:#2563eb; color:#fff; }
.btn-secondary{ background:#e5e7eb; color:#111827; }

.btn-outline{
  border:1px solid #2563eb;
  color:#2563eb;
  background:#fff;
  border-radius:10px;
  padding:7px 12px;
  font-size:13px;
  display:inline-block;
  text-decoration:none;
}
.btn-outline:hover{ background:#2563eb; color:#fff; }

.muted{ color:#9ca3af; }

footer { text-align:center; color:#777; font-size:14px; margin-top:40px; }
/* === Report Box (rapi) === */
.report-box{
  background:#fff;
  border:1px solid #eef2f7;
  border-radius:14px;
  padding:16px;
  margin:0 0 18px;
  box-shadow:0 2px 8px rgba(0,0,0,0.04);
}

.report-grid{
  display:grid;
  grid-template-columns: 1.1fr 1fr 1fr 0.9fr auto;
  column-gap:22px;   /* jarak horizontal */
  row-gap:16px;      /* jarak vertikal */
  align-items:end;
}


.report-box{
  padding:18px 20px; /* biar gak mepet pinggir card */
}


.report-field label{
  display:block;
  font-size:12px;
  font-weight:600;
  color:#374151;
  margin-bottom:6px;
}

.report-input, .report-select{
  width:100%;
  padding:12px 14px;     /* lebih lega */
  border:1px solid #e5e7eb;
  border-radius:12px;
}


.report-input:focus, .report-select:focus{
  border-color:#2563eb;
  box-shadow:0 0 0 3px rgba(37,99,235,0.12);
}

.report-actions{
  display:flex;
  justify-content:flex-end;
}

.report-btn{
  padding:10px 14px;
  border:none;
  border-radius:12px;
  background:#2563eb;
  color:#fff;
  font-size:13px;
  cursor:pointer;
  font-weight:600;
  white-space:nowrap;
  display:flex;
  align-items:center;
  gap:8px;
}

.report-btn:hover{ background:#1d4ed8; }

@media (max-width: 900px){
  .report-grid{
    grid-template-columns: 1fr 1fr;
  }
  .report-actions{
    grid-column: 1 / -1; /* tombol full 1 baris */
    justify-content:stretch;
  }
  .report-btn{ width:100%; justify-content:center; }
}


</style>
</head>
<body>

<?php include '../../sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h2>Riwayat Konseling</h2>
        <p>Berikut daftar riwayat konseling yang telah selesai dilakukan.</p>
    </div>
<?php
date_default_timezone_set('Asia/Jakarta');

$range = $_GET['range'] ?? '';
$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';

if ($range !== '') {
    $end = date('Y-m-d');
    $start = date('Y-m-d', strtotime("-{$range} months"));
}
?>

<div class="report-box">
  <form method="GET" action="report_konseling.php">
    <div class="report-grid">

      <div class="report-field">
        <label>Rentang Waktu</label>
        <select name="range" id="select-range" class="report-select">
          <option value="" <?= $range===''?'selected':''; ?>>Custom</option>
          <option value="1" <?= $range==='1'?'selected':''; ?>>1 Bulan Terakhir</option>
          <option value="3" <?= $range==='3'?'selected':''; ?>>3 Bulan Terakhir</option>
          <option value="6" <?= $range==='6'?'selected':''; ?>>6 Bulan Terakhir</option>
        </select>
      </div>

      <div class="report-field">
        <label>Tanggal Mulai</label>
        <input type="date" name="start" id="input-start" value="<?= htmlspecialchars($start) ?>" class="report-input">
      </div>

      <div class="report-field">
        <label>Tanggal Selesai</label>
        <input type="date" name="end" id="input-end" value="<?= htmlspecialchars($end) ?>" class="report-input">
      </div>

      <div class="report-field">
        <label>Format Laporan</label>
        <select name="format" class="report-select" required>
          <option value="csv">Spreadsheet (.csv)</option>
          <option value="excel">Excel (.xlsx)</option>
          <option value="word">Word (.doc)</option>
        </select>
      </div>

      <div class="report-actions">
  <button type="submit" class="report-btn">
    <i class="fa fa-download"></i> Download
  </button>
</div>


      <input type="hidden" name="id_konselor" value="<?= (int)$id_konselor ?>">
    </div>
  </form>
</div>

<script>
const sel = document.getElementById("select-range");
const startEl = document.getElementById("input-start");
const endEl = document.getElementById("input-end");

function syncDisable(){
  if (sel.value !== "") {
    startEl.disabled = true;
    endEl.disabled = true;
    startEl.value = "";
    endEl.value = "";
  } else {
    startEl.disabled = false;
    endEl.disabled = false;
  }
}
sel.addEventListener("change", syncDisable);
syncDisable();
</script>


    <div class="table-box">
        <h4>Data Riwayat Konseling</h4>

        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Topik</th>
                    <th>Status</th>
                    <th style="min-width:360px;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($riwayat_konseling && $riwayat_konseling->num_rows > 0): ?>
                    <?php while($row = $riwayat_konseling->fetch_assoc()): ?>
                        <?php
                            $id_riwayat = (int)$row['id_riwayat'];
                            $catatan_db = trim($row['catatan'] ?? '');
                            $is_edit = ($edit_id === $id_riwayat);

                            // INI YANG DIPERBAIKI (sesuai alias di SQL)
                            $nama_mhs = $row['nama_mhs'] ?? '-';
                            $nim_mhs  = $row['nim_mhs'] ?? '-';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d M Y', strtotime($row['tanggal']))) ?></td>
                            <td><?= htmlspecialchars($row['waktu']) ?></td>
                            <td><?= htmlspecialchars($nama_mhs) ?></td>
                            <td><?= htmlspecialchars($nim_mhs) ?></td>
                            <td><?= htmlspecialchars($row['topik']) ?></td>
                            <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>

                            <td>
                                <?php if ($is_edit): ?>
                                    <form method="POST">
                                        <input type="hidden" name="id_riwayat" value="<?= $id_riwayat ?>">
                                        <textarea name="catatan" placeholder="Tulis catatan konseling..."><?= htmlspecialchars($catatan_db) ?></textarea>

                                        <div style="margin-top:8px; display:flex; gap:8px;">
                                            <button class="btn btn-primary" type="submit" name="save_catatan">Simpan</button>
                                            <a class="btn btn-secondary" style="text-decoration:none; display:inline-block;"
                                               href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">Batal</a>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <?php if ($catatan_db === ''): ?>
                                        <span class="muted">Belum ada catatan</span>
                                        <a class="btn-outline" style="margin-left:10px;"
                                           href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?edit=<?= $id_riwayat ?>">
                                            Isi
                                        </a>
                                    <?php else: ?>
                                        <div><?= nl2br(htmlspecialchars(mb_strimwidth($catatan_db, 0, 150, '...'))) ?></div>
                                        <a class="btn-outline" style="margin-top:8px;"
                                           href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?edit=<?= $id_riwayat ?>">
                                            Edit
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#777; padding:20px;">Tidak ada riwayat konseling.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <footer>&copy; <?= date('Y') ?> Campus Care | Panel Dosen</footer>
</div>

</body>
</html>

<?php mysqli_close($conn); ?>
