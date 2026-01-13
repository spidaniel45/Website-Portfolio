<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
</head>
<body>
    <form action="home.php" method="POST">
        First Name<input type="text" name="first_name" placeholder="First Name" required>
        <br>
        Last Name<input type="text" name="last_name" placeholder="Last Name" required>
        <br>
        Email<input type="email" name="email" placeholder="Email" required>
        <br>
        Age<input type="number" name="age" placeholder="Age" required>
        <br>
        Gender:
        Male<input type="radio" name="gender" value="Male" required>
        Female<input type="radio" name="gender" value="Female" required>
        <br>
        <input type="submit" name="submit">
    </form>
</body>
</html>