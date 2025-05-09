<?php
// Include functions file
require_once "includes/functions.php";

// Admin checks
redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

// Fetch all feedback/ratings
global $conn;
$sql = "SELECT r.id, r.rating, r.comment, r.created_at, r.entity_type, r.entity_id, u.phone_number, 
               CASE 
                   WHEN r.entity_type = 'hotel' THEN h.name 
                   WHEN r.entity_type = 'tourist_area' THEN ta.name 
                   ELSE 'N/A' 
               END as entity_name
        FROM ratings r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN hotels h ON r.entity_type = 'hotel' AND r.entity_id = h.id
        LEFT JOIN tourist_areas ta ON r.entity_type = 'tourist_area' AND r.entity_id = ta.id
        ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $sql);
$feedback_items = []; // Initialize as an empty array
if ($result) {
    $feedback_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // You might want to log this error or display a more specific database error message for admins
    error_log("Error fetching feedback: " . mysqli_error($conn));
}


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
        <h1>Manage Visitor Feedback</h1>
    </div>

    <?php if (!empty($flash_message)):
    ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash_message['type']); ?> fade-in">
            <?php echo htmlspecialchars($flash_message['message']); ?>
        </div>
    <?php endif; ?>

    <div class="admin-table-container fade-in">
        <?php if (empty($feedback_items)):
        ?>
            <div class="alert alert-info">No feedback found.</div>
        <?php else:
        ?>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>User (Phone)</th>
                        <th>Entity Type</th>
                        <th>Entity Name</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($feedback_items as $item):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst(str_replace("_", " ", $item['entity_type']))); ?></td>
                        <td>
                            <?php 
                            $detail_page = '#';
                            if ($item['entity_type'] == 'hotel' && $item['entity_id']) {
                                $detail_page = "../hotel_details.php?id=" . $item['entity_id']; // Added ../ to go up one level if admin pages are in subfolder
                            } elseif ($item['entity_type'] == 'tourist_area' && $item['entity_id']) {
                                $detail_page = "../area_details.php?id=" . $item['entity_id']; // Added ../
                            }
                            if ($item['entity_name']) {
                                echo "<a href=\"".htmlspecialchars($detail_page)."\" target='_blank'>".htmlspecialchars($item['entity_name'])."</a>";
                            } elseif ($item['entity_id']) {
                                echo '<em>(Entity ID: '.htmlspecialchars($item['entity_id']).' - Name not found or entity deleted)</em>';
                            } else {
                                echo '<em>N/A</em>';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['rating']); ?> <i class="fas fa-star" style="color: #ffc107;"></i></td>
                        <td><?php echo nl2br(htmlspecialchars($item['comment'])); ?></td>
                        <td><?php echo htmlspecialchars($item['created_at']); ?></td>
                        <td>
                            <a href="admin_delete_feedback.php?id=<?php echo $item['id']; ?>&entity_type=<?php echo $item['entity_type']; ?>&entity_id=<?php echo $item['entity_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this feedback? This may affect the average rating of the entity.');">Delete</a>
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