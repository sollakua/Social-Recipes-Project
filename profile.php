<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Φόρτωση συνταγών
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_recipes = $stmt->fetchAll();

// Καταμέτρηση likes
$likes_stmt = $pdo->prepare("SELECT COUNT(likes.id) FROM likes 
                             JOIN recipes ON likes.recipe_id = recipes.id 
                             WHERE recipes.user_id = ?");
$likes_stmt->execute([$user_id]);
$total_received_likes = $likes_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Το Προφίλ μου - <?php echo htmlspecialchars($username); ?></title>
    <style>
    :root { --primary: #27ae60; --dark: #1a1a1a; --danger: #e74c3c; --light: #f8fafc; }
    body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
    .container { max-width: 900px; margin: 0 auto; }
    
    .profile-card { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); text-align: center; margin-bottom: 30px; }
    .stats-box { display: flex; justify-content: center; gap: 40px; margin-top: 20px; }
    .stat-item { font-size: 18px; color: #64748b; }
    .stat-item b { color: var(--dark); font-size: 24px; display: block; }

    /* Εδώ είναι οι αλλαγές για το grid */
    .recipe-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
        gap: 20px; 
    }
    
    .recipe-item { 
        background: white; 
        padding: 20px; 
        border-radius: 16px; 
        border: 1px solid #e2e8f0; 
        display: flex; 
        flex-direction: column; /* Τα βάζει το ένα κάτω από το άλλο μέσα στο πλαίσιο */
        align-items: center; 
        text-align: center;
        gap: 10px; 
        transition: 0.3s; 
    }
    .recipe-item:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    
    .recipe-item img { 
        width: 100%; /* Η εικόνα πιάνει όλο το πλάτος του πλαισίου */
        height: 180px; 
        object-fit: cover; 
        border-radius: 12px; 
    }
    
    .btn-action { text-decoration: none; font-weight: bold; font-size: 14px; padding: 6px 12px; border-radius: 8px; display: inline-block; margin-top: 10px;}
    .btn-view { color: #2980b9; background: #e3f2fd; }
    .btn-del { color: var(--danger); background: #fff0f0; margin-left: 5px; }
</style>
</head>
<body>

    <div class="container">
        <a href="index.php" style="display:inline-block; margin-bottom:20px; text-decoration:none; color:var(--dark);">← Επιστροφή</a>

        <div class="profile-card">
            <h1>Γεια σου, <?php echo htmlspecialchars($username); ?>!</h1>
            <div class="stats-box">
                <div class="stat-item"><b><?php echo count($my_recipes); ?></b> Συνταγές</div>
                <div class="stat-item"><b><?php echo $total_received_likes; ?></b> Likes</div>
            </div>
            <a href="upload_recipe.php" style="display:inline-block; margin-top:25px; background:var(--primary); color:white; padding: 12px 25px; border-radius: 50px; text-decoration:none; font-weight:bold;">+ Νέα Συνταγή</a>
        </div>

        <h3>Οι Συνταγές μου</h3>
        
        <div class="recipe-grid">
            <?php if (count($my_recipes) > 0): ?>
                <?php foreach ($my_recipes as $recipe): ?>
                    <div class="recipe-item">
                        <?php if ($recipe['image_path']): ?>
                            <img src="uploads/<?php echo $recipe['image_path']; ?>" alt="Recipe">
                        <?php endif; ?>
                        <div style="flex-grow:1;">
                            <div style="font-weight:bold; color:var(--dark);"><?php echo htmlspecialchars($recipe['title']); ?></div>
                            <small style="color:#94a3b8;"><?php echo date('d/m/Y', strtotime($recipe['created_at'])); ?></small>
                        </div>
                        <div>
                            <a href="view_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn-action btn-view">Προβολή</a>
                            <a href="delete_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn-action btn-del" onclick="return confirm('Διαγραφή συνταγής;')">Διαγραφή</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#64748b;">Δεν έχεις ανεβάσει ακόμα συνταγές.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
