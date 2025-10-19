<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "studentprofile";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if (isset($_POST['Submit'])) {
    $FirstName = trim($_POST['FirstName'] ?? '');
    $MiddleName = trim($_POST['MiddleName'] ?? '');
    $LastName = trim($_POST['LastName'] ?? '');

    $stmt = $conn->prepare("INSERT INTO tblstudentprofile (FirstName, MiddleName, LastName) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('sss', $FirstName, $MiddleName, $LastName);
        if ($stmt->execute()) {
            echo "Record inserted successfully!";
        } else {
            echo "Execute failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Prepare failed: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
</head>
<body>
    <div class="Submitbox">
        <form method="post" action="">
            <input type="text" name="FirstName" placeholder="First Name" required>
            <input type="text" name="MiddleName" placeholder="Middle Name" required>
            <input type="text" name="LastName" placeholder="Last Name" required>
            <button type="submit" name="Submit">Submit your Record</button>
        </form>
    </div>
</body>
</html>