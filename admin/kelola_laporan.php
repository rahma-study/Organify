<?php
session_start();
date_default_timezone_set('Asia/Makassar');

// simpan last_page kecuali profile_admin.php
if (basename($_SERVER['PHP_SELF']) != "profile_admin.php") {
    $_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
}

// pastikan admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php?error=not_logged_in");
    exit();
}

// koneksi database
$conn = new mysqli("localhost", "root", "", "db_banksampah");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

// ambil admin
$id_admin = $_SESSION['admin_id'];
$id_admin_esc = $conn->real_escape_string($id_admin);

// update last_active
$conn->query("UPDATE admin SET last_active = NOW() WHERE id_admin = '$id_admin_esc'");

// status admin
$q_admin = $conn->query("SELECT * FROM admin WHERE id_admin = '$id_admin_esc'");
$admin = $q_admin->fetch_assoc();
$online = (time() - strtotime($admin['last_active'])) < 60;

// helper
function scalar($conn, $sql) {
    $res = $conn->query($sql);
    if (!$res) return null;
    $row = $res->fetch_row();
    return $row[0] ?? null;
}

// =========================
// FILTER PERIODE
// =========================
$periode = $_GET['periode'] ?? 'semua';
$periode = in_array($periode, ['harian','mingguan','bulanan','tahunan','semua']) ? $periode : 'semua';

$where_date = "";

switch ($periode) {
    case 'harian':
        $where_date = "AND DATE(s.tanggal) = CURDATE()";
        break;

    case 'mingguan':
        $where_date = "AND DATE(s.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;

    case 'bulanan':
        $where_date = "AND MONTH(s.tanggal) = MONTH(CURDATE()) AND YEAR(s.tanggal) = YEAR(CURDATE())";
        break;

    case 'tahunan':
        $where_date = "AND YEAR(s.tanggal) = YEAR(CURDATE())";
        break;

    default:
        $where_date = "";
}

