<?php
include 'check_admin.php';
include '../config.php';

// ================================
// 1. Ambil ID Reward
// ================================
if (!isset($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan!');window.location='kelola_reward.php';</script>";
    exit;
}

$id = $_GET['id'];

// ================================
// 2. Ambil data reward lama
// ================================
$q = mysqli_query($conn, "SELECT * FROM reward WHERE id_reward='$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<script>alert('Reward tidak ditemukan!');window.location='kelola_reward.php';</script>";
    exit;
}

// ================================
// 3. Jika form disubmit
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = $_POST['nama_reward'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $poin = $_POST['poin_diperlukan'];
    $stok = $_POST['stok'];
    $status = $_POST['status'];

    $gambar = $data['gambar']; // default gambar lama

    // ================================
    // Upload gambar baru (jika ada)
    // ================================
    if (!empty($_FILES['gambar']['name'])) {

        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $rename = "reward_" . time() . "." . $ext;
        $tujuan = "../uploads/reward/" . $rename;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
            $gambar = $rename;

            // hapus gambar lama jika ada
            if (!empty($data['gambar']) && file_exists("../uploads/reward/" . $data['gambar'])) {
                unlink("../uploads/reward/" . $data['gambar']);
            }
        }
    }

    // ================================
    // UPDATE DATABASE
    // ================================
    $sql = "UPDATE reward SET
            nama_reward='$nama',
            kategori='$kategori',
            deskripsi='$deskripsi',
            poin_diperlukan='$poin',
            stok='$stok',
            status='$status',
            gambar='$gambar'
            WHERE id_reward='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Reward berhasil diperbarui!');window.location='kelola_reward.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui reward');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Reward | Organify</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css_admin/tambah_reward.css">
</head>

<body>

<div class="top-header">
    <a href="kelola_reward.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2>Edit Reward</h2>
</div>

<section class="form-container">

  <form action="" method="POST" enctype="multipart/form-data">

    <label>Nama Reward</label>
    <input type="text" name="nama_reward" value="<?= $data['nama_reward'] ?>" required>

    <label>Kategori</label>
    <select name="kategori" required>
      <option value="E-Money" <?= $data['kategori']=='E-Money'?'selected':'' ?>>E-Money</option>
      <option value="Voucher" <?= $data['kategori']=='Voucher'?'selected':'' ?>>Voucher</option>
      <option value="Green Impact" <?= $data['kategori']=='Green Impact'?'selected':'' ?>>Green Impact</option>
    </select>

    <label>Deskripsi</label>
    <textarea name="deskripsi" rows="4" required><?= $data['deskripsi'] ?></textarea>

    <label>Poin Diperlukan</label>
    <input type="number" name="poin_diperlukan" value="<?= $data['poin_diperlukan'] ?>" required>

    <label>Stok</label>
    <input type="number" name="stok" value="<?= $data['stok'] ?>" required>

    <label>Status</label>
    <select name="status">
      <option value="Aktif" <?= $data['status']=='Aktif'?'selected':'' ?>>Aktif</option>
      <option value="Nonaktif" <?= $data['status']=='Nonaktif'?'selected':'' ?>>Nonaktif</option>
    </select>

    <label>Gambar Reward (opsional)</label>
    <input type="file" name="gambar" accept="image/*">

    <!-- Preview gambar lama -->
    <p style="margin-top:8px; font-size:14px;">Gambar saat ini:</p>
    <img src="../uploads/reward/<?= $data['gambar'] ?>" 
         style="width:110px; border-radius:8px; margin-bottom:15px;">

    <button type="submit" class="submit-btn">Perbarui Reward</button>

  </form>

</section>

</body>
</html>
