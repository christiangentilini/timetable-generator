<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Fetch user's profile path if not already in session
if (!isset($_SESSION['profile_path'])) {
    require_once __DIR__ . '/database.php';
    $stmt = $conn->prepare("SELECT profile_path FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $_SESSION['profile_path'] = $user['profile_path'];
    }
    $stmt->close();
}
?>