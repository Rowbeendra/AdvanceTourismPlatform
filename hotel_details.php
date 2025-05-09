<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Get user ID from session
$user_id = $_SESSION["id"];

// Check if id parameter exists
if (!isset($_GET["id"])) {
    header("location: hotels.php");
    exit;
}

$hotel_id = $_GET["id"];

// Get hotel details
$hotel = getEntityDetails('hotel', $hotel_id);

// Check if hotel exists
if (!$hotel) {
    header("location: hotels.php");
    exit;
}

// Process rating submission
$rating_success = $rating_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["rating"])) {
    $rating = intval($_POST["rating"]);
    $comment = isset($_POST["comment"]) ? trim($_POST["comment"]) : "";
    
    if ($rating >= 1 && $rating <= 5) {
        if (submitRating($user_id, 'hotel', $hotel_id, $rating, $comment)) {
            $rating_success = "Your rating has been submitted successfully!";
            
            // Refresh hotel data to get updated rating
            $hotel = getEntityDetails('hotel', $hotel_id);
        } else {
            $rating_error = "There was an error submitting your rating. Please try again.";
        }
    } else {
        $rating_error = "Please select a valid rating between 1 and 5 stars.";
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="entity-details-container fade-in">
        <?php if ($rating_success): ?>
        <div class="alert alert-success"><?php echo $rating_success; ?></div>
        <?php endif; ?>
        
        <?php if ($rating_error): ?>
        <div class="alert alert-danger"><?php echo $rating_error; ?></div>
        <?php endif; ?>
        
        <div class="entity-header">
            <a href="hotels.php" class="btn btn-secondary back-btn">
                <i class="fas fa-arrow-left"></i> Back to Hotels
            </a>
            <h1><?php echo $hotel['name']; ?></h1>
            <div class="entity-meta">
                <span class="entity-rating large">
                    <i class="fas fa-star"></i> <?php echo number_format($hotel['rating'], 1); ?>
                </span>
                <span class="entity-location">
                    <i class="fas fa-map-marker-alt"></i> <?php echo $hotel['location']; ?>
                </span>
            </div>
        </div>
        
        <div class="entity-image-container">
            <div class="entity-main-image" style="background-image: url('images/<?php echo $hotel['image']; ?>');">
            </div>
        </div>
        
        <div class="entity-content-container">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <h3>Description</h3>
                        <p><?php echo $hotel['description']; ?></p>
                        
                        <h3>Features</h3>
                        <ul class="features-list">
                            <li><i class="fas fa-wifi"></i> Free Wi-Fi</li>
                            <li><i class="fas fa-parking"></i> Parking Available</li>
                            <li><i class="fas fa-utensils"></i> Restaurant</li>
                            <li><i class="fas fa-swimming-pool"></i> Swimming Pool</li>
                            <li><i class="fas fa-concierge-bell"></i> Room Service</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card sticky-sidebar">
                        <h3>Booking & Rating</h3>
                        <a href="booking.php?type=hotel&id=<?php echo $hotel_id; ?>" class="btn btn-success btn-block mb-3">Book Now</a>
                        
                        <h4>Rate this Hotel</h4>
                        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
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
                                <div class="map-info"><?php echo $hotel['name']; ?> is located in <?php echo $hotel['location']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?> 