<?php
/**
 * Speicher-Seite für Sofa-Konfigurationen
 *
 * Empfängt Konfigurationsdaten, berechnet Preis, speichert in DB und zeigt Zusammenfassung.
 * Geschützte Seite - erfordert Login.
 */

// Starte Session ganz am Anfang
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfe, ob Benutzer eingeloggt ist - wenn nicht, zu login.php weiterleiten
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialisiere Variablen
$errors = [];
$success = false;
$configuration = [
    'size' => '',
    'color' => '',
    'material' => '',
    'price' => 0
];

// Prüfe, ob POST-Daten vorhanden sind
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sofa_size = trim($_POST['sofa_size'] ?? '');
    $sofa_color = trim($_POST['sofa_color'] ?? '');
    $sofa_material = trim($_POST['sofa_material'] ?? '');
    
    // Validierung
    if (empty($sofa_size) || empty($sofa_color) || empty($sofa_material)) {
        $errors[] = 'Alle Konfigurationsdaten müssen ausgefüllt sein.';
    }
    
    if (empty($errors)) {
        // Preis berechnen
        $base_price = 500;
        
        // Größen-Zuschlag
        $size_prices = [
            '2-sitzer' => 0,
            '3-sitzer' => 200,
            'ecksofa' => 400,
            'u-sofa' => 600
        ];
        $size_price = $size_prices[$sofa_size] ?? 0;
        
        // Material-Zuschlag
        $material_prices = [
            'stoff' => 0,
            'leder' => 300,
            'kunstleder' => 150,
            'samt' => 200,
            'mikrofaser' => 100
        ];
        $material_price = $material_prices[$sofa_material] ?? 0;
        
        $total_price = $base_price + $size_price + $material_price;
        
        // Konfiguration speichern
        if (isset($_SESSION['user_id'])) {
            try {
                // Lade Datenbankverbindung
                require_once '../app/config/db.php';
                
                // Speichere in DB
                $stmt = $pdo->prepare("INSERT INTO configurations (user_id, sofa_size, sofa_color, sofa_material, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$_SESSION['user_id'], $sofa_size, $sofa_color, $sofa_material]);
                
                $success = true;
                $configuration = [
                    'size' => $sofa_size,
                    'color' => $sofa_color,
                    'material' => $sofa_material,
                    'price' => $total_price
                ];
                
            } catch (PDOException $e) {
                $errors[] = 'Fehler beim Speichern der Konfiguration: ' . $e->getMessage();
            }
        }
    }
} else {
    $errors[] = 'Keine Konfigurationsdaten empfangen.';
}

// Hilfsfunktionen für Labels
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
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfiguration speichern - Sofa Konfigurator</title>
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .save-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            max-width: 800px;
            width: 100%;
        }
        .configuration-summary {
            padding: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="save-container">
                    <h2 class="text-center mb-4">Ihre Sofa-Konfiguration</h2>
                    
                    <!-- Fehler anzeigen -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Erfolg und Zusammenfassung -->
                    <?php if ($success): ?>
                        <div class="alert alert-success mb-4">
                            Ihre Konfiguration wurde erfolgreich gespeichert!
                        </div>
                        
                        <div class="configuration-summary bg-light rounded">
                            <h4 class="mb-3">Zusammenfassung</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong class="fs-5">Sofa Größe:</strong> <?php echo htmlspecialchars(getSizeLabel($configuration['size'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong class="fs-5">Sofa Farbe:</strong> <?php echo htmlspecialchars($configuration['color']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong class="fs-5">Sofa Material:</strong> <?php echo htmlspecialchars(getMaterialLabel($configuration['material'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong class="fs-5">Gesamtpreis:</strong> <?php echo number_format($configuration['price'], 2, ',', '.'); ?> €</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                            <a href="my-configs.php" class="btn btn-primary btn-lg">Meine Konfigurationen anzeigen</a>
                            <a href="configurator.php" class="btn btn-success btn-lg">Neue Konfiguration erstellen</a>
                            <a href="index.php" class="btn btn-secondary btn-lg">Zur Startseite</a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Link zurück zur Konfiguration -->
                    <div class="text-center mt-5">
                        <a href="configurator.php" class="text-decoration-none">← Zurück zum Konfigurator</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
