<?php
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($action == 'list') {
    $kategori = $_GET['kategori'] ?? '';
    $jenis = $_GET['jenis'] ?? '';
    $tahun = $_GET['tahun'] ?? '';
    $unit = $_GET['unit'] ?? '';
    $where = [];
    $params = [];
    if ($kategori) { $where[] = 'd.kd_id = ?'; $params[] = $kategori; }
    if ($jenis) { $where[] = 'd.tipe_dokumen = ?'; $params[] = $jenis; }
    if ($tahun) { 
        $where[] = 'CAST(d.tahun AS CHAR) LIKE ?'; 
        $params[] = "%$tahun%"; 
    }
    if ($unit) {
        if (preg_match('/^(gkm|prodi|auditor)_(\d+)$/', $unit, $m)) {
            $where[] = 'd.user_id = ?';
            $params[] = $m[2];
        }
    }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT d.*, 
            GROUP_CONCAT(p.nama SEPARATOR ', ') AS nama_prodi, k.nama_kd
            FROM dokumen d
            LEFT JOIN dokumen_prodi dp ON d.dokumen_id = dp.dokumen_id
            LEFT JOIN prodi p ON dp.prodi_id = p.role_id
            LEFT JOIN kd k ON d.kd_id = k.kd_id
            $whereSql
            GROUP BY d.dokumen_id
            ORDER BY d.dokumen_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Tambah dokumen
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $kd_id = $_POST['kategori_id'];
    $tipe_dokumen = $_POST['jenis_id'];
    $nama_file = $_FILES['file']['name'] ?? null;
    $tahun = $_POST['tahun'];
    $user_id = $_POST['user_id'];
    $filename = null;

    // Validasi user_id
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User pengunggah tidak valid!']);
        exit;
    }
    // Cek user_id di tabel user
    $cekUser = $pdo->prepare("SELECT COUNT(*) FROM user WHERE user_id = ?");
    $cekUser->execute([$user_id]);
    if ($cekUser->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'User pengunggah tidak ditemukan!']);
        exit;
    }

    if ($_FILES['file'] && $_FILES['file']['tmp_name']) {
        $filename = uniqid() . '_' . $_FILES['file']['name'];
        // Simpan ke folder frontendold/uploads
        move_uploaded_file(
            $_FILES['file']['tmp_name'],
            __DIR__ . '/../frontendold/Dokumen/' . $filename
        );
    }
    $stmt = $pdo->prepare("INSERT INTO dokumen (nama_file, path_file, tipe_dokumen, kd_id, user_id, tahun, tanggal_upload) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$nama_file, $filename, $tipe_dokumen, $kd_id, $user_id, $tahun]);
    $dokumen_id = $pdo->lastInsertId();

    // Insert ke dokumen_prodi
    if (!empty($_POST['prodi_ids'])) {
        foreach ($_POST['prodi_ids'] as $prodi_id) {
            $pdo->prepare("INSERT INTO dokumen_prodi (dokumen_id, prodi_id) VALUES (?, ?)")->execute([$dokumen_id, $prodi_id]);
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

// Ambil detail dokumen untuk edit
if ($action == 'get' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM dokumen WHERE dokumen_id = ?");
    $stmt->execute([$_GET['id']]);
    $dokumen = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ambil prodi_ids
    $prodi = $pdo->prepare("SELECT prodi_id FROM dokumen_prodi WHERE dokumen_id = ?");
    $prodi->execute([$_GET['id']]);
    $dokumen['prodi_ids'] = $prodi->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($dokumen);
    exit;
}

// Update dokumen
if ($action == 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dokumen_id = $_POST['dokumen_id'];
    $kd_id = $_POST['kategori_id'];
    $tipe_dokumen = $_POST['jenis_id'];
    $tahun = $_POST['tahun'];
    $user_id = $_POST['user_id'];
    $nama_file = $_POST['nama_file'];
    $filename = $_POST['old_path_file'];
    // Jika ada file baru diupload
    if (isset($_FILES['file']) && $_FILES['file']['tmp_name']) {
        $filename = uniqid() . '_' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/../frontendold/Dokumen/'. $filename);
    }
    $stmt = $pdo->prepare("UPDATE dokumen SET nama_file=?, path_file=?, tipe_dokumen=?, kd_id=?, user_id=?, tahun=? WHERE dokumen_id=?");
    $stmt->execute([$nama_file, $filename, $tipe_dokumen, $kd_id, $user_id, $tahun, $dokumen_id]);

    // Update dokumen_prodi
    $pdo->prepare("DELETE FROM dokumen_prodi WHERE dokumen_id=?")->execute([$dokumen_id]);
    if (!empty($_POST['prodi_ids'])) {
        foreach ($_POST['prodi_ids'] as $prodi_id) {
            $pdo->prepare("INSERT INTO dokumen_prodi (dokumen_id, prodi_id) VALUES (?, ?)")->execute([$dokumen_id, $prodi_id]);
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

// Hapus dokumen
if ($action == 'delete') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM dokumen WHERE dokumen_id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

// Dropdown kategori (KD)
if ($action == 'kategori') {
    $stmt = $pdo->query("SELECT * FROM kd");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Dropdown jenis dokumen (enum manual)
if ($action == 'jenis') {
    echo json_encode([
        ['id' => 'temuan', 'nama' => 'Temuan'],
        ['id' => 'data_dukung', 'nama' => 'Data Dukung']
    ]);
    exit;
}

// Dropdown unit pengunggah
if ($action == 'unit') {
    $gkm = $pdo->query("SELECT g.user_id, g.nama FROM gkm g INNER JOIN user u ON g.user_id = u.user_id")->fetchAll(PDO::FETCH_ASSOC);
    $prodi = $pdo->query("SELECT p.user_id, p.nama FROM prodi p INNER JOIN user u ON p.user_id = u.user_id")->fetchAll(PDO::FETCH_ASSOC);
    $auditor = $pdo->query("SELECT a.user_id, a.nama FROM auditor a INNER JOIN user u ON a.user_id = u.user_id")->fetchAll(PDO::FETCH_ASSOC);
    $unit = [];
    foreach ($gkm as $g) $unit[] = ['id' => 'gkm_' . $g['user_id'], 'value' => 'gkm_' . $g['user_id'], 'label' => '[GKM] ' . $g['nama']];
    foreach ($prodi as $p) $unit[] = ['id' => 'prodi_' . $p['user_id'], 'value' => 'prodi_' . $p['user_id'], 'label' => '[Prodi] ' . $p['nama']];
    foreach ($auditor as $a) $unit[] = ['id' => 'auditor_' . $a['user_id'], 'value' => 'auditor_' . $a['user_id'], 'label' => '[Auditor] ' . $a['nama']];
    echo json_encode($unit);
    exit;
}

if ($action == 'prodi') {
    $stmt = $pdo->query("SELECT role_id, nama FROM prodi");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
?>