<?php
require_once 'db.php';

// Get semua periode
function getPeriode($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                p.periode_id,
                DATE_FORMAT(p.periode_edisi_start, '%Y-%m-%d') as periode_edisi_start,
                DATE_FORMAT(p.periode_edisi_end, '%Y-%m-%d') as periode_edisi_end,
                DATE_FORMAT(p.periode_edesk_start, '%Y-%m-%d') as periode_edesk_start,
                DATE_FORMAT(p.periode_edesk_end, '%Y-%m-%d') as periode_edesk_end,
                DATE_FORMAT(p.periode_visitatif_start, '%Y-%m-%d') as periode_visitatif_start,
                DATE_FORMAT(p.periode_visitatif_end, '%Y-%m-%d') as periode_visitatif_end,
                t.nama as tahun_nama,
                la.nama as la_nama
            FROM periode p
            JOIN lat ON p.lat_id = lat.lat_id
            JOIN tahun t ON lat.tahun_id = t.tahun_id
            JOIN la ON lat.la_id = la.la_id
            ORDER BY t.nama DESC
        ");
        
        // Debug: log query result
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log('getPeriode result: ' . print_r($result, true));
        
        return $result;
    } catch (PDOException $e) {
        error_log('Error in getPeriode: ' . $e->getMessage());
        // Debug: log the actual SQL error
        error_log('SQL Error: ' . print_r($e->errorInfo, true));
        return [];
    }
}

