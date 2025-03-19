<?php
ob_start(); // Start output buffering to prevent header issues

require_once 'config/database.php';
require_once 'config/session_check.php';

// Gestione delle operazioni CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aggiunta di una nuova definizione
    if (isset($_POST['add_definition'])) {
        $definition = trim($_POST['definition']);
        $parent = $_POST['definition_parent'];
        $image_path = '';
        
        // Gestione upload immagine per i loghi
        if ($parent === 'logo' && isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['logo_image']['type'], $allowed_types)) {
                $_SESSION['error'] = "Formato immagine non supportato. Usa JPG, PNG, GIF o SVG";
            } elseif ($_FILES['logo_image']['size'] > $max_size) {
                $_SESSION['error'] = "L'immagine non può superare i 5MB";
            } else {
                $upload_dir = __DIR__ . '/assets/logos/';
                
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'logo_' . time() . '_' . uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $target_path)) {
                    $image_path = 'assets/logos/' . $new_filename;
                } else {
                    $_SESSION['error'] = "Errore durante il caricamento dell'immagine";
                    $activeTab = isset($_POST['definition_parent']) ? $_POST['definition_parent'] : '';
                    header("Location: definizioni.php" . ($activeTab ? "?tab=$activeTab" : ""));
                    exit();
                }
            }
        }
        
        if (!empty($definition)) {
            if ($parent === 'logo' && !empty($image_path)) {
                $stmt = $conn->prepare("INSERT INTO definizioni (definition, definition_parent, image_path) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $definition, $parent, $image_path);
            } else {
                $stmt = $conn->prepare("INSERT INTO definizioni (definition, definition_parent) VALUES (?, ?)");
                $stmt->bind_param("ss", $definition, $parent);
            }
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Definizione aggiunta con successo.";
            } else {
                $_SESSION['error'] = "Errore nell'aggiunta della definizione: " . $conn->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Il campo definizione non può essere vuoto.";
        }
        
        // Redirect per evitare il riinvio del form, mantenendo il tab attivo
        $activeTab = isset($_POST['definition_parent']) ? $_POST['definition_parent'] : '';
        header("Location: definizioni.php" . ($activeTab ? "?tab=$activeTab" : ""));
        exit();
    }
    
    // Eliminazione di una definizione
    if (isset($_POST['delete_definition'])) {
        $id = $_POST['definition_id'];
        
        $stmt = $conn->prepare("DELETE FROM definizioni WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Definizione eliminata con successo.";
        } else {
            $_SESSION['error'] = "Errore nell'eliminazione della definizione: " . $conn->error;
        }
        $stmt->close();
        
        // Redirect mantenendo il tab attivo
        $activeTab = isset($_POST['active_tab']) ? $_POST['active_tab'] : '';
        header("Location: definizioni.php" . ($activeTab ? "?tab=$activeTab" : ""));
        exit();
    }
    
    // Modifica di una definizione
    if (isset($_POST['edit_definition'])) {
        $id = $_POST['definition_id'];
        $definition = trim($_POST['definition']);
        
        if (!empty($definition)) {
            $stmt = $conn->prepare("UPDATE definizioni SET definition = ? WHERE id = ?");
            $stmt->bind_param("si", $definition, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Definizione aggiornata con successo.";
            } else {
                $_SESSION['error'] = "Errore nell'aggiornamento della definizione: " . $conn->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Il campo definizione non può essere vuoto.";
        }
        
        // Redirect mantenendo il tab attivo
        $activeTab = isset($_POST['active_tab']) ? $_POST['active_tab'] : '';
        header("Location: definizioni.php" . ($activeTab ? "?tab=$activeTab" : ""));
        exit();
    }
}

// Recupero delle definizioni dal database
$definizioni = [];
// Ensure these values match exactly what's being used in the form submissions
// and don't exceed the 50 character limit of the definition_parent column
$types = ['disciplina', 'categoria', 'classe', 'tipo', 'turno', 'logo', 'linea_descrittiva'];

foreach ($types as $type) {
    $stmt = $conn->prepare("SELECT * FROM definizioni WHERE definition_parent = ? ORDER BY definition ASC");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $definizioni[$type] = [];
    while ($row = $result->fetch_assoc()) {
        $definizioni[$type][] = $row;
    }
    
    $stmt->close();
}

// Include the header after all processing that might use header() redirects
require_once 'includes/header.php';

