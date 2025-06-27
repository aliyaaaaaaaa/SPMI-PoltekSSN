<?php
require_once 'db.php';

function getAuditor($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM auditor a");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error in getAuditor: ' . $e->getMessage());
        return [];
    }
}

function addAuditor($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO auditor (
                role_id, jabatan, instansi, nama
            ) VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['role_id'],
            $data['jabatan'],
            $data['instansi'],
            $data['nama']
        ]);
    } catch (PDOException $e) {
        error_log('Error in addAuditor: ' . $e->getMessage());
        return false;
    }
}

function updateAuditor($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            UPDATE auditor 
            SET jabatan = ?,
                instansi = ?,
                nama = ?
            WHERE role_id = ?
        ");
        
        return $stmt->execute([
            $data['jabatan'],
            $data['instansi'],
            $data['nama'],
            $data['role_id']
        ]);
    } catch (PDOException $e) {
        error_log('Error in updateAuditor: ' . $e->getMessage());
        return false;
    }
}

function deleteAuditor($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM auditor WHERE role_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log('Error in deleteAuditor: ' . $e->getMessage());
        return false;
    }
}

// Handle requests
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

switch ($action) {
    case 'get_auditor':
        echo json_encode(getAuditor($pdo));
        break;

    case 'add':
        $result = addAuditor($pdo, $data);
        echo json_encode(['success' => $result]);
        break;

    case 'update':
        $result = updateAuditor($pdo, $data);
        echo json_encode(['success' => $result]);
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $result = deleteAuditor($pdo, $id);
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