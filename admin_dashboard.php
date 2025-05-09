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

// If execution reaches here, the user is an admin.
?>

<?php include "includes/header.php"; // Assuming you have a common header ?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="admin-welcome fade-in">
        <p>Welcome, Admin! This is your control panel.</p>
        <p>From here, you will be able to manage hotels, tour packages, users, and feedback.</p>
    </div>

    <div class="admin-menu fade-in">
        <!-- Links to admin functionalities will go here -->
        <ul>
            <li><a href="admin_manage_hotels.php">Manage Hotels</a></li>
            <li><a href="admin_manage_tourist_areas.php">Manage Tourist Areas</a></li>
            <li><a href="admin_manage_users.php">Manage Users</a></li>
            <li><a href="admin_manage_feedback.php">Manage Feedback</a></li>
            <li><a href="admin_manage_enquiries.php">Manage Enquiries</a></li>
        </ul>
    </div>

</div>

<?php include "includes/footer.php"; // Assuming you have a common footer ?> 