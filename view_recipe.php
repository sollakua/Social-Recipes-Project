<?php
session_start();
require_once __DIR__ . '/config/db.php';

// λήψη ID συνταγής από το URL
$recipe_id = $_GET['id'] ?? null;

if (!$recipe_id) {
    header("Location: index.php");
    exit();
}

// προσθήκη νέου σχολίου
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_direct_comment'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $comment_text = trim($_POST['comment_text']);

        if (!empty($comment_text)) {
            $insert_stmt = $pdo->prepare("INSERT INTO comments (recipe_id, user_id, comment_text) VALUES (?, ?, ?)");
            $insert_stmt->execute([$recipe_id, $user_id, $comment_text]);
            
            echo "<script>window.location.href='view_recipe.php?id=" . $recipe_id . "';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Πρέπει να συνδεθείτε για να αφήσετε σχόλιο!'); window.location.href='login.php';</script>";
        exit();
    }
}

// διαγραφή σχολίου
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    if (isset($_SESSION['user_id'])) {
        $comment_id = intval($_POST['delete_comment_id']);
        $user_id = $_SESSION['user_id'];

        $delete_stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $delete_stmt->execute([$comment_id, $user_id]);

        echo "<script>window.location.href='view_recipe.php?id=" . $recipe_id . "';</script>";
        exit();
    }
}

