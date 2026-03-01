<?php
session_start();
include 'koneksi.php'; 

if (isset($_POST['login'])) {

    $email_nidn = mysqli_real_escape_string($conn, $_POST['email_nidn']);
    $password   = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM dosen_konselor WHERE email = '$email_nidn' OR nidn = '$email_nidn' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        if (password_verify($password, $data['password']) || $password == $data['password']) {
            $_SESSION['role'] = 'dosen';
            $_SESSION['login'] = true;
            $_SESSION['dosen'] = $data;

            header("Location: dosen/page/dashboard_dosen.php");
            exit;
        } else {
            echo "<script>alert('Password salah!');</script>";
        }
    } else {
        echo "<script>alert('Email / NIDN tidak ditemukan!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Care - Login Dosen</title>
    <link rel="stylesheet" href="dosen/assets/css/global.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h2>🎓 Campus Care</h2>
                <p>Politeknik Negeri Indramayu</p>
            </div>

            <form action="" method="POST">
                <label for="email_nidn">Email / NIDN</label>
                <input type="text" id="email_nidn" name="email_nidn" placeholder="Masukkan Email atau NIDN" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan Password" required>

                <button type="submit" name="login" class="btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
