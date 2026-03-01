<?php
session_start();
include 'koneksi.php'; 

// Asumsi: Variabel koneksi adalah $koneksi. Cek keberadaan koneksi.
$db_koneksi = isset($koneksi) ? $koneksi : null;

if (!$db_koneksi) {
    // Jika koneksi.php gagal mendefinisikan $koneksi
    die("Error: Koneksi database tidak tersedia.");
}

// Cek apakah form disubmit (menggunakan nama tombol 'login')
if (isset($_POST['login'])) {
    
    // --- Langkah 1: Ambil dan sanitasi input ---
    $nim_email = mysqli_real_escape_string($db_koneksi, $_POST['nim_email']);
    $password_input = $_POST['password']; // Ubah nama variabel untuk mencegah konflik dengan $password di form
    
    // --- Langkah 2: Cari user ---
    $query = "SELECT * FROM mahasiswa WHERE nim = '$nim_email' OR email = '$nim_email' LIMIT 1";
    $result = mysqli_query($db_koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        // --- Langkah 3: Verifikasi Password (menggunakan $password_input) ---
        if (password_verify($password_input, $data['password'])) {
            
            // --- Langkah 4: Cek Status Akun (Konfirmasi Admin) ---
            if ($data['status'] === 'pending') {
                echo "<script>alert('Akun Anda belum dikonfirmasi oleh Admin. Mohon tunggu.'); window.location='login.php';</script>";
                exit;
            }
            
            // Jika statusnya 'active', lanjutkan proses login
            // Set session yang lebih detail
            $_SESSION['id_mahasiswa'] = $data['id_mahasiswa'];
            $_SESSION['nama'] = $data['nama_lengkap'];
            $_SESSION['nim'] = $data['nim'];
            $_SESSION['role'] = 'mahasiswa';
            
            // Arahkan ke dashboard
            header("Location: FE/page/dashboard.php");
            exit;
            
        } else {
            // Password salah
            echo "<script>alert('Password salah!'); window.location='login.php';</script>";
        }
    } else {
        // User (NIM/Email) tidak ditemukan
        echo "<script>alert('NIM / Email tidak ditemukan!'); window.location='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Care - Login</title>
    <link rel="stylesheet" href="FE/assets/css/global.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h2>🎓 Campus Care</h2>
                <p>Politeknik Negeri Indramayu</p>
            </div>

            <form action="" method="POST">
                <label for="nim_email">NIM / Email</label>
                <input type="text" id="nim_email" name="nim_email" placeholder="Masukkan NIM atau Email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan Password" required>

                <button type="submit" name="login" class="btn-login">Login</button>
            </form>

            <p class="register-text">Belum punya akun? <a href="register.php">Daftar Sekarang</a></p>
        </div>
    </div>
</body>
</html>