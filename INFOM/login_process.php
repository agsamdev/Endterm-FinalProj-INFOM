<?php
session_start();
require 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // password_verify handles the secure comparison
        if (password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true); // Protects against session hijacking
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['logged_in'] = true;

            // Update last_login (Addressing that #1067 error fix)
            $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update->bind_param("i", $user['id']);
            $update->execute();

            header("Location: index.php");
            exit;
        }
    }

    // If it reaches here, login failed
    $_SESSION['error'] = "Incorrect email or password.";
    header("Location: login.php");
    exit;
}