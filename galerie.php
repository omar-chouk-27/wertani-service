<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Galerie Travaux - Wertani Service';

// Get photo type filter
$photo_type = $_GET['type'] ?? 'voiture'; // voiture or piece

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_photo') {
    $description = $_POST['description'] ?? '';
    $article_id = $_POST['article_id'] ?? null;
    $photo_type_post = $_POST['photo_type'] ?? 'voiture';
    $voiture_dossier = $_POST['voiture_dossier'] ?? '';
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $file = $_FILES['photo'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_ext, $allowed_exts) && $file_size < 5000000) {
            if (!is_dir('uploads/article_photos')) {
                mkdir('uploads/article_photos', 0777, true);
            }
            
            $new_file_name = uniqid() . '_' . $file_name;
            $upload_path = 'uploads/article_photos/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                try {
                    $sql = "INSERT INTO article_ws_photos (ArticleWS_Id, PhotoPath, PhotoName, Description, IsMain, PhotoType, VoitureDossier) 
                            VALUES (?, ?, ?, ?, 0, ?, ?)";
                    if (executeQuery($conn, $sql, [$article_id, $upload_path, $file_name, $description, $photo_type_post, $voiture_dossier])) {
                        setFlash('success', 'Photo ajoutée avec succès!');
                    }
                } catch (Exception $e) {
                    setFlash('danger', 'Erreur: ' . $e->getMessage());
                }
            }
        } else {
            setFlash('danger', 'Format invalide ou fichier trop volumineux (max 5MB).');
        }
    }
    header("Location: galerie.php?type=" . $photo_type_post);
    exit();
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $photo = getRow($conn, "SELECT * FROM article_ws_photos WHERE Id = ?", [$id]);
    
    if ($photo) {
        // Delete file
        if (file_exists($photo['PhotoPath'])) {
            unlink($photo['PhotoPath']);
        }
        
        // Delete from database
        executeQuery($conn, "DELETE FROM article_ws_photos WHERE Id = ?", [$id]);
        setFlash('success', 'Photo supprimée!');
    }
    header("Location: galerie.php?type=" . $photo_type);
    exit();
}

// Get photos based on type
if ($photo_type === 'voiture') {
    $photos = getData($conn, "SELECT * FROM article_ws_photos WHERE PhotoType = 'voiture' ORDER BY CreationDate DESC");
} else {
    $photos = getData($conn, "SELECT * FROM article_ws_photos WHERE PhotoType = 'piece' ORDER BY CreationDate DESC");
}

// Get all articles for dropdown
$articles = getData($conn, "SELECT Id, Name FROM article_ws ORDER BY Name");

require 'includes/header.php';
?>

<div class="page-header">
    <h1>📸 Galerie des Travaux</h1>
    <div class="header-actions">
        <button class="btn btn-primary" onclick="document.getElementById('uploadForm').style.display='block'">
            ➕ Ajouter Photo
        </button>
    </div>
</div>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?>">
        <?php echo $_SESSION['flash']['message']; ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- Type Tabs -->
<div class="tabs-container" style="margin-bottom: 2rem;">
    <a href="?type=voiture" class="tab-link <?php echo $photo_type === 'voiture' ? 'active' : ''; ?>">
        🚗 Photos Voitures
    </a>
    <a href="?type=piece" class="tab-link <?php echo $photo_type === 'piece' ? 'active' : ''; ?>">
        🔧 Photos Pièces
    </a>
</div>

