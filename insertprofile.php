<?php
// ============================================================
// insertprofile.php
// Student profile creation — reads DB from .env.
// ============================================================
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config/database.php';

$conn    = studentDb();
$success = false;
$error   = null;

if (isset($_POST['Submit'])) {
    $firstName  = trim($_POST['FirstName']  ?? '');
    $middleName = trim($_POST['MiddleName'] ?? '');
    $lastName   = trim($_POST['LastName']   ?? '');

    if ($firstName === '' || $lastName === '') {
        $error = 'First Name and Last Name are required.';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO tblstudentprofile (FirstName, MiddleName, LastName) VALUES (?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param('sss', $firstName, $middleName, $lastName);
            $success = $stmt->execute();
            if (!$success) {
                $error = 'Could not save record. Please try again.';
                error_log('[insertprofile] Execute failed: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            $error = 'Server error. Please contact support.';
            error_log('[insertprofile] Prepare failed: ' . $conn->error);
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

    <div class="card-dark p-4" style="width: 100%; max-width: 440px; border-radius: 15px;">
        <h4 class="mb-4 text-center fw-bold">
            <i class="bi bi-person-plus me-2"></i>Student Profile
        </h4>

        <?php if ($success): ?>
            <div class="alert alert-brand text-center mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Record inserted successfully!
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" novalidate>
            <div class="mb-3">
                <label for="FirstName" class="form-label text-muted small">First Name *</label>
                <input type="text" id="FirstName" name="FirstName"
                       class="form-control form-control-dark"
                       placeholder="First Name" required
                       value="<?= htmlspecialchars($_POST['FirstName'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="MiddleName" class="form-label text-muted small">Middle Name</label>
                <input type="text" id="MiddleName" name="MiddleName"
                       class="form-control form-control-dark"
                       placeholder="Middle Name"
                       value="<?= htmlspecialchars($_POST['MiddleName'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="LastName" class="form-label text-muted small">Last Name *</label>
                <input type="text" id="LastName" name="LastName"
                       class="form-control form-control-dark"
                       placeholder="Last Name" required
                       value="<?= htmlspecialchars($_POST['LastName'] ?? '') ?>">
            </div>
            <button type="submit" name="Submit" class="action-button w-100 mt-1">
                <i class="bi bi-send me-2"></i>Submit your Record
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>