// ανάκτηση συνταγής και δημιουργού
$stmt = $pdo->prepare("SELECT recipes.*, users.username FROM recipes 
                        JOIN users ON recipes.user_id = users.id 
                        WHERE recipes.id = :id");
$stmt->execute(['id' => $recipe_id]);
$recipe = $stmt->fetch();

if (!$recipe) {
    die("Η συνταγή δεν βρέθηκε.");
}

// καταμέτρηση likes
$like_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE recipe_id = ?");
$like_count_stmt->execute([$recipe_id]);
$total_likes = $like_count_stmt->fetchColumn();

// έλεγχος αν ο χρήστης έχει κάνει like
$user_liked = false;
if (isset($_SESSION['user_id'])) {
    $check_user_like = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND recipe_id = ?");
    $check_user_like->execute([$_SESSION['user_id'], $recipe_id]);
    if ($check_user_like->fetch()) {
        $user_liked = true;
    }
}

// ανάκτηση σχολίων
$comment_stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments 
                                JOIN users ON comments.user_id = users.id 
                                WHERE recipe_id = :r_id ORDER BY created_at DESC");
$comment_stmt->execute(['r_id' => $recipe_id]);
$comments = $comment_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - Social Recipes</title>
    <style>
        :root { 
            --primary: #27ae60; 
            --primary-hover: #219150;
            --dark: #1a1a1a; 
            --danger: #e74c3c; 
            --light-bg: #e2e8f0;
        }
        
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; margin: 0; display: flex; flex-direction: column; min-height: 100vh; }

        .nav-btn-fav { color: #ff4757 !important; font-weight: bold; text-decoration: none; font-size: 15px; transition: 0.2s; }
        .nav-btn-upload { background: var(--primary); color: white !important; padding: 10px 20px; border-radius: 50px; font-weight: bold; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .nav-btn-upload:hover { background: var(--primary-hover); }
        .nav-link-standard { color: var(--dark) !important; font-weight: 600; text-decoration: none; font-size: 15px; transition: 0.2s; }
        .nav-link-standard:hover { color: var(--primary) !important; }

        .btn-back { display: inline-flex; align-items: center; background-color: white; color: var(--dark) !important; padding: 10px 20px; text-decoration: none; border-radius: 50px; font-weight: 600; border: 1px solid #cbd5e1; transition: 0.3s; margin-bottom: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .btn-back:hover { background-color: var(--dark); color: white !important; border-color: var(--dark); }
        
        .btn-delete { background-color: #fdeaea; color: var(--danger); padding: 10px 20px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: bold; transition: 0.3s; border: 1px solid #fcd4d4; cursor: pointer; }
        .btn-delete:hover { background-color: var(--danger); color: white; }

        .recipe-card { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .recipe-content img { width: 100%; max-height: 500px; object-fit: cover; border-radius: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.05); margin: 25px 0; }

        .like-pill { margin: 20px 0; padding: 10px 24px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50px; display: inline-flex; align-items: center; gap: 12px; transition: 0.2s; }
        .comment-box { background: #f8fafc; padding: 20px; border-radius: 16px; margin-bottom: 20px; border: 1px solid #e2e8f0; position: relative; }
        .btn-delete-comment { background: #fff0f0; border: 1px solid #ffe2e2; color: var(--danger); cursor: pointer; font-size: 12px; font-weight: bold; padding: 6px 12px; border-radius: 8px; transition: 0.2s; }
        .btn-delete-comment:hover { background: var(--danger); color: white; }
    </style>
</head>
<body>

    <header style="background: var(--light-bg); border-bottom: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 10px 0;">
        <div style="width: 100%; box-sizing: border-box; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; gap: 30px;">
            <div style="cursor:pointer; display:flex; align-items:center; flex-shrink: 0;" onclick="location.href='index.php'">
                <img src="uploads/Social Recipes Logo.png" alt="Social Recipes Logo" style="height: 150px; width: auto; object-fit: contain; margin: -35px 0;">
            </div>

            <nav style="display: flex; gap: 25px; align-items: center; flex-shrink: 0;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="favorites.php" class="nav-btn-fav">❤️ ΑΓΑΠΗΜΕΝΑ</a>
                    <a href="upload_recipe.php" class="nav-btn-upload">+ ΣΥΝΤΑΓΗ</a>
                    <a href="profile.php" title="Το Προφίλ μου" style="text-decoration:none; font-size: 20px;"><span style="color: var(--primary);">👤</span></a>
                    <a href="logout.php" class="nav-link-standard">Έξοδος</a>
                <?php else: ?>
                    <a href="register.php" class="nav-btn-upload">ΕΓΓΡΑΦΗ</a>
                    <a href="login.php" class="nav-link-standard">Σύνδεση</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main style="max-width: 950px; margin: 40px auto; padding: 0 20px; flex: 1; width: 100%; box-sizing: border-box;">
        <a href="index.php" class="btn-back">← Επιστροφή στην Αρχική</a>
        <div class="recipe-card">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h1 style="margin:0 0 8px 0; font-size: 38px; color: var(--dark); font-weight: 800; letter-spacing: -0.5px;"><?php echo htmlspecialchars($recipe['title']); ?></h1>
                    <p style="color: #64748b; margin: 0; font-size: 15px;">Δημιουργός: <b style="color: var(--dark); font-weight: 600;">@<?php echo htmlspecialchars($recipe['username']); ?></b></p>
                </div>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $recipe['user_id']): ?>
                    <a href="delete_recipe.php?id=<?php echo $recipe_id; ?>" class="btn-delete" onclick="return confirm('Σίγουρα θέλετε να διαγράψετε τη συνταγή;');">Διαγραφή Συνταγής</a>
                <?php endif; ?>
            </div>

            <div class="like-pill">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="like_handler.php?recipe_id=<?php echo $recipe_id; ?>" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.5rem; transition: 0.2s;"><?php echo $user_liked ? '❤️' : '🤍'; ?></span> 
                        <b style="color: #1e293b; font-size: 16px;"><?php echo $total_likes; ?> likes</b>
                    </a>
                <?php else: ?>
                    <span style="font-size: 1.5rem;">🤍</span> <b style="color: #1e293b; font-size: 16px;"><?php echo $total_likes; ?> likes</b>
                    <small style="color: #94a3b8; margin-left: 5px;">(Συνδεθείτε για like)</small>
                <?php endif; ?>
            </div>

            <div class="recipe-content">
                <?php if ($recipe['image_path']): ?>
                    <img src="uploads/<?php echo $recipe['image_path']; ?>" alt="Recipe Image">
                <?php endif; ?>
                <h3 style="color: var(--primary); font-size: 22px; margin-top: 30px; font-weight: 700;">Υλικά & Περιγραφή</h3>
                <p style="line-height: 1.8; font-size: 17px; color: #334155; background: #f8fafc; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0;"><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                <h3 style="color: var(--primary); font-size: 22px; margin-top: 40px; font-weight: 700;">Οδηγίες Εκτέλεσης</h3>
                <p style="line-height: 1.8; font-size: 17px; color: #334155; background: #f8fafc; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0;"><?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?></p>
            </div>

            <hr style="margin: 50px 0; border: 0; border-top: 1px solid #e2e8f0;">
            <h3 style="margin-bottom: 25px; font-size: 22px; color: var(--dark);">Σχόλια Κοινότητας (<?php echo count($comments); ?>)</h3>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="" method="POST" style="margin-bottom: 40px;">
                    <textarea name="comment_text" placeholder="Μοιραστείτε τη γνώμη σας..." required style="width: 100%; padding: 18px; border-radius: 14px; border: 1px solid #cbd5e1; min-height: 100px; font-family: inherit; box-sizing: border-box; font-size: 15px;"></textarea>
                    <button type="submit" name="submit_direct_comment" style="margin-top: 12px; background: var(--primary); color: white; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: bold;">Δημοσίευση Σχολίου</button>
                </form>
            <?php else: ?>
                <div style="background: #fff9db; border: 1px solid #ffe066; padding: 15px 20px; border-radius: 12px; color: #856404; margin-bottom: 30px;">
                     Πρέπει να έχετε λογαριασμό για να σχολιάσετε. <a href="register.php" style="font-weight:700; color: #856404; text-decoration: underline;">Εγγραφή εδώ</a>.
                </div>
            <?php endif; ?>

            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-box">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="color: var(--dark); font-size: 15px;">@<?php echo htmlspecialchars($comment['username']); ?></strong>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                <form action="" method="POST" style="margin: 0;" onsubmit="return confirm('Σίγουρα θέλετε να διαγράψετε το σχόλιό σας;');">
                                    <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="btn-delete-comment">Διαγραφή</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <p style="margin: 0 0 10px 0; color: #475569; font-size: 16px; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                        <small style="color: #94a3b8; font-weight: 500;"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>