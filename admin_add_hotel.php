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
$image_name_to_save = ""; // This will store the filename to be saved in DB
$name_err = $location_err = $description_err = $image_err = "";
$success_msg = $error_msg = "";

// Define the target directory for uploads
$target_dir = "images/";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize(trim($_POST["name"]));
    $location = sanitize(trim($_POST["location"]));
    $description = sanitize(trim($_POST["description"]));

    // Validate name, location, description (as before)
    if (empty($name)) $name_err = "Please enter the hotel name.";
    if (empty($location)) $location_err = "Please enter the location.";
    if (empty($description)) $description_err = "Please enter a description.";

    // Image Upload Handling
    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == UPLOAD_ERR_OK) {
        $uploaded_file = $_FILES["image_file"];
        $image_filename_original = basename($uploaded_file["name"]);
        $image_file_type = strtolower(pathinfo($image_filename_original, PATHINFO_EXTENSION));
        $target_file_path = $target_dir . $image_filename_original; // Using original name for now, consider unique names

        // Check if image file is a actual image or fake image
        $check = getimagesize($uploaded_file["tmp_name"]);
        if ($check === false) {
            $image_err = "File is not an image.";
        }

        // Check file size (e.g., 5MB limit)
        if (empty($image_err) && $uploaded_file["size"] > 5000000) {
            $image_err = "Sorry, your file is too large (max 5MB).";
        }

        // Allow certain file formats
        $allowed_types = ["jpg", "png", "jpeg", "gif"];
        if (empty($image_err) && !in_array($image_file_type, $allowed_types)) {
            $image_err = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        // Check if file already exists (optional - could overwrite or generate unique name)
        // For simplicity, we will overwrite if names clash. For production, generate unique names.
        // if (empty($image_err) && file_exists($target_file_path)) {
        //     $image_err = "Sorry, file already exists. Rename your file.";
        // }

        if (empty($image_err)) {
            if (move_uploaded_file($uploaded_file["tmp_name"], $target_file_path)) {
                $image_name_to_save = $image_filename_original; // Save the original (or unique) filename
            } else {
                $image_err = "Sorry, there was an error uploading your file.";
            }
        }
    } elseif (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] != UPLOAD_ERR_NO_FILE) {
        // If a file was selected but an error occurred (other than no file)
        $image_err = "Error during file upload. Code: " . $_FILES["image_file"]["error"];
    } else {
        // No file uploaded, this might be an error if image is mandatory
        $image_err = "Please select an image file to upload."; // Make image mandatory
    }

    // Check input errors before inserting in database
    if (empty($name_err) && empty($location_err) && empty($description_err) && empty($image_err)) {
        global $conn;
        $sql = "INSERT INTO hotels (name, location, description, image) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $location, $description, $image_name_to_save);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Hotel added successfully!'];
                header("location: admin_manage_hotels.php");
                exit();
            } else {
                $error_msg = "Database error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        if (!empty($image_err) && !empty($image_name_to_save) && file_exists($target_dir . $image_name_to_save)){
             // If there was an image upload error but other fields also have errors, 
             // and the file was somehow moved, attempt to delete it to prevent orphaned files.
             // This is a basic cleanup, more robust error handling might be needed.
            unlink($target_dir . $image_name_to_save);
        }
        $error_msg = "Please correct the errors in the form.";
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Add New Hotel</h1>
    </div>

    <?php if(!empty($success_msg)): ?>
        <div class="alert alert-success fade-in"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    <?php if(!empty($error_msg)): ?>
        <div class="alert alert-danger fade-in"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="form-container fade-in" enctype="multipart/form-data" novalidate>
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
            <label for="image_file">Hotel Image</label>
            <input type="file" name="image_file" id="image_file" class="form-control-file <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>" required>
            <span class="form-text text-danger"><?php echo $image_err; ?></span>
            <small class="form-text">Upload an image for the hotel (JPG, PNG, GIF - max 5MB).</small>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Add Hotel</button>
            <a href="admin_manage_hotels.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?> 