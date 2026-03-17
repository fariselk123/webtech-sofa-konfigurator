<?php
/**
 * Sofa-Konfigurator Startseite
 * 
 * Ermöglicht Benutzern die Konfiguration eines Sofas in einem Schritt:
 * Größe, Farbe und Material wählen mit dynamischer Vorschau.
 * 
 * Verwendet Sessions zur temporären Speicherung der Auswahl.
 */

// Session starten und prüfen, ob ein Benutzer eingeloggt ist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    // Nicht eingeloggt, zurück zur Login-Seite
    header('Location: login.php');
    exit;
}

// Initialisiere Konfigurationsdaten
$config = [
    'size' => $_SESSION['config_size'] ?? '',
    'color' => $_SESSION['config_color'] ?? '',
    'material' => $_SESSION['config_material'] ?? ''
];

// Verarbeite Formularabsendung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Speichere Auswahl in Session
    $_SESSION['config_size'] = $_POST['sofa_size'] ?? '';
    $_SESSION['config_color'] = $_POST['sofa_color'] ?? '';
    $_SESSION['config_material'] = $_POST['sofa_material'] ?? '';
    
    // Weiterleitung zur Zusammenfassung
    header('Location: save.php');
    exit;
}

// Definiere verfügbare Optionen
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

// Preise
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
    'schlafsofa' => 220,
    'recamiere' => 180,
    'modulsofa' => 250,
    'lounge-sofa' => 160,
    'daybed' => 190,
    'sofa-beistelltisch' => 130,
    'sofa-ottomane' => 140,
    'futon-sofa' => 210,
    'klappsofa' => 170,
    'bank-sofa' => 110
];

$materialPrices = [
    'stoff' => 0,
    'leder' => 300,
    'kunstleder' => 150,
    'samt' => 200,
    'mikrofaser' => 100
];

