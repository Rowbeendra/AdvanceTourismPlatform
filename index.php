<?php
// Include functions file
require_once "includes/functions.php";

// Check if user is already logged in, redirect to interface
if (isLoggedIn()) {
    header("location: interface.php");
    exit;
}
?>

<?php include "includes/header.php"; ?>

<section class="hero">
    <div class="container">
        <h1 class="fade-in">Discover Amazing Destinations</h1>
        <p class="fade-in delay-1">Find the best hotels, tourist attractions, and plan your perfect trip with our Advanced Tourism Platform.</p>
        <div class="hero-buttons fade-in delay-2">
            <a href="login.php" class="btn btn-primary">Log In</a>
            <a href="register.php" class="btn btn-secondary">Sign Up</a>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Our Features</h2>
        </div>
        
        <div class="features-grid fade-in">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <h3>Find the Best Hotels</h3>
                <p>Discover top-rated hotels for your stay, filter by location, and find the perfect accommodation for your trip.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Explore Tourist Areas</h3>
                <p>Browse popular tourist destinations, find hidden gems, and see what others are visiting the most.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-route"></i>
                </div>
                <h3>Plan Your Trip</h3>
                <p>Set your source and destination, get travel routes, and plan your perfect itinerary with our trip planner.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Rate & Review</h3>
                <p>Share your experiences by rating hotels and tourist areas to help other travelers make informed decisions.</p>
            </div>
        </div>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <div class="section-title fade-in">
            <h2>How It Works</h2>
        </div>
        
        <div class="steps fade-in">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Create an Account</h3>
                <p>Sign up with your phone number to access all features.</p>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <h3>Explore Options</h3>
                <p>Browse hotels, tourist areas, and travel guides.</p>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <h3>Plan Your Trip</h3>
                <p>Select your source and destination to get a customized trip plan.</p>
            </div>
            
            <div class="step">
                <div class="step-number">4</div>
                <h3>Enjoy Your Journey</h3>
                <p>Visit destinations, rate your experiences, and share with others.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="container">
        <div class="cta-content fade-in">
            <h2>Ready to Start Your Adventure?</h2>
            <p>Join thousands of travelers who are discovering amazing destinations every day.</p>
            <a href="register.php" class="btn btn-primary">Sign Up Now</a>
        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?> 