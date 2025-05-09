<?php
require_once "includes/functions.php";

redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

$enquiry_id = isset($_GET['id']) ? (int)sanitize($_GET['id']) : null;

if (!$enquiry_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid enquiry ID.'];
    header("location: admin_manage_enquiries.php");
    exit;
}

global $conn;
$sql = "DELETE FROM enquiries WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $enquiry_id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Enquiry deleted successfully.'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Enquiry not found or already deleted.'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Error deleting enquiry.'];
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Database error preparing delete statement.'];
}

header("location: admin_manage_enquiries.php");
exit;
?> 