<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) || !isset($_SESSION["location"]) || $_SESSION["location"] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once 'koneksi.php';

// Get report ID from URL
$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;

if ($report_id > 0) {
    // First, get the location to redirect back to the correct page
    $location_query = "SELECT u.Location 
                      FROM operational_reports o 
                      JOIN users u ON o.user_id = u.id_user 
                      WHERE o.report_id = ?";
    $stmt = $conn->prepare($location_query);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $location = $row['Location'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete energy stocks
        $delete_stocks = $conn->prepare("DELETE FROM energy_stocks WHERE report_id = ?");
        $delete_stocks->bind_param("i", $report_id);
        $delete_stocks->execute();

        // Delete unit statuses
        $delete_units = $conn->prepare("DELETE FROM unit_statuses WHERE report_id = ?");
        $delete_units->bind_param("i", $report_id);
        $delete_units->execute();

        // Delete the report
        $delete_report = $conn->prepare("DELETE FROM operational_reports WHERE report_id = ?");
        $delete_report->bind_param("i", $report_id);
        $delete_report->execute();

        // Commit transaction
        $conn->commit();

        $_SESSION['success_message'] = "Laporan berhasil dihapus";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Gagal menghapus laporan: " . $e->getMessage();
    }

    // Redirect back to the appropriate location page
    header("Location: laporan_$location.php");
    exit();
} else {
    header("Location: pelaporan-operasi-admin.php");
    exit();
}
?>