<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}

$activePage = 'partners';
$uploadDir = __DIR__ . '/../uploads/partners/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

function uploadPartnerLogo(string $fieldName, string $targetDir): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
    $fileType = $_FILES[$fieldName]['type'] ?? '';

    if (!in_array($fileType, $allowedTypes, true)) {
        throw new RuntimeException('Logo harus berupa gambar (JPG/PNG/WebP/SVG/GIF).');
    }

    $extension = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
        throw new RuntimeException('Ekstensi file tidak diizinkan.');
    }

    $fileName = uniqid('partner_', true) . '.' . $extension;
    $targetPath = rtrim($targetDir, '/') . '/' . $fileName;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        throw new RuntimeException('Gagal mengunggah logo.');
    }

    return 'uploads/partners/' . $fileName;
}

function removePartnerLogo(?string $relativePath): void
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
                $stmt = $mysqli->prepare('SELECT logo_url FROM partner_items WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($row && $row['logo_url']) {
                    removePartnerLogo($row['logo_url']);
                }

                $stmt = $mysqli->prepare('DELETE FROM partner_items WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
            $_SESSION['flash_success'] = 'Data mitra berhasil dihapus.';
        } else {
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? 'Industry');
            $website = trim($_POST['website_url'] ?? '');
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $existingLogo = $_POST['existing_logo'] ?? null;

            if ($name === '') {
                throw new RuntimeException('Nama mitra wajib diisi.');
            }

            $logoPath = $existingLogo;
            if (!empty($_FILES['logo']['name'])) {
                $logoPath = uploadPartnerLogo('logo', $uploadDir);
                if ($existingLogo && $logoPath !== $existingLogo) {
                    removePartnerLogo($existingLogo);
                }
            }

            if ($id > 0) {
                $stmt = $mysqli->prepare('UPDATE partner_items SET name = ?, category = ?, website_url = ?, logo_url = ?, sort_order = ? WHERE id = ?');
                $stmt->bind_param('ssssii', $name, $category, $website, $logoPath, $sortOrder, $id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Data mitra diperbarui.';
            } else {
                $stmt = $mysqli->prepare('INSERT INTO partner_items (name, category, website_url, logo_url, sort_order) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssi', $name, $category, $website, $logoPath, $sortOrder);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Mitra baru berhasil ditambahkan.';
            }
        }
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        $_SESSION['form_data'] = $_POST;
    }

    $redirect = '/crims/admin/partners.php';
    if ($action !== 'delete' && $id > 0 && empty($_SESSION['flash_error'])) {
        $redirect .= '?edit=' . $id;
    }
    header('Location: ' . $redirect);
    exit;
}

$partners = [];
$result = $mysqli->query('SELECT * FROM partner_items ORDER BY sort_order ASC, name ASC');
if ($result) {
    $partners = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

$editingPartner = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($partners as $partner) {
        if ((int) $partner['id'] === $editId) {
            $editingPartner = $partner;
            break;
        }
    }
}

$formData = $editingPartner ?? ($_SESSION['form_data'] ?? null);
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
        input, select{padding:12px;border:2px solid #dce4f3;border-radius:12px;font-size:15px;background:#f9fbff;transition:0.2s}
        input:focus, select:focus{outline:none;border-color:#1e5ba8;box-shadow:0 0 0 3px rgba(30,91,168,0.15)}
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
        td img{width:80px;height:50px;object-fit:contain;border-radius:12px;background:#f7f9fc;border:1px solid #e5eaf3;padding:6px}
        .actions{display:flex;gap:8px}
        @media(max-width:768px){
            .form-row{flex-direction:column}
            .card{padding:18px}
        }
    </style>

    <div class="admin-page-header">
        <h1>Kelola Mitra</h1>
    </div>
    
    <div class="wrapper">
        <div class="card">
            <h2><?= $editingPartner ? 'Edit Mitra' : 'Tambah Mitra'; ?></h2>
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= htmlspecialchars($formData['id'] ?? '') ?>">
                <input type="hidden" name="existing_logo" value="<?= htmlspecialchars($formData['logo_url'] ?? '') ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Mitra *</label>
                        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($formData['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select name="category" id="category">
                            <?php
                            $categories = ['Industry', 'University', 'Government', 'Startup', 'Community'];
                            $selectedCategory = $formData['category'] ?? 'Industry';
                            foreach ($categories as $categoryOption): ?>
                                <option value="<?= $categoryOption ?>" <?= $selectedCategory === $categoryOption ? 'selected' : '' ?>>
                                    <?= $categoryOption ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="website_url">Website (opsional)</label>
                        <input type="url" id="website_url" name="website_url" value="<?= htmlspecialchars($formData['website_url'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Urutan Tampil</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? 0) ?>">
                    </div>
                    <div class="form-group">
                        <label for="logo">Logo (PNG/JPG/SVG)</label>
                        <input type="file" id="logo" name="logo" accept="image/*">
                        <?php if (!empty($formData['logo_url'])): ?>
                            <small>Logo saat ini: <?= htmlspecialchars($formData['logo_url']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-row">
                    <button type="submit" class="btn"><?= $editingPartner ? 'Simpan Perubahan' : 'Tambah Mitra'; ?></button>
                    <?php if ($editingPartner): ?>
                        <a class="btn secondary" href="/crims/admin/partners.php">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Daftar Mitra</h2>
            <table>
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Website</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($partners) === 0): ?>
                        <tr>
                            <td colspan="6">Belum ada data mitra. Tambahkan melalui formulir.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($partners as $partner): ?>
                            <tr>
                                <td>
                                    <?php $logoSrc = $partner['logo_url'] ? '/' . ltrim($partner['logo_url'], '/') : 'https://placehold.co/120x60?text=Logo'; ?>
                                    <img src="<?= htmlspecialchars($logoSrc) ?>" alt="<?= htmlspecialchars($partner['name']) ?>">
                                </td>
                                <td><?= htmlspecialchars($partner['name']) ?></td>
                                <td><?= htmlspecialchars($partner['category']) ?></td>
                                <td>
                                    <?php if (!empty($partner['website_url'])): ?>
                                        <a href="<?= htmlspecialchars($partner['website_url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($partner['website_url']) ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= (int) $partner['sort_order'] ?></td>
                                <td class="actions">
                                    <a class="btn secondary" href="/crims/admin/partners.php?edit=<?= (int) $partner['id'] ?>">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Hapus mitra ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $partner['id'] ?>">
                                        <button type="submit" class="btn danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
$content = ob_get_clean();
echo renderAdminLayout($activePage, 'Kelola Mitra', $content);
?>

