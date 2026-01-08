<?php
include 'check_admin.php';
include '../config.php';

// Pastikan ID reward dikirim
if (!isset($_GET['id'])) {
    echo "<script>alert('ID reward tidak ditemukan!'); window.location='kelola_reward.php';</script>";
    exit;
}

$id = intval($_GET['id']); // keamanan

// Ambil data reward dulu untuk mengetahui nama file gambar
$q = mysqli_query($conn, "SELECT * FROM reward WHERE id_reward='$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<script>alert('Reward tidak ditemukan!'); window.location='kelola_reward.php';</script>";
    exit;
}

// Hapus file gambar jika ada
if (!empty($data['gambar']) && file_exists("../uploads/reward/" . $data['gambar'])) {
    unlink("../uploads/reward/" . $data['gambar']);
}

// Hapus data reward dari database
$sql = "DELETE FROM reward WHERE id_reward='$id'";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Reward berhasil dihapus!'); window.location='kelola_reward.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus reward!'); window.location='kelola_reward.php';</script>";
}
?>
