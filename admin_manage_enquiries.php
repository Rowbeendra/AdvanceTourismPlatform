<?php
require_once "includes/functions.php";

redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

// Fetch all enquiries
global $conn;
$sql = "SELECT e.id, e.name, e.email, e.subject, e.status, e.created_at, u.phone_number as user_phone
        FROM enquiries e
        LEFT JOIN users u ON e.user_id = u.id
        ORDER BY e.status = 'new' DESC, e.created_at DESC";
$result = mysqli_query($conn, $sql);
$enquiries = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Display flash messages
$flash_message = "";
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

include 'includes/header.php';
?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Manage Enquiries</h1>
    </div>

    <?php if (!empty($flash_message)):
    ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash_message['type']); ?> fade-in">
            <?php echo htmlspecialchars($flash_message['message']); ?>
        </div>
    <?php endif; ?>

    <div class="admin-table-container fade-in">
        <?php if (empty($enquiries)):
        ?>
            <div class="alert alert-info">No enquiries found.</div>
        <?php else:
        ?>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered User?</th>
                        <th>Subject</th>
                        <th>Date Received</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($enquiries as $enquiry):
                    ?>
                    <tr class="<?php echo $enquiry['status'] == 'new' ? 'font-weight-bold table-warning' : ''; ?>">
                        <td><?php echo htmlspecialchars($enquiry['id']); ?></td>
                        <td><?php echo htmlspecialchars($enquiry['name']); ?></td>
                        <td><a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>"><?php echo htmlspecialchars($enquiry['email']); ?></a></td>
                        <td><?php echo $enquiry['user_phone'] ? htmlspecialchars($enquiry['user_phone']) : 'Guest'; ?></td>
                        <td><?php echo htmlspecialchars($enquiry['subject']); ?></td>
                        <td><?php echo date("M d, Y, h:i A", strtotime($enquiry['created_at'])); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($enquiry['status'])); ?></td>
                        <td>
                            <a href="admin_view_enquiry.php?id=<?php echo $enquiry['id']; ?>" class="btn btn-sm btn-info">View</a>
                            <a href="admin_delete_enquiry.php?id=<?php echo $enquiry['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this enquiry?');">Delete</a>
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

<?php include 'includes/footer.php'; ?> 