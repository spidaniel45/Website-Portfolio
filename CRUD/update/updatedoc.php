<?php
// ============================================================
// CRUD/Update/updatedoc.php
// Handles document management: edit/update, delete, or cancel.
//
// GET  ?id=N          → Show the edit form pre-filled with document data.
// POST action=update  → Save the edited fields (file name) and/or replace the file.
//                       If a new file is uploaded it replaces the old one;
//                       if left blank the existing file is kept.
// POST action=delete  → Hard-delete the document row (and its file).
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
    // SCRIPT_NAME is e.g. /myportfolio/CRUD/Update/updatedoc.php
    // We need   /myportfolio/index.php
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    // Strip CRUD/Update/updatedoc.php (3 segments) from the end
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
    $docId     = (int) ($_POST['doc_id'] ?? 0);

    if ($docId <= 0) {
        goHome('error=invalid_id');
    }

    // ── CANCEL: do nothing, go back ──────────────────────────
    if ($action === 'cancel') {
        goHome();
    }

    // ── DELETE: remove from DB + disk ────────────────────────
    if ($action === 'delete') {
        // Fetch file path first so we can delete the file
        $sel = $conn->prepare(
            "SELECT File_Path FROM Portfolio_Documents WHERE ID = ?"
        );
        $sel->bind_param('i', $docId);
        $sel->execute();
        $row = $sel->get_result()->fetch_assoc();
        $sel->close();

        if ($row && !empty($row['File_Path']) && file_exists($row['File_Path'])) {
            unlink($row['File_Path']);
        }

        $del = $conn->prepare("DELETE FROM Portfolio_Documents WHERE ID = ?");
        $del->bind_param('i', $docId);
        $del->execute();
        $del->close();
        $conn->close();
        goHome('doc_deleted=1');
    }

    // ── UPDATE: save changed fields and/or replace file ───────────
    if ($action === 'update') {
        // Determine new file name: keep existing unless a new file was uploaded
        $newFileName = null; // will be set later
        $newFilePath = null;
        $fileUpdated = false;

        // Fetch current record to get old file name and path
        $sel = $conn->prepare("SELECT File_Name, File_Path FROM Portfolio_Documents WHERE ID = ?");
        $sel->bind_param('i', $docId);
        $sel->execute();
        $result = $sel->get_result();
        if ($result->num_rows === 0) {
            $sel->close();
            goHome('error=doc_not_found');
        }
        $doc = $result->fetch_assoc();
        $sel->close();

        $oldFileName = $doc['File_Name'];
        $oldFilePath = $doc['File_Path'];

        // Handle file upload if provided
        if (isset($_FILES['document']) && $_FILES['document']['error'][0] === UPLOAD_ERR_OK) {
            $files = $_FILES['document'];
            // We only expect single file for edit (though array due to multiple attribute? we'll treat as single)
            $fileName = str_replace(' ', '_', $files['name'][0]);
            $fileTmp  = $files['tmp_name'][0];
            $targetDir = env('UPLOAD_DIR', 'uploads/');
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $targetFile = $targetDir . basename($fileName);

            // Check if file with same name already exists (excluding current record)
            $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM Portfolio_Documents WHERE File_Name = ? AND ID <> ?");
            $check->bind_param('si', $fileName, $docId);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc()['cnt'] > 0;
            $check->close();

            if ($exists || file_exists($targetFile)) {
                // If the existing file is not the old file, prevent overwriting another user's file
                if (realpath($targetFile) !== realpath($oldFilePath)) {
                    goHome('error=file_exists');
                }
                // else it's the same file, we can proceed to overwrite.
            }

            if (move_uploaded_file($fileTmp, $targetFile)) {
                // Delete old file from disk if it's different from new file
                if (realpath($oldFilePath) !== realpath($targetFile) && file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
                $newFileName = $fileName;
                $newFilePath = $targetFile;
                $fileUpdated = true;
            } else {
                goHome('error=upload_failed');
            }
        } else {
            // No new file uploaded, keep existing file
            $newFileName = $oldFileName;
            $newFilePath = $oldFilePath;
        }

        // Update database record
        $stmt = $conn->prepare("UPDATE Portfolio_Documents SET File_Name = ?, File_Path = ? WHERE ID = ?");
        $stmt->bind_param('ssi', $newFileName, $newFilePath, $docId);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            goHome('doc_updated=1');
        } else {
            $stmt->close();
            $conn->close();
            goHome('error=db_update_failed');
        }
    }
}

// ============================================================
// GET — display the edit form
// ============================================================
$docId = (int) ($_GET['id'] ?? 0);

if ($docId <= 0) {
    goHome('error=invalid_id');
}

$sel = $conn->prepare("SELECT * FROM Portfolio_Documents WHERE ID = ? LIMIT 1");
$sel->bind_param('i', $docId);
$sel->execute();
$doc = $sel->get_result()->fetch_assoc();
$sel->close();
$conn->close();

if (!$doc) {
    goHome('error=not_found');
}

$fieldError = isset($_GET['error']) && $_GET['error'] === 'missing_fields';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document — <?= htmlspecialchars($doc['File_Name']) ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Shared portfolio styles (two levels up) -->
    <link rel="stylesheet" href="../../style.css">
</head>
<body class="d-flex align-items-start justify-content-center min-vh-100 py-5">

    <div class="card-dark p-4 w-100" style="max-width: 500px; border-radius: 15px;">

        <!-- Header -->
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil me-2"></i>Edit Document
        </h4>
        <p class="text-muted small mb-4">
            Make your changes below, then choose <strong>Save</strong>,
            <strong>Delete</strong>, or <strong>Cancel</strong>.
        </p>

        <?php if ($fieldError): ?>
            <div class="alert alert-danger mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                File name is required.
            </div>
        <?php endif; ?>

        <!-- ── EDIT FORM ─────────────────────────────────────── -->
        <form method="POST"
              action="updatedoc.php"
              enctype="multipart/form-data"
              id="editForm">

            <input type="hidden" name="doc_id" value="<?= $docId ?>">
            <input type="hidden" name="action"     value="update" id="formAction">

            <!-- Current File -->
            <div class="mb-3">
                <label class="form-label">Current File</label>
                <p class="form-control-plaintext">
                    <?= htmlspecialchars($doc['File_Name']) ?>
                </p>
                <?php if ($doc['File_Path'] && file_exists('../../' . $doc['File_Path'])): ?>
                    <p class="text-muted small">
                        <a href="../../<?= htmlspecialchars($doc['File_Path']) ?>" download>
                            <i class="bi bi-download me-1"></i>Download current file
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <!-- New File (optional) -->
            <div class="mb-3">
                <label class="form-label">New File (optional)</label>
                <input type="file"
                       name="document[]"
                       class="form-control"
                       accept=".pdf,.doc,.docx,.txt">
                <div class="form-text text-muted">
                    <i class="bi bi-info-circle me-1"></i>Accepted: PDF, DOC, DOCX, TXT
                </div>
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

                <!-- Delete document -->
                <button type="submit"
                        class="action-button ms-auto"
                        style="background: #c0392b;"
                        onclick="if(!confirmDelete()) return false; setAction('delete'); return true;">
                    <i class="bi bi-trash me-1"></i>Delete Document
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
                'Are you sure you want to permanently delete this document?\n' +
                'This cannot be undone.'
            );
        }
    </script>
</body>
</html>