// Add custom styles for this page
?>
<style>
    .nav-tabs .nav-link {
        color: #495057;
    }
    .nav-tabs .nav-link.active {
        font-weight: 600;
        color: #0d6efd;
    }
    .definition-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        margin-bottom: 5px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .definition-actions {
        display: flex;
        gap: 5px;
    }
    .alert {
        margin-top: 20px;
    }
    .floating-footer {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 40px);
        max-width: 1200px;
        background-color: #fff;
        padding: 15px;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        z-index: 1000;
        font-size: 11px;
    }
    .floating-footer a {
        font-size: 11px;
    }
    .logo-preview {
        width: 50px;
        height: 50px;
        object-fit: contain;
        margin-right: 10px;
    }
    .logo-definition-item {
        display: flex;
        align-items: center;
    }
</style>

    <div class="container">
        <h2 class="mb-4">Definizioni</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="definitionTabs" role="tablist">
                    <?php foreach ($types as $index => $type): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                                    id="<?php echo $type; ?>-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#<?php echo $type; ?>" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="<?php echo $type; ?>" 
                                    aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                <?php echo ucfirst($type); ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="tab-content mt-4" id="definitionTabsContent">
                    <?php foreach ($types as $index => $type): ?>
                        <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" 
                             id="<?php echo $type; ?>" 
                             role="tabpanel" 
                             aria-labelledby="<?php echo $type; ?>-tab">
                            
                            <!-- Form per aggiungere una nuova definizione -->
                            <?php if ($type === 'logo'): ?>
                                <form method="POST" action="" class="mb-4" enctype="multipart/form-data">
                                    <div class="input-group">
                                        <input type="text" name="definition" class="form-control" placeholder="Nome del logo" required>
                                        <input type="file" name="logo_image" class="form-control" accept="image/*" required>
                                        <input type="hidden" name="definition_parent" value="<?php echo $type; ?>">
                                        <button type="submit" name="add_definition" class="btn btn-primary">Aggiungi</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="" class="mb-4">
                                    <div class="input-group">
                                        <input type="text" name="definition" class="form-control" placeholder="Aggiungi nuova <?php echo $type; ?>" required>
                                        <input type="hidden" name="definition_parent" value="<?php echo $type; ?>">
                                        <button type="submit" name="add_definition" class="btn btn-primary">Aggiungi</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                            
                            <!-- Lista delle definizioni esistenti -->
                            <div class="definition-list">
                                <?php if (empty($definizioni[$type])): ?>
                                    <p class="text-muted">Nessuna definizione trovata per <?php echo $type; ?>.</p>
                                <?php else: ?>
                                    <?php foreach ($definizioni[$type] as $def): ?>
                                        <div class="definition-item">
                                            <?php if ($type === 'logo' && !empty($def['image_path'])): ?>
                                                <div class="logo-definition-item">
                                                    <img src="<?php echo htmlspecialchars($def['image_path']); ?>" class="logo-preview" alt="Logo">
                                                    <span><?php echo htmlspecialchars($def['definition']); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span><?php echo htmlspecialchars($def['definition']); ?></span>
                                            <?php endif; ?>
                                            <div class="definition-actions">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal" 
                                                        data-id="<?php echo $def['id']; ?>" 
                                                        data-definition="<?php echo htmlspecialchars($def['definition']); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="definition_id" value="<?php echo $def['id']; ?>">
                                                    <input type="hidden" name="active_tab" value="<?php echo $type; ?>">
                                                    <button type="submit" name="delete_definition" class="btn btn-sm btn-outline-danger" onclick="return confirm('Sei sicuro di voler eliminare questa definizione?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal per la modifica -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Modifica Definizione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="definition_id" id="edit_definition_id">
                        <input type="hidden" name="active_tab" id="edit_active_tab">
                        <div class="mb-3">
                            <label for="edit_definition" class="form-label">Definizione</label>
                            <input type="text" class="form-control" id="edit_definition" name="definition" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" name="edit_definition" class="btn btn-primary">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script per gestire il modal di modifica e mantenere il tab attivo
        document.addEventListener('DOMContentLoaded', function() {
            // Gestione del click sul pulsante di modifica
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const definition = this.getAttribute('data-definition');
                    const activeTab = this.closest('.tab-pane').id;
                    
                    // Popolare il modal con i dati
                    document.getElementById('edit_definition_id').value = id;
                    document.getElementById('edit_definition').value = definition;
                    document.getElementById('edit_active_tab').value = activeTab;
                });
            });
            
            // Attivare il tab corretto in base al parametro URL
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam) {
                const tabToActivate = document.getElementById(tabParam + '-tab');
                if (tabToActivate) {
                    const tab = new bootstrap.Tab(tabToActivate);
                    tab.show();
                }
            }
        });
    </script>
</body>
</html>