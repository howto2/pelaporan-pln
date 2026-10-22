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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #3a7bd5;
            --accent-color: #00d2ff;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 86, 179, 0.15);
        }

        .header h4 {
            font-weight: 300;
            margin-bottom: 5px;
        }

        .header h2 {
            font-weight: 600;
            margin-bottom: 0;
        }

        .table-responsive {
            margin-top: 25px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .table th {
            font-weight: 500;
            padding: 15px;
            border-bottom: none;
        }

        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 210, 255, 0.03);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 86, 179, 0.05);
            transition: all 0.2s ease;
        }

        .back-btn {
            margin-bottom: 25px;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .action-btns {
            white-space: nowrap;
        }

        .btn {
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #004494;
            border-color: #004494;
            transform: translateY(-1px);
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
            transform: translateY(-1px);
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #e0a800;
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-1px);
        }

        .btn i {
            margin-right: 5px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .badge-warning {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .badge-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }

        /* Gaya untuk container parameter */
    .parameter-container {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    /* Judul section */
    .section-title {
        color: #2c3e50;
        font-weight: 600;
        font-size: 1.1rem;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
    }
    
    /* Header tabel */
    .parameter-header {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 500;
    }
    
    .parameter-header th {
        border-bottom: 2px solid #dee2e6;
        padding: 12px 15px;
    }
    
    /* Nama parameter */
    .parameter-name {
        font-weight: 500;
        color: #34495e;
        padding: 12px 15px;
    }
    
    /* Tombol edit */
    .btn-edit {
        color: #3498db;
        border: 1px solid #3498db;
        border-radius: 4px;
        padding: 5px 10px;
        transition: all 0.3s ease;
    }
    
    .btn-edit:hover {
        background-color: #3498db;
        color: white;
    }
    
    /* Baris tabel */
    .parameter-table tbody tr {
        transition: background-color 0.2s;
    }
    
    .parameter-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .parameter-table tbody td {
        padding: 12px 15px;
        border-bottom: 1px solid #f1f3f5;
    }

    /* Filter */
    .filter-card {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 20px;
    }

    .filter-card .card-body {
        padding: 15px;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
    }
    </style>
</head>

<body>
    <div class="container">
        <a href="pelaporan-operasi-admin.php" class="btn btn-secondary back-btn">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Unit
        </a>

        <div class="header">
            <h4><i class="bi bi-lightning-charge"></i> Sistem Pelaporan Operasi - PT PLN (Persero)</h4>
            <h2><i class="bi bi-file-earmark-text"></i> Laporan Operasi PLTD KELEDANG</h2>
        </div>

        <!-- Tabel Parameter - Desain Minimalis Elegan -->
        <div class="parameter-container mb-4">
            <h4 class="section-title mb-3">
                <i class="bi bi-speedometer2"></i> Parameter Operasional
            </h4>
            <div class="parameter-table">
                <table class="table">
                    <thead>
                        <tr class="parameter-header">
                            <th>Parameter</th>
                            <th class="text-end">Nilai</th>
                            <th>Satuan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $paramQuery = "SELECT * FROM pltd_parameters WHERE location = 'keledang' LIMIT 1";
                        $paramResult = $conn->query($paramQuery);
                        $param = $paramResult->num_rows > 0 ? $paramResult->fetch_assoc() : [
                            'dts' => 16000,
                            'dmn' => 10000, 
                            'dmp' => 9000
                        ];
                        ?>
                        <tr>
                            <td class="parameter-name">DTS (Beban Total Sistem)</td>
                            <td class="text-end"><?= number_format($param['dts'], 2) ?></td>
                            <td>MW</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editParamModal" 
                                        data-param="dts" data-value="<?= $param['dts'] ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="parameter-name">DMN (Beban Minimum)</td>
                            <td class="text-end"><?= number_format($param['dmn'], 2) ?></td>
                            <td>MW</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editParamModal" 
                                        data-param="dmn" data-value="<?= $param['dmn'] ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="parameter-name">DMP (Beban Puncak)</td>
                            <td class="text-end"><?= number_format($param['dmp'], 2) ?></td>
                            <td>MW</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editParamModal" 
                                        data-param="dmp" data-value="<?= $param['dmp'] ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


<div class="parameter-container mb-4">
    <h4 class="section-title mb-3">
        <i class="bi bi-archive"></i> Data Pelaporan
    </h4>
    <!-- Form Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="filter_date" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="filter_date" name="filter_date" 
                           value="<?= isset($_GET['filter_date']) ? $_GET['filter_date'] : '' ?>">
                </div>
                <div class="col-md-4">
                    <label for="filter_time" class="form-label">Waktu Laporan</label>
                    <select class="form-select" id="filter_time" name="filter_time">
                        <option value="">Semua Waktu</option>
                        <option value="07.00 WITA" <?= (isset($_GET['filter_time']) && $_GET['filter_time'] == '07.00 WITA') ? 'selected' : '' ?>>07:00 WITA</option>
                        <option value="12.00 WITA" <?= (isset($_GET['filter_time']) && $_GET['filter_time'] == '12.00 WITA') ? 'selected' : '' ?>>12:00 WITA</option>
                        <option value="15.00 WITA" <?= (isset($_GET['filter_time']) && $_GET['filter_time'] == '15.00 WITA') ? 'selected' : '' ?>>15:00 WITA</option>
                        <option value="19.00 WITA" <?= (isset($_GET['filter_time']) && $_GET['filter_time'] == '19.00 WITA') ? 'selected' : '' ?>>19:00 WITA</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="laporan_keledang.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="parameter-table">
        <table class="table table-striped table-hover">
            <thead>
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
                // Query dengan filter
                $query = "SELECT o.*, u.username 
                          FROM operational_reports o 
                          JOIN users u ON o.user_id = u.id_user 
                          WHERE u.Location = 'keledang'";

                // Tambahkan filter tanggal jika ada
                if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
                    $filter_date = $conn->real_escape_string($_GET['filter_date']);
                    $query .= " AND o.report_date = '$filter_date'";
                }

                // Tambahkan filter waktu jika ada
                if (isset($_GET['filter_time']) && !empty($_GET['filter_time'])) {
                    $filter_time = $conn->real_escape_string($_GET['filter_time']);
                    $query .= " AND o.report_time = '$filter_time'";
                }

                $query .= " ORDER BY o.report_date DESC, o.report_time DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $no++ . '</td>';
                        echo '<td>' . date('d M Y', strtotime($row['report_date'])) . '</td>';
                        echo '<td>' . $row['report_time'] . '</td>';
                        echo '<td><span class="badge bg-primary">' . $row['total_mw'] . ' MW</span></td>';
                        echo '<td>' . ($row['notes'] ? '<span class="text-truncate d-inline-block" style="max-width: 200px;" title="' . htmlspecialchars($row['notes']) . '">' . 
                             htmlspecialchars(substr($row['notes'], 0, 50)) . (strlen($row['notes']) > 50 ? '...' : '') . '</span>' : '-') . '</td>';
                        echo '<td class="action-btns">';
                        echo '<a href="detail_laporan_keledang.php?report_id=' . $row['report_id'] . '" class="btn btn-info btn-sm"><i class="bi bi-eye"></i> Lihat</a> ';
                        echo '<a href="hapus_laporan.php?report_id=' . $row['report_id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus laporan ini?\')"><i class="bi bi-trash"></i> Hapus</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6" class="empty-state"><i class="bi bi-file-earmark-excel"></i><br>Tidak ada data laporan</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Parameter -->
<div class="modal fade" id="editParamModal" tabindex="-1" aria-labelledby="editParamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="update_parameter.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editParamModalLabel">Edit Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="param_name" id="paramName">
                    <div class="mb-3">
                        <label for="paramValue" class="form-label">Nilai</label>
                        <input type="number" step="0.01" class="form-control" id="paramValue" name="param_value" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script untuk modal edit parameter
    document.addEventListener('DOMContentLoaded', function() {
        var editParamModal = document.getElementById('editParamModal');
        editParamModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var param = button.getAttribute('data-param');
            var value = button.getAttribute('data-value');
            
            var modalTitle = editParamModal.querySelector('.modal-title');
            var paramNameInput = editParamModal.querySelector('#paramName');
            var paramValueInput = editParamModal.querySelector('#paramValue');
            
            // Set judul modal berdasarkan parameter
            var paramLabel = '';
            switch(param) {
                case 'dts': paramLabel = 'DTS (Beban Total Sistem)'; break;
                case 'dmn': paramLabel = 'DMN (Beban Minimum)'; break;
                case 'dmp': paramLabel = 'DMP (Beban Puncak)'; break;
            }
            
            modalTitle.textContent = 'Edit ' + paramLabel;
            paramNameInput.value = param;
            paramValueInput.value = value;
        });
    });
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tooltip initialization
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>

</html>
