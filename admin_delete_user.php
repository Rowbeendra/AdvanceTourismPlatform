<?php
// Include functions file
require_once "includes/functions.php";

// Admin checks
redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

$user_id_to_delete = null;

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $user_id_to_delete = sanitize(trim($_GET["id"]));

    // CRITICAL: Prevent admin from deleting their own account
    if ($user_id_to_delete == $_SESSION['id']) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'You cannot delete your own account.'
        ];
        header("location: admin_manage_users.php");
        exit;
    }

    global $conn;

    // Start a transaction
    mysqli_begin_transaction($conn);

    try {
        // Delete related ratings
        $sql_delete_ratings = "DELETE FROM ratings WHERE user_id = ?";
        if ($stmt_ratings = mysqli_prepare($conn, $sql_delete_ratings)) {
            mysqli_stmt_bind_param($stmt_ratings, "i", $user_id_to_delete);
            mysqli_stmt_execute($stmt_ratings);
            mysqli_stmt_close($stmt_ratings);
        } else {
            throw new Exception("Error preparing to delete user ratings.");
        }

        // Delete related visits
        $sql_delete_visits = "DELETE FROM visits WHERE user_id = ?";
        if ($stmt_visits = mysqli_prepare($conn, $sql_delete_visits)) {
            mysqli_stmt_bind_param($stmt_visits, "i", $user_id_to_delete);
            mysqli_stmt_execute($stmt_visits);
            mysqli_stmt_close($stmt_visits);
        } else {
            throw new Exception("Error preparing to delete user visits.");
        }

        // Potentially delete related bookings if that table exists and is linked
        // $sql_delete_bookings = "DELETE FROM bookings WHERE user_id = ?";
        // ... execute ...

        // Finally, delete the user
        $sql_delete_user = "DELETE FROM users WHERE id = ?";
        if ($stmt_user = mysqli_prepare($conn, $sql_delete_user)) {
            mysqli_stmt_bind_param($stmt_user, "i", $user_id_to_delete);
            if (mysqli_stmt_execute($stmt_user)) {
                if (mysqli_stmt_affected_rows($stmt_user) > 0) {
                    mysqli_commit($conn); // Commit transaction
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => 'User and their related data deleted successfully!'
                    ];
                } else {
                    throw new Exception("User not found or already deleted.");
                }
            } else {
                throw new Exception("Error deleting user: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_user);
        } else {
            throw new Exception("Error preparing user deletion statement.");
        }

    } catch (Exception $e) {
        mysqli_rollback($conn); // Rollback transaction on error
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => $e->getMessage()
        ];
    }

} else {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Invalid request. User ID not specified.'
    ];
}

header("location: admin_manage_users.php");
exit;
?> 