<?php
session_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id_user, password FROM user WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_user, $password);
    $stmt->fetch();

    if ($stmt->num_rows === 0) {
        $message = "Email tidak terdaftar.";
    } elseif (empty($password)) {
        $message = "Akun ini menggunakan login Google.";
    } else {

        $token  = bin2hex(random_bytes(32));
        $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update = $conn->prepare(
            "UPDATE user SET reset_token=?, reset_expire=? WHERE id_user=?"
        );
        $update->bind_param("ssi", $token, $expire, $id_user);
        $update->execute();
        $update->close();

        $mail = new PHPMailer(true);
        try {
            // SMTP BREVO
            $mail->isSMTP();
            $mail->Host       = 'smtp-relay.brevo.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '9e01fb001@smtp-brevo.com';
            $mail->Password   = 'kdzqQ8S1TpfNaW5E';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('rahmzia9@gmail.com', 'Organify');
            $mail->addAddress($email);

            $resetLink = "http://localhost/organify/reset_password.php?token=$token";

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Organify';
            $mail->Body = "
                <h3>Reset Password</h3>
                <p>Klik link berikut:</p>
                <a href='$resetLink'>$resetLink</a>
            ";

            $mail->send();
            $message = "Link reset password berhasil dikirim.";

        } catch (Exception $e) {
            $message = "Gagal kirim email.";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lupa Password | Organify</title>
<link rel="stylesheet" href="assets/css/forgot_password.css" />
</head>

<body>

<div class="overlay"></div>

<div class="container">

    <!-- LEFT PANEL -->
    <div class="left">
        <h1>Lupa Password?</h1>
        <p>
            Masukkan email yang terdaftar pada akun Organify.
            Kami akan mengirimkan tautan untuk mengatur ulang password Anda.
        </p>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right">
        <h2>Reset Password</h2>

        <form method="POST">
            <input type="email" name="email" placeholder="Email terdaftar" required>
            <button type="submit">Kirim Link Reset</button>
        </form>

        <?php if ($message): ?>
            <div class="message <?= strpos($message,'berhasil') !== false ? 'success' : 'error' ?>">
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
