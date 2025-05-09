<?php
// Include functions file
require_once "includes/functions.php";

// Admin checks
redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

$rating_id = null;
$entity_type = null;
$entity_id = null;

if (isset($_GET["id"]) && !empty(trim($_GET["id"])) && 
    isset($_GET["entity_type"]) && !empty(trim($_GET["entity_type"])) && 
    isset($_GET["entity_id"]) && !empty(trim($_GET["entity_id"]))) {
    
    $rating_id = sanitize(trim($_GET["id"]));
    $entity_type = sanitize(trim($_GET["entity_type"]));
    $entity_id = sanitize(trim($_GET["entity_id"]));

    global $conn;
    $sql = "DELETE FROM ratings WHERE id = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $rating_id);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                // Update the average rating for the entity
                updateEntityRating($entity_type, $entity_id);
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Feedback deleted successfully and entity rating updated.'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'warning',
                    'message' => 'Feedback not found or already deleted.'
                ];
            }
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Oops! Something went wrong. Please try again later.'
            ];
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Error preparing the delete statement.'
        ];
    }
} else {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Invalid request. Feedback ID or entity details not specified.'
    ];
}

header("location: admin_manage_feedback.php");
exit;
?> 