<?php
session_start();

// Periksa session
if (
    !isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) ||
    !isset($_SESSION["location"]) || $_SESSION["location"] !== 'karang_asam'
) {
    header("Location: ../../index.php");
    exit();
}

// Set variabel untuk judul
$page_title = "Sistem Pelaporan Operasi PLTD Karang asam";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #E5ECF0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        .module-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 950px;
        }

        .header-bar {
            background-color: #0056b3;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .header-left img {
            height: 50px;
            margin-right: 15px;
        }

        .header-left div h5 {
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }

        .header-left div p {
            font-size: 0.8rem;
            margin-bottom: 0;
        }

        .header-right {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .header-right a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .module-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: transform 0.2s ease-in-out;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        .module-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .module-card img {
            max-width: 60px;
            margin-bottom: 10px;
        }

        .daftar-menu-title {
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #333;
            text-align: center;
        }

        /* Media queries for responsive design */
        @media (min-width: 576px) {
            .module-container {
                padding: 20px;
            }
            
            .header-bar {
                flex-direction: row;
                justify-content: space-between;
                padding: 20px;
            }
            
            .header-left {
                margin-bottom: 0;
            }
            
            .header-left img {
                height: 70px;
                margin-right: 25px;
            }
            
            .header-left div h5 {
                font-size: 1.25rem;
            }
            
            .header-left div p {
                font-size: 1rem;
            }
            
            .header-right {
                width: auto;
                justify-content: flex-end;
            }
            
            .header-right a {
                font-size: 16px;
                margin-left: 20px;
            }
            
            .module-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 20px;
            }
            
            .module-card {
                padding: 20px;
                font-size: 16px;
            }
            
            .module-card img {
                max-width: 80px;
                margin-bottom: 15px;
            }
            
            .daftar-menu-title {
                font-size: 1.5em;
            }
        }

        @media (max-width: 400px) {
            .module-grid {
                grid-template-columns: 1fr;
            }
            
            .header-left {
                flex-direction: column;
                text-align: center;
            }
            
            .header-left img {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="module-container">
        <div class="header-bar">
            <div class="header-left">
                <img src="../../img/plnbulat.png" alt="Logo PLN">
                <div>
                    <h5 class="mb-0">Sistem Pelaporan Operasi</h5>
                    <p class="mb-0">PT PLN (Persero)</p>
                </div>
            </div>
            <div class="header-right">
                <span class="text-white me-2"><?php echo $_SESSION["username"]; ?></span>
                <a href="../../logout.php" class="text-white text-decoration-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 8.5 2H.5A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z" />
                        <path fill-rule="evenodd"
                            d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5a.5.5 0 0 0 0 1h9.293l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
                    </svg>
                    Keluar
                </a>
            </div>
        </div>

        <h3 class="daftar-menu-title">Daftar Menu</h3>

        <div class="module-grid">
            <a href="operasi-karang-asam.php" class="module-card">
                <img src="../../img/gear_bg.png" alt="Operasi" style="max-height: 60px;">
                Laporan Harian Mesin
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>