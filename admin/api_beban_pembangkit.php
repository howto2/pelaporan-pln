<?php
require_once 'koneksi.php';

header('Content-Type: application/json');

try {
    // Query untuk mendapatkan data beban terbaru per lokasi
    $query = "SELECT 
                u.Location, 
                o.total_mw,
                o.report_time,
                o.report_date
              FROM operational_reports o 
              JOIN users u ON o.user_id = u.id_user 
              WHERE o.report_id IN (
                  SELECT MAX(o2.report_id)
                  FROM operational_reports o2
                  JOIN users u2 ON o2.user_id = u2.id_user
                  WHERE DATE(o2.report_date) = CURDATE()
                  GROUP BY u2.Location
              )";
    
    $result = $conn->query($query);

    $data = [];
    $lastUpdate = null;
    
    while ($row = $result->fetch_assoc()) {
        $data[$row['Location']] = (float)$row['total_mw'];
        // Ambil waktu update terakhir
        $currentUpdate = $row['report_date'] . ' ' . $row['report_time'];
        if (!$lastUpdate || $currentUpdate > $lastUpdate) {
            $lastUpdate = $currentUpdate;
        }
    }

    // Format data untuk frontend
    $response = [
        'unitNames' => [
            'PLTGU Tanjung Batu',
            'PLTD Sentawar',
            'PLTD Bontang',
            'PLTD Muara Wahau',
            'PLTD Muara Bengkal',
            'PLTD Long Bagun',
            'PLTD Keledang',
            'PLTD Long Iram',
            'PLTD Karang Asam',
            'PLTG Sambera'
        ],
        'bebanData' => [
            $data['tanjung_batu'] ?? 0,
            $data['sentawar'] ?? 0,
            $data['bontang'] ?? 0,
            $data['muara_wahau'] ?? 0,
            $data['muara_bengkal'] ?? 0,
            $data['long_bagun'] ?? 0,
            $data['keledang'] ?? 0,
            $data['long_iram'] ?? 0,
            $data['karang_asam'] ?? 0,
            $data['sambera'] ?? 0
        ],
        'lastUpdate' => $lastUpdate
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'unitNames' => [],
        'bebanData' => []
    ]);
}
?>