<?php
/**
 * Meine Konfigurationen Seite
 * 
 * Zeigt alle gespeicherten Sofa-Konfigurationen des eingeloggten Benutzers an.
 */

// Starte Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfe, ob Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Lade Datenbankverbindung
require_once '../app/config/db.php';

// Lade Konfigurationen des Benutzers
$configs = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM configurations WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fehlerbehandlung - in Produktion loggen
    $configs = [];
}

// Hilfsfunktionen für Labels (wie in save.php)
function getSizeLabel($size) {
    $labels = [
        '2-sitzer' => '2-Sitzer Sofa',
        '3-sitzer' => '3-Sitzer Sofa',
        'ecksofa' => 'Ecksofa',
        'u-sofa' => 'U-Sofa'
    ];
    return $labels[$size] ?? $size;
}

function getMaterialLabel($material) {
    $labels = [
        'stoff' => 'Stoff',
        'leder' => 'Leder',
        'kunstleder' => 'Kunstleder',
        'samt' => 'Samt',
        'mikrofaser' => 'Mikrofaser'
    ];
    return $labels[$material] ?? $material;
}

function calculatePrice($size, $material) {
    $sizePrices = [
        '2-sitzer' => 0,
        '3-sitzer' => 200,
        'ecksofa' => 400,
        'u-sofa' => 600,
        'sessel' => 50,
        'loveseat' => 75,
        'relaxsessel' => 120,
        'relaxsessel-hocker' => 150,
        'hocker' => 20,
        'hocker-gross' => 35,
        'schlafsofa' => 300,
        'recamiere' => 250,
        'modulsofa' => 400,
        'lounge-sofa' => 350,
        'daybed' => 200,
        'sofa-beistelltisch' => 130,
        'sofa-ottomane' => 140,
        'futon-sofa' => 210,
        'klappsofa' => 150,
        'bank-sofa' => 100
    ];

    $materialPrices = [
        'stoff' => 0,
        'leder' => 300,
        'kunstleder' => 150,
        'samt' => 200,
        'mikrofaser' => 100
    ];

    $basePrice = 500;
    $sizePrice = $sizePrices[$size] ?? 0;
    $materialPrice = $materialPrices[$material] ?? 0;

    return $basePrice + $sizePrice + $materialPrice;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meine Konfigurationen - Sofa Konfigurator</title>
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .configs-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .config-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .no-configs {
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container configs-container">
        <div class="config-card">
            <h2 class="text-center mb-4">Meine Konfigurationen</h2>
            
            <?php if (empty($configs)): ?>
                <div class="no-configs">
                    <p>Sie haben noch keine Konfigurationen gespeichert.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Größe</th>
                                <th>Farbe</th>
                                <th>Material</th>
                                <th class="text-end">Preis</th>
                                <th>Erstellungsdatum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($configs as $config): ?>
                                <?php
                                    $price = null;
                                    if (isset($config['price']) && is_numeric($config['price'])) {
                                        $price = floatval($config['price']);
                                    } else {
                                        $price = calculatePrice($config['sofa_size'], $config['sofa_material']);
                                    }
                                    $priceFormatted = number_format($price, 2, ',', '.') . ' €';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(getSizeLabel($config['sofa_size'])); ?></td>
                                    <td><?php echo htmlspecialchars($config['sofa_color']); ?></td>
                                    <td><?php echo htmlspecialchars(getMaterialLabel($config['sofa_material'])); ?></td>
                                    <td class="text-end fw-bold"><?php echo $priceFormatted; ?></td>
                                    <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($config['created_at']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="configurator.php" class="btn btn-success btn-lg me-3">Neue Konfiguration erstellen</a>
                <a href="index.php" class="btn btn-primary btn-lg me-3">Zur Startseite</a>
                <a href="logout.php" class="btn btn-secondary btn-lg">Logout</a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
