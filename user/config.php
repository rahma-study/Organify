<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_banksampah";

$conn = mysqli_connect($host, $user, $pass, $db);

mysqli_query($conn, "SET time_zone = '+08:00'");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>