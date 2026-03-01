<?php
// TAMPILKAN ERROR DULU
ini_set('display_errors', 1);
error_reporting(E_ALL);

// HAPUS SEMUA HEADER YANG MUNGKIN ADA
header_remove();

// INCLUDE PALING MINIMAL
require __DIR__ . '/../../../koneksi.php';
$autoload = dirname(__DIR__, 4) . "/../vendor/autoload.php";
if (!file_exists($autoload)) {
    die("Autoload tidak ditemukan! Lokasi dicari: <b>$autoload</b>");
}
require $autoload;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// QUERY
$q = mysqli_query($koneksi, "
    SELECT p.*, m.nama_lengkap AS mahasiswa, e.judul AS event
    FROM pendaftaran_event p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    JOIN event e ON p.id_event = e.id_event
    ORDER BY p.registered_at DESC
");

$data = [];
while ($d = mysqli_fetch_assoc($q)) {
    $data[] = $d;
}

// BUAT WORD
$word = new PhpWord();
$section = $word->addSection();
$section->addText("Laporan Pendaftaran Event", ['bold' => true]);
$section->addTextBreak();

$table = $section->addTable();
$table->addRow();
foreach (["Mahasiswa","Event","Tanggal Daftar","Status Hadir"] as $h) {
    $table->addCell()->addText($h);
}

foreach ($data as $d) {
    $table->addRow();
    $table->addCell()->addText($d['mahasiswa']);
    $table->addCell()->addText($d['event']);
    $table->addCell()->addText($d['registered_at']);
    $table->addCell()->addText($d['status_hadir'] ? 'Hadir' : 'Belum Hadir');
}

// HEADER TERAKHIR
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=laporan_event.docx");
header("Cache-Control: max-age=0");

$writer = IOFactory::createWriter($word, 'Word2007');
$writer->save(__DIR__ . '/TES.docx');

echo "OK";
exit;
