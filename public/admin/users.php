<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    
    // Check if trying to delete self
    if ($deleteId === $_SESSION['user']['id']) {
        $error = "You cannot delete yourself!";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            $success = "User deleted successfully.";
        } else {
            $error = "Failed to delete user.";
        }
    }
}

// Fetch all users
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

$pageTitle = 'Manage Users';
require_once __DIR__ . '/layout_header.php';
?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1>Manage Users</h1>
        <a href="add_user.php" class="btn">+ Add User</a>
    </div>

    <?php if (isset($success)): ?>
        <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px;"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $user['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($user['name']) ?></strong>
                    </td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; background: <?= $user['role']==='admin' ? '#e0f2fe' : '#f1f5f9' ?>; color: <?= $user['role']==='admin' ? '#0369a1' : '#475569' ?>;">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                    <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php if ($user['role'] !== 'admin' || $user['id'] !== $_SESSION['user']['id']): ?>
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn small" style="background: white; color: #2563eb; border: 1px solid #cbd5e1; margin-right: 4px;">Edit</a>
                        <form method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn small danger">Delete</button>
                        </form>
                        <?php else: ?>
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn small" style="background: white; color: #2563eb; border: 1px solid #cbd5e1; margin-right: 4px;">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
