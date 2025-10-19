<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "GamExplorer_Users";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$Errors = [
    'Login' => $_SESSION['Login_Error'] ?? '',
    'Signup' => $_SESSION['Signup_Error'] ?? ''
];
$ActiveForm = $_SESSION['Active_Form'] ?? 'Login';
$showInterface = false;

function showError(string $error): string {
    return !empty($error) ? "<p class='Error-Message'>$error</p>" : '';
}

function isActiveForm(string $formName, string $activeForm): string {
    return $formName === $activeForm ? 'Active' : '';
}

if (isset($_POST['Signup'])) {
    $name = $_POST['name'];
    $username = $_POST['Username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkEmail = $conn->query("SELECT email FROM Registered_Users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['Signup_Error'] = 'Email is already registered!';
        $_SESSION['Active_Form'] = 'Signup';
    } else {
        $conn->query("INSERT INTO Registered_Users (Name, Username, Email, Password) VALUES ('$name', '$username', '$email', '$password')");
    }

    header("Location: GamExplorer_Interface.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM Registered_Users WHERE email = '$email'");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['Name'] = $user['Name'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['Email'] = $user['Email'];
            $showInterface = true;
        }
    }

    if (!$showInterface) {
        $_SESSION['Login_Error'] = 'Incorrect Email or Password!';
        $_SESSION['Active_Form'] = 'Login';
        header("Location: GamExplorer_Interface.php");
        exit();
    }
}
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

<?php if ($showInterface && isset($_SESSION['Username'])): ?>
    <header class="interface">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['Username']); ?>!</h2>
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
    
    <div class="container">
        <div class="form-box <?= isActiveForm('Login', $ActiveForm); ?>" id="Login-Form">
            <form action="GamExplorer_Interface.php" method="post">
                <h2>Log In</h2>
                <?= showError($Errors['Login']); ?>
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" name="login">Log In</button>
                <p>Don't have an account? <a href="#" onclick="showForm('Signup-Form')">Sign Up</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('Signup', $ActiveForm); ?>" id="Signup-Form">
            <form action="GamExplorer_Interface.php" method="post">
                <h2>Sign Up</h2>
                <?= showError($Errors['Signup']); ?>
                <input type="text" name="name" placeholder="Name" required />
                <input type="text" name="Username" placeholder="Username" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" name="Signup">Sign Up</button>
                <p>Already have an account? <a href="#" onclick="showForm('Login-Form')">Log In</a></p>
            </form>
        </div>
    </div>

<script src="GamExplorer_Functions.js"></script>
</body>
</html>