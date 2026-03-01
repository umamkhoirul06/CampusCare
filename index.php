<!DOCTYPE html>
<style>
    * {
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    margin: 0;
    height: 100vh;
    background: #1e5bd7; /* biru sama seperti login */
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    width: 100%;
    max-width: 420px;
    padding: 20px;
}

.card {
    background: #fff;
    padding: 35px 30px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.logo {
    font-size: 22px;
    font-weight: bold;
    color: #1e5bd7;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.subtitle {
    font-size: 13px;
    color: #777;
    margin-bottom: 25px;
}

.title {
    margin-bottom: 20px;
    color: #333;
}

.role-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn {
    text-decoration: none;
    padding: 12px;
    border-radius: 10px;
    font-weight: 600;
    color: #fff;
    transition: 0.2s ease;
}

/* Semua role tetap biru biar konsisten */
.role {
    background: #1e5bd7;
}

.role:hover {
    background: #1746a8;
}
</style>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Campus Care - Pilih Role</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container">
    <div class="card">
        <div class="logo">
            🎓 <span>Campus Care</span>
        </div>
        <p class="subtitle">Politeknik Negeri Indramayu</p>

        <h3 class="title">Masuk sebagai</h3>

        <div class="role-buttons">
            <a href="ProyekCampus/CRUD_BACKEND/BACKEND/admin/login.php" class="btn role admin">Admin</a>
            <a href="ProyekCampus/CRUD_BACKEND/frontend/dosen/login.php" class="btn role dosen">Dosen</a>
            <a href="ProyekCampus/CRUD_BACKEND/frontend/Proyek_Campuscare/login.php" class="btn role mahasiswa">Mahasiswa</a>
        </div>
    </div>
</div>

</body>
</html>