// Get semua tahun
function getTahun($pdo) {
    $stmt = $pdo->query("SELECT tahun_id, nama FROM tahun ORDER BY nama DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get semua LA (Lembaga Akreditasi)
function getLA($pdo) {
    $stmt = $pdo->query("SELECT la_id, nama FROM la ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Tambahkan fungsi untuk get PSM
function getPSM($pdo) {
    $stmt = $pdo->query("SELECT psm_id, nama FROM psm ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengecek jumlah kolom psm_id yang ada
function getExistingPsmColumns($pdo) {
    $stmt = $pdo->query("SHOW COLUMNS FROM periode LIKE 'psm_id_%'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menambah kolom psm_id baru
function addPsmColumn($pdo, $columnNumber) {
    try {
        $columnName = "psm_id_" . $columnNumber;
        $sql = "ALTER TABLE periode 
                ADD COLUMN {$columnName} INT(11),
                ADD CONSTRAINT fk_{$columnName} 
                FOREIGN KEY ({$columnName}) REFERENCES psm(psm_id)";
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        error_log("Error adding PSM column: " . $e->getMessage());
        return false;
    }
}

// Tambah periode baru
function addPeriode($pdo, $data) {
    try {
        // 1. Cek apakah perlu menambah kolom baru
        //$existingColumns = getExistingPsmColumns($pdo);
        //$currentPsmColumns = count($existingColumns);
        //$selectedPsmCount = count($data['psm_ids']);

        // 2. Tambah kolom baru jika diperlukan
        //while ($currentPsmColumns < $selectedPsmCount) {
        //    $currentPsmColumns++;
        //    if (!addPsmColumn($pdo, $currentPsmColumns)) {
        //        throw new Exception("Gagal menambah kolom PSM baru");
        //    }
        //}

        // 3. Buat query insert dinamis
        $columns = [
            'periode_edisi_start',
            'periode_edisi_end',
            'periode_edesk_start',
            'periode_edesk_end',
            'periode_visitatif_start',
            'periode_visitatif_end',
            'lat_id'
        ];
        
        $values = [
            $data['periode_edisi_start'],
            $data['periode_edisi_end'],
            $data['periode_edesk_start'],
            $data['periode_edesk_end'],
            $data['periode_visitatif_start'],
            $data['periode_visitatif_end'],
            $data['lat_id']
        ];

        // Tambahkan psm_id ke query
        //for ($i = 0; $i < count($data['psm_ids']); $i++) {
        //    $columns[] = "psm_id_" . ($i + 1);
        //    $values[] = $data['psm_ids'][$i];
        //}

        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $columnString = implode(',', $columns);

        // 4. Execute query
        $stmt = $pdo->prepare("
            INSERT INTO periode ({$columnString})
            VALUES ({$placeholders})
        ");
        
        return $stmt->execute($values);
    } catch (Exception $e) {
        error_log("Error in addPeriode: " . $e->getMessage());
        throw $e;
    }
}

// Tambahkan fungsi update
function updatePeriode($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            UPDATE periode 
            SET 
                periode_edisi_start = ?,
                periode_edisi_end = ?,
                periode_edesk_start = ?,
                periode_edesk_end = ?,
                periode_visitatif_start = ?,
                periode_visitatif_end = ?
            WHERE periode_id = ?
        ");
        
        return $stmt->execute([
            $data['periode_edisi_start'],
            $data['periode_edisi_end'],
            $data['periode_edesk_start'],
            $data['periode_edesk_end'],
            $data['periode_visitatif_start'],
            $data['periode_visitatif_end'],
            $data['periode_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updatePeriode: ' . $e->getMessage());
        throw $e;
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_periode':
        echo json_encode(getPeriode($pdo));
        break;

    case 'get_tahun':
        echo json_encode(getTahun($pdo));
        break;

    case 'get_la':
        echo json_encode(getLA($pdo));
        break;

    case 'get_psm':
        echo json_encode(getPSM($pdo));
        break;
        
    case 'add':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                // Dapatkan lat_id berdasarkan tahun_id dan la_id
                $stmt = $pdo->prepare("
                    SELECT lat_id 
                    FROM lat 
                    WHERE tahun_id = ? AND la_id = ?
                ");
                $stmt->execute([$data['tahun_id'], $data['la_id']]);
                $lat = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$lat) {
                    throw new Exception('Kombinasi tahun dan LA tidak valid');
                }
                
                $data['lat_id'] = $lat['lat_id'];
                
                if (addPeriode($pdo, $data)) {
                    echo json_encode(['message' => 'Periode berhasil ditambahkan']);
                } else {
                    throw new Exception('Gagal menambahkan periode');
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
        break;
        
    case 'update':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Cek apakah kombinasi tahun_id dan la_id sudah ada di tabel lat
            $stmt = $pdo->prepare("SELECT lat_id FROM lat WHERE tahun_id = ? AND la_id = ?");
            $stmt->execute([$data['tahun_id'], $data['la_id']]);
            $lat = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$lat) {
                // Jika tidak ada, buat lat_id baru
                $stmt = $pdo->prepare("INSERT INTO lat (tahun_id, la_id) VALUES (?, ?)");
                $stmt->execute([$data['tahun_id'], $data['la_id']]);
                $lat_id = $pdo->lastInsertId();
            } else {
                $lat_id = $lat['lat_id'];
            }
            
            // Update data periode
            $stmt = $pdo->prepare("
                UPDATE periode 
                SET lat_id = ?,
                    periode_edisi_start = ?,
                    periode_edisi_end = ?,
                    periode_edesk_start = ?,
                    periode_edesk_end = ?,
                    periode_visitatif_start = ?,
                    periode_visitatif_end = ?
                WHERE periode_id = ?
            ");
            
            $result = $stmt->execute([
                $lat_id,
                $data['periode_edisi_start'],
                $data['periode_edisi_end'],
                $data['periode_edesk_start'],
                $data['periode_edesk_end'],
                $data['periode_visitatif_start'],
                $data['periode_visitatif_end'],
                $data['periode_id']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update periode']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $periode_id = $_GET['id'] ?? null;
            
            if (!$periode_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Periode ID is required']);
                break;
            }
            
            // Cek apakah periode bisa dihapus (sesuai aturan bisnis)
            $stmt = $pdo->prepare("
                SELECT t.status_id 
                FROM periode p
                JOIN lat l ON p.lat_id = l.lat_id
                JOIN tahun t ON l.tahun_id = t.tahun_id
                WHERE p.periode_id = ?
            ");
            $stmt->execute([$periode_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['status_id'] == 1) {
                http_response_code(400);
                echo json_encode(['error' => 'Tidak dapat menghapus periode dengan status tahun Aktif']);
                break;
            }
            
            // Hapus periode
            $stmt = $pdo->prepare("DELETE FROM periode WHERE periode_id = ?");
            $result = $stmt->execute([$periode_id]);
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete periode']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?> 