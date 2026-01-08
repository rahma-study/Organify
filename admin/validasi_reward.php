<?php
include 'check_admin.php';
include '../config.php';

if (basename($_SERVER['PHP_SELF']) != "profile_admin.php") {
    $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
}

// Ambil request penukaran poin yang masih menunggu
$query = mysqli_query($conn, "
    SELECT rr.*, u.nama, r.nama_reward, r.gambar, r.poin_diperlukan
    FROM req_reward rr
    JOIN user u ON rr.id_user = u.id_user
    JOIN reward r ON rr.id_reward = r.id_reward
    WHERE rr.status = 'menunggu'
    ORDER BY rr.tanggal DESC
");

// Ambil ID admin dari session
$id_admin = $_SESSION['admin_id'];

// 🔥 UPDATE last_active dulu
mysqli_query($conn, "UPDATE admin SET last_active = NOW() WHERE id_admin = '$id_admin'");

// 🔥 Baru ambil data admin
$q_admin = mysqli_query($conn, "SELECT * FROM admin WHERE id_admin = '$id_admin'");
$admin = mysqli_fetch_assoc($q_admin);

// Hitung status online/offline
$last_active = strtotime($admin['last_active'] ?? 0);
$online = (time() - $last_active) < 60;  // < 60 detik = online
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Penukaran Reward</title>
    <link rel="stylesheet" href="../assets/css_admin/validasi_reward.css">
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
        <h1>Validasi Penukaran Reward</h1>
        <p>Periksa dan konfirmasi permintaan penukaran poin dari pengguna</p>
    </section>

    <nav class="menu">
        <a href="dashboard.php"><button>Dashboard</button></a>
        <a href="validasi_setoran.php"><button>Validasi Setoran</button></a>
        <a href="validasi_reward.php"><button class="active">Validasi Reward</button></a>
        <a href="kelola_laporan.php"><button>Kelola Laporan</button></a>
        <a href="data_user.php"><button>Data User</button></a>
        <a href="kelola_banksampah.php"><button>Kelola Bank Sampah</button></a>
        <a href="kelola_reward.php"><button>Kelola Reward</button></a>
    </nav>

    <div class="setoran-list">

        <?php while ($row = mysqli_fetch_assoc($query)) { ?>
        <div class="setoran-card">

            <!-- Foto reward -->
            <img src="../uploads/reward/<?= $row['gambar'] ?>" class="foto">

            <div class="info">

                <h3><?= $row['nama'] ?></h3>

                <p>Reward: <?= $row['nama_reward'] ?></p>
                <p>Harga Poin: <?= number_format($row['poin_diperlukan'], 0, ',', '.') ?></p>
                <p>Tanggal Request: <?= $row['tanggal'] ?></p>

                <span class="waiting">Menunggu Konfirmasi</span>

                <div class="divider"></div>

                <div class="actions">
                    <a href="validasi_reward_proses.php?id=<?= $row['id_req'] ?>&aksi=setuju" class="btn setuju">
                        ✔ Setuju & Berikan Reward
                    </a>

                    <a href="validasi_reward_proses.php?id=<?= $row['id_req'] ?>&aksi=tolak" class="btn tolak">
                        ✖ Tolak Permintaan
                    </a>
                </div>

            </div>
        </div>
        <?php } ?>

    </div>

</main>

</body>
</html>
