<?php
include __DIR__ . '/../../../koneksi.php';

// ========================
// AMBIL PARAMETER
// ========================
$range    = $_GET['range'] ?? '';
$start    = $_GET['start'] ?? '';
$end      = $_GET['end'] ?? '';
$konselor = $_GET['konselor'] ?? '';
$format   = $_GET['format'] ?? 'csv';

// ========================
// HITUNG RENTANG WAKTU
// ========================
if (!empty($range)) {
    $end   = date("Y-m-d");
    $start = date("Y-m-d", strtotime("-$range months"));
} else {
    if (empty($start) || empty($end)) {
        die("Tanggal mulai dan selesai wajib diisi.");
    }
}

// ========================
// BUILD WHERE
// ========================
$where = "WHERE DATE(p.tanggal_konseling) BETWEEN '$start' AND '$end'";
if (!empty($konselor)) {
    $where .= " AND p.id_konselor = " . intval($konselor);
}

// ========================
// QUERY DATA
// ========================
$sql = "
    SELECT p.*, 
           m.nama_lengkap AS mahasiswa,
           m.nim,
           k.nama_lengkap AS konselor
    FROM permintaan_konseling p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    JOIN dosen_konselor k ON p.id_konselor = k.id_konselor
    $where
    ORDER BY p.tanggal_konseling DESC
";

$q = mysqli_query($koneksi, $sql);
$data = [];
while ($d = mysqli_fetch_assoc($q)) {
    $data[] = $d;
}

if (!$data) {
    die("Tidak ada data ditemukan.");
}

// ========================
// AUTOLOAD COMPOSER
// ========================
require dirname(__DIR__, 4) . "/../vendor/autoload.php";

// =================================================
// ======================== CSV ====================
// =================================================
if ($format == 'csv') {

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=laporan_konseling.csv");

    $out = fopen("php://output", "w");

    fputcsv($out, [
        "Mahasiswa", "NIM", "Konselor",
        "Topik", "Tanggal", "Waktu", "Status"
    ]);

    foreach ($data as $d) {
        fputcsv($out, [
            $d['mahasiswa'],
            $d['nim'],
            $d['konselor'],
            $d['topik_masalah'],
            $d['tanggal_konseling'],
            $d['waktu_mulai'],
            $d['status']
        ]);
    }

    fclose($out);
    exit;
}

// =================================================
// ======================= EXCEL ===================
// =================================================
if ($format == 'excel') {

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // HEADER
    $sheet->fromArray([[
        "Mahasiswa", "NIM", "Konselor",
        "Topik", "Tanggal", "Waktu", "Status"
    ]], NULL, 'A1');

    // DATA
    $row = 2;
    foreach ($data as $d) {
        $sheet->fromArray([[
            $d['mahasiswa'],
            $d['nim'],
            $d['konselor'],
            $d['topik_masalah'],
            $d['tanggal_konseling'],
            $d['waktu_mulai'],
            $d['status']
        ]], NULL, "A{$row}");
        $row++;
    }

    $lastRow = $row - 1;

    // AUTO WIDTH
    foreach (range('A', 'G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // STYLE HEADER
    $sheet->getStyle('A1:G1')->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E9ECEF']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ]
        ]
    ]);

    // BORDER ALL
    $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ]
        ]
    ]);

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=laporan_konseling.xlsx");

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}

// =================================================
// ======================== WORD ===================
// =================================================
if ($format == 'word') {

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    $section->addText(
        "LAPORAN PERMINTAAN KONSELING",
        ['bold' => true, 'size' => 16],
        ['alignment' => 'center']
    );

    $section->addText("Periode: $start s/d $end");
    $section->addTextBreak();

    // TABLE STYLE
    $phpWord->addTableStyle('TabelRapi', [
        'borderSize' => 6,
        'borderColor' => '999999',
        'cellMargin' => 80
    ], [
        'bgColor' => 'E9ECEF'
    ]);

    $table = $section->addTable('TabelRapi');

    $headers = ["Mahasiswa", "NIM", "Konselor", "Topik", "Tanggal", "Waktu", "Status"];

    $table->addRow();
    foreach ($headers as $h) {
        $table->addCell(2000)->addText($h, ['bold' => true]);
    }

    foreach ($data as $d) {
        $table->addRow();
        $table->addCell()->addText($d['mahasiswa']);
        $table->addCell()->addText($d['nim']);
        $table->addCell()->addText($d['konselor']);
        $table->addCell()->addText($d['topik_masalah']);
        $table->addCell()->addText($d['tanggal_konseling']);
        $table->addCell()->addText($d['waktu_mulai']);
        $table->addCell()->addText($d['status']);
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=laporan_konseling.docx");

    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save("php://output");
    exit;
}
