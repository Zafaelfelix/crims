<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}

$activePage = 'achievements';
$uploadDir = __DIR__ . '/../uploads/achievements/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

function uploadAchievementImage(string $fieldName, string $targetDir): ?string {
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

    $fileName = uniqid('achievement_', true) . '.' . $extension;
    $targetPath = rtrim($targetDir, '/') . '/' . $fileName;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        throw new RuntimeException('Gagal mengunggah foto.');
    }

    return 'uploads/achievements/' . $fileName;
}

function removeAchievementImage(?string $relativePath): void {
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
                $stmt = $mysqli->prepare('SELECT image_url FROM achievement_items WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($row && $row['image_url']) {
                    removeAchievementImage($row['image_url']);
                }

                $stmt = $mysqli->prepare('DELETE FROM achievement_items WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
            $_SESSION['flash_success'] = 'Prestasi berhasil dihapus.';
        } else {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $iconClass = trim($_POST['icon_class'] ?? 'fas fa-trophy');
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $existingImage = $_POST['existing_image'] ?? null;

            if ($title === '') {
                throw new RuntimeException('Judul prestasi wajib diisi.');
            }

            $imagePath = $existingImage;
            if (!empty($_FILES['image']['name'])) {
                $imagePath = uploadAchievementImage('image', $uploadDir);
                if ($existingImage && $imagePath !== $existingImage) {
                    removeAchievementImage($existingImage);
                }
            }

            if ($id > 0) {
                $stmt = $mysqli->prepare('UPDATE achievement_items SET title = ?, description = ?, icon_class = ?, image_url = ?, sort_order = ? WHERE id = ?');
                $stmt->bind_param('ssssii', $title, $description, $iconClass, $imagePath, $sortOrder, $id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Prestasi berhasil diperbarui.';
            } else {
                $adminUserId = $_SESSION['user_id'] ?? null;
                $stmt = $mysqli->prepare('INSERT INTO achievement_items (title, description, icon_class, image_url, sort_order, created_by) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssii', $title, $description, $iconClass, $imagePath, $sortOrder, $adminUserId);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Prestasi baru berhasil ditambahkan.';
            }
        }
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        $_SESSION['form_data'] = $_POST;
    }

    $redirect = '/crims/admin/achievements.php';
    if ($action !== 'delete' && $id > 0 && empty($_SESSION['flash_error'])) {
        $redirect .= '?edit=' . $id;
    }
    header('Location: ' . $redirect);
    exit;
}

$achievements = [];
$result = $mysqli->query('SELECT a.*, u.role, u.full_name, u.username FROM achievement_items a LEFT JOIN users u ON a.created_by = u.id ORDER BY a.sort_order ASC, a.created_at DESC');
if ($result) {
    $achievements = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

$editingAchievement = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($achievements as $achievement) {
        if ((int) $achievement['id'] === $editId) {
            $editingAchievement = $achievement;
            break;
        }
    }
}

