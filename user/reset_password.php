<?php
session_start();
include 'config.php';

$token = $_GET['token'] ?? '';
$message = "";
$showForm = false;
$id_user = null;

/* ===============================
   CEK TOKEN
=============================== */
if ($token) {
    $stmt = $conn->prepare(
        "SELECT id_user, reset_expire FROM user WHERE reset_token=?"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_user, $reset_expire);
    $stmt->fetch();

    if ($stmt->num_rows === 0) {
        $message = "Token tidak valid.";
    } elseif (strtotime($reset_expire) < time()) {
        $message = "Token sudah kadaluarsa.";
    } else {
        $showForm = true;
    }
    $stmt->close();
} else {
    $message = "Token tidak tersedia.";
}

/* ===============================
   PROSES RESET PASSWORD
=============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($password) < 6) {
        $message = "Password minimal 6 karakter.";
    } elseif ($password !== $confirm) {
        $message = "Konfirmasi password tidak sama.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare(
            "UPDATE user 
             SET password=?, reset_token=NULL, reset_expire=NULL 
             WHERE id_user=?"
        );
        $update->bind_param("si", $hash, $id_user);
        $update->execute();
        $update->close();

        $_SESSION['success'] = "Password berhasil diubah. Silakan login.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reset Password | Organify</title>
<link rel="stylesheet" href="assets/css/reset_password.css">
</head>

<body>

<div class="overlay"></div>

<div class="container">

    <!-- LEFT -->
    <div class="left">
        <h1>Password Baru</h1>
        <p>
            Silakan buat password baru untuk akun Organify Anda.
            Pastikan password aman dan mudah diingat.
        </p>
    </div>

    <!-- RIGHT -->
    <div class="right">
        <h2>Reset Password</h2>

        <?php if ($showForm): ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Password baru" required>
                <input type="password" name="confirm" placeholder="Konfirmasi password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="message error">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="forgot-password">
            <a href="login.php">Kembali ke Login</a>
        </div>
    </div>

</div>

</body>
</html>
