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

// Define variables and initialize with empty values
$name = $location = $description = "";
$current_image_name = ""; // To store the name of the existing image
$image_name_to_save = ""; // This will store the filename to be saved in DB
$name_err = $location_err = $description_err = $image_err = "";
$success_msg = $error_msg = "";
$hotel_id = null;

$target_dir = "images/";

// Processing form data when form is submitted (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotel_id = sanitize(trim($_POST["id"]));
    $name = sanitize(trim($_POST["name"]));
    $location = sanitize(trim($_POST["location"]));
    $description = sanitize(trim($_POST["description"]));
    $current_image_name = sanitize(trim($_POST["current_image_name"])); // Get current image name from hidden field

    // Initialize image_name_to_save with the current image, in case no new image is uploaded
    $image_name_to_save = $current_image_name;

    // Validate name, location, description
    if (empty($name)) $name_err = "Please enter the hotel name.";
    if (empty($location)) $location_err = "Please enter the location.";
    if (empty($description)) $description_err = "Please enter a description.";

    // Image Upload Handling (only if a new file is selected)
    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == UPLOAD_ERR_OK && !empty($_FILES["image_file"]["tmp_name"])) {
        $uploaded_file = $_FILES["image_file"];
        $image_filename_original = basename($uploaded_file["name"]);
        $image_file_type = strtolower(pathinfo($image_filename_original, PATHINFO_EXTENSION));
        // Consider generating a unique name to prevent overwriting and for better management
        // For now, using original name, but be cautious of name clashes.
        $target_file_path = $target_dir . $image_filename_original; 

        $check = getimagesize($uploaded_file["tmp_name"]);
        if ($check === false) {
            $image_err = "File is not an image.";
        }
        if (empty($image_err) && $uploaded_file["size"] > 5000000) { // 5MB limit
            $image_err = "Sorry, your file is too large (max 5MB).";
        }
        $allowed_types = ["jpg", "png", "jpeg", "gif"];
        if (empty($image_err) && !in_array($image_file_type, $allowed_types)) {
            $image_err = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        if (empty($image_err)) {
            if (move_uploaded_file($uploaded_file["tmp_name"], $target_file_path)) {
                $image_name_to_save = $image_filename_original; // New image successfully uploaded
                // If a new image is uploaded and it's different from the old one, delete the old one
                if (!empty($current_image_name) && $current_image_name != $image_name_to_save && file_exists($target_dir . $current_image_name)) {
                    unlink($target_dir . $current_image_name);
                }
            } else {
                $image_err = "Sorry, there was an error uploading your new file.";
            }
        }
    } elseif (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] != UPLOAD_ERR_NO_FILE) {
        // An error occurred with the upload, but not simply 'no file'
        $image_err = "Error with new image upload. Code: " . $_FILES["image_file"]["error"];
    }
    // If no new file is selected, $image_name_to_save remains $current_image_name (or empty if no current image)

    if (empty($name_err) && empty($location_err) && empty($description_err) && empty($image_err)) {
        global $conn;
        $sql = "UPDATE hotels SET name = ?, location = ?, description = ?, image = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssi", $name, $location, $description, $image_name_to_save, $hotel_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Hotel updated successfully!'];
                header("location: admin_manage_hotels.php");
                exit();
            } else {
                $error_msg = "Database update error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_msg = "Please correct the errors in the form.";
        // If there was an image error AND a new file was attempted and moved, but other validation failed,
        // it might be orphaned. This logic is complex. For now, rely on not saving to DB.
        // The previously uploaded file (if different from current) might remain if other errors exist.
    }
} else {
    // If it's a GET request, fetch existing data to populate the form
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $hotel_id = sanitize(trim($_GET["id"]));
        global $conn;
        $sql = "SELECT name, location, description, image FROM hotels WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $hotel_id);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_assoc($result);
                    $name = $row["name"];
                    $location = $row["location"];
                    $description = $row["description"];
                    $current_image_name = $row["image"]; // Store existing image name
                    $image_name_to_save = $current_image_name; // Initialize with current image
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Hotel not found.'];
                    header("location: admin_manage_hotels.php");
                    exit();
                }
            } else { $error_msg = "Error fetching hotel details."; }
            mysqli_stmt_close($stmt);
        } else { $error_msg = "Database prepare error for fetching."; }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid request. Hotel ID not specified.'];
        header("location: admin_manage_hotels.php");
        exit();
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Edit Hotel</h1>
    </div>

    <?php if(!empty($error_msg)): ?>
        <div class="alert alert-danger fade-in"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" class="form-container fade-in" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="id" value="<?php echo $hotel_id; ?>"/>
        <input type="hidden" name="current_image_name" value="<?php echo htmlspecialchars($current_image_name); ?>" />
        
        <div class="form-group">
            <label for="name">Hotel Name</label>
            <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" required>
            <span class="form-text text-danger"><?php echo $name_err; ?></span>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" id="location" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($location); ?>" required>
            <span class="form-text text-danger"><?php echo $location_err; ?></span>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="5" required><?php echo htmlspecialchars($description); ?></textarea>
            <span class="form-text text-danger"><?php echo $description_err; ?></span>
        </div>

        <div class="form-group">
            <label for="image_file">New Hotel Image (Optional)</label>
            <input type="file" name="image_file" id="image_file" class="form-control-file <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
            <span class="form-text text-danger"><?php echo $image_err; ?></span>
            <small class="form-text">Upload a new image to replace the current one (JPG, PNG, GIF - max 5MB). If no file is chosen, the current image will be kept.</small>
            <?php if (!empty($current_image_name)): ?>
                <div class="mt-2">
                    <p>Current Image: <?php echo htmlspecialchars($current_image_name); ?></p>
                    <img src="<?php echo $target_dir . htmlspecialchars($current_image_name); ?>" alt="Current Hotel Image" style="max-width: 200px; max-height: 150px; border-radius: 5px;">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Hotel</button>
            <a href="admin_manage_hotels.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

</div>

<?php include "includes/footer.php"; ?> 