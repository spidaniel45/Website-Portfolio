<?php
// ============================================================
// api/projects.php
// Lightweight JSON endpoint — returns all Portfolio_Projects
// ordered newest-first. Called by the auto-refresh poller in
// index.php every 30 seconds.
//
// Response shape:
//   { "hash": "<md5>", "projects": [ { ...row }, ... ] }
//
// The hash lets the client skip a DOM rebuild when nothing changed.
// ============================================================
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
// Allow only same-origin requests (no CORS needed for a local portfolio)
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/database.php';

$conn = portfolioDb();

$rows   = [];
$result = $conn->query(
    "SELECT ID, Project_Title, Description, Screenshot_Path, Project_Link, Date_Created
     FROM Portfolio_Projects
     ORDER BY Date_Created DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$conn->close();

// Build a fast change-detection hash from IDs + titles
$hashInput = implode('|', array_map(
    fn($r) => $r['ID'] . $r['Project_Title'],
    $rows
));

echo json_encode([
    'hash'     => md5($hashInput),
    'projects' => $rows,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);