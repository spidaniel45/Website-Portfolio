<?php

new mysqli("localhost", "root", "", "Portfolio_Daniel");

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Interface</title>
<div class="main-content">
    <!-- Display feedback message if exists -->
    <?php if ($message): ?>
        <div class="Message_Info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- ==========================================
         ABOUT INFO BOX (Expandable Navigation)
         ========================================== -->
    <div class="About_Info">
      <details>
        <summary>Click Here to learn more</summary>
        <ul class="top-nav">
          <li><a href="#about">About Me</a></li>
          <li><a href="#skills">Skills</a></li>
          <li><a href="#documents" id="ViewDocumentsButton" class="action-button">Documents</a></li>
          <li><a href="#certifications">Certifications</a></li>
          <li><a href="#git">Git History</a></li>
        </ul>
      </details>
    </div>

?>