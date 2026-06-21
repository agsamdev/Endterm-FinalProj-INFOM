<?php
session_start();

// 1. Check if the user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    
    // 2. Check the role
    // We use strtolower to ensure "User" or "USER" both match
    if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'user') {
        
        // Redirect standard users to the users list
        header("Location: user.php");
        exit;
        
    } else {
        // If they are an Admin, you might want them to stay on a 
        // custom error page or go to the dashboard
        header("Location: admin.php?error=page_not_found");
        exit;
    }
}

// 3. If not logged in at all, send to login
header("Location: login.php");
exit;