<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home</title>
</head>
<body>

<?php

if (isset($_POST['submit'])) {
    $firstname = $_POST["first_name"];
    $lastname = $_POST["last_name"];
    $age = $_POST["age"];
    $gender = $_POST["gender"];

    echo "<h1>Welcome</h1>";
    echo "Your First Name is: " . $firstname;
    echo "<br>";
    echo "Your Last Name is: " . $lastname;
    echo "<br>";
    echo "Your Age is: " . $age;
    echo "<br>";
    echo "Your Gender is: " . $gender;
    
}

?>
</body>
</html>