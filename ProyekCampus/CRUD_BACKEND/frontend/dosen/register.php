<?php
session_start();
include 'koneksi.php'; // 🔗 koneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // 🔐 hash password
    $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);

    // 🔎 cek apakah NIM atau Email sudah terdaftar
    $cek = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nim='$nim' OR email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('NIM atau Email sudah terdaftar!'); window.location='register.php';</script>";
        exit();
    }

    $query = "INSERT INTO mahasiswa (nama_lengkap, nim, email, password, prodi)
              VALUES ('$nama', '$nim', '$email', '$password', '$prodi')";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Care - Register</title>
    <link rel="stylesheet" href="FE/assets/css/global.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h2>🎓 Campus Care</h2>
                <p>Politeknik Negeri Indramayu</p>
            </div>

            <!-- Form Register -->
            <form action="" method="POST">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan Nama Lengkap" required>

                <label for="nim">NIM</label>
                <input type="text" id="nim" name="nim" placeholder="Masukkan NIM" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Masukkan Email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" minlength="6" required>

                <label for="prodi">Program Studi</label>
                <select id="prodi" name="prodi" required>
                    <option value="">Pilih Program Studi</option>
                    <option value="Teknik Informatika">Teknik Informatika</option>
                    <option value="Teknik Mesin">Teknik Mesin</option>
                    <option value="Teknik Pendingin dan Tata Udara">Teknik Pendingin dan Tata Udara</option>
                    <option value="Terapan Rekayasa Perangkat Lunak">Terapan Rekayasa Perangkat Lunak</option>
                    <option value="Terapan Sistem Informasi Kota Cerdas">Terapan Sistem Informasi Kota Cerdas</option>
                    <option value="Teknologi Rekayasa Komputer">Teknologi Rekayasa Komputer</option>
                    <option value="Teknologi Laboratorium Medis">Teknologi Laboratorium Medis</option>
                    <option value="Teknologi Rekayasa Elektro-Medis">Teknologi Rekayasa Elektro-Medis</option>
                    <option value="Perancangan Manufaktur">Perancangan Manufaktur</option>
                    <option value="Teknologi Rekayasa Instrumen & Kontrol">Teknologi Rekayasa Instrumen & Kontrol</option>
                    <option value="Keperawatan">Keperawatan</option>
                </select>

                <button type="submit" class="btn-login">Daftar</button>
            </form>

            <p class="register-text">
                Sudah punya akun? <a href="login.php">Login Sekarang</a>
            </p>
        </div>
    </div>
</body>
</html>
