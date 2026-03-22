<?php
/**
 * Sofa-Konfigurator
 * 
 * Ermöglicht Benutzern die Konfiguration eines Sofas:
 * - Größe, Farbe und Material mit dynamischer 3D-Vorschau
 * - Raum-Berater für automatische Größenempfehlung
 * - Live-Preisberechnung und Zusammenfassung
 * - Session-basierte Speicherung
 */

// === PHP Backend Setup ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentifizierung prüfen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialisiere Konfiguration
$config = [
    'size' => $_SESSION['config_size'] ?? 'loveseat',
    'color' => $_SESSION['config_color'] ?? '',
    'material' => $_SESSION['config_material'] ?? ''
];

// Default auf Loveseat setzen falls leer
if (empty($_SESSION['config_size'])) {
    $_SESSION['config_size'] = 'loveseat';
}

// POST: Konfiguration speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['config_size'] = $_POST['sofa_size'] ?? '';
    $_SESSION['config_color'] = $_POST['sofa_color'] ?? '';
    $_SESSION['config_material'] = $_POST['sofa_material'] ?? '';
    header('Location: save.php');
    exit;
}

// === Datenstrukturen ===
$sizes = [
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

$materials = [
    'stoff' => 'Stoff',
    'leder' => 'Leder',
    'kunstleder' => 'Kunstleder',
    'samt' => 'Samt',
    'mikrofaser' => 'Mikrofaser'
];

$sizePrices = [
    '2-sitzer' => 0, '3-sitzer' => 200, 'ecksofa' => 400, 'u-sofa' => 600,
    'sessel' => 50, 'loveseat' => 75, 'relaxsessel' => 120, 'relaxsessel-hocker' => 150,
    'hocker' => 20, 'hocker-gross' => 35, 'schlafsofa' => 220, 'recamiere' => 180,
    'modulsofa' => 250, 'lounge-sofa' => 160, 'daybed' => 190, 'sofa-beistelltisch' => 130,
    'sofa-ottomane' => 140, 'futon-sofa' => 210, 'klappsofa' => 170, 'bank-sofa' => 110
];

$materialPrices = [
    'stoff' => 0, 'leder' => 300, 'kunstleder' => 150, 'samt' => 200, 'mikrofaser' => 100
];

$colors = [
    ['name' => 'Weiß', 'hex' => '#FFFFFF'],
    ['name' => 'Schwarz', 'hex' => '#000000'],
    ['name' => 'Grau', 'hex' => '#808080'],
    ['name' => 'Hellgrau', 'hex' => '#D3D3D3'],
    ['name' => 'Dunkelgrau', 'hex' => '#A9A9A9'],
    ['name' => 'Blau', 'hex' => '#0000FF'],
    ['name' => 'Hellblau', 'hex' => '#ADD8E6'],
    ['name' => 'Dunkelblau', 'hex' => '#00008B'],
    ['name' => 'Rot', 'hex' => '#FF0000'],
    ['name' => 'Dunkelrot', 'hex' => '#8B0000'],
    ['name' => 'Grün', 'hex' => '#008000'],
    ['name' => 'Hellgrün', 'hex' => '#90EE90'],
    ['name' => 'Gelb', 'hex' => '#FFFF00'],
    ['name' => 'Orange', 'hex' => '#FFA500'],
    ['name' => 'Lila', 'hex' => '#800080'],
    ['name' => 'Rosa', 'hex' => '#FFC0CB'],
    ['name' => 'Beige', 'hex' => '#F5F5DC'],
    ['name' => 'Braun', 'hex' => '#A0522D'],
    ['name' => 'Türkis', 'hex' => '#40E0D0'],
    ['name' => 'Mint', 'hex' => '#98FB98'],
    ['name' => 'Koralle', 'hex' => '#FF7F50'],
    ['name' => 'Lavendel', 'hex' => '#E6E6FA']
];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sofa Konfigurator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>

    <style>
        /* ===== Layout Grundlagen ===== */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .configurator-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 1rem 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
        }

        /* ===== Panel Styling ===== */
        .selection-panel,
        .preview-panel,
        .info-box,
        .summary-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .selection-panel {
            padding: 2rem;
            height: fit-content;
        }

        .preview-panel {
            padding: 2rem;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .preview-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            pointer-events: none;
        }

        .info-box {
            padding: 1.25rem;
            margin-top: 1.25rem;
        }

        .info-box h4 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding-bottom: 0.5rem;
        }

        .summary-card {
            padding: 2rem;
            margin-top: 2rem;
        }

        /* ===== Auswahloptionen ===== */
        .option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.75rem;
        }

        .size-option,
        .material-option {
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }

        .size-option:hover,
        .material-option:hover,
        .size-option.active,
        .material-option.active {
            border-color: #007bff;
            box-shadow: 0 4px 20px rgba(0, 123, 255, 0.2);
            transform: translateY(-2px);
        }

        .material-preview {
            width: 100%;
            height: 80px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .color-option {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin: 0.25rem;
        }

        .color-option:hover,
        .color-option.active {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            border-color: #007bff;
        }

        /* ===== Preisliste ===== */
        .price-list {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.35rem 0;
            border-radius: 10px;
            transition: background 0.2s ease;
            cursor: pointer;
        }

        .price-item:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        .price-item.active {
            font-weight: 700;
            font-size: 1.05rem;
            background: rgba(0, 123, 255, 0.08);
        }

        /* ===== 3D Viewer & Raum-Berater Layout ===== */
        .preview-room-container {
            display: flex;
            gap: 1rem;
            align-items: stretch;
            margin-bottom: 1rem;
        }

        .preview-room-container .preview-panel {
            flex: 0 0 80%;
            min-height: 600px;
        }

        .preview-room-container .info-box#room-advisor {
            flex: 0 0 20%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            margin-top: 0;
        }

        .preview-room-container .info-box#room-advisor .form-select {
            width: 100%;
            max-width: none;
        }

        /* ===== Dimension Overlay ===== */
        .dimensions-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.6);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #fff;
            z-index: 10;
            line-height: 1.6;
            backdrop-filter: blur(4px);
        }

        .dimension-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .dimension-item span {
            font-family: 'Courier New', monospace;
        }

        /* ===== Fortschritt & Preis ===== */
        .progress {
            height: 10px;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .price-display {
            font-size: 2.5rem;
            font-weight: 700;
            color: #28a745;
            text-align: center;
            margin: 1rem 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 1200px) {
            .preview-room-container {
                flex-direction: column;
            }

            .preview-room-container .preview-panel {
                flex: 0 0 auto;
                min-height: 500px;
            }

            .preview-room-container .info-box#room-advisor {
                flex: 0 0 auto;
            }
        }

        @media (max-width: 768px) {
            .configurator-container {
                padding: 1rem;
            }

            .preview-panel {
                min-height: 350px;
            }

            .preview-room-container {
                flex-direction: column;
            }

            .preview-room-container .preview-panel {
                flex: 0 0 auto;
            }

            .preview-room-container .info-box#room-advisor {
                flex: 0 0 auto;
            }

            .section-title {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container configurator-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a href="index.php" class="btn btn-outline-secondary me-2">← Zurück</a>
                <a href="my-configs.php" class="btn btn-primary">📋 Meine Konfigurationen</a>
            </div>
        </div>

        <h1 class="text-center mb-4" style="color: #333; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">🛋️ Sofa Konfigurator</h1>

        <div class="progress mb-4">
            <div id="progress-bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <p class="text-center text-muted mb-4" id="progress-text">0 von 3 Schritten abgeschlossen</p>

        <form id="configurator-form" onsubmit="saveConfiguration(event)">
            <div class="row g-4">
                <!-- Linke Spalte: Auswahl -->
                <div class="col-lg-5">
                    <div class="selection-panel">
                        <h3 class="section-title">Konfiguration</h3>

                        <!-- Größe -->
                        <div class="mb-4">
                            <h5 class="section-title" style="font-size: 1rem; margin-bottom: 1rem;">Größe</h5>
                            <div class="option-grid">
                                <?php foreach ($sizes as $key => $label): ?>
                                    <div class="size-option card text-center p-3 <?php echo ($config['size'] === $key) ? 'active' : ''; ?>"
                                         onclick="selectSize(event, '<?php echo $key; ?>')">
                                        <div class="card-body p-2">
                                            <h6 class="card-title mb-1" style="font-size: 0.9rem;"><?php echo $label; ?></h6>
                                            <input type="radio" name="sofa_size" value="<?php echo $key; ?>" class="d-none" 
                                                   <?php echo ($config['size'] === $key) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Farbe -->
                        <div class="mb-4">
                            <h5 class="section-title" style="font-size: 1rem; margin-bottom: 1rem;">Farbe</h5>
                            <div class="d-flex flex-wrap justify-content-center">
                                <?php foreach ($colors as $color): ?>
                                    <div class="color-option"
                                         style="background-color: <?php echo $color['hex']; ?>;"
                                         onclick="selectColor(event)"
                                         title="<?php echo $color['name']; ?>"
                                         data-color="<?php echo $color['name']; ?>"
                                         data-hex="<?php echo $color['hex']; ?>"
                                         class="<?php echo ($config['color'] === $color['name']) ? 'active' : ''; ?>">
                                    </div>
                                    <input type="radio" name="sofa_color" value="<?php echo $color['name']; ?>" class="d-none" 
                                           <?php echo ($config['color'] === $color['name']) ? 'checked' : ''; ?>>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Material -->
                        <div class="mb-4">
                            <h5 class="section-title" style="font-size: 1rem; margin-bottom: 1rem;">Material</h5>
                            <div class="option-grid">
                                <?php foreach ($materials as $key => $label): ?>
                                    <div class="material-option card text-center p-2 <?php echo ($config['material'] === $key) ? 'active' : ''; ?>"
                                         onclick="selectMaterial(event, '<?php echo $key; ?>')">
                                        <div class="card-body p-2">
                                            <div class="material-preview"><?php echo $label; ?></div>
                                            <input type="radio" name="sofa_material" value="<?php echo $key; ?>" class="d-none" 
                                                   <?php echo ($config['material'] === $key) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rechte Spalte: 3D Vorschau + Raum-Berater -->
                <div class="col-lg-7">
                    <div class="preview-room-container">
                        <!-- 3D Viewer (80%) -->
                        <div class="preview-panel" style="position: relative;">
                            <model-viewer id="sofa-3d" src="assets/models/sofa_loveseat.glb?v=<?= time() ?>" camera-controls auto-rotate shadow-intensity="1" style="width: 100%; height: 100%;"></model-viewer>
                            <div id="dimensions-overlay" class="dimensions-overlay">
                                <div class="dimension-item">↕ <span id="dim-length">Länge: 170 cm</span></div>
                                <div class="dimension-item">↔ <span id="dim-width">Breite: 95 cm</span></div>
                                <div class="dimension-item">⬆ <span id="dim-height">Höhe: 90 cm</span></div>
                            </div>
                        </div>

                        <!-- Raum-Berater (20%) -->
                        <div class="info-box" id="room-advisor">
                            <h4>Raum-Berater</h4>
                            <label for="room-volume">Verfügbarer Raum (m³)</label>
                            <select id="room-volume" class="form-select" onchange="onVolumeChange(this.value)">
                                <option value="">-- auswählen --</option>
                                <option value="0-2">Sehr klein (0-2 m³)</option>
                                <option value="2-5">Klein (2-5 m³)</option>
                                <option value="5-10">Mittel (5-10 m³)</option>
                                <option value=">10">Groß (über 10 m³)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Preiskatalog -->
                    <div class="info-box" id="price-catalog">
                        <h4>Preiskatalog & Aufpreise</h4>
                        <div class="price-list" id="price-list-sizes">
                            <?php foreach ($sizes as $key => $label): ?>
                                <div class="price-item" data-size="<?php echo $key; ?>" onclick="selectSize(null, '<?php echo $key; ?>')">
                                    <span><?php echo $label; ?></span>
                                    <span>+<?php echo $sizePrices[$key]; ?>€</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="price-list" id="price-list-materials" style="margin-top: 1rem;">
                            <?php foreach ($materials as $key => $label): ?>
                                <div class="price-item" data-material="<?php echo $key; ?>" onclick="selectMaterial(null, '<?php echo $key; ?>')">
                                    <span><?php echo $label; ?></span>
                                    <span>+<?php echo $materialPrices[$key]; ?>€</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p style="margin-top: 0.75rem; font-size: 0.9rem; color: #555;">Alle Farben sind kostenlos.</p>
                    </div>
                </div>
            </div>

            <!-- Zusammenfassung -->
            <div class="summary-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-3">Ihre aktuelle Auswahl</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Größe:</strong> <span id="summary-size" class="text-primary">Nicht ausgewählt</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Farbe:</strong> <span id="summary-color" class="text-primary">Nicht ausgewählt</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Material:</strong> <span id="summary-material" class="text-primary">Nicht ausgewählt</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="price-display" id="price-display">€ 0</div>
                        <p class="text-muted mb-0">inkl. MwSt.</p>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" id="submit-btn" class="btn btn-success btn-lg px-5 py-3" disabled>
                        💾 Speichern
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== Datenspeicher (zentral definiert) =====
        const colors = <?php echo json_encode($colors); ?>;
        
        const CONFIG = {
            BASE_PRICE: 500,
            MODEL_PATH: 'assets/models/sofa_',
            ROOM_SIZES: {
                '0-2': 'hocker',
                '2-5': 'sessel',
                '5-10': '2-sitzer',
                '>10': 'ecksofa'
            }
        };

        const PRICES = {
            sizes: {
                '2-sitzer': 0, '3-sitzer': 200, 'ecksofa': 400, 'u-sofa': 600,
                'sessel': 50, 'loveseat': 75, 'relaxsessel': 120, 'relaxsessel-hocker': 150,
                'hocker': 20, 'hocker-gross': 35, 'schlafsofa': 220, 'recamiere': 180,
                'modulsofa': 250, 'lounge-sofa': 160, 'daybed': 190, 'sofa-beistelltisch': 130,
                'sofa-ottomane': 140, 'futon-sofa': 210, 'klappsofa': 170, 'bank-sofa': 110
            },
            materials: {
                'stoff': 0, 'leder': 300, 'kunstleder': 150, 'samt': 200, 'mikrofaser': 100
            }
        };

        const DIMENSIONS = {
            '2-sitzer': { length: '190 cm', width: '95 cm', height: '90 cm' },
            '3-sitzer': { length: '230 cm', width: '95 cm', height: '90 cm' },
            'ecksofa': { length: '280 cm', width: '180 cm', height: '85 cm' },
            'u-sofa': { length: '350 cm', width: '200 cm', height: '85 cm' },
            'sessel': { length: '90 cm', width: '90 cm', height: '95 cm' },
            'loveseat': { length: '170 cm', width: '95 cm', height: '90 cm' },
            'relaxsessel': { length: '110 cm', width: '105 cm', height: '105 cm' },
            'relaxsessel-hocker': { length: '80 cm', width: '70 cm', height: '45 cm' },
            'hocker': { length: '60 cm', width: '60 cm', height: '45 cm' },
            'hocker-gross': { length: '80 cm', width: '80 cm', height: '45 cm' },
            'schlafsofa': { length: '210 cm', width: '140 cm', height: '90 cm' },
            'recamiere': { length: '190 cm', width: '80 cm', height: '85 cm' },
            'modulsofa': { length: '280 cm', width: '180 cm', height: '85 cm' },
            'lounge-sofa': { length: '240 cm', width: '100 cm', height: '85 cm' },
            'daybed': { length: '200 cm', width: '90 cm', height: '70 cm' },
            'sofa-beistelltisch': { length: '260 cm', width: '120 cm', height: '85 cm' },
            'sofa-ottomane': { length: '270 cm', width: '140 cm', height: '85 cm' },
            'futon-sofa': { length: '200 cm', width: '100 cm', height: '80 cm' },
            'klappsofa': { length: '220 cm', width: '120 cm', height: '85 cm' },
            'bank-sofa': { length: '180 cm', width: '85 cm', height: '80 cm' }
        };

        const MATERIAL_ROUGHNESS = {
            'stoff': 0.8, 'leder': 0.4, 'kunstleder': 0.6, 'samt': 1.0, 'mikrofaser': 0.7
        };

        // ===== Hilfsfunktionen =====
        function getCheckedInput(name) {
            return document.querySelector(`input[name="${name}"]:checked`);
        }

        function hexToRgb(hex) {
            return [
                parseInt(hex.slice(1, 3), 16) / 255,
                parseInt(hex.slice(3, 5), 16) / 255,
                parseInt(hex.slice(5, 7), 16) / 255
            ];
        }

        // ===== UI-Updates =====
        function updateSofa3D(size) {
            const viewer = document.getElementById('sofa-3d');
            viewer.src = CONFIG.MODEL_PATH + size + '.glb?v=' + Date.now();
            
            const dims = DIMENSIONS[size] || { length: 'n/a', width: 'n/a', height: 'n/a' };
            document.getElementById('dim-length').textContent = 'Länge: ' + dims.length;
            document.getElementById('dim-width').textContent = 'Breite: ' + dims.width;
            document.getElementById('dim-height').textContent = 'Höhe: ' + dims.height;
        }

        function updateSofaColor(hex) {
            const viewer = document.getElementById('sofa-3d');
            const [r, g, b] = hexToRgb(hex);
            
            const applyColor = () => {
                if (viewer.model?.materials?.[0]) {
                    viewer.model.materials[0].pbrMetallicRoughness.setBaseColorFactor([r, g, b, 1]);
                }
            };
            
            if (viewer.model?.materials?.[0]) {
                applyColor();
            } else {
                viewer.addEventListener('load', applyColor, { once: true });
            }
        }

        function updateSofaMaterial(material) {
            const viewer = document.getElementById('sofa-3d');
            const roughness = MATERIAL_ROUGHNESS[material] || 0.8;
            
            const applyMaterial = () => {
                if (viewer.model?.materials?.[0]) {
                    viewer.model.materials[0].pbrMetallicRoughness.setRoughnessFactor(roughness);
                }
            };
            
            if (viewer.model?.materials?.[0]) {
                applyMaterial();
            } else {
                viewer.addEventListener('load', applyMaterial, { once: true });
            }
        }

        function updatePriceCatalogHighlight() {
            const sizeInput = getCheckedInput('sofa_size');
            const materialInput = getCheckedInput('sofa_material');

            document.querySelectorAll('.price-item').forEach(el => {
                const isSizeMatch = el.getAttribute('data-size') && sizeInput?.value === el.getAttribute('data-size');
                const isMaterialMatch = el.getAttribute('data-material') && materialInput?.value === el.getAttribute('data-material');
                el.classList.toggle('active', isSizeMatch || isMaterialMatch);
            });
        }

        function updatePrice() {
            const sizeInput = getCheckedInput('sofa_size');
            const materialInput = getCheckedInput('sofa_material');

            const sizePrice = PRICES.sizes[sizeInput?.value] || 0;
            const materialPrice = PRICES.materials[materialInput?.value] || 0;
            const total = CONFIG.BASE_PRICE + sizePrice + materialPrice;

            document.getElementById('price-display').textContent = '€ ' + total.toLocaleString();
        }

        function updateSummary() {
            const sizeInput = getCheckedInput('sofa_size');
            const colorInput = getCheckedInput('sofa_color');
            const materialInput = getCheckedInput('sofa_material');

            document.getElementById('summary-size').textContent = 
                sizeInput?.parentElement.querySelector('.card-title')?.textContent || 'Nicht ausgewählt';
            document.getElementById('summary-color').textContent = colorInput?.value || 'Nicht ausgewählt';
            document.getElementById('summary-material').textContent = 
                materialInput?.parentElement.querySelector('.material-preview')?.textContent.trim() || 'Nicht ausgewählt';

            document.getElementById('submit-btn').disabled = !(sizeInput && colorInput && materialInput);
            updateProgress();
        }

        function updateProgress() {
            const completed = [getCheckedInput('sofa_size'), getCheckedInput('sofa_color'), getCheckedInput('sofa_material')]
                .filter(Boolean).length;
            const percentage = Math.round((completed / 3) * 100);

            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('progress-text').textContent = completed + ' von 3 Schritten abgeschlossen';
        }

        // ===== Event Handler =====
        function selectSize(event, size) {
            document.querySelectorAll('.size-option').forEach(el => el.classList.remove('active'));
            if (event?.currentTarget) event.currentTarget.classList.add('active');
            
            const input = document.querySelector(`input[name="sofa_size"][value="${size}"]`);
            if (input) input.checked = true;
            
            updateSofa3D(size);
            updateSummary();
            updatePrice();
            updatePriceCatalogHighlight();
        }

        function selectColor(event) {
            const target = event.currentTarget;
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('active'));
            target.classList.add('active');
            
            const colorName = target.getAttribute('data-color');
            const colorHex = target.getAttribute('data-hex');
            document.querySelector(`input[name="sofa_color"][value="${colorName}"]`).checked = true;

            updateSofaColor(colorHex);
            updateSummary();
        }

        function selectMaterial(event, material) {
            document.querySelectorAll('.material-option').forEach(el => el.classList.remove('active'));
            if (event?.currentTarget) event.currentTarget.classList.add('active');
            
            const input = document.querySelector(`input[name="sofa_material"][value="${material}"]`);
            if (input) input.checked = true;
            
            updateSofaMaterial(material);
            updateSummary();
            updatePrice();
            updatePriceCatalogHighlight();
        }

        function onVolumeChange(range) {
            if (!range) return;
            const size = CONFIG.ROOM_SIZES[range];
            if (size) selectSize(null, size);
        }

        function saveConfiguration(event) {
            event.preventDefault();

            const sizeInput = getCheckedInput('sofa_size');
            const colorInput = getCheckedInput('sofa_color');
            const materialInput = getCheckedInput('sofa_material');

            if (!sizeInput || !colorInput || !materialInput) {
                alert('Bitte wählen Sie Größe, Farbe und Material!');
                return;
            }

            const sizePrice = PRICES.sizes[sizeInput.value] || 0;
            const materialPrice = PRICES.materials[materialInput.value] || 0;
            const totalPrice = CONFIG.BASE_PRICE + sizePrice + materialPrice;

            fetch('save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    size: sizeInput.value,
                    color: colorInput.value,
                    material: materialInput.value,
                    total_price: totalPrice
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'save.php?id=' + data.config_id;
                } else {
                    alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
                }
            })
            .catch(() => alert('Fehler beim Speichern!'));
        }

        // ===== Initialisierung =====
        document.addEventListener('DOMContentLoaded', () => {
            const sizeInput = getCheckedInput('sofa_size');
            if (sizeInput) {
                updateSofa3D(sizeInput.value);
            } else {
                selectSize(null, 'loveseat');
            }

            const colorInput = getCheckedInput('sofa_color');
            if (colorInput) {
                const hex = document.querySelector(`.color-option[data-color="${colorInput.value}"]`)?.getAttribute('data-hex');
                if (hex) updateSofaColor(hex);
            }

            const materialInput = getCheckedInput('sofa_material');
            if (materialInput) updateSofaMaterial(materialInput.value);

            updateSummary();
            updatePrice();
        });
    </script>
</body>
</html>
