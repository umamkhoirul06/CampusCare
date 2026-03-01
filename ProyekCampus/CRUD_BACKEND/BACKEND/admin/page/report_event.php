<?php
ob_start();

include_once(__DIR__ . '/../../../koneksi.php');

require dirname(__DIR__, 4) . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// ======================
// Ambil filter
// ======================
$range  = $_GET['range'] ?? '';
$start  = $_GET['start'] ?? '';
$end    = $_GET['end'] ?? '';
$event  = $_GET['event'] ?? '';
$format = $_GET['format'] ?? 'csv';

// ======================
// Hitung range otomatis
// ======================
if ($range == "1") {
    $start = date("Y-m-d", strtotime("-1 month"));
    $end   = date("Y-m-d");
}
if ($range == "3") {
    $start = date("Y-m-d", strtotime("-3 month"));
    $end   = date("Y-m-d");
}

// ======================
// Filter
// ======================
$where = "WHERE 1=1";
if ($start && $end) {
    $where .= " AND DATE(p.registered_at) BETWEEN '$start' AND '$end'";
}
if ($event !== "") {
    $where .= " AND p.id_event = " . intval($event);
}

// ======================
// Query
// ======================
$q = mysqli_query($koneksi,"
    SELECT p.*, m.nama_lengkap AS mahasiswa, e.judul AS event
    FROM pendaftaran_event p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    JOIN event e ON p.id_event = e.id_event
    $where
    ORDER BY p.registered_at DESC
");

$data = [];
while ($d = mysqli_fetch_assoc($q)) $data[] = $d;

if (!$data) {
    http_response_code(204);
    exit;
}

// =================================================
// ======================= CSV =====================
// =================================================
if ($format == "csv") {

    ob_end_clean();

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=laporan_event.csv");

    $out = fopen("php://output", "w");
    fputcsv($out, ["Mahasiswa","Event","Tanggal Daftar","Status Hadir"]);

    foreach ($data as $d) {
        fputcsv($out, [
            $d['mahasiswa'],
            $d['event'],
            $d['registered_at'],
            $d['status_hadir'] ? "Hadir" : "Belum Hadir"
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

    // Header
    $sheet->fromArray([[
        "Mahasiswa","Event","Tanggal Daftar","Status Hadir"
    ]], NULL, 'A1');

    $row = 2;
    foreach ($data as $d) {
        $sheet->fromArray([[
            $d['mahasiswa'],
            $d['event'],
            $d['registered_at'],
            $d['status_hadir'] ? "Hadir" : "Belum Hadir"
        ]], NULL, "A{$row}");
        $row++;
    }

    $lastRow = $row - 1;

    // Auto width
    foreach (range('A','D') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Style header
    $sheet->getStyle('A1:D1')->applyFromArray([
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

    // Border all
    $sheet->getStyle("A1:D{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ]
        ]
    ]);

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=laporan_event.xlsx");

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
        "LAPORAN PENDAFTARAN EVENT",
        ['bold' => true, 'size' => 16],
        ['alignment' => 'center']
    );
    $section->addText("Periode: $start s/d $end");
    $section->addTextBreak();

    // Table style
    $phpWord->addTableStyle('EventTable', [
        'borderSize' => 6,
        'borderColor' => '999999',
        'cellMargin' => 80
    ], [
        'bgColor' => 'E9ECEF'
    ]);

    $table = $section->addTable('EventTable');

    // Header
    $headers = ["Mahasiswa","Event","Tanggal Daftar","Status Hadir"];
    $table->addRow();
    foreach ($headers as $h) {
        $table->addCell(3000)->addText($h, ['bold' => true]);
    }

    // Data
    foreach ($data as $d) {
        $table->addRow();
        $table->addCell()->addText($d['mahasiswa']);
        $table->addCell()->addText($d['event']);
        $table->addCell()->addText($d['registered_at']);
        $table->addCell()->addText($d['status_hadir'] ? "Hadir" : "Belum Hadir");
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=laporan_event.docx");

    $writer = IOFactory::createWriter($phpWord, "Word2007");
    $writer->save("php://output");
    exit;
}
