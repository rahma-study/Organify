<?php
include 'check_admin.php';     
include '../config.php';       

if (basename($_SERVER['PHP_SELF']) != "profile_admin.php") {
    $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
}

// ===========================
// DEFINISI POIN PER KATEGORI
// ===========================
$poinKategori = [
    'Sampah Sisa Makanan' => 10,
    'Sampah Sisa Buah dan Sayur' => 12,
    'Sampah Hewani' => 8,
    'Sampah Pertanian' => 5,
    'Sampah Kotoran Hewan' => 4,
    'Sampah Daun dan Ranting Kering' => 6
];

// ===========================
// AMBIL DATA USER
// ===========================
$search = isset($_GET['search']) ? $_GET['search'] : "";
$search = mysqli_real_escape_string($conn, $search);

$queryUser = mysqli_query($conn, "
    SELECT * FROM user 
    WHERE nama LIKE '%$search%' OR email LIKE '%$search%'
");

// ===========================
// HITUNG TOTAL POIN & BERAT PER USER
// ===========================
$totalPoinUser = [];
$totalBeratUser = [];

// Ambil semua setoran diverifikasi
$querySetoran = mysqli_query($conn, "
    SELECT id_user, kategori, berat 
    FROM setoran
    WHERE status='diverifikasi'
");

while ($row = mysqli_fetch_assoc($querySetoran)) {
    $id = $row['id_user'];
    $kategori = $row['kategori'];
    $berat = (float)$row['berat'];

    $poin = isset($poinKategori[$kategori]) ? $berat * $poinKategori[$kategori] : 0;

    $totalPoinUser[$id] = ($totalPoinUser[$id] ?? 0) + $poin;
    $totalBeratUser[$id] = ($totalBeratUser[$id] ?? 0) + $berat;
}

// Kurangi poin reward yang sudah disetujui & berhasil per user
$queryTukar = mysqli_query($conn, "
    SELECT rr.id_user, IFNULL(SUM(r.poin_diperlukan),0) AS total_tukar
    FROM req_reward rr
    LEFT JOIN reward r ON rr.id_reward = r.id_reward
    WHERE rr.status IN ('disetujui','berhasil')
    GROUP BY rr.id_user
");

while ($row = mysqli_fetch_assoc($queryTukar)) {
    $id = $row['id_user'];
    $totalTukar = (float)$row['total_tukar'];
    if (isset($totalPoinUser[$id])) {
        $totalPoinUser[$id] -= $totalTukar;
        if ($totalPoinUser[$id] < 0) $totalPoinUser[$id] = 0;
    } else {
        $totalPoinUser[$id] = 0; // user belum ada setoran tapi sudah tukar reward
    }
}

// ===========================
// STATUS ONLINE ADMIN
// ===========================
$id_admin = $_SESSION['admin_id'];
mysqli_query($conn, "UPDATE admin SET last_active = NOW() WHERE id_admin = '$id_admin'");
$q_admin = mysqli_query($conn, "SELECT * FROM admin WHERE id_admin = '$id_admin'");
$admin = mysqli_fetch_assoc($q_admin);
$last_active = strtotime($admin['last_active'] ?? 0);
$online = (time() - $last_active) < 60;  
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User | Organify</title>
    <link rel="stylesheet" href="../assets/css_admin/data_user.css">
</head>

<body>
<header class="navbar">
  <div class="logo-container">
    <img src="../assets/img/logo hitam.png" alt="Organify Logo" class="logo" />
    <div class="status-indicator <?= $online ? 'online' : 'offline' ?>">
        <?= $online ? 'Online' : 'Offline' ?>
    </div>
  </div>
  <div class="profile-icon" onclick="window.location.href='profile_admin.php'">
      <img src="../assets/img/icon user hitam.png" alt="Akun Admin" />
  </div>
</header>

<main class="dashboard-container">
    <section class="greeting">
        <h1>Data User</h1>
        <p>Kelola informasi pengguna untuk menjaga sistem tetap tertata</p>
    </section>

    <nav class="menu">
        <a href="dashboard.php"><button>Dashboard</button></a>
        <a href="validasi_setoran.php"><button>Validasi Setoran</button></a>
        <a href="validasi_reward.php"><button>Validasi Reward</button></a>
        <a href="kelola_laporan.php"><button>Kelola Laporan</button></a>
        <a href="data_user.php"><button class="active">Data User</button></a>
        <a href="kelola_banksampah.php"><button>Kelola Bank Sampah</button></a>
        <a href="kelola_reward.php"><button>Kelola Reward</button></a>
    </nav>

    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Cari user..." value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <div class="user-table">
        <div class="user-row user-header">
            <div>USER INFO</div>
            <div>KONTAK</div>
            <div>TOTAL POIN</div>
            <div>TOTAL SAMPAH</div>
            <div>BERGABUNG</div>
            <div>STATUS</div>
        </div>

        <?php while ($row = mysqli_fetch_assoc($queryUser)) { ?>
        <div class="user-row">
            <div class="cell">
                <p class="name"><?= htmlspecialchars($row['nama']) ?></p>
                <p class="id">ID: <?= $row['id_user'] ?></p>
            </div>
            <div class="cell">
                <p class="email"><?= htmlspecialchars($row['email']) ?></p>
                <p class="phone"><?= htmlspecialchars($row['no_hp']) ?></p>
            </div>
            <div class="cell total-poin">
                <p><?= number_format($totalPoinUser[$row['id_user']] ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="cell total-berat">
                <p><?= number_format($totalBeratUser[$row['id_user']] ?? 0, 2, ',', '.') ?> kg</p>
            </div>
            <div class="cell bergabung">
                <p><?= $row['tanggal_daftar'] ?></p>
            </div>
            <div class="cell">
                <span class="status <?= strtolower($row['status']) ?>">
                    <?= htmlspecialchars($row['status']) ?>
                </span>
            </div>
        </div>
        <?php } ?>
    </div>
</main>
</body>
</html>
