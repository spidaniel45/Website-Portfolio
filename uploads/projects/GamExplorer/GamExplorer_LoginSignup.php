<?php
/**
 * GamExplorer Login/Signup System - SECURE VERSION
 * Demonstrates proper security practices for user authentication
 * 
 * KEY SECURITY PRINCIPLES APPLIED:
 * 1. Prepared Statements - Prevents SQL Injection
 * 2. Input Validation - Ensures data quality
 * 3. Output Escaping - Prevents XSS attacks
 * 4. Password Hashing - Protects user passwords
 * 5. Error Handling - Prevents information leakage
 */

// Start session to store user data across pages
session_start();

// ============================================
// DATABASE CONNECTION SETUP
// ============================================
$host = "localhost";
$user = "root";
$password = "";
$database = "GamExplorer_Users";

// Create connection using mysqli (supports prepared statements)
$conn = new mysqli($host, $user, $password, $database);

// Check connection and handle errors gracefully
if ($conn->connect_error) {
    // In production, log this error and show generic message to user
    error_log("Database connection failed: " . $conn->connect_error);
    die("We're experiencing technical difficulties. Please try again later.");
}

// Set charset to UTF-8 to prevent character encoding attacks
$conn->set_charset("utf8mb4");

// ============================================
// INITIALIZE VARIABLES
// ============================================
$Errors = [
    'Login' => $_SESSION['Login_Error'] ?? '',
    'Signup' => $_SESSION['Signup_Error'] ?? ''
];
$ActiveForm = $_SESSION['Active_Form'] ?? 'Login';
$showInterface = false;

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Display error message safely (prevents XSS)
 * XSS PREVENTION: Use htmlspecialchars() on any user-generated content
 * 
 * @param string $error - The error message to display
 * @return string - HTML paragraph with escaped error or empty string
 */
function showError(string $error): string {
    // htmlspecialchars() converts special characters to HTML entities
    // This prevents malicious scripts from executing
    return !empty($error) ? "<p class='Error-Message'>" . htmlspecialchars($error) . "</p>" : '';
}

/**
 * Determine if a form should be marked as active
 * 
 * @param string $formName - Name of the form to check
 * @param string $activeForm - Currently active form name
 * @return string - 'Active' CSS class or empty string
 */
function isActiveForm(string $formName, string $activeForm): string {
    return $formName === $activeForm ? 'Active' : '';
}

/**
 * Validate email format
 * INPUT VALIDATION: Always validate data before processing
 * 
 * @param string $email - Email to validate
 * @return bool - True if valid email format
 */
