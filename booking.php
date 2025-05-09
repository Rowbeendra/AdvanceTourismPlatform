<?php
require_once "includes/functions.php";

redirectIfNotLoggedIn();

$entity_type = isset($_GET['type']) ? sanitize($_GET['type']) : null;
$entity_id = isset($_GET['id']) ? (int)sanitize($_GET['id']) : null;

$entity_name = "";
$entity_location = "";
$entity_image = "";
$error_msg = "";

if (!$entity_type || !$entity_id || !in_array($entity_type, ['hotel', 'tourist_area'])) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid booking request.'];
    header("location: index.php");
    exit;
}

// Fetch entity details
$entity = getEntityDetails($entity_type, $entity_id);

if (!$entity) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => ucfirst(str_replace('_', ' ', $entity_type)) . ' not found.'];
    header("location: index.php"); // Or redirect to hotels.php/tourist_areas.php
    exit;
}

$entity_name = $entity['name'];
$entity_location = $entity['location'];
$entity_image = $entity['image'];

// Define variables for form fields and errors
$start_date = $end_date = "";
$num_adults = 1;
$num_children = 0;
$start_date_err = $end_date_err = $num_adults_err = $num_children_err = "";

// POST request: Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $start_date = sanitize(trim($_POST["start_date"]));
    $end_date = sanitize(trim($_POST["end_date"]));
    $num_adults = (int)sanitize(trim($_POST["num_adults"]));
    $num_children = (int)sanitize(trim($_POST["num_children"]));

    // Basic Validation
    if (empty($start_date)) {
        $start_date_err = "Please select a start date.";
    } elseif (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        $start_date_err = "Start date cannot be in the past.";
    }

    if (empty($end_date)) {
        $end_date_err = "Please select an end date.";
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $end_date_err = "End date cannot be before the start date.";
    }

    if ($num_adults < 1) {
        $num_adults_err = "At least one adult is required.";
    }
    if ($num_children < 0) {
        $num_children_err = "Number of children cannot be negative.";
    }

    if (empty($start_date_err) && empty($end_date_err) && empty($num_adults_err) && empty($num_children_err)) {
        global $conn;
        $sql = "INSERT INTO bookings (user_id, entity_type, entity_id, start_date, end_date, num_adults, num_children, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "isisssi", $user_id, $entity_type, $entity_id, $start_date, $end_date, $num_adults, $num_children);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Booking confirmed successfully! You can view your bookings on your profile.'
                ];
                // Redirect to a booking success page or user's bookings page
                header("location: my_bookings.php"); 
                exit();
            } else {
                $error_msg = "Error processing booking: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Database error: Could not prepare statement.";
        }
    } else {
        $error_msg = "Please correct the errors in the form.";
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Book Your Stay/Experience</h1>
        <h2><?php echo htmlspecialchars($entity_name); ?></h2>
        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($entity_location); ?></p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger fade-in"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="booking-form-container fade-in">
        <div class="row">
            <div class="col-md-6">
                <?php if(!empty($entity_image)): ?>
                    <img src="images/<?php echo htmlspecialchars($entity_image); ?>" alt="<?php echo htmlspecialchars($entity_name); ?>" class="img-fluid rounded mb-3">
                <?php endif; ?>            
            </div>
            <div class="col-md-6">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?type=' . htmlspecialchars($entity_type) . '&id=' . htmlspecialchars($entity_id); ?>" method="post" novalidate>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control <?php echo !empty($start_date_err) ? 'is-invalid' : ''; ?>" value="<?php echo $start_date; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        <span class="form-text text-danger"><?php echo $start_date_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control <?php echo !empty($end_date_err) ? 'is-invalid' : ''; ?>" value="<?php echo $end_date; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        <span class="form-text text-danger"><?php echo $end_date_err; ?></span>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="num_adults">Adults</label>
                            <input type="number" name="num_adults" id="num_adults" class="form-control <?php echo !empty($num_adults_err) ? 'is-invalid' : ''; ?>" value="<?php echo $num_adults; ?>" min="1" required>
                            <span class="form-text text-danger"><?php echo $num_adults_err; ?></span>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="num_children">Children</label>
                            <input type="number" name="num_children" id="num_children" class="form-control <?php echo !empty($num_children_err) ? 'is-invalid' : ''; ?>" value="<?php echo $num_children; ?>" min="0">
                            <span class="form-text text-danger"><?php echo $num_children_err; ?></span>
                        </div>
                    </div>
                    
                    <!-- Placeholder for pricing if implemented -->
                    <!-- <div class="form-group">
                        <h4>Estimated Price: $XXX.XX</h4>
                    </div> -->

                    <button type="submit" class="btn btn-primary btn-block">Confirm Booking</button>
                    <a href="<?php echo $entity_type == 'hotel' ? 'hotel_details.php?id=' : 'area_details.php?id='; echo $entity_id; ?>" class="btn btn-secondary btn-block mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 