<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Get user ID from session
$user_id = $_SESSION["id"];

// Get filter parameter (if any)
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Get tourist areas based on filter
if ($filter === 'most_visited') {
    $title = "Most Visited Areas";
    $areas = getMostVisitedAreas(10);
} else {
    $title = "All Tourist Areas";
    
    global $conn;
    $sql = "SELECT * FROM tourist_areas ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    $areas = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get all available locations for filtering
$locations = getAllLocations();
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1><?php echo $title; ?></h1>
    </div>
    
    <div class="area-filters fade-in">
        <div class="card">
            <h3>Filter Options</h3>
            <div class="filter-controls">
                <a href="tourist_areas.php" class="btn <?php echo empty($filter) ? 'btn-primary' : 'btn-secondary'; ?>">All Areas</a>
                <a href="tourist_areas.php?filter=most_visited" class="btn <?php echo $filter === 'most_visited' ? 'btn-primary' : 'btn-secondary'; ?>">Most Visited</a>
                
                <form action="nearme.php" method="get" class="location-filter">
                    <input type="hidden" name="type" value="tourist_area">
                    <div class="form-group">
                        <label for="user-location">Areas Near:</label>
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
        <?php if (empty($areas)): ?>
        <div class="alert alert-info">No tourist areas found matching your criteria.</div>
        <?php else: ?>
        
        <?php foreach($areas as $area): ?>
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
                <div class="entity-stats">
                    <span><i class="fas fa-users"></i> <?php echo $area['visit_count']; ?> visits</span>
                </div>
                <a href="area_details.php?id=<?php echo $area['id']; ?>" class="btn btn-primary">View Details</a>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?> 