<?php
session_start();
date_default_timezone_set('Asia/Makassar');
if (!isset($_SESSION['admin_id'])) { http_response_code(403); exit('Login required'); }

$conn = new mysqli("localhost","root","","db_banksampah");
if ($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

$tipe = $_GET['tipe'] ?? 'excel';
$periode = $_GET['periode'] ?? 'semua';

$periode = in_array($periode,['semua','harian','mingguan','bulanan','tahunan']) ? 
           $periode : 'semua';

// Filter tanggal
$where_date = "";
switch($periode){
    case 'harian': 
        $where_date = "AND DATE(s.tanggal)=CURDATE()"; 
        break;
    case 'mingguan': 
        $where_date = "AND DATE(s.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; 
        break;
    case 'bulanan': 
        $where_date = "AND MONTH(s.tanggal)=MONTH(CURDATE()) AND YEAR(s.tanggal)=YEAR(CURDATE())"; 
        break;
    case 'tahunan': 
        $where_date = "AND YEAR(s.tanggal)=YEAR(CURDATE())"; 
        break;
}

// Query
$sql = "
    SELECT 
        s.id_setoran,
        s.id_user,
        u.nama AS nama_user,
        s.kategori,
        s.berat,
        s.tanggal,
        COALESCE(v.status_validasi,'menunggu') AS status_validasi,
        v.tanggal_validasi
    FROM setoran s
    LEFT JOIN validasi v ON v.id_setoran = s.id_setoran
    LEFT JOIN user u ON u.id_user = s.id_user
    WHERE 1=1
    $where_date
    ORDER BY s.tanggal DESC
";

$res = $conn->query($sql);
$rows = [];
while($r = $res->fetch_assoc()) $rows[] = $r;

$now = date('Ymd_His');
$label = "laporan_{$periode}_{$now}";



/* ==========================================
        EXPORT EXCEL (CSV)
===========================================*/
if($tipe === 'excel'){
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$label.'.csv"');

    $out = fopen('php://output','w');

    fputcsv($out, [
        'ID Setoran',
        'ID User',
        'Nama User',
        'Kategori',
        'Berat (kg)',
        'Tanggal Setor',
        'Status Validasi',
        'Tanggal Validasi'
    ]);

    foreach($rows as $r){
        fputcsv($out, [
            $r['id_setoran'],
            $r['id_user'],
            $r['nama_user'],
            $r['kategori'],
            $r['berat'],
            $r['tanggal'],
            $r['status_validasi'],
            $r['tanggal_validasi']
        ]);
    }

    fclose($out);
    exit();
}



/* ==========================================
            EXPORT PDF (HTML fallback)
===========================================*/
if($tipe === 'pdf'){
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="'.$label.'.html"');

    echo "<h3>Laporan — ".ucfirst($periode)."</h3>";
    echo "<table border='1' cellpadding='6' cellspacing='0'>";
    echo "<tr>
            <th>ID Setoran</th>
            <th>ID User</th>
            <th>Nama User</th>
            <th>Kategori</th>
            <th>Berat</th>
            <th>Tanggal Setor</th>
            <th>Status</th>
            <th>Tanggal Validasi</th>
          </tr>";

    foreach($rows as $r){
        echo "<tr>
                <td>{$r['id_setoran']}</td>
                <td>{$r['id_user']}</td>
                <td>".htmlspecialchars($r['nama_user'])."</td>
                <td>{$r['kategori']}</td>
                <td>{$r['berat']}</td>
                <td>{$r['tanggal']}</td>
                <td>{$r['status_validasi']}</td>
                <td>{$r['tanggal_validasi']}</td>
              </tr>";
    }

    echo "</table>";
    exit();
}

echo "Tipe tidak valid";
exit();