// Farbpalette
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

    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
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
        .selection-panel {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            height: fit-content;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .preview-panel {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 600px;
            border: 1px solid rgba(255, 255, 255, 0.2);
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
        .sofa-preview {
            width: 100%;
            max-width: 700px;
            height: auto;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
        }
        .size-option, .material-option {
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }
        .size-option:hover, .material-option:hover,
        .size-option.active, .material-option.active {
            border-color: #007bff;
            box-shadow: 0 4px 20px rgba(0, 123, 255, 0.2);
            transform: translateY(-2px);
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
        .color-option:hover, .color-option.active {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            border-color: #007bff;
        }
        .progress {
            height: 10px;
            border-radius: 5px;
            margin-bottom: 2rem;
        }
        .summary-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .price-display {
            font-size: 2.5rem;
            font-weight: 700;
            color: #28a745;
            text-align: center;
            margin: 1rem 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
        }
        .option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.75rem;
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
        }
        /* Material-Effekte */
        .mat-stoff { filter: brightness(0.95) contrast(0.9) saturate(0.8); }
        .mat-leder { filter: brightness(1.1) contrast(1.2) saturate(1.4) hue-rotate(-5deg); }
        .mat-kunstleder { filter: brightness(1.05) contrast(1.1) saturate(1.1); }
        .mat-samt { filter: brightness(0.98) saturate(1.3) hue-rotate(2deg) contrast(1.05); }
        .mat-mikrofaser { filter: brightness(1.02) contrast(0.98) saturate(0.9); }

        /* Animation für Vorschau-Änderungen */
        .sofa-svg {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Info-Boxen (Preiskatalog / Raum-Berater) */
        .info-box {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.08);
            padding: 1.25rem;
            margin-top: 1.25rem;
        }
        .info-box h4 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding-bottom: 0.5rem;
        }
        .info-box ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .info-box li {
            margin: 0.3rem 0;
            font-size: 0.95rem;
        }
        .info-box .price-list {
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .configurator-container { padding: 1rem; }
            .preview-panel { min-height: 400px; }
            .sofa-preview { max-width: 100%; }
        }
    </style>

    <!-- Google Model Viewer -->
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>
</head>
<body>
    <div class="container configurator-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a href="index.php" class="btn btn-outline-secondary me-2">← Zurück zur Startseite</a>
                <a href="my-configs.php" class="btn btn-primary">📋 Meine Konfigurationen</a>
            </div>
        </div>
        <h1 class="text-center mb-4" style="color: #333; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">🛋️ Sofa Konfigurator</h1>

        <!-- Fortschrittsanzeige -->
        <div class="progress mb-4">
            <div id="progress-bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <p class="text-center text-muted mb-4" id="progress-text" style="font-weight: 500;">0 von 3 Schritten abgeschlossen</p>

        <form method="POST" action="save.php">
            <div class="row g-4">
                <!-- Linke Spalte: Auswahloptionen -->
                <div class="col-lg-5">
                    <div class="selection-panel">
                        <h3 class="section-title">Konfiguration</h3>

                        <!-- Größe wählen -->
                        <div class="mb-4">
                            <h5 class="section-title" style="font-size: 1rem; margin-bottom: 1rem;">Größe</h5>
                            <div class="option-grid">
                                <?php foreach ($sizes as $key => $label): ?>
                                    <div class="size-option card text-center p-3 <?php echo ($config['size'] === $key) ? 'active' : ''; ?>"
                                         onclick="selectSize(event, '<?php echo $key; ?>')">
                                        <div class="card-body p-2">
                                            <h6 class="card-title mb-1" style="font-size: 0.9rem;"><?php echo $label; ?></h6>
                                            <input type="radio" name="sofa_size" value="<?php echo $key; ?>" class="d-none" <?php echo ($config['size'] === $key) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Farbe wählen -->
                        <div class="mb-4">
                            <h5 class="section-title" style="font-size: 1rem; margin-bottom: 1rem;">Farbe</h5>
                            <div class="d-flex flex-wrap justify-content-center">
                                <?php foreach ($colors as $index => $color): ?>
                                    <div class="color-option"
                                         style="background-color: <?php echo $color['hex']; ?>;"
                                         onclick="selectColor(<?php echo $index; ?>)"
                                         title="<?php echo $color['name']; ?>"
                                         data-color="<?php echo $color['name']; ?>"
                                         data-hex="<?php echo $color['hex']; ?>"
                                         class="<?php echo ($config['color'] === $color['name']) ? 'active' : ''; ?>">
                                    </div>
                                    <input type="radio" name="sofa_color" value="<?php echo $color['name']; ?>" class="d-none" <?php echo ($config['color'] === $color['name']) ? 'checked' : ''; ?>>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Material wählen -->
                        <div class="mb-4">
                            <h5 class="section-title" style="font-size: 1rem; margin-bottom: 1rem;">Material</h5>
                            <div class="option-grid">
                                <?php foreach ($materials as $key => $label): ?>
                                    <div class="material-option card text-center p-2 <?php echo ($config['material'] === $key) ? 'active' : ''; ?>"
                                         onclick="selectMaterial('<?php echo $key; ?>')">
                                        <div class="card-body p-2">
                                            <div class="material-preview" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                                <?php echo $label; ?>
                                            </div>
                                            <input type="radio" name="sofa_material" value="<?php echo $key; ?>" class="d-none" <?php echo ($config['material'] === $key) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rechte Spalte: Große Produktvorschau -->
                <div class="col-lg-7">
                    <div class="preview-panel">
                        <model-viewer id="sofa-3d" src="assets/models/sofa_ecksofa.glb" camera-controls auto-rotate shadow-intensity="1" style="width: 100%; height: 500px;"></model-viewer>
                    </div>

                    <!-- Preiskatalog (rot) -->
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
                                <div class="price-item" data-material="<?php echo $key; ?>" onclick="selectMaterial('<?php echo $key; ?>')">
                                    <span><?php echo $label; ?></span>
                                    <span>+<?php echo $materialPrices[$key]; ?>€</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p style="margin-top: 0.75rem; font-size: 0.9rem; color: #555;">Alle Farben sind im Grundpreis enthalten (0€ Aufpreis).</p>
                    </div>

                    <!-- Raum-Berater (grün) -->
                    <div class="info-box" id="room-advisor">
                        <h4>Raum-Berater</h4>
                        <label for="room-volume" style="font-weight: 600;">Verfügbarer Raum (m³)</label>
                        <select id="room-volume" class="form-select" style="max-width: 260px;" onchange="onVolumeChange(this.value)">
                            <option value="">-- auswählen --</option>
                            <option value="0-2">Sehr klein (0-2 m³)</option>
                            <option value="2-5">Klein (2-5 m³)</option>
                            <option value="5-10">Mittel (5-10 m³)</option>
                            <option value=">10">Groß (über 10 m³)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Zusammenfassung und Preis -->
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
                        <i class="fas fa-shopping-cart me-2"></i>Konfiguration speichern
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Farbpalette aus PHP
        const colors = <?php echo json_encode($colors); ?>;
    </script>

    <script>
        // JavaScript für interaktive Auswahl und Live-Zusammenfassung

        // Initialisiere Zusammenfassung und Fortschritt beim Laden
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();
            updateProgress();
            updatePrice();

            // Initialisiere Sofa basierend auf gespeicherten Werten
            const selectedSize = document.querySelector('input[name="sofa_size"]:checked');
            if (selectedSize) {
                updateSofa3D(selectedSize.value);
            }

            const selectedColor = document.querySelector('input[name="sofa_color"]:checked');
            if (selectedColor) {
                const colorOption = document.querySelector(`.color-option[data-color="${selectedColor.value}"]`);
                if (colorOption) {
                    const hex = colorOption.getAttribute('data-hex');
                    updateSofaColor(hex);
                }
            }

            const selectedMaterial = document.querySelector('input[name="sofa_material"]:checked');
            if (selectedMaterial) {
                updateSofaMaterial(selectedMaterial.value);
            }
        });

        function selectSize(event, size) {
            document.querySelectorAll('.size-option').forEach(el => el.classList.remove('active'));
            if (event && event.currentTarget) {
                event.currentTarget.classList.add('active');
            }
            const input = document.querySelector(`input[name="sofa_size"][value="${size}"]`);
            if (input) input.checked = true;
            updateSofa3D(size);
            updateSummary();
            updatePrice();
        }

        function onVolumeChange(range) {
            if (!range) return;
            let selectedSize = 'hocker';
            if (range === '0-2') {
                selectedSize = 'hocker';
            } else if (range === '2-5') {
                selectedSize = 'sessel';
            } else if (range === '5-10') {
                selectedSize = '2-sitzer';
            } else if (range === '>10') {
                selectedSize = 'ecksofa';
            }
            // Markiere die korrekte Auswahl im UI
            const option = document.querySelector(`.size-option[onclick*="${selectedSize}"]`);
            if (option) {
                option.click();
            } else {
                selectSize(null, selectedSize);
            }
        }

        function selectColor(index) {
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            const colorName = event.currentTarget.getAttribute('data-color');
            const colorHex = event.currentTarget.getAttribute('data-hex');
            document.querySelector(`input[name="sofa_color"][value="${colorName}"]`).checked = true;

            updateSofaColor(colorHex);
            updateSummary();
        }

        function selectMaterial(material) {
            document.querySelectorAll('.material-option').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.querySelector(`input[name="sofa_material"][value="${material}"]`).checked = true;
            updateSofaMaterial(material);
            updateSummary();
            updatePrice();
        }

        function updateSummary() {
            // Größe aktualisieren
            const sizeInput = document.querySelector('input[name="sofa_size"]:checked');
            const sizeText = sizeInput ? sizeInput.parentElement.querySelector('.card-title').textContent : 'Nicht ausgewählt';
            document.getElementById('summary-size').textContent = sizeText;

            // Preiskatalog hervorheben
            updatePriceCatalogHighlight();

            // Farbe aktualisieren
            const colorInput = document.querySelector('input[name="sofa_color"]:checked');
            const colorText = colorInput ? colorInput.value : 'Nicht ausgewählt';
            document.getElementById('summary-color').textContent = colorText;

            // Material aktualisieren
            const materialInput = document.querySelector('input[name="sofa_material"]:checked');
            const materialText = materialInput ? materialInput.parentElement.querySelector('.material-preview').textContent.trim() : 'Nicht ausgewählt';
            document.getElementById('summary-material').textContent = materialText;

            // Button aktivieren/deaktivieren
            const submitBtn = document.getElementById('submit-btn');
            if (sizeInput && colorInput && materialInput) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }

            // Fortschritt aktualisieren
            updateProgress();
        }

        function updateSofa3D(size) {
            const modelPath = 'assets/models/sofa_' + size + '.glb';
            const viewer = document.getElementById('sofa-3d');
            console.log('Lade 3D-Modell:', modelPath);
            viewer.src = modelPath;
            viewer.setAttribute('data-current-model', modelPath);
        }

        function updateSofaColor(hex) {
            const modelViewer = document.getElementById('sofa-3d');
            // Hex zu RGB konvertieren
            const r = parseInt(hex.slice(1, 3), 16) / 255;
            const g = parseInt(hex.slice(3, 5), 16) / 255;
            const b = parseInt(hex.slice(5, 7), 16) / 255;
            // Material setzen, wenn Modell geladen
            const applyColor = () => {
                if (modelViewer.model && modelViewer.model.materials && modelViewer.model.materials.length > 0) {
                    modelViewer.model.materials[0].pbrMetallicRoughness.setBaseColorFactor([r, g, b, 1]);
                }
            };
            if (modelViewer.model && modelViewer.model.materials && modelViewer.model.materials.length > 0) {
                applyColor();
            } else {
                // Warte auf Modell-Load
                modelViewer.addEventListener('load', applyColor);
            }
        }

        function updateSofaMaterial(material) {
            const modelViewer = document.getElementById('sofa-3d');
            const roughnessMap = {
                'stoff': 0.8,
                'leder': 0.4,
                'kunstleder': 0.6,
                'samt': 1.0,
                'mikrofaser': 0.7
            };
            const roughness = roughnessMap[material] || 0.8;

            if (modelViewer.model && modelViewer.model.materials && modelViewer.model.materials.length > 0) {
                modelViewer.model.materials[0].pbrMetallicRoughness.setRoughnessFactor(roughness);
            } else {
                modelViewer.addEventListener('load', () => {
                    if (modelViewer.model.materials && modelViewer.model.materials.length > 0) {
                        modelViewer.model.materials[0].pbrMetallicRoughness.setRoughnessFactor(roughness);
                    }
                });
            }
        }

        function updatePriceCatalogHighlight() {
            const selectedSize = document.querySelector('input[name="sofa_size"]:checked');
            const selectedMaterial = document.querySelector('input[name="sofa_material"]:checked');

            document.querySelectorAll('#price-list-sizes .price-item').forEach(el => {
                const size = el.getAttribute('data-size');
                if (selectedSize && selectedSize.value === size) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });

            document.querySelectorAll('#price-list-materials .price-item').forEach(el => {
                const material = el.getAttribute('data-material');
                if (selectedMaterial && selectedMaterial.value === material) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });
        }

        function updatePrice() {
            const sizeInput = document.querySelector('input[name="sofa_size"]:checked');
            const materialInput = document.querySelector('input[name="sofa_material"]:checked');

            let basePrice = 500;
            let sizePrice = 0;
            let materialPrice = 0;

            if (sizeInput) {
                const sizePrices = {
                    '2-sitzer': 0,
                    '3-sitzer': 200,
                    'ecksofa': 400,
                    'u-sofa': 600,
                    'sessel': 50,
                    'loveseat': 100,
                    'relaxsessel': 150,
                    'relaxsessel-hocker': 200,
                    'hocker': 30,
                    'hocker-gross': 60,
                    'schlafsofa': 300,
                    'recamiere': 250,
                    'modulsofa': 400,
                    'lounge-sofa': 350,
                    'daybed': 200,
                    'sofa-beistelltisch': 180,
                    'sofa-ottomane': 220,
                    'futon-sofa': 120,
                    'klappsofa': 150,
                    'bank-sofa': 100
                };
                sizePrice = sizePrices[sizeInput.value] || 0;
            }

            if (materialInput) {
                const materialPrices = {
                    'stoff': 0,
                    'leder': 150,
                    'kunstleder': 100,
                    'samt': 80,
                    'mikrofaser': 50
                };
                materialPrice = materialPrices[materialInput.value] || 0;
            }

            const totalPrice = basePrice + sizePrice + materialPrice;
            document.getElementById('price-display').textContent = '€ ' + totalPrice.toLocaleString();
        }

        function updateProgress() {
            const sizeSelected = document.querySelector('input[name="sofa_size"]:checked') !== null;
            const colorSelected = document.querySelector('input[name="sofa_color"]:checked') !== null;
            const materialSelected = document.querySelector('input[name="sofa_material"]:checked') !== null;

            let completed = 0;
            if (sizeSelected) completed++;
            if (colorSelected) completed++;
            if (materialSelected) completed++;

            const percentage = Math.round((completed / 3) * 100);

            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');

            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
            progressText.textContent = completed + ' von 3 Schritten abgeschlossen';
        }
    </script>
</body>
</html>
