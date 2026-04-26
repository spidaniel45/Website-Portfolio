<?php
// ============================================================
// CRUD/Delete/deletedocs.php
// ============================================================
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

$conn = portfolioDb();

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

        $del = $conn->prepare("DELETE FROM Portfolio_Documents WHERE File_Name = ?");
        $del->bind_param("s", $fileToDelete);
        $del->execute();
        $del->close();

        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
    }

    $stmt->close();
}

$conn->close();