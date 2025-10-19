<?php
session_start();

// Database config
$conn = new mysqli("localhost", "root", "", "GamExplorer_Users");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Error handling
$Errors = [
    'Login' => $_SESSION['Login_Error'] ?? '',
    'Signup' => $_SESSION['Signup_Error'] ?? ''
];
$ActiveForm = $_SESSION['Active_Form'] ?? 'Login';
$showInterface = false;

// Utility functions
function showError(string $error): string {
    return !empty($error) ? "<p class='Error-Message'>$error</p>" : '';
}

function isActiveForm(string $formName, string $activeForm): string {
    return $formName === $activeForm ? 'Active' : '';
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Registered_Users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $user = $result->fetch_assoc()) {
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['Name'] = $user['Name'];
            $_SESSION['Email'] = $user['Email'];

           // Check if user is admin based on email domain
if (substr($email, -strlen('@gamexploreradmin.com')) === '@gamexploreradmin.com') {
    $_SESSION['is_admin'] = true;
    $isAdmin = (substr($email, -strlen('@gamexploreradmin.com')) === '@gamexploreradmin.com');
    header("Location: GamExplorer_Admin_Interface.php");
    exit();
} else {
    $_SESSION['is_admin'] = false;
    header("Location: GamExplorer_Interface.php");
    exit();
}
        }
    }

    $_SESSION['Login_Error'] = 'Incorrect Email or Password!';
    $_SESSION['Active_Form'] = 'Login';
    header("Location: GamExplorer_Interface.php");
    exit();
}

$imagePath = "assets/default_profile.png";
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT Profile_Image FROM Registered_Users WHERE ID = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        if (!empty($row['Profile_Image'])) {
            $imagePath = "uploads/" . htmlspecialchars($row['Profile_Image']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GamExplorer Interface</title>
  <link rel="stylesheet" href="GamExplorer_Design.css" />
</head>
<body>

<!-- Top-left Profile + Welcome -->
<?php if (isset($_SESSION['Username'])): ?>
<div class="profile-container">
  <img src="<?= $imagePath ?>" alt="Profile Picture" class="profile-image">
  <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['Username']); ?>!</span>
</div>
<?php endif; ?>

<!-- Upload Form -->
<div class="form-box Active">
  <form action="Upload_Image_Profile.php" method="POST" enctype="multipart/form-data">
    <h2>Upload Profile Picture</h2>
    <input type="file" name="userImage" id="userImage" accept="image/*" required>
    <button type="submit">Upload</button>
  </form>
</div>

<!-- Navigation -->
<?php if (isset($_SESSION['Username'])): ?>
<header class="interface">
  <h2>Welcome, <?= htmlspecialchars($_SESSION['Username']); ?>!</h2>
  <nav>
    <ul class="nav-menu">
      <li><a href="Profile.php">Profile</a></li>
      <li><a href="Home.php">Home</a></li>
      <li><a href="About.php">About</a></li>
      <li><a href="ProductsServices.php">Products/Services</a></li>
    </ul>
  </nav>
</header>
<?php endif; ?>

<script src="GamExplorer_Functions.js"></script>
</body>
</html>