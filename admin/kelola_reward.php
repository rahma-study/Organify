<?php
include 'check_admin.php';
include '../config.php';

if (basename($_SERVER['PHP_SELF']) != "profile_admin.php") {
    $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
}

// ================================
// 1. Ambil semua data reward
// ================================
$q = mysqli_query($conn, "SELECT * FROM reward ORDER BY id_reward DESC");
$reward = [];
while ($row = mysqli_fetch_assoc($q)) {
    $reward[] = $row;
}

// ================================
// 2. Hitung Statistik
// ================================
$total_reward = count($reward);

$aktif_reward = 0;
$total_stok = 0;
$total_tukar = 0;

foreach ($reward as $r) {
    if ($r['status'] === 'aktif') $aktif_reward++;
    $total_stok += intval($r['stok']);
    $total_tukar += intval($r['ditukar']);
}

// ================================
// 3. STATUS ADMIN ONLINE
// ================================

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kelola Reward | Organify</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css_admin/kelola_reward.css">
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
    <h1>Kelola Reward</h1>
    <p>Kelola dan pantau data reward dengan mudah.</p>
</section>

    <nav class="menu">
        <a href="dashboard.php"><button>Dashboard</button></a>
        <a href="validasi_setoran.php"><button>Validasi Setoran</button></a>
        <a href="validasi_reward.php"><button>Validasi Reward</button></a>
        <a href="kelola_laporan.php"><button>Kelola Laporan</button></a>
        <a href="data_user.php"><button>Data User</button></a>
        <a href="kelola_banksampah.php"><button>Kelola Bank Sampah</button></a>
        <a href="kelola_reward.php"><button class="active">Kelola Reward</button></a>
    </nav>

<section class="stats">

  <div class="stat">
    <p>Total Reward</p>
    <h2><?= $total_reward ?></h2>
    <img src="../assets/img/total_reward1.png" />
  </div>

  <div class="stat">
    <p>Reward Aktif</p>
    <h2><?= $aktif_reward ?></h2>
    <img src="../assets/img/reward_aktif1.png" />
  </div>

  <div class="stat">
    <p>Total Ditukar</p>
    <h2><?= $total_tukar ?></h2>
    <img src="../assets/img/total_ditukar1.png" />
  </div>

  <div class="stat">
    <p>Total Stok</p>
    <h2><?= $total_stok ?></h2>
    <img src="../assets/img/total_stok1.png" />
  </div>

</section>

<section class="filter">
  <div class="filter-left">
    <h3>Filter Kategori</h3>
    <div class="filter-buttons">
        <button class="active">Semua Kategori</button>
        <button>E-Money</button>
        <button>Voucher</button>
        <button>Green Impact</button>
    </div>
  </div>

  <a href="tambah_reward.php" class="btn-add">
      <i class="fa-solid fa-plus"></i> Tambah Reward
  </a>
</section>


<section class="table-card">
  <h3>Daftar Reward</h3>

  <table>
    <thead>
      <tr>
        <th>Reward</th>
        <th>Kategori</th>
        <th>Poin</th>
        <th>Stok</th>
        <th>Ditukar</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>

    <tbody>

      <?php if (count($reward) == 0): ?>
        <tr><td colspan="7" style="text-align:center;">Tidak ada data reward</td></tr>
      <?php endif; ?>

      <?php foreach ($reward as $r): ?>
        <tr>

          <!-- KOLOM REWARD (gambar + teks rapi) -->
          <td>
            <div class="reward-cell">
              <img src="../uploads/reward/<?= $r['gambar'] ?>" />
              <span><?= $r['nama_reward'] ?></span>
            </div>
          </td>

          <!-- KATEGORI -->
          <td>
            <span class="tag 
              <?= ($r['kategori']=='E-Money'?'e':($r['kategori']=='Voucher'?'v':'g')) ?>">
              <?= $r['kategori'] ?>
            </span>
          </td>

          <td><?= $r['poin_diperlukan'] ?></td>
          <td><?= $r['stok'] ?></td>
          <td><?= $r['ditukar'] ?></td>

          <!-- STATUS -->
          <td>
            <span class="status <?= strtolower($r['status']) ?>">
              <?= $r['status'] ?>
            </span>
          </td>

          <!-- AKSI -->
          <td class="aksi">
              <a href="edit_reward.php?id=<?= $r['id_reward'] ?>" class="btn-edit">
                  <i class="fa-solid fa-pen-to-square"></i> Edit
              </a>

              <a href="hapus_reward.php?id=<?= $r['id_reward'] ?>" 
                class="btn-hapus"
                onclick="return confirm('Hapus reward ini?');">
                  <i class="fa-solid fa-trash"></i> Hapus
              </a>
          </td>

        </tr>
      <?php endforeach; ?>

    </tbody>
  </table>

</section>
<script src="../assets/js/kelola_reward.js"></script>

</body>
</html>
