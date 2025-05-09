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

// Define variables and initialize
$name = $location = $description = "";
$current_image_name = "";
$image_name_to_save = "";
$rating = 0; // Default rating for edit form, will be overwritten by fetched data
$visit_count = 0; // Default visit_count
$name_err = $location_err = $description_err = $image_err = $rating_err = "";
$error_msg = "";
$area_id = null;
$target_dir = "images/";

// Processing form data when form is submitted (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $area_id = sanitize(trim($_POST["id"]));
    $name = sanitize(trim($_POST["name"]));
    $location = sanitize(trim($_POST["location"]));
    $description = sanitize(trim($_POST["description"]));
    $current_image_name = sanitize(trim($_POST["current_image_name"]));
    $image_name_to_save = $current_image_name;

    // Validate name, location, description, image_name (similar to add form)
    if (empty($name)) $name_err = "Please enter the tourist area name.";
    if (empty($location)) $location_err = "Please enter the location.";
    if (empty($description)) $description_err = "Please enter a description.";

    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == UPLOAD_ERR_OK && !empty($_FILES["image_file"]["tmp_name"])) {
        $uploaded_file = $_FILES["image_file"];
        $image_filename_original = basename($uploaded_file["name"]);
        $image_file_type = strtolower(pathinfo($image_filename_original, PATHINFO_EXTENSION));
        $target_file_path = $target_dir . $image_filename_original;

        $check = getimagesize($uploaded_file["tmp_name"]);
        if ($check === false) $image_err = "File is not an image.";
        if (empty($image_err) && $uploaded_file["size"] > 5000000) $image_err = "File too large (max 5MB).";
        $allowed_types = ["jpg", "png", "jpeg", "gif"];
        if (empty($image_err) && !in_array($image_file_type, $allowed_types)) $image_err = "Only JPG, JPEG, PNG & GIF.";

        if (empty($image_err)) {
            if (move_uploaded_file($uploaded_file["tmp_name"], $target_file_path)) {
                $image_name_to_save = $image_filename_original;
                if (!empty($current_image_name) && $current_image_name != $image_name_to_save && file_exists($target_dir . $current_image_name)) {
                    unlink($target_dir . $current_image_name);
                }
            } else { $image_err = "Error uploading new file."; }
        }
    } elseif (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] != UPLOAD_ERR_NO_FILE) {
        $image_err = "Upload error code: " . $_FILES["image_file"]["error"];
    }
    
    // Validate rating (optional, can be edited by admin)
    if (isset($_POST["rating"])) {
        $rating_input = trim($_POST["rating"]);
        if (!is_numeric($rating_input) || $rating_input < 0 || $rating_input > 5) {
            $rating_err = "Rating must be a number between 0 and 5.";
        } else {
            $rating = floatval($rating_input);
        }
    } else {
        // If not submitted, retain existing rating (or set to 0 if it's a new field being added to edit)
        // For this case, we expect it to be part of the form, so an error if not set might be better,
        // but we'll allow it to be unchanged if not explicitly set in POST
    }

    if (empty($name_err) && empty($location_err) && empty($description_err) && empty($image_err) && empty($rating_err)) {
        global $conn;
        // Visit count is usually not directly editable by admin in this way, but rating might be.
        $sql = "UPDATE tourist_areas SET name = ?, location = ?, description = ?, image = ?, rating = ? WHERE id = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssdi", $name, $location, $description, $image_name_to_save, $rating, $area_id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Tourist area updated successfully!'
                ];
                header("location: admin_manage_tourist_areas.php");
                exit();
            } else {
                $error_msg = "Oops! Something went wrong. Please try again later. Error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_msg = "Please correct the errors in the form.";
    }
} else {
    // GET request: Fetch existing data
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $area_id = sanitize(trim($_GET["id"]));
        global $conn;
        $sql = "SELECT name, location, description, image, rating, visit_count FROM tourist_areas WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $area_id);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_assoc($result);
                    $name = $row["name"];
                    $location = $row["location"];
                    $description = $row["description"];
                    $current_image_name = $row["image"];
                    $image_name_to_save = $current_image_name;
                    $rating = $row["rating"];
                    $visit_count = $row["visit_count"]; // For display, not directly editable here
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Tourist area not found.'];
                    header("location: admin_manage_tourist_areas.php");
                    exit();
                }
            } else {
                $error_msg = "Error fetching tourist area details.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Database prepare error.";
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid request. Area ID not specified.'];
        header("location: admin_manage_tourist_areas.php");
        exit();
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Edit Tourist Area</h1>
    </div>

    <?php if(!empty($error_msg)): ?>
        <div class="alert alert-danger fade-in"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" class="form-container fade-in" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="id" value="<?php echo $area_id; ?>"/>
        <input type="hidden" name="current_image_name" value="<?php echo htmlspecialchars($current_image_name); ?>" />
        
        <div class="form-group">
            <label for="name">Tourist Area Name</label>
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
            <label for="image_file">New Area Image (Optional)</label>
            <input type="file" name="image_file" id="image_file" class="form-control-file <?php echo !empty($image_err) ? 'is-invalid' : ''; ?>">
            <span class="form-text text-danger"><?php echo $image_err; ?></span>
            <small class="form-text">Upload to replace current image (JPG, PNG, GIF - max 5MB).</small>
            <?php if (!empty($current_image_name)): ?>
                <div class="mt-2">
                    <p>Current: <?php echo htmlspecialchars($current_image_name); ?></p>
                    <img src="<?php echo $target_dir . htmlspecialchars($current_image_name); ?>" alt="Current Area Image" style="max-width: 200px; max-height: 150px; border-radius: 5px;">
                </div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="rating">Rating (0-5)</label>
            <input type="number" step="0.1" name="rating" id="rating" class="form-control <?php echo (!empty($rating_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($rating); ?>">
            <span class="form-text text-danger"><?php echo $rating_err; ?></span>
        </div>
        
        <div class="form-group">
            <label>Current Visit Count (Read-only)</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($visit_count); ?>" readonly>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Tourist Area</button>
            <a href="admin_manage_tourist_areas.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

</div>

<?php include "includes/footer.php"; ?> 