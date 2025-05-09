<?php
// Include functions file
require_once "includes/functions.php";

// Admin checks
redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

// Fetch all users
global $conn;
$sql = "SELECT id, phone_number, role, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Display flash messages
$flash_message = "";
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Manage Users</h1>
    </div>

    <?php if (!empty($flash_message)):
    ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash_message['type']); ?> fade-in">
            <?php echo htmlspecialchars($flash_message['message']); ?>
        </div>
    <?php endif; ?>

    <div class="admin-table-container fade-in">
        <?php if (empty($users)):
        ?>
            <div class="alert alert-info">No users found.</div>
        <?php else:
        ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phone Number</th>
                        <th>Role</th>
                        <th>Registered At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user_item):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user_item['id']); ?></td>
                        <td><?php echo htmlspecialchars($user_item['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($user_item['role'])); ?></td>
                        <td><?php echo htmlspecialchars($user_item['created_at']); ?></td>
                        <td>
                            <a href="admin_edit_user_role.php?id=<?php echo $user_item['id']; ?>" class="btn btn-sm btn-warning">Edit Role</a>
                            <?php if ($_SESSION['id'] != $user_item['id']): // Prevent admin from deleting themselves ?>
                                <a href="admin_delete_user.php?id=<?php echo $user_item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action may also delete their ratings and visit history.');">Delete</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-danger" disabled>Delete</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>

</div>

<?php include "includes/footer.php"; ?> 