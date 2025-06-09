<?php
require_once '../config/database.php';
require_once '../config/session_check.php';

// Ensure this is an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access not allowed');
}

// Get search term from request
$search_term = isset($_GET['term']) ? trim($_GET['term']) : '';

// Validate search term
if (empty($search_term) || strlen($search_term) < 2) {
    echo json_encode(['success' => false, 'message' => 'Termine di ricerca troppo breve']);
    exit;
}

// Get current user ID to exclude from results
$current_user_id = $_SESSION['user_id'];

// Search for users by username or email
$stmt = $conn->prepare("SELECT id, username, nome, cognome, email FROM users 
                      WHERE (username LIKE ? OR email LIKE ?) AND id != ? 
                      ORDER BY username LIMIT 10");
$search_param = "%{$search_term}%";
$stmt->bind_param("ssi", $search_param, $search_param, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($user = $result->fetch_assoc()) {
    $users[] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'nome' => $user['nome'],
        'cognome' => $user['cognome'],
        'email' => $user['email'],
        'display' => $user['username'] . ' (' . $user['nome'] . ' ' . $user['cognome'] . ')',
    ];
}

// Return results as JSON
echo json_encode(['success' => true, 'users' => $users]);