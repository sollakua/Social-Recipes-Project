<?php
// upload_recipe.php
session_start();
require_once 'config/db.php';

// έλεγχος αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// έλεγχος αν υποβλήθηκε η φόρμα
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $instructions = trim($_POST['instructions']);
    $user_id = $_SESSION['user_id'];

    // διαχείριση αρχείου εικόνας
    $image_name = $_FILES['recipe_image']['name'];
    $image_tmp  = $_FILES['recipe_image']['tmp_name'];
    $image_size = $_FILES['recipe_image']['size'];
    
    $target_dir = __DIR__ . "/uploads/"; 
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $new_image_name = uniqid('recipe_', true) . "." . $file_ext;
    $target_file = $target_dir . $new_image_name;

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // έλεγχος τύπου και μεγέθους εικόνας (max 5MB)
    if (in_array($file_ext, $allowed_types) && $image_size < 5000000) {
        
        if (move_uploaded_file($image_tmp, $target_file)) {
            try {
                // εισαγωγή συνταγής στη βάση
                $sql = "INSERT INTO recipes (user_id, title, description, instructions, image_path) 
                        VALUES (:u_id, :title, :descr, :instr, :img)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'u_id'   => $user_id,
                    'title'  => $title,
                    'descr'  => $description,
                    'instr'  => $instructions,
                    'img'    => $new_image_name
                ]);
                $message = "<div class='alert success-msg'>Η συνταγή ανέβηκε με επιτυχία!</div>";
            } catch (PDOException $e) {
                $message = "<div class='alert error-msg'>Σφάλμα βάσης: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            
        } else {
            $message = "<div class='alert error-msg'>Αποτυχία μεταφόρτωσης εικόνας.</div>";
        }
    } else {
        $message = "<div class='alert error-msg'>Μη αποδεκτός τύπος αρχείου ή πολύ μεγάλη εικόνα (Μέγιστο 5MB).</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ανέβασμα Συνταγής - Social Recipes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #27ae60;
            --primary-hover: #219150;
            --dark: #1a1a1a;
            --light-bg: #e2e8f0;
            --text-gray: #4a5568;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            color: var(--dark);
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .upload-container {
            background: white;
            width: 100%;
            max-width: 700px;
            margin: 40px auto;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            box-sizing: border-box;
        }

        h1 { font-size: 32px; color: var(--dark); margin-top: 0; margin-bottom: 8px; font-weight: 800; letter-spacing: -0.5px; }
        .subtitle { color: var(--text-gray); margin-bottom: 30px; font-size: 15px; }
        .form-group { margin-bottom: 24px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--dark); font-size: 15px; }
        input[type="text"], textarea {
            width: 100%; padding: 14px 18px; border: 2px solid #edf2f7; border-radius: 12px; font-size: 16px; 
            font-family: inherit; background-color: #fff; transition: all 0.3s ease; box-sizing: border-box; outline: none;
        }
        input[type="text"]:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1); }
        textarea { resize: vertical; min-height: 120px; line-height: 1.6; }

        .file-upload-wrapper { position: relative; display: block; }
        .file-upload-wrapper input[type="file"] { position: absolute; left: 0; top: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
        .custom-file-upload { 
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; 
            padding: 30px 20px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 14px; text-align: center; color: #64748b; font-weight: 500; transition: all 0.3s ease; 
        }
        .file-upload-wrapper:hover .custom-file-upload { border-color: var(--primary); background: rgba(39, 174, 96, 0.02); color: var(--primary); }

        .btn-submit {
            width: 100%; background-color: var(--primary); color: white; padding: 16px 20px; border: none; border-radius: 12px; 
            font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s ease; margin-top: 10px; box-shadow: 0 4px 12px rgba(39, 174, 96, 0.15);
        }
        .btn-submit:hover { background-color: var(--primary-hover); }

        .btn-back {
            display: inline-flex; align-items: center; background-color: white; color: var(--dark) !important; 
            padding: 10px 20px; text-decoration: none; border-radius: 50px; font-weight: 600; border: 1px solid #cbd5e1; transition: 0.3s; margin-bottom: 25px;
        }
        .btn-back:hover { background-color: var(--dark); color: white !important; }

        .alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; font-size: 15px; }
        .success-msg { background-color: #def7ec; color: #03543f; border: 1px solid #bcf0da; }
        .error-msg { background-color: #fde8e8; color: #9b1c1c; border: 1px solid #fbd5d5; }

        .footer-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; padding: 0 20px; text-align: left; }
        .footer-section h3 { color: var(--dark) !important; margin-bottom: 20px; font-size: 18px; border-left: 3px solid var(--primary) !important; padding-left: 10px; }
        .footer-links { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #4a5568 !important; text-decoration: none; transition: 0.3s; font-weight: 500; }
        .footer-links a:hover { color: var(--primary) !important; padding-left: 5px; }
    </style>
</head>
<body>

    <header style="background: var(--light-bg); border-bottom: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 10px 0;">
        <div style="width: 100%; box-sizing: border-box; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; gap: 30px;">
            <div style="cursor:pointer; display:flex; align-items:center; flex-shrink: 0;" onclick="location.href='index.php'">
                <img src="uploads/Social Recipes Logo.png" alt="Social Recipes Logo" style="height: 150px; width: auto; object-fit: contain; margin: -35px 0;">
            </div>
            <nav style="display: flex; gap: 25px; align-items: center; flex-shrink: 0;">
                <a href="upload_recipe.php" style="background: var(--primary); color: white; padding: 10px 20px; border-radius: 50px; font-weight: bold; text-decoration: none;">+ ΣΥΝΤΑΓΗ</a>
                
                <a href="profile.php" title="Το Προφίλ μου" style="text-decoration:none; font-size: 20px;">
                    <span style="color: var(--primary);">👤</span>
                </a>
                
                <a href="logout.php" style="color: var(--dark); font-weight: 500; text-decoration:none;">Έξοδος</a>
            </nav>
        </div>
    </header>

    <main style="max-width: 950px; margin: 40px auto; padding: 0 20px; flex: 1; width: 100%; box-sizing: border-box;">
        <a href="index.php" class="btn-back"> Επιστροφή στην Αρχική</a>
        <div class="upload-container">
            <h1>Μοιραστείτε τη συνταγή σας</h1>
            <p class="subtitle">Γεμίστε τα παρακάτω πεδία και εμπνεύστε την κοινότητα με τις μαγειρικές σας δημιουργίες!</p>
            
            <?php echo $message; ?>

            <form action="upload_recipe.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Τίτλος Συνταγής</label>
                    <input type="text" id="title" name="title" placeholder="π.χ Αυθεντική Ιταλική Καρμπονάρα" required>
                </div>
                <div class="form-group">
                    <label for="description">Υλικά & Σύντομη Περιγραφή</label>
                    <textarea id="description" name="description" rows="5" placeholder="Λίστα υλικών και ποσότητες..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="instructions">Οδηγίες Εκτέλεσης</label>
                    <textarea id="instructions" name="instructions" rows="6" placeholder="Περιγράψτε βήμα-βήμα τη διαδικασία..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Φωτογραφία Πιάτου</label>
                    <div class="file-upload-wrapper">
                        <div class="custom-file-upload" id="file-label">
                            <i class="fa-solid fa-camera" style="font-size: 24px; margin-bottom: 5px;"></i>
                            <span>Επιλέξτε μια λαχταριστή εικόνα (Max 5MB)</span>
                        </div>
                        <input type="file" name="recipe_image" id="recipe_image" accept="image/*" required onchange="updateFileName()">
                    </div>
                </div>
                <button type="submit" class="btn-submit">Δημοσίευση Συνταγής στο Social Recipes</button>
            </form>
        </div>
    </main>

    <footer style="background: #e2e8f0 !important; color: #4a5568 !important; padding: 50px 0 20px 0; margin-top: 50px; font-size: 14px; border-top: 1px solid #cbd5e1 !important;">
        <div class="footer-container">
            <div class="footer-section">
                <h3 style="border-left:none !important; padding:0; color: var(--dark) !important;">Social <span style="color:var(--primary);">Recipes</span></h3>
                <p style="color: #4a5568 !important;">Η νούμερο 1 κοινότητα για να ανακαλύπτεις και να μοιράζεσαι τις καλύτερες συνταγές.</p>
            </div>
            <div class="footer-section">
                <h3>Γρήγοροι Σύνδεσμοι</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Αρχική Σελίδα</a></li>
                    <li><a href="index.php?sort=popular">Δημοφιλή</a></li>
                    <li><a href="contact.php">Επικοινωνία</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p style="color: #4a5568 !important;">Γραφτείτε για να λαμβάνετε τις καλύτερες συνταγές.</p>
            </div>
        </div>
    </footer>

    <script>
        function updateFileName() {
            const input = document.getElementById('recipe_image');
            const label = document.getElementById('file-label');
            if (input.files.length > 0) {
                label.innerHTML = `<i class="fa-solid fa-circle-check" style="font-size: 24px; color: var(--primary);"></i> <span style="color: var(--primary); font-weight:600;">Επιλέχθηκε: ${input.files[0].name}</span>`;
                label.style.borderColor = "var(--primary)";
                label.style.background = "rgba(39, 174, 96, 0.04)";
            }
        }
    </script>
</body>
</html>