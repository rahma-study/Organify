<?php
session_start();
session_destroy(); // Hapus semua session

// Redirect ke halaman awal aplikasi (welcome / login)
header("Location: index.php"); // ganti sesuai halaman awal kamu
exit;
?>
