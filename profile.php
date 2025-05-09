<?php
require_once "includes/functions.php";

redirectIfNotLoggedIn();
$user_id = $_SESSION['id'];

// Fetch user details
global $conn;
$user_details = null;
$sql_user = "SELECT phone_number, created_at, role FROM users WHERE id = ?";
if ($stmt_user = mysqli_prepare($conn, $sql_user)) {
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    $user_details = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_user);
}

// Fetch user's ratings/feedback
$user_feedback = [];
$sql_feedback = "SELECT r.id, r.rating, r.comment, r.created_at as feedback_date, r.entity_type, r.entity_id, 
                       CASE 
                           WHEN r.entity_type = 'hotel' THEN h.name 
                           WHEN r.entity_type = 'tourist_area' THEN ta.name 
                           ELSE 'N/A' 
                       END as entity_name
                FROM ratings r
                LEFT JOIN hotels h ON r.entity_type = 'hotel' AND r.entity_id = h.id
                LEFT JOIN tourist_areas ta ON r.entity_type = 'tourist_area' AND r.entity_id = ta.id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC";

if ($stmt_feedback = mysqli_prepare($conn, $sql_feedback)) {
    mysqli_stmt_bind_param($stmt_feedback, "i", $user_id);
    mysqli_stmt_execute($stmt_feedback);
    $result_feedback = mysqli_stmt_get_result($stmt_feedback);
    $user_feedback = mysqli_fetch_all($result_feedback, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_feedback);
}

include 'includes/header.php';
?>

<div class="container">
    <div class="section-title fade-in">
        <h1>My Profile</h1>
    </div>

    <?php if ($user_details): ?>
    <div class="card profile-details-card fade-in mb-4">
        <div class="card-header">
            <h4>Account Information</h4>
        </div>
        <div class="card-body">
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user_details['phone_number']); ?></p>
            <p><strong>Member Since:</strong> <?php echo date("M d, Y", strtotime($user_details['created_at'])); ?></p>
            <p><strong>Account Type:</strong> <?php echo ucfirst(htmlspecialchars($user_details['role'])); ?></p>
            <!-- Add link to edit profile if/when that functionality is built -->
        </div>
    </div>
    <?php endif; ?>

    <div class="profile-section fade-in mb-4">
        <h3>My Activity</h3>
        <div class="list-group">
            <a href="my_bookings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar-check mr-2"></i> View My Bookings
            </a>
            <!-- Add other activity links here if needed -->
        </div>
    </div>

    <div class="profile-section fade-in">
        <h3>My Feedback & Ratings</h3>
        <?php if (empty($user_feedback)): ?>
            <div class="alert alert-info">You have not submitted any feedback yet.</div>
        <?php else: ?>
            <div class="list-group feedback-list">
                <?php foreach($user_feedback as $feedback_item):
                    $entity_detail_link = '#';
                    if ($feedback_item['entity_type'] == 'hotel' && $feedback_item['entity_id']) {
                        $entity_detail_link = "hotel_details.php?id=" . $feedback_item['entity_id'];
                    } elseif ($feedback_item['entity_type'] == 'tourist_area' && $feedback_item['entity_id']) {
                        $entity_detail_link = "area_details.php?id=" . $feedback_item['entity_id'];
                    }
                ?>
                    <div class="list-group-item">
                        <h5 class="mb-1">
                            Rating for: 
                            <?php if ($feedback_item['entity_name']): ?>
                                <a href="<?php echo htmlspecialchars($entity_detail_link); ?>"><?php echo htmlspecialchars($feedback_item['entity_name']); ?></a>
                            <?php else: ?>
                                <em>(Entity no longer available)</em>
                            <?php endif; ?>
                            (<?php echo htmlspecialchars(ucfirst(str_replace("_"," ",$feedback_item['entity_type']))); ?>)
                        </h5>
                        <p class="mb-1">
                            <strong>Rating:</strong> <?php echo htmlspecialchars($feedback_item['rating']); ?> <i class="fas fa-star" style="color: #ffc107;"></i><br>
                            <?php if(!empty($feedback_item['comment'])): ?>
                                <strong>Comment:</strong> <?php echo nl2br(htmlspecialchars($feedback_item['comment'])); ?><br>
                            <?php endif; ?>
                            <small>Submitted on: <?php echo date("M d, Y, h:i A", strtotime($feedback_item['feedback_date'])); ?></small>
                        </p>
                        <!-- Option to edit/delete feedback could go here if desired -->
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="interface.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

</div>
<style>
.profile-details-card .card-body p { margin-bottom: 0.5rem; }
.profile-section h3 { margin-bottom: 1rem; }
.feedback-list .list-group-item h5 { font-size: 1.1rem; }
.feedback-list .list-group-item p { font-size: 0.9rem; }
</style>

<?php include 'includes/footer.php'; ?> 