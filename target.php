<?php
require_once 'db.php';

// Get data nilai prodi dengan join ke tabel terkait
function getNilaiProdi($pdo) {
    $sql = "SELECT objeknilai.*, lat.tahun_id, lat.la_id, p.nama as prodi_nama, t.nama as tahun_nama, l.nama as la_nama 
            FROM objeknilai
            JOIN lat ON objeknilai.lat_id = lat.lat_id
            JOIN prodi p ON objeknilai.prodi_id = p.role_id
            JOIN tahun t ON lat.tahun_id = t.tahun_id
            JOIN la l ON lat.la_id = l.la_id
            ORDER BY p.nama";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get LAT ID berdasarkan tahun dan LA
function getLATId($pdo, $tahun_id, $la_id) {
    $stmt = $pdo->prepare("SELECT lat_id FROM lat WHERE tahun_id = ? AND la_id = ?");
    $stmt->execute([$tahun_id, $la_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get semua tahun
function getTahun($pdo) {
    $stmt = $pdo->query("SELECT tahun_id, nama FROM tahun ORDER BY nama DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get semua LA
function getLA($pdo) {
    $stmt = $pdo->query("SELECT la_id, nama FROM la ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get semua prodi
function getProdi($pdo) {
    $stmt = $pdo->query("SELECT role_id, nama FROM prodi ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_nilai':
        echo json_encode(getNilaiProdi($pdo));
        break;

    case 'get_tahun':
        echo json_encode(getTahun($pdo));
        break;

    case 'get_la':
        echo json_encode(getLA($pdo));
        break;

    case 'get_prodi':
        echo json_encode(getProdi($pdo));
        break;

    case 'add':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                // Dapatkan lat_id
                $lat = getLATId($pdo, $data['tahun_id'], $data['la_id']);
                if (!$lat) {
                    throw new Exception('Kombinasi Tahun dan LA tidak ditemukan');
                }

                $sql = "INSERT INTO objeknilai (prodi_id, lat_id, target) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$data['prodi_id'], $lat['lat_id'], $data['target']]);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
        break;

    case 'update':
        if ($method === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                // Dapatkan lat_id jika tahun atau LA diubah
                if (isset($data['tahun_id']) && isset($data['la_id'])) {
                    $lat = getLATId($pdo, $data['tahun_id'], $data['la_id']);
                    if (!$lat) {
                        throw new Exception('Kombinasi Tahun dan LA tidak ditemukan');
                    }
                    $sql = "UPDATE objeknilai SET lat_id = ?, target = ? WHERE on_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$lat['lat_id'], $data['target'], $data['on_id']]);
                } else {
                    $sql = "UPDATE objeknilai SET target = ? WHERE on_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$data['target'], $data['on_id']]);
                }
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
        break;

    case 'delete':
        if ($method === 'DELETE') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM objeknilai WHERE on_id = ?");
                    $stmt->execute([$id]);
                    echo json_encode(['success' => true]);
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?> 