<?php
session_start();
date_default_timezone_set('Asia/Makassar');
include 'config.php'; // pastikan ini koneksi $conn

// CEK USER LOGIN
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_user = $_SESSION['id_user'];
    $kategori = $_POST['kategori'];
    $berat = floatval($_POST['berat']);
    $bank = $_POST['bank'];
    $status = "menunggu";
    $tanggal = date("Y-m-d H:i:s");

    // =========================
    // UPLOAD FOTO AMAN
    // =========================
    $foto_bukti = "";
    if (!empty($_FILES["foto"]["name"])) {
        $folder = "uploads/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        $namaBaru = "setor_" . time() . "_" . rand(100, 999) . "." . $ext;

        move_uploaded_file($_FILES["foto"]["tmp_name"], $folder . $namaBaru);
        $foto_bukti = $namaBaru;
    }

    // =========================
    // AMBIL ID BANK
    // =========================
    $stmt = $conn->prepare("SELECT id_bank FROM banksampah WHERE CONCAT(nama_bank,' - ',alamat)=?");
    $stmt->bind_param("s", $bank);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $id_bank = $res['id_bank'] ?? null;
    $stmt->close();

    // =========================
    // RUMUS POIN
    // =========================
    $poinKategori = [
        "Sampah Sisa Makanan" => 10,
        "Sampah Sisa Buah dan Sayur" => 12,
        "Sampah Hewani" => 8,
        "Sampah Pertanian" => 5,
        "Sampah Kotoran Hewan" => 4,
        "Sampah Daun dan Ranting Kering" => 6,
    ];

    $poin_per_kg = $poinKategori[$kategori] ?? 10;
    $poin_didapat = $berat * $poin_per_kg;

    // =========================
    // CEK DUPLIKAT (submit ganda)
    // =========================
    $cek = $conn->prepare("SELECT id_setoran FROM setoran WHERE id_user=? AND tanggal=? AND kategori=?");
    $cek->bind_param("iss", $id_user, $tanggal, $kategori);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows == 0 && $id_bank) {
        // Simpan setoran
        $stmt = $conn->prepare("
            INSERT INTO setoran (id_user, id_bank, kategori, berat, foto_bukti, status, tanggal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisdsss", $id_user, $id_bank, $kategori, $berat, $foto_bukti, $status, $tanggal);
        $stmt->execute();
        $stmt->close();

        // Simpan riwayat poin
        $stmt2 = $conn->prepare("
            INSERT INTO transaksipoin (id_user, id_reward, tanggal, poin_digunakan, poin_diterima)
            VALUES (?, NULL, ?, 0, ?)
        ");
        $stmt2->bind_param("isd", $id_user, $tanggal, $poin_didapat);
        $stmt2->execute();
        $stmt2->close();

        echo "<script>alert('Setoran berhasil!'); window.location='riwayat_setoran.php';</script>";
        exit;
    }

    $cek->close();
    echo "<script>alert('Gagal menyimpan!'); history.back();</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Setor Sampah Organik</title>
<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300;400;500;600&family=Mingzat&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="assets/css/setor.css" />
</head>
<body>
<header class="hero">
    <button class="back-btn" onclick="window.location.href='landing_page.php'">
        <img src="assets/img/back button.png" alt="Back" />
    </button>
    <div class="hero-text">
        <h1>Setor Sampah Organik</h1>
        <p>Setorkan sampahmu sekarang dan raih hadiahnya.</p>
    </div>
</header>

<main class="container">
  <form action="" method="POST" enctype="multipart/form-data" onsubmit="this.submitBtn.disabled=true;">
    <h2>Pilih jenis sampah organik</h2>
    <div class="waste-grid">
      <!-- kartu sampah -->
      <div class="waste-card red" onclick="selectWaste(this, 'Sampah Sisa Makanan')">
        <img src="assets/img/sampah1.jpg" alt="Sisa Makanan" /><p>Sampah Sisa Makanan</p>
      </div>
      <div class="waste-card green" onclick="selectWaste(this, 'Sampah Sisa Buah dan Sayur')">
        <img src="assets/img/sampah2.jpg" alt="Buah & Sayur" /><p>Sampah Sisa Buah dan Sayur</p>
      </div>
      <div class="waste-card yellow" onclick="selectWaste(this, 'Sampah Hewani')">
        <img src="assets/img/sampah3.jpg" alt="Hewani" /><p>Sampah Hewani</p>
      </div>
      <div class="waste-card pink" onclick="selectWaste(this, 'Sampah Pertanian')">
        <img src="assets/img/sampah4.jpg" alt="Pertanian" /><p>Sampah Pertanian</p>
      </div>
      <div class="waste-card cyan" onclick="selectWaste(this, 'Sampah Kotoran Hewan')">
        <img src="assets/img/sampah5.jpg" alt="Kotoran Hewan" /><p>Sampah Kotoran Hewan</p>
      </div>
      <div class="waste-card purple" onclick="selectWaste(this, 'Sampah Daun dan Ranting Kering')">
        <img src="assets/img/sampah6.jpg" alt="Daun & Ranting" /><p>Sampah Daun dan Ranting Kering</p>
      </div>
    </div>

    <input type="hidden" id="kategori" name="kategori" />
    <p id="selectedCategory" class="selected-info"></p>

    <label for="berat">Berat Sampah (kg)</label>
    <input type="number" id="berat" name="berat" placeholder="0.0" min="0" step="0.1" required />

    <label for="bank">Pilih bank sampah terdekat</label>
    <input list="banks" id="bank" name="bank" placeholder="Ketik nama bank atau kota..." required />
    <datalist id="banks">
      <?php
      $result = $conn->query("SELECT nama_bank, alamat FROM banksampah");
      while ($row = $result->fetch_assoc()) {
          echo '<option value="'.$row['nama_bank'].' - '.$row['alamat'].'">';
      }
      ?>
    </datalist>

    <label for="foto">Upload Foto</label>
    <div class="upload-box">
      <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png" required />
      <div class="upload-placeholder">
        <div class="preview-area" id="preview-area">
          <div class="circle">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="#555" viewBox="0 0 24 24">
              <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm8-3h-2.586l-1.707-1.707A1 1 0 0 0 15 4h-6a1 1 0 0 0-.707.293L6.586 6H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2Zm0 12H4V8h3.586l1.707-1.707L10 6h4l0.707.293L16.414 8H20v10Z"/>
            </svg>
          </div>
        </div>
        <p id="uploadText">Klik untuk upload foto sampah</p>
        <small>Format: JPG, JPEG, PNG (Max. 5MB)</small>
      </div>
    </div>

    <button type="submit" class="submit-btn" name="submitBtn">SETOR</button>
  </form>
</main>

<script src="assets/js/setor.js"></script>
</body>
</html>
