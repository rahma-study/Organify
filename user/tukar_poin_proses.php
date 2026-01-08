<?php
session_start();
$conn = new mysqli("localhost", "root", "", "db_banksampah");

if ($conn->connect_error) {
    echo "db_error";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['id_user'])) {
        echo "not_login";
        exit;
    }

    $id_user   = $_SESSION['id_user'];
    $id_reward = $_POST['id_reward'] ?? 0;
    $poin      = $_POST['poin'] ?? 0;
    $nomor     = $_POST['nomor'] ?? '';

    if (!$id_reward || !$poin) {
        echo "invalid";
        exit;
    }

    /* =========================
       HITUNG TOTAL POIN USER
       ========================= */
    function hitungTotalPoin($conn, $id_user) {
        $poinKategori = [
            'Sampah Sisa Makanan' => 10,
            'Sampah Sisa Buah dan Sayur' => 12,
            'Sampah Hewani' => 8,
            'Sampah Pertanian' => 5,
            'Sampah Kotoran Hewan' => 4,
            'Sampah Daun dan Ranting Kering' => 6
        ];

        $total = 0;

        // poin dari setoran tervalidasi
        $qSetor = $conn->query("
            SELECT berat, kategori 
            FROM setoran 
            WHERE id_user = '$id_user' AND status = 'diverifikasi'
        ");
        while ($s = $qSetor->fetch_assoc()) {
            $total += (int)$s['berat'] * ($poinKategori[$s['kategori']] ?? 0);
        }

        // poin yang sudah dipakai (reward berhasil)
        $qTukar = $conn->query("
            SELECT IFNULL(SUM(r.poin_diperlukan),0) AS total
            FROM req_reward rr
            JOIN reward r ON rr.id_reward = r.id_reward
            WHERE rr.id_user = '$id_user'
            AND rr.status = 'berhasil'
        ");
        $dipakai = (int)$qTukar->fetch_assoc()['total'];

        return max(0, $total - $dipakai);
    }

    $total_poin = hitungTotalPoin($conn, $id_user);

    // CEK CUKUP ATAU TIDAK
    if ($total_poin < $poin) {
        echo "not_enough";
        exit;
    }

    /* =========================
       SIMPAN REQUEST REWARD
       ========================= */
    $stmt = $conn->prepare("
        INSERT INTO req_reward 
        (id_user, id_reward, poin_digunakan, nomor, status, tanggal)
        VALUES (?, ?, ?, ?, 'menunggu', NOW())
    ");
    $stmt->bind_param("iiis", $id_user, $id_reward, $poin, $nomor);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    exit;
}
?>