// =========================
// 1) Total Setoran DITERIMA
// =========================
$total_setor = (int) scalar($conn, "
    SELECT COUNT(*) 
    FROM setoran s
    JOIN validasi v ON v.id_setoran = s.id_setoran
    WHERE v.status_validasi = 'diterima' $where_date
");

// =========================
// 2) Total Berat Sampah (kg)
// =========================
$total_sampah = (float) scalar($conn, "
    SELECT IFNULL(SUM(s.berat), 0)
    FROM setoran s
    JOIN validasi v ON v.id_setoran = s.id_setoran
    WHERE v.status_validasi = 'diterima' $where_date
");

// =========================
// 3) Total Poin
// =========================
$poinKategori = [
    'Sampah Sisa Makanan' => 10,
    'Sampah Sisa Buah dan Sayur' => 12,
    'Sampah Hewani' => 8,
    'Sampah Pertanian' => 5,
    'Sampah Kotoran Hewan' => 4,
    'Sampah Daun dan Ranting Kering' => 6
];

$total_poin = 0;

$res_poin = $conn->query("
    SELECT s.kategori, s.berat
    FROM setoran s
    JOIN validasi v ON v.id_setoran = s.id_setoran
    WHERE v.status_validasi = 'diterima' $where_date
");

if ($res_poin) {
    while ($row = $res_poin->fetch_assoc()) {
        $nilai = $poinKategori[$row['kategori']] ?? 0;
        $total_poin += $nilai * (float) $row['berat'];
    }
}

// =========================
// 4) Reward Ditukar
// =========================
$sql_reward = "SELECT COUNT(*) FROM req_reward rr WHERE rr.status IN ('disetujui','berhasil')";

if ($periode != 'semua') {
    $sql_reward .= " AND DATE(rr.tanggal) >= (
        CASE 
            WHEN '$periode' = 'harian' THEN CURDATE()
            WHEN '$periode' = 'mingguan' THEN DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            WHEN '$periode' = 'bulanan' THEN DATE_FORMAT(CURDATE(), '%Y-%m-01')
            WHEN '$periode' = 'tahunan' THEN DATE_FORMAT(CURDATE(), '%Y-01-01')
        END
    )";
}

$reward_ditukar = (int) scalar($conn, $sql_reward);

// =========================
// 5) Total User
// =========================
$total_user = (int) scalar($conn, "SELECT COUNT(*) FROM user");

// =========================
// 6) TOTAL SETORAN PENDING
// =========================
$total_pending = (int) scalar($conn, "
    SELECT COUNT(*)
    FROM setoran s
    LEFT JOIN validasi v ON v.id_setoran = s.id_setoran
    WHERE v.id_setoran IS NULL
    $where_date
");

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kelola Laporan | Organify</title>
  <link rel="stylesheet" href="../assets/css_admin/kelola_laporan.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<header class="navbar">
    <div class="logo-container">
        <img src="../assets/img/logo hitam.png" class="logo">
        <div class="status-indicator <?= $online ? 'online' : 'offline' ?>">
            <?= $online ? 'Online' : 'Offline' ?>
        </div>
    </div>

    <div class="profile-icon" onclick="window.location='profile_admin.php'">
        <img src="../assets/img/icon user hitam.png">
    </div>
</header>

<main class="dashboard-container">

    <section class="greeting">
        <h1>Kelola Laporan</h1>
        <p>Lihat dan kelola laporan transaksimu secara detail dan akurat</p>
    </section>

    <nav class="menu">
        <a href="dashboard.php"><button>Dashboard</button></a>
        <a href="validasi_setoran.php"><button>Validasi Setoran</button></a>
        <a href="validasi_reward.php"><button>Validasi Reward</button></a>
        <a href="kelola_laporan.php"><button class="active">Kelola Laporan</button></a>
        <a href="data_user.php"><button>Data User</button></a>
        <a href="kelola_banksampah.php"><button>Kelola Bank Sampah</button></a>
        <a href="kelola_reward.php"><button>Kelola Reward</button></a>
    </nav>

    <!-- FILTER -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <label>Periode Laporan:</label>
            <select name="periode" onchange="this.form.submit()">
                <option value="semua"    <?= $periode=='semua'?'selected':'' ?>>Semua</option>
                <option value="harian"   <?= $periode=='harian'?'selected':'' ?>>Harian</option>
                <option value="mingguan" <?= $periode=='mingguan'?'selected':'' ?>>Mingguan</option>
                <option value="bulanan"  <?= $periode=='bulanan'?'selected':'' ?>>Bulanan</option>
                <option value="tahunan"  <?= $periode=='tahunan'?'selected':'' ?>>Tahunan</option>
            </select>
        </form>
    </div>

    <!-- CARDS -->
    <section class="stats-grid">

        <div class="card">
            <p>Total Setor</p>
            <h2><?= number_format($total_setor) ?></h2>
            <img src="../assets/img/total_setor.png"/>
        </div>

        <div class="card">
            <p>Total Poin Diberikan</p>
            <h2><?= number_format($total_poin) ?></h2>
            <img src="../assets/img/icon reward.png"/>
        </div>

        <div class="card">
            <p>Reward Ditukar</p>
            <h2><?= number_format($reward_ditukar) ?></h2>
            <img src="../assets/img/total_ditukar1.png"/>
        </div>

        <div class="card">
            <p>Total Sampah</p>
            <h2><?= number_format($total_sampah, 2) ?> kg</h2>
            <img src="../assets/img/recycle icon.png"/>
        </div>

        <div class="card">
            <p>Total User</p>
            <h2><?= number_format($total_user) ?></h2>
            <img src="../assets/img/icon user biru.png"/>
        </div>

        <div class="card">
            <p>Total Setoran Pending</p>
            <h2><?= number_format($total_pending) ?></h2>
            <img src="../assets/img/validasi icon.png"/>
        </div>

    </section>

    <!-- AKSI -->
    <section class="actions">
        <h2>Aksi Cepat</h2>

        <div class="action-box">

            <div class="quick-card" onclick="window.location='laporan_view.php?tipe=excel'">
                <img src="../assets/img/excel.png" class="quick-icon">
                <div>
                    <h3>Lihat Laporan Excel</h3>
                </div>
            </div>

            <div class="quick-card" onclick="window.location='laporan_view.php?tipe=pdf'">
                <img src="../assets/img/PDF.png" class="quick-icon">
                <div>
                    <h3>Lihat Laporan PDF</h3>
                </div>
            </div>

        </div>
    </section>

</main>

</body>
</html>
