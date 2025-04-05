<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get POST data
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate input
if (!$post_id || empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Insert comment
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, name, email, content) VALUES (?, ?, ?, ?)");
    $stmt->execute([$post_id, $name, $email, $message]);

    // Update comment count
    $stmt = $pdo->prepare("UPDATE blog_posts SET comments_count = comments_count + 1 WHERE id = ?");
    $stmt->execute([$post_id]);

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Comment posted successfully',
        'comment' => [
            'name' => $name,
            'content' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    http_response_code(500);
    echo json_encode(['error' => 'Failed to post comment']);
}
