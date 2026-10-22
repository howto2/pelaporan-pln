<?php
session_start();

// Konfigurasi database
$host = "localhost";
$username_db = "root";
$password_db = "";
$database_name = "data_godzilla";

// Membuat koneksi
$conn = new mysqli($host, $username_db, $password_db, $database_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    if (empty($_POST["username"]) || empty($_POST["password"])) {
        header("Location: index.php?error=Username+dan+password+harus+diisi");
        exit();
    }

    // Escape input untuk mencegah SQL injection
    $username = $conn->real_escape_string($_POST["username"]);
    $password = $_POST["password"];

    // Query dengan prepared statement (lebih aman)
    $stmt = $conn->prepare("SELECT id_user, username, password, user_type, Location FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Verifikasi password (modifikasi untuk handle plaintext)
        if ($row["password"] === $password || password_verify($password, $row["password"])) {
            // Set session
            $_SESSION = [];
            $_SESSION["user_id"] = $row["id_user"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["user_type"] = $row["user_type"];

            // Format lokasi yang konsisten (sesuai database)
            $location = strtolower($row["Location"]);
            $_SESSION["location"] = $location;

            // Redirect berdasarkan user type dan location
            if ($_SESSION["user_type"] == 'admin') {
                $_SESSION["location"] = 'admin'; // Tambahkan baris ini
                header("Location: admin/admin_dashboard.php");
            } else {
                // Daftar lokasi yang dihandle
                $location_map = [
                    'keledang' => 'user/keledang/dashboard_keledang.php',
                    'karang_asam' => 'user/karang-asam/dashboard_karang_asam.php',
                    'wahau' => 'user/wahau/dashboard_wahau.php',
                    'long_bagun' => 'user/long_bagun/dashboard_long_bagun.php',
                    'long_iram' => 'user/long_iram/dashboard_long_iram.php',
                    'bengkal' => 'user/bengkal/dashboard_bengkal.php',
                    'bontang' => 'user/bontang/dashboard_bontang.php',
                    'sambera' => 'user/sambera/dashboard_sambera.php',
                    'sentawar' => 'user/sentawar/dashboard_sentawar.php',
                    'tanjung_batu' => 'user/tanjung-batu/dashboard_tanjung_batu.php'
                ];

                $redirect = $location_map[$location] ?? 'halaman_default.php';
                header("Location: " . $redirect);
            }
            exit();
        }
    }

    // Jika login gagal
    header("Location: index.php?error=Username+atau+password+salah");
    exit();
}

// Jika bukan POST request
header("Location: index.php");
exit();
?>