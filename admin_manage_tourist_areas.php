<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Check if the user is an admin, if not redirect to the main interface
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

// Fetch all tourist areas to display
global $conn;
$sql = "SELECT id, name, location, rating, visit_count FROM tourist_areas ORDER BY name ASC";
$result = mysqli_query($conn, $sql);
$areas = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Display flash messages
$flash_message = "";
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Clear the message after displaying
}
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Manage Tourist Areas</h1>
        <a href="admin_add_tourist_area.php" class="btn btn-primary" style="margin-left: 20px;">Add New Tourist Area</a>
    </div>

    <?php if (!empty($flash_message)):
    ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash_message['type']); ?> fade-in">
            <?php echo htmlspecialchars($flash_message['message']); ?>
        </div>
    <?php endif; ?>

    <div class="admin-table-container fade-in">
        <?php if (empty($areas)):
        ?>
            <div class="alert alert-info">No tourist areas found. You can add one using the button above.</div>
        <?php else:
        ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Rating</th>
                        <th>Visits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($areas as $area):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($area['id']); ?></td>
                        <td><?php echo htmlspecialchars($area['name']); ?></td>
                        <td><?php echo htmlspecialchars($area['location']); ?></td>
                        <td><?php echo number_format($area['rating'], 1); ?></td>
                        <td><?php echo htmlspecialchars($area['visit_count']); ?></td>
                        <td>
                            <a href="admin_edit_tourist_area.php?id=<?php echo $area['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="admin_delete_tourist_area.php?id=<?php echo $area['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this tourist area?');">Delete</a>
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