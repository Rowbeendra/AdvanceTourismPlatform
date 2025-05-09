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
    $area_id = sanitize(trim($_GET["id"]));

    global $conn;
    // Before deleting the tourist area, consider related data.
    // For example, if ratings or visits are linked via foreign keys with ON DELETE RESTRICT,
    // you might need to delete those first or set up ON DELETE CASCADE.
    // For simplicity, we'll assume ON DELETE CASCADE is set or related data is handled elsewhere.
    
    // First, delete related ratings to avoid foreign key constraint issues if not using CASCADE or SET NULL
    $sql_delete_ratings = "DELETE FROM ratings WHERE entity_type = 'tourist_area' AND entity_id = ?";
    if ($stmt_ratings = mysqli_prepare($conn, $sql_delete_ratings)) {
        mysqli_stmt_bind_param($stmt_ratings, "i", $area_id);
        mysqli_stmt_execute($stmt_ratings);
        mysqli_stmt_close($stmt_ratings);
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Error preparing to delete related ratings.'
        ];
        header("location: admin_manage_tourist_areas.php");
        exit;
    }

    // Then, delete related visits
    $sql_delete_visits = "DELETE FROM visits WHERE entity_type = 'tourist_area' AND entity_id = ?";
    if ($stmt_visits = mysqli_prepare($conn, $sql_delete_visits)) {
        mysqli_stmt_bind_param($stmt_visits, "i", $area_id);
        mysqli_stmt_execute($stmt_visits);
        mysqli_stmt_close($stmt_visits);
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Error preparing to delete related visits.'
        ];
        header("location: admin_manage_tourist_areas.php");
        exit;
    }

    // Now, delete the tourist area itself
    $sql = "DELETE FROM tourist_areas WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $area_id;

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Tourist area and related data deleted successfully!'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'warning',
                    'message' => 'No tourist area found with that ID, or it was already deleted.'
                ];
            }
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Oops! Something went wrong deleting the tourist area. ' . mysqli_error($conn)
            ];
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Error preparing the delete statement for tourist area.'
        ];
    }
} else {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Invalid request. Tourist area ID not specified.'
    ];
}

header("location: admin_manage_tourist_areas.php");
exit;
?> 