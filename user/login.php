<?php
session_start();
include 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id_user, password, nama FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_user, $hashed_password, $nama);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            session_regenerate_id(true);
            $_SESSION['id_user'] = $id_user;
            $_SESSION['nama'] = $nama;
            header("Location: landing_page.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Organify</title>
  <link rel="stylesheet" href="assets/css/login.css" />
</head>

<body>
  <div class="overlay"></div>

  <div class="container">
    <!-- Bagian kiri -->
    <div class="left">
      <h1>Welcome Back!</h1>
      <p>Teruskan langkahmu menjaga bumi — login untuk lanjutkan aksi baikmu dan raih reward menarik!</p>
    </div>

    <!-- Bagian kanan -->
    <div class="right">
      <h2>Log In</h2>

      <?php if (!empty($error)) : ?>
        <p class="error"><?php echo $error; ?></p>
      <?php endif; ?>

      <form action="" method="POST">
        <input type="email" name="email" placeholder="Your Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" class="btn-primary">Log In</button>
      </form>

      <div class="divider">or</div>

      <!-- Tombol Google Login -->
      <button id="googleLogin" class="btn-google">
        <img src="assets/img/google-icon.png" alt="Google">
        Login with Google
      </button>
      <script type="module" src="assets/js/firebase-login.js"></script>


      <p class="signup-link">
        Don’t Have An Account?
        <a href="register.php">Sign Up Here</a>
      </p>

      <p class="forgot-password">
        <a href="forgot_password.php">Forgot Password?</a>
      </p>
    </div>
  </div>

  <!-- Firebase SDK -->
  <script type="module" src="https://www.gstatic.com/firebasejs/10.7.2/firebase-app.js"></script>
  <script type="module" src="https://www.gstatic.com/firebasejs/10.7.2/firebase-auth.js"></script>

  <!-- File JS eksternal -->
  <script type="module" src="assets/js/firebase-login.js"></script>
</body>
</html>
