<?php
/**
 * Registrierungsseite für den Sofa-Konfigurator
 * 
 * Ermöglicht neuen Benutzern die Registrierung mit Benutzername, Email und Passwort.
 * Verwendet PDO für sichere Datenbankoperationen.
 */

// Initialisiere Variablen für Fehlermeldungen und Erfolg
$error = '';
$success = '';

// Verarbeite POST-Anfrage bei Formularabsendung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lade Datenbankverbindung
    require_once '../app/config/db.php';
    
    // Sammle und validiere Eingaben
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Grundlegende Validierung
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Alle Felder müssen ausgefüllt werden.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
    } elseif (strlen($password) < 6) {
        $error = 'Das Passwort muss mindestens 6 Zeichen lang sein.';
    } else {
        try {
            // Prüfe, ob Benutzername oder Email bereits existiert
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Benutzername oder E-Mail-Adresse bereits vergeben.';
            } else {
                // Hash das Passwort
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Speichere neuen Benutzer in der Datenbank
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$username, $email, $hashedPassword]);
                
                // Erfolgsmeldung setzen
                $success = 'Registrierung erfolgreich! Sie können sich jetzt anmelden.';
                
                // Optional: Weiterleitung zu login.php nach kurzer Verzögerung
                // header('Location: login.php');
                // exit;
            }
        } catch (PDOException $e) {
            $error = 'Ein Datenbankfehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
            // In Produktion: Fehler loggen statt anzeigen
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
    <title>Registrierung - Sofa Konfigurator</title>
    
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
                    <h2 class="text-center mb-4">Registrierung</h2>
                    
                    <!-- Zeige Fehlermeldung falls vorhanden -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Zeige Erfolgsmeldung falls vorhanden -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Registrierungsformular -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Benutzername</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-Mail-Adresse</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Mindestens 6 Zeichen</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Registrieren</button>
                    </form>
                    
                    <!-- Link zur Login-Seite -->
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">Bereits ein Konto? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
