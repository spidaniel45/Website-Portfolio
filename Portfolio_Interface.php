<!DOCTYPE html>
    <html lang="en">
<head>
    <link rel="stylesheet" href="Portfolio_Design.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Interface</title>
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "Portfolio_Daniel");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$message = "";

if (isset($_GET['delete'])) {
    $fileToDelete = $_GET['delete'];
    $stmt = $conn->prepare("SELECT File_Path FROM Portfolio_Documents WHERE File_Name = ?");
    $stmt->bind_param("s", $fileToDelete);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $filePath = $result->fetch_assoc()['File_Path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $deleteStmt = $conn->prepare("DELETE FROM Portfolio_Documents WHERE File_Name = ?");
        $deleteStmt->bind_param("s", $fileToDelete);
        $deleteStmt->execute();
        $deleteStmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $files = $_FILES['document'];
    $targetDir = "uploads/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $uploaded = 0;
    $skipped = 0;

    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = str_replace(' ', '_', $files['name'][$i]);
        $fileTmp = $files['tmp_name'][$i];
        $fileError = $files['error'][$i];
        $targetFile = $targetDir . basename($fileName);

        if ($fileError === UPLOAD_ERR_OK) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Portfolio_Documents WHERE File_Name = ?");
            $stmt->bind_param("s", $fileName);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc()['count'] > 0;
            $stmt->close();

            if ($exists || file_exists($targetFile)) {
                $skipped++;
                continue;
            }

            if (move_uploaded_file($fileTmp, $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO Portfolio_Documents (File_Name, File_Path) VALUES (?, ?)");
                $stmt->bind_param("ss", $fileName, $targetFile);
                $stmt->execute();
                $stmt->close();
                $uploaded++;
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?uploaded=$uploaded&skipped=$skipped");
    exit();
}

if (isset($_GET['uploaded']) || isset($_GET['skipped'])) {
    $uploaded = intval($_GET['uploaded'] ?? 0);
    $skipped = intval($_GET['skipped'] ?? 0);
    $message = "✅ Uploaded: $uploaded file(s). ⚠️ Skipped: $skipped duplicate(s).";
}

?>

<?php if ($message): ?>
    <div class="Message_Info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="Header">
  <?php
  $sql = "SELECT CONCAT(First_Name, ' ', Middle_Name, ' ', Last_Name) AS FullName FROM Portfolio_Profile LIMIT 1";
  $result = $conn->query($sql);
  $fullName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['FullName'] : "Daniel C. Evangelista";
  ?>
  <img src="Profile.jpg" alt="Profile Picture" class="circle-pic">
  <div class="profile-info">
    <h2><?= htmlspecialchars($fullName) ?></h2>
    <div class="social-icons">
      <a href="https://github.com/spidaniel45" target="_blank"><ion-icon name="logo-github"></ion-icon></a>
      <a href="https://www.linkedin.com/in/daniel-c-evangelista-729793389/" target="_blank"><ion-icon name="logo-linkedin"></ion-icon></a>
      <a href="mailto:daniellora583@gmail.com"><ion-icon name="mail-outline"></ion-icon></a>
      <a href="tel:+639XXXXXXXXX"><ion-icon name="call-outline"></ion-icon></a>
    </div>
  </div>
</div>

<button id="ViewDocumentsButton" class="action-button">View my Documents</button>

<div class="About_Info">
    <h3>About Me</h3>
    <p>Hello. I am a Junior Programmer</p>
</div>

<div class="Skills">
    <h3>Skills</h3>
    <ul>
        <li>Computer Hardware Troubleshooting</li>
        <li>Computer Programming</li>
    </ul>
</div>

<div class="Upload_Info">
    <h3>Upload</h3>
    <p>You can upload one document to your portfolio.</p>
    <button id="OpenUploadModalButton" class="action-button">Upload Document</button>
</div>

<div id="DownloadModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>My Documents</h3>
        <form class="upload-form">
            <div class="documents-list">
                <?php
                $docQuery = "SELECT File_Name, File_Path FROM Portfolio_Documents ORDER BY id DESC";
                $docResult = $conn->query($docQuery);

                if ($docResult && $docResult->num_rows > 0) {
                    while ($doc = $docResult->fetch_assoc()) {
                        $safeName = htmlspecialchars($doc['File_Name']);
                        $safePath = htmlspecialchars($doc['File_Path']);
                        echo "<div class='document-item'>
                                <p>$safeName</p>
                                <div class='document-actions'>
                                    <a class='download-link' href='$safePath' download>Download</a>
                                    <a class='delete-link' href='?delete=" . urlencode($doc['File_Name']) . "' onclick='return confirm(\"Delete this file?\")'>Delete</a>
                                </div>
                              </div>";
                    }
                    echo "<button id='UploadNewButton' class='action-button' style='margin-top: 15px;'>Upload New Document</button>";
                } else {
                    echo "<p>No documents uploaded yet.</p>";
                    echo "<button id='UploadNewButton' class='action-button' style='margin-top: 15px;'>Upload Document</button>";
                }
                ?>
            </div>
        </form>
    </div>
</div>

<div id="UploadModal" class="modal">
    <div class="modal-content">
        <span class="close-upload">&times;</span>
        <h3>Upload a Document</h3>
        <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
            <input type="file" name="document[]" accept=".pdf,.doc,.docx,.txt" multiple required>
            <br><br>
            <button type="submit" class="action-button">Upload</button>
        </form>
    </div>
</div>

<form action="Upload_Project.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Project Title" required><br><br>
    <textarea name="description" placeholder="Project Description" required></textarea><br><br>
    <input type="file" name="screenshot" accept="image/*"><br><br>
    <input type="url" name="link" placeholder="Live Link or GitHub"><br><br>
    <button type="submit" class="action-button">Add Project</button>
</form>

<h3>My Projects</h3>
<div class="project-list">
<?php
$projects = $conn->query("SELECT * FROM Portfolio_Projects ORDER BY Date_Created DESC");
while ($proj = $projects->fetch_assoc()) {
    echo "<div class='project-item'>
            <h4>" . htmlspecialchars($proj['Project_Title']) . "</h4>
            <p>" . nl2br(htmlspecialchars($proj['Description'])) . "</p>";
    if ($proj['Screenshot_Path']) {
        echo "<img src='" . htmlspecialchars($proj['Screenshot_Path']) . "' width='200'>";
    }
    if ($proj['Project_Link']) {
        echo "<p><a href='" . htmlspecialchars($proj['Project_Link']) . "' target='_blank'>View Project</a></p>";
    }
    echo "</div><hr>";
}

$conn->close();
?>
</div>

<script src="Portfolio_Functions.js"></script>
<script>
    document.getElementById("OpenUploadModalButton").addEventListener("click", () => {
        document.getElementById("UploadModal").style.display = "block";
    });
</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>