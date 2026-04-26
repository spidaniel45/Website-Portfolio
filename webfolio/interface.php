<?php 

require_once __DIR__ . '/../config/database.php';

  $sql = "SELECT CONCAT(First_Name, ' ', Middle_Name, ' ', Last_Name) AS FullName FROM Portfolio_Profile LIMIT 1";
  $result = $conn->query($sql);
  
  // Use fetched name or default if no record found
  $fullName = ($result && $result->num_rows > 0) 
      ? $result->fetch_assoc()['FullName'] 
      : "Daniel Coton Evangelista";
?>  
  <img src="Profile.jpg" alt="Profile Picture" class="circle-pic">
  
  <div class="profile-info">
    <!-- htmlspecialchars prevents XSS attacks by escaping HTML characters -->
    <h2><?= htmlspecialchars($fullName) ?></h2>
    
    <div class="social-icons">
      <!-- Social media links with icons -->
      <a href="https://github.com/spidaniel45" target="_blank">
        <ion-icon name="logo-github"></ion-icon> @spidaniel45
      </a>
      <a href="https://www.linkedin.com/in/daniel-c-evangelista-729793389/" target="_blank">
        <ion-icon name="logo-linkedin"></ion-icon> Daniel C. Evangelista
      </a>
      <a href="mailto:daniellora583@gmail.com">
        <ion-icon name="mail-outline"></ion-icon> daniellora583@gmail.com
      </a>
      <a href="tel:+639272858696">
        <ion-icon name="call-outline"></ion-icon> (+63)9272858696
      </a>
    </div>
  </div>
</div>