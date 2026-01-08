<?php
session_start();
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["nama"]) || !isset($data["email"])) {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "db_banksampah";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Koneksi gagal"]);
    exit;
}

$email = $data["email"];
$nama = $data["nama"];

// Cek user di DB
$sql_check = "SELECT id_user FROM user WHERE email = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$stmt_check->store_result();
$stmt_check->bind_result($id_user);
$stmt_check->fetch();

if ($stmt_check->num_rows > 0) {
    // User sudah ada → set session untuk login
    $_SESSION['id_user'] = $id_user;
    $_SESSION['nama'] = $nama;
    echo json_encode(["success" => true, "message" => "User sudah ada"]);
} else {
    // User baru → insert ke DB
    $sql_insert = "INSERT INTO user (nama, email, password, alamat, no_hp) VALUES (?, ?, '', '', '')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ss", $nama, $email);

    if ($stmt_insert->execute()) {
        $_SESSION['id_user'] = $stmt_insert->insert_id;
        $_SESSION['nama'] = $nama;
        echo json_encode(["success" => true, "message" => "User baru ditambahkan"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menyimpan user"]);
    }

    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
?>
