<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) || !isset($_SESSION["location"]) || $_SESSION["location"] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once 'koneksi.php';

// Get report ID from URL
$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;

// Fetch all report data
$report = [];
$unit_statuses = [];
$energy_stocks = [];
$personil_piket = [];
$pltd_parameters = [];

// Get operational report
$report_query = "SELECT * FROM operational_reports WHERE report_id = ?";
$stmt = $conn->prepare($report_query);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$report_result = $stmt->get_result();
$report = $report_result->fetch_assoc();

if (!$report) {
    header("Location: laporan_karang_asam.php");
    exit();
}

// Get PLTD parameters if exists
// Get PLTD parameters - versi tanpa report_id
$pltd_parameters = [];
$location = 'karang_asam'; // Sesuaikan dengan lokasi laporan

$parameters_query = "SELECT * FROM pltd_parameters WHERE location = ? LIMIT 1";
$stmt = $conn->prepare($parameters_query);

if (!$stmt) {
    die("Error prepare: " . $conn->error);
}

$stmt->bind_param("s", $location);
$stmt->execute();
$parameters_result = $stmt->get_result();
$pltd_parameters = $parameters_result->fetch_assoc();

// Jika tidak ada data, gunakan default
if (!$pltd_parameters) {
    $pltd_parameters = [
        'dts' => 16000,
        'dmn' => 10000,
        'dmp' => 9000
    ];
}

// Fetch unit statuses
$unit_status_query = "SELECT * FROM unit_statuses WHERE report_id = ?";
$stmt = $conn->prepare($unit_status_query);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$unit_status_result = $stmt->get_result();
while ($row = $unit_status_result->fetch_assoc()) {
    $unit_statuses[] = $row;
}

// Fetch energy stocks
$energy_stock_query = "SELECT * FROM energy_stocks WHERE report_id = ?";
$stmt = $conn->prepare($energy_stock_query);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$energy_stock_result = $stmt->get_result();
while ($row = $energy_stock_result->fetch_assoc()) {
    $energy_stocks[] = $row;
}

// Fetch personil piket
$personil_query = "SELECT * FROM personil_piket WHERE report_id = ?";
$stmt = $conn->prepare($personil_query);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$personil_result = $stmt->get_result();
while ($row = $personil_result->fetch_assoc()) {
    $personil_piket[] = $row;
}

// Separate pengawas and pelaksana
$pengawas = array_filter($personil_piket, function($p) { return $p['jabatan'] == 'PENGAWAS'; });
$pelaksana = array_filter($personil_piket, function($p) { return $p['jabatan'] == 'PELAKSANA'; });

