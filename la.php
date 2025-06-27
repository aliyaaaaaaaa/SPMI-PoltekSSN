<?php
require_once 'db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Get all LA
function getLA($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM la ORDER BY nama");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getLA: ' . $e->getMessage());
        throw $e;
    }
}

// Tambahkan fungsi untuk membuat kombinasi LAT
function createLATCombinations($pdo, $la_id) {
    try {
        // Ambil semua tahun yang ada
        $stmt = $pdo->query("SELECT tahun_id FROM tahun");
        $tahunList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $insertCount = 0;
        // Buat kombinasi untuk setiap tahun
        foreach ($tahunList as $tahun) {
            // Cek apakah kombinasi sudah ada
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM lat WHERE la_id = ? AND tahun_id = ?");
            $checkStmt->execute([$la_id, $tahun['tahun_id']]);
            $exists = $checkStmt->fetchColumn() > 0;
            
            if (!$exists) {
                $stmt = $pdo->prepare("INSERT INTO lat (la_id, tahun_id) VALUES (?, ?)");
                $stmt->execute([$la_id, $tahun['tahun_id']]);
                $insertCount++;
            }
        }
        return $insertCount; // Return jumlah kombinasi baru yang dibuat
    } catch (PDOException $e) {
        error_log('Error creating LAT combinations: ' . $e->getMessage());
        throw $e;
    }
}
// Add LA
function addLA($pdo, $data) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO la (nama, keterangan) VALUES (?, ?)");
        $stmt->execute([$data['nama'], $data['keterangan']]);
        
        $la_id = $pdo->lastInsertId();
        
        // Buat kombinasi LAT untuk LA baru
        $insertCount = createLATCombinations($pdo, $la_id);
        
        $pdo->commit();
        return [
            'success' => true,
            'message' => 'Lembaga Akreditasi berhasil ditambahkan' . 
                        ($insertCount > 0 ? " dengan $insertCount kombinasi LAT baru" : '')
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Error in addLA: ' . $e->getMessage());
        throw $e;
    }
}

// Update LA
function updateLA($pdo, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE la SET nama = ?, keterangan = ? WHERE la_id = ?");
        return $stmt->execute([$data['nama'], $data['keterangan'], $data['la_id']]);
    } catch (PDOException $e) {
        error_log('Error in updateLA: ' . $e->getMessage());
        throw $e;
    }
}

// Delete LA
function deleteLA($pdo, $id) {
    try {
        // Check if LA is being used in objeknilai
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM objeknilai o
            JOIN lat l ON o.lat_id = l.lat_id
            WHERE l.la_id = ?
        ");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            throw new Exception('Lembaga Akreditasi tidak dapat dihapus karena sedang digunakan dalam penilaian');
        }

        $pdo->beginTransaction();

        // Delete from lat first
        $stmt = $pdo->prepare("DELETE FROM lat WHERE la_id = ?");
        $stmt->execute([$id]);

        // Then delete from la
        $stmt = $pdo->prepare("DELETE FROM la WHERE la_id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        return [
            'success' => true,
            'message' => 'Lembaga Akreditasi berhasil dihapus beserta semua kombinasi LAT terkait'
        ];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error in deleteLA: ' . $e->getMessage());
        throw new Exception('Gagal menghapus Lembaga Akreditasi: ' . $e->getMessage());
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get_la':
            echo json_encode(getLA($pdo));
            break;

        case 'add':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                try {
                    $result = addLA($pdo, $data);
                    echo json_encode($result);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
            break;

        case 'update':
            if ($method === 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (updateLA($pdo, $data)) {
                    echo json_encode(['message' => 'Lembaga Akreditasi berhasil diupdate']);
                } else {
                    throw new Exception('Gagal mengupdate Lembaga Akreditasi');
                }
            }
            break;

        case 'delete':
            if ($method === 'DELETE') {
                $id = $_GET['id'] ?? null;
                if ($id) {
                    try {
                        $result = deleteLA($pdo, $id);
                        echo json_encode($result);
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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>