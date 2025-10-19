<?php
$conn = new mysqli("localhost", "root", "", "Portfolio_Daniel");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $link = $_POST['link'];
    $screenshotPath = "";

    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $fileName = str_replace(' ', '_', $_FILES['screenshot']['name']);
        $targetDir = "project_screenshots/";
        $targetFile = $targetDir . basename($fileName);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $targetFile)) {
            $screenshotPath = $targetFile;
        }
    }

    $stmt = $conn->prepare("INSERT INTO Portfolio_Projects (Project_Title, Description, Screenshot_Path, Project_Link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $desc, $screenshotPath, $link);
    $stmt->execute();
    $stmt->close();

    header("Location: Portfolio_Interface.php?project_added=1");
    exit();
}
?>