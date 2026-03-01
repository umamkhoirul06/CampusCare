<?php
include "../../koneksi.php";

if (!$koneksi) {
    die("Koneksi database gagal!");
}

// Ambil semua riwayat dari berbagai tabel
$query = "
    SELECT 
        m.nama_lengkap AS mahasiswa,
        'Konseling' AS jenis,
        p.topik_masalah AS detail,
        p.status,
        CONCAT(DATE_FORMAT(p.tanggal_konseling, '%d %M %Y'), ' - ', TIME_FORMAT(p.waktu_mulai, '%H:%i')) AS tanggal
    FROM permintaan_konseling p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa

    UNION ALL

    SELECT 
        m.nama_lengkap AS mahasiswa,
        'Fasilitas' AS jenis,
        f.nama_fasilitas AS detail,
        b.status,
        DATE_FORMAT(b.tanggal_mulai, '%d %M %Y %H:%i') AS tanggal
    FROM booking_fasilitas b
    JOIN mahasiswa m ON b.id_mahasiswa = m.id_mahasiswa
    JOIN fasilitas f ON b.id_fasilitas = f.id_fasilitas

    UNION ALL

    SELECT 
        m.nama_lengkap AS mahasiswa,
        'Event' AS jenis,
        e.judul AS detail,
        CASE 
            WHEN p.status_hadir = 1 THEN 'Hadir'
            ELSE 'Belum Hadir'
        END AS status,
        DATE_FORMAT(p.registered_at, '%d %M %Y %H:%i') AS tanggal
    FROM pendaftaran_event p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    JOIN event e ON p.id_event = e.id_event

    UNION ALL

    SELECT 
        m.nama_lengkap AS mahasiswa,
        'Pengaduan' AS jenis,
        l.judul AS detail,
        l.status,
        DATE_FORMAT(l.created_at, '%d %M %Y %H:%i') AS tanggal
    FROM laporan_pengaduan l
    LEFT JOIN mahasiswa m ON l.id_mahasiswa = m.id_mahasiswa

    ORDER BY tanggal DESC
";

$riwayat = mysqli_query($koneksi, $query);
?>

<h2 class="mb-4">Riwayat Layanan Mahasiswa</h2>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="5%">No</th>
            <th>Mahasiswa</th>
            <th>Jenis Layanan</th>
            <th>Detail</th>
            <th>Status</th>
            <th>Tanggal & Waktu</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1; 
        while ($r = mysqli_fetch_assoc($riwayat)): 
        ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($r['mahasiswa'] ?: '-'); ?></td>
            <td class="text-center"><?= htmlspecialchars($r['jenis']); ?></td>
            <td><?= htmlspecialchars($r['detail']); ?></td>
            <td class="text-center">
                <?php 
                $status = strtolower($r['status']);
                if (in_array($status, ['disetujui', 'hadir', 'selesai'])) {
                    echo '<span class="badge bg-success">'.ucfirst($r['status']).'</span>';
                } elseif (in_array($status, ['ditolak', 'batal'])) {
                    echo '<span class="badge bg-danger">'.ucfirst($r['status']).'</span>';
                } elseif (in_array($status, ['diproses', 'pending', 'belum hadir'])) {
                    echo '<span class="badge bg-warning text-dark">'.ucfirst($r['status']).'</span>';
                } else {
                    echo '<span class="badge bg-secondary">'.ucfirst($r['status']).'</span>';
                }
                ?>
            </td>
            <td class="text-center"><?= htmlspecialchars($r['tanggal']); ?></td>
        </tr>
        <?php endwhile; ?>

        <?php if (mysqli_num_rows($riwayat) == 0): ?>
        <tr>
            <td colspan="6" class="text-center">Belum ada riwayat layanan mahasiswa.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
