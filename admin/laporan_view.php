<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php?error=not_logged_in");
    exit();
}

$conn = new mysqli("localhost","root","","db_banksampah");
if ($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

// Ambil periode & tipe
$periode = $_GET['periode'] ?? 'semua';
$periode = in_array($periode, ['semua','harian','mingguan','bulanan','tahunan']) ? $periode : 'semua';

$tipe = $_GET['tipe'] ?? null; // excel atau pdf
$allowed_tipe = ['excel','pdf'];
$tipe = in_array($tipe, $allowed_tipe) ? $tipe : null;

// Filter tanggal
$where_date = "";
switch($periode){
    case 'harian': $where_date = "AND DATE(s.tanggal)=CURDATE()"; break;
    case 'mingguan': $where_date = "AND DATE(s.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
    case 'bulanan': $where_date = "AND MONTH(s.tanggal)=MONTH(CURDATE()) AND YEAR(s.tanggal)=YEAR(CURDATE())"; break;
    case 'tahunan': $where_date = "AND YEAR(s.tanggal)=YEAR(CURDATE())"; break;
}

$sql = "
    SELECT 
        s.id_setoran, 
        s.id_user, 
        u.nama AS nama_user, 
        s.kategori, 
        s.berat, 
        s.tanggal,
        COALESCE(v.status_validasi, 'menunggu') AS status_validasi,
        COALESCE(v.tanggal_validasi, '-') AS tanggal_validasi
    FROM setoran s
    LEFT JOIN user u ON u.id_user = s.id_user
    LEFT JOIN validasi v ON v.id_setoran = s.id_setoran
    WHERE 1=1
    $where_date
    ORDER BY s.tanggal DESC
";
$result = $conn->query($sql);
$rows = [];
while($row = $result->fetch_assoc()) $rows[] = $row;

// URL untuk download Excel
$base_excel = "laporan_export.php?tipe=excel&period=".urlencode($periode);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Setoran</title>
<link rel="stylesheet" href="../assets/css_admin/laporan_view.css">
<style>
.table { border-collapse: collapse; width: 100%; margin-top: 20px; }
.table th, .table td { border: 1px solid #333; padding: 6px; text-align: left; }
.btn { padding: 6px 12px; background: #4CAF50; color: #fff; text-decoration: none; border-radius: 4px; margin-right: 5px; cursor:pointer; }
.btn-back { background: #555; }
.controls { margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
</style>
</head>
<body>

<header>
  <h2>Laporan Setoran — <?= htmlspecialchars(ucfirst($periode)) ?></h2>

  <div class="controls">
      <!-- Periode -->
      <form method="GET" action="laporan_view.php" style="display:flex; gap:10px; align-items:center;">
        <label class="small">Periode:</label>
        <select name="periode" onchange="this.form.submit()">
          <option value="semua"   <?= $periode==='semua'?'selected':'' ?>>Semua</option>
          <option value="harian"  <?= $periode==='harian'?'selected':'' ?>>Harian</option>
          <option value="mingguan"<?= $periode==='mingguan'?'selected':'' ?>>Mingguan</option>
          <option value="bulanan" <?= $periode==='bulanan'?'selected':'' ?>>Bulanan</option>
          <option value="tahunan" <?= $periode==='tahunan'?'selected':'' ?>>Tahunan</option>
        </select>
        <input type="hidden" name="tipe" value="<?= $tipe ?>">
      </form>

      <!-- Tombol Kembali -->
      <a class="btn btn-back" href="kelola_laporan.php">Kembali</a>

      <!-- Tombol Download Excel atau PDF sesuai tipe -->
      <?php if($tipe === 'excel'): ?>
          <a class="btn" href="<?= $base_excel ?>" target="_blank">Download Excel</a>
      <?php elseif($tipe === 'pdf'): ?>
          <button id="downloadPdf" class="btn">Download PDF</button>
      <?php endif; ?>
  </div>
</header>

<section>
  <p class="small">Jumlah baris: <?= count($rows) ?></p>

  <table class="table" id="tabelSetoran">
    <thead>
      <tr>
        <th>ID Setoran</th>
        <th>ID User</th>
        <th>Nama User</th>
        <th>Kategori</th>
        <th>Berat (kg)</th>
        <th>Tanggal Setor</th>
        <th>Status Validasi</th>
        <th>Tanggal Validasi</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($rows)): ?>
        <tr><td colspan="8" style="text-align:center">Belum ada data</td></tr>
      <?php else: ?>
        <?php foreach($rows as $r): ?>
        <tr>
          <td><?= $r['id_setoran'] ?></td>
          <td><?= $r['id_user'] ?></td>
          <td><?= htmlspecialchars($r['nama_user']) ?></td>
          <td><?= htmlspecialchars($r['kategori']) ?></td>
          <td style="text-align:right"><?= number_format($r['berat'],2,',','.') ?></td>
          <td><?= $r['tanggal'] ?></td>
          <td><?= $r['status_validasi'] ?></td>
          <td><?= $r['tanggal_validasi'] ?></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<?php if($tipe === 'pdf'): ?>
<!-- jsPDF + autoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script>
document.getElementById('downloadPdf').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(16);
    doc.text("Laporan Setoran — <?= ucfirst($periode) ?>", 14, 20);

    const headers = [];
    document.querySelectorAll('#tabelSetoran thead th').forEach(th => headers.push(th.innerText));

    const data = [];
    document.querySelectorAll('#tabelSetoran tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => row.push(td.innerText));
        data.push(row);
    });

    doc.autoTable({
        startY: 30,
        head: [headers],
        body: data,
        styles: { fontSize: 10 },
        headStyles: { fillColor: [100, 100, 100], textColor: 255 },
    });

    doc.save("laporan_setoran.pdf");
});
</script>
<?php endif; ?>

</body>
</html>
