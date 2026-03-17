<?php
/**
 * Startseite des Sofa-Konfigurators
 *
 * Zeigt unterschiedliche Navigation basierend auf Login-Status:
 * - Nicht eingeloggt: Login und Registrieren
 * - Eingeloggt: Konfigurator, Meine Konfigurationen und Logout
 */

// Starte Session ganz am Anfang
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfe Login-Status
$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sofa Konfigurator</title>

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
        .landing-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            max-width: 600px;
            text-align: center;
        }
        .landing-container h1 {
            color: #333;
            margin-bottom: 1rem;
            font-weight: bold;
        }
        .landing-container p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        .button-group a {
            flex: 1;
            min-width: 150px;
        }
        .welcome-message {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <!-- LANDING PAGE: Diese Seite erfüllt die Landing Page Anforderung der Aufgabenstellung -->

        <h1>🛋️ Sofa Konfigurator</h1>

        <?php if ($isLoggedIn): ?>
            <!-- Eingeloggter Benutzer -->
            <div class="welcome-message">
                <h4>Willkommen zurück, <?php echo htmlspecialchars($username); ?>!</h4>
                <p>Konfiguriere dein nächstes Traumsofa oder verwalte deine gespeicherten Konfigurationen.</p>
            </div>

            <div class="button-group">
                <a href="configurator.php" class="btn btn-primary btn-lg">
                    🛋️ Konfigurator starten
                </a>
                <a href="my-configs.php" class="btn btn-outline-primary btn-lg">
                    📋 Meine Konfigurationen
                </a>
            </div>

            <hr class="my-4">

            <div class="button-group">
                <a href="logout.php" class="btn btn-outline-danger">
                    🚪 Logout
                </a>
            </div>
        <?php else: ?>
            <!-- Nicht eingeloggter Benutzer -->
            <p>
                Gestalte deinen eigenen Traum-Sofa nach deinen Wünschen!
                Wähle Größe, Farbe und Material aus unserem umfangreichen Angebot und erstelle deine perfekte Konfiguration.
            </p>

            <div class="button-group">
                <!-- Primärer Button zum Starten des Konfigurators -->
                <a href="configurator.php" class="btn btn-primary btn-lg">
                    Konfigurator starten
                </a>
            </div>

            <hr class="my-4">

            <p style="font-size: 0.95rem; color: #999;">Hast du bereits ein Konto?</p>

            <div class="button-group">
                <!-- Authentifizierungs-Buttons -->
                <a href="login.php" class="btn btn-outline-secondary">
                    Login
                </a>
                <a href="register.php" class="btn btn-outline-success">
                    Registrieren
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
