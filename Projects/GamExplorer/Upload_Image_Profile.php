<?php
session_start();

// Database config
$host = "localhost";
$user = "root";
$password = "";
$database = "GamExplorer_Users";

// Validate session
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to upload a profile image.");
}
$userId = $_SESSION['user_id'];

// Validate file upload
if (!isset($_FILES["userImage"]) || $_FILES["userImage"]["error"] !== UPLOAD_ERR_OK) {
    die("No image uploaded or upload error.");
}

$uploadDir = "uploads/";
$filename = basename($_FILES["userImage"]["name"]);
$targetFile = $uploadDir . $filename;
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

// Validate image type
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($imageFileType, $allowedTypes)) {
    die("Only JPG, JPEG, PNG & GIF files are allowed.");
}

// Validate image content
if (!getimagesize($_FILES["userImage"]["tmp_name"])) {
    die("File is not a valid image.");
}

// Create uploads folder if missing
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Prevent overwriting
if (file_exists($targetFile)) {
    die("Sorry, file already exists.");
}

// Move file
if (!move_uploaded_file($_FILES["userImage"]["tmp_name"], $targetFile)) {
    die("Error moving uploaded file.");
}

// Save filename to database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("UPDATE Registered_Users SET Profile_Image = ? WHERE ID = ?");
$stmt->bind_param("si", $filename, $userId);
if ($stmt->execute()) {
    echo "Profile image uploaded and saved successfully!";
} else {
    echo "Database error: " . $stmt->error;
}

$conn->close();
?>