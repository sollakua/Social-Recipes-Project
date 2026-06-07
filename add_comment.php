<?php
// έναρξη session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// σύνδεση με βάση
require_once __DIR__ . '/config/db.php';

// παίρνουμε το id της συνταγής
$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// έλεγχος αν έγινε post για νέο σχόλιο
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    // πρέπει ο χρήστης να είναι συνδεδεμένος
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $comment_text = trim($_POST['comment_text']);

        if (!empty($comment_text) && $recipe_id > 0) {
            // εισαγωγή σχολίου στη βάση
            $insert_query = "INSERT INTO comments (recipe_id, user_id, comment_text) VALUES (:recipe_id, :user_id, :comment_text)";
            $stmt = $pdo->prepare($insert_query);
            $stmt->execute([
                ':recipe_id' => $recipe_id,
                ':user_id' => $user_id,
                ':comment_text' => $comment_text
            ]);
            
            // ανανέωση σελίδας
            header("Location: view_recipe.php?id=" . $recipe_id);
            exit();
        }
    } else {
        // αν δεν είναι συνδεδεμένος πάει στο login
        echo "<script>alert('Πρέπει να συνδεθείτε για να αφήσετε σχόλιο!'); window.location.href='login.php';</script>";
        exit();
    }
}

// παίρνουμε όλα τα σχόλια για τη συνταγή
$select_query = "SELECT comments.*, users.username FROM comments 
                 JOIN users ON comments.user_id = users.id 
                 WHERE comments.recipe_id = :recipe_id 
                 ORDER BY comments.created_at DESC";
$comments_stmt = $pdo->prepare($select_query);
$comments_stmt->execute([':recipe_id' => $recipe_id]);
$all_comments = $comments_stmt->fetchAll();
?>

<style>
    /* styles για τα σχόλια */
    .comments-box-section { max-width: 100%; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); font-family: sans-serif; }
    .comments-box-section h3 { border-bottom: 2px solid #2cc16f; padding-bottom: 8px; color: #333; font-size: 20px; }
    .comment-item { background: #fcfcfc; border-left: 5px solid #2cc16f; padding: 10px 15px; margin-bottom: 10px; border-radius: 4px; text-align: left; }
    .comment-user { font-weight: bold; color: #2cc16f; }
    .comment-date { font-size: 12px; color: #999; margin-left: 10px; }
    .comment-content { margin-top: 5px; color: #444; line-height: 1.4; }
    .comment-form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: none; box-sizing: border-box; font-family: inherit; }
    .comment-form-group textarea:focus { border-color: #2cc16f; outline: none; }
    .comment-submit-btn { background: #2cc16f; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 10px; transition: background 0.2s; }
    .comment-submit-btn:hover { background: #25a35e; }
    .no-comments-msg { color: #aaa; text-align: center; font-style: italic; }
</style>

<div class="comments-box-section">
    <h3>Σχόλια Κοινότητας (<?php echo count($all_comments); ?>)</h3>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form action="view_recipe.php?id=<?php echo $recipe_id; ?>" method="POST">
            <div class="comment-form-group">
                <textarea name="comment_text" rows="3" placeholder="Προσθέστε ένα σχόλιο για αυτή τη συνταγή..." required></textarea>
            </div>
            <button type="submit" name="submit_comment" class="comment-submit-btn">Δημοσίευση</button>
        </form>
    <?php else: ?>
        <p style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; text-align: center;">
            Πρέπει να έχετε <a href="login.php" style="color: #2cc16f; font-weight: bold; text-decoration: none;">συνδεθεί</a> για να σχολιάσετε.
        </p>
    <?php endif; ?>

    <div style="margin-top: 20px;">
        <?php if (!empty($all_comments)): ?>
            <?php foreach ($all_comments as $comment): ?>
                <div class="comment-item">
                    <div>
                        <span class="comment-user">@<?php echo htmlspecialchars($comment['username']); ?></span>
                        <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                    </div>
                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-comments-msg">Δεν υπάρχουν σχόλια για αυτή τη συνταγή. Γράψτε το πρώτο!</p>
        <?php endif; ?>
    </div>
</div>