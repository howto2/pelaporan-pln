<?php
session_start();
if (
    !isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) ||
    !isset($_SESSION["location"]) || $_SESSION["location"] !== 'admin'
) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pelaporan Operasi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('img/banner.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 0;
        }

        .module-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header-bar {
            background-color: #0056b3;
            color: white;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-left img {
            height: 60px;
            margin-right: 20px;
        }

        .header-right a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .menu-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            transition: 0.3s;
            text-decoration: none;
            color: #333;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .menu-card img {
            width: 50px;
            height: auto;
            margin-right: 15px;
        }

        .menu-title {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .section-title {
            text-align: center;
            margin-bottom: 25px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>

    <div class="module-container">
        <div class="header-bar">
            <div class="header-left">
                <img src="img/pln.png" alt="Logo PLN">
                <div>
                    <h5 class="mb-0">Sistem Pelaporan Operasi</h5>
                    <small>PT PLN (Persero)</small>
                </div>
            </div>
            <div class="header-right">
                <a href="admin_dashboard.php">Keluar</a>
            </div>
        </div>

        <h4 class="section-title">Daftar Unit PLTD / PLTG</h4>

        <?php
        $units = [
            "PLTD KELEDANG" => "laporan_keledang.php",
            "PLTD KARANG ASAM" => "laporan_karang_asam.php",
            "PLTD-MG BONTANG" => "laporan_bontang.php",
            "PLTG PEAKING" => "laporan_peaking.php",
            "PLTG SAMBERA" => "laporan_sambera.php",
            "PLTGU TANJUNG BATU" => "laporan_tanjung_batu.php"
        ];

        foreach ($units as $name => $url) {
            echo '
        <a href="' . $url . '" class="menu-card">
            <img src="img/pln.png" alt="Logo PLN">
            <div class="menu-title">' . $name . '</div>
        </a>';
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>