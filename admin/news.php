<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}

$activePage = 'news';
$uploadDir = __DIR__ . '/../uploads/news/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

function uploadNewsImage(string $fieldName, string $targetDir): ?string
{
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

    $fileName = uniqid('news_', true) . '.' . $extension;
    $targetPath = rtrim($targetDir, '/') . '/' . $fileName;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        throw new RuntimeException('Gagal mengunggah foto.');
    }

    return 'uploads/news/' . $fileName;
}

function removeNewsImage(?string $relativePath): void
{
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
                $stmt = $mysqli->prepare('SELECT image_url FROM news_items WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($row && $row['image_url']) {
                    removeNewsImage($row['image_url']);
                }

                $stmt = $mysqli->prepare('DELETE FROM news_items WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
            $_SESSION['flash_success'] = 'Berita berhasil dihapus.';
        } else {
            $title = trim($_POST['title'] ?? '');
            $summary = trim($_POST['summary'] ?? '');
            $articleUrl = trim($_POST['article_url'] ?? '');
            $publishedAt = trim($_POST['published_at'] ?? '');
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $existingImage = $_POST['existing_image'] ?? null;

            if ($title === '') {
                throw new RuntimeException('Judul berita wajib diisi.');
            }

            $imagePath = $existingImage;
            if (!empty($_FILES['image']['name'])) {
                $imagePath = uploadNewsImage('image', $uploadDir);
                if ($existingImage && $imagePath !== $existingImage) {
                    removeNewsImage($existingImage);
                }
            }

            // Convert published_at to NULL if empty
            $publishedDate = $publishedAt ? $publishedAt : null;

            if ($id > 0) {
                $stmt = $mysqli->prepare('UPDATE news_items SET title = ?, summary = ?, image_url = ?, article_url = ?, published_at = ?, sort_order = ? WHERE id = ?');
                $stmt->bind_param('sssssii', $title, $summary, $imagePath, $articleUrl, $publishedDate, $sortOrder, $id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Berita berhasil diperbarui.';
            } else {
                $stmt = $mysqli->prepare('INSERT INTO news_items (title, summary, image_url, article_url, published_at, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('sssssi', $title, $summary, $imagePath, $articleUrl, $publishedDate, $sortOrder);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Berita baru berhasil ditambahkan.';
            }
        }
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        $_SESSION['form_data'] = $_POST;
    }

    $redirect = '/crims/admin/news.php';
    if ($action !== 'delete' && $id > 0 && empty($_SESSION['flash_error'])) {
        $redirect .= '?edit=' . $id;
    }
    header('Location: ' . $redirect);
    exit;
}

$newsItems = [];
$result = $mysqli->query('SELECT * FROM news_items ORDER BY sort_order ASC, published_at DESC, created_at DESC');
if ($result) {
    $newsItems = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

$editingNews = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($newsItems as $news) {
        if ((int) $news['id'] === $editId) {
            $editingNews = $news;
            break;
        }
    }
}

$formData = $editingNews ?? ($_SESSION['form_data'] ?? null);
unset($_SESSION['form_data']);

$successMessage = $_SESSION['flash_success'] ?? null;
$errorMessage = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

require_once __DIR__ . '/admin_layout.php';

ob_start();
?>
<style>
        .wrapper{margin:0}
        .card{background:#fff;border-radius:20px;padding:24px;box-shadow:0 18px 40px rgba(23,43,77,0.08);margin-bottom:24px}
        .card h2{font-size:22px;margin-bottom:16px}
        form{display:flex;flex-direction:column;gap:16px}
        .form-row{display:flex;gap:16px;flex-wrap:wrap}
        .form-group{flex:1;min-width:220px;display:flex;flex-direction:column}
        label{font-weight:600;color:#1e5ba8;margin-bottom:8px}
        input, select, textarea{padding:12px;border:2px solid #dce4f3;border-radius:12px;font-size:15px;background:#f9fbff;transition:0.2s}
        textarea{min-height:100px;resize:vertical}
        input:focus, select:focus, textarea:focus{outline:none;border-color:#1e5ba8;box-shadow:0 0 0 3px rgba(30,91,168,0.15)}
        .btn{border:none;border-radius:12px;padding:12px 20px;font-weight:600;color:#fff;background:#1e5ba8;cursor:pointer;transition:0.2s;align-self:flex-start}
        .btn.secondary{background:#f1f5f9;color:#1e5ba8}
        .btn.danger{background:#d62828}
        .btn:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(30,91,168,0.2)}
        .alert{padding:14px 18px;border-radius:12px;margin-bottom:16px;font-size:14px}
        .alert-success{background:#e8f7ef;color:#1f7a4d;border:1px solid #a8e0c4}
        .alert-error{background:#fdecea;color:#b7182b;border:1px solid #f5b5b5}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th, td{text-align:left;padding:12px;border-bottom:1px solid #eef2fb}
        th{color:#6b7a90;font-weight:600;text-transform:uppercase;font-size:12px}
        td img{width:120px;height:80px;object-fit:cover;border-radius:12px;background:#f7f9fc;border:1px solid #e5eaf3}
        .actions{display:flex;gap:8px}
        .badge{display:inline-block;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600}
        @media(max-width:768px){
            .form-row{flex-direction:column}
            .card{padding:18px}
        }
    </style>

    <div class="admin-page-header">
        <h1>Kelola Berita</h1>
    </div>
    
    <div class="wrapper">
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2><?= $editingNews ? 'Edit Berita' : 'Tambah Berita Baru' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $formData['id'] ?? 0 ?>">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($formData['image_url'] ?? '') ?>">
                
                <div class="form-group">
                    <label for="title">Judul Berita *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($formData['title'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="summary">Ringkasan Berita</label>
                    <textarea id="summary" name="summary" placeholder="Ringkasan singkat berita..."><?= htmlspecialchars($formData['summary'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="image">Foto Berita</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if (!empty($formData['image_url'])): ?>
                            <div style="margin-top:8px">
                                <img src="/crims/<?= htmlspecialchars($formData['image_url']) ?>" alt="Preview" style="max-width:200px;border-radius:8px;margin-top:8px">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="article_url">Link Artikel Lengkap (opsional)</label>
                        <input type="url" id="article_url" name="article_url" value="<?= htmlspecialchars($formData['article_url'] ?? '') ?>" placeholder="https://...">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="published_at">Tanggal Publikasi</label>
                        <input type="date" id="published_at" name="published_at" value="<?= htmlspecialchars($formData['published_at'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Urutan Tampil</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? 0) ?>" min="0">
                    </div>
                </div>

                <div style="display:flex;gap:12px">
                    <button type="submit" class="btn"><?= $editingNews ? 'Simpan Perubahan' : 'Tambah Berita' ?></button>
                    <?php if ($editingNews): ?>
                        <a href="/crims/admin/news.php" class="btn secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Daftar Berita</h2>
            <?php if (empty($newsItems)): ?>
                <p style="color:#6b7a90;padding:20px 0">Belum ada berita. Tambahkan berita pertama Anda di atas.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Judul</th>
                            <th>Tanggal</th>
                            <th>Urutan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newsItems as $news): ?>
                            <tr>
                                <td>
                                    <?php if ($news['image_url']): ?>
                                        <img src="/crims/<?= htmlspecialchars($news['image_url']) ?>" alt="<?= htmlspecialchars($news['title']) ?>">
                                    <?php else: ?>
                                        <span style="color:#9ca3af">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($news['title']) ?></strong></td>
                                <td>
                                    <?php if ($news['published_at']): ?>
                                        <?= date('d M Y', strtotime($news['published_at'])) ?>
                                    <?php else: ?>
                                        <span style="color:#9ca3af">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($news['sort_order']) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="?edit=<?= $news['id'] ?>" class="btn secondary" style="padding:6px 12px;font-size:13px">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus berita ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $news['id'] ?>">
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
echo renderAdminLayout($activePage, 'Kelola Berita', $content);
?>


