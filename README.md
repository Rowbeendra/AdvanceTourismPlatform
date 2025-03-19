# Advanced Tourism Platform

A comprehensive web-based tourism platform that helps users explore hotels and tourist destinations, plan their trips, and rate their experiences.

## Features

- **User Authentication**: Secure login and registration system with phone number validation (must start with "98" and be 10 digits long)
- **Hotel Exploration**: Browse high-rated hotels or find hotels near specific locations
- **Tourist Area Discovery**: Explore the most visited tourist areas or find attractions near you
- **Travel Guide**: Get recommendations and travel tips for different trip types
- **Trip Planning**: Plan your journey by selecting source and destination locations
- **Interactive Maps**: View hotel and tourist area locations on maps
- **Rating System**: Rate hotels and tourist areas with a 5-star rating system
- **Visit Tracking**: Record visits to tourist areas and see popularity metrics
- **Responsive Design**: Fully responsive interface that works on all devices

## Screenshots

(Screenshots would be added here after deployment)

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

### Steps

1. **Clone the repository**
   ```
   git clone https://github.com/yourusername/advanced-tourism-platform.git
   cd advanced-tourism-platform
   ```

2. **Set up the database**
   - Create a MySQL database named `tourism_platform`
   - Import the database schema (optional, as the application creates tables automatically)

3. **Configure the database connection**
   - Open `includes/config.php`
   - Update the database credentials if needed:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'tourism_platform');
     ```

4. **Set up image directory**
   - Create an `images` directory in the root folder if it doesn't exist
   - Ensure the web server has write permissions to this directory

5. **Deploy to a web server**
   - Copy all files to your web server's document root
   - Ensure the web server has appropriate read/write permissions

## Usage

1. **Registration and Login**
   - Register with a phone number that starts with "98" and is exactly 10 digits long
   - Create a secure password
   - Log in with your credentials

2. **Exploring Hotels and Tourist Areas**
   - From the main interface, click on "Hotels" or "Tourist Areas"
   - Use filters to find high-rated hotels or most visited areas
   - Use the "Near Me" feature to find locations near a specific place

3. **Planning a Trip**
   - Go to "Travel Guide" section
   - Select your source and destination
   - View recommended routes, hotels, tourist areas, and a customized itinerary

4. **Rating and Visiting**
   - Visit hotel or tourist area details
   - Rate them using the 5-star rating system
   - For tourist areas, mark your visit to increase the visit count

## User Flow

The application follows this specific user journey:
1. Start → Login (Authentication required)
2. Interface (Main Hub)
3. Hotel Section (High Rated Hotels & Near Me options)
4. Tourist Area Section (Most Visited Area & Near Me options)
5. Guide → Source + Destination → Visit Destination → Star Rating
6. End

## Development

### Technologies Used
- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Libraries**: Font Awesome for icons

### File Structure
- `/` - Root directory containing the main PHP files
- `/css` - Stylesheets
- `/js` - JavaScript files
- `/includes` - PHP includes for functions, configuration, and common elements
- `/images` - Image assets and uploaded images

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

For any questions or feedback, please contact:
- Email: info@tourismplatform.com
- Website: www.tourismplatform.com 