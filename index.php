<?php
// ============================================================
// index.php  –  Portfolio Entry Point
// ============================================================
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$conn    = portfolioDb();
$message = null;

// ── Handle file deletion ──────────────────────────────────────
if (isset($_GET['delete'])) {
    $fileName = $_GET['delete'];
    $stmt = $conn->prepare("SELECT File_Path FROM Portfolio_Documents WHERE File_Name = ?");
    $stmt->bind_param("s", $fileName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $filePath = $result->fetch_assoc()['File_Path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $del = $conn->prepare("DELETE FROM Portfolio_Documents WHERE File_Name = ?");
        $del->bind_param("s", $fileName);
        $del->execute();
        $del->close();
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
    }
    $stmt->close();
}

// ── Handle document upload ─────────────────────────────────────
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

    header("Location: ?uploaded={$uploaded}&skipped={$skipped}");
    exit();
}

// ── Feedback message ───────────────────────────────────────────
if (isset($_GET['project_added'])) {
    $message = '<i class="bi bi-check-circle-fill me-2"></i>Project added successfully!';
}
if (isset($_GET['uploaded'])) {
    $up  = (int) $_GET['uploaded'];
    $sk  = (int) $_GET['skipped'];
    $message = "<i class='bi bi-cloud-upload-fill me-2'></i>Uploaded: {$up} file(s). Skipped: {$sk} duplicate(s).";
}

// ── Fetch profile name ─────────────────────────────────────────
$nameResult = $conn->query("SELECT CONCAT(First_Name, ' ', Middle_Name, ' ', Last_Name) AS FullName FROM Portfolio_Profile LIMIT 1");
$fullName   = ($nameResult && $nameResult->num_rows > 0)
    ? $nameResult->fetch_assoc()['FullName']
    : 'Daniel Coton Evangelista';

// ── Fetch projects ─────────────────────────────────────────────
$projects = $conn->query("SELECT * FROM Portfolio_Projects ORDER BY Date_Created DESC");

