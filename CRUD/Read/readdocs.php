<?php

$conn = new mysqli("localhost", "root", "", "Portfolio_Daniel");
if (isset($_GET['uploaded']) || isset($_GET['skipped'])) {
    $uploaded = intval($_GET['uploaded'] ?? 0);
    $skipped = intval($_GET['skipped'] ?? 0);
    $message = "Uploaded: $uploaded file(s). Skipped: $skipped duplicate(s).";
}
?>