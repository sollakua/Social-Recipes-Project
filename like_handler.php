<?php
// like_handler.php

session_start();
require_once __DIR__ . '/config/db.php';

// παίρνουμε το id της συνταγής
$recipe_id = $_GET['recipe_id'] ?? null;

// αν δεν είναι συνδεδεμένος ή δεν υπάρχει id, επιστροφή στην αρχική
if (!isset($_SESSION['user_id']) || !$recipe_id) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // έλεγχος αν υπάρχει ήδη το like στη βάση
    $check_stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND recipe_id = ?");
    $check_stmt->execute([$user_id, $recipe_id]);
    $existing_like = $check_stmt->fetch();

    if ($existing_like) {
        // αν υπάρχει, το αφαιρούμε (unlike)
        $delete_stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND recipe_id = ?");
        $delete_stmt->execute([$user_id, $recipe_id]);
    } else {
        // αν δεν υπάρχει, το προσθέτουμε (like)
        $insert_stmt = $pdo->prepare("INSERT INTO likes (user_id, recipe_id) VALUES (?, ?)");
        $insert_stmt->execute([$user_id, $recipe_id]);
    }
} catch (PDOException $e) {
    // σφάλμα βάσης
}

// επιστροφή στην προηγούμενη σελίδα για ανανέωση του UI
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: index.php");
}
exit();
?>