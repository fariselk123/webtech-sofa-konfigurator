<?php
/**
 * Speicher- und Zusammenfassungs-Seite für Sofa-Konfigurationen
 *
 * Modes:
 * 1. POST mit JSON (fetch vom Konfigurator): Speichert Konfiguration, antwortet mit JSON
 * 2. GET mit ?id=X: Zeigt Zusammenfassungsseite für gespeicherte Konfiguration
 * 3. POST mit form-data: Legacy-Modus für traditionelle Form-Submissions
 * 
 * Geschützte Seite - erfordert Login.
 */

// Starte Session ganz am Anfang
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfe, ob Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // JSON Response für nicht eingeloggte User
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Bitte loggen Sie sich ein, um zu speichern'
        ]);
        exit;
    } else {
        header('Location: login.php');
        exit;
    }
}

// Initialisiere Variablen
$errors = [];
$success = false;
$configuration = [
    'size' => '',
    'color' => '',
    'material' => '',
    'price' => 0,
    'id' => ''
];

// Erkenne den Request-Typ
$isJsonRequest = false;
$sofa_size = '';
$sofa_color = '';
$sofa_material = '';
$total_price = 0;

// GET-Request: zeige Zusammenfassung basierend auf ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $config_id = intval($_GET['id']);
    
    try {
        require_once '../app/config/db.php';
        
        $stmt = $pdo->prepare("SELECT user_id, sofa_size, sofa_color, sofa_material, created_at FROM configurations WHERE id = ? AND user_id = ?");
        $stmt->execute([$config_id, $_SESSION['user_id']]);
        $config_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config_data) {
            // Berechne Preis
            $base_price = 500;
            $size_prices = [
                '2-sitzer' => 0,
                '3-sitzer' => 200,
                'ecksofa' => 400,
                'u-sofa' => 600,
                'sessel' => 50,
                'loveseat' => 100,
                'relaxsessel' => 150,
                'relaxsessel-hocker' => 200,
                'hocker' => 30,
                'hocker-gross' => 60,
                'schlafsofa' => 300,
                'recamiere' => 250,
                'modulsofa' => 400,
                'lounge-sofa' => 350,
                'daybed' => 200,
                'sofa-beistelltisch' => 180,
                'sofa-ottomane' => 220,
                'futon-sofa' => 120,
                'klappsofa' => 150,
                'bank-sofa' => 100
            ];
            $material_prices = [
                'stoff' => 0,
                'leder' => 150,
                'kunstleder' => 100,
                'samt' => 80,
                'mikrofaser' => 50
            ];
            
            $size_price = $size_prices[$config_data['sofa_size']] ?? 0;
            $material_price = $material_prices[$config_data['sofa_material']] ?? 0;
            $total_price = $base_price + $size_price + $material_price;
            
            $success = true;
            $configuration = [
                'id' => $config_id,
                'size' => $config_data['sofa_size'],
                'color' => $config_data['sofa_color'],
                'material' => $config_data['sofa_material'],
                'price' => $total_price
            ];
        } else {
            $errors[] = 'Konfiguration nicht gefunden.';
        }
    } catch (PDOException $e) {
        $errors[] = 'Fehler beim Abrufen der Konfiguration: ' . $e->getMessage();
    }
}
// POST-Request: speichere neue Konfiguration
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Erkenne ob JSON oder Form-Data
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // JSON-Request vom fetch()
        $isJsonRequest = true;
        $json_data = json_decode(file_get_contents('php://input'), true);
        
        if ($json_data) {
            $sofa_size = trim($json_data['size'] ?? '');
            $sofa_color = trim($json_data['color'] ?? '');
            $sofa_material = trim($json_data['material'] ?? '');
            $total_price = $json_data['total_price'] ?? 0;
        }
    } else {
        // Form-Data Request (Legacy)
        $sofa_size = trim($_POST['sofa_size'] ?? '');
        $sofa_color = trim($_POST['sofa_color'] ?? '');
        $sofa_material = trim($_POST['sofa_material'] ?? '');
    }
    
    // Validierung
    if (empty($sofa_size) || empty($sofa_color) || empty($sofa_material)) {
        $errors[] = 'Alle Konfigurationsdaten müssen ausgefüllt sein.';
        
        if ($isJsonRequest) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
            exit;
        }
    }
    
    if (empty($errors)) {
        // Wenn Preis nicht übergeben wurde, berechne ihn
        if ($total_price == 0) {
            $base_price = 500;
            $size_prices = [
                '2-sitzer' => 0,
                '3-sitzer' => 200,
                'ecksofa' => 400,
                'u-sofa' => 600,
                'sessel' => 50,
                'loveseat' => 100,
                'relaxsessel' => 150,
                'relaxsessel-hocker' => 200,
                'hocker' => 30,
                'hocker-gross' => 60,
                'schlafsofa' => 300,
                'recamiere' => 250,
                'modulsofa' => 400,
                'lounge-sofa' => 350,
                'daybed' => 200,
                'sofa-beistelltisch' => 180,
                'sofa-ottomane' => 220,
                'futon-sofa' => 120,
                'klappsofa' => 150,
                'bank-sofa' => 100
            ];
            $material_prices = [
                'stoff' => 0,
                'leder' => 150,
                'kunstleder' => 100,
                'samt' => 80,
                'mikrofaser' => 50
            ];
            $size_price = $size_prices[$sofa_size] ?? 0;
            $material_price = $material_prices[$sofa_material] ?? 0;
            $total_price = $base_price + $size_price + $material_price;
        }
        
        // Konfiguration speichern
        try {
            require_once '../app/config/db.php';
            
            // Speichere in DB
            $stmt = $pdo->prepare("INSERT INTO configurations (user_id, sofa_size, sofa_color, sofa_material, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['user_id'], $sofa_size, $sofa_color, $sofa_material]);
            
            // Hole die ID der eingefügten Konfiguration
            $config_id = $pdo->lastInsertId();
            
            if ($isJsonRequest) {
                // Antworte mit JSON für fetch()-Requests
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'config_id' => $config_id,
                    'message' => 'Konfiguration erfolgreich gespeichert'
                ]);
                exit;
            } else {
                // Weiterleitung zu GET-Request mit ID
                header('Location: save.php?id=' . $config_id);
                exit;
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Fehler beim Speichern der Konfiguration: ' . $e->getMessage();
            
            if ($isJsonRequest) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $errors[0]
                ]);
                exit;
            }
        }
    }
}

