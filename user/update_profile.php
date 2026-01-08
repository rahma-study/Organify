<?php
session_start();
include 'config.php';

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    header("Location: login.php");
    exit;
}

// Ambil & rapikan input
$nama           = trim($_POST['nama'] ?? '');
$email          = trim($_POST['email'] ?? '');
$no_hp          = trim($_POST['no_hp'] ?? '');
$tanggal_lahir  = trim($_POST['tanggal_lahir'] ?? '');
$alamat         = trim($_POST['alamat'] ?? '');

// Validasi wajib isi
if (
    $nama === '' ||
    $email === '' ||
    $no_hp === '' ||
    $tanggal_lahir === '' ||
    $alamat === ''
) {
    $_SESSION['flash_error'] = "Semua data wajib diisi.";
    header("Location: profile.php");
    exit;
}

// Update data
$stmt = $conn->prepare("
    UPDATE user 
    SET nama = ?, email = ?, no_hp = ?, tanggal_lahir = ?, alamat = ?
    WHERE id_user = ?
");

$stmt->bind_param(
    "sssssi",
    $nama,
    $email,
    $no_hp,
    $tanggal_lahir,
    $alamat,
    $id_user
);

if ($stmt->execute()) {
    $_SESSION['flash_success'] = "Profil berhasil diperbarui.";
} else {
    $_SESSION['flash_error'] = "Gagal memperbarui profil.";
}

$stmt->close();
$conn->close();

header("Location: profile.php");
exit;
