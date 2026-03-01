<?php
include '../../koneksi.php';
// Hapus baris ini: include '../../sidebar.php'; 
session_start();

// Cek login dosen
if (!isset($_SESSION['dosen'])) {
    // Pastikan ini baris pertama yang mengeluarkan output header!
    header("Location: ../../login.php");
    exit;
}

// Ambil data dosen
$dosen = $_SESSION['dosen'];
$id_konselor = $dosen['id_konselor'] ?? null;
$nama_dosen = $dosen['nama_lengkap'] ?? "Dosen Konselor"; // Variabel yang diperlukan sidebar

// Simpan perubahan data profil
if (isset($_POST['simpan'])) {
    if ($id_konselor === null) {
        echo "<script>alert('ID Konselor tidak ditemukan!'); window.location='profil_saya.php';</script>";
        exit;
    }

    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nidn = mysqli_real_escape_string($conn, $_POST['nidn']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $spesialisasi = mysqli_real_escape_string($conn, $_POST['spesialisasi']);
    
    $update = mysqli_query($conn, "
    UPDATE dosen_konselor 
    SET 
        nama_lengkap = '$nama_lengkap',
        nidn = '$nidn',
        email = '$email',
        spesialisasi = '$spesialisasi'
    WHERE id_konselor = $id_konselor
");


    if ($update) {
        // Update data di sesi setelah berhasil
        $dosen_updated_query = $conn->query("SELECT * FROM dosen_konselor WHERE id_konselor='$id_konselor'")->fetch_assoc();
        $_SESSION['dosen'] = $dosen_updated_query;
        
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil_saya.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui profil!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profil Dosen - Campus Care</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
/* SEMUA CSS TETAP DI SINI */
body {
  margin: 0;
  display: flex;
  font-family: 'Poppins', sans-serif;
  background: #f7f8fa;
  color: #333;
}


/* MAIN CONTENT */
.main-content {
  flex: 1;
  margin-left: 250px;
  padding: 30px;
}
.header {
  margin-bottom: 25px;
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

/* FORM STYLING */
.form-container {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  padding: 25px;
  max-width: 700px;
}
.form-group {
  margin-bottom: 15px;
}
label {
  display: block;
  font-weight: 500;
  margin-bottom: 5px;
}
input, textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}
textarea {
  resize: none;
  height: 100px;
}
button {
  background: #2563eb;
  color: white;
  padding: 12px;
  border: none;
  border-radius: 8px;
  width: 100%;
  font-weight: 600;
  cursor: pointer;
  font-size: 15px;
}
button:hover {
  background: #1e4ed8;
}
footer {
  text-align: center;
  color: #777;
  font-size: 14px;
  margin-top: 40px;
}
</style>
</head>
<body>

<?php
// INCLUDE SIDEBAR DI SINI
include '../../sidebar.php';
?>

<div class="main-content">
  <div class="header">
    <h2>Profil Dosen</h2>
    <p>Kelola informasi profil anda</p>
  </div>

  <div class="form-container">
    <form method="post">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($dosen['nama_lengkap'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>NIDN</label>
        <input type="text" name="nidn" value="<?= htmlspecialchars($dosen['nidn'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($dosen['email'] ?? '') ?>" required>
      </div>
      
      <div class="form-group">
        <label>Spesialisasi/Bidang Keahlian</label>
        <input type="text" name="spesialisasi" value="<?= htmlspecialchars($dosen['spesialisasi'] ?? '') ?>">
      </div>
      <button type="submit" name="simpan">Simpan Perubahan</button>
    </form>
  </div>

  <footer>
    &copy; <?= date('Y') ?> Campus Care | Panel Dosen
  </footer>
</div>
</body>
</html>