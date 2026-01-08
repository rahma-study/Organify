<?php
session_start();
date_default_timezone_set('Asia/Makassar');
include 'config.php';

// CEK USER LOGIN
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$where = "id_user='$id_user'";

// FILTER KATEGORI (lebih aman)
if (isset($_GET['kategori']) && $_GET['kategori'] != '') {
    $kategori = mysqli_real_escape_string($conn, $_GET['kategori']);
    $where .= " AND kategori='$kategori'";
}

// ===================
// 1. RINGKASAN TOTAL (hanya diverifikasi, semua waktu)
// ===================
$q_summary = mysqli_query($conn, "
    SELECT 
        IFNULL(SUM(berat), 0) AS total_berat,
        COUNT(*) AS total_setor
    FROM setoran
    WHERE id_user = '$id_user' 
      AND status='diverifikasi'
");
$summary = mysqli_fetch_assoc($q_summary);
$total_berat = $summary['total_berat'];
$total_setor  = $summary['total_setor'];

// ===================
// 2. PENCAPAIAN BULAN INI (hanya diverifikasi, bulan berjalan)
// ===================
$q_bulan_ini = mysqli_query($conn, "
    SELECT 
        IFNULL(SUM(berat),0) AS total_berat_bulan,
        COUNT(*) AS total_setor_bulan
    FROM setoran
    WHERE id_user='$id_user'
      AND status='diverifikasi'
      AND MONTH(tanggal) = MONTH(CURDATE())
      AND YEAR(tanggal) = YEAR(CURDATE())
");
$bulan_ini = mysqli_fetch_assoc($q_bulan_ini);
$total_berat_bulan = $bulan_ini['total_berat_bulan'];
$total_setor_bulan = $bulan_ini['total_setor_bulan'];

// ===================
// 3. QUERY LIST RIWAYAT (tampilkan semua status)
// ===================
$q = mysqli_query($conn, "SELECT * FROM setoran WHERE $where ORDER BY id_setoran DESC");

// ===================
// 4. Fungsi format waktu
// ===================
function timeAgo($time){
    $timestamp = strtotime($time);
    $diff = time() - $timestamp;

    if ($diff < 60) return "Baru saja";
    if ($diff < 3600) return floor($diff/60)." menit yang lalu";
    if ($diff < 86400) return floor($diff/3600)." jam yang lalu";
    if ($diff < 604800) return floor($diff/86400)." hari yang lalu";
    return floor($diff/604800)." minggu yang lalu";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Riwayat Aktivitas</title>
<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300;400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="assets/css/riwayat_setoran.css">
<style>
/* Status warna */
.status-menunggu { color: #FFA500; font-weight:bold; }
.status-diverifikasi { color: #4CAF50; font-weight:bold; }
.status-ditolak { color: #F44336; font-weight:bold; }
</style>
</head>
<body>

<div class="header">
    <button class="back-btn" onclick="window.location.href='landing_page.php'">
        <img src="assets/img/back button.png">
    </button>

    <h1>Riwayat Aktivitas</h1>
    <p>Semua aktivitas setor sampah organik Anda</p>
</div>

<!-- SUMMARY -->
<div class="summary-wrapper">
    <div class="summary-title">Ringkasan Total</div>
    <div class="summary">
        <div class="card">
            <div class="num-total-poin"><?= $total_setor ?></div>
            <div class="label">Total Setoran</div>
        </div>
        <div class="card">
            <div class="num-total-berat"><?= $total_berat ?> Kg</div>
            <div class="label">Total Berat</div>
        </div>
    </div>
</div>

<!-- FILTER -->
<div class="filter-box">
    <div class="filter-title">Filter Jenis Sampah</div>
    <div class="filter-options">
        <a href="riwayat_setoran.php" class="filter-item <?= empty($_GET['kategori']) ? 'active' : '' ?>">Semua</a>
        <a href="riwayat_setoran.php?kategori=Sampah Sisa Makanan" class="filter-item <?= ($_GET['kategori'] ?? '') == 'Sampah Sisa Makanan' ? 'active' : '' ?>">Sisa Makanan</a>
        <a href="riwayat_setoran.php?kategori=Sampah Sisa Buah dan Sayur" class="filter-item <?= ($_GET['kategori'] ?? '') == 'Sampah Sisa Buah dan Sayur' ? 'active' : '' ?>">Buah & Sayur</a>
        <a href="riwayat_setoran.php?kategori=Sampah Hewani" class="filter-item <?= ($_GET['kategori'] ?? '') == 'Sampah Hewani' ? 'active' : '' ?>">Hewani</a>
        <a href="riwayat_setoran.php?kategori=Sampah Pertanian" class="filter-item <?= ($_GET['kategori'] ?? '') == 'Sampah Pertanian' ? 'active' : '' ?>">Pertanian</a>
        <a href="riwayat_setoran.php?kategori=Sampah Kotoran Hewan" class="filter-item <?= ($_GET['kategori'] ?? '') == 'Sampah Kotoran Hewan' ? 'active' : '' ?>">Kotoran Hewan</a>
        <a href="riwayat_setoran.php?kategori=Sampah Daun dan Ranting Kering" class="filter-item <?= ($_GET['kategori'] ?? '') == 'Sampah Daun dan Ranting Kering' ? 'active' : '' ?>">Daun & Ranting</a>
    </div>
</div>

<!-- LIST RIWAYAT -->
<div class="activities-box">
    <h3>Semua Aktivitas</h3>

    <?php if (mysqli_num_rows($q) == 0): ?>
        <p style="text-align:center; opacity:0.7; margin:20px 0;">Belum ada riwayat setoran.</p>
    <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($q)): ?>
            <div class="activity-item">
                <div class="activity-left">
                    <div class="activity-icon">
                        <img src="assets/img/daur_ulang.png" alt="">
                    </div>
                    <div>
                        <div class="activity-title">Setor Sampah <?= htmlspecialchars($row['kategori']) ?></div>
                        <div class="activity-time"><?= date("d M Y - H:i", strtotime($row['tanggal'])) ?></div>
                    </div>
                </div>

                <div class="activity-point">
                    +<?= rtrim(rtrim(number_format((float)$row['berat'], 2, '.', ''), '0'), '.') ?> Kg
                    <br>
                    <span class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<!-- =================== CARD PENCAPAIAN =================== -->
<div class="pencapaian-card">
    <div class="bottom-title">Pencapaian Bulan Ini</div>
    <div class="bottom-subtext">Ayo terus setor sampah untuk kontribusi hijau!</div>
    <div class="bottom-content">
        <div class="bottom-card">
            <div class="num-total-berat"><?= $total_berat_bulan ?> Kg</div>
            <div class="label-pencapaian">Total Berat</div>
        </div>
        <div class="bottom-content-arrow">
            <img src="assets/img/panah.png" alt="">
        </div>
        <div class="bottom-card">
            <div class="num-total-poin"><?= $total_setor_bulan ?></div>
            <div class="label-pencapaian">Setoran Diverifikasi</div>
        </div>
    </div>
</div>


</body>
</html>
