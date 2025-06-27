<?php
require_once 'db.php';

// Get semua kategori temuan
function getKT($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM kt ORDER BY nama_kt");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getKT: ' . $e->getMessage());
        return [];
    }
}

// Add kategori temuan
function addKT($pdo, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO kt (nama_kt, keterangan) VALUES (?, ?)");
        return $stmt->execute([
            $data['nama_kt'],
            $data['keterangan']
        ]);
    } catch (PDOException $e) {
        error_log('Error in addKT: ' . $e->getMessage());
        throw $e;
    }
}

// Update kategori temuan
function updateKT($pdo, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE kt SET nama_kt = ?, keterangan = ? WHERE kt_id = ?");
        return $stmt->execute([
            $data['nama_kt'],
            $data['keterangan'],
            $data['kt_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateKT: ' . $e->getMessage());
        throw $e;
    }
}

// Delete kategori temuan
function deleteKT($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM kt WHERE kt_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Error in deleteKT: ' . $e->getMessage());
        throw $e;
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_kt':
        echo json_encode(getKT($pdo));
        break;

    case 'add':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                if (addKT($pdo, $data)) {
                    echo json_encode(['message' => 'Kategori Temuan berhasil ditambahkan']);
                } else {
                    throw new Exception('Gagal menambahkan kategori temuan');
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
                if (updateKT($pdo, $data)) {
                    echo json_encode(['message' => 'Kategori Temuan berhasil diupdate']);
                } else {
                    throw new Exception('Gagal mengupdate kategori temuan');
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
                    if (deleteKT($pdo, $id)) {
                        echo json_encode(['message' => 'Kategori Temuan berhasil dihapus']);
                    } else {
                        throw new Exception('Gagal menghapus kategori temuan');
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