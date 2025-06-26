<?php
require_once 'db.php';

// Get data kegiatan dengan join ke tabel terkait
function getKegiatan($pdo) {
    $sql = "SELECT k.*, sm.nama as standar_nama, i.nama as indikator_nama 
            FROM kegiatan k
            JOIN standar sm ON k.standar_id = sm.standar_id
            LEFT JOIN indikator i ON k.indikator_id = i.indikator_id
            ORDER BY sm.nama, k.nomor";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get semua standar mutu
function getStandarMutu($pdo) {
    $stmt = $pdo->query("SELECT standar_id, nama FROM standar ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get semua indikator
function getIndikator($pdo) {
    $stmt = $pdo->query("SELECT indikator_id, jenis FROM indikator ORDER BY jenis");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add new functions to get filter options
function getTahun($pdo) {
    $stmt = $pdo->query("SELECT tahun_id, nama FROM tahun ORDER BY nama DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLA($pdo) {
    $stmt = $pdo->query("SELECT la_id, nama FROM la ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Modify getProdi function to use role_id
function getProdi($pdo) {
    $stmt = $pdo->query("SELECT role_id as prodi_id, nama FROM prodi ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Modify getStandarHirarki to handle perubahan_psm
function getStandarHirarki($pdo, $tahun_id = null, $la_id = null, $prodi_id = null) {
    // Jika semua filter kosong, ambil semua standar tanpa perubahan
    if (!$tahun_id && !$la_id && !$prodi_id) {
        $standar_sql = "SELECT * FROM standar ORDER BY standar_id";
        $standar_stmt = $pdo->prepare($standar_sql);
        $standar_stmt->execute();
        $standar = $standar_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get related PSM, SSM, and SubSSM data
        foreach ($standar as &$s) {
            // Get PSM
            $psm_sql = "SELECT * FROM psm WHERE standar_id = ? ORDER BY psm_id";
            $psm_stmt = $pdo->prepare($psm_sql);
            $psm_stmt->execute([$s['standar_id']]);
            $psm_list = $psm_stmt->fetchAll(PDO::FETCH_ASSOC);
            $s['psm'] = [];
            foreach ($psm_list as $psm) {
                // Get SSM
                $ssm_sql = "SELECT * FROM ssm WHERE psm_id = ? ORDER BY ssm_id";
                $ssm_stmt = $pdo->prepare($ssm_sql);
                $ssm_stmt->execute([$psm['psm_id']]);
                $ssm_list = $ssm_stmt->fetchAll(PDO::FETCH_ASSOC);
                $psm['ssm'] = [];
                foreach ($ssm_list as $ssm) {
                    // Get SubSSM
                    $subssm_sql = "SELECT * FROM subssm WHERE ssm_id = ? ORDER BY subssm_id";
                    $subssm_stmt = $pdo->prepare($subssm_sql);
                    $subssm_stmt->execute([$ssm['ssm_id']]);
                    $subssm_list = $subssm_stmt->fetchAll(PDO::FETCH_ASSOC);
                    $ssm['subssm'] = $subssm_list;
                    $psm['ssm'][] = $ssm;
                }
                $s['psm'][] = $psm;
            }
        }
        return $standar;
    }
    
    // Jika hanya 1 atau 2 filter yang dipilih, return empty array
    if ((!$tahun_id && !$la_id) || (!$tahun_id && !$prodi_id) || (!$la_id && !$prodi_id)) {
        return [];
    }
    
    // Jika semua filter dipilih, cari on_id yang sesuai
    $stmt = $pdo->prepare("SELECT on_id FROM objeknilai 
                          WHERE lat_id IN (SELECT lat_id FROM lat WHERE la_id = ? AND tahun_id = ?)
                          AND prodi_id = ?");
    $stmt->execute([$la_id, $tahun_id, $prodi_id]);
    $on_id = $stmt->fetchColumn();
    
    if (!$on_id) {
        return [];
    }
    
    // Get standar data HANYA YANG ADA DI OBJEKNILAI UNTUK on_id INI
    $standar_sql = "SELECT s.* FROM standar s
                    JOIN objeknilai o ON o.standar_id = s.standar_id
                    WHERE o.on_id = ?
                    ORDER BY s.standar_id";
    $standar_stmt = $pdo->prepare($standar_sql);
    $standar_stmt->execute([$on_id]);
    $standar = $standar_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil perubahan_psm, perubahan_ssm, perubahan_subssm SEKALI SAJA
    $perubahan_psm_sql = "SELECT * FROM perubahan_psm WHERE on_id = ? ORDER BY psm_id, updated_at DESC";
    $perubahan_psm_stmt = $pdo->prepare($perubahan_psm_sql);
    $perubahan_psm_stmt->execute([$on_id]);
    $perubahan_psm = $perubahan_psm_stmt->fetchAll(PDO::FETCH_ASSOC);

    $perubahan_ssm_sql = "SELECT * FROM perubahan_ssm WHERE on_id = ? ORDER BY ssm_id, updated_at DESC";
    $perubahan_ssm_stmt = $pdo->prepare($perubahan_ssm_sql);
    $perubahan_ssm_stmt->execute([$on_id]);
    $perubahan_ssm = $perubahan_ssm_stmt->fetchAll(PDO::FETCH_ASSOC);

    $perubahan_subssm_sql = "SELECT * FROM perubahan_subssm WHERE on_id = ? ORDER BY subssm_id, updated_at DESC";
    $perubahan_subssm_stmt = $pdo->prepare($perubahan_subssm_sql);
    $perubahan_subssm_stmt->execute([$on_id]);
    $perubahan_subssm = $perubahan_subssm_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil perubahan_standar untuk on_id ini
    $perubahan_standar_sql = "SELECT * FROM perubahan_standar WHERE on_id = ? ORDER BY standar_id, updated_at DESC";
    $perubahan_standar_stmt = $pdo->prepare($perubahan_standar_sql);
    $perubahan_standar_stmt->execute([$on_id]);
    $perubahan_standar = $perubahan_standar_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process each standar and apply changes
    foreach ($standar as &$s) {
        // Merge perubahan_standar
        $latest_changes = [];
        foreach ($perubahan_standar as $p) {
            if ($p['standar_id'] == $s['standar_id']) {
                if (!isset($latest_changes[$p['kolom']]) || strtotime($p['updated_at']) > strtotime($latest_changes[$p['kolom']]['updated_at'])) {
                    $latest_changes[$p['kolom']] = $p;
                }
            }
        }
        foreach ($latest_changes as $change) {
            $s[$change['kolom']] = $change['newval'];
        }
        // Get PSM
        $psm_sql = "SELECT * FROM psm WHERE standar_id = ? ORDER BY psm_id";
        $psm_stmt = $pdo->prepare($psm_sql);
        $psm_stmt->execute([$s['standar_id']]);
        $psm_list = $psm_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filter out deleted PSM and apply changes
        $s['psm'] = [];
        foreach ($psm_list as $psm) {
            $psm_id = $psm['psm_id'];
            $is_deleted = false;
            $latest_changes = [];
            // Find latest changes for this PSM
            foreach ($perubahan_psm as $p) {
                if ($p['psm_id'] == $psm_id) {
                    if (!isset($latest_changes[$p['kolom']]) || 
                        strtotime($p['updated_at']) > strtotime($latest_changes[$p['kolom']]['updated_at'])) {
                        $latest_changes[$p['kolom']] = $p;
                    }
                }
            }
            // Check if PSM is deleted
            if (isset($latest_changes['nama']) && $latest_changes['nama']['newval'] === null) {
                $is_deleted = true;
            }
            if (!$is_deleted) {
                // Apply changes to PSM
                foreach ($latest_changes as $change) {
                    $psm[$change['kolom']] = $change['newval'];
                }
                // Get SSM
                $ssm_sql = "SELECT * FROM ssm WHERE psm_id = ? ORDER BY ssm_id";
                $ssm_stmt = $pdo->prepare($ssm_sql);
                $ssm_stmt->execute([$psm_id]);
                $ssm_list = $ssm_stmt->fetchAll(PDO::FETCH_ASSOC);
                $ssm_final = [];
                foreach ($ssm_list as $ssm) {
                    $ssm_id = $ssm['ssm_id'];
                    $is_deleted_ssm = false;
                    $latest_changes_ssm = [];
                    foreach ($perubahan_ssm as $p) {
                        if ($p['ssm_id'] == $ssm_id) {
                            if (!isset($latest_changes_ssm[$p['kolom']]) || strtotime($p['updated_at']) > strtotime($latest_changes_ssm[$p['kolom']]['updated_at'])) {
                                $latest_changes_ssm[$p['kolom']] = $p;
                            }
                        }
                    }
                    if (isset($latest_changes_ssm['nama']) && $latest_changes_ssm['nama']['newval'] === null) {
                        $is_deleted_ssm = true;
                    }
                    if (!$is_deleted_ssm) {
                        foreach ($latest_changes_ssm as $change) {
                            $ssm[$change['kolom']] = $change['newval'];
                        }
                        // Get SubSSM
                        $subssm_sql = "SELECT * FROM subssm WHERE ssm_id = ? ORDER BY subssm_id";
                        $subssm_stmt = $pdo->prepare($subssm_sql);
                        $subssm_stmt->execute([$ssm_id]);
                        $subssm_list = $subssm_stmt->fetchAll(PDO::FETCH_ASSOC);
                        $subssm_final = [];
                        foreach ($subssm_list as $subssm) {
                            $subssm_id = $subssm['subssm_id'];
                            $is_deleted_subssm = false;
                            $latest_changes_subssm = [];
                            foreach ($perubahan_subssm as $p) {
                                if ($p['subssm_id'] == $subssm_id) {
                                    if (!isset($latest_changes_subssm[$p['kolom']]) || strtotime($p['updated_at']) > strtotime($latest_changes_subssm[$p['kolom']]['updated_at'])) {
                                        $latest_changes_subssm[$p['kolom']] = $p;
                                    }
                                }
                            }
                            if (isset($latest_changes_subssm['nama']) && $latest_changes_subssm['nama']['newval'] === null) {
                                $is_deleted_subssm = true;
                            }
                            if (!$is_deleted_subssm) {
                                foreach ($latest_changes_subssm as $change) {
                                    $subssm[$change['kolom']] = $change['newval'];
                                }
                                $subssm_final[] = $subssm;
                            }
                        }
                        $ssm['subssm'] = $subssm_final;
                        $ssm_final[] = $ssm;
                    }
                }
                $psm['ssm'] = $ssm_final;
                $s['psm'][] = $psm;
            }
        }
    }
    return $standar;
}

// Fungsi untuk menghapus semua perubahan_xxx terkait id tertentu (psm_id, ssm_id, subssm_id, standar_id)
function deleteAllPerubahanPSM($pdo, $psm_id) {
    $stmt = $pdo->prepare("DELETE FROM perubahan_psm WHERE psm_id = ?");
    $stmt->execute([$psm_id]);
}
function deleteAllPerubahanSSM($pdo, $ssm_id) {
    $stmt = $pdo->prepare("DELETE FROM perubahan_ssm WHERE ssm_id = ?");
    $stmt->execute([$ssm_id]);
}
function deleteAllPerubahanSubSSM($pdo, $subssm_id) {
    $stmt = $pdo->prepare("DELETE FROM perubahan_subssm WHERE subssm_id = ?");
    $stmt->execute([$subssm_id]);
}
function deleteAllPerubahanStandar($pdo, $standar_id) {
    $stmt = $pdo->prepare("DELETE FROM perubahan_standar WHERE standar_id = ?");
    $stmt->execute([$standar_id]);
}

// Add new function to handle PSM changes
function handlePSMChange($pdo, $data) {
    if (empty($data['kolom']) || !array_key_exists('newval', $data)) {
        return ['success' => false, 'error' => 'Kolom dan nilai baru harus diisi'];
    }
    $on_id = $data['on_id'] ?? null;
    $kolom = $data['kolom'];
    if ($on_id && $kolom === 'nama') {
        return ['success' => false, 'error' => 'Nama tidak dapat diubah pada mode filter, silakan ubah di template.'];
    }
    try {
        $pdo->beginTransaction();
        $psm_id = $data['psm_id'];
        $newval = $data['newval'];
        $username = $data['username'];
        if ($on_id) {
            // Check if there's an existing change for this PSM and column
            $stmt = $pdo->prepare("SELECT perubahan_id FROM perubahan_psm 
                                 WHERE psm_id = ? AND on_id = ? AND kolom = ?
                                 ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$psm_id, $on_id, $kolom]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Ambil nilai newval lama
                $stmt = $pdo->prepare("SELECT newval FROM perubahan_psm WHERE perubahan_id = ?");
                $stmt->execute([$existing['perubahan_id']]);
                $prev_newval = $stmt->fetchColumn();

                // Update oldval = prev_newval, newval = nama baru
                $stmt = $pdo->prepare("UPDATE perubahan_psm SET oldval = ?, newval = ?, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$prev_newval, $newval, $existing['perubahan_id']]);
            } else {
                // Get old value from PSM table
                $stmt = $pdo->prepare("SELECT $kolom FROM psm WHERE psm_id = ?");
                $stmt->execute([$psm_id]);
                $oldval = $stmt->fetchColumn();
                
                // Insert new change
                $stmt = $pdo->prepare("INSERT INTO perubahan_psm 
                                     (psm_id, on_id, kolom, oldval, newval, username, updated_at)
                                     VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$psm_id, $on_id, $kolom, $oldval, $newval, $username]);
            }
        } else {
            // Direct update to PSM table
            $stmt = $pdo->prepare("UPDATE psm SET $kolom = ? WHERE psm_id = ?");
            $stmt->execute([$newval, $psm_id]);
            // Hapus semua perubahan_psm terkait psm_id ini
            deleteAllPerubahanPSM($pdo, $psm_id);
        }
        
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Add new function to handle PSM deletion
function handlePSMDelete($pdo, $data) {
    try {
        $pdo->beginTransaction();
        
        $psm_id = $data['psm_id'];
        $on_id = $data['on_id'] ?? null;
        $username = $data['username'];

        // Jika ada on_id, lakukan soft delete
        if ($on_id) {
            // Cek apakah sudah ada record di perubahan_psm
            $stmt = $pdo->prepare("SELECT perubahan_id, newval FROM perubahan_psm WHERE psm_id = ? AND on_id = ? AND kolom = 'nama' ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$psm_id, $on_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Update existing record
                $stmt = $pdo->prepare("UPDATE perubahan_psm SET oldval = ?, newval = NULL, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$row['newval'], $row['perubahan_id']]);
            } else {
                // Jika belum ada record, insert baru
                $stmt = $pdo->prepare("INSERT INTO perubahan_psm (psm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, 'nama', NULL, NULL, ?, NOW())");
                $stmt->execute([$psm_id, $on_id, $username]);
            }
        } else {
            // Hard delete
            deleteAllPerubahanPSM($pdo, $psm_id);
            $stmt = $pdo->prepare("DELETE FROM psm WHERE psm_id = ?");
            $stmt->execute([$psm_id]);
        }
        
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Fungsi untuk mengecek apakah nama sudah ada di tabel psm
function checkExistingPSMName($pdo, $nama, $standar_id) {
    $stmt = $pdo->prepare("SELECT psm_id FROM psm WHERE nama = ? AND standar_id = ?");
    $stmt->execute([$nama, $standar_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan informasi on_id
function getOnInfo($pdo, $on_id) {
    $stmt = $pdo->prepare("
        SELECT o.on_id, s.nama as standar_nama, la.nama as la_nama, 
               t.nama as tahun_nama, p.nama as prodi_nama
        FROM objeknilai o
        JOIN standar s ON o.standar_id = s.standar_id
        JOIN lat l ON o.lat_id = l.lat_id
        JOIN la la ON l.la_id = la.la_id
        JOIN tahun t ON l.tahun_id = t.tahun_id
        JOIN prodi p ON o.prodi_id = p.role_id
        WHERE o.on_id = ?
    ");
    $stmt->execute([$on_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan semua on_id
function getAllOnIds($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT on_id FROM objeknilai");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fungsi untuk mengecek perubahan_psm
function checkExistingPSMChange($pdo, $psm_id, $on_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM perubahan_psm 
        WHERE psm_id = ? AND on_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$psm_id, $on_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Modifikasi fungsi handlePSMAdd
function handlePSMAdd($pdo, $data) {
    try {
        $pdo->beginTransaction();
        $on_id = $data['on_id'] ?? null;
        $standar_id = $data['standar_id'];
        $nama = $data['nama'];
        $psm_id = $data['psm_id'] ?? null;
        $keterangan = $data['keterangan'] ?? null;
        $data_dukung = $data['data_dukung'] ?? null;
        $bobot_nilai = $data['bobot_nilai'] ?? null;
        $username = $data['username'];
        // Jika psm_id & on_id, update/insert perubahan_psm (soft add)
        if ($psm_id && $on_id) {
            $kolom = 'nama';
            $stmt = $pdo->prepare("SELECT perubahan_id FROM perubahan_psm WHERE psm_id = ? AND on_id = ? AND kolom = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$psm_id, $on_id, $kolom]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stmt = $pdo->prepare("SELECT newval FROM perubahan_psm WHERE perubahan_id = ?");
                $stmt->execute([$row['perubahan_id']]);
                $prev_newval = $stmt->fetchColumn();
                $stmt = $pdo->prepare("UPDATE perubahan_psm SET oldval = ?, newval = ?, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$prev_newval, $nama, $row['perubahan_id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO perubahan_psm (psm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, ?, NULL, ?, ?, NOW())");
                $stmt->execute([$psm_id, $on_id, $kolom, $nama, $username]);
            }
            $pdo->commit();
            return ['success' => true, 'psm_id' => $psm_id];
        }
        // === JIKA psm_id TIDAK ADA, CEK NAMA SEPERTI BIASA ===
        $existingPSM = checkExistingPSMName($pdo, $nama, $standar_id);

        if ($existingPSM) {
            // Jika FILTER ON, logika sudah ada
            if ($on_id) {
                // ... existing code ...
            } else {
                // FILTER OFF: beri warning
                $pdo->commit();
                return [
                    'success' => false,
                    'exists' => true,
                    'message' => 'PSM dengan nama tersebut sudah ada di bawah standar ini!'
                ];
            }
        }

        // Jika nama belum ada, buat PSM baru
        if (!$existingPSM) {
            $stmt = $pdo->prepare("INSERT INTO psm 
                                 (standar_id, nama, keterangan, data_dukung, bobot_nilai)
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$standar_id, $nama, $keterangan, $data_dukung, $bobot_nilai]);
            $new_psm_id = $pdo->lastInsertId();

            // Jika ada on_id, tambahkan perubahan untuk semua on_id
            if ($on_id) {
                $all_on_ids = getAllOnIdsByStandar($pdo, $standar_id);
                foreach ($all_on_ids as $oid) {
                    $newval = ($oid == $on_id) ? $nama : null;
                    $oldval = ($oid == $on_id) ? null : $nama;
                    $stmt = $pdo->prepare("INSERT INTO perubahan_psm 
                                         (psm_id, on_id, kolom, oldval, newval, username, updated_at)
                                         VALUES (?, ?, 'nama', ?, ?, ?, NOW())");
                    $stmt->execute([$new_psm_id, $oid, $oldval, $newval, $username]);
                }
            }

            $pdo->commit();
            return ['success' => true, 'psm_id' => $new_psm_id];
        }

        $pdo->commit();
        return ['success' => true];

    } catch (Exception $e) {
        error_log('PSM ADD ERROR: ' . $e->getMessage());
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function handleStandarChange($pdo, $data) {
    if (empty($data['kolom']) || !array_key_exists('newval', $data)) {
        return ['success' => false, 'error' => 'Kolom dan nilai baru harus diisi'];
    }
    $on_id = $data['on_id'] ?? null;
    $kolom = $data['kolom'];
    if ($on_id && $kolom === 'nama') {
        return ['success' => false, 'error' => 'Nama tidak dapat diubah pada mode filter, silakan ubah di template.'];
    }
    try {
        $pdo->beginTransaction();
        $standar_id = $data['standar_id'];
        $newval = $data['newval'];
        $username = $data['username'];
        if ($on_id) {
            $stmt = $pdo->prepare("SELECT perubahan_id FROM perubahan_standar WHERE standar_id = ? AND on_id = ? AND kolom = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$standar_id, $on_id, $kolom]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE perubahan_standar SET newval = ?, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$newval, $existing['perubahan_id']]);
            } else {
                $stmt = $pdo->prepare("SELECT $kolom FROM standar WHERE standar_id = ?");
                $stmt->execute([$standar_id]);
                $oldval = $stmt->fetchColumn();
                $stmt = $pdo->prepare("INSERT INTO perubahan_standar (standar_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$standar_id, $on_id, $kolom, $oldval, $newval, $username]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE standar SET $kolom = ? WHERE standar_id = ?");
            $stmt->execute([$newval, $standar_id]);
            // Hapus semua perubahan_standar terkait standar_id ini
            deleteAllPerubahanStandar($pdo, $standar_id);
        }
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function handleSSMChange($pdo, $data) {
    if (empty($data['kolom']) || !array_key_exists('newval', $data)) {
        return ['success' => false, 'error' => 'Kolom dan nilai baru harus diisi'];
    }
    $on_id = $data['on_id'] ?? null;
    $kolom = $data['kolom'];
    if ($on_id && $kolom === 'nama') {
        return ['success' => false, 'error' => 'Nama tidak dapat diubah pada mode filter, silakan ubah di template.'];
    }
    try {
        $pdo->beginTransaction();
        $ssm_id = $data['ssm_id'];
        $newval = $data['newval'];
        $username = $data['username'];
        if ($on_id) {
            $stmt = $pdo->prepare("SELECT perubahan_id FROM perubahan_ssm WHERE ssm_id = ? AND on_id = ? AND kolom = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$ssm_id, $on_id, $kolom]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE perubahan_ssm SET newval = ?, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$newval, $existing['perubahan_id']]);
            } else {
                $stmt = $pdo->prepare("SELECT $kolom FROM ssm WHERE ssm_id = ?");
                $stmt->execute([$ssm_id]);
                $oldval = $stmt->fetchColumn();
                $stmt = $pdo->prepare("INSERT INTO perubahan_ssm (ssm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$ssm_id, $on_id, $kolom, $oldval, $newval, $username]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE ssm SET $kolom = ? WHERE ssm_id = ?");
            $stmt->execute([$newval, $ssm_id]);
            // Hapus semua perubahan_ssm terkait ssm_id ini
            deleteAllPerubahanSSM($pdo, $ssm_id);
        }
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function handleSubSSMChange($pdo, $data) {
    if (empty($data['kolom']) || !array_key_exists('newval', $data)) {
        return ['success' => false, 'error' => 'Kolom dan nilai baru harus diisi'];
    }
    $on_id = $data['on_id'] ?? null;
    $kolom = $data['kolom'];
    if ($on_id && $kolom === 'nama') {
        return ['success' => false, 'error' => 'Nama tidak dapat diubah pada mode filter, silakan ubah di template.'];
    }
    try {
        $pdo->beginTransaction();
        $subssm_id = $data['subssm_id'];
        $newval = $data['newval'];
        $username = $data['username'];
        if ($on_id) {
            $stmt = $pdo->prepare("SELECT perubahan_id FROM perubahan_subssm WHERE subssm_id = ? AND on_id = ? AND kolom = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$subssm_id, $on_id, $kolom]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE perubahan_subssm SET newval = ?, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$newval, $existing['perubahan_id']]);
            } else {
                $stmt = $pdo->prepare("SELECT $kolom FROM subssm WHERE subssm_id = ?");
                $stmt->execute([$subssm_id]);
                $oldval = $stmt->fetchColumn();
                $stmt = $pdo->prepare("INSERT INTO perubahan_subssm (subssm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$subssm_id, $on_id, $kolom, $oldval, $newval, $username]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE subssm SET $kolom = ? WHERE subssm_id = ?");
            $stmt->execute([$newval, $subssm_id]);
            // Hapus semua perubahan_subssm terkait subssm_id ini
            deleteAllPerubahanSubSSM($pdo, $subssm_id);
        }
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Tambahkan fungsi handleSSMDelete dan handleSubSSMDelete
function handleSSMDelete($pdo, $data) {
    try {
        $pdo->beginTransaction();
        
        $ssm_id = $data['ssm_id'];
        $on_id = $data['on_id'] ?? null;
        $username = $data['username'];

        // Jika ada on_id, lakukan soft delete
        if ($on_id) {
            // Cek apakah sudah ada record di perubahan_ssm
            $stmt = $pdo->prepare("SELECT perubahan_id, newval FROM perubahan_ssm WHERE ssm_id = ? AND on_id = ? AND kolom = 'nama' ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$ssm_id, $on_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Update existing record
                $stmt = $pdo->prepare("UPDATE perubahan_ssm SET oldval = ?, newval = NULL, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$row['newval'], $row['perubahan_id']]);
            } else {
                // Jika belum ada record, insert baru
                $stmt = $pdo->prepare("INSERT INTO perubahan_ssm (ssm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, 'nama', NULL, NULL, ?, NOW())");
                $stmt->execute([$ssm_id, $on_id, $username]);
            }
        } else {
            // Hard delete
            deleteAllPerubahanSSM($pdo, $ssm_id);
            $stmt = $pdo->prepare("DELETE FROM ssm WHERE ssm_id = ?");
            $stmt->execute([$ssm_id]);
        }
        
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function handleSubSSMDelete($pdo, $data) {
    try {
        $pdo->beginTransaction();
        
        $subssm_id = $data['subssm_id'];
        $on_id = $data['on_id'] ?? null;
        $username = $data['username'];

        // Jika ada on_id, lakukan soft delete
        if ($on_id) {
            // Cek apakah sudah ada record di perubahan_subssm
            $stmt = $pdo->prepare("SELECT perubahan_id, newval FROM perubahan_subssm WHERE subssm_id = ? AND on_id = ? AND kolom = 'nama' ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$subssm_id, $on_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Update existing record
                $stmt = $pdo->prepare("UPDATE perubahan_subssm SET oldval = ?, newval = NULL, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$row['newval'], $row['perubahan_id']]);
            } else {
                // Jika belum ada record, insert baru
                $stmt = $pdo->prepare("INSERT INTO perubahan_subssm (subssm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, 'nama', NULL, NULL, ?, NOW())");
                $stmt->execute([$subssm_id, $on_id, $username]);
            }
        } else {
            // Hard delete
            deleteAllPerubahanSubSSM($pdo, $subssm_id);
            $stmt = $pdo->prepare("DELETE FROM subssm WHERE subssm_id = ?");
            $stmt->execute([$subssm_id]);
        }
        
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Fungsi untuk mengecek apakah nama sudah ada di tabel ssm
function checkExistingSSMName($pdo, $nama) {
    $stmt = $pdo->prepare("SELECT ssm_id FROM ssm WHERE nama = ?");
    $stmt->execute([$nama]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengecek perubahan_ssm
function checkExistingSSMChange($pdo, $ssm_id, $on_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM perubahan_ssm 
        WHERE ssm_id = ? AND on_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$ssm_id, $on_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengecek apakah nama sudah ada di tabel subssm
function checkExistingSubSSMName($pdo, $nama) {
    $stmt = $pdo->prepare("SELECT subssm_id FROM subssm WHERE nama = ?");
    $stmt->execute([$nama]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengecek perubahan_subssm
function checkExistingSubSSMChange($pdo, $subssm_id, $on_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM perubahan_subssm 
        WHERE subssm_id = ? AND on_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$subssm_id, $on_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Logic ADD SSM mirip PSM
function handleSSMAdd($pdo, $data) {
    try {
        $pdo->beginTransaction();
        $on_id = $data['on_id'] ?? null;
        $psm_id = $data['parent_id'] ?? $data['psm_id'] ?? null;
        $nama = $data['nama'];
        $ssm_id = $data['ssm_id'] ?? null;
        $keterangan = $data['keterangan'] ?? null;
        $data_dukung = $data['data_dukung'] ?? null;
        $bobot_nilai = $data['bobot_nilai'] ?? null;
        $username = $data['username'];
        if ($ssm_id && $on_id) {
            $kolom = 'nama';
            $stmt = $pdo->prepare("SELECT perubahan_id FROM perubahan_ssm WHERE ssm_id = ? AND on_id = ? AND kolom = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$ssm_id, $on_id, $kolom]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stmt = $pdo->prepare("SELECT newval FROM perubahan_ssm WHERE perubahan_id = ?");
                $stmt->execute([$row['perubahan_id']]);
                $prev_newval = $stmt->fetchColumn();
                $stmt = $pdo->prepare("UPDATE perubahan_ssm SET oldval = ?, newval = ?, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$prev_newval, $nama, $row['perubahan_id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO perubahan_ssm (ssm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, ?, NULL, ?, ?, NOW())");
                $stmt->execute([$ssm_id, $on_id, $kolom, $nama, $username]);
            }
            $pdo->commit();
            return ['success' => true, 'ssm_id' => $ssm_id];
        }
        $existingSSM = checkExistingSSMName($pdo, $nama);
        if ($existingSSM) {
            if ($on_id) {
                $existingChange = checkExistingSSMChange($pdo, $existingSSM['ssm_id'], $on_id);
                if ($existingChange && $existingChange['newval'] === null) {
                    $onInfo = getOnInfo($pdo, $on_id);
                    return [
                        'success' => false,
                        'exists' => true,
                        'message' => "SSM dengan nama tersebut sudah tersedia! Apakah anda ingin menampilkan SSM tersebut juga di standar {$onInfo['standar_nama']} lembaga akreditasi {$onInfo['la_nama']} tahun akreditasi {$onInfo['tahun_nama']} untuk prodi {$onInfo['prodi_nama']}?",
                        'ssm_id' => $existingSSM['ssm_id']
                    ];
                }
            }
        }
        if (!$existingSSM) {
            $stmt = $pdo->prepare("INSERT INTO ssm (psm_id, nama, keterangan, data_dukung, bobot_nilai) VALUES ( ?, ?, ?, ?, ?)");
            $stmt->execute([$psm_id, $nama, $keterangan, $data_dukung, $bobot_nilai]);
            $new_ssm_id = $pdo->lastInsertId();
            if ($on_id) {
                // Ambil semua on_id yang punya psm_id ini (berdasarkan standar_id parent)
                $stmt = $pdo->prepare("SELECT DISTINCT on_id FROM objeknilai WHERE standar_id = (SELECT standar_id FROM psm WHERE psm_id = ?)");
                $stmt->execute([$psm_id]);
                $all_on_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($all_on_ids as $oid) {
                    $newval = ($oid == $on_id) ? $nama : null;
                    $oldval = ($oid == $on_id) ? null : $nama;
                    $stmt2 = $pdo->prepare("INSERT INTO perubahan_ssm (ssm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, 'nama', ?, ?, ?, NOW())");
                    $stmt2->execute([$new_ssm_id, $oid, $oldval, $newval, $username]);
                }
            }
            $pdo->commit();
            return ['success' => true, 'ssm_id' => $new_ssm_id];
        }
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        error_log('SSM ADD ERROR: ' . $e->getMessage());
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Logic ADD SubSSM mirip SSM/PSM
function handleSubSSMAdd($pdo, $data) {
    try {
        $pdo->beginTransaction();
        $on_id = $data['on_id'] ?? null;
        $ssm_id = $data['parent_id'] ?? $data['ssm_id'] ?? null;
        $nama = $data['nama'];
        $subssm_id = $data['subssm_id'] ?? null;
        $indikatorArr = $jsonData['indikatorArr'] ?? [];
        foreach ($indikatorArr as $indikator) {
            $stmt = $pdo->prepare("INSERT INTO indikator (subssm_id, nm_id, jenis, keterangan, on_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $subssm_id,
                $indikator['nm_id'],
                $indikator['jenis'],
                $indikator['keterangan'],
                $indikator['on_id']
            ]);
        }
        $keterangan = $data['keterangan'] ?? null;
        $data_dukung = $data['data_dukung'] ?? null;
        $bobot_nilai = $data['bobot_nilai'] ?? null;
        $username = $data['username'];
        if ($subssm_id && $on_id) {
            $kolom = 'nama';
            $stmt = $pdo->prepare("SELECT perubahan_id FROM perubahan_subssm WHERE subssm_id = ? AND on_id = ? AND kolom = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$subssm_id, $on_id, $kolom]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stmt = $pdo->prepare("SELECT newval FROM perubahan_subssm WHERE perubahan_id = ?");
                $stmt->execute([$row['perubahan_id']]);
                $prev_newval = $stmt->fetchColumn();
                $stmt = $pdo->prepare("UPDATE perubahan_subssm SET oldval = ?, newval = ?, updated_at = NOW() WHERE perubahan_id = ?");
                $stmt->execute([$prev_newval, $nama, $row['perubahan_id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO perubahan_subssm (subssm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, ?, NULL, ?, ?, NOW())");
                $stmt->execute([$subssm_id, $on_id, $kolom, $nama, $username]);
            }
            $pdo->commit();
            return ['success' => true, 'subssm_id' => $subssm_id];
        }
        $existingSubSSM = checkExistingSubSSMName($pdo, $nama);
        if ($existingSubSSM) {
            if ($on_id) {
                $existingChange = checkExistingSubSSMChange($pdo, $existingSubSSM['subssm_id'], $on_id);
                if ($existingChange && $existingChange['newval'] === null) {
                    $onInfo = getOnInfo($pdo, $on_id);
                    return [
                        'success' => false,
                        'exists' => true,
                        'message' => "SubSSM dengan nama tersebut sudah tersedia! Apakah anda ingin menampilkan SubSSM tersebut juga di standar {$onInfo['standar_nama']} lembaga akreditasi {$onInfo['la_nama']} tahun akreditasi {$onInfo['tahun_nama']} untuk prodi {$onInfo['prodi_nama']}?",
                        'subssm_id' => $existingSubSSM['subssm_id']
                    ];
                }
            }
        }
        if (!$existingSubSSM) {
            $stmt = $pdo->prepare("INSERT INTO subssm (ssm_id, nama, keterangan, data_dukung, bobot_nilai) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$ssm_id, $nama, $keterangan, $data_dukung, $bobot_nilai]);
            $new_subssm_id = $pdo->lastInsertId();
            if ($on_id) {
                // Ambil semua on_id yang punya ssm_id ini (berdasarkan standar_id parent)
                $stmt = $pdo->prepare("SELECT DISTINCT on_id FROM objeknilai WHERE standar_id = (SELECT standar_id FROM psm WHERE psm_id = (SELECT psm_id FROM ssm WHERE ssm_id = ?))");
                $stmt->execute([$ssm_id]);
                $all_on_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($all_on_ids as $oid) {
                    $newval = ($oid == $on_id) ? $nama : null;
                    $oldval = ($oid == $on_id) ? null : $nama;
                    $stmt2 = $pdo->prepare("INSERT INTO perubahan_subssm (subssm_id, on_id, kolom, oldval, newval, username, updated_at) VALUES (?, ?, 'nama', ?, ?, ?, NOW())");
                    $stmt2->execute([$new_subssm_id, $oid, $oldval, $newval, $username]);
                }
            }
            $pdo->commit();
            return ['success' => true, 'subssm_id' => $new_subssm_id];
        }
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        error_log('SUBSSM ADD ERROR: ' . $e->getMessage());
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

// Ambil data JSON untuk POST/PUT requests
$jsonData = null;
if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
    $jsonData = json_decode(file_get_contents('php://input'), true);
}

switch ($action) {
    case 'get_kegiatan':
        echo json_encode(getKegiatan($pdo));
        break;

    case 'get_standar':
        echo json_encode(getStandarMutu($pdo));
        break;

    case 'get_indikator':
        echo json_encode(getIndikator($pdo));
        break;

    case 'get_tahun':
        echo json_encode(getTahun($pdo));
        break;
        
    case 'get_la':
        echo json_encode(getLA($pdo));
        break;
        
    case 'get_prodi':
        echo json_encode(getProdi($pdo));
        break;

    case 'get_on_id':
        $tahun_id = $_GET['tahun_id'] ?? null;
        $la_id = $_GET['la_id'] ?? null;
        $prodi_id = $_GET['prodi_id'] ?? null;
        
        if ($tahun_id && $la_id && $prodi_id) {
            $stmt = $pdo->prepare("SELECT on_id, lat_id FROM objeknilai 
                                 WHERE lat_id IN (SELECT lat_id FROM lat WHERE la_id = ? AND tahun_id = ?)
                                 AND prodi_id = ? LIMIT 1");
            $stmt->execute([$la_id, $tahun_id, $prodi_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                echo json_encode(['on_id' => $row['on_id'], 'lat_id' => $row['lat_id']]);
            } else {
                echo json_encode(['on_id' => null, 'lat_id' => null]);
            }
        } else {
            echo json_encode(['on_id' => null, 'lat_id' => null]);
        }
        break;

    case 'get_hirarki':
        $tahun_id = $_GET['tahun_id'] ?? null;
        $la_id = $_GET['la_id'] ?? null;
        $prodi_id = $_GET['prodi_id'] ?? null;
        echo json_encode(getStandarHirarki($pdo, $tahun_id, $la_id, $prodi_id));
        break;

    // Add Standar
    case 'add_standar':
        if ($method !== 'POST') break;
        try {
            $stmt = $pdo->prepare("INSERT INTO standar (nama, keterangan, data_dukung, bobot_nilai) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $jsonData['nama'], 
                $jsonData['keterangan'],
                $jsonData['data_dukung'],
                $jsonData['bobot_nilai']
            ]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    // Add PSM
    case 'add_psm':
        if ($method !== 'POST') break;
        echo json_encode(handlePSMAdd($pdo, $jsonData));
        break;

    // Add SSM
    case 'add_ssm':
        if ($method !== 'POST') break;
        echo json_encode(handleSSMAdd($pdo, $jsonData));
        break;

    // Add SubSSM
    case 'add_subssm':
        if ($method !== 'POST') break;
        echo json_encode(handleSubSSMAdd($pdo, $jsonData));
        break;

    // Update Standar
    case 'update_standar':
        if ($method !== 'PUT') break;
        $standar_id = $jsonData['standar_id'];
        $on_id = $jsonData['on_id'] ?? null;
        $username = $jsonData['username'];
        $changes = $jsonData['changes'] ?? [];
        $results = [];
        foreach ($changes as $change) {
            $result = handleStandarChange($pdo, [
                'standar_id' => $standar_id,
                'on_id' => $on_id,
                'kolom' => $change['kolom'],
                'newval' => $change['newval'],
                'username' => $username
            ]);
            $results[] = $result;
        }
        echo json_encode(['success' => true, 'results' => $results]);
        break;

    // Update PSM
    case 'update_psm':
        if ($method !== 'PUT') break;
        $psm_id = $jsonData['psm_id'];
        $on_id = $jsonData['on_id'] ?? null;
        $username = $jsonData['username'];
        $changes = $jsonData['changes'] ?? [];

        $results = [];
        foreach ($changes as $change) {
            $result = handlePSMChange($pdo, [
                'psm_id' => $psm_id,
                'on_id' => $on_id,
                'kolom' => $change['kolom'],
                'newval' => $change['newval'],
                'username' => $username
            ]);
            $results[] = $result;
        }
        echo json_encode(['success' => true, 'results' => $results]);
        break;

    // Update SSM
    case 'update_ssm':
        if ($method !== 'PUT') break;
        $ssm_id = $jsonData['ssm_id'];
        $on_id = $jsonData['on_id'] ?? null;
        $username = $jsonData['username'];
        $changes = $jsonData['changes'] ?? [];
        $results = [];
        foreach ($changes as $change) {
            $result = handleSSMChange($pdo, [
                'ssm_id' => $ssm_id,
                'on_id' => $on_id,
                'kolom' => $change['kolom'],
                'newval' => $change['newval'],
                'username' => $username
            ]);
            $results[] = $result;
        }
        echo json_encode(['success' => true, 'results' => $results]);
        break;

    // Update SubSSM
    case 'update_subssm':
        if ($method !== 'PUT') break;
        $subssm_id = $jsonData['subssm_id'];
        $on_id = $jsonData['on_id'] ?? null;
        $pdo->prepare("DELETE FROM indikator WHERE subssm_id = ? AND on_id = ?")->execute([$subssm_id, $on_id]);
        $indikatorArr = $jsonData['indikatorArr'] ?? [];
        foreach ($indikatorArr as $indikator) {
            $stmt = $pdo->prepare("INSERT INTO indikator (subssm_id, nm_id, jenis, keterangan, on_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $subssm_id,
                $indikator['nm_id'],
                $indikator['jenis'],
                $indikator['keterangan'],
                $indikator['on_id']
            ]);
        }
        $username = $jsonData['username'];
        $changes = $jsonData['changes'] ?? [];
        $results = [];
        foreach ($changes as $change) {
            $result = handleSubSSMChange($pdo, [
                'subssm_id' => $subssm_id,
                'on_id' => $on_id,
                'kolom' => $change['kolom'],
                'newval' => $change['newval'],
                'username' => $username
            ]);
            $results[] = $result;
        }
        echo json_encode(['success' => true, 'results' => $results]);
        break;

    // Delete Standar
    case 'delete_standar':
        if ($method !== 'DELETE') break;
        // Jika FILTER ON (on_id dikirim), hapus objeknilai dan semua perubahan terkait on_id
        if (isset($jsonData['on_id']) && $jsonData['on_id']) {
            $on_id = $jsonData['on_id'];
            $standar_id = $jsonData['standar_id'];
            try {
                $pdo->beginTransaction();
                // Hapus objeknilai untuk on_id dan standar_id ini
                $stmt = $pdo->prepare("DELETE FROM objeknilai WHERE on_id = ? AND standar_id = ?");
                $stmt->execute([$on_id, $standar_id]);
                // Hapus semua perubahan terkait on_id dan standar_id
                $tables = [
                    ['perubahan_standar', 'standar_id'],
                    ['perubahan_psm', null],
                    ['perubahan_ssm', null],
                    ['perubahan_subssm', null],
                    ['dp', null],
                    ['edesk', null],
                    ['edi', null]
                ];
                foreach ($tables as $arr) {
                    $table = $arr[0];
                    $col = $arr[1];
                    if ($col) {
                        $stmt = $pdo->prepare("DELETE FROM $table WHERE on_id = ? AND $col = ?");
                        $stmt->execute([$on_id, $standar_id]);
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM $table WHERE on_id = ?");
                        $stmt->execute([$on_id]);
                    }
                }
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        }
        // FILTER OFF: hard delete standar & bawahannya
        try {
            $id = $jsonData['standar_id'];
            $pdo->beginTransaction();
            // ... existing code ...
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    // Delete PSM
    case 'delete_psm':
        if ($method !== 'DELETE') break;
        echo json_encode(handlePSMDelete($pdo, $jsonData));
        break;

    // Delete SSM
    case 'delete_ssm':
        if ($method !== 'DELETE') break;
        echo json_encode(handleSSMDelete($pdo, $jsonData));
        break;

    // Delete SubSSM
    case 'delete_subssm':
        if ($method !== 'DELETE') break;
        echo json_encode(handleSubSSMDelete($pdo, $jsonData));
        break;

    case 'generate_standar':
        if ($method !== 'POST') break;
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $selectedLA = is_array($data['selectedLA']) ? $data['selectedLA'] : [$data['selectedLA']];
            $selectedTahun = is_array($data['selectedTahun']) ? $data['selectedTahun'] : [$data['selectedTahun']];
            $selectedProdi = is_array($data['selectedProdi']) ? $data['selectedProdi'] : [$data['selectedProdi']];
            $selectedStandar = is_array($data['selectedStandar']) ? $data['selectedStandar'] : (isset($data['selectedStandar']) ? [$data['selectedStandar']] : []);

            $response = ['success' => true, 'message' => '', 'status' => 'success'];
            $successCount = 0;
            $warningMessages = [];

            foreach ($selectedTahun as $tahun_id) {
                foreach ($selectedLA as $la_id) {
                    // cari lat_id untuk tahun & la ini
                    $stmt = $pdo->prepare("SELECT lat_id FROM lat WHERE la_id = ? AND tahun_id = ?");
                    $stmt->execute([$la_id, $tahun_id]);
                    $currentLat = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$currentLat) continue;
                    $current_lat_id = $currentLat['lat_id'];

                    foreach ($selectedProdi as $prodi_id) {
                        // Ambil nama LA, Tahun, Prodi untuk pesan warning
                        $stmt = $pdo->prepare("SELECT nama FROM la WHERE la_id = ?");
                        $stmt->execute([$la_id]);
                        $la_nama = $stmt->fetchColumn();
                        $stmt = $pdo->prepare("SELECT nama FROM tahun WHERE tahun_id = ?");
                        $stmt->execute([$tahun_id]);
                        $tahun_nama = $stmt->fetchColumn();
                        $stmt = $pdo->prepare("SELECT nama FROM prodi WHERE role_id = ?");
                        $stmt->execute([$prodi_id]);
                        $prodi_nama = $stmt->fetchColumn();
                        // CEK: Apakah sudah ada data objeknilai untuk kombinasi ini?
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM objeknilai WHERE lat_id = ? AND prodi_id = ?");
                        $stmt->execute([$current_lat_id, $prodi_id]);
                        $existsAny = $stmt->fetchColumn();
                        if ($existsAny > 0) {
                            $warningMessages[] = "Sudah ada data standar mutu untuk Lembaga Akreditasi '$la_nama', Tahun '$tahun_nama', Prodi '$prodi_nama'. Tidak dapat generate ulang kombinasi ini.";
                            continue;
                        }
                        foreach ($selectedStandar as $standar_id) {
                            // cek apakah sudah ada di objeknilai (ini redundant, tapi biar aman)
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM objeknilai WHERE lat_id = ? AND prodi_id = ? AND standar_id = ?");
                            $stmt->execute([$current_lat_id, $prodi_id, $standar_id]);
                            $exists = $stmt->fetchColumn();
                            if (!$exists) {
                                // insert baru
                                $stmt = $pdo->prepare("INSERT INTO objeknilai (lat_id, prodi_id, standar_id) VALUES (?, ?, ?)");
                                $stmt->execute([$current_lat_id, $prodi_id, $standar_id]);
                                $successCount++;
                            }
                        }
                    }
                }
            }

            if (count($warningMessages) > 0) {
                $response['status'] = 'warning';
                $response['message'] = implode("\n", $warningMessages);
                if ($successCount > 0) {
                    $response['message'] .= "\n\nBerhasil generate " . $successCount . " data objeknilai.";
                }
            } else if ($successCount == 0) {
                $response['status'] = 'info';
                $response['message'] = "Tidak ada data baru yang perlu di-generate.";
            } else {
                $response['message'] = "Berhasil generate " . $successCount . " data objeknilai.";
            }

            echo json_encode($response);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        break;

    case 'get_prev_standar':
        $la_id = $_GET['la_id'] ?? null;
        $tahun_id = $_GET['tahun_id'] ?? null;
        $prodi_id = $_GET['prodi_id'] ?? null;
        $result = [];
        if ($la_id && $tahun_id && $prodi_id) {
            // Cari tahun sebelumnya
            $stmt = $pdo->prepare("SELECT nama FROM tahun WHERE tahun_id = ?");
            $stmt->execute([$tahun_id]);
            $tahunNama = $stmt->fetchColumn();
            $prevTahunNum = intval($tahunNama) - 1;
            $stmt = $pdo->prepare("SELECT tahun_id FROM tahun WHERE nama = ?");
            $stmt->execute([$prevTahunNum]);
            $prevTahunId = $stmt->fetchColumn();
            if ($prevTahunId) {
                // Cari lat_id tahun sebelumnya
                $stmt = $pdo->prepare("SELECT lat_id FROM lat WHERE la_id = ? AND tahun_id = ?");
                $stmt->execute([$la_id, $prevTahunId]);
                $prevLatId = $stmt->fetchColumn();
                if ($prevLatId) {
                    // Ambil standar_id dari objeknilai tahun sebelumnya
                    $stmt = $pdo->prepare("SELECT DISTINCT standar_id FROM objeknilai WHERE lat_id = ? AND prodi_id = ?");
                    $stmt->execute([$prevLatId, $prodi_id]);
                    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
                }
            }
        }
        echo json_encode($result);
        break;

    case 'get_nilaimutu_by_subssm':
        $subssm_id = $_GET['subssm_id'] ?? null;
        $on_id = $_GET['on_id'] ?? null;
        
        if ($on_id) {
            // 1. Ambil lat_id dari on_id
            $stmt = $pdo->prepare("SELECT lat_id FROM objeknilai WHERE on_id = ?");
            $stmt->execute([$on_id]);
            $lat_id = $stmt->fetchColumn();

            if ($lat_id) {
                // 2. Ambil semua nilai mutu untuk lat_id ini
                $stmt = $pdo->prepare("SELECT nm_id, nilai FROM nilaimutu WHERE lat_id = ?");
                $stmt->execute([$lat_id]);
                $nmList = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 3. Jika ada subssm_id (edit mode), ambil indikator yang sudah ada
                $indikatorList = [];
                if ($subssm_id) {
                    $stmt = $pdo->prepare("SELECT * FROM indikator WHERE subssm_id = ? AND on_id = ?");
                    $stmt->execute([$subssm_id, $on_id]);
                    $indikatorList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                echo json_encode([
                    'nilaimutu' => $nmList,
                    'indikator' => $indikatorList
                ]);
                exit;
            }
        }
        echo json_encode(['nilaimutu' => [], 'indikator' => []]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}

function getAllOnIdsByStandar($pdo, $standar_id) {
    $stmt = $pdo->prepare("SELECT DISTINCT on_id FROM objeknilai WHERE standar_id = ?");
    $stmt->execute([$standar_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>