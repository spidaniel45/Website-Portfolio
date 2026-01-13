<?php

$conn = new mysqli("localhost", "root", "", "Portfolio_Daniel");

if (isset($_GET['delete'])) {
    $fileToDelete = $_GET['delete'];
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT File_Path FROM Portfolio_Documents WHERE File_Name = ?");
    $stmt->bind_param("s", $fileToDelete); // "s" means string parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // If file record exists in database
    if ($result && $result->num_rows > 0) {
        $filePath = $result->fetch_assoc()['File_Path'];
        
        // Delete physical file if it exists
        if (file_exists($filePath)) {
            unlink($filePath); // Delete file from server
        }
        
        // Delete database record
        $deleteStmt = $conn->prepare("DELETE FROM Portfolio_Documents WHERE File_Name = ?");
        $deleteStmt->bind_param("s", $fileToDelete);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Redirect to same page to clear URL parameters and show updated list
        header("Location: " . $_SERVER['PHP_SELF']);
        exit(); // Stop script execution after redirect
    }
    $stmt->close();
}
?>