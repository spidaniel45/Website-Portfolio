<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["playerName"])) {
    $_SESSION["playerName"] = trim($_POST["playerName"]);
}

if (empty($_SESSION["playerName"])) {
    echo "<script>alert('Name is required to play.'); window.location.href='Index.php';</script>";
    exit;
}

$playerName = $_SESSION["playerName"];
$score = isset($_POST['score']) ? intval($_POST['score']) : 0;

// Connect to database
$conn = new mysqli("localhost", "root", "", "Tetris_Scores");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($score > 0) {
    $stmt = $conn->prepare("INSERT INTO Player_Scores (Name, Scores) VALUES (?, ?)");
    $stmt->bind_param("si", $playerName, $score);
    $stmt->execute();
    $stmt->close();
}

$leaderboard = [];
$result = $conn->query("SELECT Name, Scores FROM Player_Scores ORDER BY Scores DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
    $result->free();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tetris</title>
  <link rel="stylesheet" href="Tetris.css">
</head>
<body>

<h1>Tetris</h1>
<canvas id="tetris" width="240" height="400"></canvas>

<div id="gameOverPopup" class="popup" style="display:none;">
  <h2>Game Over</h2>
  <button onclick="restartGame()">Play Again</button>
</div>

<p>Score: <span id="score">0</span></p>

<div id="leaderboard">
  <h2>🏆 Leaderboard</h2>
  <table>
    <tr>
      <th>Rank</th>
      <th>Name</th>
      <th>Score</th>
    </tr>
    <?php
    $rank = 1;
    foreach ($leaderboard as $row) {
        echo "<tr>";
        echo "<td>{$rank}</td>";
        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
        echo "<td>" . $row['Scores'] . "</td>";
        echo "</tr>";
        $rank++;
    }
    ?>
  </table>
</div>

<script src="Tetris.js"></script>
<script>
function restartGame() {
  location.reload();
}
</script>

</body>
</html>