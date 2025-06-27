<?php
require_once 'db.php';

// Get semua nilai mutu
function getNilaiProdi($pdo) {
    try {
        $query = "
            SELECT 
                o.on_id,
                o.lat_id,
                o.prodi_id,
                o.nilai_edi,
                o.nilai_edesk,
                o.target,
                t.tahun_id,
                t.nama as tahun_nama,
                la.la_id,
                la.nama as la_nama,
                prodi.role_id,
                prodi.nama as prodi_nama
            FROM objeknilai o
            LEFT JOIN lat ON o.lat_id = lat.lat_id
            LEFT JOIN tahun t ON lat.tahun_id = t.tahun_id
            LEFT JOIN la ON lat.la_id = la.la_id
            LEFT JOIN prodi ON o.prodi_id = prodi.role_id            
            ORDER BY t.nama DESC, la.nama ASC, prodi.nama ASC
        ";
        
        // Debug: log the query
        error_log("Query: " . $query);
        
        $stmt = $pdo->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: log the result
        error_log("Result: " . print_r($result, true));
        
        return $result;
    } catch (PDOException $e) {
        error_log('Error in getNilaiProdi: ' . $e->getMessage());
        error_log('SQL State: ' . $e->errorInfo[0]);
        error_log('Error Code: ' . $e->errorInfo[1]);
        error_log('Error Message: ' . $e->errorInfo[2]);
        return [];
    }
}

// Get semua LAT
function getLAT($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                lat.lat_id,
                t.tahun_id,
                la.la_id,
                t.nama as tahun_nama,
                la.nama as la_nama
            FROM lat
            JOIN tahun t ON lat.tahun_id = t.tahun_id
            JOIN la ON lat.la_id = la.la_id
            ORDER BY t.nama DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getLAT: ' . $e->getMessage());
        return [];
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_nilai':
        $data = getNilaiProdi($pdo);
        error_log("Sending data: " . print_r($data, true));
        echo json_encode($data);
        break;

    case 'get_lat':
        echo json_encode(getLAT($pdo));
        break;

    case 'get_tahun':
        $stmt = $pdo->query("SELECT tahun_id, nama FROM tahun ORDER BY nama DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'get_la':
        $stmt = $pdo->query("SELECT la_id, nama FROM la ORDER BY nama");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'get_prodi':
        $stmt = $pdo->query("SELECT role_id, nama FROM prodi ORDER BY nama");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?> 