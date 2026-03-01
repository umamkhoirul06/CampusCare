<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php';

// Pastikan variabel $koneksi sudah didefinisikan dari file koneksi.php
// Asumsi $koneksi adalah objek koneksi yang valid.
$db_koneksi = isset($koneksi) ? $koneksi : (isset($conn) ? $conn : null);

if (!$db_koneksi) {
    // Handle jika koneksi gagal (meskipun seharusnya sudah dihandle di koneksi.php)
    die("Error: Koneksi database tidak tersedia.");
}

$id_mahasiswa = $_SESSION['id_mahasiswa'] ?? 0;
// Dapatkan nama mahasiswa untuk riwayat layanan
$nama = $_SESSION['nama'] ?? 'Mahasiswa'; 


// ----------------------
// PROSES PEMBATALAN BOOKING
// ----------------------
if (isset($_GET['aksi'], $_GET['id']) && $_GET['aksi'] === 'batal') {
    $id_permintaan = (int)$_GET['id'];
    
    // Gunakan prepared statement atau minimal mysqli_real_escape_string
    $id_permintaan_esc = mysqli_real_escape_string($db_koneksi, $id_permintaan);
    $id_mahasiswa_esc = mysqli_real_escape_string($db_koneksi, $id_mahasiswa);
    
    $update = "UPDATE permintaan_konseling 
               SET status='dibatalkan' 
               WHERE id_permintaan='$id_permintaan_esc' 
               AND id_mahasiswa='$id_mahasiswa_esc' 
               AND LOWER(TRIM(status))='pending'";
    
    if (mysqli_query($db_koneksi, $update)) {
        echo "<script>alert('Booking berhasil dibatalkan!'); window.location.href='booking_konseling.php';</script>";
    } else {
        echo "<script>alert('Gagal membatalkan booking: " . mysqli_error($db_koneksi) . "'); window.location.href='booking_konseling.php';</script>";
    }
    exit;
}

// ----------------------
// AMBIL DATA JADWAL TERSEDIA
// ----------------------
$jadwal_konseling = [];
$sql = "SELECT jk.id_jadwal, jk.id_konselor, dk.nama_lengkap AS nama_dosen, 
               jk.hari, jk.waktu_mulai, jk.waktu_selesai, jk.kuota_harian
        FROM jadwal_konseling jk
        JOIN dosen_konselor dk ON jk.id_konselor = dk.id_konselor
        ORDER BY jk.hari, jk.waktu_mulai";
$q1 = mysqli_query($db_koneksi, $sql);
if ($q1) {
    while ($r = mysqli_fetch_assoc($q1)) {
        $jadwal_konseling[] = $r;
    }
}

// ----------------------
// PROSES BOOKING
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'booking') {
    $id_jadwal = $_POST['id_jadwal'];
    $id_konselor = $_POST['id_konselor'];
    $topik = $_POST['topik'];
    $tanggal_konseling = $_POST['tanggal_konseling'];
    $waktu_mulai = $_POST['waktu_mulai'];

    if (!empty($id_jadwal) && !empty($id_konselor) && !empty($topik) && !empty($tanggal_konseling) && !empty($waktu_mulai)) {
        
        // Sanitisasi Input
        $id_jadwal_esc = mysqli_real_escape_string($db_koneksi, $id_jadwal);
        $id_konselor_esc = mysqli_real_escape_string($db_koneksi, $id_konselor);
        $topik_esc = mysqli_real_escape_string($db_koneksi, $topik);
        $tanggal_konseling_esc = mysqli_real_escape_string($db_koneksi, $tanggal_konseling);
        $waktu_mulai_esc = mysqli_real_escape_string($db_koneksi, $waktu_mulai);
        $id_mahasiswa_esc = mysqli_real_escape_string($db_koneksi, $id_mahasiswa);
        $nama_esc = mysqli_real_escape_string($db_koneksi, $nama);
        
        // Ambil hari dari tabel jadwal (menggunakan ID yang sudah di-escape)
        $jadwal_query = mysqli_query($db_koneksi, "SELECT hari FROM jadwal_konseling WHERE id_jadwal='$id_jadwal_esc'");
        $jadwal = mysqli_fetch_assoc($jadwal_query);
        $hari = mysqli_real_escape_string($db_koneksi, $jadwal['hari'] ?? 'N/A');

        // Simpan ke tabel permintaan_konseling
        $insert = "INSERT INTO permintaan_konseling 
                    (id_mahasiswa, id_konselor, hari, waktu_mulai, tanggal_konseling, topik_masalah, status, created_at)
                   VALUES 
                    ('$id_mahasiswa_esc', '$id_konselor_esc', '$hari', '$waktu_mulai_esc', '$tanggal_konseling_esc', '$topik_esc', 'pending', NOW())";

        if (mysqli_query($db_koneksi, $insert)) {
            // Simpan juga ke riwayat layanan
            $detail = "Topik: $topik, Waktu: $hari, $tanggal_konseling $waktu_mulai";
            $detail_esc = mysqli_real_escape_string($db_koneksi, $detail);
            
            $sql_riwayat = "INSERT INTO riwayat_layanan (id_user, nama_user, role_user, jenis, detail)
                            VALUES ('$id_mahasiswa_esc', '$nama_esc', 'mahasiswa', 'Konseling', '$detail_esc')";
            mysqli_query($db_koneksi, $sql_riwayat);

            echo "<script>alert('Booking berhasil!'); window.location.href='booking_konseling.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal menyimpan booking: " . mysqli_error($db_koneksi) . "');</script>";
        }
    } else {
        echo "<script>alert('Lengkapi semua data!');</script>";
    }
}

