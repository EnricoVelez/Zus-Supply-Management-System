<?php
session_start();
session_destroy(); 
header("Location: ZusLogin.php");  // Redirect to the login page
exit();  
?>