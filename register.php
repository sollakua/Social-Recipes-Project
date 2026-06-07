<?php
// register.php

session_start();
require_once __DIR__ . '/config/db.php';

$error = "";
$success = "";

// έλεγχος αν υποβλήθηκε η φόρμα
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // έλεγχος αν υπάρχει ήδη το email στη βάση
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "Αυτό το email χρησιμοποιείται ήδη.";
    } else {
        // κρυπτογράφηση κωδικού και εισαγωγή νέου χρήστη
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($insert->execute([$username, $email, $hashed_password])) {
            $success = "Η εγγραφή ολοκληρώθηκε! Μπορείτε να συνδεθείτε.";
        } else {
            $error = "Κάτι πήγε στραβά. Δοκιμάστε ξανά.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Εγγραφή - Social Recipes</title>
    <style>
        :root {
            --primary: #27ae60;
            --primary-hover: #219150;
            --dark: #1a1a1a;
            --light-bg: #e2e8f0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* στυλ για το κεντρικό box εγγραφής */
        .form-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .form-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .form-card h2 {
            margin-bottom: 30px;
            color: var(--dark);
            font-size: 28px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            outline: none;
            transition: 0.3s;
            box-sizing: border-box;
            font-size: 16px;
        }

        .form-group input:focus {
            border-color: var(--primary);
            background-color: #f9fffb;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        /* μηνύματα σφάλματος / επιτυχίας */
        .message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error-msg { background-color: #fdeaea; color: #d93025; }
        .success-msg { background-color: #eafaf1; color: #27ae60; }

        .login-link {
            margin-top: 25px;
            color: #777;
            font-size: 14px;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <header style="background: var(--light-bg); border-bottom: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 10px 0;">
        <div style="width: 100%; box-sizing: border-box; padding: 0 40px; display: flex; justify-content: space-between; align-items: center;">
            <div style="cursor:pointer; display:flex; align-items:center;" onclick="location.href='index.php'">
                <img src="uploads/Social Recipes Logo.png" alt="Social Recipes Logo" style="height: 150px; width: auto; object-fit: contain; margin: -35px 0;">
            </div>
            <nav style="display: flex; align-items: center;">
                <a href="login.php" style="color: var(--dark); font-weight: 500; text-decoration:none; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--dark)'">Σύνδεση</a>
            </nav>
        </div>
    </header>

    <div class="form-wrapper">
        <div class="form-card">
            <h2>Δημιουργία Λογαριασμού</h2>
            
            <?php if ($error): ?>
                <div class="message error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success-msg"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label>Όνομα Χρήστη</label>
                    <input type="text" name="username" placeholder="π.χ. Ardit" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="π.χ. ardit@gmail.com" required>
                </div>

                <div class="form-group">
                    <label>Κωδικός Πρόσβασης</label>
                    <input type="password" name="password" placeholder="Τουλάχιστον 6 χαρακτήρες" required>
                </div>

                <button type="submit" class="btn-submit">Εγγραφή</button>
            </form>

            <div class="login-link">
                Έχετε ήδη λογαριασμό; <a href="login.php">Συνδεθείτε εδώ</a>
            </div>
        </div>
    </div>

</body>
</html>