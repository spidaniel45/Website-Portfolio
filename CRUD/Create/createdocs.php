<?php 

$conn = new mysqli("localhost", "root", "", "Portfolio_Daniel");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $files = $_FILES['document']; // Get uploaded files array
    $targetDir = "uploads/";       // Directory to store uploaded files

    // Create uploads directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true); // 0755 sets read/write/execute permissions
    }

    // Counters for feedback message
    $uploaded = 0;
    $skipped = 0;

    // Loop through all uploaded files (supports multiple file upload)
    for ($i = 0; $i < count($files['name']); $i++) {
        // Replace spaces with underscores in filename to avoid URL issues
        $fileName = str_replace(' ', '_', $files['name'][$i]);
        $fileTmp = $files['tmp_name'][$i];      // Temporary file location
        $fileError = $files['error'][$i];       // Error code (0 = no error)
        $targetFile = $targetDir . basename($fileName); // Full path for file

        // Check if file uploaded without errors
        if ($fileError === UPLOAD_ERR_OK) {
            // Check if filename already exists in database
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Portfolio_Documents WHERE File_Name = ?");
            $stmt->bind_param("s", $fileName);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc()['count'] > 0;
            $stmt->close();

            // Skip if file already exists in database or on server
            if ($exists || file_exists($targetFile)) {
                $skipped++;
                continue; // Skip to next file
            }

            // Move file from temporary location to uploads directory
            if (move_uploaded_file($fileTmp, $targetFile)) {
                // Insert file record into database
                $stmt = $conn->prepare("INSERT INTO Portfolio_Documents (File_Name, File_Path) VALUES (?, ?)");
                $stmt->bind_param("ss", $fileName, $targetFile); // "ss" = two string parameters
                $stmt->execute();
                $stmt->close();
                $uploaded++;
            }
        }
    }

    // Redirect with upload statistics in URL
    header("Location: " . $_SERVER['PHP_SELF'] . "?uploaded=$uploaded&skipped=$skipped");
    exit();
}
?>