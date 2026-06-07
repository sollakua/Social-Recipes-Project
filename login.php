<?php
// login.php

session_start();
require_once __DIR__ . '/config/db.php';

$error = "";

// έλεγχος αν υποβλήθηκε η φόρμα
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // αναζήτηση χρήστη στη βάση
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // επαλήθευση κωδικού και έναρξη session
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Λάθος email ή κωδικός πρόσβασης.";
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Σύνδεση - Social Recipes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #27ae60;
            --primary-hover: #219150;
            --dark: #1a1a1a;
            --light-bg: #e2e8f0;
            --gray-text: #64748b;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* στυλ για το κεντρικό box σύνδεσης */
        .login-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0,0,0,0.02);
            width: 100%;
            max-width: 410px;
            box-sizing: border-box;
            text-align: center;
        }

        .login-card h2 {
            margin: 0 0 10px 0;
            color: var(--dark);
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.8px;
        }

        .login-card p {
            color: var(--gray-text);
            font-size: 15px;
            margin-bottom: 35px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            color: #94a3b8;
            font-size: 16px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
            font-size: 15px;
            color: var(--dark);
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1);
        }

        /* εικονίδιο εμφάνισης/απόκρυψης κωδικού */
        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: #94a3b8;
            transition: 0.2s;
        }
        .toggle-password:hover { color: var(--primary); }

        .btn-login {
            width: 100%;
            padding: 15px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.2);
        }

        .btn-login:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(39, 174, 96, 0.3);
        }

        /* σφάλματα */
        .error-msg {
            background-color: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .register-box {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            color: var(--gray-text);
            font-size: 14px;
        }

        .register-box a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .register-box a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <header style="background: var(--light-bg); border-bottom: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 10px 0;">
        <div style="width: 100%; box-sizing: border-box; padding: 0 40px; display: flex; justify-content: space-between; align-items: center;">
            <div style="cursor:pointer; display:flex; align-items:center;" onclick="location.href='index.php'">
                <img src="uploads/Social Recipes Logo.png" alt="Social Recipes Logo" style="height: 150px; width: auto; object-fit: contain; margin: -35px 0;">
            </div>
            <nav style="display: flex; align-items: center;">
                <a href="register.php" style="color: var(--dark); font-weight: 500; text-decoration:none; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--dark)'">Εγγραφή</a>
            </nav>
        </div>
    </header>

    <div class="login-wrapper">
        <div class="login-card">
            <h2>Καλώς ήρθες!</h2>
            <p>Συνδέσου για να μοιραστείς τη δική σου συνταγή.</p>
            
            <?php if ($error): ?>
                <div class="error-msg">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="Όνομα χρήστη ή email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Κωδικός Πρόσβασης</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="passwordField" placeholder="Κωδικός πρόσβασης" required>
                        <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">Είσοδος</button>
            </form>

            <div class="register-box">
                Δεν έχεις λογαριασμό; <a href="register.php">Δημιούργησε έναν</a>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#passwordField');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>