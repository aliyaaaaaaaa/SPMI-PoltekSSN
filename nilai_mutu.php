<?php
require_once 'db.php';

// Get semua nilai mutu
function getNilaiMutu($pdo) {
    try {
        $query = "
            SELECT 
                nm.nm_id,
                nm.lat_id,
                nm.nilai,
                nm.keterangan,
                t.tahun_id,
                t.nama as tahun_nama,
                la.la_id,
                la.nama as la_nama
            FROM nilaimutu nm
            LEFT JOIN lat ON nm.lat_id = lat.lat_id
            LEFT JOIN tahun t ON lat.tahun_id = t.tahun_id
            LEFT JOIN la ON lat.la_id = la.la_id
            ORDER BY t.nama DESC, la.nama ASC
        ";
        
        // Debug: log the query
        error_log("Query: " . $query);
        
        $stmt = $pdo->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: log the result
        error_log("Result: " . print_r($result, true));
        
        return $result;
    } catch (PDOException $e) {
        error_log('Error in getNilaiMutu: ' . $e->getMessage());
        error_log('SQL State: ' . $e->errorInfo[0]);
        error_log('Error Code: ' . $e->errorInfo[1]);
        error_log('Error Message: ' . $e->errorInfo[2]);
        return [];
    }
}

// Get semua PSM
function getPSM($pdo) {
    try {
        $stmt = $pdo->query("SELECT psm_id, nama FROM psm ORDER BY nama");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getPSM: ' . $e->getMessage());
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

// Tambahkan fungsi untuk add dan update
function addNilaiMutu($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO nilaimutu (lat_id, nilai, keterangan) 
            VALUES (?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['lat_id'],
            $data['nilai'],
            $data['keterangan']
        ]);
    } catch (PDOException $e) {
        error_log('Error in addNilaiMutu: ' . $e->getMessage());
        throw $e;
    }
}

function updateNilaiMutu($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            UPDATE nilaimutu 
            SET lat_id = ?, nilai = ?, keterangan = ?
            WHERE nm_id = ?
        ");
        
        return $stmt->execute([
            $data['lat_id'],
            $data['nilai'],
            $data['keterangan'],
            $data['nm_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateNilaiMutu: ' . $e->getMessage());
        throw $e;
    }
}

function deleteNilaiMutu($pdo, $nm_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM nilaimutu WHERE nm_id = ?");
        return $stmt->execute([$nm_id]);
    } catch (PDOException $e) {
        error_log('Error in deleteNilaiMutu: ' . $e->getMessage());
        throw $e;
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Set error handling to return JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get_nilai':
            $data = getNilaiMutu($pdo);
            echo json_encode($data);
            break;

        case 'get_nilaiLat':
            $lat_id = $_GET['lat_id'] ?? null;
            if ($lat_id) {
                $stmt = $pdo->prepare("SELECT nm_id, lat_id, nilai, keterangan FROM nilaimutu WHERE lat_id = ?");
                $stmt->execute([$lat_id]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                // fallback: tampilkan semua (opsional)
                $data = getNilaiMutu($pdo);
                echo json_encode($data);
            }
            break;

        case 'get_psm':
            echo json_encode(getPSM($pdo));
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

        case 'add':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON data received');
                }
                if (addNilaiMutu($pdo, $data)) {
                    echo json_encode(['message' => 'Nilai mutu berhasil ditambahkan']);
                } else {
                    throw new Exception('Gagal menambahkan nilai mutu');
                }
            }
            break;

        case 'update':
            if ($method === 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON data received');
                }
                if (updateNilaiMutu($pdo, $data)) {
                    echo json_encode(['message' => 'Nilai mutu berhasil diupdate']);
                } else {
                    throw new Exception('Gagal mengupdate nilai mutu');
                }
            }
            break;

        case 'delete':
            if ($method === 'DELETE') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['nm_id'])) {
                    throw new Exception('nm_id is required');
                }
                if (deleteNilaiMutu($pdo, $data['nm_id'])) {
                    echo json_encode(['message' => 'Nilai mutu berhasil dihapus']);
                } else {
                    throw new Exception('Gagal menghapus nilai mutu');
                }
            }
            break;

        default:
            throw new Exception('Action not found');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?> 