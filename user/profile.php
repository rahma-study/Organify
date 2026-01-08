<?php
session_start();
include 'config.php';

// Cek user login
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    header("Location: login.php");
    exit;
}

// ===== Fungsi hitung total poin =====
function hitungTotalPoin($conn, $id_user) {
    $poinKategori = [
        'Sampah Sisa Makanan' => 10,
        'Sampah Sisa Buah dan Sayur' => 12,
        'Sampah Hewani' => 8,
        'Sampah Pertanian' => 5,
        'Sampah Kotoran Hewan' => 4,
        'Sampah Daun dan Ranting Kering' => 6
    ];

    $id_user_safe = $conn->real_escape_string($id_user);
    $total_poin = 0;

    // Hitung poin dari setoran diverifikasi
    $qSetoran = $conn->query("
        SELECT berat, kategori
        FROM setoran
        WHERE id_user='$id_user_safe' AND status='diverifikasi'
    ");
    while ($row = $qSetoran->fetch_assoc()) {
        $total_poin += $row['berat'] * ($poinKategori[$row['kategori']] ?? 0);
    }

    // Kurangi poin yang sudah digunakan di reward disetujui/berhasil
    $qTukar = $conn->query("
        SELECT IFNULL(SUM(r.poin_diperlukan),0) AS poin_tukar
        FROM req_reward rr
        LEFT JOIN reward r ON rr.id_reward = r.id_reward
        WHERE rr.id_user='$id_user_safe' AND rr.status IN ('disetujui','berhasil')
    ");
    $poinTukar = (int)$qTukar->fetch_assoc()['poin_tukar'];

    $total_poin -= $poinTukar;
    return $total_poin < 0 ? 0 : $total_poin;
}

// Ambil data user
$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT nama, email, alamat, no_hp, tanggal_lahir
    FROM user
    WHERE id_user = '$id_user'
"));

// Hitung total poin, berat, dan setoran
$total_poin = hitungTotalPoin($conn, $id_user);

$total_setor = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_setor
    FROM setoran
    WHERE id_user = '$id_user' AND status='diverifikasi'
"))['total_setor'];

$total_berat = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(berat),0) AS total_berat
    FROM setoran
    WHERE id_user = '$id_user' AND status='diverifikasi'
"))['total_berat'];

// Ambil riwayat reward
$riwayat = mysqli_query($conn, "
    SELECT rr.id_req, rr.status, rr.tanggal AS tanggal_pengajuan,
           r.nama_reward, r.kategori, r.gambar,
           s.nomor_struk, s.tanggal AS tanggal_diterima,
           r.poin_diperlukan AS poin_digunakan
    FROM req_reward rr
    LEFT JOIN reward r ON rr.id_reward = r.id_reward
    LEFT JOIN struk_reward s ON rr.id_req = s.id_req
    WHERE rr.id_user = '$id_user'
    ORDER BY rr.id_req DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya</title>
<link rel="stylesheet" href="assets/css/profile.css">
<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

<header class="navbar">
    <button class="back-btn" onclick="window.location.href='landing_page.php'">
        <img src="assets/img/back button.png">
    </button>
    <h1>Profil Saya</h1>
    <button class="logout-btn" onclick="window.location.href='logout.php'">
        <img src="assets/img/logout button.png">
    </button>
</header>

<main class="container">

<!-- Profil -->
<section class="profile-card">
    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-icon">
                <img src="assets/img/user icon.png" alt="Foto Profil Pengguna">
            </div>
            <div class="profile-info-text">
                <h2><?= htmlspecialchars($user['nama']); ?></h2>
                <p><?= htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
    </div>
    <div class="stats">
        <div class="stat-box blue">
            <div class="icon">⭐</div>
            <h3><?= $total_setor ?></h3>
            <p>Total Setor</p>
        </div>
        <div class="stat-box green">
            <div class="icon">⚖</div>
            <h3><?= $total_berat ?> Kg</h3>
            <p>Total Berat</p>
        </div>
        <div class="stat-box yellow">
            <div class="icon">🪙</div>
            <h3><?= number_format($total_poin, 0, ',', '.') ?></h3>
            <p>Total Poin</p>
        </div>
    </div>
</section>

<!-- Info Profil -->
<section class="info-card">
    <h2 class="info-title">👤 Info Profil</h2>
    <form id="formProfile" action="update_profile.php" method="POST">
        <div class="info-grid">
            <div class="info-item">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']); ?>">
            </div>
            <div class="info-item">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>">
            </div>
            <div class="info-item">
                <label>No. Telepon</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp']); ?>">
            </div>
            <div class="info-item">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="<?= date('Y-m-d', strtotime($user['tanggal_lahir'])) ?>">
            </div>
            <div class="info-item full">
                <label>Alamat</label>
                <textarea name="alamat"><?= htmlspecialchars($user['alamat']); ?></textarea>
            </div>
        </div>
    </form>
</section>

<!-- Riwayat Penukaran -->
<section class="riwayat-card">
    <h2 class="riwayat-title">🧾 Riwayat Penukaran Poin</h2>
    <?php if(mysqli_num_rows($riwayat) > 0): ?>
        <div class="riwayat-list">
            <?php while($row = mysqli_fetch_assoc($riwayat)): ?>
            <div class="riwayat-item <?= strtolower($row['status']) ?>">
                <div class="riwayat-left">
                    <div class="riwayat-image">
                        <img src="uploads/reward/<?= htmlspecialchars($row['gambar']) ?>" alt="">
                    </div>
                    <div class="riwayat-info">
                        <h4><?= htmlspecialchars($row['nama_reward']) ?></h4>
                        <p>Kategori: <?= htmlspecialchars($row['kategori']) ?></p>
                        <p>Tanggal Pengajuan: <?= date('d M Y', strtotime($row['tanggal_pengajuan'])) ?></p>

                        <?php if($row['status'] == 'berhasil' && !empty($row['nomor_struk'])): ?>
                        <div class="struk-box">
                            <p><strong>Struk Reward</strong></p>
                            <p>Nomor Struk: <strong><?= htmlspecialchars($row['nomor_struk']) ?></strong></p>
                            <p>Reward: <?= htmlspecialchars($row['nama_reward']) ?></p>
                            <p>Poin Digunakan: <?= $row['poin_digunakan'] ?? 0 ?></p>
                            <p>Tanggal Diterima: <?= date('d M Y', strtotime($row['tanggal_diterima'])) ?></p>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
                <span class="riwayat-status <?= strtolower($row['status']) ?>">
                    <?= ucfirst($row['status']) ?>
                </span>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="riwayat-empty">Belum ada riwayat penukaran poin.</p>
    <?php endif; ?>
</section>

</main>
</body>
</html>
