<?php
session_start();
require_once 'koneksi.php';

// Redirect jika bukan admin
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) || !isset($_SESSION["location"]) || $_SESSION["location"] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Tangkap parameter filter tanggal jika ada
$selectedDate = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');

// Fungsi untuk mengambil data beban pembangkit dengan filter tanggal
function getBebanData($conn, $selectedDate) {
    $query = "SELECT 
                u.Location, 
                o.total_mw,
                o.report_time,
                o.report_date
              FROM operational_reports o 
              JOIN users u ON o.user_id = u.id_user 
              WHERE o.report_id IN (
                  SELECT MAX(o2.report_id)
                  FROM operational_reports o2
                  JOIN users u2 ON o2.user_id = u2.id_user
                  WHERE DATE(o2.report_date) = ?
                  GROUP BY u2.Location
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    $lastUpdate = null;
    
    while ($row = $result->fetch_assoc()) {
        $data[$row['Location']] = [
            'beban' => (float)$row['total_mw'],
            'waktu' => $row['report_date'] . ' ' . $row['report_time']
        ];
        if (!$lastUpdate || $row['report_date'] . ' ' . $row['report_time'] > $lastUpdate) {
            $lastUpdate = $row['report_date'] . ' ' . $row['report_time'];
        }
    }
    
    return [
        'data' => $data,
        'lastUpdate' => $lastUpdate,
        'selectedDate' => $selectedDate
    ];
}

$bebanData = getBebanData($conn, $selectedDate);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Beban Pembangkit - UB Pembangkitan Mahakam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        :root {
            --pln-blue: #0056b3;
            --pln-light-blue: #1aa7ec;
            --pln-dark-blue: #1E2F97;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .header {
            background: #1F57B2;
            color: white;
            padding: 20px 0;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header-logo {
            height: 60px;
            margin-right: 15px;
        }

        .card-dashboard {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border: none;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .card-dashboard:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--pln-blue), var(--pln-light-blue));
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }

        .beban-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--pln-dark-blue);
        }

        .total-beban {
            background:rgb(52, 182, 99);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.5rem;
            text-align: center;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .pie-chart-wrapper {
            flex: 0 0 70%;
            max-width: 70%;
            padding-right: 15px;
        }

        .legend-wrapper {
            flex: 0 0 30%;
            max-width: 30%;
            padding-left: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            padding: 6px;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .legend-text {
            flex-grow: 1;
            font-size: 0.8rem;
        }

        .legend-value {
            font-weight: 600;
            margin-left: 8px;
            color: var(--pln-dark-blue);
            font-size: 0.8rem;
        }

        .last-update {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .filter-container {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 992px) {
            .pie-chart-wrapper,
            .legend-wrapper {
                flex: 0 0 100%;
                max-width: 100%;
                padding: 0;
            }

            .pie-chart-wrapper {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="d-flex align-items-center">
                <img src="../img/plnbulat.png" alt="PLN Logo" class="header-logo">
                <div>
                    <h1 class="header-title mb-0">Monitoring Beban Pembangkit</h1>
                    <p class="mb-0">Unit Bisnis Pembangkitan Mahakam</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Filter Tanggal -->
        <div class="filter-container">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="filter_date" class="form-label">Pilih Tanggal</label>
                    <input type="date" class="form-control" id="filter_date" name="filter_date" 
                           value="<?= htmlspecialchars($bebanData['selectedDate']) ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="?filter_date=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Hari Ini
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Tanggal dan Shift -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card card-dashboard">
                    <div class="card-header">
                        <i class="bi bi-calendar-event me-2"></i>Informasi Monitoring
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tanggal:</strong> <span id="currentDate"><?= date('d F Y', strtotime($bebanData['selectedDate'])) ?></span></p>
                                <p><strong>Shift:</strong> <span id="currentShift"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Update Terakhir:</strong> <span id="lastUpdate"><?= 
                                    $bebanData['lastUpdate'] ? date('d F Y H:i', strtotime($bebanData['lastUpdate'])) : 'Belum ada data' 
                                ?></span></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge <?= $bebanData['selectedDate'] == date('Y-m-d') ? 'bg-success' : 'bg-info' ?>">
                                        <?= $bebanData['selectedDate'] == date('Y-m-d') ? 'Live Data' : 'Data Historis' ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="total-beban">
                    <div>Total Beban Pembangkit</div>
                    <div id="totalBeban">0.0 MW</div>
                    <div class="last-update">Tanggal: <?= date('d F Y', strtotime($bebanData['selectedDate'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Grafik Pie Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-dashboard">
                    <div class="card-header">
                        <i class="bi bi-pie-chart me-2"></i>Distribusi Beban Pembangkit (MW)
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="pie-chart-wrapper">
                                <canvas id="pieChart" height="400"></canvas>
                            </div>
                            <div class="legend-wrapper" id="chartLegend"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Unit Pembangkit -->
        <div class="row">
            <!-- Kolom 1 -->
            <div class="col-md-4">
                <div class="card card-dashboard mb-4" id="card-tanjung_batu">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTGU Tanjung Batu
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['tanjung_batu']) ? $bebanData['data']['tanjung_batu']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['tanjung_batu']) ? ($bebanData['data']['tanjung_batu']['beban'] / 15 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['tanjung_batu']) ? $bebanData['data']['tanjung_batu']['beban'] : 0, 15) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['tanjung_batu']) ? ($bebanData['data']['tanjung_batu']['beban'] / 15 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-dashboard mb-4" id="card-sentawar">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTD Sentawar
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['sentawar']) ? $bebanData['data']['sentawar']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['sentawar']) ? ($bebanData['data']['sentawar']['beban'] / 15 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['sentawar']) ? $bebanData['data']['sentawar']['beban'] : 0, 15) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['sentawar']) ? ($bebanData['data']['sentawar']['beban'] / 15 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-dashboard mb-4" id="card-muara_wahau">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTD Muara Wahau
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['muara_wahau']) ? $bebanData['data']['muara_wahau']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['muara_wahau']) ? ($bebanData['data']['muara_wahau']['beban'] / 10 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['muara_wahau']) ? $bebanData['data']['muara_wahau']['beban'] : 0, 10) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['muara_wahau']) ? ($bebanData['data']['muara_wahau']['beban'] / 10 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom 2 -->
            <div class="col-md-4">
                <div class="card card-dashboard mb-4" id="card-bontang">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTD Bontang
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['bontang']) ? $bebanData['data']['bontang']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['bontang']) ? ($bebanData['data']['bontang']['beban'] / 15 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['bontang']) ? $bebanData['data']['bontang']['beban'] : 0, 15) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['bontang']) ? ($bebanData['data']['bontang']['beban'] / 15 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-dashboard mb-4" id="card-muara_bengkal">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTD Muara Bengkal
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['muara_bengkal']) ? $bebanData['data']['muara_bengkal']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['muara_bengkal']) ? ($bebanData['data']['muara_bengkal']['beban'] / 5 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['muara_bengkal']) ? $bebanData['data']['muara_bengkal']['beban'] : 0, 5) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['muara_bengkal']) ? ($bebanData['data']['muara_bengkal']['beban'] / 5 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-dashboard mb-4" id="card-long_bagun">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTD Long Bagun
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['long_bagun']) ? $bebanData['data']['long_bagun']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['long_bagun']) ? ($bebanData['data']['long_bagun']['beban'] / 5 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['long_bagun']) ? $bebanData['data']['long_bagun']['beban'] : 0, 5) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['long_bagun']) ? ($bebanData['data']['long_bagun']['beban'] / 5 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>  

            <!-- Kolom 3 -->
            <div class="col-md-4">
                <div class="card card-dashboard mb-4" id="card-keledang">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTD Keledang
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['keledang']) ? $bebanData['data']['keledang']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['keledang']) ? ($bebanData['data']['keledang']['beban'] / 5 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['keledang']) ? $bebanData['data']['keledang']['beban'] : 0, 5) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['keledang']) ? ($bebanData['data']['keledang']['beban'] / 5 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-dashboard mb-4" id="card-long_iram">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTD Long Iram
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['long_iram']) ? $bebanData['data']['long_iram']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['long_iram']) ? ($bebanData['data']['long_iram']['beban'] / 5 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['long_iram']) ? $bebanData['data']['long_iram']['beban'] : 0, 5) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['long_iram']) ? ($bebanData['data']['long_iram']['beban'] / 5 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-dashboard mb-4" id="card-karang_asam">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTD Karang Asam
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['karang_asam']) ? $bebanData['data']['karang_asam']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['karang_asam']) ? ($bebanData['data']['karang_asam']['beban'] / 10 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['karang_asam']) ? $bebanData['data']['karang_asam']['beban'] : 0, 10) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['karang_asam']) ? ($bebanData['data']['karang_asam']['beban'] / 10 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-dashboard" id="card-sambera">
                    <div class="card-header">
                        <i class="bi bi-lightning-charge me-2"></i>PLTG Sambera
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="beban-value"><?= isset($bebanData['data']['sambera']) ? $bebanData['data']['sambera']['beban'] . ' MW' : '0.0 MW' ?></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: <?= isset($bebanData['data']['sambera']) ? ($bebanData['data']['sambera']['beban'] / 10 * 100) : 0 ?>%; 
                                background-color: <?= getProgressColor(isset($bebanData['data']['sambera']) ? $bebanData['data']['sambera']['beban'] : 0, 10) ?>;" 
                                aria-valuenow="<?= isset($bebanData['data']['sambera']) ? ($bebanData['data']['sambera']['beban'] / 10 * 100) : 0 ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk format tanggal
        function formatDate(date) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('id-ID', options);
        }

        // Fungsi untuk menentukan shift
        function getCurrentShift() {
            const hours = new Date().getHours();
            if (hours >= 7 && hours < 12) return 'Pagi (07:00 - 12:00)';
            if (hours >= 12 && hours < 15) return 'Siang (12:00 - 15:00)';
            if (hours >= 15 && hours < 23) return 'Sore (15:00 - 19:00)';
            return 'Malam (23:00 - 07:00)';
        }

        // Set tanggal dan shift
        document.getElementById('currentDate').textContent = '<?= date('d F Y', strtotime($bebanData['selectedDate'])) ?>';
        document.getElementById('currentShift').textContent = getCurrentShift();

        // Inisialisasi chart
        document.addEventListener('DOMContentLoaded', function() {
            const unitNames = [
                'PLTGU Tanjung Batu',
                'PLTD Sentawar',
                'PLTD Bontang',
                'PLTD Muara Wahau',
                'PLTD Muara Bengkal',
                'PLTD Long Bagun',
                'PLTD Keledang',
                'PLTD Long Iram',
                'PLTD Karang Asam',
                'PLTG Sambera'
            ];

            // Ambil data dari PHP
            const bebanData = [
                <?= isset($bebanData['data']['tanjung_batu']) ? $bebanData['data']['tanjung_batu']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['sentawar']) ? $bebanData['data']['sentawar']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['bontang']) ? $bebanData['data']['bontang']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['muara_wahau']) ? $bebanData['data']['muara_wahau']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['muara_bengkal']) ? $bebanData['data']['muara_bengkal']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['long_bagun']) ? $bebanData['data']['long_bagun']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['keledang']) ? $bebanData['data']['keledang']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['long_iram']) ? $bebanData['data']['long_iram']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['karang_asam']) ? $bebanData['data']['karang_asam']['beban'] : 0 ?>,
                <?= isset($bebanData['data']['sambera']) ? $bebanData['data']['sambera']['beban'] : 0 ?>
            ];

            // Warna untuk setiap unit
            const backgroundColors = [
                '#28a745', '#17a2b8', '#0d6efd', '#ffc107', '#184951',
                '#6c757d', '#6610f2', '#fd7e14', '#20c997', '#d63384'
            ];

            // Hitung total beban
            const totalBeban = bebanData.reduce((sum, value) => sum + value, 0);
            document.getElementById('totalBeban').textContent = totalBeban.toFixed(1) + ' MW';

            // Inisialisasi pie chart
            const ctx = document.getElementById('pieChart').getContext('2d');
            const pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: unitNames,
                    datasets: [{
                        data: bebanData,
                        backgroundColor: backgroundColors,
                        borderWidth: 1,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} MW (${percentage}%)`;
                                }
                            }
                        },
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return percentage > 5 ? `${percentage}%` : '';
                            },
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 14
                            }
                        }
                    },
                    onClick: (evt, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const unitName = pieChart.data.labels[index];
                            const unitValue = pieChart.data.datasets[0].data[index];
                            const total = pieChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((unitValue / total) * 100);
                            alert(`${unitName}\nBeban: ${unitValue} MW\nKontribusi: ${percentage}%`);
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });

            // Buat legend
            function createLegend() {
                const legendContainer = document.getElementById('chartLegend');
                legendContainer.innerHTML = '';

                const total = pieChart.data.datasets[0].data.reduce((a, b) => a + b, 0);

                pieChart.data.labels.forEach((label, i) => {
                    const value = pieChart.data.datasets[0].data[i];
                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;

                    const legendItem = document.createElement('div');
                    legendItem.className = 'legend-item';

                    const colorBox = document.createElement('div');
                    colorBox.className = 'legend-color';
                    colorBox.style.backgroundColor = pieChart.data.datasets[0].backgroundColor[i];

                    const text = document.createElement('div');
                    text.className = 'legend-text';
                    text.textContent = label;

                    const valueBox = document.createElement('div');
                    valueBox.className = 'legend-value';
                    valueBox.textContent = `${value} MW (${percentage}%)`;

                    legendItem.appendChild(colorBox);
                    legendItem.appendChild(text);
                    legendItem.appendChild(valueBox);
                    legendContainer.appendChild(legendItem);
                });
            }

            createLegend();
        });
    </script>
</body>
</html>

<?php
// Fungsi untuk menentukan warna progress bar berdasarkan beban
function getProgressColor($beban, $kapasitas) {
    if ($beban === 0) {
        return '#6c757d'; // Abu-abu untuk unit mati
    }
    
    $persentase = ($beban / $kapasitas) * 100;
    
    if ($persentase > 80) {
        return '#dc3545'; // Merah untuk beban tinggi
    } elseif ($persentase > 50) {
        return '#ffc107'; // Kuning untuk beban sedang
    } else {
        return '#28a745'; // Hijau untuk beban rendah
    }
}
?>