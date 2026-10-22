<?php
session_start();

// Periksa session admin
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) || $_SESSION["user_type"] != "admin") {
    header("Location: index.php");
    exit();
}

// Koneksi database (sama dengan proses_login.php)
$host = "localhost";
$username_db = "root";
$password_db = "";
$database_name = "data_godzilla";

$conn = new mysqli($host, $username_db, $password_db, $database_name);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Query dengan filter
$where = [];
if (!empty($_GET['nama'])) {
    $nama = $conn->real_escape_string($_GET['nama']);
    $where[] = "u.username LIKE '%$nama%'";
}
if (!empty($_GET['tanggal'])) {
    $tanggal = $conn->real_escape_string($_GET['tanggal']);
    $where[] = "r.report_date = '$tanggal'";
}
if (!empty($_GET['shift'])) {
    $shift = $conn->real_escape_string($_GET['shift']);
    $where[] = "r.report_time LIKE '%$shift%'";
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT 
            r.report_id, 
            r.report_date, 
            r.report_time, 
            u.username, 
            r.location,
            r.total_mw,
            r.notes
        FROM operational_reports r
        JOIN users u ON r.user_id = u.id_user
        $where_sql
        ORDER BY r.report_date DESC, r.report_time DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Laporan - Admin</title>
    <!-- Gunakan CSS yang sama dengan admin_dashboard.php -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* [Sisipkan semua CSS dari file asli] */
        /* Tetap gunakan CSS yang sama untuk konsistensi tampilan */
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="menu-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="daftar_laporan_admin.php">Daftar Laporan</a>
            <!-- Tambahkan menu lain sesuai kebutuhan -->
        </div>
    </div>

    <!-- Header -->
    <div class="admin-header" id="adminHeader">
        <div class="logo-nama">
            <img src="img/pln.png" alt="Logo PLN" class="logo-img" />
            <div class="title-group">
                <h2>Daftar Laporan Operasi</h2>
                <small>PT. PLN (Persero)</small>
            </div>
        </div>
        <button class="menu-toggle" id="menuToggle">&#9776;</button>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="laporan-container">
            <h1 style="margin-bottom: 20px;">Daftar Laporan Operasi</h1>

            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="nama">Nama PLTD</label>
                    <input type="text" name="nama" id="nama" placeholder="Cari nama PLTD"
                        value="<?= isset($_GET['nama']) ? htmlspecialchars($_GET['nama']) : '' ?>" />
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal"
                        value="<?= isset($_GET['tanggal']) ? htmlspecialchars($_GET['tanggal']) : '' ?>" />
                </div>

                <div class="form-group">
                    <label for="shift">Waktu Laporan</label>
                    <select name="shift" id="shift">
                        <option value="">Semua</option>
                        <option value="07.00" <?= (isset($_GET['shift']) && $_GET['shift'] == '07.00') ? 'selected' : '' ?>>07.00 WITA</option>
                        <option value="12.00" <?= (isset($_GET['shift']) && $_GET['shift'] == '12.00') ? 'selected' : '' ?>>12.00 WITA</option>
                        <option value="15.00" <?= (isset($_GET['shift']) && $_GET['shift'] == '15.00') ? 'selected' : '' ?>>15.00 WITA</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit">Filter</button>
                </div>
            </form>

            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                // Ambil data unit
                $units = [];
                $unit_query = $conn->query("SELECT unit_number, status, load_mw, last_operation, notes 
                                          FROM unit_statuses 
                                          WHERE report_id = {$row['report_id']}");
                while ($unit = $unit_query->fetch_assoc()) {
                    $units[] = $unit;
                }

                // Ambil data stok energi
                $stocks = [];
                $stock_query = $conn->query("SELECT fuel_type, received, used, stock, stock_days 
                                           FROM energy_stocks 
                                           WHERE report_id = {$row['report_id']}");
                while ($stock = $stock_query->fetch_assoc()) {
                    $stocks[$stock['fuel_type']] = $stock;
                }

                // Format tanggal Indonesia
                function formatTanggalIndonesia($tanggal)
                {
                    $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                    $timestamp = strtotime($tanggal);
                    $hari = $hariIndo[date('w', $timestamp)];
                    $tgl = date('d', $timestamp);
                    $bulan = $bulanIndo[date('n', $timestamp) - 1];
                    $tahun = date('Y', $timestamp);

                    return "$hari, $tgl $bulan $tahun";
                }
                ?>

                <div class="laporan-card">
                    <pre style="white-space: pre-wrap; font-family: 'Courier New', monospace;">
                    <?= formatTanggalIndonesia($row['report_date']) ?>                     <?= $row['report_time'] ?>
                    ---------------------------------------------------------
                    <b>UP <?= htmlspecialchars($row['username']) ?> (<?= htmlspecialchars($row['location']) ?>)</b>

                    <?php foreach ($units as $unit): ?>
                                        <?= htmlspecialchars($unit['unit_number']) ?> 
                                            Beban: <?= $unit['load_mw'] ?> MW
                                            Status: <?= $unit['status'] ?>
                                            Operasi Terakhir: <?= $unit['last_operation'] ? htmlspecialchars($unit['last_operation']) : '-' ?>
                                            Catatan: <?= $unit['notes'] ? htmlspecialchars($unit['notes']) : '-' ?>

                    <?php endforeach; ?>
                    <b>TOTAL BEBAN: <?= $row['total_mw'] ?> MW</b>

                    <b>STOK ENERGI PRIMER</b>
                    <?php foreach ($stocks as $type => $stock): ?>
                                        <b><?= $type ?></b>
                                        - Penerimaan: <?= $stock['received'] ?> Liter
                                        - Pemakaian: <?= $stock['used'] ?> Liter
                                        - Stok: <?= $stock['stock'] ?> Liter
                                        - Hari Stok: <?= $stock['stock_days'] ? $stock['stock_days'] : '-' ?>

                    <?php endforeach; ?>
                    Catatan: <?= htmlspecialchars($row['notes']) ?>
                                        </pre>

                    <form action="hapus_laporan.php" method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus laporan ini?');" style="margin-top: 15px;">
                        <input type="hidden" name="laporan_id" value="<?= $row['report_id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            Hapus Laporan
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>

            <?php if ($result->num_rows === 0): ?>
                <div class="alert alert-info">Tidak ada laporan yang ditemukan.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script toggle sidebar (sama dengan sebelumnya)
        const toggleBtn = document.getElementById("menuToggle");
        const sidebar = document.getElementById("sidebar");
        const adminHeader = document.querySelector(".admin-header");
        const mainContent = document.getElementById("mainContent");

        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("active");
            adminHeader.style.marginLeft = sidebar.classList.contains("active") ? "230px" : "0";
            mainContent.style.marginLeft = sidebar.classList.contains("active") ? "235px" : "0";
        });
    </script>
</body>

</html>