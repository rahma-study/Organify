<?php
include 'check_admin.php';
include '../config.php';

if (basename($_SERVER['PHP_SELF']) != "profile_admin.php") {
    $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
}

// ===========================
// AMBIL DATA SETORAN MENUNGGU
// ===========================
$query = mysqli_query($conn, "
    SELECT s.*, u.nama, b.nama_bank
    FROM setoran s
    JOIN user u ON s.id_user = u.id_user
    LEFT JOIN banksampah b ON s.id_bank = b.id_bank
    WHERE s.status = 'menunggu'
");

// ===========================
// DATA ADMIN
// ===========================
$id_admin = $_SESSION['admin_id'];

mysqli_query($conn, "UPDATE admin SET last_active = NOW() WHERE id_admin = '$id_admin'");
$q_admin = mysqli_query($conn, "SELECT * FROM admin WHERE id_admin = '$id_admin'");
$admin = mysqli_fetch_assoc($q_admin);

$last_active = strtotime($admin['last_active'] ?? 0);
$online = (time() - $last_active) < 60;

// ===========================
// SET POIN PER KATEGORI
// ===========================
$poinKategori = [
    'Sampah Sisa Makanan' => 10,
    'Sampah Sisa Buah dan Sayur' => 12,
    'Sampah Hewani' => 8,
    'Sampah Pertanian' => 5,
    'Sampah Kotoran Hewan' => 4,
    'Sampah Daun dan Ranting Kering' => 6
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Validasi Setoran</title>
    <link rel="stylesheet" href="../assets/css_admin/validasi_setoran.css">
</head>

<body>

<header class="navbar">
  <div class="logo-container">
    <img src="../assets/img/logo hitam.png" class="logo" />
    <div class="status-indicator <?= $online ? 'online' : 'offline' ?>">
        <?= $online ? 'Online' : 'Offline' ?>
    </div>
  </div>

  <div class="profile-icon" onclick="window.location.href='profile_admin.php'">
      <img src="../assets/img/icon user hitam.png" />
  </div>
</header>

<main class="dashboard-container">

<section class="greeting">
    <h1>Validasi Setoran</h1>
    <p>Periksa dan konfirmasi setoran sampah dari pengguna</p>
</section>

<nav class="menu">
    <a href="dashboard.php"><button>Dashboard</button></a>
    <a href="validasi_setoran.php"><button class="active">Validasi Setoran</button></a>
    <a href="validasi_reward.php"><button>Validasi Reward</button></a>
    <a href="kelola_laporan.php"><button>Kelola Laporan</button></a>
    <a href="data_user.php"><button>Data User</button></a>
    <a href="kelola_banksampah.php"><button>Kelola Bank Sampah</button></a>
    <a href="kelola_reward.php"><button>Kelola Reward</button></a>
</nav>

<div class="setoran-list">

<?php while ($row = mysqli_fetch_assoc($query)) { 
    $kategori = $row['kategori'];
    $berat = $row['berat'];
    $poinPerKg = $poinKategori[$kategori] ?? 10;
    $estimasiPoin = $berat * $poinPerKg;
?>

<div class="setoran-card">

    <img src="../uploads/<?= $row['foto_bukti'] ?>" class="foto">

    <div class="info">
        <h3><?= $row['nama'] ?></h3>

        <p>Jenis: <?= $kategori ?></p>
        <p>Berat: <?= $berat ?> Kg</p>
        <p><b>Estimasi Poin: <?= $estimasiPoin ?></b></p>

        <p>Bank Sampah: <?= $row['nama_bank'] ?? '-' ?></p>
        <p>Tanggal: <?= $row['tanggal'] ?></p>

        <span class="waiting">Menunggu Validasi</span>

        <div class="divider"></div>

        <div class="actions">
            <a href="validasi_proses.php?id=<?= $row['id_setoran'] ?>&aksi=setuju" class="btn setuju">
                ✔ Setuju & Berikan Poin
            </a>

            <a href="validasi_proses.php?id=<?= $row['id_setoran'] ?>&aksi=tolak" class="btn tolak">
                ✖ Tolak Setoran
            </a>
        </div>

    </div>
</div>

<?php } ?>

</div>

</main>
</body>
</html>
