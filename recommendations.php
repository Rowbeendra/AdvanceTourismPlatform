<?php
// Include header and functions
require_once "includes/header.php";
require_once "includes/functions.php";
require_once "includes/recommendations.php";

// Check if user is logged in
redirectIfNotLoggedIn();

// Get user ID from session
$userId = $_SESSION["id"];

// Get recommendation type from query parameter (default to hybrid)
$recommendationType = isset($_GET['type']) ? sanitize($_GET['type']) : 'hybrid';
$entityType = isset($_GET['entity']) ? sanitize($_GET['entity']) : 'hotel';
$limit = 5;

// Validate recommendation type
$validTypes = ['content', 'collaborative', 'hybrid'];
if (!in_array($recommendationType, $validTypes)) {
    $recommendationType = 'hybrid';
}

// Validate entity type
$validEntities = ['hotel', 'tourist_area'];
if (!in_array($entityType, $validEntities)) {
    $entityType = 'hotel';
}

// Get recommendations based on selected type
$recommendations = [];

switch ($recommendationType) {
    case 'content':
        $recommendations = getContentBasedRecommendations($userId, $entityType, $limit);
        $title = "Content-Based Recommendations";
        break;
    case 'collaborative':
        $recommendations = getCollaborativeRecommendations($userId, $entityType, $limit);
        $title = "Collaborative Filtering Recommendations";
        break;
    case 'hybrid':
    default:
        $recommendations = getHybridRecommendations($userId, $entityType, $limit);
        $title = "Personalized Recommendations";
        break;
}

// Get entity name for display
$entityName = ($entityType == 'hotel') ? 'Hotels' : 'Tourist Areas';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4"><?php echo $title; ?></h1>
            <p class="lead">Discover <?php echo $entityName; ?> tailored just for you based on your preferences and similar users.</p>
            
            <!-- Recommendation Type Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Recommendation Type</h5>
                    <div class="btn-group" role="group">
                        <a href="?type=content&entity=<?php echo $entityType; ?>" class="btn <?php echo $recommendationType == 'content' ? 'btn-primary' : 'btn-outline-primary'; ?>">Content-Based</a>
                        <a href="?type=collaborative&entity=<?php echo $entityType; ?>" class="btn <?php echo $recommendationType == 'collaborative' ? 'btn-primary' : 'btn-outline-primary'; ?>">Collaborative</a>
                        <a href="?type=hybrid&entity=<?php echo $entityType; ?>" class="btn <?php echo $recommendationType == 'hybrid' ? 'btn-primary' : 'btn-outline-primary'; ?>">Hybrid</a>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Entity Type:</h6>
                        <div class="btn-group" role="group">
                            <a href="?type=<?php echo $recommendationType; ?>&entity=hotel" class="btn <?php echo $entityType == 'hotel' ? 'btn-success' : 'btn-outline-success'; ?>">Hotels</a>
                            <a href="?type=<?php echo $recommendationType; ?>&entity=tourist_area" class="btn <?php echo $entityType == 'tourist_area' ? 'btn-success' : 'btn-outline-success'; ?>">Tourist Areas</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recommendations Display -->
            <?php if (empty($recommendations)): ?>
                <div class="alert alert-info">
                    <p>We don't have enough data to generate personalized recommendations yet. Try rating some <?php echo strtolower($entityName); ?> first!</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($recommendations as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="images/<?php echo $item['image']; ?>" class="card-img-top" alt="<?php echo $item['name']; ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-light text-center py-5">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $item['name']; ?></h5>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo $item['location']; ?>
                                        </small>
                                    </p>
                                    
                                    <div class="mb-2">
                                        <?php
                                        $rating = $item['rating'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star text-warning"></i>';
                                            } elseif ($i - 0.5 <= $rating) {
                                                echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                            } else {
                                                echo '<i class="far fa-star text-warning"></i>';
                                            }
                                        }
                                        ?>
                                        <span class="ms-1"><?php echo number_format($rating, 1); ?></span>
                                    </div>
                                    
                                    <?php if ($entityType == 'tourist_area' && isset($item['visit_count'])): ?>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-users"></i> <?php echo $item['visit_count']; ?> visits
                                            </small>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="card-text"><?php echo substr($item['description'], 0, 100) . '...'; ?></p>
                                </div>
                                
                                <div class="card-footer">
                                    <?php if ($entityType == 'hotel'): ?>
                                        <a href="hotel_details.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    <?php else: ?>
                                        <a href="area_details.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- How Recommendations Work -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">How Our Recommendation System Works</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Content-Based Filtering</h6>
                            <p>Recommends items similar to what you've liked in the past, based on features like location, rating, and popularity.</p>
                        </div>
                        <div class="col-md-4">
                            <h6>Collaborative Filtering</h6>
                            <p>Recommends items based on what similar users have liked, helping you discover new places you might enjoy.</p>
                        </div>
                        <div class="col-md-4">
                            <h6>Hybrid Approach</h6>
                            <p>Combines both methods to provide the most personalized recommendations, balancing your preferences with popular choices.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once "includes/footer.php";
?> 