// Get day name from report date
$report_date = new DateTime($report['report_date']);
$hari = $report_date->format('l');
$hari_indonesia = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
][$hari];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan PLTD Karang Asam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #797ef6, #4adede, #1aa7ec, #1E2F97);
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .container {
            margin-top: 30px;
            background-color: rgba(248, 249, 250, 0.9);
            padding: 20px;
            border-radius: 5px;
        }

        .header {
            background-color: rgba(0, 123, 255, 0.8);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .header a {
            color: white;
            text-decoration: none;
        }

        .form-section {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        /* Status Table */
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .status-table th {
            background-color: #0056b3;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .status-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        /* Status color coding */
        .status-OPERASI {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }

        .status-RS {
            background-color: #d4ff00;
            color: black;
            font-weight: bold;
        }

        .status-RU {
            background-color: #4f0000;
            color: white;
            font-weight: bold;
        }

        .status-FO {
            background-color: #e80000;
            color: white;
            font-weight: bold;
        }

        .status-PO {
            background-color: #022bf8;
            color: white;
            font-weight: bold;
        }

        .status-MO {
            background-color: #fd8300;
            color: white;
            font-weight: bold;
        }

        .status-SF {
            background-color: #04ebef;
            color: black;
            font-weight: bold;
        }

        /* Total MW Card */
        .total-card {
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .total-card-body {
            background: linear-gradient(to right, #1E2F97, #4adede);
            min-height: 80px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
        }

        .total-text {
            color: white;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .total-value {
            background-color: white;
            color: #343a40;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 1.8rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Sticky header */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: linear-gradient(#fafaff, #fdfcfa);
            padding: 15px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .sticky-header img {
            height: 60px;
            margin-right: 15px;
        }

        .back-btn {
            margin-bottom: 25px;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .view-only-input {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px 12px;
            width: 100%;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Header Logo & Judul -->
    <div class="sticky-header">
        <img src="img/pln.png" alt="Logo PLN">
        <h5 class="mb-0 font-weight-bold text-dark">PT PLN (PERSERO) PLTD KARANG ASAM</h5>
    </div>

    <div class="container">
        <a href="laporan_karang_asam.php" class="btn btn-secondary back-btn">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Laporan
        </a>

        <div class="header">
            <a href="#">
                <h2>LAPORAN OPERASI PLTD KARANG ASAM</h2>
            </a>
        </div>

        <!-- Date and Time Section -->
        <div class="form-section">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tanggal">TANGGAL HARI</label>
                        <div class="view-only-input"><?= date('d/m/Y', strtotime($report['report_date'])) ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="hari">HARI</label>
                        <div class="view-only-input"><?= $hari_indonesia ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="waktu">WAKTU</label>
                        <div class="view-only-input"><?= $report['report_time'] ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="cuaca">CUACA</label>
                        <div class="view-only-input"><?= $report['Cuaca'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personil Piket Section -->
        <div class="form-section">
            <h4 class="text-center mb-4">PERSONIL PIKET</h4>

            <table class="table table-bordered">
                <thead>
                    <tr class="bg-primary text-white">
                        <th class="text-center">PENGAWAS (1 ORANG)</th>
                        <th class="text-center">PELAKSANA (3 ORANG)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="3">
                            <div class="view-only-input"><?= !empty($pengawas) ? reset($pengawas)['nama_personil'] : 'Tidak tercatat' ?></div>
                        </td>
                        <td>
                            <div class="view-only-input"><?= isset($pelaksana[1]) ? $pelaksana[1]['nama_personil'] : 'Tidak tercatat' ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="view-only-input"><?= isset($pelaksana[2]) ? $pelaksana[2]['nama_personil'] : 'Tidak tercatat' ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="view-only-input"><?= isset($pelaksana[3]) ? $pelaksana[3]['nama_personil'] : 'Tidak tercatat' ?></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Status Table -->
        <div class="form-section">
            <h4 class="text-center mb-4">LAPORAN OPERASI HARIAN</h4>

            <table class="status-table">
                <thead>
                    <tr>
                        <th>MESIN</th>
                        <th>STATUS</th>
                        <th>BEBAN (MW)</th>
                        <th>TES KESIAPAN OPERASI</th>
                        <th>CATATAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unit_statuses as $unit): ?>
                    <tr>
                        <td><?= $unit['unit_number'] ?></td>
                        <td class="status-<?= $unit['status'] ?>"><?= $unit['status'] ?></td>
                        <td><?= number_format($unit['load_mw'], 2) ?></td>
                        <td><?= $unit['last_operation'] ?: '-' ?></td>
                        <td><?= $unit['notes'] ?: '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- TOTAL PLTD  -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow total-card">
                        <div class="card-body total-card-body">
                            <div class="total-text">
                                TOTAL BEBAN
                            </div>
                            <div class="total-value">
                                <?= number_format($report['total_mw'], 2) ?> MW
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PLTD Parameters -->
        <?php if ($pltd_parameters): ?>
        <div class="form-section">
            <h4 class="text-center mb-4">PLTD KARANG ASAM PARAMETERS</h4>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <table class="status-table">
                        <thead>
                            <tr class="bg-success text-white">
                                <th class="text-center" style="width: 40%;">PARAMETER</th>
                                <th class="text-center" style="width: 30%;">NILAI</th>
                                <th class="text-center" style="width: 30%;">SATUAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-weight-bold">DTS (Beban Total Sistem)</td>
                                <td><?= number_format($pltd_parameters['dts'], 2) ?></td>
                                <td class="text-center">MW</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">DMN (Beban Minimum)</td>
                                <td><?= number_format($pltd_parameters['dmn'], 2) ?></td>
                                <td class="text-center">MW</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">DMP (Beban Puncak)</td>
                                <td><?= number_format($pltd_parameters['dmp'], 2) ?></td>
                                <td class="text-center">MW</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">BP (Beban Pembangkit)</td>
                                <td><?= number_format($pltd_parameters['bp'], 2) ?></td>
                                <td class="text-center">MW</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">SURPLUS</td>
                                <td><?= number_format($pltd_parameters['surplus'], 2) ?></td>
                                <td class="text-center">MW</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stock Energy (only show for 07.00 WITA) -->
        <?php if ($report['report_time'] === '07.00 WITA' && !empty($energy_stocks)): ?>
        <div class="form-section">
            <h4 class="text-center mb-4">STOCK ENERGY PRIMER</h4>

            <div class="row">
                <?php foreach ($energy_stocks as $stock): ?>
                <div class="col-md-6">
                    <h5 class="text-center bg-primary text-white p-2"><?= $stock['fuel_type'] ?></h5>
                    <table class="status-table">
                        <tr>
                            <td>Penerimaan Liter</td>
                            <td><?= number_format($stock['received'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Pemakaian Liter</td>
                            <td><?= number_format($stock['used'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Stock Liter</td>
                            <td><?= number_format($stock['stock'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>HOP</td>
                            <td><?= $stock['HOP'] ?></td>
                        </tr>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Catatan Tambahan -->
        <?php if (!empty($report['notes'])): ?>
        <div class="form-section">
            <h4 class="text-center mb-4">CATATAN TAMBAHAN</h4>
            <div class="view-only-input" style="min-height: 100px;"><?= nl2br(htmlspecialchars($report['notes'])) ?></div>
        </div>
        <?php endif; ?>

        <!-- Tombol Aksi -->
        <div class="text-center mt-4">
            <a href="edit_laporan_karang_asam.php?report_id=<?= $report_id ?>&location=karang_asam" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Laporan
            </a>
            <a href="laporan_karang_asam.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>