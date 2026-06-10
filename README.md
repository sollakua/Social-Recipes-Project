# Social Network of Recipes

Αυτό είναι ένα ακαδημαϊκό project για το μάθημα **Web Application Programming (AG203)**. Πρόκειται για μια δυναμική εφαρμογή κοινωνικού δικτύου ανταλλαγής συνταγών, αναπτυγμένη με τεχνολογίες Web.

## Χαρακτηριστικά
- **Ασφάλεια:** Προστασία από SQL Injection με χρήση Prepared Statements (PDO) και κρυπτογράφηση κωδικών πρόσβασης με bcrypt.
- **Διαχείριση Χρηστών:** Σύστημα Σύνδεσης/Εγγραφής (Login/Signup) με χρήση PHP Sessions.
- **Διαδραστικότητα:** Δυνατότητα για Likes, Σχόλια και μεταφόρτωση δικών σας συνταγών με εικόνες.
- **Ευκολία Χρήσης:** Έξυπνη αναζήτηση συνταγών με normalization χαρακτήρων (αγνόηση τόνων).
- **Ασφάλεια Δεδομένων:** Επικύρωση αρχείων κατά το upload και φιλτράρισμα δεδομένων φορμών.

## Τεχνολογίες
- **Back-end:** PHP
- **Database:** MySQL (μέσω XAMPP)
- **Front-end:** HTML5, CSS3, JavaScript
- **Αρχιτεκτονική:** Two-Tier (Client-Server)

## Οδηγίες Εγκατάστασης
1. Εγκαταστήστε το [XAMPP](https://www.apachefriends.org/index.html).
2. Κάντε κλώνο το repository στον φάκελο `htdocs` του XAMPP.
3. Εισάγετε το αρχείο `database.sql` στη MySQL μέσω του phpMyAdmin.
4. Εκκινήστε τους Apache και MySQL διακομιστές.
5. Ανοίξτε στον browser το `http://localhost/Social-Recipes-Project/
