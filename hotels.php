<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Get user ID from session
$user_id = $_SESSION["id"];

// Get filter parameter (if any)
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Get hotels based on filter
if ($filter === 'top_rated') {
    $title = "High Rated Hotels";
    $hotels = getHighRatedHotels(10);
} else {
    $title = "All Hotels";
    
    global $conn;
    $sql = "SELECT * FROM hotels ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    $hotels = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get all available locations for filtering
$locations = getAllLocations();
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1><?php echo $title; ?></h1>
    </div>
    
    <div class="hotel-filters fade-in">
        <div class="card">
            <h3>Filter Options</h3>
            <div class="filter-controls">
                <a href="hotels.php" class="btn <?php echo empty($filter) ? 'btn-primary' : 'btn-secondary'; ?>">All Hotels</a>
                <a href="hotels.php?filter=top_rated" class="btn <?php echo $filter === 'top_rated' ? 'btn-primary' : 'btn-secondary'; ?>">High Rated</a>
                
                <form action="nearme.php" method="get" class="location-filter">
                    <input type="hidden" name="type" value="hotel">
                    <div class="form-group">
                        <label for="user-location">Hotels Near:</label>
                        <select id="user-location" name="location" class="form-control">
                            <option value="">Select Location</option>
                            <?php foreach($locations as $location): ?>
                            <option value="<?php echo $location; ?>"><?php echo $location; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Find</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="entity-grid fade-in">
        <?php if (empty($hotels)): ?>
        <div class="alert alert-info">No hotels found matching your criteria.</div>
        <?php else: ?>
        
        <?php foreach($hotels as $hotel): ?>
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
        
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?> 