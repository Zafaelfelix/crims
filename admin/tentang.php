<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}

$activePage = 'tentang';
$uploadDir = __DIR__ . '/../uploads/about/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

function uploadAboutImage(string $fieldName, string $targetDir): ?string
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

    $fileName = uniqid('about_', true) . '.' . $extension;
    $targetPath = rtrim($targetDir, '/') . '/' . $fileName;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        throw new RuntimeException('Gagal mengunggah foto.');
    }

    return 'uploads/about/' . $fileName;
}

function removeAboutImage(?string $relativePath): void
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

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $overview = trim($_POST['overview'] ?? '');
        $buttonText = trim($_POST['button_text'] ?? 'Selengkapnya');
        $buttonUrl = trim($_POST['button_url'] ?? '/tentang.html');
        $existingImage = $_POST['existing_image'] ?? null;

        if ($overview === '') {
            throw new RuntimeException('Deskripsi tentang wajib diisi.');
        }

        $imagePath = $existingImage;
        if (!empty($_FILES['image']['name'])) {
            if ($existingImage) {
                removeAboutImage($existingImage);
            }
            $imagePath = uploadAboutImage('image', $uploadDir);
        }

        $extra = json_encode([
            'button_text' => $buttonText,
            'button_url' => $buttonUrl,
            'image_url' => $imagePath ?: ($existingImage ?: '')
        ]);

        // Update or insert about_overview
        $stmt = $mysqli->prepare('
            INSERT INTO cms_sections (slug, title, content, extra)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                title = VALUES(title),
                content = VALUES(content),
                extra = VALUES(extra),
                updated_at = CURRENT_TIMESTAMP
        ');
        $title = 'Tentang CRiMS';
        $stmt->bind_param('ssss', $slug, $title, $overview, $extra);
        $slug = 'about_overview';
        $stmt->execute();
        $stmt->close();

        // Handle features
        $features = [];
        for ($i = 1; $i <= 3; $i++) {
            $icon = trim($_POST["feature_icon_$i"] ?? '');
            $title = trim($_POST["feature_title_$i"] ?? '');
            $description = trim($_POST["feature_description_$i"] ?? '');
            
            if ($icon && $title && $description) {
                $features[] = [
                    'icon' => $icon,
                    'title' => $title,
                    'description' => $description
                ];
            }
        }

        $featuresJson = json_encode($features);
        $stmt = $mysqli->prepare('
            INSERT INTO cms_sections (slug, title, content, extra)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                title = VALUES(title),
                content = VALUES(content),
                extra = VALUES(extra),
                updated_at = CURRENT_TIMESTAMP
        ');
        $title = 'Keunggulan';
        $stmt->bind_param('ssss', $slug, $title, $featuresJson, $extra);
        $slug = 'about_features';
        $extra = null;
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash_success'] = 'Konten tentang berhasil disimpan.';
        header('Location: /crims/admin/tentang.php');
        exit;
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get existing data
$overviewData = null;
$featuresData = [];

$stmt = $mysqli->prepare('SELECT content, extra FROM cms_sections WHERE slug = ?');
$slug = 'about_overview';
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $overviewData = [
        'content' => $row['content'],
        'extra' => $row['extra'] ? json_decode($row['extra'], true) : []
    ];
}
$stmt->close();

$stmt = $mysqli->prepare('SELECT content FROM cms_sections WHERE slug = ?');
$slug = 'about_features';
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row && !empty($row['content'])) {
    $featuresData = json_decode($row['content'], true) ?: [];
}
$stmt->close();

// Fill default values if empty
if (!$overviewData) {
    $overviewData = [
        'content' => 'Center for Research in Manufacturing System (CRiMS) merupakan pusat penelitian yang berfokus pada pengembangan sistem manufaktur terpadu dan berkelanjutan. Kami berkomitmen untuk memberikan solusi inovatif dalam bidang manufaktur melalui penelitian berkualitas tinggi dan kolaborasi dengan industri.',
        'extra' => [
            'button_text' => 'Selengkapnya',
            'button_url' => '/tentang.html',
            'image_url' => 'https://images.unsplash.com/photo-1573164713988-8665fc963095?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80'
        ]
    ];
}

