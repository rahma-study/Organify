<?php
$conn = new mysqli("localhost", "root", "", "db_banksampah");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_bank  = $_POST['nama_bank'];
    $alamat     = $_POST['alamat'];
    $kontak     = $_POST['kontak'];
    $kota       = $_POST['kota_kabupaten'];
    $status     = $_POST['status'];
    $latitude   = $_POST['latitude'];
    $longitude  = $_POST['longitude'];

    $sql = "INSERT INTO banksampah 
        (nama_bank, alamat, kota_kabupaten, kontak, latitude, longitude, status)
        VALUES ('$nama_bank', '$alamat', '$kota', '$kontak', '$latitude', '$longitude', '$status')";


    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Data bank sampah berhasil ditambahkan!');
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
  <title>Tambah Data Bank Sampah | Admin Organify</title>

  <link rel="stylesheet" href="../assets/css_admin/tambah_banksampah.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="top-header">
    <a href="kelola_banksampah.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2>Tambah Data Bank Sampah</h2>
</div>

<div class="form-wrapper">
    <div class="form-card">

        <h3 class="form-title">Data Bank Sampah</h3>
        <hr class="divider">

        <form method="POST" class="grid-form">

            <!-- Nama Bank -->
            <input type="text" name="nama_bank" placeholder="Nama Bank Sampah" required>

            <!-- Alamat -->
            <input type="text" name="alamat" placeholder="Alamat / Lokasi" required>

            <!-- Kontak -->
            <input type="text" name="kontak" placeholder="No. Telepon">

            <!-- Kota / Kabupaten -->
            <select name="kota_kabupaten" class="full-input" required>
                <option value="" disabled selected hidden>Pilih Kota / Kabupaten</option>
                <option value="Denpasar">Denpasar</option>
                <option value="Badung">Badung</option>
                <option value="Tabanan">Tabanan</option>
                <option value="Gianyar">Gianyar</option>
                <option value="Bangli">Bangli</option>
                <option value="Klungkung">Klungkung</option>
                <option value="Karangasem">Karangasem</option>
                <option value="Buleleng">Buleleng</option>
            </select>
            
            <!-- Latitude -->
            <input type="text" name="latitude" placeholder="Latitude (contoh: -8.675000)" required>

            <!-- Longitude -->
            <input type="text" name="longitude" placeholder="Longitude (contoh: 115.215000)" required>


            <!-- Status -->
            <select name="status" class="full-input" required>
                <option value="Aktif">Aktif</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>

            <!-- Tombol Submit -->
            <button type="submit" class="submit-btn">
                <i class="fa-solid fa-plus"></i> Tambah Data
            </button>

        </form>

    </div>
</div>

</body>
</html>
