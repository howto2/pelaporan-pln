<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) || !isset($_SESSION["location"]) || $_SESSION["location"] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once 'koneksi.php';

// Get report ID and location from URL
$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;
$location = isset($_GET['location']) ? $_GET['location'] : '';

// Validate location
$valid_locations = ['keledang', 'bontang', 'karang_asam', 'peaking', 'sambera', 'tanjung_batu'];
if (!in_array($location, $valid_locations)) {
    header("Location: pelaporan-operasi-admin.php");
    exit();
}

// Fetch report data
$report = [];
$unit_statuses = [];
$energy_stocks = [];
$personil_piket = [];

// Get operational report
$report_query = "SELECT * FROM operational_reports WHERE report_id = ?";
$stmt = $conn->prepare($report_query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $report_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$report_result = $stmt->get_result();
$report = $report_result->fetch_assoc();

if (!$report) {
    header("Location: pelaporan-operasi-admin.php");
    exit();
}

// Parse notes to get DTS, DMN, DMP values
$dts = $dmn = $dmp = 0;
if (preg_match('/DTS:(\d+)/', $report['notes'], $matches)) {
    $dts = intval($matches[1]);
}
if (preg_match('/DMN:(\d+)/', $report['notes'], $matches)) {
    $dmn = intval($matches[1]);
}
if (preg_match('/DMP:(\d+)/', $report['notes'], $matches)) {
    $dmp = intval($matches[1]);
}

// Get additional notes (excluding DTS, DMN, DMP)
$additional_notes = preg_replace('/DTS:\d+ DMN:\d+ DMP:\d+/', '', $report['notes']);

// Fetch unit statuses
$unit_status_query = "SELECT * FROM unit_statuses WHERE report_id = ?";
$stmt = $conn->prepare($unit_status_query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $report_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$unit_status_result = $stmt->get_result();
while ($row = $unit_status_result->fetch_assoc()) {
    $unit_statuses[] = $row;
}

// Fetch energy stocks
$energy_stock_query = "SELECT * FROM energy_stocks WHERE report_id = ?";
$stmt = $conn->prepare($energy_stock_query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $report_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$energy_stock_result = $stmt->get_result();
while ($row = $energy_stock_result->fetch_assoc()) {
    $energy_stocks[] = $row;
}

// Fetch personil piket
$personil_query = "SELECT * FROM personil_piket WHERE report_id = ?";
$stmt = $conn->prepare($personil_query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $report_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$personil_result = $stmt->get_result();
while ($row = $personil_result->fetch_assoc()) {
    $personil_piket[] = $row;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $report_date = $_POST['report_date'];
    $report_time = $_POST['report_time'];
    $cuaca = $_POST['cuaca'];
    $total_mw = $_POST['total_mw'];
    
    // Construct notes
    $notes = "DTS:" . $_POST['dts'] . " DMN:" . $_POST['dmn'] . " DMP:" . $_POST['dmp'];
    if (!empty($_POST['additional_notes'])) {
        $notes .= " " . trim($_POST['additional_notes']);
    }

    // Update operational_reports table
    $update_report_query = "UPDATE operational_reports SET 
                          report_date = ?, 
                          report_time = ?, 
                          Cuaca = ?, 
                          total_mw = ?, 
                          notes = ?
                          WHERE report_id = ?";
    $stmt = $conn->prepare($update_report_query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sssdsi", $report_date, $report_time, $cuaca, $total_mw, $notes, $report_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    // Update unit statuses if they exist
    if (!empty($_POST['unit_status']) && is_array($_POST['unit_status'])) {
        foreach ($_POST['unit_status'] as $status_id => $data) {
            $update_unit_query = "UPDATE unit_statuses SET 
                                unit_number = ?,
                                status = ?,
                                load_mw = ?,
                                last_operation = ?,
                                notes = ?
                                WHERE status_id = ? AND report_id = ?";
            $stmt = $conn->prepare($update_unit_query);
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ssdssii", 
                $data['unit_number'],
                $data['status'],
                $data['load_mw'],
                $data['last_operation'],
                $data['notes'],
                $status_id,
                $report_id
            );
            if (!$stmt->execute()) {
                die("Execute failed: " . $stmt->error);
            }
        }
    }

    // Update energy stocks if they exist
    if (!empty($_POST['energy_stock']) && is_array($_POST['energy_stock'])) {
        foreach ($_POST['energy_stock'] as $stock_id => $data) {
            $update_stock_query = "UPDATE energy_stocks SET 
                                 received = ?,
                                 used = ?,
                                 stock = ?,
                                 HOP = ?
                                 WHERE stock_id = ? AND report_id = ?";
            $stmt = $conn->prepare($update_stock_query);
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ddddii", 
                $data['received'],
                $data['used'],
                $data['stock'],
                $data['HOP'],
                $stock_id,
                $report_id
            );
            if (!$stmt->execute()) {
                die("Execute failed: " . $stmt->error);
            }
        }
    }

    // Update personil piket - delete existing and insert new
    $delete_personil_query = "DELETE FROM personil_piket WHERE report_id = ?";
    $stmt = $conn->prepare($delete_personil_query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $report_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    // Insert pengawas
    $insert_personil_query = "INSERT INTO personil_piket (report_id, nama_personil, jabatan) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_personil_query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("iss", $report_id, $_POST['pengawas'], 'PENGAWAS');
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    // Insert pelaksana
    $pelaksana_names = [$_POST['pelaksana1'], $_POST['pelaksana2'], $_POST['pelaksana3']];
    foreach ($pelaksana_names as $nama) {
        $stmt->bind_param("iss", $report_id, $nama, 'PELAKSANA');
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
    }

    // Redirect back to the appropriate laporan page
    header("Location: laporan_$location.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan Operasional - <?php echo strtoupper(str_replace('_', ' ', $location)); ?></title>
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

        .form-section {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

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
    </style>
</head>

<body>
    <!-- Header Logo & Judul -->
    <div class="sticky-header">
        <img src="img/pln.png" alt="Logo PLN" height="60">
        <h5 class="mb-0 font-weight-bold text-dark">EDIT LAPORAN OPERASI - <?php echo strtoupper(str_replace('_', ' ', $location)); ?></h5>
    </div>

    <div class="container">
        <form method="POST" action="">
            <input type="hidden" name="report_id" value="<?php echo $report_id; ?>">
            <input type="hidden" name="location" value="<?php echo $location; ?>">

            <div class="form-section">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="report_date">TANGGAL</label>
                            <input type="date" class="form-control" id="report_date" name="report_date" 
                                   value="<?php echo $report['report_date']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="report_time">WAKTU</label>
                            <select class="form-control" id="report_time" name="report_time" required>
                                <option value="07.00 WITA" <?php echo $report['report_time'] == '07.00 WITA' ? 'selected' : ''; ?>>07.00 WITA</option>
                                <option value="12.00 WITA" <?php echo $report['report_time'] == '12.00 WITA' ? 'selected' : ''; ?>>12.00 WITA</option>
                                <option value="15.00 WITA" <?php echo $report['report_time'] == '15.00 WITA' ? 'selected' : ''; ?>>15.00 WITA</option>
                                <option value="19.00 WITA" <?php echo $report['report_time'] == '19.00 WITA' ? 'selected' : ''; ?>>19.00 WITA</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cuaca">CUACA</label>
                            <select class="form-control" id="cuaca" name="cuaca" required>
                                <option value="Cerah" <?php echo $report['Cuaca'] == 'Cerah' ? 'selected' : ''; ?>>Cerah</option>
                                <option value="Berawan" <?php echo $report['Cuaca'] == 'Berawan' ? 'selected' : ''; ?>>Berawan</option>
                                <option value="Mendung" <?php echo $report['Cuaca'] == 'Mendung' ? 'selected' : ''; ?>>Mendung</option>
                                <option value="Hujan Lebat" <?php echo $report['Cuaca'] == 'Hujan Lebat' ? 'selected' : ''; ?>>Hujan Lebat</option>
                                <option value="Hujan" <?php echo $report['Cuaca'] == 'Hujan' ? 'selected' : ''; ?>>Hujan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_mw">TOTAL BEBAN (MW)</label>
                            <input type="number" step="0.01" class="form-control" id="total_mw" name="total_mw" 
                                   value="<?php echo $report['total_mw']; ?>" required>
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
                        <?php
                        $pengawas = array_filter($personil_piket, function($p) { return $p['jabatan'] == 'PENGAWAS'; });
                        $pelaksana = array_filter($personil_piket, function($p) { return $p['jabatan'] == 'PELAKSANA'; });
                        ?>
                        <tr>
                            <td rowspan="3">
                                <select class="form-control" name="pengawas" required>
                                    <option value="">Pilih Pengawas</option>
                                    <option value="M.IRVAN S.J" <?php echo isset($pengawas[0]) && $pengawas[0]['nama_personil'] == 'M.IRVAN S.J' ? 'selected' : ''; ?>>M.IRVAN S.J</option>
                                    <option value="M ZAKKI NABILAH" <?php echo isset($pengawas[0]) && $pengawas[0]['nama_personil'] == 'M ZAKKI NABILAH' ? 'selected' : ''; ?>>M ZAKKI NABILAH</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-control" name="pelaksana1" required>
                                    <option value="">Pilih Pelaksana 1</option>
                                    <?php
                                    $pelaksana_list = [
                                        'AGUNG RIYANTO', 'AGUS PRASETYO', 'ADE HARYANTO', 'WENDI ANUGRAH A', 
                                        'BUDIONO', 'SETIAN JORDI', 'AHMAD FURQON', 'ACHMAD VERDI V.', 
                                        'P KURNIAWAN SANDI', 'BASTIANSYAH', 'PURWOKO', 'SAMAD ABDULLAH'
                                    ];
                                    
                                    foreach ($pelaksana_list as $p) {
                                        $selected = (isset($pelaksana[0]) && $pelaksana[0]['nama_personil'] == $p) ? 'selected' : '';
                                        echo "<option value=\"$p\" $selected>$p</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select class="form-control" name="pelaksana2" required>
                                    <option value="">Pilih Pelaksana 2</option>
                                    <?php
                                    foreach ($pelaksana_list as $p) {
                                        $selected = (isset($pelaksana[1]) && $pelaksana[1]['nama_personil'] == $p) ? 'selected' : '';
                                        echo "<option value=\"$p\" $selected>$p</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select class="form-control" name="pelaksana3" required>
                                    <option value="">Pilih Pelaksana 3</option>
                                    <?php
                                    foreach ($pelaksana_list as $p) {
                                        $selected = (isset($pelaksana[2]) && $pelaksana[2]['nama_personil'] == $p) ? 'selected' : '';
                                        echo "<option value=\"$p\" $selected>$p</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Status Unit Section -->
            <div class="form-section">
                <h4 class="text-center mb-4">STATUS UNIT</h4>
                <table class="status-table">
                    <thead>
                        <tr>
                            <th>UNIT</th>
                            <th>STATUS</th>
                            <th>BEBAN (MW)</th>
                            <th>TES KESIAPAN OPERASI</th>
                            <th>CATATAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unit_statuses as $unit): ?>
                            <tr>
                                <td><?php echo $unit['unit_number']; ?></td>
                                <td>
                                    <select class="form-control status-select" name="unit_status[<?php echo $unit['status_id']; ?>][status]" 
                                            onchange="updateStatusColor(this)" required>
                                        <option value="OPERASI" class="status-OPERASI" <?php echo $unit['status'] == 'OPERASI' ? 'selected' : ''; ?>>OPERASI</option>
                                        <option value="RS" class="status-RS" <?php echo $unit['status'] == 'RS' ? 'selected' : ''; ?>>RS</option>
                                        <option value="RU" class="status-RU" <?php echo $unit['status'] == 'RU' ? 'selected' : ''; ?>>RU</option>
                                        <option value="FO" class="status-FO" <?php echo $unit['status'] == 'FO' ? 'selected' : ''; ?>>FO</option>
                                        <option value="PO" class="status-PO" <?php echo $unit['status'] == 'PO' ? 'selected' : ''; ?>>PO</option>
                                        <option value="MO" class="status-MO" <?php echo $unit['status'] == 'MO' ? 'selected' : ''; ?>>MO</option>
                                        <option value="SF" class="status-SF" <?php echo $unit['status'] == 'SF' ? 'selected' : ''; ?>>SF</option>
                                    </select>
                                    <input type="hidden" name="unit_status[<?php echo $unit['status_id']; ?>][unit_number]" 
                                           value="<?php echo $unit['unit_number']; ?>">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control beban-input" 
                                           name="unit_status[<?php echo $unit['status_id']; ?>][load_mw]" 
                                           value="<?php echo $unit['load_mw']; ?>" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control" 
                                           name="unit_status[<?php echo $unit['status_id']; ?>][last_operation]" 
                                           value="<?php echo $unit['last_operation']; ?>">
                                </td>
                                <td>
                                    <textarea class="form-control" rows="1" 
                                              name="unit_status[<?php echo $unit['status_id']; ?>][notes]"><?php echo $unit['notes']; ?></textarea>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Additional Parameters -->
            <div class="form-section">
                <h4 class="text-center mb-4">PARAMETER OPERASI</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="dts">DTS (Beban Total Sistem)</label>
                            <input type="number" class="form-control" id="dts" name="dts" 
                                   value="<?php echo $dts; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="dmn">DMN (Beban Minimum)</label>
                            <input type="number" class="form-control" id="dmn" name="dmn" 
                                   value="<?php echo $dmn; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="dmp">DMP (Beban Puncak)</label>
                            <input type="number" class="form-control" id="dmp" name="dmp" 
                                   value="<?php echo $dmp; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="additional_notes">CATATAN TAMBAHAN</label>
                    <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3"><?php echo trim($additional_notes); ?></textarea>
                </div>
            </div>

            <!-- Energy Stock Section -->
            <div class="form-section">
                <h4 class="text-center mb-4">STOCK ENERGY PRIMER</h4>
                <div class="row">
                    <?php foreach ($energy_stocks as $stock): ?>
                        <div class="col-md-6">
                            <h5 class="text-center bg-primary text-white p-2"><?php echo $stock['fuel_type']; ?></h5>
                            <table class="status-table">
                                <tr>
                                    <td>Penerimaan (Liter)</td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control" 
                                               name="energy_stock[<?php echo $stock['stock_id']; ?>][received]" 
                                               value="<?php echo $stock['received']; ?>" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Pemakaian (Liter)</td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control" 
                                               name="energy_stock[<?php echo $stock['stock_id']; ?>][used]" 
                                               value="<?php echo $stock['used']; ?>" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Stock (Liter)</td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control" 
                                               name="energy_stock[<?php echo $stock['stock_id']; ?>][stock]" 
                                               value="<?php echo $stock['stock']; ?>" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td>HOP (Hari)</td>
                                    <td>
                                        <input type="number" step="1" class="form-control" 
                                               name="energy_stock[<?php echo $stock['stock_id']; ?>][HOP]" 
                                               value="<?php echo $stock['HOP']; ?>" required>
                                    </td>
                                </tr>
                                <input type="hidden" name="energy_stock[<?php echo $stock['stock_id']; ?>][fuel_type]" 
                                       value="<?php echo $stock['fuel_type']; ?>">
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">SIMPAN PERUBAHAN</button>
                <a href="laporan_<?php echo $location; ?>.php" class="btn btn-secondary btn-lg">BATAL</a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Function to update status color
        function updateStatusColor(select) {
            // Remove all status classes
            select.className = 'form-control status-select';
            
            // Add the appropriate status class
            const status = select.value;
            select.classList.add(`status-${status}`);
            
            // If status is not OPERASI, set beban to 0
            if (status !== 'OPERASI') {
                const row = select.closest('tr');
                const bebanInput = row.querySelector('.beban-input');
                if (bebanInput) {
                    bebanInput.value = '0';
                }
            }
        }
        
        // Initialize status colors
        document.querySelectorAll('.status-select').forEach(select => {
            updateStatusColor(select);
        });
        
        // Calculate total MW when beban inputs change
        document.querySelectorAll('.beban-input').forEach(input => {
            input.addEventListener('input', calculateTotalMW);
        });
        
        // Function to calculate total MW
        function calculateTotalMW() {
            let total = 0;
            document.querySelectorAll('.beban-input').forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });
            
            document.getElementById('total_mw').value = total.toFixed(2);
        }
        
        // Initialize total MW calculation
        calculateTotalMW();
    </script>
</body>
</html>