<?php
include 'check_admin.php';
include '../config.php';

// simpan last_page kecuali profile_admin
if (basename($_SERVER['PHP_SELF']) != "profile_admin.php") {
    $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
}

// ===========================
// Fungsi helper scalar()
// ===========================
function scalar($conn, $sql) {
    $res = mysqli_query($conn, $sql);
    if (!$res) return null;
    $row = mysqli_fetch_row($res);
    return $row[0] ?? null;
}

// ===========================
// 1. Total User
// ===========================
$total_user = (int) scalar($conn, "SELECT COUNT(*) FROM user");

// ===========================
// 2. Validasi Pending (Setoran + Reward)
// ===========================
$setoran_pending = (int) scalar($conn, "
    SELECT COUNT(*) 
    FROM setoran 
    WHERE status='menunggu'
");

$reward_pending = (int) scalar($conn, "
    SELECT COUNT(*) 
    FROM req_reward
    WHERE status='menunggu'
");

$validasi_pending = $setoran_pending + $reward_pending;

// ===========================
// 3. Setoran Hari Ini
// ===========================
$setoran_hari_ini = (int) scalar($conn, "
    SELECT COUNT(*) 
    FROM setoran 
    WHERE DATE(tanggal) = CURDATE()
");

// ===========================
// 4. Total Sampah (kg) — Hanya yang diverifikasi
// ===========================
$total_sampah = (float) scalar($conn, "
    SELECT IFNULL(SUM(berat),0) 
    FROM setoran 
    WHERE status='diverifikasi'
");

// ===========================
// 5. Jumlah Bank Sampah
// ===========================
$bank_aktif = (int) scalar($conn, "SELECT COUNT(*) FROM banksampah");

// ===========================
// 6. Total Poin Diberikan
// ===========================
$poinKategori = [
    'Sampah Sisa Makanan' => 10,
    'Sampah Sisa Buah dan Sayur' => 12,
    'Sampah Hewani' => 8,
    'Sampah Pertanian' => 5,
    'Sampah Kotoran Hewan' => 4,
    'Sampah Daun dan Ranting Kering' => 6
];

$total_poin = 0;
$res_poin = mysqli_query($conn, "
    SELECT kategori, berat 
    FROM setoran 
    WHERE status='diverifikasi'
");
while ($row = mysqli_fetch_assoc($res_poin)) {
    $kategori = $row['kategori'];
    $berat = (float)$row['berat'];
    $total_poin += ($poinKategori[$kategori] ?? 0) * $berat;
}

// ===========================
// 7. Status Online Admin
// ===========================
$id_admin = $_SESSION['admin_id'];
mysqli_query($conn, "UPDATE admin SET last_active = NOW() WHERE id_admin = '$id_admin'");
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT last_active FROM admin WHERE id_admin = '$id_admin'"));
$online = (time() - strtotime($admin['last_active'] ?? 0)) < 60;

// ===========================
// 8. Grafik Setoran per Bulan
// ===========================
$grafik = array_fill(0, 12, 0);
$q_grafik = mysqli_query($conn, "
    SELECT MONTH(tanggal) AS bulan, COUNT(*) AS total 
    FROM setoran 
    WHERE status='diverifikasi'
    GROUP BY MONTH(tanggal)
");
while ($row = mysqli_fetch_assoc($q_grafik)) {
    $grafik[(int)$row['bulan']-1] = (int)$row['total'];
}
$data_grafik = json_encode($grafik);

// ===========================
// 9. Grafik Total Sampah (kg) per Bulan
// ===========================
$grafik_kg = array_fill(0, 12, 0);
$q_sampah_bulan = mysqli_query($conn, "
    SELECT MONTH(tanggal) AS bulan, SUM(berat) AS total_kg
    FROM setoran
    WHERE status='diverifikasi'
    GROUP BY MONTH(tanggal)
");
while ($row = mysqli_fetch_assoc($q_sampah_bulan)) {
    $grafik_kg[(int)$row['bulan']-1] = (float)$row['total_kg'];
}
$data_grafik_kg = json_encode($grafik_kg);
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin | Organify</title>
  <link rel="stylesheet" href="../assets/css_admin/dashboard.css">
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
    <h1>Halo, Admin!</h1>
    <p>Pantau aktivitas terbaru sistem secara cepat dan efisien</p>
  </section>

  <nav class="menu">
      <a href="dashboard.php"><button class="active">Dashboard</button></a>
      <a href="validasi_setoran.php"><button>Validasi Setoran</button></a>
      <a href="validasi_reward.php"><button>Validasi Reward</button></a>
      <a href="kelola_laporan.php"><button>Kelola Laporan</button></a>
      <a href="data_user.php"><button>Data User</button></a>
      <a href="kelola_banksampah.php"><button>Kelola Bank Sampah</button></a>
      <a href="kelola_reward.php"><button>Kelola Reward</button></a>
  </nav>

  <section class="stats-grid">
    <div class="card">
      <p>Total User</p>
      <h2><?= $total_user ?></h2>
      <img src="../assets/img/icon user biru.png" alt="User Icon" />
    </div>

    <div class="card">
      <p>Validasi Pending</p>
      <h2 class="orange"><?= $validasi_pending ?></h2>
      <img src="../assets/img/validasi icon.png" alt="Validasi Icon" />
    </div>

    <div class="card">
      <p>Setoran Hari Ini</p>
      <h2 class="red"><?= $setoran_hari_ini ?></h2>
      <img src="../assets/img/reward_aktif1.png" />
    </div>

    <div class="card">
      <p>Total Sampah</p>
      <h2><?= number_format($total_sampah, 2) ?> kg</h2>
      <img src="../assets/img/recycle icon.png" alt="Recycle Icon" />
    </div>

    <div class="card">
      <p>Bank Sampah Aktif</p>
      <h2><?= $bank_aktif ?></h2>
      <img src="../assets/img/icon bank.png" alt="Bank Icon" />
    </div>

    <div class="card">
      <p>Total Poin Diberikan</p>
      <h2><?= $total_poin ?></h2>
      <img src="../assets/img/icon reward.png" alt="Reward Icon" />
    </div>
  </section>

  <section class="chart-section">
      <div class="chart-box">
          <h2>Jumlah Setoran Diverifikasi per Bulan</h2>
          <canvas id="grafikSetoran"></canvas>
      </div>

      <div class="chart-box">
          <h2>Total Berat Sampah per Bulan (kg)</h2>
          <canvas id="grafikBerat"></canvas>
      </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dataGrafik = <?= $data_grafik ?>;
    const dataGrafikKg = <?= $data_grafik_kg ?>;
</script>
<script src="../assets/js/dashboard_admin.js"></script>
</body>
</html>
