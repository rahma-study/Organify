<?php
session_start();
include '../config.php'; // path benar karena login.php ada di folder admin

$error = "";

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Ambil admin berdasarkan email
    $stmt = $conn->prepare("SELECT id_admin, password, nama FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_admin, $hashed_password, $nama);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        // Cek password
        if (password_verify($password, $hashed_password)) {

            session_regenerate_id(true);

            // Simpan session admin
            $_SESSION['admin_id'] = $id_admin;
            $_SESSION['admin_nama'] = $nama;
            $_SESSION['role'] = "admin";

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email admin tidak ditemukan!";
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
  <title>Admin Login - Organify</title>
  <link rel="stylesheet" href="../assets/css_admin/login_admin.css">
</head>

<body>
  <div class="overlay"></div>

  <div class="container admin">

    <div class="left">
      <h1>Admin Panel Login</h1>
      <p>Akses halaman pengelolaan sistem Organify.</p>
    </div>

    <div class="right">
      <h2>Admin Log In</h2>

      <?php if (!empty($error)) : ?>
        <p class="error"><?php echo $error; ?></p>
      <?php endif; ?>

      <form action="" method="POST">
        <input type="email" name="email" placeholder="Admin Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" class="btn-primary">Log In</button>
      </form>

      <div class="divider"></div>

      <p class="signup-link" style="opacity: 0.6; cursor: not-allowed; margin-top: 20px;">
        Admin account created manually.
      </p>
    </div>
  </div>

</body>
</html>
