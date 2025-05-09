<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Get user ID from session
$user_id = $_SESSION["id"];

// Get all available locations for the guide
$locations = getAllLocations();
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <br>
        <h1>Travel Guide</h1>
    </div>
    
    <div class="guide-container fade-in">
        <div class="guide-intro card">
            <h3>Plan Your Perfect Trip</h3>
            <p>Welcome to our comprehensive travel guide! Whether you're looking for a relaxing getaway or an adventure-filled trip, we have recommendations tailored just for you. Select your preferences below to get started.</p>
        </div>
        
        <div class="guide-content card">
            <h3>Travel Recommendations</h3>
            
            <div class="guide-filters">
                <div class="form-group">
                    <label>Trip Type</label>
                    <div class="trip-type-options">
                        <button class="btn btn-outline-primary guide-item active" data-content="Discover breathtaking mountains, serene lakes, and lush forests. Perfect for outdoor enthusiasts and nature lovers.">Nature</button>
                        <button class="btn btn-outline-primary guide-item" data-content="Immerse yourself in rich history, stunning architecture, and local traditions. Great for cultural explorers.">Cultural</button>
                        <button class="btn btn-outline-primary guide-item" data-content="Experience thrilling activities like hiking, rock climbing, water sports, and more. Ideal for thrill-seekers.">Adventure</button>
                        <button class="btn btn-outline-primary guide-item" data-content="Enjoy beautiful beaches, luxury resorts, and spa treatments. Perfect for those looking to unwind.">Relaxation</button>
                    </div>
                </div>
            </div>
            
            <div id="guide-content" class="guide-result">
                <p>Discover breathtaking mountains, serene lakes, and lush forests. Perfect for outdoor enthusiasts and nature lovers.</p>
                
                <h4>Top Nature Destinations</h4>
                <div class="top-destinations">
                    <div class="destination-card">
                        <div class="destination-img" style="background-image: url('images/phewa.jpg');"></div>
                        <div class="destination-content">
                            <h5>Phewa Lake, Pokhara</h5>
                            <p>A beautiful lake with stunning mountain views.</p>
                        </div>
                    </div>
                    
                    <div class="destination-card">
                        <div class="destination-img" style="background-image: url('images/chitwan.jpg');"></div>
                        <div class="destination-content">
                            <h5>Chitwan National Park</h5>
                            <p>Home to diverse wildlife and lush forests.</p>
                        </div>
                    </div>
                    
                    <div class="destination-card">
                        <div class="destination-img" style="background-image: url('images/annapurna.jpg');"></div>
                        <div class="destination-content">
                            <h5>Annapurna Base Camp</h5>
                            <p>Spectacular mountain views and trekking trails.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="destination-planner card">
            <h3>Destination Planner</h3>
            <p>Plan your trip by selecting your source and destination:</p>
            
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
        
        <div class="travel-tips card">
            <h3>Essential Travel Tips</h3>
            <div class="tips-grid">
                <div class="tip-item">
                    <i class="fas fa-suitcase"></i>
                    <h4>Packing</h4>
                    <p>Pack light but smart. Include essentials like weather-appropriate clothing, toiletries, and any necessary medications.</p>
                </div>
                
                <div class="tip-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <h4>Budget</h4>
                    <p>Set a daily budget for accommodations, food, activities, and souvenirs to avoid overspending.</p>
                </div>
                
                <div class="tip-item">
                    <i class="fas fa-map-marked-alt"></i>
                    <h4>Navigation</h4>
                    <p>Download offline maps before your trip and consider learning a few basic phrases in the local language.</p>
                </div>
                
                <div class="tip-item">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Safety</h4>
                    <p>Always keep copies of important documents, be aware of your surroundings, and know emergency contact numbers.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?> 