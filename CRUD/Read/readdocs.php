<?php
// ============================================================
// CRUD/Read/readdocs.php
// ============================================================
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

$conn    = portfolioDb();
$message = null;

if (isset($_GET['uploaded']) || isset($_GET['skipped'])) {
    $uploaded = (int) ($_GET['uploaded'] ?? 0);
    $skipped  = (int) ($_GET['skipped']  ?? 0);
    $message  = "Uploaded: {$uploaded} file(s). Skipped: {$skipped} duplicate(s).";
}

$conn->close();