$formData = $editingAchievement ?? ($_SESSION['form_data'] ?? null);
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
        .icon-preview{display:flex;align-items:center;gap:12px;padding:12px;background:#f8f9fa;border-radius:12px;margin-top:8px}
        .icon-preview i{font-size:32px;color:#1e5ba8}
        .note-editor.note-frame{border:2px solid #dce4f3;border-radius:12px;overflow:hidden;margin-top:8px}
        .note-editor.note-frame .note-toolbar{background:#f8f9fa;border-bottom:1px solid #e5eaf3;padding:8px}
        .note-editor.note-frame .note-editing-area .note-editable{min-height:250px;padding:15px;font-size:14px;line-height:1.6}
        .note-editor.note-frame .note-editing-area .note-editable:focus{outline:none}
        .btn{border:none;border-radius:12px;padding:12px 20px;font-weight:600;color:#fff;background:#1e5ba8;cursor:pointer;transition:0.2s;align-self:flex-start}
        
        /* Summernote CSS Import */
        @import url('https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css');
        .btn.secondary{background:#f1f5f9;color:#1e5ba8}
        .btn.danger{background:#d62828}
        .btn:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(30,91,168,0.2)}
        .alert{padding:14px 18px;border-radius:12px;margin-bottom:16px;font-size:14px}
        .alert-success{background:#e8f7ef;color:#1f7a4d;border:1px solid #a8e0c4}
        .alert-error{background:#fdecea;color:#b7182b;border:1px solid #f5b5b5}
        .achievements-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;margin-top:20px}
        .achievement-preview-card{background:linear-gradient(135deg,#1e5ba8 0%,#2980b9 100%);border-radius:16px;padding:24px;text-align:center;color:#fff;box-shadow:0 8px 24px rgba(30,91,168,0.2);min-height:200px;display:flex;flex-direction:column;justify-content:center}
        .achievement-preview-card i{font-size:48px;margin-bottom:16px;opacity:0.95}
        .achievement-preview-card h4{font-size:18px;margin-bottom:8px;font-weight:600}
        .achievement-preview-card p{font-size:14px;opacity:0.9;line-height:1.5}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th, td{text-align:left;padding:12px;border-bottom:1px solid #eef2fb}
        th{color:#6b7a90;font-weight:600;text-transform:uppercase;font-size:12px}
        .actions{display:flex;gap:8px}
        @media(max-width:768px){
            .form-row{flex-direction:column}
            .card{padding:18px}
            .achievements-grid{grid-template-columns:1fr}
        }
    </style>

    <div class="admin-page-header">
        <h1>Kelola Prestasi</h1>
    </div>
    
    <div class="wrapper">
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2><?= $editingAchievement ? 'Edit Prestasi' : 'Tambah Prestasi Baru' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $formData['id'] ?? 0 ?>">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($formData['image_url'] ?? '') ?>">
                
                <div class="form-group" style="flex: 2;">
                    <label for="title">Judul Prestasi *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($formData['title'] ?? '') ?>" required placeholder="Contoh: Juara 1 Inovasi Teknologi 2025">
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" placeholder="Tulis deskripsi prestasi di sini..."><?= $formData['description'] ?? '' ?></textarea>
                    <small style="color:#6b7a90;margin-top:4px;font-size:12px">Gunakan toolbar di atas untuk memformat teks (Bold, Italic, Underline, dll)</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="image">Foto Prestasi</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if (!empty($formData['image_url'])): ?>
                            <div style="margin-top:8px">
                                <img src="/crims/<?= htmlspecialchars($formData['image_url']) ?>" alt="Preview" style="max-width:200px;border-radius:8px;margin-top:8px;border:2px solid #e5eaf3">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="icon_class">Icon (Font Awesome Class)</label>
                        <input type="text" id="icon_class" name="icon_class" value="<?= htmlspecialchars($formData['icon_class'] ?? 'fas fa-trophy') ?>" placeholder="fas fa-trophy">
                        <small style="color:#6b7a90;margin-top:4px;font-size:12px">Contoh: fas fa-trophy, fas fa-medal, fas fa-award, fas fa-star</small>
                        <div class="icon-preview">
                            <i class="<?= htmlspecialchars($formData['icon_class'] ?? 'fas fa-trophy') ?>" id="iconPreview"></i>
                            <span style="color:#6b7a90;font-size:13px">Preview Icon</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sort_order">Urutan Tampil</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? 0) ?>" min="0">
                </div>

                <div style="display:flex;gap:12px">
                    <button type="submit" class="btn"><?= $editingAchievement ? 'Simpan Perubahan' : 'Tambah Prestasi' ?></button>
                    <?php if ($editingAchievement): ?>
                        <a href="/crims/admin/achievements.php" class="btn secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Daftar Prestasi</h2>
            <?php if (empty($achievements)): ?>
                <p style="color:#6b7a90;padding:20px 0">Belum ada prestasi. Tambahkan prestasi pertama Anda di atas.</p>
            <?php else: ?>
                <div class="achievements-grid">
                    <?php foreach ($achievements as $achievement): ?>
                        <div class="achievement-preview-card" style="position:relative;overflow:hidden">
                            <?php if (!empty($achievement['image_url'])): ?>
                                <img src="/crims/<?= htmlspecialchars($achievement['image_url']) ?>" alt="<?= htmlspecialchars($achievement['title']) ?>" style="width:100%;height:120px;object-fit:cover;position:absolute;top:0;left:0;right:0;z-index:1">
                                <div style="position:absolute;top:10px;right:10px;width:50px;height:50px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:2">
                                    <i class="<?= htmlspecialchars($achievement['icon_class']) ?>" style="color:#1e5ba8;font-size:1.5rem"></i>
                                </div>
                                <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top, rgba(0,0,0,0.7), transparent);padding:20px;z-index:2">
                                    <h4 style="color:#fff;margin:0;font-size:1rem"><?= htmlspecialchars($achievement['title']) ?></h4>
                                </div>
                            <?php else: ?>
                                <i class="<?= htmlspecialchars($achievement['icon_class']) ?>"></i>
                                <h4><?= htmlspecialchars($achievement['title']) ?></h4>
                                <?php if (!empty($achievement['description'])): ?>
                                    <div><?= $achievement['description'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <table style="margin-top:24px">
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Judul</th>
                            <th>Urutan</th>
                            <th>Upload oleh</th>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($achievements as $achievement): ?>
                            <tr>
                                <td><i class="<?= htmlspecialchars($achievement['icon_class']) ?>" style="font-size:24px;color:#1e5ba8"></i></td>
                                <td><strong><?= htmlspecialchars($achievement['title']) ?></strong></td>
                                <td><?= htmlspecialchars($achievement['sort_order']) ?></td>
                                <td><?= !empty($achievement['role']) ? ucfirst($achievement['role']) : 'Admin' ?></td>
                                <td><?= date('d M Y', strtotime($achievement['created_at'])) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="?edit=<?= $achievement['id'] ?>" class="btn secondary" style="padding:6px 12px;font-size:13px">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus prestasi ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $achievement['id'] ?>">
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
    
    <script>
        // Live icon preview
        (function() {
            var iconClassInput = document.getElementById('icon_class');
            if (iconClassInput) {
                iconClassInput.addEventListener('input', function(e) {
                    const preview = document.getElementById('iconPreview');
                    if (preview) {
                        preview.className = e.target.value || 'fas fa-trophy';
                    }
                });
            }
        })();
    </script>
<?php
$content = ob_get_clean();
echo renderAdminLayout($activePage, 'Kelola Prestasi', $content);
?>

