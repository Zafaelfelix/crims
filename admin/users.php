<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /crims/login/');
    exit;
}

// Hanya admin yang bisa akses halaman ini
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /crims/admin/dashboard.php');
    exit;
}

$activePage = 'users';
$uploadDir = __DIR__ . '/../uploads/users/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $id = (int) ($_POST['id'] ?? 0);

    try {
        if ($action === 'delete') {
            if ($id > 0) {
                // Cek apakah user yang akan dihapus adalah admin
                $stmt = $mysqli->prepare('SELECT role FROM users WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();

                if ($user && $user['role'] === 'admin') {
                    throw new RuntimeException('User dengan role Admin tidak dapat dihapus.');
                }

                // Cek jumlah admin yang tersisa
                $adminCount = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
                $adminRow = $adminCount->fetch_assoc();
                if ($adminRow['total'] <= 1) {
                    throw new RuntimeException('Minimal harus ada 1 user dengan role Admin.');
                }

                $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
            $_SESSION['flash_success'] = 'User berhasil dihapus.';
        } else {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role = trim($_POST['role'] ?? 'admin');
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            // Validasi
            if ($username === '') {
                throw new RuntimeException('Username wajib diisi.');
            }

            if (!in_array($role, ['admin', 'dosen', 'mahasiswa'], true)) {
                throw new RuntimeException('Role tidak valid.');
            }
            
            // Validasi: Hanya username "admin1" yang boleh menjadi admin
            if ($role === 'admin' && strtolower($username) !== 'admin1') {
                throw new RuntimeException('Role Admin hanya untuk username "admin1".');
            }
            
            // Validasi: Tidak boleh membuat user baru dengan role admin
            if ($id <= 0 && $role === 'admin') {
                throw new RuntimeException('Tidak dapat menambahkan user baru dengan role Admin. Role Admin hanya untuk user admin1.');
            }
            
            // Validasi: Tidak boleh mengubah role user yang sudah ada menjadi admin
            if ($id > 0 && $role === 'admin') {
                // Cek apakah user yang diedit memang sudah admin sebelumnya
                $checkStmt = $mysqli->prepare('SELECT username, role FROM users WHERE id = ?');
                $checkStmt->bind_param('i', $id);
                $checkStmt->execute();
                $existingUser = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();
                
                // Hanya admin1 yang boleh tetap admin
                if ($existingUser && (strtolower($existingUser['username']) !== 'admin1' || $existingUser['role'] !== 'admin')) {
                    throw new RuntimeException('Tidak dapat mengubah role menjadi Admin. Role Admin hanya untuk user admin1.');
                }
            }

            // Cek apakah username sudah ada (untuk user baru atau edit dengan username berbeda)
            $checkStmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
            $checkId = $id > 0 ? $id : 0;
            $checkStmt->bind_param('si', $username, $checkId);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if ($existing) {
                throw new RuntimeException('Username sudah digunakan.');
            }

            if ($id > 0) {
                // Update existing user
                if ($password !== '') {
                    // Update dengan password baru
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare('UPDATE users SET username = ?, password = ?, role = ?, full_name = ?, email = ? WHERE id = ?');
                    $stmt->bind_param('sssssi', $username, $hashedPassword, $role, $fullName, $email, $id);
                } else {
                    // Update tanpa mengubah password
                    $stmt = $mysqli->prepare('UPDATE users SET username = ?, role = ?, full_name = ?, email = ? WHERE id = ?');
                    $stmt->bind_param('ssssi', $username, $role, $fullName, $email, $id);
                }
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'User berhasil diperbarui.';
            } else {
                // Insert new user
                if ($password === '') {
                    throw new RuntimeException('Password wajib diisi untuk user baru.');
                }
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare('INSERT INTO users (username, password, role, full_name, email) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('sssss', $username, $hashedPassword, $role, $fullName, $email);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash_success'] = 'User baru berhasil ditambahkan.';
            }
        }
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        $_SESSION['form_data'] = $_POST;
    }

    $redirect = '/crims/admin/users.php';
    if ($action !== 'delete' && $id > 0 && empty($_SESSION['flash_error'])) {
        $redirect .= '?edit=' . $id;
    }
    header('Location: ' . $redirect);
    exit;
}

// Get all users
$users = [];
$result = $mysqli->query('SELECT id, username, role, full_name, email, created_at FROM users ORDER BY role ASC, username ASC');
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

// Get user untuk edit
$editingUser = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($users as $user) {
        if ((int) $user['id'] === $editId) {
            $editingUser = $user;
            break;
        }
    }
}

$formData = $editingUser ?? ($_SESSION['form_data'] ?? null);
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
    .card h2{font-size:22px;margin-bottom:16px;color:#1e5ba8}
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
    .actions{display:flex;gap:8px}
    .badge{display:inline-block;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600}
    .badge-admin{background:#e1edff;color:#1e5ba8}
    .badge-dosen{background:#d1fae5;color:#059669}
    .badge-mahasiswa{background:#fef3c7;color:#d97706}
    .password-note{font-size:12px;color:#64748b;margin-top:4px}
    @media(max-width:768px){
        .form-row{flex-direction:column}
        .card{padding:18px}
    }
</style>

<div class="admin-page-header">
    <h1>Kelola Users</h1>
    <p>Tambahkan dan kelola akun user (Admin, Dosen, Mahasiswa)</p>
</div>

<?php if ($successMessage): ?>
    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>

<div class="card">
    <h2><?= $editingUser ? 'Edit User' : 'Tambah User Baru' ?></h2>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?= $editingUser['id'] ?? 0 ?>">
        <input type="hidden" name="action" value="save">
        
        <div class="form-row">
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($formData['username'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <?php if ($editingUser && $editingUser['role'] === 'admin'): ?>
                        <!-- Jika edit user admin, tetap tampilkan admin tapi disabled -->
                        <option value="admin" selected>Admin</option>
                    <?php endif; ?>
                    <option value="dosen" <?= ($formData['role'] ?? '') === 'dosen' ? 'selected' : '' ?>>Dosen</option>
                    <option value="mahasiswa" <?= ($formData['role'] ?? '') === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                </select>
                <?php if (!$editingUser || $editingUser['role'] !== 'admin'): ?>
                    <div class="password-note" style="margin-top: 4px;">Note: Role Admin hanya untuk user admin1</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="password">Password <?= $editingUser ? '' : '*' ?></label>
                <input type="password" id="password" name="password" <?= $editingUser ? '' : 'required' ?>>
                <?php if ($editingUser): ?>
                    <div class="password-note">Kosongkan jika tidak ingin mengubah password</div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($formData['full_name'] ?? '') ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
            </div>
        </div>
        
        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn"><?= $editingUser ? 'Update User' : 'Tambah User' ?></button>
            <?php if ($editingUser): ?>
                <a href="/crims/admin/users.php" class="btn secondary">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <h2>Daftar Users</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Role</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                        Belum ada user. Tambahkan user baru di atas.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                        <td><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                        <td>
                            <?php
                            $roleClass = 'badge-' . $user['role'];
                            $roleLabel = ucfirst($user['role']);
                            ?>
                            <span class="badge <?= $roleClass ?>"><?= $roleLabel ?></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                        <td>
                            <div class="actions">
                                <a href="?edit=<?= $user['id'] ?>" class="btn secondary" style="padding: 6px 12px; font-size: 13px;">Edit</a>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn danger" style="padding: 6px 12px; font-size: 13px;">Hapus</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #64748b; font-size: 13px;">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
renderAdminLayout($activePage, 'Kelola Users', $content);
?>

