<?php
session_start();
include '../../koneksi.php';
include '../../sidebar.php'; // sesuaikan path jika beda folder

$nimSession = $_SESSION['nim'];

// Ambil data dari database
$query = "SELECT * FROM mahasiswa WHERE nim = '$nimSession' LIMIT 1";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

// Jika ada data, masukkan ke variabel
$nama   = $data['nama_lengkap'];
$nim    = $data['nim'];
$email  = $data['email'];
$prodi  = $data['prodi'];
$angkatan  = $data['angkatan'];

// ======== Update Profil ========= //
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama   = $_POST['nama'];
    $email  = $_POST['email'];
    $prodi  = $_POST['prodi'];
    $angkatan  = $_POST['angkatan'];
    

    // Query update data mahasiswa
    $update = "UPDATE mahasiswa 
               SET nama_lengkap='$nama', email='$email', prodi='$prodi', angkatan='$angkatan'
               WHERE nim='$nimSession'";

    if (mysqli_query($koneksi, $update)) {
        echo "<script>alert('✅ Profil berhasil diperbarui!'); window.location='profil.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal memperbarui profil!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya - Campus Care</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>

    /* Main Content */
    .main-content {
      margin-left: 270px;
      padding: 30px;
      width: calc(100% - 270px);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    h1 {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 5px;
    }

    p.subtitle {
      font-size: 14px;
      color: #666;
      margin-bottom: 20px;
    }

    /* Profile Card */
    .profile-card {
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .profile-avatar-large {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: #2563eb;
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 32px;
      font-weight: 600;
      margin: 0 auto 10px;
    }

    .profile-name {
      font-size: 18px;
      font-weight: 600;
      text-align: center;
    }

    .profile-nim {
      color: #666;
      font-size: 13px;
      text-align: center;
      margin-bottom: 20px;
    }

    form {
      max-width: 850px;
      width: 100%;
      margin: 0 auto;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 5px;
    }

    input, textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 14px;
    }

    .form-row {
      display: flex;
      gap: 15px;
    }

    .form-row .form-group {
      flex: 1;
    }

    textarea {
      resize: none;
    }

    .btn-submit {
      display: block;
      width: 100%;
      padding: 12px;
      background-color: #2563eb;
      color: white;
      font-size: 15px;
      font-weight: 500;
      border: none;
      border-radius: 8px;
      margin-top: 20px;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-submit:hover {
      background-color: #1d4ed8;
    }
  </style>
</head>

<body>

  <main class="main-content">
    <h1>Profil Saya</h1>
    <p class="subtitle">Kelola informasi profil Anda</p>

    <div class="profile-card">
      <div class="profile-avatar-large"> 
        <?= strtoupper(substr($nama, 0, 1)); ?></div>
      <p class="profile-nim"><?= $nim ?></p>

      <form action="" method="post">
        <div class="form-row">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= $nama ?>">
          </div>
          <div class="form-group">
            <label>NIM</label>
            <input type="text" name="nim" value="<?= $nim ?>" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= $email ?>">
          </div>
          <div class="form-group">
            <label>Program Studi</label>
            <input type="text" name="prodi" value="<?= $prodi ?>">
          </div>
        </div>

        <div class="form-group">
          <label>Angkatan</label>
          <input type="text" name="angkatan" value="<?= $angkatan ?>">
        </div>

        <button type="submit" class="btn-submit">Simpan Perubahan</button>
      </form>
    </div>
  </main>
  <script>
function confirmLogout() {
  if (confirm("Yakin ingin logout?")) {
    window.location.href = "../../logout.php"; // arahkan ke file logout
  }
}
</script>

</body>
</html>
