<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Get user ID from session
$user_id = $_SESSION["id"];

// Get high rated hotels and most visited areas for the dashboard
$topHotels = getHighRatedHotels(3);
$topAreas = getMostVisitedAreas(3);

// Get all available locations for the "Near Me" feature
$locations = getAllLocations();
?>

<?php include "includes/header.php"; ?>

<div class="container interface-container">
    <div class="interface-header">
        <h1 class="fade-in">Welcome to Advanced Tourism Platform</h1>
        <p class="fade-in delay-1">Discover amazing hotels and tourist destinations for your next trip</p>
    </div>
    
    <div class="section-title fade-in delay-2">
        <h2>Explore Options</h2>
    </div>
    
    <div class="interface-options fade-in delay-3">
        <!-- Hotel Option -->
        <div class="option-card">
            <div class="option-card-img" style="background-image: url('images/hotel-bg.jpg');"></div>
            <div class="option-card-content">
                <h3>Hotels</h3>
                <p>Find the perfect place to stay during your travels.</p>
                <div class="option-buttons">
                    <a href="hotels.php?filter=top_rated" class="btn btn-primary">High Rated Hotels</a>
                    <a href="nearme.php?type=hotel" class="btn btn-secondary">Hotels Near Me</a>
                </div>
            </div>
        </div>
        
        <!-- Tourist Area Option -->
        <div class="option-card">
            <div class="option-card-img" style="background-image: url('images/tourist-bg.jpg');"></div>
            <div class="option-card-content">
                <h3>Tourist Areas</h3>
                <p>Explore popular destinations and hidden gems.</p>
                <div class="option-buttons">
                    <a href="tourist_areas.php?filter=most_visited" class="btn btn-primary">Most Visited Areas</a>
                    <a href="nearme.php?type=tourist_area" class="btn btn-secondary">Areas Near Me</a>
                </div>
            </div>
        </div>
        
        <!-- Personalized Recommendations Option -->
        <div class="option-card">
            <div class="option-card-img" style="background-image: url('images/recommendations-bg.jpg');"></div>
            <div class="option-card-content">
                <h3>Personalized Recommendations</h3>
                <p>Get tailored suggestions based on your preferences and similar users.</p>
                <div class="option-buttons">
                    <a href="recommendations.php" class="btn btn-primary">View Recommendations</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-title fade-in">
        <h2>Travel Guide</h2>
    </div>
    
    <div class="card guide-container fade-in">
        <p>Plan your perfect trip with our comprehensive travel guide. Select your source and destination to get started.</p>
        
        <form id="destination-form" action="plan_trip.php" method="get" class="destination-form">
            <div class="form-group">
                <label for="source-location">From</label>
                <select id="source-location" name="source" class="form-control">
                    <option value="">Select Source</option>
                    <?php foreach($locations as $location): ?>
                    <option value="<?php echo $location; ?>"><?php echo $location; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="destination-location">To</label>
                <select id="destination-location" name="destination" class="form-control">
                    <option value="">Select Destination</option>
                    <?php foreach($locations as $location): ?>
                    <option value="<?php echo $location; ?>"><?php echo $location; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-accent">Plan My Trip</button>
            </div>
        </form>
    </div>
    
    <div class="section-title fade-in">
        <h2>Featured This Week</h2>
    </div>
    
    <div class="row fade-in">
        <div class="col-md-6">
            <div class="card">
                <h3>Top Rated Hotels</h3>
                <div class="entity-grid">
                    <?php foreach($topHotels as $hotel): ?>
                    <div class="entity-card">
                        <div class="entity-img" style="background-image: url('images/<?php echo $hotel['image']; ?>');">
                            <div class="entity-rating"><i class="fas fa-star"></i> <?php echo number_format($hotel['rating'], 1); ?></div>
                        </div>
                        <div class="entity-content">
                            <h4 class="entity-title"><?php echo $hotel['name']; ?></h4>
                            <div class="entity-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $hotel['location']; ?>
                            </div>
                            <p class="entity-description"><?php echo $hotel['description']; ?></p>
                            <a href="hotel_details.php?id=<?php echo $hotel['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="hotels.php" class="btn btn-secondary">View All Hotels</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <h3>Most Visited Areas</h3>
                <div class="entity-grid">
                    <?php foreach($topAreas as $area): ?>
                    <div class="entity-card">
                        <div class="entity-img" style="background-image: url('images/<?php echo $area['image']; ?>');">
                            <div class="entity-rating"><i class="fas fa-star"></i> <?php echo number_format($area['rating'], 1); ?></div>
                        </div>
                        <div class="entity-content">
                            <h4 class="entity-title"><?php echo $area['name']; ?></h4>
                            <div class="entity-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $area['location']; ?>
                            </div>
                            <p class="entity-description"><?php echo $area['description']; ?></p>
                            <a href="area_details.php?id=<?php echo $area['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="tourist_areas.php" class="btn btn-secondary">View All Tourist Areas</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?> 