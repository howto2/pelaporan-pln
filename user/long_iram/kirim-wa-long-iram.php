<?php
session_start();
// Di file kirim-wa-sentawar.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/Project-PLN/koneksi.php');
// Verifikasi session
if (!isset($_SESSION["user_id"]) || ($_SESSION["location"] ?? '') !== 'long_iram') {
    $_SESSION['error_message'] = "Akses ditolak. Silakan login kembali.";
    header("Location: index.php");
    exit();
}

// Dapatkan report_id
$report_id = $_SESSION['last_report_id'] ?? 0;

if ($report_id == 0) {
    $_SESSION['error_message'] = "Tidak ada laporan terbaru yang ditemukan";
    header("Location: operasi-long-iram.php");
    exit();
}

// Fungsi untuk mengirim WA via FONNTE (tetap sama)
function sendWA_FONNTE($number, $message)
{
    $api_key = 'PNkXU237oqMphMo2U53Q'; // Ganti dengan API key FONNTE Anda

    $url = 'https://api.fonnte.com/send';

    $data = [
        "target" => $number,
        "message" => $message,
        "delay" => "" // Delay pengiriman (opsional)
    ];

    $headers = [
        'Authorization: ' . $api_key
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false // Hanya untuk testing
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    error_log("FONNTE API Response: HTTP $http_code - " . $response);

    if ($error) {
        error_log("CURL Error: " . $error);
        return ['status' => false, 'message' => $error];
    }

    $result = json_decode($response, true);

    // Format response FONNTE berbeda dengan Wablas
    if (isset($result['status']) && $result['status'] == 'success') {
        return ['status' => true, 'message' => 'Pesan terkirim'];
    } else {
        return ['status' => false, 'message' => $result['message'] ?? 'Unknown error'];
    }
}

try {
    // Ambil data operasional dari database
    $query_report = "SELECT report_date, report_time, total_mw 
                    FROM operational_reports 
                    WHERE report_id = ?";
    $stmt = $conn->prepare($query_report);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $report = $stmt->get_result()->fetch_assoc();

    if (!$report) {
        throw new Exception("Data laporan tidak ditemukan di database");
    }

    // Ambil data unit status
    $query_units = "SELECT unit_number, status, load_mw, last_operation, notes 
                   FROM unit_statuses 
                   WHERE report_id = ?";
    $stmt = $conn->prepare($query_units);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $units = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Ambil data energy stocks (jika ada)
    $query_stocks = "SELECT fuel_type, received, used, stock, HOP 
                     FROM energy_stocks 
                     WHERE report_id = ?";
    $stmt = $conn->prepare($query_stocks);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $stocks_result = $stmt->get_result();

    $energy_stocks = [];
    while ($row = $stocks_result->fetch_assoc()) {
        $energy_stocks[$row['fuel_type']] = $row;
    }

    // Ambil data dari session atau form
    $cuaca = $_SESSION['form_data']['cuaca'] ?? $_POST['cuaca'] ?? 'Tidak tercatat';
    $pengawas = $_SESSION['form_data']['pengawas'] ?? $_POST['pengawas'] ?? 'Tidak tercatat';
    $pelaksana1 = $_SESSION['form_data']['pelaksana1'] ?? $_POST['pelaksana1'] ?? 'Tidak tercatat';
    $pelaksana2 = $_SESSION['form_data']['pelaksana2'] ?? $_POST['pelaksana2'] ?? 'Tidak tercatat';
    $bp = $_SESSION['form_data']['bp'] ?? 0;
    $surplus = $_SESSION['form_data']['surplus'] ?? 0;
    $padam_jaringan = $_SESSION['form_data']['padam_jaringan'] ?? 0;
    $padam_defisit = $_SESSION['form_data']['padam_defisit'] ?? 0;

    // Format pesan WhatsApp
    $pesan = "⚡ *LAPORAN OPERASI PLTD LONG IRAM*\n";
    $pesan .= "📅 *Tanggal:* {$report['report_date']}\n";
    $pesan .= "🕒 *Waktu:* {$report['report_time']}\n";
    $pesan .= "☁️ *Cuaca:* $cuaca\n\n";

    $pesan .= "👷 *PERSONIL PIKET:*\n";
    $pesan .= "- *Pengawas:* $pengawas\n";
    $pesan .= "- *Pelaksana:* \n";
    $pesan .= "  • $pelaksana1\n";
    $pesan .= "  • $pelaksana2\n\n";

    $pesan .= "🔧 *STATUS UNIT:*\n";
    foreach ($units as $unit) {
        $pesan .= "- *{$unit['unit_number']}*: {$unit['load_mw']} MW ({$unit['status']})";
        if (!empty($unit['last_operation'])) {
            $pesan .= "\n  Terakhir operasi: {$unit['last_operation']}";
        }
        if (!empty($unit['notes'])) {
            $pesan .= "\n  Catatan: {$unit['notes']}";
        }
        $pesan .= "\n";
    }

    $pesan .= "\n⚡ *TOTAL BEBAN:* {$report['total_mw']} MW\n\n";

    // Tambahkan energy stocks hanya jika ada data dan waktu 07.00 WITA
    if (!empty($energy_stocks) && strpos($report['report_time'], '07.00') !== false) {
        $pesan .= "⛽ *STOCK ENERGY PRIMER UP PLTD LONG IRAM*\n";

        foreach (['HSD', 'MFO'] as $fuel_type) {
            if (isset($energy_stocks[$fuel_type])) {
                $stock = $energy_stocks[$fuel_type];
                $pesan .= "• *$fuel_type:*\n";
                $pesan .= "  - Penerimaan: {$stock['received']} L\n";
                $pesan .= "  - Pemakaian: {$stock['used']} L\n";
                $pesan .= "  - Stok: {$stock['stock']} L\n";
                $pesan .= "  - HOP: {$stock['HOP']} Hari\n\n";
            }
        }
    }

    // Tambahkan parameter operasi
    $pesan .= "\n📊 *PARAMETER OPERASI PLTD LONG IRAM*\n";
    $pesan .= "--------------------------------\n";
    $pesan .= "• *DTS (Beban Total Sistem)*: 16000 MW\n";
    $pesan .= "• *DMN (Beban Minimum)*: 10000 MW\n";
    $pesan .= "• *DMP (Beban Puncak)*: 9000 MW\n";
    $pesan .= "• *BP (Beban Pembangkit)*: " . number_format($bp, 2) . " MW\n";
    $pesan .= "• *SURPLUS*: " . number_format($surplus, 2) . " MW\n";
    $pesan .= "• *PADAM JARINGAN*: " . number_format($padam_jaringan, 2) . " MW\n";
    $pesan .= "• *PADAM DEFISIT*: " . number_format($padam_defisit, 2) . " MW\n";
    $pesan .= "--------------------------------\n";

    // kirim WhatsApp via FONNTE ke multiple nomor
    $nomorTujuan = [
        "6285753428836", // Nomor pertama
        "6282253689279" // Nomor kedua
        // Nomor ketiga (opsional)
    ];

    $successCount = 0;
    $errorMessages = [];

    foreach ($nomorTujuan as $nomor) {
        // Bersihkan format nomor
        $nomor = preg_replace('/^\+|^00/', '', $nomor);

        try {
            $resultWA = sendWA_FONNTE(number: $nomor, message: $pesan);

            if ($resultWA['status']) {
                $successCount++;
            } else {
                $errorMessages[] = "Gagal mengirim ke $nomor: " . $resultWA['message'];
            }
        } catch (Exception $e) {
            $errorMessages[] = "Error mengirim ke $nomor: " . $e->getMessage();
        }
    }

    if ($successCount > 0) {
        $_SESSION['success_message'] = "📌 Laporan berhasil dikirim via WhatsApp ke $successCount nomor!";

        // Jika ada error, tambahkan ke session juga
        if (!empty($errorMessages)) {
            $_SESSION['error_messages'] = $errorMessages;
        }
    } else {
        throw new Exception("Gagal mengirim WA ke semua nomor: " . implode(", ", $errorMessages));
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    error_log("Error in kirim-wa-long-iram.php: " . $e->getMessage());
}

// Kembali ke halaman laporan
header("Location: operasi-long-iram.php");
exit();
?>