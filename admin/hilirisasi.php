<?php
session_start();
require_once __DIR__ . '/../config.php';

// Function to safely render HTML content
if (!function_exists('renderSafeHtml')) {
    function renderSafeHtml($html) {
        if (empty($html)) {
            return '';
        }
        
        // Allowed tags for formatting (safe tags only)
        $allowedTags = '<p><br><b><strong><i><em><u><s><strike><span><div><ul><ol><li><h1><h2><h3><h4><h5><h6>';
        
        // Remove dangerous tags and attributes
        $html = strip_tags($html, $allowedTags);
        
        // Remove dangerous attributes but keep style for formatting
        $html = preg_replace_callback('/<([^>]+)>/i', function($matches) {
            $tag = $matches[1];
            // Remove dangerous attributes
            $tag = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $tag);
            $tag = preg_replace('/\s*javascript\s*:/i', '', $tag);
            // Keep style attribute for formatting
            return '<' . $tag . '>';
        }, $html);
        
        return $html;
    }
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}

$activePage = 'hilirisasi';
$uploadDir = __DIR__ . '/../uploads/hilirisasi/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

function uploadHilirisasiImage(string $fieldName, string $targetDir): ?string {
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $fileType = $_FILES[$fieldName]['type'] ?? '';

    if (!in_array($fileType, $allowedTypes, true)) {
        throw new RuntimeException('Foto harus berupa gambar (JPG/PNG/WebP/GIF).');
    }

    $extension = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        throw new RuntimeException('Ekstensi file tidak diizinkan.');
    }

    $fileName = uniqid('hilirisasi_', true) . '.' . $extension;
    $targetPath = rtrim($targetDir, '/') . '/' . $fileName;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        throw new RuntimeException('Gagal mengunggah foto.');
    }

    return 'uploads/hilirisasi/' . $fileName;
}