// ── Fetch documents ────────────────────────────────────────────
$documents = $conn->query("SELECT File_Name, File_Path FROM Portfolio_Documents ORDER BY id DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio — <?= htmlspecialchars($fullName) ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom overrides -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex">

    <!-- =====================================================
         SIDEBAR — Profile Card
         ===================================================== -->
    <aside class="Header">
        <img src="Profile.jpg" alt="Profile Picture" class="circle-pic">

        <div class="profile-info">
            <h2><?= htmlspecialchars($fullName) ?></h2>

            <div class="social-icons w-100">
                <a href="https://github.com/spidaniel45" target="_blank" rel="noopener">
                    <ion-icon name="logo-github"></ion-icon> @spidaniel45
                </a>
                <a href="https://www.linkedin.com/in/daniel-c-evangelista-729793389/" target="_blank" rel="noopener">
                    <ion-icon name="logo-linkedin"></ion-icon> Daniel C. Evangelista
                </a>
                <a href="mailto:daniellora583@gmail.com">
                    <ion-icon name="mail-outline"></ion-icon> daniellora583@gmail.com
                </a>
                <a href="tel:+639272858696">
                    <ion-icon name="call-outline"></ion-icon> (+63) 927 285 8696
                </a>
            </div>
        </div>
    </aside>

    <!-- =====================================================
         MAIN CONTENT
         ===================================================== -->
    <main class="main-content flex-fill">

        <!-- Flash message -->
        <?php if ($message): ?>
            <div class="alert alert-brand mb-4" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- About Info / Navigation -->
        <div class="About_Info mb-4">
            <details>
                <summary>Click here to explore</summary>
                <ul class="top-nav mt-3">
                    <li><a href="#about">About Me</a></li>
                    <li><a href="#skills">Skills</a></li>
                    <li>
                        <a href="#"
                           data-bs-toggle="modal"
                           data-bs-target="#DownloadModal"
                           class="action-button">
                            Documents
                        </a>
                    </li>
                    <li><a href="#certifications">Certifications</a></li>
                    <li><a href="#git">Git History</a></li>
                </ul>
            </details>
        </div>

        <!-- ── Sub-interface includes ───────────────────────── -->
        <?php include __DIR__ . '/webfolio/sub-interface/aboutme.php'; ?>
        <?php include __DIR__ . '/webfolio/sub-interface/skills.php'; ?>
        <?php include __DIR__ . '/webfolio/sub-interface/github.php'; ?>

        <!-- ── Projects ─────────────────────────────────────── -->
        <h3 class="mt-4 mb-3 fw-bold">My Projects</h3>

        <!-- Add Project Form -->
        <div class="form-dark">
            <h5 class="mb-3">Add New Project</h5>
            <form action="Upload_Project.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="projectTitle">Project Title</label>
                    <input type="text" id="projectTitle" name="title"
                           class="form-control" placeholder="Project Title" required>
                </div>
                <div class="mb-3">
                    <label for="projectDesc">Description</label>
                    <textarea id="projectDesc" name="description"
                              class="form-control" rows="4"
                              placeholder="Project Description" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="projectScreenshot">Screenshot</label>
                    <input type="file" id="projectScreenshot" name="screenshot"
                           class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label for="projectLink">Live / GitHub Link</label>
                    <input type="url" id="projectLink" name="link"
                           class="form-control" placeholder="https://...">
                </div>
                <button type="submit" class="action-button">
                    <i class="bi bi-plus-circle me-1"></i> Add Project
                </button>
            </form>
        </div>

        <!-- Project List -->
        <div class="project-list">
            <?php if ($projects && $projects->num_rows > 0): ?>
                <?php while ($proj = $projects->fetch_assoc()): ?>
                    <div class="project-item">
                        <h4><?= htmlspecialchars($proj['Project_Title']) ?></h4>
                        <p><?= nl2br(htmlspecialchars($proj['Description'])) ?></p>

                        <?php if ($proj['Screenshot_Path']): ?>
                            <img src="<?= htmlspecialchars($proj['Screenshot_Path']) ?>"
                                 alt="<?= htmlspecialchars($proj['Project_Title']) ?> screenshot">
                        <?php endif; ?>

                        <?php if ($proj['Project_Link']): ?>
                            <p>
                                <a href="<?= htmlspecialchars($proj['Project_Link']) ?>"
                                   target="_blank" rel="noopener">
                                    View Project →
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted fst-italic">No projects added yet.</p>
            <?php endif; ?>
        </div>

    </main><!-- /main-content -->


    <!-- =====================================================
         MODAL — View Documents
         ===================================================== -->
    <div id="DownloadModal" class="modal fade modal-dark" tabindex="-1"
         aria-labelledby="DownloadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="DownloadModalLabel">
                        <i class="bi bi-folder2-open me-2"></i>My Documents
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="documents-list">
                        <?php if ($documents && $documents->num_rows > 0): ?>
                            <?php while ($doc = $documents->fetch_assoc()): ?>
                                <div class="document-item">
                                    <p>
                                        <i class="bi bi-file-earmark me-1"></i>
                                        <?= htmlspecialchars($doc['File_Name']) ?>
                                    </p>
                                    <div class="document-actions">
                                        <a class="download-link"
                                           href="<?= htmlspecialchars($doc['File_Path']) ?>"
                                           download>
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                        <a class="delete-link"
                                           href="?delete=<?= urlencode($doc['File_Name']) ?>"
                                           onclick="return confirm('Delete this file?')">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No documents uploaded yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="action-button"
                            data-bs-dismiss="modal"
                            data-bs-toggle="modal"
                            data-bs-target="#UploadModal">
                        <i class="bi bi-cloud-upload me-1"></i>Upload New Document
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         MODAL — Upload Document
         ===================================================== -->
    <div id="UploadModal" class="modal fade modal-dark" tabindex="-1"
         aria-labelledby="UploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="UploadModalLabel">
                        <i class="bi bi-cloud-upload me-2"></i>Upload a Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data" class="form-dark">
                        <div class="mb-3">
                            <label class="form-label">Select Files</label>
                            <input type="file"
                                   name="document[]"
                                   class="form-control"
                                   accept=".pdf,.doc,.docx,.txt"
                                   multiple
                                   required>
                            <div class="form-text text-muted">Accepted: PDF, DOC, DOCX, TXT</div>
                        </div>
                        <button type="submit" class="action-button w-100">
                            <i class="bi bi-upload me-1"></i>Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         SCRIPTS
         ===================================================== -->
    <!-- Bootstrap 5 JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <!-- Custom JS -->
    <script src="script.js"></script>
</body>
</html>