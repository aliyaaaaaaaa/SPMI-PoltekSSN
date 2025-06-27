<?php
require_once 'db.php';

function getProdi($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.*, a.nama as akreditasi_nama
            FROM prodi p
            LEFT JOIN akreditasi a ON p.akreditasi_id = a.akreditasi_id
            ORDER BY p.nama
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getProdi: ' . $e->getMessage());
        return [];
    }
}

function getAkreditasi($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM akreditasi ORDER BY nama");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getAkreditasi: ' . $e->getMessage());
        return [];
    }
}

function uploadFile($file) {
    $target_dir = "../uploads/akreditasi/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $file_name = time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return 'uploads/akreditasi/' . $file_name;
    }
    return null;
}

function addProdi($pdo, $data, $file) {
    try {
        $file_path = null;
        if ($file && $file['error'] == 0) {
            $file_path = uploadFile($file);
        }

        $stmt = $pdo->prepare("
            INSERT INTO prodi (
                nama, standar_id, tanggal_akreditasi, 
                tanggal_expired, nomorSK, akreditasi_id, 
                grup_id, dokumenakreditasi
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['nama'],
            $data['standar_id'],
            $data['tanggal_akreditasi'],
            $data['tanggal_expired'],
            $data['nomorSK'],
            $data['akreditasi_id'],
            $data['grup_id'],
            $file_path
        ]);
    } catch (PDOException $e) {
        error_log('Error in addProdi: ' . $e->getMessage());
        return false;
    }
}

function updateProdi($pdo, $data, $file) {
    try {
        $file_path = null;
        if ($file && $file['error'] == 0) {
            $file_path = uploadFile($file);
        }

        $sql = "
            UPDATE prodi 
            SET nama = ?,
                tanggal_akreditasi = ?,
                tanggal_expired = ?,
                nomorSK = ?,
                akreditasi_id = ?
        ";
        $params = [
            $data['nama'],
            $data['tanggal_akreditasi'],
            $data['tanggal_expired'],
            $data['nomorSK'],
            $data['akreditasi_id']
        ];

        if ($file_path) {
            $sql .= ", dokumenakreditasi = ?";
            $params[] = $file_path;
        }

        $sql .= " WHERE role_id = ?";
        $params[] = $data['role_id'];

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('Error in updateProdi: ' . $e->getMessage());
        return false;
    }
}

function deleteProdi($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM prodi WHERE role_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Error in deleteProdi: ' . $e->getMessage());
        return false;
    }
}

// Handle requests
$action = $_GET['action'] ?? '';
$data = $_POST;

header('Content-Type: application/json');

switch ($action) {
    case 'get_prodi':
        echo json_encode(getProdi($pdo));
        break;

    case 'get_akreditasi':
        echo json_encode(getAkreditasi($pdo));
        break;

    case 'add':
        $result = addProdi($pdo, $data, $_FILES['dokumenakreditasi'] ?? null);
        echo json_encode(['success' => $result]);
        break;

    case 'update':
        $result = updateProdi($pdo, $data, $_FILES['dokumenakreditasi'] ?? null);
        echo json_encode(['success' => $result]);
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $result = deleteProdi($pdo, $id);
            echo json_encode(['success' => $result]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?> 