function removeHilirisasiImage(?string $relativePath): void {
    if (!$relativePath) {
        return;
    }
    $safePath = realpath(__DIR__ . '/../' . $relativePath);
    $uploadsRoot = realpath(__DIR__ . '/../uploads');

    if ($safePath && $uploadsRoot && str_starts_with($safePath, $uploadsRoot) && file_exists($safePath)) {
        @unlink($safePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $id = (int) ($_POST['id'] ?? 0);

    try {
        if ($action === 'delete') {
            if ($id > 0) {
                $stmt = $mysqli->prepare('SELECT image_url FROM hilirisasi_items WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($row && $row['image_url']) {
                    removeHilirisasiImage($row['image_url']);
                }

                $stmt = $mysqli->prepare('DELETE FROM hilirisasi_items WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
            $_SESSION['flash_success'] = 'Hilirisasi berhasil dihapus.';
        } else {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $detailUrl = trim($_POST['detail_url'] ?? '');
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $existingImage = $_POST['existing_image'] ?? null;

            if ($title === '') {
                throw new RuntimeException('Judul hilirisasi wajib diisi.');
            }

            $imagePath = $existingImage;
            if (!empty($_FILES['image']['name'])) {
                $imagePath = uploadHilirisasiImage('image', $uploadDir);
                if ($existingImage && $imagePath !== $existingImage) {
                    removeHilirisasiImage($existingImage);
                }
            }

            if ($id > 0) {
                $stmt = $mysqli->prepare('UPDATE hilirisasi_items SET title = ?, description = ?, image_url = ?, detail_url = ?, sort_order = ? WHERE id = ?');
                $stmt->bind_param('ssssii', $title, $description, $imagePath, $detailUrl, $sortOrder, $id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Hilirisasi berhasil diperbarui.';
            } else {
                $adminUserId = $_SESSION['user_id'] ?? null;
                // Check if created_by column exists
                $checkColumn = $mysqli->query("SHOW COLUMNS FROM hilirisasi_items LIKE 'created_by'");
                $hasCreatedBy = $checkColumn && $checkColumn->num_rows > 0;
                if ($checkColumn) $checkColumn->free();
                
                if ($hasCreatedBy) {
                    $stmt = $mysqli->prepare('INSERT INTO hilirisasi_items (title, description, image_url, detail_url, sort_order, created_by) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->bind_param('ssssii', $title, $description, $imagePath, $detailUrl, $sortOrder, $adminUserId);
                } else {
                    $stmt = $mysqli->prepare('INSERT INTO hilirisasi_items (title, description, image_url, detail_url, sort_order) VALUES (?, ?, ?, ?, ?)');
                    $stmt->bind_param('ssssi', $title, $description, $imagePath, $detailUrl, $sortOrder);
                }
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Hilirisasi baru berhasil ditambahkan.';
            }
        }
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        $_SESSION['form_data'] = $_POST;
    }

    $redirect = '/crims/admin/hilirisasi.php';
    if ($action !== 'delete' && $id > 0 && empty($_SESSION['flash_error'])) {
        $redirect .= '?edit=' . $id;
    }
    header('Location: ' . $redirect);
    exit;
}

// Check if created_by column exists for query
$checkColumn = $mysqli->query("SHOW COLUMNS FROM hilirisasi_items LIKE 'created_by'");
$hasCreatedBy = $checkColumn && $checkColumn->num_rows > 0;
if ($checkColumn) $checkColumn->free();

if ($hasCreatedBy) {
    $result = $mysqli->query('SELECT h.*, u.role, u.full_name, u.username FROM hilirisasi_items h LEFT JOIN users u ON h.created_by = u.id ORDER BY h.sort_order ASC, h.created_at DESC');
} else {
    $result = $mysqli->query('SELECT h.*, NULL as role, NULL as full_name, NULL as username FROM hilirisasi_items h ORDER BY h.sort_order ASC, h.created_at DESC');
}

$hilirisasiItems = [];
if ($result) {
    $hilirisasiItems = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

$editingItem = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($hilirisasiItems as $item) {
        if ((int) $item['id'] === $editId) {
            $editingItem = $item;
            break;
        }
    }
}

$formData = $editingItem ?? ($_SESSION['form_data'] ?? null);
unset($_SESSION['form_data']);

$successMessage = $_SESSION['flash_success'] ?? null;
$errorMessage = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

require_once __DIR__ . '/admin_layout.php';

ob_start();
?>
<style>
    .wrapper{margin:0}
    .card{background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;box-shadow:0 1px 1px rgba(0,0,0,.04);margin-bottom:20px}
    .card h2{font-size:18px;font-weight:400;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #c3c4c7}
    form{display:flex;flex-direction:column;gap:16px}
    .form-row{display:flex;gap:16px;flex-wrap:wrap}
    .form-group{flex:1;min-width:220px;display:flex;flex-direction:column}
    label{font-weight:600;color:#1e5ba8;margin-bottom:8px}
    input, select, textarea{padding:12px;border:2px solid #dce4f3;border-radius:12px;font-size:15px;background:#f9fbff;transition:0.2s}
    textarea{min-height:100px;resize:vertical}
    input:focus, select:focus, textarea:focus{outline:none;border-color:#1e5ba8;box-shadow:0 0 0 3px rgba(30,91,168,0.15)}
    .note-editor.note-frame{border:2px solid #dce4f3;border-radius:12px;overflow:hidden;margin-top:8px}
    .note-editor.note-frame .note-toolbar{background:#f8f9fa;border-bottom:1px solid #e5eaf3;padding:8px}
    .note-editor.note-frame .note-editing-area .note-editable{min-height:250px;padding:15px;font-size:14px;line-height:1.6}
    .note-editor.note-frame .note-editing-area .note-editable:focus{outline:none}
    .btn{border:none;border-radius:12px;padding:12px 20px;font-weight:600;color:#fff;background:#1e5ba8;cursor:pointer;transition:0.2s;align-self:flex-start}
    .btn.secondary{background:#f1f5f9;color:#1e5ba8}
    .btn.danger{background:#d62828}
    .btn:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(30,91,168,0.2)}
    .alert{padding:14px 18px;border-radius:12px;margin-bottom:16px;font-size:14px}
    .alert-success{background:#e8f7ef;color:#1f7a4d;border:1px solid #a8e0c4}
    .alert-error{background:#fdecea;color:#b7182b;border:1px solid #f5b5b5}
    .hilirisasi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin-top:20px}
    .hilirisasi-preview-card{background:linear-gradient(135deg,#1e5ba8 0%,#2980b9 100%);border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(30,91,168,0.2);min-height:280px;display:flex;flex-direction:column}
    .hilirisasi-preview-card img{width:100%;height:180px;object-fit:cover}
    .hilirisasi-preview-card-content{padding:20px;color:#fff;flex:1;display:flex;flex-direction:column}
    .hilirisasi-preview-card h4{font-size:18px;margin-bottom:8px;font-weight:600;color:#fff}
    .hilirisasi-preview-card p{font-size:14px;opacity:0.9;line-height:1.5;flex:1}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th, td{text-align:left;padding:12px;border-bottom:1px solid #eef2fb}
    th{color:#6b7a90;font-weight:600;text-transform:uppercase;font-size:12px}
    .actions{display:flex;gap:8px}
    @media(max-width:768px){
        .form-row{flex-direction:column}
        .card{padding:18px}
        .hilirisasi-grid{grid-template-columns:1fr}
    }
</style>

<div class="admin-page-header">
    <h1>Kelola Hilirisasi</h1>
</div>

<div class="wrapper">
    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $editingItem ? 'Edit Hilirisasi' : 'Tambah Hilirisasi Baru' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $formData['id'] ?? 0 ?>">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($formData['image_url'] ?? '') ?>">
            
            <div class="form-group" style="flex: 2;">
                <label for="title">Judul Hilirisasi *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($formData['title'] ?? '') ?>" required placeholder="Contoh: Teknologi Robot Industri">
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" placeholder="Tulis deskripsi hilirisasi di sini..."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                <small style="color:#6b7a90;margin-top:4px;font-size:12px">Gunakan toolbar di atas untuk memformat teks (Bold, Italic, Underline, dll)</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="image">Gambar Hilirisasi</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if (!empty($formData['image_url'])): ?>
                        <div style="margin-top:8px">
                            <img src="/crims/<?= htmlspecialchars($formData['image_url']) ?>" alt="Preview" style="max-width:200px;border-radius:8px;margin-top:8px;border:2px solid #e5eaf3">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="detail_url">URL Detail (opsional)</label>
                    <input type="url" id="detail_url" name="detail_url" value="<?= htmlspecialchars($formData['detail_url'] ?? '') ?>" placeholder="https://example.com/detail">
                    <small style="color:#6b7a90;margin-top:4px;font-size:12px">Link ke halaman detail atau informasi lebih lanjut</small>
                </div>
            </div>

            <div class="form-group">
                <label for="sort_order">Urutan Tampil</label>
                <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? 0) ?>" min="0">
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn"><?= $editingItem ? 'Simpan Perubahan' : 'Tambah Hilirisasi' ?></button>
                <?php if ($editingItem): ?>
                    <a href="/crims/admin/hilirisasi.php" class="btn secondary">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Hilirisasi</h2>
        <?php if (empty($hilirisasiItems)): ?>
            <p style="color:#6b7a90;padding:20px 0">Belum ada hilirisasi. Tambahkan hilirisasi pertama Anda di atas.</p>
        <?php else: ?>
            <div class="hilirisasi-grid">
                <?php foreach ($hilirisasiItems as $item): ?>
                    <div class="hilirisasi-preview-card">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="/crims/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <?php endif; ?>
                        <div class="hilirisasi-preview-card-content">
                            <h4><?= htmlspecialchars($item['title']) ?></h4>
                            <?php if (!empty($item['description'])): ?>
                                <p><?= htmlspecialchars(strip_tags(substr($item['description'], 0, 100))) ?><?= strlen(strip_tags($item['description'])) > 100 ? '...' : '' ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <table style="margin-top:24px">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>URL Detail</th>
                        <th>Urutan</th>
                        <?php if ($hasCreatedBy): ?>
                            <th>Upload oleh</th>
                        <?php endif; ?>
                        <th>Tanggal Upload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hilirisasiItems as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="/crims/<?= htmlspecialchars($item['image_url']) ?>" alt="Preview" style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:2px solid #e5eaf3">
                                <?php else: ?>
                                    <span style="color:#6b7a90">-</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                            <td>
                                <?php if (!empty($item['detail_url'])): ?>
                                    <a href="<?= htmlspecialchars($item['detail_url']) ?>" target="_blank" style="color:#1e5ba8"><?= htmlspecialchars(substr($item['detail_url'], 0, 30)) ?>...</a>
                                <?php else: ?>
                                    <span style="color:#6b7a90">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['sort_order']) ?></td>
                            <?php if ($hasCreatedBy): ?>
                                <td><?= !empty($item['role']) ? ucfirst($item['role']) : 'Admin' ?></td>
                            <?php endif; ?>
                            <td><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                            <td>
                                <div class="actions">
                                    <a href="?edit=<?= $item['id'] ?>" class="btn secondary" style="padding:6px 12px;font-size:13px">Edit</a>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus hilirisasi ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn danger" style="padding:6px 12px;font-size:13px">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
// Ensure Summernote is loaded by including 'description' in page title check
echo renderAdminLayout($activePage, 'Kelola Hilirisasi', $content);
?>

