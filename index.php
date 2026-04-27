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

// ── Handle quick project deletion from index (delete_project=N) ─
if (isset($_GET['delete_project'])) {
    $delId = (int) $_GET['delete_project'];
    if ($delId > 0) {
        $sel = $conn->prepare("SELECT Screenshot_Path FROM Portfolio_Projects WHERE ID = ?");
        $sel->bind_param('i', $delId);
        $sel->execute();
        $row = $sel->get_result()->fetch_assoc();
        $sel->close();

        if ($row && !empty($row['Screenshot_Path']) && file_exists($row['Screenshot_Path'])) {
            unlink($row['Screenshot_Path']);
        }
        $del = $conn->prepare("DELETE FROM Portfolio_Projects WHERE ID = ?");
        $del->bind_param('i', $delId);
        $del->execute();
        $del->close();
    }
    header('Location: index.php?project_deleted=1');
    exit();
}

// ── Feedback message ───────────────────────────────────────────
if (isset($_GET['project_added'])) {
    $message = '<i class="bi bi-check-circle-fill me-2"></i>Project added successfully!';
}
if (isset($_GET['project_updated'])) {
    $message = '<i class="bi bi-check-circle-fill me-2"></i>Project updated successfully!';
}
if (isset($_GET['project_deleted'])) {
    $message = '<i class="bi bi-trash-fill me-2"></i>Project deleted.';
}
if (isset($_GET['uploaded'])) {
    $up      = (int) $_GET['uploaded'];
    $sk      = (int) $_GET['skipped'];
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
$documents = $conn->query("SELECT File_Name, File_Path FROM Portfolio_Documents ORDER BY ID DESC");

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
    <!-- Devicons (tech stack logos) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/devicons/devicon@v2.15.1/devicon.min.css">
    <!-- Custom overrides -->
    <link rel="stylesheet" href="style.css">

    <!-- Skeleton + toast + live-dot styles -->
    <style>
    /* ── Snipzy-style shimmer keyframe ───────────────────────── */
    @keyframes shimmer {
        0%   { background-position: -200% 0; }
        100% { background-position:  200% 0; }
    }

    /* ── Skeleton container — matches project-list width ─────── */
    .skeleton-loader-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
        width: 100%;
        max-width: 820px;
    }

    /* ── Skeleton card shell — mirrors .project-item border/radius */
    .skeleton-card {
        background-color: var(--surface-1);
        border: 3px solid var(--brand-dark);
        border-radius: 10px;
        overflow: hidden;
        transition: opacity 0.3s ease;
    }

    /* ── Image placeholder at top of card ────────────────────── */
    .skeleton-image {
        width: 100%;
        height: 160px;
        background: linear-gradient(110deg,
            #2a2a2a 8%,
            #3a3a3a 18%,
            #2a2a2a 33%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite linear;
    }

    /* ── Text-line area ──────────────────────────────────────── */
    .skeleton-content {
        padding: 18px 20px 20px;
    }
    .skeleton-title,
    .skeleton-text {
        border-radius: 4px;
        background: linear-gradient(110deg,
            #2a2a2a 8%,
            #3a3a3a 18%,
            #2a2a2a 33%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite linear;
    }
    .skeleton-title {
        width: 55%;
        height: 20px;
        margin-bottom: 14px;
    }
    .skeleton-text {
        height: 12px;
        margin-bottom: 8px;
        animation-delay: 0.1s;
    }
    .skeleton-text-short {
        width: 65%;
        animation-delay: 0.2s;
    }
    .skeleton-date {
        width: 30%;
        height: 11px;
        margin-bottom: 16px;
        border-radius: 4px;
        background: linear-gradient(110deg,
            #2a2a2a 8%,
            #3a3a3a 18%,
            #2a2a2a 33%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite linear;
        animation-delay: 0.05s;
    }
    .skeleton-btns {
        display: flex;
        gap: 8px;
        margin-top: 14px;
    }
    .skeleton-btn {
        width: 72px;
        height: 32px;
        border-radius: 50px;
        background: linear-gradient(110deg,
            #2a2a2a 8%,
            #3a3a3a 18%,
            #2a2a2a 33%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite linear;
        animation-delay: 0.25s;
    }

    /* ── Fade-in for real cards after skeleton swap ──────────── */
    @keyframes card-fade-in {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0);   }
    }
    .project-item {
        animation: card-fade-in 0.35s ease both;
    }

    /* ── Live indicator dot ──────────────────────────────────── */
    #projects-live-dot {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #2ecc71;
        margin-left: 8px;
        vertical-align: middle;
        box-shadow: 0 0 0 0 rgba(46,204,113,0.6);
        animation: live-pulse 2.4s ease infinite;
    }
    @keyframes live-pulse {
        0%  { box-shadow: 0 0 0 0   rgba(46,204,113,0.6); }
        60% { box-shadow: 0 0 0 8px rgba(46,204,113,0);   }
        100%{ box-shadow: 0 0 0 0   rgba(46,204,113,0);   }
    }

    /* ── Toast notification (top-right) ─────────────────────── */
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }
    .toast-note {
        pointer-events: auto;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 18px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        color: #fff;
        min-width: 260px;
        max-width: 340px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.35);
        opacity: 0;
        transform: translateX(30px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .toast-note.show {
        opacity: 1;
        transform: translateX(0);
    }
    .toast-note.toast-deleted  { background: #c0392b; border-left: 4px solid #e74c3c; }
    .toast-note.toast-updated  { background: #27ae60; border-left: 4px solid #2ecc71; }
    .toast-note.toast-added    { background: #2980b9; border-left: 4px solid #3498db; }
    .toast-note.toast-info     { background: var(--surface-1); border-left: 4px solid var(--brand-accent); color: var(--text-primary); }
    .toast-close {
        margin-left: auto;
        background: none;
        border: none;
        color: inherit;
        opacity: 0.7;
        cursor: pointer;
        font-size: 1rem;
        padding: 0;
        line-height: 1;
    }
    .toast-close:hover { opacity: 1; }
    </style>
</head>
<body class="d-flex">

    <!-- SVG Filter for Glass Distortion (hidden, referenced by CSS filter) -->
    <svg style="display:none" aria-hidden="true">
        <filter id="glass-distortion">
            <feTurbulence type="turbulence" baseFrequency="0.008" numOctaves="2" result="noise" />
            <feDisplacementMap in="SourceGraphic" in2="noise" scale="77" />
        </filter>
    </svg>

    <!-- =====================================================
         SIDEBAR — Profile Card
         ===================================================== -->
    <aside class="Header">
        <img src="./uploads/images/profile/profile.jpeg"
             alt="Profile Picture"
             class="circle-pic"
             onerror="this.onerror=null;this.style.opacity='0.2';">

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

        <!-- Hamburger Nav — starts open (nav-open), user clicks ☰ to close -->
        <div class="hamburger-nav nav-open" id="hamburgerNav">
            <!-- Hamburger toggle button — aria-expanded matches open state -->
            <button class="hamburger-btn is-open" id="hamburgerBtn" aria-label="Toggle navigation" aria-expanded="true">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <!-- Slide-out icon tray -->
            <ul class="top-nav" id="navTray">
                <li style="--i:0">
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
                <li style="--i:1">
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
                <li style="--i:2">
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
                <li style="--i:3">
                    <a href="#projects">
                        <div class="glass-filter"></div>
                        <div class="glass-overlay"></div>
                        <div class="glass-specular"></div>
                        <div class="glass-content">
                            <i class="bi bi-code-slash"></i>
                            <span>Projects</span>
                        </div>
                    </a>
                </li>
                <li style="--i:4">
                    <!--
                        This link opens a Bootstrap modal — it must NOT close the nav tray.
                        The data-nav-keep attribute tells our JS to skip auto-close for it.
                    -->
                    <a href="#"
                       data-bs-toggle="modal"
                       data-bs-target="#DownloadModal"
                       data-nav-keep="true"
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
                <li style="--i:5">
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
            </ul>
        </div>

        <!-- ── Sub-interface includes ───────────────────────── -->
        <?php include __DIR__ . '/webfolio/sub-interface/aboutme.php'; ?>
        <?php include __DIR__ . '/webfolio/sub-interface/skills.php'; ?>
        <?php include __DIR__ . '/webfolio/sub-interface/github.php'; ?>

        <!-- ── Projects Section ─────────────────────────────── -->
        <section id="projects" class="content-section">
            <h2 class="fw-bold mb-4">
                <i class="bi bi-code-slash me-2"></i>My Projects
                <span id="projects-live-dot" title="Live — refreshes every 30 s"></span>
            </h2>

            <!--
                Project list — populated and auto-refreshed by JS.
                PHP renders the initial payload into a data attribute so
                the first paint is instant (no extra network round-trip).
                The poller then re-fetches api/projects.php every 30 s.
            -->
            <?php
                // Encode the initial project set for JS bootstrap
                $initialProjects = [];
                if ($projects && $projects->num_rows > 0) {
                    while ($p = $projects->fetch_assoc()) {
                        $initialProjects[] = $p;
                    }
                }
            ?>
            <div id="project-list-container" class="project-list mb-4"
                 data-initial="<?= htmlspecialchars(json_encode($initialProjects, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>">
                <!-- JS renders cards here; skeletons shown during each refresh -->
            </div>

            <!-- Add Project Form — below the showcase -->
            <div class="form-dark">
                <h5 class="mb-3 fw-semibold">
                    <i class="bi bi-plus-square me-2"></i>Add New Project
                </h5>
                <form action="Upload_Project.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="projectTitle" class="form-label">Project Title</label>
                        <input type="text" id="projectTitle" name="title"
                               class="form-control" placeholder="Project Title" required>
                    </div>
                    <div class="mb-3">
                        <label for="projectDesc" class="form-label">Description</label>
                        <textarea id="projectDesc" name="description"
                                  class="form-control" rows="4"
                                  placeholder="Project Description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="projectScreenshot" class="form-label">Screenshot</label>
                        <input type="file" id="projectScreenshot" name="screenshot"
                               class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="projectLink" class="form-label">Live / GitHub Link</label>
                        <input type="url" id="projectLink" name="link"
                               class="form-control" placeholder="https://...">
                    </div>
                    <button type="submit" class="action-button">
                        <i class="bi bi-plus-circle me-1"></i> Add Project
                    </button>
                </form>
            </div>
        </section>

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
                            <p class="text-muted text-center py-3">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No documents uploaded yet.
                            </p>
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
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Select Files</label>
                            <input type="file"
                                   name="document[]"
                                   class="form-control"
                                   accept=".pdf,.doc,.docx,.txt"
                                   multiple
                                   required>
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>Accepted: PDF, DOC, DOCX, TXT
                            </div>
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
    <!-- Toast container (top-right popup notifications) -->
    <div id="toast-container" aria-live="polite"></div>

    <script>
    // ============================================================
    // HAMBURGER NAV — stays open until user clicks ☰ to close
    // ============================================================
    (function () {
        const btn  = document.getElementById('hamburgerBtn');
        const nav  = document.getElementById('hamburgerNav');
        const tray = document.getElementById('navTray');

        btn.addEventListener('click', () => {
            const open = nav.classList.toggle('nav-open');
            btn.setAttribute('aria-expanded', String(open));
            btn.classList.toggle('is-open', open);
        });

        tray.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', () => {
                tray.querySelectorAll('a').forEach(x => x.classList.remove('active-nav'));
                a.classList.add('active-nav');
            });
        });
    })();

    // ============================================================
    // TOAST NOTIFICATIONS — top-right popup, auto-dismiss 4 s
    // Types: 'deleted' | 'updated' | 'added' | 'info'
    // ============================================================
    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');

        const toast = document.createElement('div');
        toast.className = `toast-note toast-${type}`;

        const icons = {
            deleted : 'bi-trash-fill',
            updated : 'bi-check-circle-fill',
            added   : 'bi-plus-circle-fill',
            info    : 'bi-info-circle-fill',
        };
        const icon = icons[type] || icons.info;

        toast.innerHTML = `
            <i class="bi ${icon}"></i>
            <span>${message}</span>
            <button class="toast-close" aria-label="Close">&times;</button>`;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => dismissToast(toast));

        // Auto-dismiss after 4 s
        setTimeout(() => dismissToast(toast), 4000);
    }

    function dismissToast(toast) {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    }

    // Show toast for PHP flash params (project_deleted, project_added, etc.)
    (function checkFlash() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('project_deleted')) {
            showToast('Project deleted successfully.', 'deleted');
        } else if (params.has('project_added')) {
            showToast('Project added successfully.', 'added');
        } else if (params.has('project_updated')) {
            showToast('Project updated successfully.', 'updated');
        }
        // Clean the query string from the URL bar without reloading
        if (params.has('project_deleted') || params.has('project_added') || params.has('project_updated')) {
            history.replaceState({}, '', window.location.pathname + '#projects');
        }
    })();

    // ============================================================
    // PROJECTS — Snipzy-style skeleton + auto-refresh every 30 s
    //
    // Flow:
    //  1. DOMContentLoaded → read PHP inline data → render instantly.
    //  2. Every 30 s → show skeletons → fetch api/projects.php →
    //     compare hash → rebuild DOM only when data changed →
    //     show toast if count changed.
    //  3. Tab hidden → pause poll. Tab visible → immediate refresh.
    // ============================================================
    (function () {
        const container = document.getElementById('project-list-container');
        const POLL_MS   = 30_000;
        let lastHash    = '';
        let lastCount   = 0;
        let pollTimer   = null;

        // ── Snipzy skeleton card (image-first layout) ───────────
        function skeletonCard() {
            return `
            <div class="skeleton-card" aria-hidden="true">
                <div class="skeleton-image"></div>
                <div class="skeleton-content">
                    <div class="skeleton-title"></div>
                    <div class="skeleton-date"></div>
                    <div class="skeleton-text"></div>
                    <div class="skeleton-text"></div>
                    <div class="skeleton-text skeleton-text-short"></div>
                    <div class="skeleton-btns">
                        <div class="skeleton-btn"></div>
                        <div class="skeleton-btn"></div>
                    </div>
                </div>
            </div>`;
        }

        function showSkeletons(count) {
            container.innerHTML =
                '<div class="skeleton-loader-container">' +
                Array(Math.max(count, 1)).fill(null).map(skeletonCard).join('') +
                '</div>';
        }

        // ── Build a real project card ────────────────────────────
        function buildCard(p) {
            const title   = escHtml(p.Project_Title ?? '');
            const desc    = escHtml(p.Description   ?? '').replace(/\n/g, '<br>');
            const date    = formatDate(p.Date_Created ?? '');
            const id      = parseInt(p.ID, 10);

            const imgHtml = p.Screenshot_Path
                ? `<img src="${escHtml(p.Screenshot_Path)}"
                        alt="${title} screenshot"
                        class="img-fluid rounded-3 mb-2" loading="lazy">`
                : '';

            const linkHtml = p.Project_Link
                ? `<p class="mb-0">
                       <a href="${escHtml(p.Project_Link)}" target="_blank" rel="noopener">
                           <i class="bi bi-box-arrow-up-right me-1"></i>View Project →
                       </a>
                   </p>`
                : '';

            return `
            <div class="project-item">
                <h4>${title}</h4>
                <p class="text-muted small mb-2">
                    <i class="bi bi-calendar3 me-1"></i>${date}
                </p>
                <p>${desc}</p>
                ${imgHtml}
                ${linkHtml}
                <div class="d-flex gap-2 mt-3">
                    <a href="CRUD/Update/updatedocs.php?id=${id}"
                       class="action-button"
                       style="font-size:0.8rem;padding:6px 16px;">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <a href="?delete_project=${id}"
                       class="action-button"
                       style="font-size:0.8rem;padding:6px 16px;background:var(--brand-light);"
                       onclick="return confirm('Delete this project?')">
                        <i class="bi bi-trash me-1"></i>Delete
                    </a>
                </div>
            </div>`;
        }

        // ── Render real cards into the container ─────────────────
        function renderProjects(projects) {
            if (!projects || projects.length === 0) {
                container.innerHTML =
                    '<p class="text-muted fst-italic mb-4">No projects added yet.</p>';
                return;
            }
            container.innerHTML = projects.map(buildCard).join('');
        }

        // ── Fetch + diff + update ────────────────────────────────
        async function fetchProjects(silent = false) {
            const currentCount = lastCount || 2;
            if (!silent) showSkeletons(currentCount);

            try {
                const res  = await fetch('api/projects.php', { cache: 'no-store' });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();

                const newCount = (data.projects || []).length;

                if (data.hash !== lastHash) {
                    // Something changed — figure out what to tell the user
                    if (lastHash !== '') {
                        if (newCount > lastCount) {
                            showToast(`New project added! (${newCount} total)`, 'added');
                        } else if (newCount < lastCount) {
                            showToast('A project was removed.', 'deleted');
                        } else {
                            showToast('Projects updated.', 'updated');
                        }
                    }
                    lastHash  = data.hash;
                    lastCount = newCount;
                    renderProjects(data.projects);
                } else {
                    // No change — just remove skeletons, restore cards quietly
                    renderProjects(data.projects);
                }
            } catch (err) {
                console.warn('[projects] Fetch failed:', err);
                // Re-render from last known state if we have it
                if (lastHash) renderProjects([]);
            }
        }

        // ── Utility: HTML escape ─────────────────────────────────
        function escHtml(str) {
            return String(str)
                .replace(/&/g,  '&amp;')
                .replace(/</g,  '&lt;')
                .replace(/>/g,  '&gt;')
                .replace(/"/g,  '&quot;')
                .replace(/'/g,  '&#39;');
        }

        // ── Utility: "Apr 26, 2026" from MySQL datetime ──────────
        function formatDate(raw) {
            if (!raw) return '';
            const d = new Date(raw.replace(' ', 'T'));
            return isNaN(d) ? raw : d.toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });
        }

        // ── djb2 hash to seed lastHash from inline PHP data ──────
        function djb2(str) {
            let h = 5381;
            for (let i = 0; i < str.length; i++) {
                h = ((h << 5) + h) ^ str.charCodeAt(i);
            }
            return (h >>> 0).toString(16);
        }

        // ── Init: paint from PHP inline data immediately ─────────
        function init() {
            try {
                const initial = JSON.parse(container.dataset.initial || '[]');
                lastCount = initial.length;
                lastHash  = djb2(initial.map(p => p.ID + p.Project_Title).join('|'));
                renderProjects(initial);
            } catch (e) {
                fetchProjects();
                return;
            }
            pollTimer = setInterval(fetchProjects, POLL_MS);
        }

        // ── Pause polling when tab is hidden ─────────────────────
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(pollTimer);
            } else {
                fetchProjects();
                pollTimer = setInterval(fetchProjects, POLL_MS);
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
</body>
</html>