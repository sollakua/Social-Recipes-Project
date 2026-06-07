<?php
require_once __DIR__ . '/config/db.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? ''); 
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed_password]);
            $message = "<div style='background-color: #eafaf1; color: #27ae60; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align:center;'>Η εγγραφή ολοκληρώθηκε! <a href='login.php' style='color:#219150; font-weight:bold; text-decoration:none;'>Συνδεθείτε εδώ</a></div>";
        } catch (PDOException $e) {
            $message = ($e->getCode() == 23000) 
                ? "<div style='background-color: #fdeaea; color: #d93025; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align:center;'>Το email υπάρχει ήδη.</div>" 
                : "<div style='background-color: #fdeaea; color: #d93025; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align:center;'>" . $e->getMessage() . "</div>";
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
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary: #27ae60;
            --primary-hover: #219150;
            --dark: #1a1a1a;
            --light-bg: #e2e8f0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .signup-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .signup-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 450px;
            box-sizing: border-box;
        }

        .signup-card h2 {
            margin: 0 0 30px 0;
            color: var(--dark);
            font-size: 28px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
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

    <div class="signup-wrapper">
        <div class="signup-card">
            <h2>Εγγραφή</h2>
            
            <?php echo $message; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Όνομα Χρήστη</label>
                    <input type="text" name="username" placeholder="Username" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="form-group">
                    <label>Κωδικός Πρόσβασης</label>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-submit">Εγγραφή</button>
            </form>
        </div>
    </div>

</body>
</html>