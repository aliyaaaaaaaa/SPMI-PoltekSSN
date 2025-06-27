<?php
require_once 'db.php';

// Get evaluation data for a specific on_id

function get_evaluasi($pdo, $on_id) {
    // Ambil semua evaluasi untuk on_id
    $sql = "SELECT edi_id, subssm_id, nm_id FROM edi WHERE on_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$on_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil semua dokumen_edi untuk edi_id yang ditemukan
    $edi_ids = array_column($results, 'edi_id');
    $dokumen_map = [];
    if ($edi_ids) {
        $in = str_repeat('?,', count($edi_ids) - 1) . '?';
        $sql = "SELECT de.edi_id, d.dokumen_id, d.nama_file, d.path_file
                FROM dokumen_edi de
                JOIN dokumen d ON de.dokumen_id = d.dokumen_id
                WHERE de.edi_id IN ($in)";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute($edi_ids);
        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dokumen_map[$row['edi_id']][] = [
                'dokumen_id' => $row['dokumen_id'],
                'nama_file' => $row['nama_file'],
                'path_file' => $row['path_file'],
            ];
        }
    }

    // Gabungkan hasil evaluasi dengan daftar dokumen
    $evaluasi_map = [];
    foreach ($results as $row) {
        $edi_id = $row['edi_id'];
        $row['dokumen_list'] = $dokumen_map[$edi_id] ?? [];
        $row['has_dokumen'] = !empty($row['dokumen_list']);
        $evaluasi_map[$row['subssm_id']] = $row;
    }
    return $evaluasi_map;
}

// Save or update evaluation data, now handles multipart/form-data
function save_evaluasi($pdo, $post_data, $file_data) {
    if (empty($post_data['on_id']) || empty($post_data['subssm_id'])) {
        return ['success' => false, 'error' => 'on_id and subssm_id are required.'];
    }

    $on_id = $post_data['on_id'];
    $subssm_id = $post_data['subssm_id'];
    $nm_id = $post_data['nm_id'] ?? 1; // Default nm_id as it's NOT NULL    
    $dokumen_id = null;

    // 1. Upload file jika ada
    if (isset($file_data['dokumen']) && $file_data['dokumen']['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid() . '_' . $file_data['dokumen']['name'];
        move_uploaded_file($file_data['dokumen']['tmp_name'], __DIR__ . '/../frontendold/uploads/' . $filename);

        // 2. Insert ke tabel dokumen
        $stmt = $pdo->prepare("INSERT INTO dokumen (nama_file, path_file, tanggal_upload) VALUES (?, ?, NOW())");
        $stmt->execute([$file_data['dokumen']['name'], $filename]);
        $dokumen_id = $pdo->lastInsertId();
    }

    // 3. Simpan/update evaluasi desk (tanpa kolom dokumen blob)
    // Using a unique key on (on_id, subssm_id) is crucial for this to work
    $sql = "INSERT INTO edi (on_id, subssm_id, nm_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                nm_id = VALUES(nm_id),
                edi_id = LAST_INSERT_ID(edi_id)"; // Keep the same ID on update
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $on_id,
            $subssm_id,
            $nm_id
        ]);

        // 4. Jika ada dokumen, insert ke dokumen_edi
        if ($dokumen_id) {
            // Pastikan sudah dapat edi_id (misal dari insert/update edi)
            $edi_id = $pdo->lastInsertId(); // Ambil edi_id terakhir yang diinsert
            $pdo->prepare("INSERT INTO dokumen_edi (dokumen_id, edi_id) VALUES (?, ?)")->execute([$dokumen_id, $edi_id]);
        }

        return ['success' => true, 'message' => 'Evaluasi berhasil disimpan.'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

// Main request handler
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get_evaluasi':
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            break;
        }
        $on_id = $_GET['on_id'] ?? null;
        if (!$on_id) {
            http_response_code(400);
            echo json_encode(['error' => 'on_id parameter is missing.']);
            break;
        }
        echo json_encode(get_evaluasi($pdo, $on_id));
        break;

    case 'save_evaluasi':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            break;
        }
        // Data is now expected as multipart/form-data, not raw JSON
        echo json_encode(save_evaluasi($pdo, $_POST, $_FILES));
        break;

    case 'get_nilaimutu':
        $lat_id = $_GET['lat_id'] ?? null;
        if (!$lat_id) {
            echo json_encode([]);
            break;
        }
        $stmt = $pdo->prepare("SELECT nm_id, nilai, keterangan FROM nilaimutu WHERE lat_id = ?");
        $stmt->execute([$lat_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'get_bukti_dokumen':
        // Ambil dokumen bukti evaluasi diri berdasarkan edi_id
        $edi_id = $_GET['edi_id'] ?? null;
        if (!$edi_id) {
            echo json_encode([]);
            exit;
        }
        $sql = "SELECT d.dokumen_id, d.nama_file, d.path_file
                FROM dokumen_edi de
                JOIN dokumen d ON de.dokumen_id = d.dokumen_id
                WHERE de.edi_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$edi_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?>