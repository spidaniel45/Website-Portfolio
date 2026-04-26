<?php
// ============================================================
// CRUD/Create/createdocs.php
// ============================================================
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

$conn = portfolioDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $files     = $_FILES['document'];
    $targetDir = env('UPLOAD_DIR', 'uploads/');
    $uploaded  = 0;
    $skipped   = 0;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $fileName   = str_replace(' ', '_', $files['name'][$i]);
        $fileTmp    = $files['tmp_name'][$i];
        $targetFile = $targetDir . basename($fileName);

        $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM Portfolio_Documents WHERE File_Name = ?");
        $check->bind_param("s", $fileName);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc()['cnt'] > 0;
        $check->close();

        if ($exists || file_exists($targetFile)) {
            $skipped++;
            continue;
        }

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $ins = $conn->prepare("INSERT INTO Portfolio_Documents (File_Name, File_Path) VALUES (?, ?)");
            $ins->bind_param("ss", $fileName, $targetFile);
            $ins->execute();
            $ins->close();
            $uploaded++;
        }
    }

    $conn->close();
    header("Location: " . $_SERVER['PHP_SELF'] . "?uploaded={$uploaded}&skipped={$skipped}");
    exit();
}

$conn->close();