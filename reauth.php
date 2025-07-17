<?php
    session_start();
    include './database/connection2.php';

    $password = $_POST['password'];
    $username = $_SESSION['user_name'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM sdn_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user['password'] === $password) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    // Test RHU
?>
