<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Get user ID from session
$user_id = $_SESSION["id"];

// Check if id parameter exists
if (!isset($_GET["id"])) {
    header("location: tourist_areas.php");
    exit;
}

$area_id = $_GET["id"];

// Get tourist area details
$area = getEntityDetails('tourist_area', $area_id);

// Check if area exists
if (!$area) {
    header("location: tourist_areas.php");
    exit;
}

// Process rating submission
$rating_success = $rating_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle visit recording
    if (isset($_POST["visit"])) {
        if (recordVisit($user_id, 'tourist_area', $area_id)) {
            $visit_success = "Your visit has been recorded!";
            
            // Refresh area data to get updated visit count
            $area = getEntityDetails('tourist_area', $area_id);
        } else {
            $visit_error = "There was an error recording your visit. Please try again.";
        }
    }
    
    // Handle rating submission
    if (isset($_POST["rating"])) {
        $rating = intval($_POST["rating"]);
        $comment = isset($_POST["comment"]) ? trim($_POST["comment"]) : "";
        
        if ($rating >= 1 && $rating <= 5) {
            if (submitRating($user_id, 'tourist_area', $area_id, $rating, $comment)) {
                $rating_success = "Your rating has been submitted successfully!";
                
                // Refresh area data to get updated rating
                $area = getEntityDetails('tourist_area', $area_id);
            } else {
                $rating_error = "There was an error submitting your rating. Please try again.";
            }
        } else {
            $rating_error = "Please select a valid rating between 1 and 5 stars.";
        }
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="entity-details-container fade-in">
        <?php if (isset($rating_success) && $rating_success): ?>
        <div class="alert alert-success"><?php echo $rating_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($rating_error) && $rating_error): ?>
        <div class="alert alert-danger"><?php echo $rating_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($visit_success) && $visit_success): ?>
        <div class="alert alert-success"><?php echo $visit_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($visit_error) && $visit_error): ?>
        <div class="alert alert-danger"><?php echo $visit_error; ?></div>
        <?php endif; ?>
        
        <div class="entity-header">
            <a href="tourist_areas.php" class="btn btn-secondary back-btn">
                <i class="fas fa-arrow-left"></i> Back to Tourist Areas
            </a>
            <h1><?php echo $area['name']; ?></h1>
            <div class="entity-meta">
                <span class="entity-rating large">
                    <i class="fas fa-star"></i> <?php echo number_format($area['rating'], 1); ?>
                </span>
                <span class="entity-visits">
                    <i class="fas fa-users"></i> <?php echo $area['visit_count']; ?> visits
                </span>
                <span class="entity-location">
                    <i class="fas fa-map-marker-alt"></i> <?php echo $area['location']; ?>
                </span>
            </div>
        </div>
        
        <div class="entity-image-container">
            <div class="entity-main-image" style="background-image: url('images/<?php echo $area['image']; ?>');">
            </div>
        </div>
        
        <div class="entity-content-container">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <h3>Description</h3>
                        <p><?php echo $area['description']; ?></p>
                        
                        <h3>Activities</h3>
                        <ul class="activities-list">
                            <li><i class="fas fa-hiking"></i> Hiking</li>
                            <li><i class="fas fa-camera"></i> Photography</li>
                            <li><i class="fas fa-utensils"></i> Local Cuisine</li>
                            <li><i class="fas fa-monument"></i> Cultural Tours</li>
                            <li><i class="fas fa-shopping-bag"></i> Shopping</li>
                        </ul>
                        
                        <div class="visit-action">
                            <form id="visit-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $area_id); ?>" method="post">
                                <input type="hidden" name="visit" value="1">
                                <button type="button" class="btn btn-accent visit-btn">I'm Visiting This Place</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card sticky-sidebar">
                        <h3>Actions & Rating</h3>
                        <a href="booking.php?type=tourist_area&id=<?php echo $area_id; ?>" class="btn btn-success btn-block mb-2">Book This Experience</a>
                        
                        <form id="visit-form" action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" class="mb-2">
                            <input type="hidden" name="visit" value="1">
                            <button type="submit" class="btn btn-accent visit-btn">I'm Visiting This Place</button>
                        </form>
                    </div>
                    
                    <div class="card rating-card">
                        <h3>Rate This Tourist Area</h3>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $area_id); ?>" method="post">
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5">
                                <label for="star5"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1"><i class="fas fa-star"></i></label>
                            </div>
                            <input type="hidden" id="rating-value" name="rating" value="">
                            
                            <div class="form-group">
                                <label for="comment">Comment (Optional)</label>
                                <textarea id="comment" name="comment" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Rating</button>
                        </form>
                    </div>
                    
                    <div class="card">
                        <h3>Location</h3>
                        <div class="map-container">
                            <!-- Simulated Map Image -->
                            <div class="simulated-map">
                                <i class="fas fa-map-marker-alt map-marker"></i>
                                <div class="map-info"><?php echo $area['name']; ?> is located in <?php echo $area['location']; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h3>Best Time to Visit</h3>
                        <div class="visit-time">
                            <p><i class="fas fa-calendar-alt"></i> March to May</p>
                            <p><i class="fas fa-clock"></i> 9:00 AM - 5:00 PM</p>
                            <p><i class="fas fa-info-circle"></i> Less crowded on weekdays</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?> 