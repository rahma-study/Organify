<?php
session_start();

// koneksi
$conn = new mysqli("localhost", "root", "", "db_banksampah");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// cek user login
$id_user = $_SESSION['id_user'] ?? null;

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

    $qSetoran = $conn->query("
        SELECT berat, kategori
        FROM setoran
        WHERE id_user='$id_user_safe' AND status='diverifikasi'
    ");
    while ($row = $qSetoran->fetch_assoc()) {
        $total_poin += $row['berat'] * ($poinKategori[$row['kategori']] ?? 0);
    }

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

// Hitung total poin
$total_poin = $id_user ? hitungTotalPoin($conn, $id_user) : 0;

// ===== Ambil data reward =====
$reward = [];
$q_reward = $conn->query("SELECT * FROM reward WHERE status = 1 ORDER BY id_reward DESC");
if ($q_reward && $q_reward->num_rows > 0) {
    while ($row = $q_reward->fetch_assoc()) {
        $reward[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Hadiah</title>
    <link rel="stylesheet" href="assets/css/tukar_poin.css" />
</head>
<body>

<section class="reward-hero">
    <img class="hero-bg" src="assets/img/reward1.jpg" alt="">

    <div class="top-nav">
        <button class="back-btn" onclick="window.location.href='landing_page.php'">
            <img src="assets/img/back button.png" />
        </button>

        <div class="user-point">
            <img src="assets/img/poin.png" class="coin-icon">
            <?= number_format($total_poin, 0, ',', '.'); ?> poin
        </div>
    </div>

    <div class="hero-content">
        <h1>Katalog Hadiah</h1>
        <p>Tukarkan poin untuk hadiah menarik</p>
    </div>
</section>

<div class="tabs">
    <button class="tab active">Semua</button>
    <button class="tab">E-Money</button>
    <button class="tab">Voucher</button>
    <button class="tab">Green Impact</button>
</div>

<section class="gift-grid">

<?php foreach ($reward as $r): ?>
    <div class="gift-card">
        <span class="tag"><?= htmlspecialchars($r['kategori']); ?></span>
        <img src="uploads/reward/<?= htmlspecialchars($r['gambar']); ?>" alt="Reward">
        <h3><?= htmlspecialchars($r['nama_reward']); ?></h3>
        <p><?= htmlspecialchars($r['deskripsi']); ?></p>
        <div class="point"><?= number_format($r['poin_diperlukan'], 0, ',', '.'); ?> poin</div>

        <?php if ($r['stok'] > 0): ?>
        <!-- Tombol tetap aktif, JS nanti yang cek poin -->
        <button class="btn-tukar"
            data-id="<?= $r['id_reward']; ?>"
            data-nama="<?= htmlspecialchars($r['nama_reward']); ?>"
            data-poin="<?= $r['poin_diperlukan']; ?>"
            data-img="uploads/reward/<?= htmlspecialchars($r['gambar']); ?>"
            data-desc="<?= htmlspecialchars($r['deskripsi']); ?>"
            data-kategori="<?= htmlspecialchars($r['kategori']); ?>"
            data-stok="<?= $r['stok']; ?>"
            data-userpoin="<?= $total_poin; ?>">
            Tukar
        </button>
        <?php else: ?>
            <button class="btn-tukar disabled" disabled>Stok Habis</button>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

    <!-- ===== POPUP ===== -->
    <div id="popup-overlay" class="popup-overlay hidden"></div>
    <div id="popup-tukar" class="popup hidden">
        <div class="popup-header">
            <h2>Tukar Hadiah</h2>
            <button id="popup-close">×</button>
        </div>

        <div class="popup-content">
            <div class="popup-gift">
                <img id="popup-img" src="" alt="">
                <div>
                    <h3 id="popup-nama"></h3>
                    <p id="popup-desc"></p>
                    <span class="popup-poin"><span id="popup-harga"></span> poin</span>
                </div>
            </div>

            <div class="popup-detail">
                <p>Poin Anda Saat Ini: <b id="popup-user"></b> poin</p>
                <p>Harga Hadiah: <b id="popup-harga2"></b> poin</p>
                <p class="sisa">Sisa Poin: <b id="popup-sisa"></b> poin</p>
                <p id="popup-notif" style="color:red; display:none;">Poin Anda kurang untuk menukar hadiah ini.</p>
            </div>

            <div id="popup-input-group" class="hidden">
                <label id="popup-label">Nomor</label>
                <input type="text" id="popup-input" placeholder="">
                <small>Pastikan nomor yang dimasukkan sudah benar</small>
            </div>

            <div class="popup-btn-group">
                <button id="btn-batal" class="btn-batal">Batal</button>
                <button id="btn-konfirmasi" class="btn-konfirmasi">✔ Konfirmasi</button>
            </div>
        </div>
    </div>

</section>

<script src="assets/js/tukar_poin.js"></script>
</body>
</html>
