<?php
require_once 'db.php';

// Get semua jenis temuan dengan nama kategori
function getJenisTemuan($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT jt.*, kt.nama_kt 
            FROM jt 
            LEFT JOIN kt ON jt.kt_id = kt.kt_id 
            ORDER BY kt.nama_kt, jt.nama_jt
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getJenisTemuan: ' . $e->getMessage());
        return [];
    }
}

// Add jenis temuan
function addJenisTemuan($pdo, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO jt (kt_id, nama_jt) VALUES (?, ?)");
        return $stmt->execute([
            $data['kt_id'],
            $data['nama_jt']
        ]);
    } catch (PDOException $e) {
        error_log('Error in addJenisTemuan: ' . $e->getMessage());
        throw $e;
    }
}

// Update jenis temuan
function updateJenisTemuan($pdo, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE jt SET kt_id = ?, nama_jt = ? WHERE jt_id = ?");
        return $stmt->execute([
            $data['kt_id'],
            $data['nama_jt'],
            $data['jt_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateJenisTemuan: ' . $e->getMessage());
        throw $e;
    }
}

// Delete jenis temuan
function deleteJenisTemuan($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM jt WHERE jt_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Error in deleteJenisTemuan: ' . $e->getMessage());
        throw $e;
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_jenis':
        echo json_encode(getJenisTemuan($pdo));
        break;

    case 'add':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                if (addJenisTemuan($pdo, $data)) {
                    echo json_encode(['message' => 'Jenis Temuan berhasil ditambahkan']);
                } else {
                    throw new Exception('Gagal menambahkan jenis temuan');
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
                if (updateJenisTemuan($pdo, $data)) {
                    echo json_encode(['message' => 'Jenis Temuan berhasil diupdate']);
                } else {
                    throw new Exception('Gagal mengupdate jenis temuan');
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
                    if (deleteJenisTemuan($pdo, $id)) {
                        echo json_encode(['message' => 'Jenis Temuan berhasil dihapus']);
                    } else {
                        throw new Exception('Gagal menghapus jenis temuan');
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