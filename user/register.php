<?php
session_start(); // wajib di paling atas
include 'config.php'; // koneksi database

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua kolom wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Cek email unik
        $stmt_check = $conn->prepare("SELECT id_user FROM user WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            // Hash password & insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (nama, email, password, alamat, no_hp) VALUES (?, ?, ?, '', '')");
            $stmt->bind_param("sss", $nama, $email, $hashed_password);

            if ($stmt->execute()) {
                // Auto-login setelah register
                session_regenerate_id(true);
                $_SESSION['id_user'] = $conn->insert_id;
                $_SESSION['nama'] = $nama;

                header("Location: landing_page.php");
                exit();
            } else {
                $error = "Terjadi kesalahan: " . $stmt->error;
            }

            $stmt->close();
        }

        $stmt_check->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Organify</title>
  <link rel="stylesheet" href="assets/css/register.css" />
</head>
<body>
  <div class="overlay"></div>

  <div class="container">
    <div class="left">
      <h1>Let’s Get Started</h1>
      <p>Mulai langkah kecilmu bersama kami untuk jaga bumi — jadi pahlawan lingkungan sambil cuan!</p>
    </div>

    <div class="right">
      <h2>Sign Up</h2>

      <?php if (!empty($error)) : ?>
        <p class="error"><?php echo $error; ?></p>
      <?php endif; ?>

      <form action="" method="POST">
        <input type="text" name="name" placeholder="Your Name" required />
        <input type="email" name="email" placeholder="Your Email" required />
        <input type="password" name="password" placeholder="Create Password" required />
        <input type="password" name="confirm_password" placeholder="Repeat Your Password" required />
        <button type="submit" class="btn-primary">Sign Up</button>
      </form>

      <div class="divider">or</div>

      <button id="googleSignUp" class="btn-google">
        <img src="assets/img/google-icon.png" alt="Google">
        Continue with Google
      </button>

      <p class="login-link">
        Already Have An Account? 
        <a href="login.php">Login Here</a>
      </p>
    </div>
  </div>

  <!-- Firebase SDK (jika mau pakai Google Login) -->
  <script type="module" src="https://www.gstatic.com/firebasejs/10.7.2/firebase-app.js"></script>
  <script type="module" src="https://www.gstatic.com/firebasejs/10.7.2/firebase-auth.js"></script>
  <script type="module" src="assets/js/firebase-login.js"></script>
</body>
</html>
