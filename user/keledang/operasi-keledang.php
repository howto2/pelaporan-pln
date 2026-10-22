<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["location"] !== 'keledang') {
    header("Location: ../../index.php");
    exit();
}

$koneksi_path = __DIR__ . '/../../koneksi.php';
if (!file_exists($koneksi_path)) {
    die("File koneksi tidak ditemukan di: " . $koneksi_path);
} else {
    require_once $koneksi_path;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $report_date = $_POST['tanggal'];
    $report_time = $_POST['waktu'];
    $location = 'keledang';

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
        'Mesin Unit #7'
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
    // Replace the existing pltd_parameters insert statement with this:
    $stmt = $conn->prepare("INSERT INTO pltd_parameters 
    (location, dts, dmn, dmp, bp, surplus, updated_by)  
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
    dts = VALUES(dts),
    dmn = VALUES(dmn),
    dmp = VALUES(dmp),
    bp = VALUES(bp),
    surplus = VALUES(surplus),
    updated_by = VALUES(updated_by),
    updated_at = CURRENT_TIMESTAMP");

    if (!$stmt) {
    throw new Exception("Prepare failed: " . $conn->error);
    }

    $location = 'keledang';
    $bp = $_POST['bp'];
    $surplus = $_POST['surplus'];
    $stmt->bind_param(
    "sdddddi",
    $location,
    $_POST['dts'],
    $_POST['dmn'],
    $_POST['dmp'],
    $bp,
    $surplus,
    $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
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

        $notes_combined = "Laporan operasi PLTD KELEDANG";
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

        // Dalam blok try, setelah menyimpan unit statuses dan sebelum commit
        // Insert personil piket
        $stmt = $conn->prepare("INSERT INTO personil_piket (report_id, jabatan, nama_personil) VALUES (?, ?, ?)");

        // Insert pengawas
        $jabatan = 'PENGAWAS';
        $stmt->bind_param("iss", $report_id, $jabatan, $_POST['pengawas']);
        $stmt->execute();

        // Insert pelaksana 1
        $jabatan = 'PELAKSANA';
        $stmt->bind_param("iss", $report_id, $jabatan, $_POST['pelaksana1']);
        $stmt->execute();

        // Insert pelaksana 2
        $stmt->bind_param("iss", $report_id, $jabatan, $_POST['pelaksana2']);
        $stmt->execute();

        // Insert pelaksana 3
        $stmt->bind_param("iss", $report_id, $jabatan, $_POST['pelaksana3']);
        $stmt->execute();

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
        header("Location: kirim-wa-keledang.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Gagal mengirim laporan: " . $e->getMessage();
        header("Location: kirim-wa-keledang.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLTD KELEDANG</title>
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
        <h5 class="mb-0 font-weight-bold text-dark">PT PLN (PERSERO) PLTD KELEDANG</h5>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <div class="header">
                <h2>PLTD KELEDANG</h2>
            </a>
        </div>

        <form method="POST" action="operasi-keledang.php">
            <div class="datetime-section">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-grup">
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
                            <th class="text-center">PELAKSANA (3 ORANG)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="3">
                                <select class="form-control" name="pengawas" required>
                                    <option value="">Pilih Pengawas</option>
                                    <option value="M.IRVAN S.J">M.IRVAN S.J</option>
                                    <option value="M ZAKKI NABILAH">M ZAKKI NABILAH</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-control" name="pelaksana1" required>
                                    <option value="">Pilih Pelaksana 1</option>
                                    <option value="AGUNG RIYANTO">AGUNG RIYANTO</option>
                                    <option value="AGUS PRASETYO">AGUS PRASETYO</option>
                                    <option value="ADE HARYANTO">ADE HARYANTO</option>
                                    <option value="WENDI ANUGRAH A">WENDI ANUGRAH A</option>
                                    <option value="BUDIONO">BUDIONO</option>
                                    <option value="SETIAN JORDI">SETIAN JORDI</option>
                                    <option value="AHMAD FURQON">AHMAD FURQON</option>
                                    <option value="ACHMAD VERDI V.">ACHMAD VERDI V.</option>
                                    <option value="P KURNIAWAN SANDI">P KURNIAWAN SANDI</option>
                                    <option value="BASTIANSYAH">BASTIANSYAH</option>
                                    <option value="PURWOKO">PURWOKO</option>
                                    <option value="SAMAD ABDULLAH">SAMAD ABDULLAH</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select class="form-control" name="pelaksana2" required>
                                    <option value="">Pilih Pelaksana 2</option>
                                    <option value="AGUNG RIYANTO">AGUNG RIYANTO</option>
                                    <option value="AGUS PRASETYO">AGUS PRASETYO</option>
                                    <option value="WENDI ANUGRAH A.">WENDI ANUGRAH A.</option>
                                    <option value="ADE HARYANTO">ADE HARYANTO</option>
                                    <option value="BUDIONO">BUDIONO</option>
                                    <option value="SETIAN JORDI">SETIAN JORDI</option>
                                    <option value="AHMAD FURQON">AHMAD FURQON</option>
                                    <option value="ACHMAD VERDI V.">ACHMAD VERDI V.</option>
                                    <option value="P KURNIAWAN SANDI">P KURNIAWAN SANDI</option>
                                    <option value="BASTIANSYAH">BASTIANSYAH</option>
                                    <option value="PURWOKO">PURWOKO</option>
                                    <option value="SAMAD ABDULLAH">SAMAD ABDULLAH</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select class="form-control" name="pelaksana3" required>
                                    <option value="">Pilih Pelaksana 3</option>
                                    <option value="SETIAN JORDI">SETIAN JORDI</option>
                                    <option value="AHMAD FURQON">AHMAD FURQON</option>
                                    <option value="ACHMAD VERDI V.">ACHMAD VERDI V.</option>
                                    <option value="BUDIONO">BUDIONO</option>
                                    <option value="AGUNG RIYANTO">AGUNG RIYANTO</option>
                                    <option value="AGUS PRASETYO">AGUS PRASETYO</option>
                                    <option value="ADE HARYANTO">ADE HARYANTO</option>
                                    <option value="WENDI ANUGRAH A">WENDI ANUGRAH A</option>
                                    <option value="P KURNIAWAN SANDI">P KURNIAWAN SANDI</option>
                                    <option value="BASTIANSYAH">BASTIANSYAH</option>
                                    <option value="PURWOKO">PURWOKO</option>
                                    <option value="SAMAD ABDULLAH">SAMAD ABDULLAH</option>
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
                        <!-- Mesin 1 -->
                        <tr>
                            <td>Mesin Unit #1</td>
                            <td>
                                <select class="form-control status-select" id="status1" name="status1" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" id="beban1"
                                    name="beban1" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" id="tes1" name="tes1" placeholder="Tanggal">
                            </td>
                            <td><textarea class="form-control" rows="1" name="catatan1"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- Mesin 2 -->
                        <tr>
                            <td>Mesin Unit #2</td>
                            <td>
                                <select class="form-control status-select" id="status2" name="status2" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" id="beban2"
                                    name="beban2" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" id="tes2" name="tes2" placeholder="Tanggal">
                            </td>
                            <td><textarea class="form-control" rows="1" name="catatan2"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- MAK 1 -->
                        <tr>
                            <td>Mesin Unit #3</td>
                            <td>
                                <select class="form-control status-select" id="status3" name="status3" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" id="beban3"
                                    name="beban3" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" id="tes3" name="tes3" placeholder="Tanggal">
                            </td>
                            <td><textarea class="form-control" rows="1" name="catatan3"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- MAK 2 -->
                        <tr>
                            <td>Mesin Unit #4</td>
                            <td>
                                <select class="form-control status-select" id="status4" name="status4" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" id="beban4"
                                    name="beban4" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" id="tes4" name="tes4" placeholder="Tanggal">
                            </td>
                            <td><textarea class="form-control" rows="1" name="catatan4"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- MAK 3 -->
                        <tr>
                            <td>Mesin Unit #5</td>
                            <td>
                                <select class="form-control status-select" id="status5" name="status5" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" id="beban5"
                                    name="beban5" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" id="tes5" name="tes5" placeholder="Tanggal">
                            </td>
                            <td><textarea class="form-control" rows="1" name="catatan5"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <!-- MAK 4 -->
                        <tr>
                            <td>Mesin Unit #6</td>
                            <td>
                                <select class="form-control status-select" id="status6" name="status6" required>
                                    <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                    <option value="RS" class="status-RS">RS</option>
                                    <option value="RU" class="status-RU">RU</option>
                                    <option value="FO" class="status-FO">FO</option>
                                    <option value="PO" class="status-PO">PO</option>
                                    <option value="MO" class="status-MO">MO</option>
                                    <option value="SF" class="status-SF">SF</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control beban-input" id="beban6"
                                    name="beban6" placeholder="MW" required></td>
                            <td><input type="text" class="form-control" id="tes6" name="tes6" placeholder="Tanggal">
                            </td>
                            <td><textarea class="form-control" rows="1" name="catatan6"
                                    placeholder="Catatan"></textarea>
                            </td>
                        </tr>

                        <td>Mesin Unit #7</td>
                        <td>
                            <select class="form-control status-select" id="status7" name="status7" required>
                                <option value="OPERASI" class="status-OPERASI">OPERASI</option>
                                <option value="RS" class="status-RS">RS</option>
                                <option value="RU" class="status-RU">RU</option>
                                <option value="FO" class="status-FO">FO</option>
                                <option value="PO" class="status-PO">PO</option>
                                <option value="MO" class="status-MO">MO</option>
                                <option value="SF" class="status-SF">SF</option>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" class="form-control beban-input" id="beban7" name="beban7"
                                placeholder="MW" required></td>
                        <td><input type="text" class="form-control" id="tes7" name="tes7" placeholder="Tanggal"></td>
                        <td><textarea class="form-control" rows="1" name="catatan7" placeholder="Catatan"></textarea>
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
                    <h4 class="text-center mb-4">PLTD KELEDANG</h4>

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
                                    <?php
                                    // Ambil data parameter dari database
                                    $paramQuery = "SELECT * FROM pltd_parameters WHERE location = 'keledang' LIMIT 1";
                                    $paramResult = $conn->query($paramQuery);
                                    
                                    if ($paramResult->num_rows > 0) {
                                        $param = $paramResult->fetch_assoc();
                                    } else {
                                        // Default values jika belum ada data
                                        $param = [
                                            'dts' => 0,
                                            'dmn' => 0,
                                            'dmp' => 0
                                        ];
                                    }
                                    ?>
                                    <tr>
                                        <td class="font-weight-bold">DTS (Beban Total Sistem)</td>
                                        <td class="text-right"><?php echo $param['dts']; ?></td>
                                        <td class="text-center">MW</td>
                                        <input type="hidden" name="dts" value="<?php echo $param['dts']; ?>">
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">DMN (Beban Minimum)</td>
                                        <td class="text-right"><?php echo $param['dmn']; ?></td>
                                        <td class="text-center">MW</td>
                                        <input type="hidden" name="dmn" value="<?php echo $param['dmn']; ?>">
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">DMP (Beban Puncak)</td>
                                        <td class="text-right"><?php echo $param['dmp']; ?></td>
                                        <td class="text-center">MW</td>
                                        <input type="hidden" name="dmp" value="<?php echo $param['dmp']; ?>">
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">BP (Beban Pembangkit)</td>
                                        <td><input type="number" step="1" class="form-control text-right" id="bp"
                                                name="bp" placeholder="0.00" required readonly value="<?php echo isset($param['bp']) ? $param['bp'] : 0; ?>"></td>
                                        <td class="text-center">MW</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">SURPLUS</td>
                                        <td><input type="number" step="1" class="form-control text-right" id="surplus"
                                                name="surplus" placeholder="0" required readonly value="<?php echo isset($param['surplus']) ? $param['surplus'] : 0; ?>"></td>
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
        // Set tanggal hari ini otomatis


        // Auto-fill hari based on tanggal
        const inputTanggal = document.getElementById('tanggal');
        const inputHari = document.getElementById('hari');

        inputTanggal.addEventListener('change', function () {
            const tanggal = new Date(this.value);
            const options = { weekday: 'long' };
            const namaHari = tanggal.toLocaleDateString('id-ID', options);
            inputHari.value = namaHari.charAt(0).toUpperCase() + namaHari.slice(1);
        });

        // Update status select colors when changed and set beban to 0 if not OPERASI
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function () {
                // Remove all status classes
                this.className = 'form-control status-select';

                // Add the appropriate status class
                const status = this.value;
                this.classList.add(`status-${status}`);

                // Get the unit number from select ID (status1, status2, etc)
                const unitNumber = this.id.replace('status', '');
                const bebanInput = document.getElementById(`beban${unitNumber}`);

                // If status is not OPERASI, set beban to 0
                if (status !== 'OPERASI') {
                    bebanInput.value = '0';
                }

                // Recalculate total MW
                calculateTotalMW();
            });

            // Initialize with correct color
            const status = select.value;
            select.classList.add(`status-${status}`);
        });

        // Initialize beban values based on initial status
        document.querySelectorAll('.status-select').forEach(select => {
            const unitNumber = select.id.replace('status', '');
            const bebanInput = document.getElementById(`beban${unitNumber}`);

            if (select.value !== 'OPERASI') {
                bebanInput.value = '0';
            }
        });

        // Function to toggle stock energy section visibility
        // Function to toggle stock energy section visibility and required attribute
        function toggleStockEnergySection() {
            const waktuSelect = document.getElementById('waktu');
            const stockEnergySection = document.getElementById('stockEnergySection');
            const energyInputs = document.querySelectorAll('#stockEnergySection input');

            if (waktuSelect.value === '07.00 WITA') {
                stockEnergySection.style.display = 'block';
                energyInputs.forEach(input => {
                    input.disabled = false;
                    input.required = true;
                });
            } else {
                stockEnergySection.style.display = 'none';
                energyInputs.forEach(input => {
                    input.disabled = true;
                    input.required = false;
                    input.value = ''; // Clear values when hidden
                });
            }
        }

        // Event listener untuk waktu
        document.getElementById('waktu').addEventListener('change', toggleStockEnergySection);

        // Panggil saat halaman dimuat
        window.onload = function () {
            calculateTotalMW();
            toggleStockEnergySection(); // Tambahkan ini
        };

        // Function to calculate surplus
        // Function to calculate surplus
        function calculateSurplus() {
            const bp = parseFloat(document.getElementById('bp').value) || 0;
            const dmp = parseFloat(document.querySelector('input[name="dmp"]').value) || 0;
            const surplus = Math.round(dmp - bp);
            document.getElementById('surplus').value = surplus;
            
            // Update the hidden surplus field if it exists
            const hiddenSurplus = document.querySelector('input[name="surplus"][type="hidden"]');
            if (hiddenSurplus) {
                hiddenSurplus.value = surplus;
            }
        }

        // Function to calculate total MW
        function calculateTotalMW() {
            let total = 0;
            // Get all beban inputs
            document.querySelectorAll('.beban-input').forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });

            const totalMW = total.toFixed(2);
            document.getElementById('totalMW').textContent = totalMW;

            // Update BP (Beban Pembangkit) dengan nilai total yang sama
            document.getElementById('bp').value = totalMW;

            // Hitung surplus
            calculateSurplus();
        }

        // Event listener untuk BP
        document.getElementById('bp').addEventListener('input', calculateSurplus);

        // Event listeners untuk beban mesin
        document.querySelectorAll('.beban-input').forEach(input => {
            input.addEventListener('input', calculateTotalMW);
        });

        // Event listeners untuk status mesin
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function () {
                // Remove all status classes
                this.className = 'form-control status-select';

                // Add the appropriate status class
                const status = this.value;
                this.classList.add(`status-${status}`);

                // Get the unit number from select ID (status1, status2, etc)
                const unitNumber = this.id.replace('status', '');
                const bebanInput = document.getElementById(`beban${unitNumber}`);

                // If status is not OPERASI, set beban to 0
                if (status !== 'OPERASI') {
                    bebanInput.value = '0';
                }

                // Recalculate total MW
                calculateTotalMW();
            });
        });

        // Panggil fungsi saat halaman dimuat untuk mengisi nilai awal
        window.onload = function () {
            calculateTotalMW();
        };
    </script>
</body>

</html>