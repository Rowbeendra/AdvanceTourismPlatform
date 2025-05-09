<?php
require_once "includes/functions.php";

// Initialize variables
$name = $email = $phone = $subject = $message = "";
$name_err = $email_err = $subject_err = $message_err = "";
$success_msg = $error_msg = "";

$user_id_for_enquiry = null;

if (isLoggedIn()) {
    $user_id_for_enquiry = $_SESSION['id'];
    // Pre-fill name and email if user is logged in and we have that info
    // Assuming user table has name and email. If not, adjust or remove pre-fill.
    // For this project, users table only has phone_number, so we can't prefill email/name directly from there.
    // We will let them fill it manually, or you can add these fields to your users table.
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize(trim($_POST["name"]));
    $email = sanitize(trim($_POST["email"]));
    $phone = sanitize(trim($_POST["phone"]));
    $subject = sanitize(trim($_POST["subject"]));
    $message = sanitize(trim($_POST["message"]));

    // Validation
    if (empty($name)) {
        $name_err = "Please enter your name.";
    }
    if (empty($email)) {
        $email_err = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    }
    if (empty($subject)) {
        $subject_err = "Please enter a subject.";
    }
    if (empty($message)) {
        $message_err = "Please enter your message.";
    }

    if (empty($name_err) && empty($email_err) && empty($subject_err) && empty($message_err)) {
        global $conn;
        $sql = "INSERT INTO enquiries (name, email, phone, subject, message, user_id, status) VALUES (?, ?, ?, ?, ?, ?, 'new')";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $phone, $subject, $message, $user_id_for_enquiry);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Your enquiry has been sent successfully! We will get back to you shortly.";
                // Clear form fields after successful submission
                $name = $email = $phone = $subject = $message = ""; 
            } else {
                $error_msg = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Database error: Could not prepare statement.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="section-title fade-in">
        <h1>Contact Us / Make an Enquiry</h1>
        <p>Have questions or need assistance? Fill out the form below, and we'll get back to you as soon as possible.</p>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success fade-in"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger fade-in"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="form-container contact-form fade-in" novalidate>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" class="form-control <?php echo !empty($name_err) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>" required>
                <span class="form-text text-danger"><?php echo $name_err; ?></span>
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control <?php echo !empty($email_err) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required>
                <span class="form-text text-danger"><?php echo $email_err; ?></span>
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number (Optional)</label>
            <input type="tel" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
        </div>

        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" name="subject" id="subject" class="form-control <?php echo !empty($subject_err) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($subject); ?>" required>
            <span class="form-text text-danger"><?php echo $subject_err; ?></span>
        </div>

        <div class="form-group">
            <label for="message">Message</label>
            <textarea name="message" id="message" class="form-control <?php echo !empty($message_err) ? 'is-invalid' : ''; ?>" rows="6" required><?php echo htmlspecialchars($message); ?></textarea>
            <span class="form-text text-danger"><?php echo $message_err; ?></span>
        </div>

        <button type="submit" class="btn btn-primary">Send Enquiry</button>
    </form>
</div>

<style>
.contact-form {
    max-width: 700px;
    margin: 30px auto;
    padding: 25px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
</style>

<?php include 'includes/footer.php'; ?> 