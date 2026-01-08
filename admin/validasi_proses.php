<?php
include 'check_admin.php';
include '../config.php';

if (!isset($_GET['id']) || !isset($_GET['aksi'])) {
    showMsg("Parameter tidak lengkap!", "error");
    exit;
}

$id_setoran = $_GET['id'];
$aksi       = $_GET['aksi'];
$id_admin   = $_SESSION['admin_id'] ?? null;

if (!$id_admin) {
    showMsg("Admin belum login!", "error");
    exit;
}

// ===============================
// AMBIL DATA SETORAN
// ===============================
$q = mysqli_query($conn, "
    SELECT id_user, berat, kategori 
    FROM setoran 
    WHERE id_setoran = '$id_setoran'
");

$data = mysqli_fetch_assoc($q);
if (!$data) {
    showMsg("Setoran tidak ditemukan!", "error");
    exit;
}

$id_user  = $data['id_user'];
$berat    = $data['berat'];
$kategori = $data['kategori'];

// ===============================
// POIN PER KATEGORI
// ===============================
$poinKategori = [
    'Sampah Sisa Makanan' => 10,
    'Sampah Sisa Buah dan Sayur' => 12,
    'Sampah Hewani' => 8,
    'Sampah Pertanian' => 5,
    'Sampah Kotoran Hewan' => 4,
    'Sampah Daun dan Ranting Kering' => 6
];

$poin = $berat * ($poinKategori[$kategori] ?? 0);

// ===============================
// SETUJU
// ===============================
if ($aksi === "setuju") {

    mysqli_query($conn, "
        INSERT INTO validasi 
        (id_admin, id_setoran, tanggal_validasi, keterangan, status_validasi)
        VALUES 
        ('$id_admin', '$id_setoran', NOW(), 'Setoran diterima', 'diterima')
    ");

    mysqli_query($conn, "
        UPDATE setoran 
        SET status = 'diverifikasi'
        WHERE id_setoran = '$id_setoran'
    ");

    mysqli_query($conn, "
        UPDATE user 
        SET 
            total_setor = total_setor + 1,
            total_berat = total_berat + $berat
        WHERE id_user = '$id_user'
    ");

    showMsg("Setoran berhasil diverifikasi & poin diberikan!", "success");
    exit;
}

// ===============================
// TOLAK
// ===============================
if ($aksi === "tolak") {

    mysqli_query($conn, "
        INSERT INTO validasi 
        (id_admin, id_setoran, tanggal_validasi, keterangan, status_validasi)
        VALUES 
        ('$id_admin', '$id_setoran', NOW(), 'Setoran ditolak', 'ditolak')
    ");

    mysqli_query($conn, "
        UPDATE setoran 
        SET status = 'ditolak'
        WHERE id_setoran = '$id_setoran'
    ");

    showMsg("Setoran berhasil ditolak!", "success");
    exit;
}

// ===============================
// FUNGSI NOTIF
// ===============================
function showMsg($msg, $type) {
    echo "
    <div class='notif $type'>$msg</div>
    <script>
        setTimeout(() => {
            window.location.href = 'validasi_setoran.php';
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
