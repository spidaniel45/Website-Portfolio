<?php
/**
 * GamExplorer User Interface
 * Handles user authentication, profile display, and navigation
 * 
 * CLEANED VERSION - Uses centralized db_configuration.php
 */

// ============================================================================
// INITIALIZATION & CONFIGURATION
// ============================================================================

// Include database and authentication configuration
require_once 'db_configuration.php';

// Require user authentication
requireAuth();

// Generate CSRF token for forms
$csrfToken = generateCSRFToken();

// ============================================================================
// LOGOUT HANDLER (Must be before any output)
// ============================================================================

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Log the logout
    if (isset($_SESSION['user_id']) && isset($_SESSION['Username'])) {
        error_log("User logout: " . $_SESSION['Username'] . " (ID: " . $_SESSION['user_id'] . ")");
    }

    // Unset all session variables
    $_SESSION = array();

    // Delete the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(
            session_name(), 
            '', 
            time() - 3600, 
            '/',
            '',
            isset($_SERVER["HTTPS"]), 
            true
        );
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: GamExplorer_LoginSignup.php?logout=success");
    exit();
}

// ============================================================================
// LOGIN HANDLER (Process before any output)
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['Login_Error'] = 'Invalid security token. Please try again.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Validate and sanitize input
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email) {
        $_SESSION['Login_Error'] = 'Invalid email format!';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    try {
        $conn = getDBConnection();
        
        // Check if account is locked
        $stmt = $conn->prepare("SELECT locked_until FROM Registered_Users WHERE Email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $lockData = $result->fetch_assoc()) {
            if ($lockData['locked_until'] && strtotime($lockData['locked_until']) > time()) {
                $stmt->close();
                $_SESSION['Login_Error'] = 'Account temporarily locked. Please try again later.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
        $stmt->close();
        
        // Get user with role from database
        $stmt = $conn->prepare("
            SELECT ID, Username, Name, Email, Password, role, account_status, failed_login_attempts 
            FROM Registered_Users 
            WHERE Email = ? 
            LIMIT 1
        ");
        
        if (!$stmt) {
            error_log("Database prepare failed: " . $conn->error);
            $_SESSION['Login_Error'] = 'System error. Please try again.';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {
            
            // Check if account is active
            if ($user['account_status'] !== 'active') {
                $stmt->close();
                $_SESSION['Login_Error'] = 'Account is not active. Contact administrator.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            
            // Verify password
            if (password_verify($password, $user['Password'])) {
                
                // Reset failed login attempts and update last login
                $resetStmt = $conn->prepare("
                    UPDATE Registered_Users 
                    SET failed_login_attempts = 0, 
                        locked_until = NULL, 
                        last_login = NOW() 
                    WHERE ID = ?
                ");
                $resetStmt->bind_param("i", $user['ID']);
                $resetStmt->execute();
                $resetStmt->close();
                
                // Regenerate session ID to prevent session fixation (also generates new CSRF token)
                regenerateSession();

                // Set session variables
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['Username'] = $user['Username'];
                $_SESSION['Name'] = $user['Name'];
                $_SESSION['Email'] = $user['Email'];
                $_SESSION['is_admin'] = ($user['role'] === 'admin');
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();

                // Log successful login
                error_log("Successful login for user: " . $user['Username'] . " (ID: " . $user['ID'] . ")");

                $stmt->close();
                
                // Redirect based on role
                $redirectPage = ($user['role'] === 'admin') 
                    ? 'GamExplorer_Admin_Interface.php' 
                    : 'GamExplorer_Interface.php';
                
                // Check if there's a saved redirect URL
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirectPage = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                }
                
                header("Location: $redirectPage");
                exit();
                
            } else {
                // Increment failed login attempts
                $failedAttempts = $user['failed_login_attempts'] + 1;
                $lockUntil = null;
                
                // Lock account after 5 failed attempts for 15 minutes
                if ($failedAttempts >= 5) {
                    $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    error_log("Account locked for user: " . $user['Email']);
                }
                
                $updateStmt = $conn->prepare("
                    UPDATE Registered_Users 
                    SET failed_login_attempts = ?, 
                        locked_until = ? 
                    WHERE ID = ?
                ");
                $updateStmt->bind_param("isi", $failedAttempts, $lockUntil, $user['ID']);
                $updateStmt->execute();
                $updateStmt->close();
            }
        }

        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['Login_Error'] = 'System error. Please try again.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Generic error message to prevent user enumeration
    $_SESSION['Login_Error'] = 'Incorrect Email or Password!';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ============================================================================
// SESSION VALIDATION (Check if user is logged in)
// ============================================================================

// Require authentication - redirects if not logged in
requireAuth();

// Session timeout is automatically checked by db_configuration.php

// ============================================================================
// GET USER PROFILE DATA
// ============================================================================

$conn = getDBConnection();

// Get user's profile image
$imagePath = "assets/default_profile.png";
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT Profile_Image FROM Registered_Users WHERE ID = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        if (!empty($row['Profile_Image'])) {
            // Sanitize filename to prevent directory traversal
            $cleanFilename = basename($row['Profile_Image']);
            $imagePath = "uploads/" . htmlspecialchars($cleanFilename, ENT_QUOTES, 'UTF-8');
        }
    }
    $stmt->close();
}

// Initialize error messages for display
$errors = [
    'login' => $_SESSION['Login_Error'] ?? '',
    'signup' => $_SESSION['Signup_Error'] ?? ''
];

// Clear error messages after reading
unset($_SESSION['Login_Error'], $_SESSION['Signup_Error']);

// ============================================================================
// HTML OUTPUT STARTS HERE
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';">
    <title>GamExplorer - Dashboard</title>
    <link rel="stylesheet" href="GamExplorer_Design.css">
</head>
<body>

    <!-- Profile Container -->
    <div class="profile-container">
        <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" 
             alt="Profile Picture" 
             class="profile-image"
             onerror="this.src='assets/default_profile.png'">
        <span class="welcome-text">
            Welcome, <?= htmlspecialchars($_SESSION['Username'], ENT_QUOTES, 'UTF-8') ?>!
        </span>
    </div>

    <!-- Upload Profile Picture Form -->
    <div class="form-box Active">
        <form action="Upload_Image_Profile.php" method="POST" enctype="multipart/form-data">
            <h2>Upload Profile Picture</h2>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="file" 
                   name="userImage" 
                   id="userImage" 
                   accept="image/jpeg,image/png,image/jpg,image/gif" required>
            <button type="submit">Upload</button>
        </form>
    </div>

    <!-- Navigation Header -->
    <header class="interface">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['Username'], ENT_QUOTES, 'UTF-8') ?>!</h2>
        <nav>
            <ul class="nav-menu">
                <li><a href="#Profile">Profile</a></li>
                <li><a href="#Home">Home</a></li>
                <li><a href="#About">About</a></li>
                <li><a href="#ProductsServices">Products/Services</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="GamExplorer_Admin_Interface.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="?action=logout">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Content Sections -->
    <section id="Profile" class="Design">
        <h2>Profile</h2>
        <div class="profile-info">
            <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION['Username'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($_SESSION['Name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['Email'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (isAdmin()): ?>
                <p><strong>Role:</strong> <span class="badge-admin">Administrator</span></p>
            <?php endif; ?>
        </div>
    </section>

    <section id="Home" class="Design">
        <h2>Home</h2>
        <p>Welcome to your dashboard!</p>
    </section>

    <section id="About" class="Design">
        <h2>About</h2>
        <p>Learn more about GamExplorer.</p>
    </section>

    <section id="ProductsServices" class="Design">
        <h2>Products & Services</h2>
        <p>Explore our offerings.</p>
    </section>

    <script src="GamExplorer_Functions.js"></script>
</body>
</html>