<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Interface</title>
</head>
<body>

<?php
$conn = new mysqli("localhost", "root", "", "Portfolio_Daniel");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}


?>

<!-- ==========================================
     MAIN CONTENT AREA
     ========================================== -->
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

    <!-- ==========================================
         CONTENT SECTIONS
         ========================================== -->
    
    <!-- About Me Section -->
    <section id="about" class="content-section">
      <h2>About Me</h2>
      <p>I'm Still Learning</p>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="content-section">
      <h2>Skills</h2>
      <p>Front-End Tools: HTML, CSS, JavaScript</p>
      <p>Back-End Tools: Java, Python, PHP</p>
      <p>Database Management: MySQL, MariaDB</p>
      <p>Version Control: Git, GitHub</p>
      <p>Tools Used for Development: VS Code, XAMPP, Wordpress</p>
    </section>
    
    <!-- GitHub Stats Image -->
    <section id="git" class="content-section">
    <h2>GitHub History</h2>
    <a href="https://github.com/spidaniel45?tab=repositories" target="_blank">
    <img src="https://github-readme-stats.vercel.app/api?username=spidaniel45&show_icons=true&theme=dark" alt="GitHub Stats">
    <img src="https://ghchart.rshah.org/2ea44f/spidaniel45" alt="GitHub Contributions" class="githubcontributions">
    <img src="https://github-readme-activity-graph.vercel.app/graph?username=spidaniel45&theme=github-dark" alt="GitHub Activity Graph" class="githubactivitygraph">
    </a>
    
    <h3>My Projects</h3>

    <!-- ==========================================
         ADD PROJECT FORM
         ========================================== -->
    <form action="Upload_Project.php" method="POST" enctype="multipart/form-data">
        <h3>Add New Project</h3>
        <!-- Text input for project title -->
        <input type="text" name="title" placeholder="Project Title" required>
        
        <!-- Textarea for project description (multiline) -->
        <textarea name="description" placeholder="Project Description" rows="4" required></textarea>
        
        <!-- File input for project screenshot (accept attribute limits to images) -->
        <input type="file" name="screenshot" accept="image/*">
        
        <!-- URL input for project link (validates URL format) -->
        <input type="url" name="link" placeholder="Live Link or GitHub">
        
        <button type="submit" class="action-button">Add Project</button>
    </form>

    </section>

    <!-- ==========================================
         PROJECTS DISPLAY
         ========================================== -->

    <div class="project-list">
    <?php
    // Fetch all projects, newest first (DESC = descending order)
    $projects = $conn->query("SELECT * FROM Portfolio_Projects ORDER BY Date_Created DESC");
    
    if ($projects && $projects->num_rows > 0) {
        // Loop through each project and display it
        while ($proj = $projects->fetch_assoc()) {
            echo "<div class='project-item'>";
            
            // Display project title (htmlspecialchars prevents XSS)
            echo "<h4>" . htmlspecialchars($proj['Project_Title']) . "</h4>";
            
            // Display description (nl2br converts newlines to <br> tags for proper formatting)
            echo "<p>" . nl2br(htmlspecialchars($proj['Description'])) . "</p>";
            
            // Display screenshot if available
            if ($proj['Screenshot_Path']) {
                echo "<img src='" . htmlspecialchars($proj['Screenshot_Path']) . "' alt='Project Screenshot'>";
            }
            
            // Display project link if available (target='_blank' opens in new tab)
            if ($proj['Project_Link']) {
                echo "<p><a href='" . htmlspecialchars($proj['Project_Link']) . "' target='_blank'>View Project →</a></p>";
            }
            
            echo "</div>";
        }
    } else {
        // Display message if no projects exist
        echo "<p> </p>";
    }
    ?>
    </div>
</div>

<!-- ==========================================
     DOWNLOAD MODAL (View Documents)
     ========================================== -->
<div id="DownloadModal" class="modal">
    <div class="modal-content">
        <!-- Close button (× symbol) -->
        <span class="close">&times;</span>
        <h3>My Documents</h3>
        
        <div class="documents-list">
            <?php
            // Fetch all uploaded documents, newest first
            $docQuery = "SELECT File_Name, File_Path FROM Portfolio_Documents ORDER BY id DESC";
            $docResult = $conn->query($docQuery);

            if ($docResult && $docResult->num_rows > 0) {
                // Display each document with download and delete buttons
                while ($doc = $docResult->fetch_assoc()) {
                    // Sanitize output to prevent XSS attacks
                    $safeName = htmlspecialchars($doc['File_Name']);
                    $safePath = htmlspecialchars($doc['File_Path']);
                    
                    echo "<div class='document-item'>
                            <p>$safeName</p>
                            <div class='document-actions'>
                                <a class='download-link' href='$safePath' download>Download</a>
                                <a class='delete-link' 
                                   href='?delete=" . urlencode($doc['File_Name']) . "' 
                                   onclick='return confirm(\"Delete this file?\")'>Delete</a>
                            </div>
                          </div>";
                }
                // Show upload button after document list
                echo "<button id='UploadNewButton' class='action-button' style='margin-top: 15px;'>Upload New Document</button>";
            } else {
                // No documents found - show message and upload button
                echo "<p>No documents uploaded yet.</p>";
                echo "<button id='UploadNewButton' class='action-button' style='margin-top: 15px;'>Upload Document</button>";
            }
            ?>
        </div>
    </div>
</div>


<!-- ==========================================
     UPLOAD MODAL (Upload New Document)
     ========================================== -->
<div id="UploadModal" class="modal">
    <div class="modal-content">
        <span class="close-upload">&times;</span>
        <h3>Upload a Document</h3>
        
        <!-- Upload form submits to same page (empty action means current page) -->
        <!-- enctype="multipart/form-data" is REQUIRED for file uploads -->
        <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
            <!-- File input with multiple file support -->
            <!-- name="document[]" with brackets allows multiple file upload -->
            <!-- accept attribute limits file types users can select -->
            <input type="file" 
                   name="document[]" 
                   accept=".pdf,.doc,.docx,.txt" 
                   multiple 
                   required>
            <br><br>
            <button type="submit" class="action-button">Upload</button>
        </form>
    </div>
</div>

<?php 
// Close database connection when done (good practice to free resources)
$conn->close(); 
?>

<!-- ==========================================
     JAVASCRIPT LIBRARIES
     ========================================== -->

<!-- Custom JavaScript functions for modals and interactions -->
<script src="script.js"></script>

<!-- Ionicons library for social media icons -->
<!-- type="module" for modern browsers, nomodule for older browsers -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>