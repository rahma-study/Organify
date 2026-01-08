<?php
session_start();

if (basename($_SERVER['PHP_SELF']) != "profile_admin.php") {
    $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
}

// Jika admin belum login → paksa login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php?error=not_logged_in");
    exit();
}

$conn = new mysqli("localhost", "root", "", "db_banksampah");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID admin dari session
$id_admin = $_SESSION['admin_id'];

// Update last_active terlebih dahulu
mysqli_query($conn, "UPDATE admin SET last_active = NOW() WHERE id_admin = '$id_admin'");

// Ambil ulang data admin
$q_admin = mysqli_query($conn, "SELECT * FROM admin WHERE id_admin = '$id_admin'");
$admin = mysqli_fetch_assoc($q_admin);

// Hitung status online/offline
$last_active = strtotime($admin['last_active'] ?? 0);
$online = (time() - $last_active) < 60;

// Ambil semua data bank sampah
$sql = "SELECT * FROM banksampah ORDER BY id_bank ASC";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kelola Bank Sampah | Admin Organify</title>
  <link rel="stylesheet" href="../assets/css_admin/kelola_banksampah.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
    <h1>Kelola Bank Sampah</h1>
    <p>Kelola semua data bank sampah yang terdaftar.</p>
  </section>

    <nav class="menu">
        <a href="dashboard.php"><button>Dashboard</button></a>
        <a href="validasi_setoran.php"><button>Validasi Setoran</button></a>
        <a href="validasi_reward.php"><button>Validasi Reward</button></a>
        <a href="kelola_laporan.php"><button>Kelola Laporan</button></a>
        <a href="data_user.php"><button>Data User</button></a>
        <a href="kelola_banksampah.php"><button class="active">Kelola Bank Sampah</button></a>
        <a href="kelola_reward.php"><button>Kelola Reward</button></a>
    </nav>

  <!-- Tombol Tambah Bank Sampah -->
  <div class="stats-header">
    <a href="tambah_bank.php" class="btn-add">
      <i class="fa-solid fa-plus"></i> Tambah Bank Sampah
    </a>
  </div>

  <!-- Grid Bank Sampah -->
  <section class="stats-grid">
  <?php
  if ($result->num_rows > 0) {
    while($bank = $result->fetch_assoc()) {
      $statusClass = ($bank['status'] == 'Aktif') ? 'status-active' : 'status-nonaktif';
      echo '<div class="card">';
      echo '<div class="status-tag ' . $statusClass . '">' . $bank['status'] . '</div>';
      echo '<h2>' . $bank['nama_bank'] . '</h2>';
      echo '<p>' . $bank['alamat'] . '</p>';
      echo '<p>Kota: ' . $bank['kota_kabupaten'] . '</p>';
      echo '<p>Kontak: ' . ($bank['kontak'] ?: '-') . '</p>';
      echo '<div class="card-btns">';
      echo '<a href="edit_bank.php?id=' . $bank['id_bank'] . '" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i> Edit</a>';
      echo '<a href="hapus_bank.php?id=' . $bank['id_bank'] . '" class="btn-hapus"><i class="fa-solid fa-trash"></i> Hapus</a>';
      echo '</div>';
      echo '</div>';
    }
  } else {
    echo '<p class="empty-msg">Belum ada data bank sampah.</p>';
  }
  ?>
  </section>

</main>
</body>
</html>
