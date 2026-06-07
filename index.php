<?php
session_start();
require_once __DIR__ . '/config/db.php';

// charset για σωστά ελληνικά στη βάση
$pdo->exec("SET NAMES utf8mb4");

// αναζήτηση και καθαρισμός κειμένου
$search_raw = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = $search_raw;

function normalize($string) {
    $string = mb_strtolower($string, 'UTF-8');
    return str_replace(
        ['ά','έ','ή','ί','ό','ύ','ώ','ϊ','ΐ','ϋ','ΰ'],
        ['α','ε','η','ι','ο','υ','ω','ι','ι','υ','υ'],
        $string
    );
}

$search_norm = normalize($search);

// τυχαία συνταγή για το hero banner
try {
    $stmt_random = $pdo->query("SELECT * FROM recipes ORDER BY RAND() LIMIT 1");
    $random_recipe = $stmt_random->fetch();
} catch (PDOException $e) {
    $random_recipe = null;
}

// φόρτωση όλων των συνταγών με βάση το φίλτρο
try {
    $sort = $_GET['sort'] ?? 'newest';
    
    if ($sort == 'popular') {
        $stmt = $pdo->query("
            SELECT recipes.*, users.username, COUNT(likes.id) as like_count 
            FROM recipes
            JOIN users ON recipes.user_id = users.id
            LEFT JOIN likes ON recipes.id = likes.recipe_id
            GROUP BY recipes.id
            ORDER BY like_count DESC
        ");
    } else {
        $stmt = $pdo->query("
            SELECT recipes.*, users.username
            FROM recipes
            JOIN users ON recipes.user_id = users.id
            ORDER BY recipes.created_at DESC
        ");
    }
    $all_recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_recipes = [];
}

// φιλτράρισμα με php αν έγινε αναζήτηση
$recipes = [];
if ($search_norm === '') {
    $recipes = $all_recipes;
} else {
    foreach ($all_recipes as $recipe) {
        $title = normalize($recipe['title']);
        $desc  = normalize($recipe['description']);
        
        if (str_contains($title, $search_norm) || str_contains($desc, $search_norm)) {
            $recipes[] = $recipe;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Recipes</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root { 
            --primary: #27ae60; 
            --primary-hover: #219150;
            --dark: #1a1a1a; 
            --light-bg: #e2e8f0; 
            --text-gray: #4a5568;
        }
        
        /* στυλ για το footer */
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            padding: 0 20px;
        }
        .footer-section h3 {
            color: var(--dark) !important;
            margin-bottom: 20px;
            font-size: 18px;
            border-left: 3px solid var(--primary) !important;
            padding-left: 10px;
        }
        .footer-links { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #4a5568 !important; text-decoration: none; transition: 0.3s; font-weight: 500; }
        .footer-links a:hover { color: var(--primary) !important; padding-left: 5px; }

        /* κουμπιά πλοήγησης */
        .nav-btn-fav {
            color: #ff4757 !important;
            font-weight: bold;
            text-decoration: none;
            font-size: 15px;
            transition: 0.2s;
        }
        .nav-btn-fav:hover { opacity: 0.8; }

        .nav-btn-upload {
            background: var(--primary);
            color: white !important;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }
        .nav-btn-upload:hover { background: var(--primary-hover); }

        .nav-link-standard {
            color: var(--dark) !important;
            font-weight: 600;
            text-decoration: none;
            font-size: 15px;
            transition: 0.2s;
        }
        .nav-link-standard:hover { color: var(--primary) !important; }

        /* ειδοποιήσεις push */
        .push-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            font-weight: bold;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn { from { transform: translateX(120%); } to { transform: translateX(0); } }
        .push-notification.fade-out { animation: slideOut 0.5s ease-in forwards; }
        @keyframes slideOut { from { transform: translateX(0); } to { transform: translateX(120%); } }
    </style>
</head>

<body style="font-family:'Segoe UI',sans-serif;background:#f0f2f5;margin:0; display: flex; flex-direction: column; min-height: 100vh;">

<div style="flex: 1;"> 
    <header style="background: var(--light-bg); border-bottom: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 10px 0;">
        <div style="width: 100%; box-sizing: border-box; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; gap: 30px;">
            
            <div style="cursor:pointer; display:flex; align-items:center; flex-shrink: 0;" onclick="location.href='index.php'">
                <img src="uploads/Social Recipes Logo.png" alt="Social Recipes Logo" style="height: 150px; width: auto; object-fit: contain; margin: -35px 0;">
            </div>

            <form method="GET" action="index.php" style="display:flex; background:white; border: 1px solid #94a3b8; border-radius:25px; padding:6px 18px; width: 600px; max-width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <input type="text" name="search" placeholder="Αναζήτηση συνταγής..." value="<?php echo htmlspecialchars($search_raw); ?>" style="border:none; outline:none; flex:1; font-size: 15px;">
                <button type="submit" style="background:none; border:none; cursor:pointer; font-size:15px; color: var(--dark); font-weight: 600; padding-left: 10px;">Αναζήτηση</button>
            </form>

            <nav style="display: flex; gap: 25px; align-items: center; flex-shrink: 0;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="favorites.php" class="nav-btn-fav">❤️ ΑΓΑΠΗΜΕΝΑ</a>
                    
                    <a href="profile.php" title="Το Προφίλ μου" style="text-decoration:none; font-size: 20px;">
                        <span style="color: var(--primary);">👤</span>
                    </a>
                    
                    <a href="upload_recipe.php" class="nav-btn-upload">+ ΣΥΝΤΑΓΗ</a>
                    <a href="logout.php" class="nav-link-standard">Έξοδος</a>
                <?php else: ?>
                    <a href="register.php" class="nav-link-standard" style="color: var(--primary) !important;">ΕΓΓΡΑΦΗ</a>
                    <a href="login.php" class="nav-link-standard">Σύνδεση</a>
                <?php endif; ?>
            </nav>
            
        </div>
    </header>

    <?php if ($search_raw === '' && $random_recipe): ?>
    <section style="background:#fff;margin:20px auto;max-width:1100px;border-radius:15px;display:flex;overflow:hidden;box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <img src="uploads/<?php echo $random_recipe['image_path']; ?>" style="width:50%;height:350px;object-fit:cover;">
        <div style="padding:40px; width:50%;">
            <span style="color:var(--primary); font-weight:bold;">ΠΡΟΤΑΣΗ ΤΗΣ ΣΤΙΓΜΗΣ</span>
            <h2 style="margin-top:10px;"><?php echo htmlspecialchars($random_recipe['title']); ?></h2>
            <p style="color:#666;"><?php echo mb_strimwidth(htmlspecialchars($random_recipe['description']),0,150,'...'); ?></p>
            <a href="view_recipe.php?id=<?php echo $random_recipe['id']; ?>" style="background:var(--dark); color:white; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block; margin-top:10px;">Δες τη Συνταγή</a>
        </div>
    </section>
    <?php endif; ?>

    <main style="padding:20px;max-width:1200px;margin:auto;">
        <h2 style="border-bottom: 2px solid #ddd; padding-bottom: 10px;">
            <?php 
                if ($search_raw !== '') echo 'Αποτελέσματα για: '.htmlspecialchars($search_raw);
                elseif (isset($_GET['sort']) && $_GET['sort'] == 'popular') echo 'Δημοφιλείς Συνταγές';
                else echo 'Όλες οι Συνταγές';
            ?>
        </h2>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:25px;">
            <?php foreach ($recipes as $recipe): ?>
            <div style="background:white;border-radius:10px;overflow:hidden;box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <img src="uploads/<?php echo $recipe['image_path']; ?>" style="width:100%;height:200px;object-fit:cover;">
                <div style="padding:20px;">
                    <h3 style="margin:0 0 10px 0;"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                    <p style="color:#777; font-size:14px;"><?php echo mb_strimwidth(htmlspecialchars($recipe['description']),0,80,'...'); ?></p>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; border-top: 1px solid #eee; padding-top:10px;">
                        <small style="color:#999;">By <b><?php echo htmlspecialchars($recipe['username']); ?></b></small>
                        <a href="view_recipe.php?id=<?php echo $recipe['id']; ?>" style="color:var(--primary); text-decoration:none; font-weight:bold;">Προβολή</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

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
            <form action="subscribe_newsletter.php" method="POST" style="display:flex; margin-top:10px; background: #fff; border-radius: 5px; overflow: hidden; border: 1px solid #cbd5e1;">
                <input type="email" name="email" placeholder="Email..." required style="padding:10px; border:none; outline:none; width:100%;">
                <button type="submit" style="background:var(--primary); color:white; border:none; padding:10px 15px; cursor:pointer; font-weight: bold;">Εγγραφή</button>
            </form>
        </div>
    </div>
    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #cbd5e1; color: #718096 !important;">
        &copy; 2026 Social Recipes. | All Rights Reserved.
    </div>
</footer>

<?php if (isset($_GET['newsletter'])): ?>
    <?php 
        $msg = ""; $color = "#27ae60";
        if($_GET['newsletter'] == 'success') $msg = "Επιτυχής εγγραφή στο Newsletter!";
        if($_GET['newsletter'] == 'exists') { $msg = "Είστε ήδη εγγεγραμμένοι!"; $color = "#f39c12"; }
    ?>
    <?php if($msg): ?>
        <div id="push-notice" class="push-notification" style="background: <?php echo $color; ?>;">
            <span><?php echo $msg; ?></span>
        </div>
        <script>
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('newsletter');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }

            setTimeout(function() {
                const notice = document.getElementById('push-notice');
                if(notice) {
                    notice.classList.add('fade-out');
                    setTimeout(() => notice.remove(), 500);
                }
            }, 3000);
        </script>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>