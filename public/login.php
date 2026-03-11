<?php
/**
 * Login-Seite für den Sofa-Konfigurator
 * 
 * Ermöglicht bestehenden Benutzern die Anmeldung mit E-Mail und Passwort.
 * Verwendet Sessions für die Authentifizierung.
 */

// Starte Session, falls noch nicht geschehen
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialisiere Variable für Fehlermeldung
$error = '';

// Verarbeite POST-Anfrage bei Formularabsendung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lade Datenbankverbindung
    require_once '../app/config/db.php';
    
    // Sammle Eingaben
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Grundlegende Validierung
    if (empty($email) || empty($password)) {
        $error = 'Bitte füllen Sie alle Felder aus.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
    } else {
        try {
            // Suche Benutzer anhand der E-Mail
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login erfolgreich: Session starten und User-ID sowie Username speichern
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Weiterleitung zur Startseite
                header('Location: index.php');
                exit;
            } else {
                $error = 'Ungültige E-Mail-Adresse oder Passwort.';
            }
        } catch (PDOException $e) {
            $error = 'Ein Datenbankfehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
            // In Produktion: Fehler loggen
            // error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sofa Konfigurator</title>
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-card">
                    <h2 class="text-center mb-4">Login</h2>
                    
                    <!-- Zeige Fehlermeldung falls vorhanden -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Login-Formular -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-Mail-Adresse</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Einloggen</button>
                    </form>
                    
                    <!-- Link zur Registrierungsseite -->
                    <div class="text-center mt-3">
                        <a href="register.php" class="text-decoration-none">Noch kein Konto? Registrieren</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