// ----------------------
// RIWAYAT BOOKING MAHASISWA
// ----------------------
$riwayat_booking = [];
$id_mahasiswa_esc = mysqli_real_escape_string($db_koneksi, $id_mahasiswa);

$sql2 = "SELECT pk.id_permintaan, pk.tanggal_konseling AS tanggal, pk.hari AS hari, pk.waktu_mulai, pk.topik_masalah, pk.status, 
                dk.nama_lengkap AS nama_dosen
         FROM permintaan_konseling pk
         JOIN dosen_konselor dk ON pk.id_konselor = dk.id_konselor
         WHERE pk.id_mahasiswa = '$id_mahasiswa_esc'
         ORDER BY pk.created_at DESC";

$q2 = mysqli_query($db_koneksi, $sql2);
if ($q2) {
    while ($r = mysqli_fetch_assoc($q2)) {
        $riwayat_booking[] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Konseling - Campus Care</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* CSS Styling */
.content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
.content h2 { font-size: 22px; font-weight: 700; color: #222; }
.content p { color: #666; margin-top: 5px; margin-bottom: 20px; }

.card { background: #fff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 25px; }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.card-header h4 { font-size: 16px; font-weight: 600; }

.btn-primary { background-color: #0066ff; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: 0.2s; }
.btn-primary:hover { background-color: #004cd6; }
.btn-batal { background-color: #dc2626; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; transition: 0.2s; }
.btn-batal:hover { background-color: #b91c1c; }

table { width: 100%; border-collapse: collapse; font-size: 14px; }
thead { background-color: #f1f1f1; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
th { color: #333; font-weight: 600; }
td { color: #555; }
td.center { text-align: center; color: #888; }

.status-pending { color: #d97706; font-weight: 600; }
.status-disetujui { color: #16a34a; font-weight: 600; }
.status-ditolak { color: #dc2626; font-weight: 600; }
.status-dibatalkan { color: #888; font-weight: 600; }

.modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center; }
.modal-content { background: white; border-radius: 10px; width: 420px; padding: 25px; position: relative; }
.close-btn { position: absolute; top: 12px; right: 20px; font-size: 22px; cursor: pointer; color: gray; }
.modal-footer { margin-top: 15px; }
label { font-size: 14px; font-weight: 500; display: block; margin-top: 10px; margin-bottom: 5px; }
select, input, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
textarea { resize: none; height: 70px; }
</style>
</head>
<body>

<div class="content">
    <h2>Booking Konseling</h2>
    <p>Jadwalkan konseling dengan dosen</p>

    <div class="card">
        <div class="card-header"><h4>Jadwal Konseling Tersedia</h4></div>
        <table>
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Waktu</th>
                    <th>Dosen</th>
                    <th>Kuota Harian</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jadwal_konseling)): ?>
                    <tr><td colspan="5" class="center">Belum ada jadwal tersedia</td></tr>
                <?php else: ?>
                    <?php foreach ($jadwal_konseling as $j): ?>
                        <tr>
                            <td><?= htmlspecialchars($j['hari']); ?></td>
                            <td><?= htmlspecialchars($j['waktu_mulai']) . ' - ' . htmlspecialchars($j['waktu_selesai']); ?></td>
                            <td><?= htmlspecialchars($j['nama_dosen']); ?></td>
                            <td><?= htmlspecialchars($j['kuota_harian']); ?></td>
                            <td>
                                <button class="btn-primary"
                                        onclick="bukaModal(<?= $j['id_jadwal']; ?>, <?= $j['id_konselor']; ?>, '<?= $j['nama_dosen']; ?>', '<?= $j['hari']; ?>', '<?= $j['waktu_mulai']; ?> - <?= $j['waktu_selesai']; ?>')">
                                    Booking
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header"><h4>Riwayat Booking Konseling</h4></div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Waktu</th>
                    <th>Dosen</th>
                    <th>Topik</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat_booking)): ?>
                    <tr><td colspan="7" class="center">Belum ada riwayat booking</td></tr>
                <?php else: ?>
                    <?php foreach ($riwayat_booking as $r): 
                        $statusClass = 'status-' . strtolower($r['status']);
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($r['tanggal']); ?></td>
                            <td><?= htmlspecialchars($r['hari']); ?></td>
                            <td><?= htmlspecialchars($r['waktu_mulai']); ?></td>
                            <td><?= htmlspecialchars($r['nama_dosen']); ?></td>
                            <td><?= htmlspecialchars($r['topik_masalah']); ?></td>
                            <td class="<?= $statusClass; ?>"><?= ucfirst($r['status']); ?></td>
                            <td>
                                <?php if (trim(strtolower($r['status'])) === 'pending'): ?>
                                    <a href="?aksi=batal&id=<?= $r['id_permintaan']; ?>" class="btn-batal"
                                       onclick="return confirm('Yakin ingin membatalkan booking ini?')">Batal</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>


    <div class="modal" id="modalBooking">
        <div class="modal-content">
            <span class="close-btn" onclick="tutupModal()">&times;</span>
            <h3>Booking Konseling</h3>
            <form method="POST">
                <input type="hidden" name="aksi" value="booking">
                <input type="hidden" name="id_jadwal" id="id_jadwal">
                <input type="hidden" name="id_konselor" id="id_konselor">

                <div class="form-group">
                    <label>Nama Dosen</label>
                    <input type="text" id="nama_dosen" readonly>
                </div>

                <div class="form-group">
                    <label>Hari</label>
                    <input type="text" id="hari_konseling" readonly>
                </div>

                <div class="form-group">
                    <label>Tanggal Konseling</label>
                    <input type="date" name="tanggal_konseling" id="tanggal_konseling" required>
                </div>

                <div class="form-group">
                    <label>Pilih Jam</label>
                    <select name="waktu_mulai" id="pilih_waktu" required></select>
                </div>

                <div class="form-group">
                    <label>Topik Konseling</label>
                    <textarea name="topik" required placeholder="Tuliskan topik konseling..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-primary">Ajukan Booking</button>
                </div>
            </form>
        </div>
    </div>

</div> <script>
// Fungsi untuk mendapatkan tanggal hari ini dalam format YYYY-MM-DD
function getTodayDate() {
    const today = new Date();
    const year = today.getFullYear();
    // Bulan dan hari harus memiliki dua digit (misal: 01, 09)
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function bukaModal(id_jadwal, id_konselor, nama_dosen, hari, waktu) {
    document.getElementById('modalBooking').style.display = 'flex';
    document.getElementById('id_jadwal').value = id_jadwal;
    document.getElementById('id_konselor').value = id_konselor;
    document.getElementById('nama_dosen').value = nama_dosen;
    document.getElementById('hari_konseling').value = hari;

    // 1. Mengatur Tanggal Minimum (Perubahan Utama)
    const inputTanggal = document.getElementById('tanggal_konseling');
    const todayDate = getTodayDate();
    inputTanggal.min = todayDate;
    
    // Set tanggal default ke hari ini (jika belum ada nilai)
    if (!inputTanggal.value || inputTanggal.value < todayDate) {
        inputTanggal.value = todayDate;
    }
    
    // 2. Mengisi Dropdown Waktu
    const [mulai, selesai] = waktu.split(' - ');
    const dropdown = document.getElementById('pilih_waktu');
    dropdown.innerHTML = "";

    // Logika pengisian waktu setiap 30 menit
    const start = new Date(`1970-01-01T${mulai}`);
    const end = new Date(`1970-01-01T${selesai}`);

    // Pastikan batas akhir jam tidak terlewat
    end.setMinutes(end.getMinutes() - 1); 

    let isFirst = true;
    while (start.getTime() <= end.getTime()) {
        const jam = start.toTimeString().substring(0, 5);
        const option = document.createElement('option');
        option.value = jam;
        option.textContent = jam;
        // Pilih opsi pertama secara default
        if (isFirst) {
            option.selected = true;
            isFirst = false;
        }
        dropdown.appendChild(option);
        start.setMinutes(start.getMinutes() + 30); // interval 30 menit
    }
}

function tutupModal() {
    document.getElementById('modalBooking').style.display = 'none';
}

// Menutup modal jika klik di luar area modal
window.onclick = function(e) {
    const modal = document.getElementById('modalBooking');
    if (e.target === modal) tutupModal();
};
</script>

</body>
</html>