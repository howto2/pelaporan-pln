<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) || !isset($_SESSION["location"]) || $_SESSION["location"] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Operasi PLTD KELEDANG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .header {
            background-color: #0056b3;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .btn-download {
            margin-top: 10px;
        }

        .back-btn {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="pelaporan-operasi-admin.php" class="btn btn-secondary back-btn">Kembali ke Daftar Unit</a>

        <div class="header">
            <h4>Sistem Pelaporan Operasi - PT PLN (Persero)</h4>
            <h2>Laporan Operasi PLTG PEAKING</h2>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Total Beban (MW)</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT o.*, u.username 
                              FROM operational_reports o 
                              JOIN users u ON o.user_id = u.id_user 
                              WHERE u.Location = 'peaking' 
                              ORDER BY o.report_date DESC, o.report_time DESC";
                    $result = $conn->query($query);

                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $no++ . '</td>';
                        echo '<td>' . $row['report_date'] . '</td>';
                        echo '<td>' . $row['report_time'] . '</td>';
                        echo '<td>' . $row['total_mw'] . ' MW</td>';
                        echo '<td>' . ($row['notes'] ? substr($row['notes'], 0, 50) . (strlen($row['notes']) > 50 ? '...' : '') : '-') . '</td>';
                        echo '<td><a href="generate_detail_laporan.php?report_id=' . $row['report_id'] . '" class="btn btn-primary btn-sm">Unduh PDF</a></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>