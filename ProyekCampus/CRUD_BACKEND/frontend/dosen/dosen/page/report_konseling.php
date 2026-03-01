<?php
ob_start();
session_start();

include '../../koneksi.php';

// ======================
// CEK LOGIN DOSEN
// ======================
if (!isset($_SESSION['dosen']['id_konselor'])) {
    header("Location: ../../login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$id_konselor = (int)$_SESSION['dosen']['id_konselor'];
if ($id_konselor <= 0) {
    http_response_code(403);
    exit("Session dosen tidak valid");
}

// ======================
// PARAMETER
// ======================
$format = strtolower($_GET['format'] ?? 'csv'); // csv | excel | word
$range  = $_GET['range'] ?? '';
$start  = $_GET['start'] ?? '';
$end    = $_GET['end'] ?? '';

// ======================
// RANGE OTOMATIS
// ======================
if ($range !== '') {
    $end   = date('Y-m-d');
    $start = date('Y-m-d', strtotime("-{$range} months"));
}

// custom mode
if ($range === '') {
    if (empty($start) || empty($end)) {
        http_response_code(400);
        exit("Tanggal mulai & selesai wajib diisi");
    }
}

$start_date = date('Y-m-d', strtotime($start));
$end_date   = date('Y-m-d', strtotime($end));

// ======================
// QUERY DATA
// ======================
$sql = "
    SELECT rk.tanggal, rk.waktu,
           COALESCE(m.nama_lengkap, rk.nama_mahasiswa) AS nama_mahasiswa,
           COALESCE(m.nim, rk.nim) AS nim,
           rk.topik, rk.status, rk.catatan, rk.created_at
    FROM riwayat_konseling rk
    LEFT JOIN mahasiswa m ON m.id_mahasiswa = rk.id_mahasiswa
    WHERE rk.id_konselor = ?
      AND rk.tanggal BETWEEN ? AND ?
    ORDER BY rk.tanggal ASC, rk.waktu ASC
";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "iss", $id_konselor, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($r = mysqli_fetch_assoc($result)) {
    $data[] = $r;
}
mysqli_stmt_close($stmt);

if (!$data) {
    http_response_code(204);
    exit;
}

$filename = "laporan_konseling_{$start_date}_sd_{$end_date}";

// =================================================
// ====================== CSV ======================
// =================================================
if ($format === 'csv') {

    ob_end_clean();

    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename={$filename}.csv");

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8

    fputcsv($out, [
        'Tanggal','Waktu','Nama Mahasiswa','NIM',
        'Topik','Status','Catatan','Created At'
    ]);

    foreach ($data as $d) {
        fputcsv($out, [
            $d['tanggal'],
            $d['waktu'],
            $d['nama_mahasiswa'],
            $d['nim'],
            $d['topik'],
            $d['status'],
            $d['catatan'],
            $d['created_at']
        ]);
    }

    fclose($out);
    exit;
}

// ======================
// HELPER HTML TABLE
// ======================
function build_html($data, $start, $end) {
    $html = "<html><head><meta charset='utf-8'>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;font-size:12px;}
        table{border-collapse:collapse;width:100%;}
        th,td{border:1px solid #999;padding:6px;}
        th{background:#f2f2f2;}
    </style>
    </head><body>";

    $html .= "<h3>Laporan Riwayat Konseling</h3>";
    $html .= "<p>Periode: <strong>{$start}</strong> s/d <strong>{$end}</strong></p>";

    $html .= "<table>
        <tr>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Nama Mahasiswa</th>
            <th>NIM</th>
            <th>Topik</th>
            <th>Status</th>
            <th>Catatan</th>
            <th>Created At</th>
        </tr>";

    foreach ($data as $d) {
        $html .= "<tr>
            <td>{$d['tanggal']}</td>
            <td>{$d['waktu']}</td>
            <td>".htmlspecialchars($d['nama_mahasiswa'])."</td>
            <td>".htmlspecialchars($d['nim'])."</td>
            <td>".htmlspecialchars($d['topik'])."</td>
            <td>".htmlspecialchars($d['status'])."</td>
            <td>".nl2br(htmlspecialchars($d['catatan']))."</td>
            <td>{$d['created_at']}</td>
        </tr>";
    }

    $html .= "</table></body></html>";
    return $html;
}

// =================================================
// ====================== EXCEL ====================
// =================================================
if ($format === 'excel') {

    ob_end_clean();

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename={$filename}.xls");

    echo "\xEF\xBB\xBF";
    echo build_html($data, $start_date, $end_date);
    exit;
}

// =================================================
// ======================= WORD ====================
// =================================================
if ($format === 'word') {

    ob_end_clean();

    header("Content-Type: application/msword; charset=utf-8");
    header("Content-Disposition: attachment; filename={$filename}.doc");

    echo "\xEF\xBB\xBF";
    echo build_html($data, $start_date, $end_date);
    exit;
}

http_response_code(400);
exit("Format tidak dikenali");
