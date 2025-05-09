<?php
// Include functions file
require_once "includes/functions.php";

// Check if the user is logged in, if not redirect to login page
redirectIfNotLoggedIn();

// Get user ID from session
$user_id = $_SESSION["id"];

// Get source and destination parameters
$source = isset($_GET['source']) ? $_GET['source'] : '';
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';

// Check if both source and destination are provided
if (empty($source) || empty($destination)) {
    header("location: guide.php");
    exit;
}

// Get all available locations for the dropdown
$locations = getAllLocations();

// Simulate travel route data - in a real app, this would come from a routing algorithm or API
$travel_time = rand(1, 5) . ' hours ' . rand(10, 59) . ' minutes';
$distance = rand(20, 300) . ' km';
$route_options = [
    'Fastest Route',
    'Scenic Route',
    'Budget Route'
];

// Get hotels in the destination
$destination_hotels = getHotelsNearMe($destination, 3);

// Get tourist areas in the destination
$destination_areas = getTouristAreasNearMe($destination, 3);
?>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Trip Plan: <?php echo $source; ?> to <?php echo $destination; ?></h1>
    </div>
    
    <div class="trip-planner fade-in">
        <div class="card route-card">
            <h3>Travel Route</h3>
            
            <div class="route-details">
                <div class="route-map" style="position: relative; /* Needed for potential overlays */">
                    <!-- Leaflet Map Container -->
                    <div id="leaflet-map" style="height: 400px; width: 100%; border-radius: 8px; margin-bottom: 15px;"></div>

                    <!-- Hidden elements to store source/destination for JS -->
                    <span id="source-loc" style="display: none;"><?php echo htmlspecialchars($source); ?></span>
                    <span id="destination-loc" style="display: none;"><?php echo htmlspecialchars($destination); ?></span>
                </div>
                
                <div class="route-info">
                    <div class="route-stats">
                        <div class="stat-item">
                            <i class="fas fa-clock"></i>
                            <div class="stat-details">
                                <span class="stat-label">Est. Travel Time</span>
                                <!-- JS will update this -->
                                <span class="stat-value" id="travel-time">Calculating...</span>
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <i class="fas fa-road"></i>
                            <div class="stat-details">
                                <span class="stat-label">Distance</span>
                                <!-- JS will update this -->
                                <span class="stat-value" id="travel-distance">Calculating...</span>
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <i class="fas fa-gas-pump"></i>
                            <div class="stat-details">
                                <span class="stat-label">Est. Fuel Cost</span>
                                <!-- JS will update this based on distance -->
                                <span class="stat-value" id="fuel-cost">Calculating...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="route-options">
                        <h4>Route Options</h4>
                        <div class="options-list">
                            <?php foreach($route_options as $index => $option): ?>
                            <div class="option-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <input type="radio" name="route_option" id="route<?php echo $index; ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                <label for="route<?php echo $index; ?>"><?php echo $option; ?></label>
                                <div class="option-details">
                                    <?php if($index === 0): ?>
                                    <span><i class="fas fa-clock"></i> Fastest travel time</span>
                                    <?php elseif($index === 1): ?>
                                    <span><i class="fas fa-mountain"></i> Beautiful views along the way</span>
                                    <?php else: ?>
                                    <span><i class="fas fa-coins"></i> Lowest cost option</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>Recommended Stays in <?php echo $destination; ?></h3>
            
            <?php if (empty($destination_hotels)): ?>
            <div class="alert alert-info">No hotels found in <?php echo $destination; ?>.</div>
            <?php else: ?>
            <div class="entity-grid small">
                <?php foreach($destination_hotels as $hotel): ?>
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
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3>Places to Visit in <?php echo $destination; ?></h3>
            
            <?php if (empty($destination_areas)): ?>
            <div class="alert alert-info">No tourist areas found in <?php echo $destination; ?>.</div>
            <?php else: ?>
            <div class="entity-grid small">
                <?php foreach($destination_areas as $area): ?>
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
            </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3>Trip Itinerary</h3>
            <div class="itinerary">
                <div class="day-item">
                    <div class="day-header">
                        <h4>Day 1</h4>
                        <span class="day-date">Arrival Day</span>
                    </div>
                    <div class="day-activities">
                        <div class="activity">
                            <div class="activity-time">9:00 AM</div>
                            <div class="activity-details">
                                <h5>Departure from <?php echo $source; ?></h5>
                                <p>Start your journey to <?php echo $destination; ?>.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">12:00 PM</div>
                            <div class="activity-details">
                                <h5>Lunch Break</h5>
                                <p>Stop for a delicious meal at a local restaurant.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">3:00 PM</div>
                            <div class="activity-details">
                                <h5>Arrival in <?php echo $destination; ?></h5>
                                <p>Check-in to your hotel and rest.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">6:00 PM</div>
                            <div class="activity-details">
                                <h5>Evening Exploration</h5>
                                <p>Take a leisurely walk around your hotel area to get familiar with the surroundings.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="day-item">
                    <div class="day-header">
                        <h4>Day 2</h4>
                        <span class="day-date">Exploration Day</span>
                    </div>
                    <div class="day-activities">
                        <div class="activity">
                            <div class="activity-time">8:00 AM</div>
                            <div class="activity-details">
                                <h5>Breakfast</h5>
                                <p>Enjoy a hearty breakfast at your hotel.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">10:00 AM</div>
                            <div class="activity-details">
                                <h5>Local Sightseeing</h5>
                                <p>Visit the top tourist attractions in <?php echo $destination; ?>.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">2:00 PM</div>
                            <div class="activity-details">
                                <h5>Local Experience</h5>
                                <p>Participate in local activities or cultural experiences.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">7:00 PM</div>
                            <div class="activity-details">
                                <h5>Dinner</h5>
                                <p>Try local cuisine at a recommended restaurant.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="day-item">
                    <div class="day-header">
                        <h4>Day 3</h4>
                        <span class="day-date">Departure Day</span>
                    </div>
                    <div class="day-activities">
                        <div class="activity">
                            <div class="activity-time">8:00 AM</div>
                            <div class="activity-details">
                                <h5>Breakfast</h5>
                                <p>Have your final breakfast in <?php echo $destination; ?>.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">10:00 AM</div>
                            <div class="activity-details">
                                <h5>Check-out</h5>
                                <p>Check-out from your hotel.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">11:00 AM</div>
                            <div class="activity-details">
                                <h5>Last-minute Shopping</h5>
                                <p>Buy souvenirs and local products.</p>
                            </div>
                        </div>
                        <div class="activity">
                            <div class="activity-time">2:00 PM</div>
                            <div class="activity-details">
                                <h5>Return to <?php echo $source; ?></h5>
                                <p>Begin your journey back home.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>Weather Forecast for <?php echo $destination; ?></h3>
            <div class="weather-forecast">
                <div class="weather-day">
                    <div class="weather-date">Today</div>
                    <div class="weather-icon"><i class="fas fa-sun"></i></div>
                    <div class="weather-temp">28°C</div>
                    <div class="weather-desc">Sunny</div>
                </div>
                
                <div class="weather-day">
                    <div class="weather-date">Tomorrow</div>
                    <div class="weather-icon"><i class="fas fa-cloud-sun"></i></div>
                    <div class="weather-temp">26°C</div>
                    <div class="weather-desc">Partly Cloudy</div>
                </div>
                
                <div class="weather-day">
                    <div class="weather-date">Day 3</div>
                    <div class="weather-icon"><i class="fas fa-cloud-showers-heavy"></i></div>
                    <div class="weather-temp">23°C</div>
                    <div class="weather-desc">Rainy</div>
                </div>
                
                <div class="weather-day">
                    <div class="weather-date">Day 4</div>
                    <div class="weather-icon"><i class="fas fa-cloud-sun"></i></div>
                    <div class="weather-temp">25°C</div>
                    <div class="weather-desc">Partly Cloudy</div>
                </div>
                
                <div class="weather-day">
                    <div class="weather-date">Day 5</div>
                    <div class="weather-icon"><i class="fas fa-sun"></i></div>
                    <div class="weather-temp">27°C</div>
                    <div class="weather-desc">Sunny</div>
                </div>
            </div>
        </div>
        
        <div class="trip-actions">
            <a href="#" class="btn btn-primary" onclick="window.print();return false;">
                <i class="fas fa-print"></i> Print Itinerary
            </a>
            <a href="guide.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Guide
            </a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?> 