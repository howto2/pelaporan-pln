<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["location"] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paramName = $_POST['param_name'];
    $paramValue = $_POST['param_value'];
    
    // Cek apakah data sudah ada
    $checkQuery = "SELECT * FROM pltd_parameters WHERE location = 'keledang'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        // Update data yang ada
        $stmt = $conn->prepare("UPDATE pltd_parameters SET $paramName = ? WHERE location = 'keledang'");
        $stmt->bind_param("d", $paramValue);
    } else {
        // Insert data baru
        $dts = $paramName === 'dts' ? $paramValue : 16000;
        $dmn = $paramName === 'dmn' ? $paramValue : 10000;
        $dmp = $paramName === 'dmp' ? $paramValue : 9000;
        
        $stmt = $conn->prepare("INSERT INTO pltd_parameters (location, dts, dmn, dmp) VALUES ('keledang', ?, ?, ?)");
        $stmt->bind_param("ddd", $dts, $dmn, $dmp);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Parameter berhasil diperbarui";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui parameter: " . $conn->error;
    }
    
    header("Location: laporan_keledang.php");
    exit();
}
?>