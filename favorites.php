<?php
session_start();
require_once __DIR__ . '/config/db.php';

// έλεγχος αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // παίρνουμε τις συνταγές που έχει κάνει like ο χρήστης
    $stmt = $pdo->prepare("
        SELECT recipes.*, users.username 
        FROM likes 
        JOIN recipes ON likes.recipe_id = recipes.id 
        LEFT JOIN users ON recipes.user_id = users.id 
        WHERE likes.user_id = ? 
        ORDER BY likes.id DESC
    ");
    $stmt->execute([$user_id]);
    $fav_recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    // αν σκάσει η βάση
    die("Σφάλμα Βάσης Δεδομένων: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Τα Αγαπημένα μου - Social Recipes</title>
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

        .nav-btn-fav {
            color: #ff4757 !important;
            font-weight: bold;
            text-decoration: none;
            font-size: 15px;
        }

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

        /* στυλ για το grid και τις κάρτες */
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        .recipe-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
            display: flex;
            flex-direction: column;
        }
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .recipe-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .recipe-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .recipe-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .recipe-title {
            margin: 0 0 10px 0;
            font-size: 20px;
            color: var(--dark);
            font-weight: 700;
        }

        .recipe-author {
            color: #718096;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .btn-view {
            margin-top: auto;
            background: var(--primary);
            color: white;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s;
        }
        .btn-view:hover { background: var(--primary-hover); }

        .btn-back { 
            display: inline-flex; 
            align-items: center;
            background-color: white; 
            color: var(--dark) !important; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 50px; 
            font-weight: 600; 
            border: 1px solid #cbd5e1; 
            transition: 0.3s; 
            margin-bottom: 25px; 
        }
        .btn-back:hover { background-color: var(--dark); color: white !important; }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            padding: 0 20px;
            text-align: left;
        }
    </style>
</head>
<body>

    <header style="background: var(--light-bg); border-bottom: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 10px 0;">
        <div style="width: 100%; box-sizing: border-box; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; gap: 30px;">
            <div style="cursor:pointer; display:flex; align-items:center; flex-shrink: 0;" onclick="location.href='index.php'">
                <img src="uploads/Social Recipes Logo.png" alt="Social Recipes Logo" style="height: 150px; width: auto; object-fit: contain; margin: -35px 0;">
            </div>
            <nav style="display: flex; gap: 25px; align-items: center; flex-shrink: 0;">
                <a href="favorites.php" class="nav-btn-fav">❤️ ΑΓΑΠΗΜΕΝΑ</a>
                <a href="upload_recipe.php" class="nav-btn-upload">+ ΣΥΝΤΑΓΗ</a>
                <a href="logout.php" class="nav-link-standard">Έξοδος</a>
            </nav>
        </div>
    </header>

    <main style="max-width: 1200px; margin: 40px auto; padding: 0 20px; flex: 1; width: 100%; box-sizing: border-box;">
        
        <a href="index.php" class="btn-back">← Επιστροφή στην Αρχική</a>

        <h1 style="color: var(--dark); font-weight: 800; margin-bottom: 5px;">❤️ Τα Αγαπημένα μου</h1>
        <p style="color: #718096; margin-bottom: 30px;">Οι συνταγές που έχετε ξεχωρίσει και αποθηκεύσει.</p>

        <?php if (empty($fav_recipes)): ?>
            <div style="background: white; padding: 40px; text-align: center; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                <p style="font-size: 18px; color: #4a5568; margin-bottom: 20px;">Δεν έχετε προσθέσει ακόμα καμία συνταγή στα αγαπημένα σας.</p>
                <a href="index.php" style="background: var(--primary); color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: bold;">Εξερεύνηση Συνταγών</a>
            </div>
        <?php else: ?>
            <div class="recipes-grid">
                <?php foreach ($fav_recipes as $recipe): ?>
                    <div class="recipe-card">
                        <div style="position: relative;">
                            <?php if (!empty($recipe['image_path'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($recipe['image_path']); ?>" class="recipe-image" alt="Recipe">
                            <?php else: ?>
                                <div style="width:100%; height:200px; background:#e2e8f0; display:flex; align-items:center; justify-content:center; color:#94a3b8;">Χωρίς Εικόνα</div>
                            <?php endif; ?>
                            <div class="recipe-badge">❤️</div>
                        </div>
                        <div class="recipe-body">
                            <h3 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                            <p class="recipe-author">Από: @<?php echo htmlspecialchars($recipe['username'] ?? 'Άγνωστος'); ?></p>
                            <a href="view_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn-view">Προβολή Συνταγής</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer style="background: #e2e8f0 !important; color: #4a5568 !important; padding: 50px 0 20px 0; margin-top: 50px; font-size: 14px; border-top: 1px solid #cbd5e1 !important;">
        <div class="footer-container">
            <div style="text-align: left;">
                <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 18px;">Social <span style="color:var(--primary);">Recipes</span></h3>
                <p style="color: #4a5568;">Η νούμερο 1 κοινότητα για να ανακαλύπτεις και να μοιράζεσαι τις καλύτερες συνταγές.</p>
            </div>
        </div>
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #cbd5e1; color: #718096 !important;">
            &copy; 2026 Social Recipes. | All Rights Reserved.
        </div>
    </footer>

</body>
</html>