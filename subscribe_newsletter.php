<?php
// newsletter.php

require_once __DIR__ . '/config/db.php';

// έλεγχος αν υποβλήθηκε το email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    
    // καθαρισμός email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // έλεγχος εγκυρότητας email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            // εισαγωγή στη βάση
            $stmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)");
            $stmt->execute([$email]);
            
            header("Location: index.php?newsletter=success");
        } catch (PDOException $e) {
            // σφάλμα αν το email υπάρχει ήδη
            header("Location: index.php?newsletter=exists");
        }
    } else {
        // μη έγκυρη μορφή email
        header("Location: index.php?newsletter=invalid");
    }
    
    exit();
}
?>