<?php
// delete_recipe.php

// αρχεία για session και βάση
require_once __DIR__ . '/lang.php'; 
require_once __DIR__ . '/config/db.php';

// αν δεν είναι συνδεδεμένος, πάει στο login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// παίρνουμε τα ids
$recipe_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($recipe_id) {
    try {
        // έλεγχος αν υπάρχει η συνταγή και σε ποιον ανήκει
        $stmt = $pdo->prepare("SELECT user_id, image_path FROM recipes WHERE id = ?");
        $stmt->execute([$recipe_id]);
        $recipe = $stmt->fetch();

        // πρέπει η συνταγή να είναι του χρήστη
        if ($recipe && $recipe['user_id'] == $user_id) {
            
            // σβήνουμε τη φωτογραφία από τον φάκελο
            if ($recipe['image_path'] && file_exists("uploads/" . $recipe['image_path'])) {
                unlink("uploads/" . $recipe['image_path']);
            }

            // διαγραφή από τη βάση
            $delete_stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
            $delete_stmt->execute([$recipe_id]);
        }
    } catch (PDOException $e) {
        // αν σκάσει η βάση
        header("Location: index.php?error=delete_failed");
        exit();
    }
}

// επιστροφή στην αρχική
header("Location: index.php");
exit();
?>