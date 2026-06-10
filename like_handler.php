<?php
// like_handler.php

session_start();
require_once __DIR__ . '/config/db.php';

// Παίρνουμε το id της συνταγής (είτε από GET είτε από AJAX)
$recipe_id = $_GET['recipe_id'] ?? null;

// Έλεγχος αν το αίτημα είναι AJAX (αν περιέχει την παράμετρο ajax=1)
$is_ajax = isset($_GET['ajax']);

// Αν δεν είναι συνδεδεμένος ή δεν υπάρχει id
if (!isset($_SESSION['user_id']) || !$recipe_id) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Μη εξουσιοδοτημένη πρόσβαση.']);
        exit();
    }
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = '';

try {
    // Έλεγχος αν υπάρχει ήδη το like
    $check_stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND recipe_id = ?");
    $check_stmt->execute([$user_id, $recipe_id]);
    $existing_like = $check_stmt->fetch();

    if ($existing_like) {
        // Αν υπάρχει, το αφαιρούμε (unlike)
        $delete_stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND recipe_id = ?");
        $delete_stmt->execute([$user_id, $recipe_id]);
        $action = 'unliked';
    } else {
        // Αν δεν υπάρχει, το προσθέτουμε (like)
        $insert_stmt = $pdo->prepare("INSERT INTO likes (user_id, recipe_id) VALUES (?, ?)");
        $insert_stmt->execute([$user_id, $recipe_id]);
        $action = 'liked';
    }

    // Παίρνουμε το νέο σύνολο των likes για να ενημερωθεί η JS
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE recipe_id = ?");
    $count_stmt->execute([$recipe_id]);
    $total_likes = $count_stmt->fetchColumn();

    // ΑΠΑΝΤΗΣΗ AJAX: Αν το αίτημα έγινε μέσω JS, επέστρεψε JSON και σταμάτα εδώ
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'action' => $action,
            'total_likes' => $total_likes
        ]);
        exit();
    }

} catch (PDOException $e) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Σφάλμα βάσης δεδομένων.']);
        exit();
    }
}

// ΠΑΡΑΔΟΣΙΑΚΟ FALLBACK: Αν για κάποιο λόγο η JS απενεργοποιηθεί, κάνει το παλιό redirect
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: index.php");
}
exit();
?>
