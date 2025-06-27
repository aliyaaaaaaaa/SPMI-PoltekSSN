<?php
require_once 'db.php';

// Get semua jenis dokumen dengan nama kategori
function getJenisDokumen($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT jd.*, kd.nama_kd 
            FROM jd 
            LEFT JOIN kd ON jd.kd_id = kd.kd_id 
            ORDER BY kd.nama_kd, jd.nama_jd
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getJenisDokumen: ' . $e->getMessage());
        return [];
    }
}

// Add jenis dokumen
function addJenisDokumen($pdo, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO jd (kd_id, nama_jd) VALUES (?, ?)");
        return $stmt->execute([
            $data['kd_id'],
            $data['nama_jd']
        ]);
    } catch (PDOException $e) {
        error_log('Error in addJenisDokumen: ' . $e->getMessage());
        throw $e;
    }
}

// Update jenis dokumen
function updateJenisDokumen($pdo, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE jd SET kd_id = ?, nama_jd = ? WHERE jd_id = ?");
        return $stmt->execute([
            $data['kd_id'],
            $data['nama_jd'],
            $data['jd_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateJenisDokumen: ' . $e->getMessage());
        throw $e;
    }
}

// Delete jenis dokumen
function deleteJenisDokumen($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM jd WHERE jd_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Error in deleteJenisDokumen: ' . $e->getMessage());
        throw $e;
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_jenis':
        echo json_encode(getJenisDokumen($pdo));
        break;

    case 'add':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                if (addJenisDokumen($pdo, $data)) {
                    echo json_encode(['message' => 'Jenis Dokumen berhasil ditambahkan']);
                } else {
                    throw new Exception('Gagal menambahkan jenis dokumen');
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
                if (updateJenisDokumen($pdo, $data)) {
                    echo json_encode(['message' => 'Jenis Dokumen berhasil diupdate']);
                } else {
                    throw new Exception('Gagal mengupdate jenis dokumen');
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
                    if (deleteJenisDokumen($pdo, $id)) {
                        echo json_encode(['message' => 'Jenis Dokumen berhasil dihapus']);
                    } else {
                        throw new Exception('Gagal menghapus jenis dokumen');
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