<?php
session_start();

// Hanya redirect jika sudah login DAN session valid
if (isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {
    if ($_SESSION["user_type"] == 'admin') {
        header("Location: admin/admin_dashboard.php");
        exit();
    } elseif (isset($_SESSION["location"])) {
        if ($_SESSION["location"] == 'karang-asam') {
            header("Location: user/karang-asam/dashboard_karang_asam.php");
        } elseif ($_SESSION["location"] == 'keledang') {
            header("Location: user/keledang/dashboard_keledang.php");
        } elseif ($_SESSION["location"] == 'wahau') {
            header("Location: user/wahau/dashboard_wahau.php");
        } elseif ($_SESSION["location"] == 'long_bagun') {
            header("Location: user/loang_bagun/dashboard_long_bagun.php");
        } elseif ($_SESSION["location"] == 'long_iram') {
            header("Location: user/long_iram/dashboard_long_iram.php");
        } elseif ($_SESSION["location"] == 'bengkal') {
            header("Location: user/bengkal/dashboard_bengkal.php");
        } elseif ($_SESSION["location"] == 'bontang') {
            header("Location: user/bontang/dashboard_bontang.php");
        } elseif ($_SESSION["location"] == 'sambera') {
            header("Location: user/sambera/dashboard_sambera.php");
        } elseif ($_SESSION["location"] == 'sentawar') {
            header("Location: user/sentawar/dashboard_sentawar.php");
        } elseif ($_SESSION["location"] == 'tanjung_batu') {
            header("Location: user/tanjung-batu/dashboard_tanjung_batu.php");
        } else {
            header("Location: halaman_default.php");
        }
        exit();
    }
    // Jika tidak memenuhi kondisi di atas, tetap di index.php
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | Sistem Pelaporan Operasi PLN</title>

  <!-- Font Black Han Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Black+Han+Sans&display=swap" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body.admin-page {
      background-color: #f5f5f5;
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      min-height: 100vh;
    }

    .judul-utama {
      font-family: 'Black Han Sans', sans-serif;
      color: rgb(51, 59, 79);
      text-align: center;
      text-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
      margin-bottom: 20px; 
      font-size: 2.3rem;
      position: relative;
      top: -20px; 
    }

    .container {
      font-family: 'Black Han Sans', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      padding: 20px;
    }

    .login-box {
      display: flex;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
      overflow: hidden;
      max-width: 900px;
      width: 100%;
      position: relative;
    }

    .pln-logo {
      position: absolute;
      top: 15px;
      left: 15px;
      width: 80px;
      height: auto;
      z-index: 1;
    }

    .left, .right {
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .left {
      align-items: center;
      flex: 1;
      background: #ffffff;
      position: relative;
      padding-bottom: 60px; /* Added space for watermark */
    }

    .right {
      font-family: 'Black Han Sans', sans-serif;
      flex: 1;
      background: #6276AA;
      color: white;
      text-align: center;
      position: relative;
      min-height: 450px;
    }

    .left h2 {
      color: rgb(69, 80, 107);
      margin-bottom: 50px;
      font-size: 1.8rem;
      margin-top: 30px;
    }

    .welcome-text {
      position: absolute;
      top: 40px;
      left: 0;
      right: 0;
      padding: 0 20px;
    }

    .right h3 {
      margin-bottom: 20px;
      font-size: 1.8rem;
    }

    .right img {
      max-width: 58%;
      height: auto;
      position: absolute;
      bottom: 20px;
      right: 80px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }

    button[type="submit"] {
      width: 40%;
      padding: 12px;
      background-color: rgb(147, 147, 147);
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button[type="submit"]:hover {
      background-color: #003d7a;
    }

    .error-message {
      color: #dc3545;
      margin-bottom: 15px;
      text-align: center;
    }

    .version-watermark {
      position: absolute;
      bottom: 15px;
      right: 20px;
      color: rgba(69, 80, 107, 0.4);
      font-size: 0.8rem;
      font-weight: bold;
      letter-spacing: 1px;
      font-family: 'Black Han Sans', sans-serif;
    }

    @media (max-width: 768px) {
      .login-box {
        flex-direction: column;
      }
      
      .right {
        display: none;
      }
      
      .judul-utama {
        top: 0;
        margin-bottom: 10px;
      }
      
      .pln-logo {
        width: 60px;
        top: 10px;
        left: 10px;
      }
      
      .left h2 {
        margin-top: 20px;
      }
      
      .version-watermark {
        font-size: 0.7rem;
        bottom: 10px;
        right: 15px;
      }
      
      .left {
        padding-bottom: 50px;
      }
    }
  </style>
</head>
<body class="admin-page">

  <!-- Judul di luar kotak -->
  <h1 class="judul-utama">Sistem Pelaporan Operasi PLN</h1>

  <!-- Kotak login -->
  <div class="container">
    <div class="login-box">
      <!-- PLN Logo -->
      <img src="img/plnnobg.png" alt="PLN Logo" class="pln-logo">
      
      <div class="left">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
          <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="proses_login.php">
          <input type="text" name="username" placeholder="Username" required />
          <input type="password" name="password" placeholder="Password" required />
          <button type="submit">Login Now</button>
        </form>
        <!-- Version Watermark -->
        <div class="version-watermark">ZR version</div>
      </div>
      <div class="right">
        <div class="welcome-text">
          <h3>Halo. Selamat Datang !</h3>
        </div>
        <img src="img/admin.png" alt="Admin" />
      </div>
    </div>
  </div>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>