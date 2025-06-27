<?php
require_once 'db.php';

function getGKM($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT * FROM gkm 
            ORDER BY nama
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getGKM: ' . $e->getMessage());
        return [];
    }
}

function addGKM($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO gkm (
                nama, keterangan, grup_id
            ) VALUES (?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['nama'],
            $data['keterangan'],
            $data['grup_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in addGKM: ' . $e->getMessage());
        return false;
    }
}

function updateGKM($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            UPDATE gkm 
            SET nama = ?,
                keterangan = ?
            WHERE role_id = ?
        ");
        
        return $stmt->execute([
            $data['nama'],
            $data['keterangan'],
            $data['role_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateGKM: ' . $e->getMessage());
        return false;
    }
}

function deleteGKM($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM gkm WHERE role_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Error in deleteGKM: ' . $e->getMessage());
        return false;
    }
}

// Handle requests
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

switch ($action) {
    case 'get_gkm':
        echo json_encode(getGKM($pdo));
        break;

    case 'add':
        $result = addGKM($pdo, $data);
        echo json_encode(['success' => $result]);
        break;

    case 'update':
        $result = updateGKM($pdo, $data);
        echo json_encode(['success' => $result]);
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $result = deleteGKM($pdo, $id);
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