// Hilfsfunktionen für Labels
function getSizeLabel($size) {
    $labels = [
        '2-sitzer' => '2-Sitzer Sofa',
        '3-sitzer' => '3-Sitzer Sofa',
        'ecksofa' => 'Ecksofa',
        'u-sofa' => 'U-Sofa',
        'sessel' => 'Sessel',
        'loveseat' => 'Loveseat',
        'relaxsessel' => 'Relaxsessel',
        'relaxsessel-hocker' => 'Stoffhocker',
        'hocker' => 'Hocker',
        'hocker-gross' => 'Hocker groß',
        'schlafsofa' => 'Schlafsofa',
        'recamiere' => 'Recamiere',
        'modulsofa' => 'Modulsofa',
        'lounge-sofa' => 'Lounge Sofa',
        'daybed' => 'Daybed',
        'sofa-beistelltisch' => 'Sofa mit Beistelltisch',
        'sofa-ottomane' => 'Sofa mit Ottomane',
        'futon-sofa' => 'Futon Sofa',
        'klappsofa' => 'Klappsofa',
        'bank-sofa' => 'Bank Sofa'
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
        .order-button {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .modal-content {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
        }
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        .success-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
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
                        
                        <!-- Bestellbutton -->
                        <div class="order-button">
                            <button type="button" class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#orderModal" data-config-id="<?php echo htmlspecialchars($configuration['id']); ?>">
                                💳 Jetzt zahlungspflichtig bestellen
                            </button>
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
    
    <!-- Modal für Bestellbestätigung -->
    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderModalLabel">Bestellung Erfolgreich! 🎉</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="success-icon">✅</div>
                    <p class="lead"><strong>Vielen Dank für Ihre Bestellung!</strong></p>
                    <p>
                        Ihre Konfiguration <span id="configIdDisplay" class="badge bg-success"></span> wurde an unsere Manufaktur übermittelt.
                    </p>
                    <p>
                        Wir senden Ihnen in Kürze eine Bestätigung per E-Mail.
                    </p>
                    <hr>
                    <p class="text-muted small">
                        <strong>Bestelldetails:</strong><br>
                        Wir werden Sie kontaktieren, um die Bezahlung und Lieferung zu arrangieren.
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success btn-lg" id="closeOrderModal">Weiter zu Meine Konfigurationen</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Wenn das Modal geöffnet wird, Konfiguration-ID in den Modal einfügen
        const orderModal = document.getElementById('orderModal');
        if (orderModal) {
            orderModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const configId = button.getAttribute('data-config-id');
                document.getElementById('configIdDisplay').textContent = '#' + configId;
            });
        }
        
        // Schließ-Button leitet zu my-configs.php weiter
        const closeOrderModal = document.getElementById('closeOrderModal');
        if (closeOrderModal) {
            closeOrderModal.addEventListener('click', function () {
                window.location.href = 'my-configs.php';
            });
        }
    </script>
</body>
</html>
