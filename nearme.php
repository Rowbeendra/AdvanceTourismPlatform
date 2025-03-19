<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Get user ID from session
$user_id = $_SESSION["id"];

// Get entity type and location parameters
$type = isset($_GET['type']) ? $_GET['type'] : 'hotel'; // Default to hotel
$location = isset($_GET['location']) ? $_GET['location'] : '';

// Set page title and get entities based on type and location
if ($type === 'hotel') {
    $title = "Hotels Near Me";
    $entities = !empty($location) ? getHotelsNearMe($location) : [];
    $empty_message = "Please select a location to find nearby hotels.";
    $detail_page = "hotel_details.php";
} else {
    $title = "Tourist Areas Near Me";
    $entities = !empty($location) ? getTouristAreasNearMe($location) : [];
    $empty_message = "Please select a location to find nearby tourist areas.";
    $detail_page = "area_details.php";
}

// Get all available locations for the dropdown
$locations = getAllLocations();
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1><?php echo $title; ?></h1>
    </div>
    
    <div class="location-selector fade-in">
        <div class="card">
            <h3>Find <?php echo $type === 'hotel' ? 'Hotels' : 'Tourist Areas'; ?> Near:</h3>
            <form action="nearme.php" method="get" class="location-filter">
                <input type="hidden" name="type" value="<?php echo $type; ?>">
                <div class="form-group">
                    <select id="user-location" name="location" class="form-control">
                        <option value="">Select Location</option>
                        <?php foreach($locations as $loc): ?>
                        <option value="<?php echo $loc; ?>" <?php echo $loc === $location ? 'selected' : ''; ?>><?php echo $loc; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Find Nearby</button>
                
                <div class="toggle-type">
                    <span>Looking for: </span>
                    <a href="nearme.php?type=hotel<?php echo !empty($location) ? '&location=' . $location : ''; ?>" class="btn <?php echo $type === 'hotel' ? 'btn-primary' : 'btn-secondary'; ?>">Hotels</a>
                    <a href="nearme.php?type=tourist_area<?php echo !empty($location) ? '&location=' . $location : ''; ?>" class="btn <?php echo $type === 'tourist_area' ? 'btn-primary' : 'btn-secondary'; ?>">Tourist Areas</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="entity-grid fade-in">
        <?php if (empty($location)): ?>
        <div class="alert alert-info"><?php echo $empty_message; ?></div>
        <?php elseif (empty($entities)): ?>
        <div class="alert alert-info">No <?php echo $type === 'hotel' ? 'hotels' : 'tourist areas'; ?> found near <?php echo $location; ?>.</div>
        <?php else: ?>
        
        <?php foreach($entities as $entity): ?>
        <div class="entity-card">
            <div class="entity-img" style="background-image: url('images/<?php echo $entity['image']; ?>');">
                <div class="entity-rating"><i class="fas fa-star"></i> <?php echo number_format($entity['rating'], 1); ?></div>
            </div>
            <div class="entity-content">
                <h4 class="entity-title"><?php echo $entity['name']; ?></h4>
                <div class="entity-location">
                    <i class="fas fa-map-marker-alt"></i> <?php echo $entity['location']; ?>
                </div>
                <p class="entity-description"><?php echo $entity['description']; ?></p>
                
                <?php if ($type === 'tourist_area'): ?>
                <div class="entity-stats">
                    <span><i class="fas fa-users"></i> <?php echo $entity['visit_count']; ?> visits</span>
                </div>
                <?php endif; ?>
                
                <a href="<?php echo $detail_page; ?>?id=<?php echo $entity['id']; ?>" class="btn btn-primary">View Details</a>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php endif; ?>
    </div>
    
    <div class="back-link">
        <a href="<?php echo $type === 'hotel' ? 'hotels.php' : 'tourist_areas.php'; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to All <?php echo $type === 'hotel' ? 'Hotels' : 'Tourist Areas'; ?>
        </a>
    </div>
</div>

<?php include "includes/footer.php"; ?> 