function isValidEmail(string $email): bool {
    // filter_var with FILTER_VALIDATE_EMAIL checks proper email format
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * SECURITY: Enforce strong passwords to protect user accounts
 * 
 * @param string $password - Password to validate
 * @return array - ['valid' => bool, 'message' => string]
 */
function validatePassword(string $password): array {
    // Check minimum length
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
    }
    
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one number'];
    }
    
    // Check for at least one letter
    if (!preg_match('/[a-zA-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one letter'];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Sanitize text input
 * INPUT SANITIZATION: Remove potentially harmful characters
 * 
 * @param string $input - Input to sanitize
 * @return string - Sanitized input
 */
function sanitizeInput(string $input): string {
    // trim() removes whitespace from beginning and end
    // strip_tags() removes HTML and PHP tags
    return trim(strip_tags($input));
}

// ============================================
// HANDLE SIGNUP FORM SUBMISSION
// ============================================
if (isset($_POST['Signup'])) {
    // STEP 1: COLLECT AND SANITIZE INPUT
    // Always sanitize user input to remove unwanted characters
    $name = sanitizeInput($_POST['name']);
    $username = sanitizeInput($_POST['Username']);
    $email = sanitizeInput($_POST['email']);
    $rawPassword = $_POST['password']; // Don't sanitize password (might remove valid chars)
    
    // STEP 2: VALIDATE INPUT
    // Check if any required fields are empty after sanitization
    if (empty($name) || empty($username) || empty($email) || empty($rawPassword)) {
        $_SESSION['Signup_Error'] = 'All fields are required';
        $_SESSION['Active_Form'] = 'Signup';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Validate email format
    if (!isValidEmail($email)) {
        $_SESSION['Signup_Error'] = 'Please enter a valid email address';
        $_SESSION['Active_Form'] = 'Signup';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Validate password strength
    $passwordCheck = validatePassword($rawPassword);
    if (!$passwordCheck['valid']) {
        $_SESSION['Signup_Error'] = $passwordCheck['message'];
        $_SESSION['Active_Form'] = 'Signup';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Validate username length (3-20 characters)
    if (strlen($username) < 3 || strlen($username) > 20) {
        $_SESSION['Signup_Error'] = 'Username must be between 3 and 20 characters';
        $_SESSION['Active_Form'] = 'Signup';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // STEP 3: CHECK IF EMAIL ALREADY EXISTS
    // SQL INJECTION PREVENTION: Use prepared statements with parameter binding
    // The ? is a placeholder that will be safely replaced with the actual value
    $stmt = $conn->prepare("SELECT email FROM Registered_Users WHERE email = ?");
    
    if (!$stmt) {
        // If prepare fails, log error and show generic message
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['Signup_Error'] = 'System error. Please try again later.';
        $_SESSION['Active_Form'] = 'Signup';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // bind_param() safely binds the email value to the ? placeholder
    // "s" means the parameter is a string
    // Other types: "i" = integer, "d" = double, "b" = blob
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Email already exists
        $_SESSION['Signup_Error'] = 'Email is already registered!';
        $_SESSION['Active_Form'] = 'Signup';
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $stmt->close();
    
    // STEP 4: CHECK IF USERNAME ALREADY EXISTS
    // Always validate unique fields separately to give specific error messages
    $stmt = $conn->prepare("SELECT Username FROM Registered_Users WHERE Username = ?");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['Signup_Error'] = 'System error. Please try again later.';
        $_SESSION['Active_Form'] = 'Signup';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['Signup_Error'] = 'Username is already taken!';
        $_SESSION['Active_Form'] = 'Signup';
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $stmt->close();
    
    // STEP 5: HASH PASSWORD
    // PASSWORD SECURITY: Never store plain text passwords!
    // PASSWORD_DEFAULT uses bcrypt algorithm (currently the best option)
    // The hash includes a random salt automatically
    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
    
    // STEP 6: INSERT NEW USER
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO Registered_Users (Name, Username, Email, Password) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['Signup_Error'] = 'System error. Please try again later.';
        $_SESSION['Active_Form'] = 'Signup';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Bind all 4 parameters (all are strings, hence "ssss")
    $stmt->bind_param("ssss", $name, $username, $email, $hashedPassword);
    
    // Execute and check if successful
    if ($stmt->execute()) {
        // Success! Set session variables and log user in
        $_SESSION['Name'] = $name;
        $_SESSION['Username'] = $username;
        $_SESSION['Email'] = $email;
        $_SESSION['Signup_Success'] = 'Account created successfully!';
        $stmt->close();
        
        // Redirect to main interface
        header("Location: GamExplorer_Interface.php");
        exit();
    } else {
        // Insert failed
        error_log("Insert failed: " . $stmt->error);
        $_SESSION['Signup_Error'] = 'Failed to create account. Please try again.';
        $_SESSION['Active_Form'] = 'Signup';
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ============================================
// HANDLE LOGIN FORM SUBMISSION
// ============================================
if (isset($_POST['login'])) {
    // STEP 1: COLLECT AND SANITIZE INPUT
    $email = sanitizeInput($_POST['email']);
    $rawPassword = $_POST['password'];
    
    // STEP 2: VALIDATE INPUT
    if (empty($email) || empty($rawPassword)) {
        $_SESSION['Login_Error'] = 'All fields are required';
        $_SESSION['Active_Form'] = 'Login';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if (!isValidEmail($email)) {
        $_SESSION['Login_Error'] = 'Please enter a valid email address';
        $_SESSION['Active_Form'] = 'Login';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // STEP 3: FETCH USER FROM DATABASE
    // SQL INJECTION PREVENTION: Use prepared statement
    $stmt = $conn->prepare("SELECT Name, Username, Email, Password FROM Registered_Users WHERE Email = ?");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['Login_Error'] = 'System error. Please try again later.';
        $_SESSION['Active_Form'] = 'Login';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // STEP 4: VERIFY USER EXISTS AND PASSWORD IS CORRECT
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // PASSWORD VERIFICATION: Use password_verify() to check hashed password
        // This function safely compares the entered password with the stored hash
        // It handles the salt automatically
        if (password_verify($rawPassword, $user['Password'])) {
            // Login successful!
            
            // SESSION SECURITY: Store only necessary user data
            // Never store sensitive data like passwords in session
            $_SESSION['Name'] = $user['Name'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['Email'] = $user['Email'];
            
            // OPTIONAL: Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);
            
            $showInterface = true;
            $stmt->close();
            
            // Clear any previous error messages
            unset($_SESSION['Login_Error']);
            
            // Redirect to main interface
            header("Location: GamExplorer_Interface.php");
            exit();
        }
    }
    
    $stmt->close();
    
    // SECURITY: Use generic error message
    // Don't reveal whether email exists or password is wrong
    // This prevents user enumeration attacks
    $_SESSION['Login_Error'] = 'Incorrect email or password';
    $_SESSION['Active_Form'] = 'Login';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ============================================
// CLEAR ERROR MESSAGES
// ============================================
// After displaying errors, clear them so they don't persist
// This is done at the end so errors can be displayed first
$displayLoginError = $Errors['Login'];
$displaySignupError = $Errors['Signup'];
unset($_SESSION['Login_Error']);
unset($_SESSION['Signup_Error']);
unset($_SESSION['Active_Form']);

// Close database connection when done
// Although PHP closes it automatically, it's good practice
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GamExplorer Login/Signup</title>
    <link rel="stylesheet" href="GamExplorer_Design.css" />
</head>
<body>

<?php 
/**
 * MAIN INTERFACE - Only shown after successful login
 * XSS PREVENTION: Always use htmlspecialchars() when outputting user data
 */
if ($showInterface && isset($_SESSION['Username'])): 
?>
    <header class="interface">
        <!-- XSS PROTECTION: htmlspecialchars() prevents malicious scripts -->
        <!-- ENT_QUOTES ensures both single and double quotes are encoded -->
        <!-- UTF-8 ensures proper character encoding -->
        <h2>Welcome, <?= htmlspecialchars($_SESSION['Username'], ENT_QUOTES, 'UTF-8'); ?>!</h2>
        <nav>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="products.php">Products/Services</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
<?php endif; ?>
    
    <!-- ============================================ -->
    <!-- LOGIN AND SIGNUP FORMS -->
    <!-- ============================================ -->
    <div class="container">
        <!-- LOGIN FORM -->
        <div class="form-box <?= isActiveForm('Login', $ActiveForm); ?>" id="Login-Form">
            <!-- CSRF PROTECTION: Use $_SERVER['PHP_SELF'] or absolute path -->
            <!-- Avoid using external URLs in form actions -->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <h2>Log In</h2>
                
                <!-- Display login errors (already sanitized in showError function) -->
                <?= showError($displayLoginError); ?>
                
                <!-- FORM SECURITY: Use proper input types for validation -->
                <!-- type="email" provides built-in browser validation -->
                <input type="email" name="email" placeholder="Email" required />
                
                <!-- type="password" hides input and prevents autocomplete by default -->
                <input type="password" name="password" placeholder="Password" required />
                
                <button type="submit" name="login">Log In</button>
                
                <!-- JavaScript form switching (requires external JS file) -->
                <p>Don't have an account? <a href="#" onclick="showForm('Signup-Form'); return false;">Sign Up</a></p>
            </form>
        </div>

        <!-- SIGNUP FORM -->
        <div class="form-box <?= isActiveForm('Signup', $ActiveForm); ?>" id="Signup-Form">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <h2>Sign Up</h2>
                
                <!-- Display signup errors (already sanitized in showError function) -->
                <?= showError($displaySignupError); ?>
                
                <!-- INPUT VALIDATION: Use maxlength to limit input size -->
                <!-- This prevents excessively long inputs and potential DoS attacks -->
                <input type="text" name="name" placeholder="Name" required maxlength="50" />
                <input type="text" name="Username" placeholder="Username" required maxlength="20" minlength="3" />
                <input type="email" name="email" placeholder="Email" required maxlength="100" />
                
                <!-- PASSWORD: Use minlength for client-side validation -->
                <!-- Always validate again server-side (never trust client) -->
                <input type="password" name="password" placeholder="Password (min 8 chars)" required minlength="8" />
                
                <button type="submit" name="Signup">Sign Up</button>
                
                <p>Already have an account? <a href="#" onclick="showForm('Login-Form'); return false;">Log In</a></p>
            </form>
        </div>
    </div>

<!-- Include JavaScript file for form switching functionality -->
<script src="GamExplorer_Functions.js"></script>
</body>
</html>