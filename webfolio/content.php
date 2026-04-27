<?php
// ============================================================
// webfolio/content.php
// Shared top-of-page content: DB connection + nav box.
// ============================================================
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

$conn    = portfolioDb();
$message = null;

// ── Feedback messages ──────────────────────────────────────────
if (isset($_GET['project_added'])) {
    $message = '<i class="bi bi-check-circle-fill me-2"></i>Project added successfully!';
}
if (isset($_GET['uploaded'])) {
    $up      = (int) $_GET['uploaded'];
    $sk      = (int) $_GET['skipped'];
    $message = "<i class='bi bi-cloud-upload-fill me-2'></i>Uploaded: {$up} file(s). Skipped: {$sk} duplicate(s).";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Interface</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Devicons (tech logos) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/devicons/devicon@v2.15.1/devicon.min.css">
    <!-- Custom overrides -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex">

<!-- SVG Filter for Glass Distortion -->
<svg style="display:none" aria-hidden="true">
    <filter id="glass-distortion">
        <feTurbulence type="turbulence" baseFrequency="0.008" numOctaves="2" result="noise" />
        <feDisplacementMap in="SourceGraphic" in2="noise" scale="77" />
    </filter>
</svg>

<div class="main-content flex-fill">

    <!-- Flash message -->
    <?php if ($message): ?>
        <div class="alert alert-brand mb-4" role="alert">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- ==========================================
         ABOUT INFO BOX (Expandable Navigation)
         ========================================== -->
    <div class="About_Info mb-4">
        <details>
            <summary>Click here to explore</summary>
            <ul class="top-nav mt-3">
                <li>
                    <a href="#about">
                        <div class="glass-filter"></div>
                        <div class="glass-overlay"></div>
                        <div class="glass-specular"></div>
                        <div class="glass-content">
                            <i class="bi bi-person"></i>
                            <span>About</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#skills">
                        <div class="glass-filter"></div>
                        <div class="glass-overlay"></div>
                        <div class="glass-specular"></div>
                        <div class="glass-content">
                            <i class="bi bi-tools"></i>
                            <span>Skills</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#"
                       data-bs-toggle="modal"
                       data-bs-target="#DownloadModal"
                       class="action-button glass-nav-btn">
                        <div class="glass-filter"></div>
                        <div class="glass-overlay"></div>
                        <div class="glass-specular"></div>
                        <div class="glass-content">
                            <i class="bi bi-folder2-open"></i>
                            <span>Docs</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#certifications">
                        <div class="glass-filter"></div>
                        <div class="glass-overlay"></div>
                        <div class="glass-specular"></div>
                        <div class="glass-content">
                            <i class="bi bi-award"></i>
                            <span>Certs</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#git">
                        <div class="glass-filter"></div>
                        <div class="glass-overlay"></div>
                        <div class="glass-specular"></div>
                        <div class="glass-content">
                            <i class="bi bi-github"></i>
                            <span>GitHub</span>
                        </div>
                    </a>
                </li>
            </ul>
        </details>
    </div>