if (empty($featuresData)) {
    $featuresData = [
        ['icon' => 'fas fa-flask', 'title' => 'Penelitian Unggulan', 'description' => 'Mengembangkan teknologi mutakhir di bidang manufaktur'],
        ['icon' => 'fas fa-handshake', 'title' => 'Kolaborasi', 'description' => 'Bekerja sama dengan industri dan akademisi'],
        ['icon' => 'fas fa-graduation-cap', 'title' => 'Pengembangan SDM', 'description' => 'Mencetak peneliti dan praktisi handal']
    ];
}

// Ensure we have 3 features
while (count($featuresData) < 3) {
    $featuresData[] = ['icon' => '', 'title' => '', 'description' => ''];
}

if (isset($_SESSION['flash_success'])) {
    $message = $_SESSION['flash_success'];
    $messageType = 'success';
    unset($_SESSION['flash_success']);
}

require_once __DIR__ . '/admin_layout.php';

ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-info-circle"></i> Kelola Tentang Kami</h1>
    <p>Ubah konten halaman "Tentang Kami" di beranda</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType === 'error' ? 'error' : 'success' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="card">
    <div class="card-header">
        <h2>Deskripsi Tentang Kami</h2>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="overview">Deskripsi <span class="required">*</span></label>
            <textarea id="overview" name="overview" rows="5" class="form-control" required><?= htmlspecialchars($overviewData['content']) ?></textarea>
            <small class="form-text">Masukkan deskripsi tentang CRiMS yang akan ditampilkan di beranda</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="button_text">Teks Tombol</label>
                <input type="text" id="button_text" name="button_text" class="form-control" value="<?= htmlspecialchars($overviewData['extra']['button_text'] ?? 'Selengkapnya') ?>">
            </div>
            <div class="form-group">
                <label for="button_url">URL Tombol</label>
                <input type="text" id="button_url" name="button_url" class="form-control" value="<?= htmlspecialchars($overviewData['extra']['button_url'] ?? '/tentang.html') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="image">Gambar</label>
            <?php if (!empty($overviewData['extra']['image_url'])): ?>
                <div class="current-image">
                    <img src="/crims/<?= htmlspecialchars($overviewData['extra']['image_url']) ?>" alt="Current image" style="max-width: 300px; height: auto; border-radius: 8px; margin-bottom: 10px;">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($overviewData['extra']['image_url']) ?>">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form-control" accept="image/*">
            <small class="form-text">Unggah gambar baru untuk mengganti gambar yang ada (opsional)</small>
        </div>
    </div>

    <div class="card-header">
        <h2>Fitur Keunggulan</h2>
    </div>
    <div class="card-body">
        <?php for ($i = 1; $i <= 3; $i++): 
            $feature = $featuresData[$i - 1] ?? ['icon' => '', 'title' => '', 'description' => ''];
        ?>
        <div class="feature-group" style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <h3 style="margin-bottom: 15px; color: #1e5ba8;">Fitur <?= $i ?></h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="feature_icon_<?= $i ?>">Icon (Font Awesome class)</label>
                    <input type="text" id="feature_icon_<?= $i ?>" name="feature_icon_<?= $i ?>" class="form-control" value="<?= htmlspecialchars($feature['icon']) ?>" placeholder="fas fa-flask">
                    <small class="form-text">Contoh: fas fa-flask, fas fa-handshake, fas fa-graduation-cap</small>
                </div>
                <div class="form-group">
                    <label for="feature_title_<?= $i ?>">Judul</label>
                    <input type="text" id="feature_title_<?= $i ?>" name="feature_title_<?= $i ?>" class="form-control" value="<?= htmlspecialchars($feature['title']) ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="feature_description_<?= $i ?>">Deskripsi</label>
                <textarea id="feature_description_<?= $i ?>" name="feature_description_<?= $i ?>" rows="2" class="form-control"><?= htmlspecialchars($feature['description']) ?></textarea>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Perubahan
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
renderAdminLayout($activePage, 'Kelola Tentang Kami', $content);
?>

