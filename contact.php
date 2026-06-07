<?php
// ξεκινάμε το session
session_start();
// σύνδεση με τη βάση δεδομένων
require_once __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Επικοινωνία - Social Recipes</title>
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
            margin: 0; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
        }

        /* css για το grid της σελίδας */
        .contact-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
            margin: 40px 0;
        }

        @media (max-width: 850px) {
            .contact-grid { grid-template-columns: 1fr; }
        }

        /* στυλ για τη φόρμα */
        .contact-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }

        .contact-card h2 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 800;
            color: var(--dark);
        }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark); font-size: 14px; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #edf2f7;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
            box-sizing: border-box;
            font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--primary);
            background: #f9fffb;
        }

        .btn-send {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
        }
        .btn-send:hover { background: var(--primary-hover); transform: translateY(-2px); }

        /* στυλ για τα στοιχεία επικοινωνίας δεξιά */
        .info-card {
            background: var(--dark);
            color: white;
            padding: 40px;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: rgba(39, 174, 96, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 20px;
            flex-shrink: 0;
        }

        .info-text h4 { margin: 0; font-size: 18px; font-weight: 700; }
        .info-text p { margin: 5px 0 0 0; color: #cbd5e1; font-size: 15px; }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .social-btn {
            width: 45px;
            height: 45px;
            border: 1px solid #334155;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            text-decoration: none;
            font-size: 20px;
            transition: 0.3s;
        }
        .social-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }

        /* στυλ για το footer */
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            padding: 0 20px;
            text-align: left;
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
                    <a href="upload_recipe.php" style="background: var(--primary); color: white; padding: 10px 20px; border-radius: 50px; font-weight: bold; text-decoration: none; transition: 0.2s;">+ ΣΥΝΤΑΓΗ</a>
                    <a href="logout.php" style="color: var(--dark); font-weight: 500; text-decoration:none;">Έξοδος</a>
                <?php else: ?>
                    <a href="register.php" style="background: var(--primary); color: white; padding: 10px 20px; border-radius: 50px; font-weight: bold; text-decoration: none;">ΕΓΓΡΑΦΗ</a>
                    <a href="login.php" style="color: var(--dark); font-weight: 500; text-decoration:none;">Σύνδεση</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main style="max-width: 1100px; margin: 40px auto; padding: 0 20px; flex: 1; width: 100%; box-sizing: border-box;">
        
        <div class="contact-grid">
            <div class="contact-card">
                <h2>Πείτε μας ένα γεια!</h2>
                <p style="color: var(--text-gray); margin-bottom: 30px;">Έχετε κάποια ερώτηση ή πρόταση για τη σελίδα μας; Συμπληρώστε τη φόρμα και θα σας απαντήσουμε το συντομότερο.</p>
                
                <form action="send_contact.php" method="POST">
                    <div class="form-group">
                        <label>Ονοματεπώνυμο</label>
                        <input type="text" name="name" placeholder="π.χ. Γιώργος Παπαδόπουλος" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="π.χ. g.papadopoulos@email.com" required>
                    </div>
                    <div class="form-group">
                        <label>Το μήνυμά σας</label>
                        <textarea name="message" rows="5" placeholder="Γράψτε εδώ τις απορίες σας..." required></textarea>
                    </div>
                    <button type="submit" class="btn-send">Αποστολή Μηνύματος</button>
                </form>
            </div>

            <div class="info-card">
                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                    <div class="info-text">
                        <h4>Email</h4>
                        <p>info@socialrecipes.gr</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                    <div class="info-text">
                        <h4>Τηλέφωνο</h4>
                        <p>+30 210 123 4567</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="info-text">
                        <h4>Τοποθεσία</h4>
                        <p>Αθήνα, Ελλάδα</p>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <h4 style="margin: 0 0 15px 0;">Ακολουθήστε μας</h4>
                    <div class="social-links">
                        <a href="#" class="social-btn"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-btn"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="fa-brands fa-tiktok"></i></a>
                        <a href="#" class="social-btn"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
            </div>
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

</body>
</html>