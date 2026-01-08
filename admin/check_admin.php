<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login_admin.php?error=not_logged_in");
    exit();
}

include '../config.php';

$id_admin = $_SESSION['admin_id'];
mysqli_query($conn, "
    UPDATE admin 
    SET last_active = NOW() 
    WHERE id_admin = '$id_admin'
");

?>
