<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}

$activePage = 'team';
$uploadDir = __DIR__ . '/../uploads/team/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

function uploadTeamPhoto(string $fieldName, string $targetDir): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $fileType = $_FILES[$fieldName]['type'] ?? '';

    if (!in_array($fileType, $allowedTypes, true)) {
        throw new RuntimeException('Format foto harus JPG, PNG, GIF, atau WebP.');
    }

    $extension = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        throw new RuntimeException('Ekstensi file tidak diizinkan.');
    }

    $fileName = uniqid('team_', true) . '.' . $extension;
    $targetPath = rtrim($targetDir, '/') . '/' . $fileName;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        throw new RuntimeException('Gagal mengunggah foto.');
    }

    return 'uploads/team/' . $fileName;
}

function removeExistingFile(?string $relativePath): void
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

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $id = (int) ($_POST['id'] ?? 0);

    try {
        if ($action === 'delete') {
            if ($id > 0) {
                $stmt = $mysqli->prepare('SELECT photo_url FROM team_structure WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($result && $result['photo_url']) {
                    removeExistingFile($result['photo_url']);
                }

                $stmt = $mysqli->prepare('DELETE FROM team_structure WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
            $_SESSION['flash_success'] = 'Anggota tim berhasil dihapus.';
        } else {
            $name = trim($_POST['name'] ?? '');
            $position = trim($_POST['position_title'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $scopus = trim($_POST['scopus_id'] ?? '');
            $category = trim($_POST['category'] ?? 'Researcher');
            $interests = trim($_POST['research_interests'] ?? '');
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $existingPhoto = $_POST['existing_photo'] ?? null;

            if ($name === '' || $position === '') {
                throw new RuntimeException('Nama dan jabatan wajib diisi.');
            }

            $photoPath = $existingPhoto;
            if (!empty($_FILES['photo']['name'])) {
                $photoPath = uploadTeamPhoto('photo', $uploadDir);
                if ($existingPhoto && $photoPath !== $existingPhoto) {
                    removeExistingFile($existingPhoto);
                }
            }

            if ($id > 0) {
                $stmt = $mysqli->prepare('UPDATE team_structure SET name = ?, position_title = ?, email = ?, scopus_id = ?, photo_url = ?, research_interests = ?, category = ?, sort_order = ? WHERE id = ?');
                $stmt->bind_param('sssssssii', $name, $position, $email, $scopus, $photoPath, $interests, $category, $sortOrder, $id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Data tim berhasil diperbarui.';
            } else {
                $stmt = $mysqli->prepare('INSERT INTO team_structure (name, position_title, email, scopus_id, photo_url, research_interests, category, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('sssssssi', $name, $position, $email, $scopus, $photoPath, $interests, $category, $sortOrder);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'Anggota tim baru berhasil ditambahkan.';
            }
        }
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        $_SESSION['form_data'] = $_POST;
    }

    $redirect = '/crims/admin/team.php';
    if ($action !== 'delete' && $id > 0 && empty($_SESSION['flash_error'])) {
        $redirect .= '?edit=' . $id;
    }

    header('Location: ' . $redirect);
    exit;
}

$teamMembers = [];
$result = $mysqli->query('SELECT * FROM team_structure ORDER BY sort_order ASC, name ASC');
if ($result) {
    $teamMembers = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

$editingMember = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($teamMembers as $member) {
        if ((int) $member['id'] === $editId) {
            $editingMember = $member;
            break;
        }
    }
}

$formData = $editingMember ?? ($_SESSION['form_data'] ?? null);
unset($_SESSION['form_data']);

$successMessage = $_SESSION['flash_success'] ?? null;
$errorMessage = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

require_once __DIR__ . '/admin_layout.php';

ob_start();
?>
<style>
        .wrapper{margin:0}
        .grid{display:grid;grid-template-columns:1fr;gap:24px}
        .card{background:#fff;border-radius:20px;padding:24px;box-shadow:0 18px 40px rgba(23,43,77,0.08)}
        .card h2{font-size:22px;margin-bottom:16px}
        form{display:flex;flex-direction:column;gap:16px}
        .form-row{display:flex;gap:16px;flex-wrap:wrap}
        .form-group{flex:1;min-width:220px;display:flex;flex-direction:column}
        label{font-weight:600;color:#1e5ba8;margin-bottom:8px}
        input, select, textarea{padding:12px;border:2px solid #dce4f3;border-radius:12px;font-size:15px;transition:0.2s;background:#f9fbff}
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
        td img{width:56px;height:56px;border-radius:12px;object-fit:cover;border:2px solid #edf2fb}
        .actions{display:flex;gap:8px}
        @media(max-width:768px){
            .form-row{flex-direction:column}
            .card{padding:18px}
        }
    </style>

    <div class="admin-page-header">
        <h1>Kelola Struktur Tim</h1>
    </div>
    
    <div class="wrapper">
        <div class="grid">
            <div class="card">
                <h2><?= $editingMember ? 'Edit Anggota Tim' : 'Tambah Anggota Tim'; ?></h2>
                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>
                <?php if ($errorMessage): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($formData['id'] ?? '') ?>">
                    <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($formData['photo_url'] ?? '') ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nama Lengkap *</label>
                            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($formData['name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="position_title">Jabatan *</label>
                            <input type="text" id="position_title" name="position_title" required value="<?= htmlspecialchars($formData['position_title'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="scopus_id">Scopus ID</label>
                            <input type="text" id="scopus_id" name="scopus_id" value="<?= htmlspecialchars($formData['scopus_id'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <select name="category" id="category">
                                <?php
                                $categories = ['Researcher', 'Professor', 'Engineer', 'Coordinator', 'Staff'];
                                $selectedCategory = $formData['category'] ?? 'Researcher';
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
                            <label for="research_interests">Research Interests (pisahkan dengan koma)</label>
                            <textarea id="research_interests" name="research_interests"><?= htmlspecialchars($formData['research_interests'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Urutan Tampil</label>
                            <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? 0) ?>">
                        </div>
                        <div class="form-group">
                            <label for="photo">Foto Profil (PNG/JPG/WebP)</label>
                            <input type="file" id="photo" name="photo" accept="image/*">
                            <?php if (!empty($formData['photo_url'])): ?>
                                <small>Foto saat ini: <?= htmlspecialchars($formData['photo_url']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn"><?= $editingMember ? 'Simpan Perubahan' : 'Tambah Anggota'; ?></button>
                        <?php if ($editingMember): ?>
                            <a class="btn secondary" href="/crims/admin/team.php">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="card">
                <h2>Daftar Anggota Tim</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Kategori</th>
                            <th>Email</th>
                            <th>Urutan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($teamMembers) === 0): ?>
                            <tr>
                                <td colspan="7">Belum ada data tim. Tambahkan anggota melalui formulir.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teamMembers as $member): ?>
                                <tr>
                                    <td>
                                        <?php $photoSrc = $member['photo_url'] ? '/' . ltrim($member['photo_url'], '/') : 'https://placehold.co/80x80?text=CRIMS'; ?>
                                        <img src="<?= htmlspecialchars($photoSrc) ?>" alt="<?= htmlspecialchars($member['name']) ?>">
                                    </td>
                                    <td><?= htmlspecialchars($member['name']) ?></td>
                                    <td><?= htmlspecialchars($member['position_title']) ?></td>
                                    <td><?= htmlspecialchars($member['category']) ?></td>
                                    <td><?= htmlspecialchars($member['email']) ?></td>
                                    <td><?= (int) $member['sort_order'] ?></td>
                                    <td class="actions">
                                        <a class="btn secondary" href="/crims/admin/team.php?edit=<?= (int) $member['id'] ?>">Edit</a>
                                        <form method="POST" onsubmit="return confirm('Hapus anggota ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
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
    </div>
<?php
$content = ob_get_clean();
echo renderAdminLayout($activePage, 'Kelola Struktur Tim', $content);
?>

