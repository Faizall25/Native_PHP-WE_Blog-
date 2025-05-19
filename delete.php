<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $article_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM article WHERE article_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $article_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: profile.php");
    exit;
}
