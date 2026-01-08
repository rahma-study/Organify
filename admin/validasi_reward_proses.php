<?php
include 'check_admin.php';
include '../config.php';

$id_req = $_GET['id'] ?? 0;
$aksi   = $_GET['aksi'] ?? '';

if (!$id_req) {
    showMsg("ID tidak valid!", "error");
    exit;
}

// =======================
// AMBIL DATA REQUEST
// =======================
$q = mysqli_query($conn, "
    SELECT rr.id_req, rr.id_user, rr.id_reward,
           r.stok, r.poin_diperlukan
    FROM req_reward rr
    JOIN reward r ON rr.id_reward = r.id_reward
    WHERE rr.id_req = '$id_req'
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
    showMsg("Data tidak ditemukan!", "error");
    exit;
}

$id_user   = $data['id_user'];
$id_reward = $data['id_reward'];
$poin      = $data['poin_diperlukan'];
$stok      = $data['stok'];

// =======================
// SETUJUI REWARD
// =======================
if ($aksi === "setuju") {

    if ($stok <= 0) {
        showMsg("Stok reward habis!", "error");
        exit;
    }

    // Ambil total poin user dari histori transaksi
    $qUser = mysqli_query($conn, "
        SELECT SUM(poin_diterima - poin_digunakan) AS total_poin
        FROM transaksipoin
        WHERE id_user = '$id_user'
    ");
    $userData = mysqli_fetch_assoc($qUser);
    $totalPoin = $userData['total_poin'] ?? 0;

    if ($totalPoin < $poin) {
        showMsg("Poin user tidak cukup untuk menukar reward ini!", "error");
        exit;
    }

    // 1. Update status request menjadi berhasil
    mysqli_query($conn, "
        UPDATE req_reward 
        SET status = 'berhasil'
        WHERE id_req = '$id_req'
    ");

    // 2. Kurangi stok reward + tambah ditukar
    mysqli_query($conn, "
        UPDATE reward 
        SET stok = stok - 1,
            ditukar = ditukar + 1
        WHERE id_reward = '$id_reward'
    ");

    // 3. Buat nomor struk
    $nomor_struk = date('Ymd') . str_pad($id_req, 4, '0', STR_PAD_LEFT);
    mysqli_query($conn, "
        INSERT INTO struk_reward (id_req, nomor_struk)
        VALUES ('$id_req', '$nomor_struk')
    ");

    // 4. Catat histori transaksi poin (poin digunakan dikurangi)
    mysqli_query($conn, "
        INSERT INTO transaksipoin 
        (id_user, id_reward, tanggal, poin_digunakan, poin_diterima)
        VALUES ('$id_user', '$id_reward', NOW(), '$poin', 0)
    ");

    // **Tidak perlu update kolom total_poin di tabel user lagi**

    showMsg("Reward berhasil disetujui & poin user dikurangi!", "success");
    exit;
}

// =======================
// TOLAK REWARD
// =======================
if ($aksi === "tolak") {
    mysqli_query($conn, "
        UPDATE req_reward 
        SET status = 'ditolak'
        WHERE id_req = '$id_req'
    ");

    showMsg("Reward berhasil ditolak!", "success");
    exit;
}

showMsg("Aksi tidak dikenal!", "error");

// ==========================
// FUNGSI NOTIFIKASI
// ==========================
function showMsg($msg, $type) {
    echo "
    <div class='notif $type'>$msg</div>
    <script>
        setTimeout(() => {
            window.location.href = 'validasi_reward.php';
        }, 1500);
    </script>
    " . notifCSS();
}

function notifCSS() {
    return "
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .notif {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 14px 18px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
            animation: fadeIn .3s ease-out;
            z-index: 99999;
        }
        .success { background: #28a745; }
        .error   { background: #dc3545; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
    ";
}
?>
