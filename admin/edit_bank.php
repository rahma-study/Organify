<?php
$conn = new mysqli("localhost", "root", "", "db_banksampah");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID bank dari URL
$id = $_GET['id'];

// Ambil data bank berdasarkan ID
$sql = "SELECT * FROM banksampah WHERE id_bank = $id";
$result = $conn->query($sql);
$bank = $result->fetch_assoc();

// Jika form disubmit → UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_bank  = $_POST['nama_bank'];
    $alamat     = $_POST['alamat'];
    $kontak     = $_POST['kontak'];
    $kota       = $_POST['kota_kabupaten'];
    $status     = $_POST['status'];
    $latitude   = $_POST['latitude'];
    $longitude  = $_POST['longitude'];

    $update = "UPDATE banksampah SET 
                nama_bank = '$nama_bank',
                alamat = '$alamat',
                kontak = '$kontak',
                kota_kabupaten = '$kota',
                latitude = '$latitude',
                longitude = '$longitude',
                status = '$status'
               WHERE id_bank = $id";

    if ($conn->query($update) === TRUE) {
        echo "<script>
                alert('Data berhasil diperbarui!');
                window.location.href = 'kelola_banksampah.php';
              </script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Data Bank Sampah | Admin Organify</title>

  <link rel="stylesheet" href="../assets/css_admin/edit_banksampah.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="top-header">
    <a href="kelola_banksampah.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2>Edit Data Bank Sampah</h2>
</div>

<div class="form-wrapper">
    <div class="form-card">

        <h3 class="form-title">Edit Data Bank Sampah</h3>
        <hr class="divider">

        <form method="POST" class="grid-form">

            <!-- Nama Bank -->
            <input type="text" name="nama_bank" value="<?php echo $bank['nama_bank']; ?>" required>

            <!-- Alamat -->
            <input type="text" name="alamat" value="<?php echo $bank['alamat']; ?>" required>

            <!-- Kontak -->
            <input type="text" name="kontak" value="<?php echo $bank['kontak']; ?>">

            <!-- Kota / Kabupaten -->
            <select name="kota_kabupaten" class="full-input" required>
                <option value="Denpasar"  <?php if($bank['kota_kabupaten']=='Denpasar') echo 'selected'; ?>>Denpasar</option>
                <option value="Badung"    <?php if($bank['kota_kabupaten']=='Badung') echo 'selected'; ?>>Badung</option>
                <option value="Tabanan"   <?php if($bank['kota_kabupaten']=='Tabanan') echo 'selected'; ?>>Tabanan</option>
                <option value="Gianyar"   <?php if($bank['kota_kabupaten']=='Gianyar') echo 'selected'; ?>>Gianyar</option>
                <option value="Bangli"    <?php if($bank['kota_kabupaten']=='Bangli') echo 'selected'; ?>>Bangli</option>
                <option value="Klungkung" <?php if($bank['kota_kabupaten']=='Klungkung') echo 'selected'; ?>>Klungkung</option>
                <option value="Karangasem"<?php if($bank['kota_kabupaten']=='Karangasem') echo 'selected'; ?>>Karangasem</option>
                <option value="Buleleng"  <?php if($bank['kota_kabupaten']=='Buleleng') echo 'selected'; ?>>Buleleng</option>
            </select>

            <!-- Latitude -->
            <input type="text" name="latitude" value="<?php echo $bank['latitude']; ?>" required>

            <!-- Longitude -->
            <input type="text" name="longitude" value="<?php echo $bank['longitude']; ?>" required>

            <!-- Status -->
            <select name="status" class="full-input" required>
                <option value="Aktif" <?php if($bank['status']=='Aktif') echo 'selected'; ?>>Aktif</option>
                <option value="Nonaktif" <?php if($bank['status']=='Nonaktif') echo 'selected'; ?>>Nonaktif</option>
            </select>

            <!-- Tombol Submit -->
            <button type="submit" class="submit-btn">
                <i class="fa-solid fa-save"></i> Simpan Perubahan
            </button>

        </form>

    </div>
</div>

</body>
</html>
