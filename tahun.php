<?php
require_once 'db.php';

// Get semua tahun
function getTahun($pdo) {
    try {
        $stmt = $pdo->query("SELECT tahun_id, nama, status_id FROM tahun ORDER BY nama DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getTahun: ' . $e->getMessage());
        return [];
    }
}

// Tambahkan fungsi untuk membuat kombinasi LAT
function createLATCombinations($pdo, $tahun_id) {
    try {
        // Ambil semua LA yang ada
        $stmt = $pdo->query("SELECT la_id FROM la");
        $laList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $insertCount = 0;
        // Buat kombinasi untuk setiap LA
        foreach ($laList as $la) {
            // Cek apakah kombinasi sudah ada
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM lat WHERE la_id = ? AND tahun_id = ?");
            $checkStmt->execute([$la['la_id'], $tahun_id]);
            $exists = $checkStmt->fetchColumn() > 0;
            
            if (!$exists) {
                $stmt = $pdo->prepare("INSERT INTO lat (la_id, tahun_id) VALUES (?, ?)");
                $stmt->execute([$la['la_id'], $tahun_id]);
                $insertCount++;
            }
        }
        return $insertCount; // Return jumlah kombinasi baru yang dibuat
    } catch (PDOException $e) {
        error_log('Error creating LAT combinations: ' . $e->getMessage());
        throw $e;
    }
}

// Add tahun
function addTahun($pdo, $data) {
    try {
        // Jika status_id 1, nonaktifkan tahun lain yang aktif
        if ($data['status_id'] == 1) {
            $pdo->exec("UPDATE tahun SET status_id = 2 WHERE status_id = 1");
        }

        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO tahun (nama, status_id) VALUES (?, ?)");
        $stmt->execute([$data['nama'], $data['status_id']]);
        
        $tahun_id = $pdo->lastInsertId();
        
        // Buat kombinasi LAT untuk tahun baru
        $insertCount = createLATCombinations($pdo, $tahun_id);
        
        $pdo->commit();
        return [
            'success' => true,
            'message' => 'Tahun berhasil ditambahkan' . 
                        ($insertCount > 0 ? " dengan $insertCount kombinasi LAT baru" : '')
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Error in addTahun: ' . $e->getMessage());
        throw $e;
    }
}

// Update tahun
function updateTahun($pdo, $data) {
    try {
        // Jika status_id 1, nonaktifkan tahun lain yang aktif
        if ($data['status_id'] == 1) {
            $pdo->exec("UPDATE tahun SET status_id = 2 WHERE status_id = 1 AND tahun_id != " . intval($data['tahun_id']));
        }

        $stmt = $pdo->prepare("UPDATE tahun SET nama = ?, status_id = ? WHERE tahun_id = ?");
        return $stmt->execute([
            $data['nama'],
            $data['status_id'],
            $data['tahun_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateTahun: ' . $e->getMessage());
        throw $e;
    }
}

// Delete tahun (hanya jika status tidak aktif)
function deleteTahun($pdo, $id) {
    try {
        // Check if tahun is being used in objeknilai
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM objeknilai o
            JOIN lat l ON o.lat_id = l.lat_id
            WHERE l.tahun_id = ?
        ");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            throw new Exception('Tahun tidak dapat dihapus karena sedang digunakan dalam penilaian');
        }

        // Check status
        $stmt = $pdo->prepare("SELECT status_id FROM tahun WHERE tahun_id = ?");
        $stmt->execute([$id]);
        $tahun = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tahun && $tahun['status_id'] == 1) {
            throw new Exception('Tahun dengan status Aktif tidak dapat dihapus');
        }

        $pdo->beginTransaction();

        // Delete from lat first
        $stmt = $pdo->prepare("DELETE FROM lat WHERE tahun_id = ?");
        $stmt->execute([$id]);

        // Then delete from tahun
        $stmt = $pdo->prepare("DELETE FROM tahun WHERE tahun_id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        return [
            'success' => true,
            'message' => 'Tahun berhasil dihapus beserta semua kombinasi LAT terkait'
        ];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error in deleteTahun: ' . $e->getMessage());
        throw new Exception('Gagal menghapus Tahun: ' . $e->getMessage());
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_tahun':
        echo json_encode(getTahun($pdo));
        break;
    
    case 'add':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                $result = addTahun($pdo, $data);
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
            try {
                if (updateTahun($pdo, $data)) {
                    echo json_encode(['message' => 'Tahun berhasil diupdate']);
                } else {
                    throw new Exception('Gagal mengupdate tahun');
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
                    $result = deleteTahun($pdo, $id);
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
?>