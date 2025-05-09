<?php
// Include functions file
require_once "includes/functions.php";

// Admin checks
redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

$user_id_to_edit = null;
$phone_number = "";
$current_role = "";
$new_role = "";
$role_err = "";
$error_msg = "";
$available_roles = ['visitor', 'admin']; // Define available roles

// GET request: Fetch user details
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $user_id_to_edit = sanitize(trim($_GET["id"]));

    global $conn;
    $sql = "SELECT phone_number, role FROM users WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id_to_edit);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) == 1) {
                $user_data = mysqli_fetch_assoc($result);
                $phone_number = $user_data["phone_number"];
                $current_role = $user_data["role"];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'User not found.'];
                header("location: admin_manage_users.php");
                exit();
            }
        } else {
            $error_msg = "Error fetching user details.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = "Database prepare error.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // POST request: Update user role
    $user_id_to_edit = sanitize(trim($_POST["id"]));
    $new_role = sanitize(trim($_POST["role"]));
    $phone_number = sanitize(trim($_POST["phone_number"])); // Keep for display if error
    $current_role = sanitize(trim($_POST["current_role"])); // Keep for display if error

    if (!in_array($new_role, $available_roles)) {
        $role_err = "Invalid role selected.";
    }

    // Prevent admin from changing their own role to visitor if they are the only admin
    if ($user_id_to_edit == $_SESSION['id'] && $new_role == 'visitor') {
        global $conn;
        $sql_check_admins = "SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'";
        $res_check_admins = mysqli_query($conn, $sql_check_admins);
        $row_check_admins = mysqli_fetch_assoc($res_check_admins);
        if ($row_check_admins['admin_count'] <= 1) {
            $role_err = "Cannot change your own role to visitor. You are the only admin.";
        }
    }

    if (empty($role_err)) {
        global $conn;
        $sql_update = "UPDATE users SET role = ? WHERE id = ?";
        if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "si", $new_role, $user_id_to_edit);
            if (mysqli_stmt_execute($stmt_update)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'User role updated successfully.'];
                header("location: admin_manage_users.php");
                exit();
            } else {
                $error_msg = "Error updating user role.";
            }
            mysqli_stmt_close($stmt_update);
        }
    } else {
        $error_msg = "Please correct the errors.";
    }
} else {
    // Invalid request or no ID for GET
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid request.'];
    header("location: admin_manage_users.php");
    exit();
}
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Edit User Role</h1>
        <p>Editing role for user: <strong><?php echo htmlspecialchars($phone_number); ?></strong></p>
        <p>Current Role: <strong><?php echo htmlspecialchars(ucfirst($current_role)); ?></strong></p>
    </div>

    <?php if(!empty($error_msg)): ?>
        <div class="alert alert-danger fade-in"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" class="form-container fade-in" novalidate>
        <input type="hidden" name="id" value="<?php echo $user_id_to_edit; ?>">
        <input type="hidden" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>"> 
        <input type="hidden" name="current_role" value="<?php echo htmlspecialchars($current_role); ?>">

        <div class="form-group">
            <label for="role">New Role</label>
            <select name="role" id="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                <?php foreach ($available_roles as $role_option): ?>
                    <option value="<?php echo $role_option; ?>" <?php echo ($role_option == $current_role) ? 'selected' : ''; ?>>
                        <?php echo ucfirst($role_option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="form-text text-danger"><?php echo $role_err; ?></span>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Role</button>
            <a href="admin_manage_users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

</div>

<?php include "includes/footer.php"; ?> 