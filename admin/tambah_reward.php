<?php
include 'check_admin.php';
include '../config.php';

// =======================
// Jika form disubmit
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = $_POST['nama_reward'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $poin = $_POST['poin_diperlukan'];
    $stok = $_POST['stok'];
    $status = $_POST['status'];

    // Ditukar default 0
    $ditukar = 0;

    // =======================
    // UPLOAD GAMBAR
    // =======================
    $gambar = "";
    if (!empty($_FILES['gambar']['name'])) {

        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $rename = "reward_" . time() . "." . $ext;

        $tujuan = "../uploads/reward/" . $rename;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
            $gambar = $rename;
        }
    }

    // =======================
    // INSERT KE DATABASE
    // =======================
    $sql = "INSERT INTO reward 
            (nama_reward, kategori, deskripsi, poin_diperlukan, stok, ditukar, status, gambar)
            VALUES ('$nama', '$kategori', '$deskripsi', '$poin', '$stok', '$ditukar', '$status', '$gambar')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Reward berhasil ditambahkan!');window.location='kelola_reward.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah reward');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Reward | Organify</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css_admin/tambah_reward.css">
</head>

<body>

<div class="top-header">
    <a href="kelola_reward.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2>Tambah Reward</h2>
</div>

<section class="form-container">

  <form action="" method="POST" enctype="multipart/form-data">

    <label>Nama Reward</label>
    <input type="text" name="nama_reward" required>

    <label>Kategori</label>
    <select name="kategori" required>
      <option value="E-Money">E-Money</option>
      <option value="Voucher">Voucher</option>
      <option value="Green Impact">Green Impact</option>
    </select>

    <label>Deskripsi</label>
    <textarea name="deskripsi" rows="4" required></textarea>

    <label>Poin Diperlukan</label>
    <input type="number" name="poin_diperlukan" required>

    <label>Stok</label>
    <input type="number" name="stok" required>

    <label>Status</label>
    <select name="status">
      <option value="Aktif">Aktif</option>
      <option value="Nonaktif">Nonaktif</option>
    </select>

    <label>Gambar Reward</label>
    <input type="file" name="gambar" accept="image/*" required>

    <button type="submit" class="submit-btn">Simpan Reward</button>

  </form>

</section>

</body>
</html>
