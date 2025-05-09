<?php
require_once "includes/functions.php";

redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("location: interface.php");
    exit;
}

$enquiry_id = isset($_GET['id']) ? (int)sanitize($_GET['id']) : null;
$enquiry = null;
$error_msg = "";
$available_statuses = ['new', 'read', 'replied', 'closed'];

if (!$enquiry_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Invalid enquiry ID.'];
    header("location: admin_manage_enquiries.php");
    exit;
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $new_status = sanitize(trim($_POST['status']));
    if (in_array($new_status, $available_statuses)) {
        global $conn;
        $sql_update = "UPDATE enquiries SET status = ? WHERE id = ?";
        if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "si", $new_status, $enquiry_id);
            if (mysqli_stmt_execute($stmt_update)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Enquiry status updated.'];
            } else {
                $error_msg = "Error updating status.";
            }
            mysqli_stmt_close($stmt_update);
        } else {
            $error_msg = "Database error preparing status update.";
        }
    } else {
        $error_msg = "Invalid status selected.";
    }
}

// Fetch enquiry details
global $conn;
$sql = "SELECT e.*, u.phone_number as user_phone 
        FROM enquiries e 
        LEFT JOIN users u ON e.user_id = u.id 
        WHERE e.id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $enquiry_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $enquiry = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // If it's a 'new' enquiry and just viewed, update status to 'read'
    if ($enquiry && $enquiry['status'] == 'new' && $_SERVER["REQUEST_METHOD"] == "GET") {
        $sql_mark_read = "UPDATE enquiries SET status = 'read' WHERE id = ?";
        if ($stmt_mark_read = mysqli_prepare($conn, $sql_mark_read)) {
            mysqli_stmt_bind_param($stmt_mark_read, "i", $enquiry_id);
            mysqli_stmt_execute($stmt_mark_read);
            mysqli_stmt_close($stmt_mark_read);
            $enquiry['status'] = 'read'; // Reflect change immediately on page
        }
    }
} else {
    $error_msg = "Error fetching enquiry details.";
}

if (!$enquiry && empty($error_msg)) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Enquiry not found.'];
    header("location: admin_manage_enquiries.php");
    exit;
}

include 'includes/header.php';
?>
<div class="container">
    <div class="section-title fade-in">
        <h1>View Enquiry #<?php echo htmlspecialchars($enquiry_id); ?></h1>
    </div>

    <?php if (!empty($_SESSION['flash_message'])):
        $flash = $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> fade-in">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger fade-in"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <?php if ($enquiry): ?>
    <div class="card enquiry-details-card fade-in">
        <div class="card-header">
            Subject: <strong><?php echo htmlspecialchars($enquiry['subject']); ?></strong>
        </div>
        <div class="card-body">
            <p><strong>From:</strong> <?php echo htmlspecialchars($enquiry['name']); ?> (<a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>"><?php echo htmlspecialchars($enquiry['email']); ?></a>)</p>
            <?php if ($enquiry['phone']): ?>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($enquiry['phone']); ?></p>
            <?php endif; ?>
            <?php if ($enquiry['user_phone']): ?>
                <p><strong>Registered User Phone:</strong> <?php echo htmlspecialchars($enquiry['user_phone']); ?> (User ID: <?php echo $enquiry['user_id']; ?>)</p>
            <?php else: ?>
                 <p><strong>User Type:</strong> Guest</p>
            <?php endif; ?>            
            <p><strong>Received:</strong> <?php echo date("M d, Y, h:i A", strtotime($enquiry['created_at'])); ?></p>
            <p><strong>Current Status:</strong> <span class="badge badge-info"><?php echo ucfirst(htmlspecialchars($enquiry['status'])); ?></span></p>
            <hr>
            <h5>Message:</h5>
            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($enquiry['message'])); ?>
            </div>
        </div>
        <div class="card-footer">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $enquiry_id; ?>" method="post" class="form-inline">
                <input type="hidden" name="update_status" value="1">
                <div class="form-group mr-2">
                    <label for="status" class="mr-2">Change Status:</label>
                    <select name="status" id="status" class="form-control form-control-sm">
                        <?php foreach ($available_statuses as $status_option): ?>
                            <option value="<?php echo $status_option; ?>" <?php echo ($status_option == $enquiry['status']) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($status_option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
            </form>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning">Could not load enquiry details.</div>
    <?php endif; ?>

    <div style="margin-top: 20px;">
        <a href="admin_manage_enquiries.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Enquiries List</a>
    </div>
</div>
<style>
.enquiry-details-card .card-body p { margin-bottom: 0.5rem; }
.message-content { background-color: #f8f9fa; border: 1px solid #eee; padding: 15px; border-radius: 5px; white-space: pre-wrap; }
</style>
<?php include 'includes/footer.php'; ?> 