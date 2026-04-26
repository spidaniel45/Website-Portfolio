<?php
// ============================================================
// Upload_Project.php
// Handles new project creation with screenshot upload.
// ============================================================
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$conn           = portfolioDb();
$title          = trim($_POST['title'] ?? '');
$desc           = trim($_POST['description'] ?? '');
$link           = trim($_POST['link'] ?? '');
$screenshotPath = '';

// ── Validate required fields ───────────────────────────────────
if ($title === '' || $desc === '') {
    header('Location: index.php?error=missing_fields');
    exit();
}

// ── Handle screenshot upload ───────────────────────────────────
if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
    $targetDir = env('SCREENSHOT_DIR', 'project_screenshots/');

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName   = str_replace(' ', '_', basename($_FILES['screenshot']['name']));
    $targetFile = $targetDir . $fileName;

    // Basic MIME check — defence-in-depth
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mimeType     = mime_content_type($_FILES['screenshot']['tmp_name']);

    if (in_array($mimeType, $allowedMimes, true)) {
        if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $targetFile)) {
            $screenshotPath = $targetFile;
        }
    }
}

// ── Insert into DB ─────────────────────────────────────────────
$stmt = $conn->prepare(
    "INSERT INTO Portfolio_Projects (Project_Title, Description, Screenshot_Path, Project_Link)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $title, $desc, $screenshotPath, $link);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: index.php?project_added=1");
exit();