<!-- Upload Form (Hidden by default) -->
<div id="uploadForm" class="card" style="display: none; margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">➕ Ajouter une Photo</h3>
        <button onclick="document.getElementById('uploadForm').style.display='none'" class="btn btn-secondary">
            ✕ Fermer
        </button>
    </div>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_photo">
        <input type="hidden" name="photo_type" value="<?php echo $photo_type; ?>">
        
        <table class="form-table">
            <tr>
                <td>Photo</td>
                <td>
                    <input type="file" name="photo" class="form-control" accept="image/*" required>
                </td>
            </tr>
            <tr>
                <td>Article (optionnel)</td>
                <td>
                    <select name="article_id" class="form-select">
                        <option value="">-- Aucun article --</option>
                        <?php foreach ($articles as $article): ?>
                            <option value="<?php echo $article['Id']; ?>">
                                <?php echo htmlspecialchars($article['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php if ($photo_type === 'voiture'): ?>
            <tr>
                <td>Voiture</td>
                <td>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select name="voiture_dossier" id="car_select" class="form-select" style="flex: 1;">
                            <option value="">-- Sélectionner une voiture --</option>
                            <?php 
                            $cars = getData($conn, "SELECT * FROM cars WHERE IsActive = 1 ORDER BY Marque, Modele");
                            $current_marque = '';
                            foreach ($cars as $car): 
                                if ($car['Marque'] !== $current_marque) {
                                    if ($current_marque !== '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($car['Marque']) . '">';
                                    $current_marque = $car['Marque'];
                                }
                                $car_label = $car['Modele'];
                                if ($car['Annee']) $car_label .= ' (' . $car['Annee'] . ')';
                            ?>
                                <option value="<?php echo htmlspecialchars($car['Marque'] . ' ' . $car['Modele']); ?>">
                                    <?php echo htmlspecialchars($car_label); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_marque !== '') echo '</optgroup>'; ?>
                        </select>
                        <button type="button" class="btn btn-success" onclick="openAddCarModal()" style="white-space: nowrap;">
                            ➕ Ajouter
                        </button>
                    </div>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        💡 Sélectionnez une voiture ou ajoutez-en une nouvelle
                    </small>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Description</td>
                <td>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </td>
            </tr>
        </table>
        
        <div style="padding: 1rem; text-align: right; background: #f8f9fa; border-top: 1px solid #ddd;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('uploadForm').style.display='none'">
                Annuler
            </button>
            <button type="submit" class="btn btn-success">
                📤 Upload
            </button>
        </div>
    </form>
</div>

<!-- Photos Grid -->
<?php if (empty($photos)): ?>
    <div class="empty-state">
        <div style="font-size: 4rem; margin-bottom: 1rem;">📷</div>
        <h3>Aucune photo</h3>
        <p>Cliquez sur "Ajouter Photo" pour commencer</p>
    </div>
<?php else: ?>
    <div class="photos-grid">
        <?php foreach ($photos as $photo): ?>
            <div class="photo-card">
                <div class="photo-image-container">
                    <img src="<?php echo htmlspecialchars($photo['PhotoPath']); ?>" 
                         alt="<?php echo htmlspecialchars($photo['PhotoName']); ?>"
                         class="photo-image"
                         onclick="openImageModal('<?php echo htmlspecialchars($photo['PhotoPath']); ?>')">
                </div>
                <div class="photo-info">
                    <?php if ($photo['VoitureDossier']): ?>
                        <div class="photo-label">🚗 <?php echo htmlspecialchars($photo['VoitureDossier']); ?></div>
                    <?php endif; ?>
                    <div class="photo-name"><?php echo htmlspecialchars($photo['PhotoName']); ?></div>
                    <?php if ($photo['Description']): ?>
                        <div class="photo-description"><?php echo htmlspecialchars($photo['Description']); ?></div>
                    <?php endif; ?>
                    <div class="photo-actions">
                        <a href="?type=<?php echo $photo_type; ?>&delete=1&id=<?php echo $photo['Id']; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Supprimer cette photo?')">
                            🗑️ Supprimer
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Image Modal -->
<div id="imageModal" class="modal" onclick="closeImageModal()">
    <span class="modal-close">&times;</span>
    <img id="modalImage" class="modal-image" src="" alt="">
</div>

<!-- Add Car Modal -->
<div id="addCarModal" class="modal" style="display: none;">
    <div style="background: white; border-radius: 12px; padding: 2rem; max-width: 500px; width: 90%; position: relative;">
        <span style="position: absolute; top: 10px; right: 20px; font-size: 28px; cursor: pointer; color: #666;" onclick="closeAddCarModal()">&times;</span>
        <h3 style="margin-bottom: 1.5rem; color: #333;">➕ Ajouter une Nouvelle Voiture</h3>
        
        <form id="addCarForm" onsubmit="return addNewCar(event)">
            <table class="form-table">
                <tr>
                    <td class="required">Marque</td>
                    <td>
                        <input type="text" id="new_car_marque" class="form-control" 
                               placeholder="Ex: Toyota, Nissan, Ford..." required>
                    </td>
                </tr>
                <tr>
                    <td class="required">Modèle</td>
                    <td>
                        <input type="text" id="new_car_modele" class="form-control" 
                               placeholder="Ex: Hilux, Patrol, Ranger..." required>
                    </td>
                </tr>
                <tr>
                    <td>Année</td>
                    <td>
                        <input type="number" id="new_car_annee" class="form-control" 
                               value="<?php echo date('Y'); ?>" min="1990" max="<?php echo date('Y') + 1; ?>">
                    </td>
                </tr>
                <tr>
                    <td>Type</td>
                    <td>
                        <select id="new_car_type" class="form-select">
                            <option value="4x4">4x4</option>
                            <option value="SUV">SUV</option>
                            <option value="Pickup">Pickup</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <div style="padding: 1rem 0; text-align: right; margin-top: 1rem; border-top: 1px solid #ddd;">
                <button type="button" class="btn btn-secondary" onclick="closeAddCarModal()">
                    Annuler
                </button>
                <button type="submit" class="btn btn-success">
                    ✅ Ajouter et Sélectionner
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.tabs-container {
    display: flex;
    gap: 1rem;
    border-bottom: 2px solid #ddd;
}

.tab-link {
    padding: 1rem 2rem;
    text-decoration: none;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.tab-link:hover {
    color: #333;
    background: #f8f9fa;
}

.tab-link.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    font-weight: 600;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.photo-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.photo-image-container {
    width: 100%;
    height: 250px;
    overflow: hidden;
    background: #f0f0f0;
}

.photo-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
}

.photo-info {
    padding: 1rem;
}

.photo-label {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.photo-name {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.photo-description {
    font-size: 0.85rem;
    color: #999;
    margin-bottom: 1rem;
}

.photo-actions {
    display: flex;
    gap: 0.5rem;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal-image {
    max-width: 90%;
    max-height: 90vh;
    border-radius: 8px;
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 40px;
    font-size: 50px;
    color: white;
    cursor: pointer;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #999;
}
</style>

<script>
function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'flex';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

function openAddCarModal() {
    document.getElementById('addCarModal').style.display = 'flex';
}

function closeAddCarModal() {
    document.getElementById('addCarModal').style.display = 'none';
    document.getElementById('addCarForm').reset();
}

function addNewCar(event) {
    event.preventDefault();
    
    const marque = document.getElementById('new_car_marque').value;
    const modele = document.getElementById('new_car_modele').value;
    const annee = document.getElementById('new_car_annee').value;
    const type = document.getElementById('new_car_type').value;
    
    // Create FormData
    const formData = new FormData();
    formData.append('action', 'add_car_ajax');
    formData.append('marque', marque);
    formData.append('modele', modele);
    formData.append('annee', annee);
    formData.append('type', type);
    
    // Send AJAX request
    fetch('cars_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new option to select
            const select = document.getElementById('car_select');
            
            // Find or create optgroup for this marque
            let optgroup = null;
            const optgroups = select.getElementsByTagName('optgroup');
            for (let i = 0; i < optgroups.length; i++) {
                if (optgroups[i].label === marque) {
                    optgroup = optgroups[i];
                    break;
                }
            }
            
            if (!optgroup) {
                optgroup = document.createElement('optgroup');
                optgroup.label = marque;
                select.appendChild(optgroup);
            }
            
            // Create new option
            const option = document.createElement('option');
            const carValue = marque + ' ' + modele;
            option.value = carValue;
            option.text = modele + (annee ? ' (' + annee + ')' : '');
            option.selected = true;
            optgroup.appendChild(option);
            
            // Close modal
            closeAddCarModal();
            
            // Show success message
            alert('✅ Voiture ajoutée avec succès!');
        } else {
            alert('❌ Erreur: ' + (data.message || 'Impossible d\'ajouter la voiture'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Erreur lors de l\'ajout de la voiture');
    });
    
    return false;
}

// Close modals on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
        closeAddCarModal();
    }
});
</script>

<?php require 'includes/footer.php'; ?>
