<?php
// ============================================================
// CRUD/Update/updatedocs.php
// Handles project management: edit/update, delete, or cancel.
//
// GET  ?id=N          → Show the edit form pre-filled with project data.
// POST action=update  → Save the edited fields (title, description, link).
//                       If a new screenshot is uploaded it replaces the old one;
//                       if left blank the existing screenshot is kept.
// POST action=delete  → Hard-delete the project row (and its screenshot file).
// POST action=cancel  → No changes; redirect back to the portfolio.
// ============================================================
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

$conn = portfolioDb();

// ── Helper: redirect to portfolio root ───────────────────────
// Build the URL back to index.php using SCRIPT_NAME so it works
// at any subdirectory depth (XAMPP, Apache, Nginx).
function goHome(string $flash = ''): never
{
    // SCRIPT_NAME is e.g. /myportfolio/CRUD/Update/updatedocs.php
    // We need   /myportfolio/index.php
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    // Strip CRUD/Update/updatedocs.php (3 segments) from the end
    $parts = explode('/', rtrim($scriptName, '/'));
    array_splice($parts, -3);          // remove last 3 segments
    $base = implode('/', $parts);      // e.g. /myportfolio
    $qs   = $flash ? '?' . $flash : '';
    header('Location: ' . $base . '/index.php' . $qs);
    exit();
}

// ============================================================
// POST — process form actions
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action    = trim($_POST['action']    ?? '');
    $projectId = (int) ($_POST['project_id'] ?? 0);

    if ($projectId <= 0) {
        goHome('error=invalid_id');
    }

    // ── CANCEL: do nothing, go back ──────────────────────────
    if ($action === 'cancel') {
        goHome();
    }

    // ── DELETE: remove from DB + disk ────────────────────────
    if ($action === 'delete') {
        // Fetch screenshot path first so we can delete the file
        $sel = $conn->prepare(
            "SELECT Screenshot_Path FROM Portfolio_Projects WHERE ID = ?"
        );
        $sel->bind_param('i', $projectId);
        $sel->execute();
        $row = $sel->get_result()->fetch_assoc();
        $sel->close();

        if ($row && !empty($row['Screenshot_Path']) && file_exists($row['Screenshot_Path'])) {
            unlink($row['Screenshot_Path']);
        }

        $del = $conn->prepare("DELETE FROM Portfolio_Projects WHERE ID = ?");
        $del->bind_param('i', $projectId);
        $del->execute();
        $del->close();
        $conn->close();
        goHome('project_deleted=1');
    }

    // ── UPDATE: save changed fields ───────────────────────────
    if ($action === 'update') {
        $title       = trim($_POST['title']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $link        = trim($_POST['link']        ?? '');

        if ($title === '' || $description === '') {
            // Re-show form with an error; fall through to GET block below
            $_GET['id']    = $projectId;
            $_GET['error'] = 'missing_fields';
            // (continues past the POST block into the GET/display block)
        } else {
            // Determine screenshot path: keep existing unless a new file was uploaded
            $screenshotPath = null; // will stay NULL if no change

            if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
                $targetDir    = env('SCREENSHOT_DIR', 'project_screenshots/');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $mimeType     = mime_content_type($_FILES['screenshot']['tmp_name']);

                if (in_array($mimeType, $allowedMimes, true)) {
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    $fileName   = str_replace(' ', '_', basename($_FILES['screenshot']['name']));
                    $targetFile = $targetDir . $fileName;

                    if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $targetFile)) {
                        // Delete old screenshot from disk
                        $sel2 = $conn->prepare(
                            "SELECT Screenshot_Path FROM Portfolio_Projects WHERE ID = ?"
                        );
                        $sel2->bind_param('i', $projectId);
                        $sel2->execute();
                        $old = $sel2->get_result()->fetch_assoc();
                        $sel2->close();

                        if ($old && !empty($old['Screenshot_Path']) && file_exists($old['Screenshot_Path'])) {
                            unlink($old['Screenshot_Path']);
                        }

                        $screenshotPath = $targetFile;
                    }
                }
            }

            // Build query: update screenshot only if a new one was uploaded
            if ($screenshotPath !== null) {
                $stmt = $conn->prepare(
                    "UPDATE Portfolio_Projects
                     SET Project_Title = ?, Description = ?, Project_Link = ?, Screenshot_Path = ?
                     WHERE ID = ?"
                );
                $stmt->bind_param('ssssi', $title, $description, $link, $screenshotPath, $projectId);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE Portfolio_Projects
                     SET Project_Title = ?, Description = ?, Project_Link = ?
                     WHERE ID = ?"
                );
                $stmt->bind_param('sssi', $title, $description, $link, $projectId);
            }

            $stmt->execute();
            $stmt->close();
            $conn->close();
            goHome('project_updated=1');
        }
    }
}

