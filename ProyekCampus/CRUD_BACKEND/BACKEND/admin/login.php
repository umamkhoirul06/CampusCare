<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Jakarta');

include "../../koneksi.php";
include "../assets/tgl_indo.php";

if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['username'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, md5($_POST['password']));

    $cekQuery = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($cekQuery) > 0) {
        $data = mysqli_fetch_assoc($cekQuery);
        $_SESSION['username'] = $data['username'];
        $_SESSION['password'] = $data['password'];
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Username atau password salah');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Campus Care - Login</title>
    <link href="../assets/bootstrap-5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #1557d6;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 35px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .login-card h3 {
            font-weight: 700;
            color: #1557d6;
            margin-bottom: 5px;
        }

        .login-card p {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px 14px;
        }

        .btn-login {
            background-color: #1557d6;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: #0e45b4;
        }

        .register-text {
            margin-top: 15px;
            font-size: 14px;
        }

        .register-text a {
            color: #1557d6;
            font-weight: 600;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .logo {
            width: 50px;
            height: auto;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h3>🎓 Campus Care</h3>
        <p>Politeknik Negeri Indramayu</p>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label class="form-label">Username/Email</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan Username atau Email" required autofocus>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
            </div>
            <button type="submit" class="btn btn-login w-100 py-2 mt-2">Login</button>
        </form>

    </div>

    <script src="../assets/bootstrap-5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
