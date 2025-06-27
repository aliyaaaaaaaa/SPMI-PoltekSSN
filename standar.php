<?php
require_once 'db.php';

// Get semua standar
function getStandar($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM psm ORDER BY nama");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getStandar: ' . $e->getMessage());
        return [];
    }
}

// Add standar
function addStandar($pdo, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO psm (nama, keterangan) VALUES (?, ?)");
        return $stmt->execute([
            $data['nama'],
            $data['keterangan']
        ]);
    } catch (PDOException $e) {
        error_log('Error in addStandar: ' . $e->getMessage());
        throw $e;
    }
}

// Update standar
function updateStandar($pdo, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE psm SET nama = ?, keterangan = ? WHERE psm_id = ?");
        return $stmt->execute([
            $data['nama'],
            $data['keterangan'],
            $data['psm_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateStandar: ' . $e->getMessage());
        throw $e;
    }
}

// Delete standar
function deleteStandar($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM psm WHERE psm_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Error in deleteStandar: ' . $e->getMessage());
        throw $e;
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_standar':
        echo json_encode(getStandar($pdo));
        break;

    case 'add':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                if (addStandar($pdo, $data)) {
                    echo json_encode(['message' => 'Standar berhasil ditambahkan']);
                } else {
                    throw new Exception('Gagal menambahkan standar');
                }
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
                if (updateStandar($pdo, $data)) {
                    echo json_encode(['message' => 'Standar berhasil diupdate']);
                } else {
                    throw new Exception('Gagal mengupdate standar');
                }
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
                    if (deleteStandar($pdo, $id)) {
                        echo json_encode(['message' => 'Standar berhasil dihapus']);
                    } else {
                        throw new Exception('Gagal menghapus standar');
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['error' => $e->getMessage()]);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID tidak ditemukan']);
            }
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?> 