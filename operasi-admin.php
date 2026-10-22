<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLN Power Plant Reporting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pln-blue: #0056b3;
            --pln-dark-blue: #003366;
            --pln-light-blue: #e6f0ff;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand img {
            height: 40px;
        }

        .power-plant-card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            margin-bottom: 20px;
        }

        .power-plant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: var(--pln-blue);
            color: white;
            font-weight: bold;
            padding: 15px;
        }

        .card-body {
            padding: 20px;
            background-color: white;
        }

        .plant-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--pln-blue);
        }

        .btn-pln {
            background-color: var(--pln-blue);
            color: white;
            border: none;
        }

        .btn-pln:hover {
            background-color: var(--pln-dark-blue);
            color: white;
        }

        .report-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .report-header {
            border-bottom: 2px solid var(--pln-blue);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .status-operasi {
            background-color: #28a745;
            color: white;
        }

        .status-rs {
            background-color: #ffc107;
            color: black;
        }

        .status-ru {
            background-color: #6c757d;
            color: white;
        }

        .status-fo {
            background-color: #dc3545;
            color: white;
        }

        .total-mw {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--pln-blue);
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: white !important;
            }

            .report-container {
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--pln-dark-blue);">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="img/pln.png" alt="PLN Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Users</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <h2 class="mb-4">Power Plant Reporting System</h2>

        <!-- Power Plant Cards -->
        <div class="row">
            <!-- PLTD KELEDANG -->
            <div class="col-md-4">
                <div class="card power-plant-card">
                    <div class="card-header">
                        PLTD KELEDANG
                    </div>
                    <div class="card-body text-center">
                        <div class="plant-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>Diesel Power Plant</h5>
                        <p class="text-muted">Operational status reporting</p>
                        <a href="operasi-keledang.php" class="btn btn-pln">
                            <i class="fas fa-file-alt"></i> Create Report
                        </a>
                        <a href="laporan-keledang.php" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-list"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- PLTD KARANG ASAM -->
            <div class="col-md-4">
                <div class="card power-plant-card">
                    <div class="card-header">
                        PLTD KARANG ASAM
                    </div>
                    <div class="card-body text-center">
                        <div class="plant-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>Diesel Power Plant</h5>
                        <p class="text-muted">Operational status reporting</p>
                        <a href="operasi-karang_asam.php" class="btn btn-pln">
                            <i class="fas fa-file-alt"></i> Create Report
                        </a>
                        <a href="laporan-karang_asam.php" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-list"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- PLTD-MG BONTANG -->
            <div class="col-md-4">
                <div class="card power-plant-card">
                    <div class="card-header">
                        PLTD-MG BONTANG
                    </div>
                    <div class="card-body text-center">
                        <div class="plant-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>Diesel Power Plant</h5>
                        <p class="text-muted">Operational status reporting</p>
                        <a href="operasi-bontang.php" class="btn btn-pln">
                            <i class="fas fa-file-alt"></i> Create Report
                        </a>
                        <a href="laporan-bontang.php" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-list"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- PLTG PEAKING -->
            <div class="col-md-4">
                <div class="card power-plant-card">
                    <div class="card-header">
                        PLTG PEAKING
                    </div>
                    <div class="card-body text-center">
                        <div class="plant-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <h5>Gas Turbine Power Plant</h5>
                        <p class="text-muted">Operational status reporting</p>
                        <a href="operasi-peaking.php" class="btn btn-pln">
                            <i class="fas fa-file-alt"></i> Create Report
                        </a>
                        <a href="laporan-peaking.php" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-list"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- PLTG SAMBERA -->
            <div class="col-md-4">
                <div class="card power-plant-card">
                    <div class="card-header">
                        PLTG SAMBERA
                    </div>
                    <div class="card-body text-center">
                        <div class="plant-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <h5>Gas Turbine Power Plant</h5>
                        <p class="text-muted">Operational status reporting</p>
                        <a href="operasi-sambera.php" class="btn btn-pln">
                            <i class="fas fa-file-alt"></i> Create Report
                        </a>
                        <a href="laporan-sambera.php" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-list"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- PLTGU TANJUNG BATU -->
            <div class="col-md-4">
                <div class="card power-plant-card">
                    <div class="card-header">
                        PLTGU TANJUNG BATU
                    </div>
                    <div class="card-body text-center">
                        <div class="plant-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <h5>Combined Cycle Power Plant</h5>
                        <p class="text-muted">Operational status reporting</p>
                        <a href="operasi-tanjung_batu.php" class="btn btn-pln">
                            <i class="fas fa-file-alt"></i> Create Report
                        </a>
                        <a href="laporan-tanjung_batu.php" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-list"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Template for PDF Export -->
    <div class="container report-container no-print">
        <div class="report-header">
            <div class="row">
                <div class="col-md-8">
                    <h3>PLTD KELEDANG OPERATIONAL REPORT</h3>
                    <p class="text-muted">Date: April 25, 2025 | Time: 07:00 WITA</p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-pln" onclick="window.print()">
                        <i class="fas fa-file-pdf"></i> Export to PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Unit Status -->
        <h4 class="mt-4">Unit Status</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Load (MW)</th>
                        <th>Last Operation</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Unit #1</td>
                        <td><span class="status-badge status-operasi">OPERASI</span></td>
                        <td>12.00</td>
                        <td>April 24, 2025</td>
                        <td>Normal operation</td>
                    </tr>
                    <tr>
                        <td>Unit #2</td>
                        <td><span class="status-badge status-operasi">OPERASI</span></td>
                        <td>11.50</td>
                        <td>April 24, 2025</td>
                        <td>Normal operation</td>
                    </tr>
                    <tr>
                        <td>Unit #3</td>
                        <td><span class="status-badge status-rs">RS</span></td>
                        <td>0.00</td>
                        <td>April 20, 2025</td>
                        <td>Scheduled maintenance</td>
                    </tr>
                    <tr>
                        <td>Unit #4</td>
                        <td><span class="status-badge status-ru">RU</span></td>
                        <td>0.00</td>
                        <td>April 18, 2025</td>
                        <td>Under repair</td>
                    </tr>
                    <tr>
                        <td>Unit #5</td>
                        <td><span class="status-badge status-operasi">OPERASI</span></td>
                        <td>13.20</td>
                        <td>April 24, 2025</td>
                        <td>Normal operation</td>
                    </tr>
                    <tr>
                        <td>Unit #6</td>
                        <td><span class="status-badge status-operasi">OPERASI</span></td>
                        <td>12.80</td>
                        <td>April 24, 2025</td>
                        <td>Normal operation</td>
                    </tr>
                    <tr>
                        <td>Unit #7</td>
                        <td><span class="status-badge status-fo">FO</span></td>
                        <td>0.00</td>
                        <td>April 15, 2025</td>
                        <td>Fuel outage</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="2">Total Load</th>
                        <th class="total-mw">49.50 MW</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Energy Stock -->
        <h4 class="mt-4">Energy Stock</h4>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        HSD
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Penerimaan</th>
                                        <td>12,000 L</td>
                                    </tr>
                                    <tr>
                                        <th>Pemakaian</th>
                                        <td>8,500 L</td>
                                    </tr>
                                    <tr>
                                        <th>Stock</th>
                                        <td>16 Hari</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        MFO
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Penerimaan</th>
                                        <td>5,000 L</td>
                                    </tr>
                                    <tr>
                                        <th>Pemakaian</th>
                                        <td>3,200 L</td>
                                    </tr>
                                    <tr>
                                        <th>Stock</th>
                                        <td>12 Hari</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Footer -->
        <div class="mt-4 pt-3 border-top">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Prepared by:</strong></p>
                    <p>John Doe</p>
                    <p>Operator PLTD KELEDANG</p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Approved by:</strong></p>
                    <p>Jane Smith</p>
                    <p>Supervisor PLTD KELEDANG</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to generate PDF
        function generatePDF() {
            // You can use a library like jsPDF or html2pdf.js here
            // For example with html2pdf.js:
            // html2pdf().from(document.getElementById('report-content')).save();

            // For now we'll just use the browser's print functionality
            window.print();
        }

        // You can add more JavaScript for dynamic functionality as needed
    </script>
</body>

</html>