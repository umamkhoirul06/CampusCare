<?php
function connectDB() {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "pw";
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    return $conn;
}

function getAllArtikel() {
    $conn = connectDB();
    $sql = "SELECT a.*, k.nama_kategori FROM artikel a 
        LEFT JOIN kategori k ON a.kode_kategori = k.kode_kategori 
        ORDER BY a.tgl_artikel DESC";

    $result = $conn->query($sql);

    $artikel = [];

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $artikel[] = $row;
        }
    } elseif (!$result) {
        echo "Query error: " . $conn->error;
    }

    $conn->close();
    return $artikel;
}

function getArtikelById($id) {
    $conn = connectDB();
    $sql = "SELECT a.*, k.nama_kategori FROM artikel a 
            LEFT JOIN kategori k ON a.kode_kategori = k.kode_kategori 
            WHERE a.id_artikel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $artikel = null;
    if ($result->num_rows > 0) {
        $artikel = $result->fetch_assoc();
    }
    
    $stmt->close();
    $conn->close();
    return $artikel;
}

function getAllKategori() {
    $conn = connectDB();
    $sql = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
    $result = $conn->query($sql);
    
    $kategori = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $kategori[] = $row;
        }
    }
    
    $conn->close();
    return $kategori;
}

function truncateText($text, $limit = 150) {
    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . '...';
    }
    return $text;
}

function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

function simpanKontak($nama, $email, $alamat, $pesan) {
    $conn = connectDB();
    $sql = "INSERT INTO kontak (nama_lengkap, email, alamat, pesan, tanggal) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nama, $email, $alamat, $pesan);
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}
?>