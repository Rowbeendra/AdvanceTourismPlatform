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

// Check if ID is provided in URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $hotel_id = sanitize(trim($_GET["id"]));

    global $conn;
    $sql = "DELETE FROM hotels WHERE id = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $hotel_id;

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Hotel deleted successfully!'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'warning',
                    'message' => 'No hotel found with that ID, or it was already deleted.'
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
    // If no ID is provided, set an error message
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Invalid request. Hotel ID not specified.'
    ];
}

// Redirect back to the manage hotels page
header("location: admin_manage_hotels.php");
exit;
?> 