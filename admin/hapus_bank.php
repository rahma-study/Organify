<?php
// Koneksi
$conn = new mysqli("localhost", "root", "", "db_banksampah");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Pastikan ID dikirim
if (!isset($_GET['id'])) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='kelola_banksampah.php';</script>";
    exit;
}

$id = intval($_GET['id']); // untuk keamanan

// Query hapus
$sql = "DELETE FROM banksampah WHERE id_bank = $id";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Data bank sampah berhasil dihapus!'); window.location='kelola_banksampah.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data!'); window.location='kelola_banksampah.php';</script>";
}

$conn->close();
?>
