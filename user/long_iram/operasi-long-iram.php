<?php
session_start();

// Periksa session
if (!isset($_SESSION["user_id"]) || $_SESSION["location"] !== 'long_iram') {
    header("Location: ../../index.php");
    exit();
}

// Perbaikan path koneksi.php
$koneksi_path = __DIR__ . '/../../koneksi.php';
if (!file_exists($koneksi_path)) {
    die("File koneksi tidak ditemukan di: " . $koneksi_path);
}
require_once $koneksi_path;
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $report_date = $_POST['tanggal'];
    $report_time = $_POST['waktu'];
    $location = 'long_iram';

    // Calculate total MW and prepare unit statuses
    $total_mw = 0;
    $unit_statuses = [];

    // Process each unit (adjust based on your units)
    $units = [
        'Mesin Unit #1',
        'Mesin Unit #2',
        'Mesin Unit #3',
        'Mesin Unit #4',
        'Mesin Unit #5',
        'Mesin Unit #6',
        'Mesin Unit #7',
        'Mesin Unit #8',
        'Mesin Unit #9',
        'Mesin Unit #10'
    ];

    foreach ($units as $index => $unit_name) {
        $i = $index + 1;
        $status = $_POST["status$i"];
        $load_mw = (float) $_POST["beban$i"];
        $last_operation = $_POST["tes$i"] ?? '';
        $notes = $_POST["catatan$i"] ?? '';

        $total_mw += $load_mw;

        $unit_statuses[] = [
            'unit_number' => $unit_name,
            'status' => $status,
            'load_mw' => $load_mw,
            'last_operation' => $last_operation,
            'notes' => $notes
        ];
    }

    // Insert PLTD Keledang parameters
    if ($location === 'long_iram') {
        $stmt = $conn->prepare("INSERT INTO pltd_parameters 
                          (dts, dmn, dmp, bp, surplus, padam_jaringan, padam_defisit)  
                          VALUES (?, ?, ?, ?, ?, ?, ?)");

        $dts = (float) $_POST['dts'];
        $dmn = (float) $_POST['dmn'];
        $dmp = (float) $_POST['dmp'];
        $bp = (float) $_POST['bp'];
        $surplus = (float) $_POST['surplus'];
        $padam_jaringan = (float) $_POST['padam_jaringan'];
        $padam_defisit = (float) $_POST['padam_defisit'];

        $stmt->bind_param("ddddddd", $dts, $dmn, $dmp, $bp, $surplus, $padam_jaringan, $padam_defisit);
        $stmt->execute();
    }

    // Get energy stock data (only for 07.00 WITA)
    $energy_stocks = [];
    if ($report_time === '07.00 WITA') {
        $energy_stocks = [
            'HSD' => [
                'received' => (float) $_POST['hsd_penerimaan'],
                'used' => (float) $_POST['hsd_pemakaian'],
                'stock' => (float) $_POST['hsd_stock'],
                'HOP' => (int) $_POST['hsd_HOP']
            ],
            'MFO' => [
                'received' => (float) $_POST['mfo_penerimaan'],
                'used' => (float) $_POST['mfo_pemakaian'],
                'stock' => (float) $_POST['mfo_stock'],
                'HOP' => (int) $_POST['mfo_HOP']
            ]
        ];
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        // Insert operational report - PERBAIKAN DISINI
        $stmt = $conn->prepare("INSERT INTO operational_reports 
                              (user_id, report_date, report_time, Cuaca, location, total_mw, notes, created_at)  
                              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $notes_combined = "Laporan operasi PLTD LONG IRAM";
        $stmt->bind_param(
            "issssds",
            $_SESSION['user_id'],
            $report_date,
            $report_time,
            $_POST['cuaca'], // Pastikan ini ada di form
            $location,
            $total_mw,
            $notes_combined
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $report_id = $conn->insert_id;


        // Insert unit statuses
        $stmt = $conn->prepare("INSERT INTO unit_statuses (report_id, unit_number, status, load_mw, last_operation, notes) 
                               VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($unit_statuses as $unit) {
            $stmt->bind_param(
                "issdss",
                $report_id,
                $unit['unit_number'],
                $unit['status'],
                $unit['load_mw'],
                $unit['last_operation'],
                $unit['notes']
            );
            $stmt->execute();
        }

        // Menggunakan prepared statement untuk memasukkan data energi ke dalam database
        // Insert energy stocks (only for 07.00 WITA)
        if ($report_time === '07.00 WITA') {
            $stmt = $conn->prepare("INSERT INTO energy_stocks (report_id, fuel_type, received, used, stock, HOP) 
                           VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($energy_stocks as $fuel_type => $stock) {
                $stmt->bind_param("isdddd", $report_id, $fuel_type, $stock['received'], $stock['used'], $stock['stock'], $stock['HOP']);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting energy stock for $fuel_type: " . $stmt->error);
                }
            }
        }
        // Commit transaction
        $conn->commit();

        // Di bagian akhir sebelum redirect ke kirim-wa-keledang.php
// Simpan semua data form yang diperlukan ke session
        $_SESSION['form_data'] = [
            'cuaca' => $_POST['cuaca'],
            'pengawas' => $_POST['pengawas'],
            'pelaksana1' => $_POST['pelaksana1'],
            'pelaksana2' => $_POST['pelaksana2'],
            'pelaksana3' => $_POST['pelaksana3'],
            'tanggal' => $_POST['tanggal'],
            'waktu' => $_POST['waktu'],
            'bp' => $_POST['bp'],
            'surplus' => $_POST['surplus'],
            'padam_jaringan' => $_POST['padam_jaringan'],
            'padam_defisit' => $_POST['padam_defisit']
        ];

        // Only include energy data for 07.00 WITA
        if ($_POST['waktu'] === '07.00 WITA') {
            $_SESSION['form_data'] += [
                'hsd_penerimaan' => $_POST['hsd_penerimaan'],
                'hsd_pemakaian' => $_POST['hsd_pemakaian'],
                'hsd_stock' => $_POST['hsd_stock'],
                'hsd_HOP' => $_POST['hsd_HOP'],
                'mfo_penerimaan' => $_POST['mfo_penerimaan'],
                'mfo_pemakaian' => $_POST['mfo_pemakaian'],
                'mfo_stock' => $_POST['mfo_stock'],
                'mfo_HOP' => $_POST['mfo_HOP']
            ];
        }

        // Set session untuk report_id yang baru dibuat
        $_SESSION['last_report_id'] = $report_id;

        // Redirect ke halaman kirim WA
        header("Location: kirim-wa-long-iram.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Gagal mengirim laporan: " . $e->getMessage();
        header("Location: kirim-wa-long-iram.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLTD LONG IRAM</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

        /* New styles for the status table */
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
            color: white;
            font-weight: bold;
        }

        .status-RU {
            background-color: #4f0000;
            color: white;
            font-weight: bold;
        }

        .status-FO {
            background-color: #e80000;
            color: black;
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
            color: white;
            font-weight: bold;
        }

        .status-default {
            background-color: #f8f9fa;
        }

        /* Form input styling */
        .form-control {
            margin-bottom: 10px;
        }

        /* Button styling */
        .btn-submit {
            background-color: #ffc107;
            color: black;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .btn-submit:hover {
            background-color: #e0a800;
        }

        /* Date and time section */
        .datetime-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
    </style>
</head>

<body>
    <!-- Header Logo & Judul -->
    <div class="sticky-header">
        <img src="img/pln.png" alt="Logo PLN">
        <h5 class="mb-0 font-weight-bold text-dark">PT PLN (PERSERO) PLTD LONG IRAM</h5>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <a href="dashboard_long_iram.php">
                <h2>PLTD LONG IRAM</h2>
            </a>
        </div>

        <form method="POST" action="operasi-long-iram.php">
            <div class="datetime-section">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal">TANGGAL HARI</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="hari">HARI</label>
                            <input type="text" class="form-control" id="hari" name="hari" readonly
                                placeholder="Akan muncul otomatis">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="waktu">WAKTU</label>
                            <select class="form-control" id="waktu" name="waktu" required>
                                <option value="">-- Pilih Sesi --</option>
                                <option value="07.00 WITA">07.00 WITA</option>
                                <option value="12.00 WITA">12.00 WITA</option>
                                <option value="15.00 WITA">15.00 WITA</option>
                                <option value="19.00 WITA">19.00 WITA</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cuaca">CUACA</label>
                            <select class="form-control" id="cuaca" name="cuaca" required>
                                <option value="">-- Pilih Cuaca --</option>
                                <option value="Cerah">Cerah</option>
                                <option value="Berawan">Berawan</option>
                                <option value="Mendung">Mendung</option>
                                <option value="Gerimis">Gerimis</option>
                                <option value="Hujan ">Hujan </option>

                            </select>
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
                            <th class="text-center">PELAKSANA (2 ORANG)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="2">
                                <select class="form-control" name="pengawas" required>
                                    <option value="">Pilih Pengawas</option>
                                    <option value="ANDI SHOFANI">ANDI SHOFANI</option>
                                    <option value="DARWANTO">DARWANTO</option>
                                    <option value="HERMANSYAH">HERMANSYAH</option>
                                    <option value="KARNODIANSYAH">KARNODIANSYAH</option>
                                    <option value="M. FERRY">M. FERRY</option>
                                    <option value="M. IKBAL">M. IKBAL</option>
                                    <option value="M. RIFAI">M. RIFAI</option>
                                    <option value="MUSLIMIN">MUSLIMIN</option>
                                    <option value="MURNI">MURNI</option>
                                    <option value="SANI AJI">SANI AJI</option>
                                    <option value="SARODIN">SARODIN</option>
                                    <option value="TEGUH PUTRA">TEGUH PUTRA</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-control" name="pelaksana1" required>
                                    <option value="">Pilih Pelaksana 1</option>
                                    <option value="ANDI SHOFANI">ANDI SHOFANI</option>
                                    <option value="DARWANTO">DARWANTO</option>
                                    <option value="HERMANSYAH">HERMANSYAH</option>
                                    <option value="KARNODIANSYAH">KARNODIANSYAH</option>
                                    <option value="M. FERRY">M. FERRY</option>
                                    <option value="M. IKBAL">M. IKBAL</option>
                                    <option value="M. RIFAI">M. RIFAI</option>
                                    <option value="MUSLIMIN">MUSLIMIN</option>
                                    <option value="MURNI">MURNI</option>
                                    <option value="SANI AJI">SANI AJI</option>
                                    <option value="SARODIN">SARODIN</option>
                                    <option value="TEGUH PUTRA">TEGUH PUTRA</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select class="form-control" name="pelaksana2" required>
                                    <option value="">Pilih Pelaksana 2</option>
                                    <option value="ANDI SHOFANI">ANDI SHOFANI</option>
                                    <option value="DARWANTO">DARWANTO</option>
                                    <option value="HERMANSYAH">HERMANSYAH</option>
                                    <option value="KARNODIANSYAH">KARNODIANSYAH</option>
                                    <option value="M. FERRY">M. FERRY</option>
                                    <option value="M. IKBAL">M. IKBAL</option>
                                    <option value="M. RIFAI">M. RIFAI</option>
                                    <option value="MUSLIMIN">MUSLIMIN</option>
                                    <option value="MURNI">MURNI</option>
                                    <option value="SANI AJI">SANI AJI</option>
                                    <option value="SARODIN">SARODIN</option>
                                    <option value="TEGUH PUTRA">TEGUH PUTRA</option>
                                </select>
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
                        <!-- Machine 1 -->
                        <tr>
                            <td>Mesin Unit #1</td>
                            <td>
                                <select class="form-control status-select" name="status1" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban1"
                                    id="beban1" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes1" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan1"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machine 2 -->
                        <tr>
                            <td>Mesin Unit #2</td>
                            <td>
                                <select class="form-control status-select" name="status2" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban2"
                                    id="beban2" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes2" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan2"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machine 3 -->
                        <tr>
                            <td>Mesin Unit #3</td>
                            <td>
                                <select class="form-control status-select" name="status3" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban3"
                                    id="beban3" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes3" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan3"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machine 4 -->
                        <tr>
                            <td>Mesin Unit #4</td>
                            <td>
                                <select class="form-control status-select" name="status4" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban4"
                                    id="beban4" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes4" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan4"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machine 5 -->
                        <tr>
                            <td>Mesin Unit #5</td>
                            <td>
                                <select class="form-control status-select" name="status5" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban5"
                                    id="beban5" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes5" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan5"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machines 6-20 (following same pattern) -->
                        <!-- Machine 6 -->
                        <tr>
                            <td>Mesin Unit #6</td>
                            <td>
                                <select class="form-control status-select" name="status6" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban6"
                                    id="beban6" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes6" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan6"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machine 7 -->
                        <tr>
                            <td>Mesin Unit #7</td>
                            <td>
                                <select class="form-control status-select" name="status7" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban7"
                                    id="beban7" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes7" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan7"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machine 8 -->
                        <tr>
                            <td>Mesin Unit #8</td>
                            <td>
                                <select class="form-control status-select" name="status8" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban8"
                                    id="beban8" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes8" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan8"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machine 9 -->
                        <tr>
                            <td>Mesin Unit #9</td>
                            <td>
                                <select class="form-control status-select" name="status9" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban9"
                                    id="beban9" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes9" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan9"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Machine 10 -->
                        <tr>
                            <td>Mesin Unit #10</td>
                            <td>
                                <select class="form-control status-select" name="status10" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" name="beban10"
                                    id="beban10" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" name="tes10" placeholder="Tanggal"></td>
                            <td><textarea class="form-control" rows="1" name="catatan10"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- TOTAL PLTD-MG BONTANG -->
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow total-card">
                            <div class="card-body total-card-body">
                                <div class="total-text">
                                    TOTAL BEBAN
                                </div>
                                <div class="total-value">
                                    <span id="totalMW">0</span> MW
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tambahkan setelah bagian TOTAL PLTD KELEDANG -->
                <div class="form-section mt-4">
                    <h4 class="text-center mb-4">PLTD LONG IRAM</h4>

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
                                        <td class="text-right">16000</td>
                                        <td class="text-center">MW</td>
                                        <input type="hidden" name="dts" value="16000">
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">DMN (Beban Minimum)</td>
                                        <td class="text-right">10000</td>
                                        <td class="text-center">MW</td>
                                        <input type="hidden" name="dmn" value="10000">
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">DMP (Beban Puncak)</td>
                                        <td class="text-right">9000</td>
                                        <td class="text-center">MW</td>
                                        <input type="hidden" name="dmp" value="9000">
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">BP (Beban Pembangkit)</td>
                                        <td><input type="number" step="1" class="form-control text-right" id="bp"
                                                name="bp" placeholder="0.00" required readonly></td>
                                        <td class="text-center">MW</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">SURPLUS</td>
                                        <td><input type="number" step="1" class="form-control text-right" id="surplus"
                                                name="surplus" placeholder="0" required readonly>
                                        </td>
                                        <td class="text-center">MW</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">PADAM JARINGAN</td>
                                        <td><input type="number" step="1" class="form-control text-right"
                                                name="padam_jaringan" placeholder="0.00" required></td>
                                        <td class="text-center">MW</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">PADAM DEFISIT</td>
                                        <td><input type="number" step="0.001" class="form-control text-right"
                                                name="padam_defisit" placeholder="0.00" required></td>
                                        <td class="text-center">MW</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Stock Energy Table -->
                <div class="form-section" id="stockEnergySection">
                    <h4 class="text-center mb-4">STOCK ENERGY PRIMER</h4>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-center bg-primary text-white p-2">HSD</h5>
                            <table class="status-table">
                                <tr>
                                    <td>Penerimaan Liter</td>
                                    <td><input type="number" step="1" class="form-control" name="hsd_penerimaan"
                                            placeholder="Jumlah" required></td>
                                </tr>
                                <tr>
                                    <td>Pemakaian Liter</td>
                                    <td><input type="number" step="1" class="form-control" name="hsd_pemakaian"
                                            placeholder="Jumlah" required></td>
                                </tr>
                                <tr>
                                    <td>Stock Liter</td>
                                    <td><input type="number" step="1" class="form-control" name="hsd_stock"
                                            placeholder="Jumlah" required></td>
                                </tr>
                                <tr>
                                    <td>HOP</td>
                                    <td><input type="number" step="1" class="form-control" name="hsd_HOP"
                                            placeholder="Jumlah Hari" required></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-center bg-primary text-white p-2">MFO</h5>
                            <table class="status-table">
                                <tr>
                                    <td>Penerimaan Liter</td>
                                    <td><input type="number" step="1" class="form-control" name="mfo_penerimaan"
                                            placeholder="Jumlah" required></td>
                                </tr>
                                <tr>
                                    <td>Pemakaian Liter</td>
                                    <td><input type="number" step="1" class="form-control" name="mfo_pemakaian"
                                            placeholder="Jumlah" required></td>
                                </tr>
                                <tr>
                                    <td>Stock Liter</td>
                                    <td><input type="number" step="1" class="form-control" name="mfo_stock"
                                            placeholder="Jumlah" required></td>
                                </tr>
                                <tr>
                                    <td>HOP</td>
                                    <td><input type="number" step="1" class="form-control" name="mfo_HOP"
                                            placeholder="Jumlah Hari" required></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-submit btn-lg">KIRIM LAPORAN</button>
                </div>
        </form>
    </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Constants
        const DMP_VALUE = 9000; // Nilai tetap DMP

        // DOM Elements
        const tanggalInput = document.getElementById("tanggal");
        const hariInput = document.getElementById('hari');
        const waktuSelect = document.getElementById('waktu');
        const stockEnergySection = document.getElementById('stockEnergySection');
        const bpInput = document.getElementById('bp');
        const surplusInput = document.getElementById('surplus');
        const totalMWDisplay = document.getElementById('totalMW');

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
            // Set current date and day
            setCurrentDate();

            // Initialize machine statuses
            initMachineStatuses();

            // Initialize stock energy section
            toggleStockEnergySection();

            // Calculate initial values
            calculateTotalMW();
        });

        // Set current date and auto-fill day
        function setCurrentDate() {
            const today = new Date();
            tanggalInput.valueAsDate = today;
            updateDayName(today);

            tanggalInput.addEventListener('change', function () {
                updateDayName(new Date(this.value));
            });
        }

        function updateDayName(date) {
            const options = { weekday: 'long' };
            const namaHari = date.toLocaleDateString('id-ID', options);
            hariInput.value = namaHari.charAt(0).toUpperCase() + namaHari.slice(1);
        }

        // Initialize machine statuses
        function initMachineStatuses() {
            document.querySelectorAll('.status-select').forEach(select => {
                // Set initial color
                const status = select.value;
                select.className = 'form-control status-select';
                select.classList.add(`status-${status}`);

                // Set initial load value if not OPERASI
                const unitNumber = select.name.replace('status', '');
                const bebanInput = document.getElementById(`beban${unitNumber}`);

                if (status !== 'OPERASI') {
                    bebanInput.value = '0';
                    bebanInput.readOnly = true;
                }

                // Add change event
                select.addEventListener('change', handleStatusChange);
            });
        }

        // Handle status change
        function handleStatusChange() {
            const status = this.value;
            const unitNumber = this.name.replace('status', '');
            const bebanInput = document.getElementById(`beban${unitNumber}`);

            // Update color
            this.className = 'form-control status-select';
            this.classList.add(`status-${status}`);

            // Update load input
            if (status !== 'OPERASI') {
                bebanInput.value = '0';
                bebanInput.readOnly = true;
            } else {
                bebanInput.readOnly = false;
            }

            calculateTotalMW();
        }

        // Toggle stock energy section
        function toggleStockEnergySection() {
            const energyInputs = document.querySelectorAll('#stockEnergySection input');
            const is0700WITA = waktuSelect.value === '07.00 WITA';

            stockEnergySection.style.display = is0700WITA ? 'block' : 'none';

            energyInputs.forEach(input => {
                input.disabled = !is0700WITA;
                input.required = is0700WITA;
                if (!is0700WITA) input.value = '';
            });
        }

        // Calculate surplus
        function calculateSurplus() {
            const bp = parseFloat(bpInput.value) || 0;
            surplusInput.value = Math.round(DMP_VALUE - bp);
        }

        // Calculate total MW
        function calculateTotalMW() {
            let total = 0;
            document.querySelectorAll('.beban-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            const totalMW = total.toFixed(2);
            totalMWDisplay.textContent = totalMW;
            bpInput.value = totalMW;
            calculateSurplus();
        }

        // Event listeners
        waktuSelect.addEventListener('change', toggleStockEnergySection);
        bpInput.addEventListener('input', calculateSurplus);

        // Delegated event for dynamic beban inputs
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('beban-input')) {
                calculateTotalMW();
            }
        });
    </script>
</body>

</html>