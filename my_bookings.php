<?php
require_once "includes/functions.php";

redirectIfNotLoggedIn();
$user_id = $_SESSION['id'];

// Fetch user's bookings
global $conn;
$sql = "SELECT b.id, b.entity_type, b.entity_id, b.start_date, b.end_date, b.num_adults, b.num_children, b.status, b.booking_date, 
               CASE 
                   WHEN b.entity_type = 'hotel' THEN h.name 
                   WHEN b.entity_type = 'tourist_area' THEN ta.name 
                   ELSE 'N/A' 
               END as entity_name,
               CASE 
                   WHEN b.entity_type = 'hotel' THEN h.image 
                   WHEN b.entity_type = 'tourist_area' THEN ta.image 
                   ELSE '' 
               END as entity_image
        FROM bookings b
        LEFT JOIN hotels h ON b.entity_type = 'hotel' AND b.entity_id = h.id
        LEFT JOIN tourist_areas ta ON b.entity_type = 'tourist_area' AND b.entity_id = ta.id
        WHERE b.user_id = ?
        ORDER BY b.start_date DESC, b.booking_date DESC";

$bookings = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
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
        <h1>My Bookings</h1>
    </div>

    <?php if (!empty($flash_message)):
    ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash_message['type']); ?> fade-in">
            <?php echo htmlspecialchars($flash_message['message']); ?>
        </div>
    <?php endif; ?>

    <div class="my-bookings-container fade-in">
        <?php if (empty($bookings)):
        ?>
            <div class="alert alert-info">You have no bookings yet. <a href="index.php">Explore destinations</a> and book your next trip!</div>
        <?php else:
        ?>
            <div class="list-group">
                <?php foreach ($bookings as $booking):
                    $is_past_booking = strtotime($booking['end_date']) < strtotime(date('Y-m-d'));
                ?>
                    <div class="list-group-item booking-card <?php echo $booking['status'] == 'cancelled' ? 'cancelled-booking' : ($is_past_booking && $booking['status'] != 'cancelled' ? 'past-booking' : ''); ?>">
                        <div class="row">
                            <div class="col-md-3">
                                <?php if (!empty($booking['entity_image'])) : ?>
                                    <img src="images/<?php echo htmlspecialchars($booking['entity_image']); ?>" alt="<?php echo htmlspecialchars($booking['entity_name']); ?>" class="img-fluid rounded">
                                <?php else: ?>
                                    <div class="img-placeholder">No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5>
                                    <?php if ($booking['entity_name']): ?>
                                        <a href="<?php echo $booking['entity_type'] == 'hotel' ? 'hotel_details.php?id=' : 'area_details.php?id='; echo $booking['entity_id']; ?>">
                                            <?php echo htmlspecialchars($booking['entity_name']); ?> 
                                        </a>
                                    <?php else: ?>
                                        <em>(Booked item no longer available)</em>
                                    <?php endif; ?>
                                    <span class="badge badge-info"><?php echo ucfirst(str_replace('_', ' ', $booking['entity_type'])); ?></span>
                                </h5>
                                <p>
                                    <strong>Dates:</strong> <?php echo date("M d, Y", strtotime($booking['start_date'])); ?> - <?php echo date("M d, Y", strtotime($booking['end_date'])); ?><br>
                                    <strong>Guests:</strong> <?php echo $booking['num_adults']; ?> Adult(s)<?php if($booking['num_children'] > 0) echo ", " . $booking['num_children'] . " Child(ren)"; ?><br>
                                    <strong>Booked On:</strong> <?php echo date("M d, Y, h:i A", strtotime($booking['booking_date'])); ?><br>
                                    <strong>Status:</strong> <span class="badge badge-<?php echo $booking['status'] == 'confirmed' ? 'success' : ($booking['status'] == 'cancelled' ? 'danger' : 'secondary'); ?>"><?php echo ucfirst($booking['status']); ?></span>
                                </p>
                            </div>
                            <div class="col-md-3 booking-actions">
                                <?php if ($booking['status'] == 'confirmed' && !$is_past_booking): ?>
                                    <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel Booking</a>
                                <?php elseif ($booking['status'] == 'cancelled'): ?>
                                    <p class="text-muted">Cancelled</p>
                                <?php elseif ($is_past_booking && $booking['status'] != 'cancelled'): ?>
                                    <p class="text-muted">Completed/Past</p>
                                    <!-- Option to rebook or review could go here -->
                                <?php endif; ?>
                            </div>
                        </div>
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
.booking-card {
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 15px;
}
.cancelled-booking {
    background-color: #f8d7da; /* Light red for cancelled */
    opacity: 0.7;
}
.past-booking {
    background-color: #e9ecef; /* Light grey for past */
}
.booking-card img {
    max-height: 150px;
    object-fit: cover;
}
.img-placeholder {
    height: 150px; 
    background-color: #f0f0f0; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    border-radius: 5px; 
    color: #777;
}
.booking-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}
</style>

<?php include 'includes/footer.php'; ?> 