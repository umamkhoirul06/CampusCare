<?php
include_once(__DIR__ . '/../../../koneksi.php');

// =======================
// AMBIL PARAMETER
// =======================
$range     = $_GET['range'] ?? '';
$start     = $_GET['start'] ?? '';
$end       = $_GET['end'] ?? '';
$fasilitas = $_GET['fasilitas'] ?? '';
$format    = $_GET['format'] ?? 'csv';

// =======================
// HITUNG RANGE BULAN
// =======================
if (!empty($range)) {
    $endDate = new DateTime();
    $end = $endDate->format('Y-m-d');

    $startDate = new DateTime();
    $startDate->modify("-{$range} months");
    $start = $startDate->format('Y-m-d');
} else {
    if (empty($start) || empty($end)) {
        die("Tanggal mulai & selesai wajib diisi!");
    }
}

$start_date = $start;
$end_date   = $end;

// =======================
// QUERY DATA
// =======================
$query = "
    SELECT b.*, 
           m.nama_lengkap AS nama_mahasiswa,
           f.nama_fasilitas,
           k.nama_lengkap AS admin
    FROM booking_fasilitas b
    JOIN mahasiswa m ON b.id_mahasiswa = m.id_mahasiswa
    JOIN fasilitas f ON b.id_fasilitas = f.id_fasilitas
    LEFT JOIN dosen_konselor k ON b.admin_verifikasi = k.id_konselor
    WHERE DATE(b.tanggal_mulai) BETWEEN '$start_date' AND '$end_date'
    " . (!empty($fasilitas) ? " AND b.id_fasilitas = " . (int)$fasilitas : "") . "
    ORDER BY b.tanggal_mulai DESC
";

$data = mysqli_query($koneksi, $query);
$rows = [];
while ($d = mysqli_fetch_assoc($data)) {
    $rows[] = $d;
}

if (!$rows) {
    die("Tidak ada data ditemukan.");
}

// =======================
// AUTOLOAD VENDOR
// =======================
require dirname(__DIR__, 4) . "/../vendor/autoload.php";

// ==================================================
// ======================= CSV =======================
// ==================================================
if ($format == 'csv') {

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=laporan_booking.csv");

    $out = fopen("php://output", "w");

    fputcsv($out, [
        "Mahasiswa", "Fasilitas", "Mulai", "Selesai",
        "Keperluan", "Status", "Admin", "Tanggal Input"
    ]);

    foreach ($rows as $r) {
        fputcsv($out, [
            $r['nama_mahasiswa'],
            $r['nama_fasilitas'],
            $r['tanggal_mulai'],
            $r['tanggal_selesai'],
            $r['keperluan'],
            $r['status'],
            $r['admin'],
            $r['created_at']
        ]);
    }

    fclose($out);
    exit;
}

// ==================================================
// ====================== EXCEL =====================
// ==================================================
if ($format == 'excel') {

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // HEADER
    $sheet->fromArray([[
        "Mahasiswa", "Fasilitas", "Mulai", "Selesai",
        "Keperluan", "Status", "Admin", "Tanggal Input"
    ]], NULL, 'A1');

    // DATA
    $rowNum = 2;
    foreach ($rows as $r) {
        $sheet->fromArray([[
            $r['nama_mahasiswa'],
            $r['nama_fasilitas'],
            $r['tanggal_mulai'],
            $r['tanggal_selesai'],
            $r['keperluan'],
            $r['status'],
            $r['admin'],
            $r['created_at']
        ]], NULL, "A{$rowNum}");
        $rowNum++;
    }

    $lastRow = $rowNum - 1;

    // AUTO WIDTH
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // STYLE HEADER
    $sheet->getStyle('A1:H1')->applyFromArray([
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
    $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ]
        ]
    ]);

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=laporan_booking.xlsx");

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}

// ==================================================
// ======================= WORD =====================
// ==================================================
if ($format == 'word') {

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    $section->addText("LAPORAN BOOKING FASILITAS", [
        'bold' => true, 'size' => 16
    ], ['alignment' => 'center']);

    $section->addText("Periode: $start_date s/d $end_date");
    $section->addTextBreak();

    $phpWord->addTableStyle('TabelRapi', [
        'borderSize' => 6,
        'borderColor' => '999999',
        'cellMargin' => 80
    ], [
        'bgColor' => 'E9ECEF'
    ]);

    $table = $section->addTable('TabelRapi');

    $headers = ["Mahasiswa", "Fasilitas", "Mulai", "Selesai",
                "Keperluan", "Status", "Admin", "Input"];

    $table->addRow();
    foreach ($headers as $h) {
        $table->addCell(2000)->addText($h, ['bold' => true]);
    }

    foreach ($rows as $r) {
        $table->addRow();
        $table->addCell()->addText($r['nama_mahasiswa']);
        $table->addCell()->addText($r['nama_fasilitas']);
        $table->addCell()->addText($r['tanggal_mulai']);
        $table->addCell()->addText($r['tanggal_selesai']);
        $table->addCell()->addText($r['keperluan']);
        $table->addCell()->addText($r['status']);
        $table->addCell()->addText($r['admin']);
        $table->addCell()->addText($r['created_at']);
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=laporan_booking.docx");

    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save("php://output");
    exit;
}
