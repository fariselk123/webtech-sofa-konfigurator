<?php
/**
 * Sofa-Konfigurator Startseite
 * 
 * Ermöglicht Benutzern die Konfiguration eines Sofas in mehreren Schritten:
 * 1. Größe wählen
 * 2. Farbe wählen
 * 3. Material wählen
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
    $_SESSION['config_size'] = $_POST['size'] ?? '';
    $_SESSION['config_color'] = $_POST['color'] ?? '';
    $_SESSION['config_material'] = $_POST['material'] ?? '';
    
    // Aktualisiere lokale Variable
    $config = [
        'size' => $_SESSION['config_size'],
        'color' => $_SESSION['config_color'],
        'material' => $_SESSION['config_material']
    ];
    
    // Hier könnte eine Weiterleitung zur Zusammenfassung erfolgen
    // header('Location: summary.php');
    // exit;
}

// Definiere verfügbare Optionen
$sizes = [
    '2-sitzer' => '2-Sitzer Sofa',
    '3-sitzer' => '3-Sitzer Sofa',
    'ecksofa' => 'Ecksofa',
    'u-sofa' => 'U-Sofa'
];

$materials = [
    'stoff' => 'Stoff',
    'leder' => 'Leder',
    'kunstleder' => 'Kunstleder',
    'samt' => 'Samt',
    'mikrofaser' => 'Mikrofaser'
];

// Farbpalette mit mindestens 20 Farben
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
        }
        .configurator-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 1rem 2rem;
        }
        .step-card {
            padding: 1.5rem;
        }
        /* ensure preview area larger */
        .sofa-3d-container { max-width: 100%; height: auto; }
        .step-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .size-option, .material-option {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
        }
        .size-option:hover, .material-option:hover,
        .size-option.active, .material-option.active {
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
        }
        .color-option {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        .color-option:hover, .color-option.active {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        .progress {
            height: 8px;
            margin-bottom: 2rem;
        }
        .sofa-preview {
            width: 200px;
            height: 140px;
            border: 2px solid #ddd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            margin: 0 auto;
            transition: color 0.3s ease;
            color: #ccc; /* Standardfarbe */
        }
        /* 360‑Grad Container */
        .sofa-3d-container {
            perspective: 800px;
            width: 240px;
            height: 160px;
            margin: 1rem auto;
            cursor: grab;
        }
        .sofa-3d {
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 0.1s linear;
        }
        /* Material-Muster beispielhaft (Stoff, Leder) */
        .mat-stoff svg rect,
        .mat-leder svg rect,
        .mat-kunstleder svg rect,
        .mat-samt svg rect,
        .mat-mikrofaser svg rect {
            fill: currentColor;
        }
        .mat-stoff { color: #aaa; }
        .mat-leder { color: #c5804d; }
        .mat-kunstleder { color: #d0b7a0; }
        .mat-samt { color: #a984ac; }
        .mat-mikrofaser { color: #999; }
    </style>
</head>
<body>
    <div class="container configurator-container">
        <h1 class="text-center mb-4">🛋️ Sofa konfigurieren</h1>
        
        <!-- Fortschrittsanzeige -->
        <div class="progress mb-4">
            <div id="progress-bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <p class="text-center text-muted mb-4" id="progress-text">0 von 3 Schritten abgeschlossen</p>
        
        <form method="POST" action="save.php">
            <!-- Schritt 1: Größe wählen -->
            <div class="step-card">
                <h3 class="mb-4">1. Größe wählen</h3>
                <div class="row g-3">
                    <?php foreach ($sizes as $key => $label): ?>
                        <div class="col-md-3">
                            <div class="size-option card h-100 text-center p-3 <?php echo ($config['size'] === $key) ? 'active' : ''; ?>" 
                                 onclick="selectSize('<?php echo $key; ?>')">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $label; ?></h5>
                                    <input type="radio" name="sofa_size" value="<?php echo $key; ?>" 
                                           class="d-none">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Schritt 2: Farbe wählen -->
            <div class="step-card">
                <h3 class="mb-4">2. Farbe wählen</h3>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <?php foreach ($colors as $index => $color): ?>
                        <div class="text-center">
                            <div class="color-option" 
                                 style="background-color: <?php echo $color['hex']; ?>;" 
                                 onclick="selectColor(<?php echo $index; ?>)"
                                 title="<?php echo $color['name']; ?>"
                                 data-color="<?php echo $color['name']; ?>"
                                 data-hex="<?php echo $color['hex']; ?>"
                                 class="<?php echo ($config['color'] === $color['name']) ? 'active' : ''; ?>">
                            </div>
                            <input type="radio" name="sofa_color" value="<?php echo $color['name']; ?>" 
                                   class="d-none">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Farb- und 360° Vorschau -->
                <div class="text-center mt-4">
                    <h5>Vorschau</h5>
                    <div id="color-preview" class="sofa-preview">
                        <svg width="150" height="100" viewBox="0 0 150 100" xmlns="http://www.w3.org/2000/svg">
                            <rect x="20" y="40" width="110" height="50" rx="10" ry="10" fill="currentColor" stroke="#333" stroke-width="2"/>
                            <rect x="25" y="45" width="100" height="20" rx="5" ry="5" fill="currentColor"/>
                            <rect x="25" y="25" width="100" height="20" rx="5" ry="5" fill="currentColor"/>
                            <rect x="15" y="35" width="10" height="40" rx="5" ry="5" fill="currentColor"/>
                            <rect x="125" y="35" width="10" height="40" rx="5" ry="5" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
                
                <div class="sofa-3d-container" id="sofa3d">
                    <div class="sofa-3d" id="sofa3dinner"></div>
                </div>
            </div>
            
            <!-- Schritt 3: Material wählen -->
            <div class="step-card">
                <h3 class="mb-4">3. Material wählen</h3>
                <div class="row g-3">
                    <?php foreach ($materials as $key => $label): ?>
                        <div class="col-md-4">
                            <div class="material-option card h-100 text-center p-3 <?php echo ($config['material'] === $key) ? 'active' : ''; ?>" 
                                 onclick="selectMaterial('<?php echo $key; ?>')">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $label; ?></h5>
                                    <input type="radio" name="sofa_material" value="<?php echo $key; ?>" 
                                           class="d-none">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Live-Zusammenfassung -->
            <div class="step-card">
                <h3 class="mb-4">Ihre aktuelle Auswahl</h3>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Größe:</strong> <span id="summary-size">Nicht ausgewählt</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Farbe:</strong> <span id="summary-color">Nicht ausgewählt</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Material:</strong> <span id="summary-material">Nicht ausgewählt</span>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" id="submit-btn" class="btn btn-success btn-lg" disabled>Weiter zur Zusammenfassung</button>
                </div>
            </div>
        </form>
    </div>
            
            <!-- Schritt 2: Farbe wählen -->
            <div class="step-card">
                <h3 class="mb-4">2. Farbe wählen</h3>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <?php foreach ($colors as $index => $color): ?>
                        <div class="text-center">
                            <div class="color-option" 
                                 style="background-color: <?php echo $color['hex']; ?>;" 
                                 onclick="selectColor(<?php echo $index; ?>)"
                                 title="<?php echo $color['name']; ?>"
                                 data-color="<?php echo $color['name']; ?>"
                                 data-hex="<?php echo $color['hex']; ?>"
                                 class="<?php echo ($config['color'] === $color['name']) ? 'active' : ''; ?>">
                            </div>
                            <small class="d-block mt-1"><?php echo $color['name']; ?></small>
                            <input type="radio" name="sofa_color" value="<?php echo $color['name']; ?>" 
                                   class="d-none">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Farb- und 360‑Grad-Vorschau -->
                <div class="text-center mt-4">
                    <h5>Vorschau</h5>
                    <!-- statische Farbanzeige (klein) -->
                    <div id="color-preview" class="sofa-preview">
                        <svg width="150" height="100" viewBox="0 0 150 100" xmlns="http://www.w3.org/2000/svg">
                            <rect x="20" y="40" width="110" height="50" rx="10" ry="10" fill="currentColor" stroke="#333" stroke-width="2"/>
                            <rect x="25" y="45" width="100" height="20" rx="5" ry="5" fill="currentColor"/>
                            <rect x="25" y="25" width="100" height="20" rx="5" ry="5" fill="currentColor"/>
                            <rect x="15" y="35" width="10" height="40" rx="5" ry="5" fill="currentColor"/>
                            <rect x="125" y="35" width="10" height="40" rx="5" ry="5" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
                <!-- 360° drehbarer Sofa-Block -->
                <div class="sofa-3d-container" id="sofa3d">
                    <div class="sofa-3d" id="sofa3dinner">
                        <svg width="240" height="160" viewBox="0 0 240 160" xmlns="http://www.w3.org/2000/svg">
                            <rect x="40" y="60" width="160" height="80" rx="15" ry="15" fill="currentColor" stroke="#333" stroke-width="3"/>
                            <rect x="50" y="70" width="140" height="30" rx="8" ry="8" fill="currentColor"/>
                            <rect x="50" y="45" width="140" height="30" rx="8" ry="8" fill="currentColor"/>
                            <rect x="35" y="55" width="15" height="60" rx="8" ry="8" fill="currentColor"/>
                            <rect x="190" y="55" width="15" height="60" rx="8" ry="8" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Schritt 3: Material wählen -->
            <div class="step-card">
                <h3 class="mb-4">3. Material wählen</h3>
                <div class="row g-3">
                    <?php foreach ($materials as $key => $label): ?>
                        <div class="col-md-4">
                            <div class="material-option card h-100 text-center p-3 <?php echo ($config['material'] === $key) ? 'active' : ''; ?>" 
                                 onclick="selectMaterial('<?php echo $key; ?>')">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $label; ?></h5>
                                    <input type="radio" name="sofa_material" value="<?php echo $key; ?>" 
                                           class="d-none">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Live-Zusammenfassung -->
            <div class="step-card">
                <h3 class="mb-4">Ihre aktuelle Auswahl</h3>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Größe:</strong> <span id="summary-size">Nicht ausgewählt</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Farbe:</strong> <span id="summary-color">Nicht ausgewählt</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Material:</strong> <span id="summary-material">Nicht ausgewählt</span>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" id="submit-btn" class="btn btn-success btn-lg" disabled>Weiter zur Zusammenfassung</button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // JavaScript für interaktive Auswahl und Live-Zusammenfassung
        
        // Initialisiere Zusammenfassung und Fortschritt beim Laden
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();
            updateProgress();
            
            // Initialisiere Farb-Vorschau
            const selectedColor = document.querySelector('input[name="sofa_color"]:checked');
            if (selectedColor) {
                const colorOption = document.querySelector(`.color-option[data-color="${selectedColor.value}"]`);
                if (colorOption) {
                    const hex = colorOption.getAttribute('data-hex');
                    updateColorPreview(hex);
                }
            }
        });
        
        function selectSize(size) {
            document.querySelectorAll('.size-option').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.querySelector(`input[name="sofa_size"][value="${size}"]`).checked = true;
            updateSummary();
        }
        
        function selectColor(index) {
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            const colorName = event.currentTarget.getAttribute('data-color');
            const colorHex = event.currentTarget.getAttribute('data-hex');
            document.querySelector(`input[name="sofa_color"][value="${colorName}"]`).checked = true;
            
            // Vorschau aktualisieren
            updateColorPreview(colorHex);
            
            updateSummary();
        }
        
        function selectMaterial(material) {
            document.querySelectorAll('.material-option').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.querySelector(`input[name="sofa_material"][value="${material}"]`).checked = true;
            updateSummary();
        }
        
        function updateSummary() {
            // Größe aktualisieren
            const sizeInput = document.querySelector('input[name="sofa_size"]:checked');
            const sizeText = sizeInput ? sizeInput.parentElement.querySelector('.card-title').textContent : 'Nicht ausgewählt';
            document.getElementById('summary-size').textContent = sizeText;
            
            // Farbe aktualisieren
            const colorInput = document.querySelector('input[name="sofa_color"]:checked');
            const colorText = colorInput ? colorInput.value : 'Nicht ausgewählt';
            document.getElementById('summary-color').textContent = colorText;
            
            // Material aktualisieren
            const materialInput = document.querySelector('input[name="sofa_material"]:checked');
            const materialText = materialInput ? materialInput.parentElement.querySelector('.card-title').textContent : 'Nicht ausgewählt';
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
        
        function updateColorPreview(hex) {
            const preview = document.getElementById('color-preview');
            preview.style.color = hex;
        }
        
        // drehen durch ziehen
        (function(){
            const container = document.getElementById('sofa3d');
            const inner = document.getElementById('sofa3dinner');
            let angle = 0;
            let dragging = false;
            let startX;
            container.addEventListener('mousedown', e => {
                dragging = true;
                startX = e.clientX;
                container.style.cursor = 'grabbing';
            });
            window.addEventListener('mouseup', () => {
                dragging = false;
                container.style.cursor = 'grab';
            });
            window.addEventListener('mousemove', e => {
                if (!dragging) return;
                const dx = e.clientX - startX;
                angle += dx * 0.5;
                inner.style.transform = `rotateY(${angle}deg)`;
                startX = e.clientX;
            });
        })();
        
        function updateMaterialClass() {
            const material = document.querySelector('input[name="sofa_material"]:checked');
            const container = document.getElementById('sofa3dinner');
            container.className = 'sofa-3d';
            if (material) {
                container.classList.add('mat-' + material.value);
            }
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
