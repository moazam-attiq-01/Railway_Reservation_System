<?php
session_start(); // Start the session
session_destroy(); // Destroy all session data
header("Location: Home.php"); // Redirect to login page
exit(); // Ensure no further code is executed
?>
