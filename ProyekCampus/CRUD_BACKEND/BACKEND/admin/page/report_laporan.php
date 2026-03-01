<?php
ob_start();

include_once(__DIR__ . '/../../../koneksi.php');

// ======================
// Ambil parameter
// ======================
$range  = $_GET['range'] ?? '';
$start  = $_GET['start'] ?? '';
$end    = $_GET['end'] ?? '';
$format = $_GET['format'] ?? 'csv';

// ======================
// Hitung range otomatis
// ======================
if (!empty($range)) {
    $endDate = new DateTime();
    $end = $endDate->format('Y-m-d');

    $startDate = new DateTime();
    $startDate->modify("-{$range} months");
    $start = $startDate->format('Y-m-d');
}

// Custom range validation
if (empty($range)) {
    if (empty($start) || empty($end)) {
        http_response_code(400);
        exit("Tanggal mulai & selesai wajib diisi");
    }
}

$start_date = $start;
$end_date   = $end;

// ======================
// Query Data
// ======================
$q = mysqli_query($koneksi, "
    SELECT l.*,
           m.nama_lengkap AS mahasiswa,
           k.nama_lengkap AS penanggung
    FROM laporan_pengaduan l
    LEFT JOIN mahasiswa m ON l.id_mahasiswa = m.id_mahasiswa
    LEFT JOIN dosen_konselor k ON l.ditanggapi_oleh = k.id_konselor
    WHERE DATE(l.created_at) BETWEEN '$start_date' AND '$end_date'
    ORDER BY l.created_at DESC
");

$data = [];
while ($d = mysqli_fetch_assoc($q)) $data[] = $d;

if (!$data) {
    http_response_code(204);
    exit;
}

// ======================
// Load vendor
// ======================
require dirname(__DIR__, 4) . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// =================================================
// ======================= CSV =====================
// =================================================
if ($format == "csv") {

    ob_end_clean();

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=laporan_pengaduan.csv");

    $out = fopen("php://output", "w");
    fputcsv($out, [
        "Judul", "Deskripsi", "Mahasiswa", "Anonim",
        "Status", "Penanggung Jawab", "Tanggal Dibuat"
    ]);

    foreach ($data as $d) {
        fputcsv($out, [
            $d['judul'],
            $d['deskripsi'],
            $d['mahasiswa'] ?: "-",
            $d['is_anonim'] ? "Ya" : "Tidak",
            $d['status'],
            $d['penanggung'] ?: "-",
            $d['created_at']
        ]);
    }

    fclose($out);
    exit;
}

// =================================================
// ====================== EXCEL ====================
// =================================================
if ($format == "excel") {

    ob_end_clean();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->fromArray([[
        "Judul", "Deskripsi", "Mahasiswa", "Anonim",
        "Status", "Penanggung Jawab", "Tanggal Dibuat"
    ]], NULL, 'A1');

    $row = 2;
    foreach ($data as $d) {
        $sheet->fromArray([[
            $d['judul'],
            $d['deskripsi'],
            $d['mahasiswa'] ?: "-",
            $d['is_anonim'] ? "Ya" : "Tidak",
            $d['status'],
            $d['penanggung'] ?: "-",
            $d['created_at']
        ]], NULL, "A{$row}");
        $row++;
    }

    $lastRow = $row - 1;

    foreach (range('A','G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $sheet->getStyle("A1:G1")->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => 'center'],
        'fill' => [
            'fillType' => 'solid',
            'startColor' => ['rgb' => 'E9ECEF']
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => 'thin']
        ]
    ]);

    $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => ['borderStyle' => 'thin']
        ]
    ]);

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=laporan_pengaduan.xlsx");

    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}

// =================================================
// ======================= WORD ====================
// =================================================
if ($format == "word") {

    ob_end_clean();

    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    $section->addText(
        "LAPORAN PENGADUAN MAHASISWA",
        ['bold' => true, 'size' => 16],
        ['alignment' => 'center']
    );
    $section->addText("Periode: $start_date s/d $end_date");
    $section->addTextBreak();

    $phpWord->addTableStyle('PengaduanTable', [
        'borderSize' => 6,
        'borderColor' => '999999',
        'cellMargin' => 80
    ], [
        'bgColor' => 'E9ECEF'
    ]);

    $table = $section->addTable('PengaduanTable');

    $headers = [
        "Judul", "Deskripsi", "Mahasiswa", "Anonim",
        "Status", "Penanggung Jawab", "Tanggal Dibuat"
    ];

    $table->addRow();
    foreach ($headers as $h) {
        $table->addCell(2500)->addText($h, ['bold' => true]);
    }

    foreach ($data as $d) {
        $table->addRow();
        $table->addCell()->addText($d['judul']);
        $table->addCell()->addText($d['deskripsi']);
        $table->addCell()->addText($d['mahasiswa'] ?: "-");
        $table->addCell()->addText($d['is_anonim'] ? "Ya" : "Tidak");
        $table->addCell()->addText($d['status']);
        $table->addCell()->addText($d['penanggung'] ?: "-");
        $table->addCell()->addText($d['created_at']);
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=laporan_pengaduan.docx");

    $writer = IOFactory::createWriter($phpWord, "Word2007");
    $writer->save("php://output");
    exit;
}
