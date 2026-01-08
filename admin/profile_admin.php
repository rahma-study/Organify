<?php
session_start();
include 'check_admin.php';
include '../config.php';

// Ambil ID admin dari session
$id_admin = $_SESSION['admin_id'] ?? null;

if (!$id_admin) {
    header("Location: login_admin.php");
    exit();
}

// Ambil data admin
$q = mysqli_query($conn, "SELECT * FROM admin WHERE id_admin = '$id_admin'");
$admin = mysqli_fetch_assoc($q);

if (!$admin) {
    die("Data admin tidak ditemukan.");
}

// Folder upload
$upload_dir = "../uploads/admin/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Jika tombol simpan ditekan
if (isset($_POST['simpan'])) {

    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // FOTO PROFIL
    $nama_file_baru = $admin['foto'] ?? "default.png";

    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $nama_file_baru = "admin_" . $id_admin . "." . $ext;
        $lokasi = $upload_dir . $nama_file_baru;

        move_uploaded_file($_FILES['foto']['tmp_name'], $lokasi);
    }

    // PASSWORD OPSIONAL
    $password_sql = "";
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $password_sql = ", password = '$password_hash'";
    }

    // UPDATE DATA
    $update = "
        UPDATE admin SET
            nama = '$nama',
            email = '$email',
            foto = '$nama_file_baru'
            $password_sql
        WHERE id_admin = '$id_admin'
    ";

    mysqli_query($conn, $update);

    // Simpan alert ke session (supaya alert muncul tanpa double history)
    $_SESSION['alert_sukses'] = "Profil berhasil diperbarui!";

    // Redirect aman tanpa tambah riwayat
    header("Location: profile_admin.php");
    exit();
}

// Foto tampil
$foto = (!empty($admin['foto']) && file_exists($upload_dir . $admin['foto']))
        ? $admin['foto']
        : "default.png";
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin</title>
    <link rel="stylesheet" href="../assets/css_admin/profile_admin.css">
</head>

<body>

<?php  
// Tampilkan alert jika ada
if (isset($_SESSION['alert_sukses'])) {
    echo "<script>alert('" . $_SESSION['alert_sukses'] . "');</script>";
    unset($_SESSION['alert_sukses']);
}
?>

<!-- HEADER -->
<div class="header">
    <a href="<?= $_SESSION['last_page'] ?? 'dashboard.php' ?>" class="back-btn">
        <img src="../assets/img/back button.png" alt="Back">
    </a>

    <div class="header-title">Profil Admin</div>

    <a href="logout_admin.php" class="logout-btn">
        <img src="../assets/img/logout button.png" alt="Logout">
    </a>
</div>

<div class="wrapper">

    <div class="title-section">
        <img src="../assets/img/logo hitam.png" style="height: 60px">
        <h1>Hallo, <?= htmlspecialchars($admin['nama']) ?>!</h1>
    </div>

    <form method="POST" enctype="multipart/form-data">

        <div class="card">

            <button class="save-btn" name="simpan">Simpan</button>

            <div class="card-content">

                <!-- LEFT PROFILE -->
                <div class="left-profile">
                    <div class="photo" style="background-image: url('../uploads/admin/<?= $foto ?>');"></div>

                    <h2><?= htmlspecialchars($admin['nama']) ?></h2>
                    <p>Administrator</p>
                    <div class="status">Aktif</div>
                </div>

                <!-- RIGHT -->
                <div class="form-section">

                    <div class="form-row">
                        <label>ID Admin</label>
                        <input type="text" value="<?= $admin['id_admin'] ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label>Nama</label>
                        <input type="text" name="nama" value="<?= $admin['nama'] ?>" required>
                    </div>

                    <div class="form-row">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= $admin['email'] ?>" required>
                    </div>

                    <div class="form-row">
                        <label>Password (opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ganti">
                    </div>

                    <div class="form-row">
                        <label>Foto Profil</label>
                        <input type="file" name="foto" accept="image/*">
                    </div>

                </div>

            </div>

        </div>
    </form>

</div>

</body>
</html>
