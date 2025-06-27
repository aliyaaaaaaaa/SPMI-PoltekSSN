<?php
require_once 'db.php';

// Get semua kategori dokumen
function getKategoriDokumen($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM kd ORDER BY nama_kd");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getKategoriDokumen: ' . $e->getMessage());
        return [];
    }
}

// Add kategori dokumen
function addKategoriDokumen($pdo, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO kd (nama_kd) VALUES (?)");
        return $stmt->execute([$data['nama_kd']]);
    } catch (PDOException $e) {
        error_log('Error in addKategoriDokumen: ' . $e->getMessage());
        throw $e;
    }
}

// Update kategori dokumen
function updateKategoriDokumen($pdo, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE kd SET nama_kd = ? WHERE kd_id = ?");
        return $stmt->execute([
            $data['nama_kd'],
            $data['kd_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateKategoriDokumen: ' . $e->getMessage());
        throw $e;
    }
}

// Delete kategori dokumen
function deleteKategoriDokumen($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM kd WHERE kd_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Error in deleteKategoriDokumen: ' . $e->getMessage());
        throw $e;
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_kategori':
        echo json_encode(getKategoriDokumen($pdo));
        break;

    case 'add':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                if (addKategoriDokumen($pdo, $data)) {
                    echo json_encode(['message' => 'Kategori Dokumen berhasil ditambahkan']);
                } else {
                    throw new Exception('Gagal menambahkan kategori dokumen');
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
                if (updateKategoriDokumen($pdo, $data)) {
                    echo json_encode(['message' => 'Kategori Dokumen berhasil diupdate']);
                } else {
                    throw new Exception('Gagal mengupdate kategori dokumen');
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
                    if (deleteKategoriDokumen($pdo, $id)) {
                        echo json_encode(['message' => 'Kategori Dokumen berhasil dihapus']);
                    } else {
                        throw new Exception('Gagal menghapus kategori dokumen');
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