<?php
session_start();

// Koneksi database
$conn = new mysqli("localhost", "root", "", "db_banksampah");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data bank sampah
$result = $conn->query("SELECT id_bank, nama_bank, alamat, kota_kabupaten, kontak, latitude, longitude FROM banksampah");
$bankSampahData = [];
while ($row = $result->fetch_assoc()) {
    $bankSampahData[] = $row;
}

$total_bank = count($bankSampahData); // jumlah bank sampah

// ================================
// DATA USER
// ================================
$id_user = $_SESSION['id_user'] ?? null;

$total_berat = 0;
$total_poin  = 0;
$total_setor = 0;

if ($id_user) {
    $id_user_safe = $conn->real_escape_string($id_user);

    // TOTAL SETOR (hanya diverifikasi)
    $qSetor = $conn->query("
        SELECT COUNT(*) AS total_setor
        FROM setoran
        WHERE id_user = '$id_user_safe' AND status='diverifikasi'
    ");
    $total_setor = $qSetor->fetch_assoc()['total_setor'];

    // TOTAL BERAT (hanya diverifikasi)
    $qBerat = $conn->query("
        SELECT IFNULL(SUM(berat), 0) AS total_berat
        FROM setoran
        WHERE id_user = '$id_user_safe' AND status='diverifikasi'
    ");
    $total_berat = $qBerat->fetch_assoc()['total_berat'];

    // HITUNG TOTAL POIN SESUAI KATEGORI (hanya diverifikasi)
    $poinKategori = [
        'Sampah Sisa Makanan' => 10,
        'Sampah Sisa Buah dan Sayur' => 12,
        'Sampah Hewani' => 8,
        'Sampah Pertanian' => 5,
        'Sampah Kotoran Hewan' => 4,
        'Sampah Daun dan Ranting Kering' => 6
    ];

    $total_poin = 0;
    $qSetoran = $conn->query("
        SELECT berat, kategori
        FROM setoran
        WHERE id_user = '$id_user_safe' AND status='diverifikasi'
    ");
    while ($row = $qSetoran->fetch_assoc()) {
        $total_poin += $row['berat'] * ($poinKategori[$row['kategori']] ?? 0);
    }

    // Kurangi poin yang sudah ditukar & disetujui/berhasil (sinkron dengan profil.php)
    $qTukar = $conn->query("
        SELECT SUM(r.poin_diperlukan) AS poin_tukar
        FROM req_reward rr
        LEFT JOIN reward r ON rr.id_reward = r.id_reward
        WHERE rr.id_user = '$id_user_safe' AND rr.status IN ('disetujui','berhasil')
    ");
    $poinTukar = $qTukar->fetch_assoc()['poin_tukar'] ?? 0;
    $total_poin -= $poinTukar;
    if ($total_poin < 0) $total_poin = 0;
}

// Ambil aktivitas terbaru (3 aktivitas terakhir)
$aktivitas = [];
if ($id_user) {
    $qAkt = $conn->query("
        SELECT kategori, berat, tanggal, status
        FROM setoran
        WHERE id_user = '$id_user_safe'
        ORDER BY tanggal DESC
        LIMIT 3
    ");
    while ($row = $qAkt->fetch_assoc()) {
        $aktivitas[] = $row;
    }
}

// Fungsi hitung waktu relatif
function timeAgo($time) {
    $timestamp = strtotime($time);
    $selisih = time() - $timestamp;

    if ($selisih < 60) return "Baru saja";
    if ($selisih < 3600) return floor($selisih / 60) . " menit yang lalu";
    if ($selisih < 86400) return floor($selisih / 3600) . " jam yang lalu";
    if ($selisih < 604800) return floor($selisih / 86400) . " hari yang lalu";
    return floor($selisih / 604800) . " minggu yang lalu";
}

// Fungsi hitung poin per kategori
function hitungPoin($berat, $kategori) {
    $poinKategori = [
        'Sampah Sisa Makanan' => 10,
        'Sampah Sisa Buah dan Sayur' => 12,
        'Sampah Hewani' => 8,
        'Sampah Pertanian' => 5,
        'Sampah Kotoran Hewan' => 4,
        'Sampah Daun dan Ranting Kering' => 6
    ];
    return $berat * ($poinKategori[$kategori] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Organify</title>
<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/landing.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<style>#map { height: 500px; margin-bottom: 30px; }</style>
</head>
<body>

<section class="hero">
  <h1>ORGANIFY</h1>
  <p>Langkah kecilmu mengelola sampah bisa membawa dampak besar — termasuk untuk dompetmu.</p>

  <a href="profile.php" class="user-link">
    <img src="assets/img/user.png" alt="User" class="user-logo">
  </a>
</section>

<div class="search-box">
  <div class="search-wrapper">
    <img src="assets/img/search.png" class="search-icon">
    <input type="text" id="search" placeholder="Ketik kota, misal Denpasar" />
  </div>
  <button id="btn-search">Tampilkan</button>
</div>

<div class="map-container">
  <div id="map"></div>
</div>

<a href="setor.php" class="btn-setor">
  <img src="assets/img/daur_ulang.png" class="icon">
  SETOR SAMPAH
</a>

<section class="stats">
  <h3>Statistik</h3>
  <div class="stats-container">

    <div class="stat">
      <h2 class="stat-bank"><?= htmlspecialchars((string)$total_bank) ?></h2>
      <p>Bank Sampah</p>
    </div>

    <div class="stat">
      <h2 class="stat-berat"><?= htmlspecialchars($total_berat); ?>Kg</h2>
      <p>Total Sampah (Anda)</p>
    </div>

  </div>
</section>

<section class="activity">
  <h3>Aktivitas Terbaru</h3>

  <?php if (empty($aktivitas)): ?>
    <p style="text-align:center;color:#777;">Belum ada aktivitas setoran.</p>
  <?php else: ?>
    <?php foreach ($aktivitas as $a): ?>
      <div class="activity-item">
        <div class="activity-left">
          <div class="activity-icon">
            <img src="assets/img/daur-ulang.png">
          </div>
          <div class="activity-info">
            <b><?= htmlspecialchars($a['kategori']); ?></b>
            <small><?= timeAgo($a['tanggal']); ?></small>
          </div>
        </div>
        <span class="point">+<?= hitungPoin($a['berat'], $a['kategori']); ?></span>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <a href="riwayat_setoran.php" class="lihat-semua">LIHAT SEMUA</a>
</section>

<section class="reward">
  <a href="tukar_poin.php" class="tukar-poin-btn">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
      <path d="M20 12v8H4v-8" stroke="white" stroke-width="2" stroke-linecap="round"/>
      <path d="M22 7H2v5h20V7z" stroke="white" stroke-width="2" stroke-linecap="round"/>
      <path d="M7 7V5a3 3 0 1 1 6 0v2" stroke="white" stroke-width="2" stroke-linecap="round"/>
    </svg>
    Tukar Poin
  </a>
  
  <h4>Reward Poin</h4>

  <div class="reward-content">
    <div>
      <h2><?= htmlspecialchars($total_berat); ?>Kg</h2>
      <p>Sampah Organik</p>
    </div>
    <div>
      <h2><?= number_format($total_poin, 0, ',', '.'); ?></h2>
      <p>Poin</p>
    </div>
  </div>
</section>

<section class="tutorial">
  <h2>Cara Menggunakan ORGANIFY</h2>
  <small>4 langkah sederhana untuk mulai berkontribusi</small>

  <div class="tutorial-grid">

    <div class="tutorial-card card-1">
      <div class="badge">1</div>
      <img src="assets/img/step1.jpg" class="tuto-img">
      <h3>Cari Lokasi</h3>
      <p>Temukan titik pengumpulan terdekat</p>
    </div>

    <div class="tutorial-card card-2">
      <div class="badge">2</div>
      <img src="assets/img/step2.jpg" class="tuto-img">
      <h3>Setor Sampah</h3>
      <p>Bawa sampah organik ke lokasi</p>
    </div>

    <div class="tutorial-card card-3">
      <div class="badge">3</div>
      <img src="assets/img/step3.jpg" class="tuto-img">
      <h3>Dapat Poin</h3>
      <p>Poin otomatis masuk ke akun</p>
    </div>

    <div class="tutorial-card card-4">
      <div class="badge">4</div>
      <img src="assets/img/step4.jpg" class="tuto-img">
      <h3>Tukar Hadiah</h3>
      <p>Redeem voucher & hadiah menarik</p>
    </div>

  </div>
</section>

<script>
  const bankSampahData = <?= json_encode($bankSampahData); ?>;
</script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="assets/js/leaflet-map.js"></script>

</body>
</html>
