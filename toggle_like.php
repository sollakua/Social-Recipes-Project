<?php
// toggle_like.php

require_once __DIR__ . '/lang.php'; 
require_once __DIR__ . '/config/db.php';

// έλεγχος αν ο χρήστης είναι συνδεδεμένος και αν υπάρχει έγκυρο ID
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$recipe_id = (int)$_GET['id'];

try {
    // έλεγχος αν ο χρήστης έχει ήδη κάνει like
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND recipe_id = ?");
    $stmt->execute([$user_id, $recipe_id]);
    $like = $stmt->fetch();

    if ($like) {
        // αφαίρεση του like (unlike)
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
    } else {
        // προσθήκη νέου like
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, recipe_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $recipe_id]);
    }
} catch (PDOException $e) {
    // διαχείριση σφάλματος
}

// επιστροφή στην προηγούμενη σελίδα
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: " . $redirect);
exit();
?>