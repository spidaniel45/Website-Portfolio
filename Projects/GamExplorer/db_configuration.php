<?php
/**
 * Database Configuration
 * Centralized database connection with security settings
 */

// Database credentials - MOVE THESE TO ENVIRONMENT VARIABLES IN PRODUCTION
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'GamExplorer_Users');

/**
 * Get database connection
 * @return mysqli
 * @throws Exception if connection fails
 */
function getDBConnection(): mysqli {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            throw new Exception("Database connection error. Please try again later.");
        }
        
        // Set charset to prevent SQL injection
        if (!$conn->set_charset("utf8mb4")) {
            error_log("Error setting charset: " . $conn->error);
        }
    }
    
    return $conn;
}

/**
 * Close database connection
 */
function closeDBConnection(): void {
    global $conn;
    if ($conn !== null) {
        $conn->close();
        $conn = null;
    }
}

/**
 * Execute prepared statement safely
 * @param mysqli $conn Database connection
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types (e.g., "ssi" for string, string, int)
 * @param array $params Parameters to bind
 * @return mysqli_result|bool
 */
function executeQuery(mysqli $conn, string $query, string $types, array $params) {
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}

/**
 * Authentication Middleware
 * Include this file at the top of pages that require authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1); // Enable if using HTTPS
    session_start();
}

/**
 * Check if user is authenticated
 * @return bool
 */
function isAuthenticated(): bool {
    return isset($_SESSION['user_id']) && isset($_SESSION['Username']);
}

/**
 * Check if user is an admin
 * @return bool
 */
function isAdmin(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 */
function requireAuth(): void {
    if (!isAuthenticated()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: GamExplorer_Interface.php");
        exit();
    }
}

/**
 * Require user to be an admin
 * Redirects to user interface if not admin
 */
function requireAdmin(): void {
    requireAuth(); // First check if logged in
    
    if (!isAdmin()) {
        // Log unauthorized access attempt
        error_log("Unauthorized admin access attempt by user ID: " . ($_SESSION['user_id'] ?? 'unknown'));
        
        $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
        header("Location: GamExplorer_Interface.php");
        exit();
    }
}

/**
 * Check session timeout (30 minutes of inactivity)
 */
function checkSessionTimeout(): void {
    $sessionTimeout = 1800; // 30 minutes in seconds
    
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
            session_unset();
            session_destroy();
            header("Location: GamExplorer_LoginSignup.php?timeout=1");
            exit();
        }
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool
 */
function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate session ID to prevent session fixation
 */
function regenerateSession(): void {
    session_regenerate_id(true);
    // Generate new CSRF token after session regeneration
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Auto-check session timeout if user is authenticated
if (isAuthenticated()) {
    checkSessionTimeout();
}

?>