// ============================================================
// GET — display the edit form
// ============================================================
$projectId = (int) ($_GET['id'] ?? 0);

if ($projectId <= 0) {
    goHome('error=invalid_id');
}

$sel = $conn->prepare("SELECT * FROM Portfolio_Projects WHERE ID = ? LIMIT 1");
$sel->bind_param('i', $projectId);
$sel->execute();
$project = $sel->get_result()->fetch_assoc();
$sel->close();
$conn->close();

if (!$project) {
    goHome('error=not_found');
}

$fieldError = isset($_GET['error']) && $_GET['error'] === 'missing_fields';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project — <?= htmlspecialchars($project['Project_Title']) ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Shared portfolio styles (two levels up) -->
    <link rel="stylesheet" href="../../style.css">
</head>
<body class="d-flex align-items-start justify-content-center min-vh-100 py-5">

    <div class="card-dark p-4 w-100" style="max-width: 680px; border-radius: 15px;">

        <!-- Header -->
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square me-2"></i>Edit Project
        </h4>
        <p class="text-muted small mb-4">
            Make your changes below, then choose <strong>Save</strong>,
            <strong>Delete</strong>, or <strong>Cancel</strong>.
        </p>

        <?php if ($fieldError): ?>
            <div class="alert alert-danger mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Title and Description are required.
            </div>
        <?php endif; ?>

        <!-- ── EDIT FORM ─────────────────────────────────────── -->
        <form method="POST"
              action="updatedocs.php"
              enctype="multipart/form-data"
              id="editForm">

            <input type="hidden" name="project_id" value="<?= $projectId ?>">
            <input type="hidden" name="action"     value="update" id="formAction">

            <!-- Title -->
            <div class="mb-3">
                <label for="title" class="form-label">Project Title <span class="text-danger">*</span></label>
                <input type="text"
                       id="title"
                       name="title"
                       class="form-control form-control-dark"
                       value="<?= htmlspecialchars($project['Project_Title']) ?>"
                       required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea id="description"
                          name="description"
                          class="form-control form-control-dark"
                          rows="5"
                          required><?= htmlspecialchars($project['Description']) ?></textarea>
            </div>

            <!-- Screenshot -->
            <div class="mb-3">
                <label class="form-label">Screenshot</label>
                <?php if ($project['Screenshot_Path'] && file_exists('../../' . $project['Screenshot_Path'])): ?>
                    <div class="mb-2">
                        <img src="../../<?= htmlspecialchars($project['Screenshot_Path']) ?>"
                             alt="Current screenshot"
                             class="img-fluid rounded-3"
                             style="max-height:180px; object-fit:cover;">
                        <p class="text-muted small mt-1">
                            <i class="bi bi-image me-1"></i>Current screenshot — upload a new one to replace it, or leave blank to keep it.
                        </p>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-image me-1"></i>No screenshot uploaded yet.
                    </p>
                <?php endif; ?>
                <input type="file"
                       name="screenshot"
                       class="form-control form-control-dark"
                       accept="image/*">
            </div>

            <!-- Link -->
            <div class="mb-4">
                <label for="link" class="form-label">Live / GitHub Link</label>
                <input type="url"
                       id="link"
                       name="link"
                       class="form-control form-control-dark"
                       placeholder="https://..."
                       value="<?= htmlspecialchars($project['Project_Link'] ?? '') ?>">
            </div>

            <!-- Action buttons -->
            <div class="d-flex flex-wrap gap-2">

                <!-- Save changes -->
                <button type="submit"
                        class="action-button"
                        onclick="setAction('update')">
                    <i class="bi bi-floppy me-1"></i>Save Changes
                </button>

                <!-- Cancel — no changes -->
                <button type="submit"
                        class="action-button"
                        style="background: var(--surface-2); border: 2px solid var(--brand-dark);"
                        onclick="setAction('cancel')">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>

                <!-- Delete project -->
                <button type="submit"
                        class="action-button ms-auto"
                        style="background: #c0392b;"
                        onclick="if(!confirmDelete()) return false; setAction('delete'); return true;">
                    <i class="bi bi-trash me-1"></i>Delete Project
                </button>

            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set the hidden action field then allow the form to submit
        function setAction(action) {
            document.getElementById('formAction').value = action;
            return true;
        }

        // Confirm before deleting
        function confirmDelete() {
            return confirm(
                'Are you sure you want to permanently delete this project?\n' +
                'This cannot be undone.'
            );
        }
    </script>
</body>
</html>