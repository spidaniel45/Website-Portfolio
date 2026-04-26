<?php
/**
 * GamExplorer Admin Interface
 * Secure admin dashboard with role verification
 */

// Include security middleware and database config
require_once 'db_configuration.php';

// SECURITY: Require admin authentication
requireAdmin();

// Protect this admin page
requireAdmin();

// Generate CSRF token for forms
$csrfToken = generateCSRFToken();

// Get database connection
try {
    $conn = getDBConnection();
} catch (Exception $e) {
    die("System error. Please contact administrator.");
}

// Get admin user information
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT Username, Email, Name, Profile_Image FROM Registered_Users WHERE ID = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$adminUser = $result->fetch_assoc();
$stmt->close();

// Set profile image
$imagePath = "assets/default_profile.png";
if (!empty($adminUser['Profile_Image'])) {
    $cleanFilename = basename($adminUser['Profile_Image']);
    $imagePath = "uploads/" . htmlspecialchars($cleanFilename, ENT_QUOTES, 'UTF-8');
}

// Get statistics for dashboard
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as total FROM Registered_Users");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Total admins
$result = $conn->query("SELECT COUNT(*) as total FROM Registered_Users WHERE role = 'admin'");
$stats['total_admins'] = $result->fetch_assoc()['total'];

// Recent registrations (last 7 days)
$result = $conn->query("SELECT COUNT(*) as total FROM Registered_Users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['recent_users'] = $result->fetch_assoc()['total'];

// Get recent users list
$recentUsers = [];
$result = $conn->query("SELECT ID, Username, Email, Name, role, created_at FROM Registered_Users ORDER BY created_at DESC LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $recentUsers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- SECURITY: Content Security Policy -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';">
    <title>GamExplorer - Admin Dashboard</title>
    <link rel="stylesheet" href="GamExplorer_Design.css">
    <style>
        .admin-badge {
            background: #ff0000;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 10px;
        }
        .stats-container {
            display: flex;
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            flex: 1;
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .users-table th {
            background: #333;
            color: white;
        }
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .role-admin {
            background: #ff4444;
            color: white;
        }
        .role-user {
            background: #4CAF50;
            color: white;
        }
        .action-btn {
            padding: 5px 10px;
            margin: 0 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
    </style>
</head>
<body>

    <!-- Admin Profile Container -->
    <div class="profile-container">
        <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" 
             alt="Admin Profile" 
             class="profile-image"
             onerror="this.src='assets/default_profile.png'">
        <span class="welcome-text">
            Welcome, <?= htmlspecialchars($adminUser['Username'], ENT_QUOTES, 'UTF-8') ?>
            <span class="admin-badge">ADMIN</span>
        </span>
    </div>

    <!-- Admin Navigation -->
    <header class="interface">
        <h2>Admin Dashboard</h2>
        <nav>
            <ul class="nav-menu">
                <li><a href="GamExplorer_Admin_Interface.php">Dashboard</a></li>
                <li><a href="admin_users.php">Manage Users</a></li>
                <li><a href="admin_content.php">Manage Content</a></li>
                <li><a href="admin_reports.php">Reports</a></li>
                <li><a href="admin_settings.php">Settings</a></li>
                <li><a href="Profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Admin Content -->
    <main class="admin-content">
        <h1>Dashboard Overview</h1>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?= htmlspecialchars($stats['total_users']) ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= htmlspecialchars($stats['total_admins']) ?></div>
                <div class="stat-label">Total Admins</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= htmlspecialchars($stats['recent_users']) ?></div>
                <div class="stat-label">New Users (7 days)</div>
            </div>
        </div>

        <!-- Recent Users Table -->
        <h2>Recent Users</h2>
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUsers as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['ID']) ?></td>
                    <td><?= htmlspecialchars($user['Username']) ?></td>
                    <td><?= htmlspecialchars($user['Email']) ?></td>
                    <td><?= htmlspecialchars($user['Name']) ?></td>
                    <td>
                        <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                            <?= strtoupper(htmlspecialchars($user['role'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars(date('M d, Y', strtotime($user['created_at']))) ?></td>
                    <td>
                        <form method="POST" action="admin_edit_user.php" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['ID']) ?>">
                            <button type="submit" class="action-btn btn-edit">Edit</button>
                        </form>
                        <form method="POST" action="admin_delete_user.php" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['ID']) ?>">
                            <button type="submit" class="action-btn btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <script src="GamExplorer_Functions.js"></script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>