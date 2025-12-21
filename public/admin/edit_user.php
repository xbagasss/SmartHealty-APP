<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

if (!$user) {
    die("User not found.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    
    // Valiate role
    if (!in_array($role, ['user', 'admin'])) {
        $error = "Invalid role selected.";
    } else {
        $updateStmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $updateStmt->bind_param("si", $role, $id);
        
        if ($updateStmt->execute()) {
            $success = "User role updated successfully!";
            // Refresh user data
            $user['role'] = $role;
        } else {
            $error = "Failed to update user.";
        }
    }
}

$pageTitle = 'Edit User';
require_once __DIR__ . '/layout_header.php';
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
        <h1>Edit User</h1>
        <a href="users.php" class="btn small" style="background:transparent; color:#64748b; border:1px solid #cbd5e1;">&larr; Back</a>
    </div>

    <?php if ($success): ?>
        <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #86efac;">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #fca5a5;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin-bottom: 16px;">
            <label style="display:block; margin-bottom: 8px; font-weight: 500;">Name</label>
            <input type="text" value="<?= htmlspecialchars($user['name']) ?>" disabled 
                   style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f1f5f9; color: #64748b;">
            <small style="color: #94a3b8;">User name cannot be changed here.</small>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display:block; margin-bottom: 8px; font-weight: 500;">Email</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled 
                   style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f1f5f9; color: #64748b;">
        </div>

        <div style="margin-bottom: 24px;">
            <label for="role" style="display:block; margin-bottom: 8px; font-weight: 500;">Role</label>
            <select name="role" id="role" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <small style="color: #64748b; display:block; margin-top:4px;">
                ⚠️ <strong>Admin</strong> has full access to manage content and other users.
            </small>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Save Changes</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
