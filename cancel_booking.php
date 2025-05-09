<?php
require_once "includes/functions.php";

redirectIfNotLoggedIn();
$user_id = $_SESSION['id'];

$booking_id = isset($_GET['id']) ? (int)sanitize($_GET['id']) : null;

if (!$booking_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid booking ID.'];
    header("location: my_bookings.php");
    exit;
}

global $conn;
// Check if the booking belongs to the current user and is cancellable
$sql_check = "SELECT id, status, start_date FROM bookings WHERE id = ? AND user_id = ?";
if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
    mysqli_stmt_bind_param($stmt_check, "ii", $booking_id, $user_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    if ($booking_to_cancel = mysqli_fetch_assoc($result_check)) {
        // Add any cancellation policy logic here (e.g., cannot cancel if start_date is too close)
        if ($booking_to_cancel['status'] === 'confirmed') {
            // Check if it's not a past booking before cancelling
            if (strtotime($booking_to_cancel['start_date']) >= strtotime(date('Y-m-d'))) {
                $sql_update = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
                if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                    mysqli_stmt_bind_param($stmt_update, "i", $booking_id);
                    if (mysqli_stmt_execute($stmt_update)) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Booking cancelled successfully.'];
                    } else {
                        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Error cancelling booking.'];
                    }
                    mysqli_stmt_close($stmt_update);
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Database error preparing update.'];
                }
            } else {
                 $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Cannot cancel a past booking.'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'This booking cannot be cancelled (it might already be cancelled or not confirmed).'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Booking not found or you do not have permission to cancel it.'];
    }
    mysqli_stmt_close($stmt_check);
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Database error checking booking.'];
}

header("location: my_bookings.php");
exit;
?> 