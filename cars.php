<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $marque = $_POST['marque'] ?? '';
        $modele = $_POST['modele'] ?? '';
        $annee = $_POST['annee'] ?? null;
        $type = $_POST['type'] ?? '4x4';
        
        if (!empty($marque) && !empty($modele)) {
            $sql = "INSERT INTO cars (Marque, Modele, Annee, Type) VALUES (?, ?, ?, ?)";
            if (executeQuery($conn, $sql, [$marque, $modele, $annee, $type])) {
                setFlash('success', 'Voiture ajoutée avec succès!');
            } else {
                setFlash('danger', 'Erreur lors de l\'ajout.');
            }
        }
        header("Location: cars.php");
        exit();
    }
    
    if ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $marque = $_POST['marque'] ?? '';
        $modele = $_POST['modele'] ?? '';
        $annee = $_POST['annee'] ?? null;
        $type = $_POST['type'] ?? '4x4';
        
        if ($id && !empty($marque) && !empty($modele)) {
            $sql = "UPDATE cars SET Marque = ?, Modele = ?, Annee = ?, Type = ? WHERE Id = ?";
            if (executeQuery($conn, $sql, [$marque, $modele, $annee, $type, $id])) {
                setFlash('success', 'Voiture modifiée avec succès!');
            }
        }
        header("Location: cars.php");
        exit();
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            // Soft delete - just mark as inactive
            $sql = "UPDATE cars SET IsActive = 0 WHERE Id = ?";
            if (executeQuery($conn, $sql, [$id])) {
                setFlash('success', 'Voiture archivée.');
            }
        }
        header("Location: cars.php");
        exit();
    }
    
    if ($action === 'restore') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $sql = "UPDATE cars SET IsActive = 1 WHERE Id = ?";
            if (executeQuery($conn, $sql, [$id])) {
                setFlash('success', 'Voiture restaurée.');
            }
        }
        header("Location: cars.php");
        exit();
    }
}

// Get car for editing
$car = null;
if ($action === 'edit' && $id) {
    $car = getRow($conn, "SELECT * FROM cars WHERE Id = ?", [$id]);
}

// Get all cars
$show_archived = isset($_GET['archived']) ? 1 : 0;
if ($show_archived) {
    $cars = getData($conn, "SELECT * FROM cars WHERE IsActive = 0 ORDER BY Marque, Modele");
} else {
    $cars = getData($conn, "SELECT * FROM cars WHERE IsActive = 1 ORDER BY Marque, Modele");
}

// Get statistics
$total_active = getRow($conn, "SELECT COUNT(*) as count FROM cars WHERE IsActive = 1")['count'] ?? 0;
$total_archived = getRow($conn, "SELECT COUNT(*) as count FROM cars WHERE IsActive = 0")['count'] ?? 0;

require 'includes/header.php';
?>

<div class="page-header">
    <h1>🚗 Gestion des Voitures</h1>
    <div class="header-actions">
        <?php if ($show_archived): ?>
            <a href="cars.php" class="btn btn-secondary">
                📋 Voitures Actives
            </a>
        <?php else: ?>
            <a href="?archived=1" class="btn btn-secondary">
                📦 Voitures Archivées (<?php echo $total_archived; ?>)
            </a>
        <?php endif; ?>
        <a href="?action=add" class="btn btn-primary">
            ➕ Ajouter Voiture
        </a>
    </div>
</div>

<?php displayFlashMessage(); ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- ADD/EDIT FORM -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <?php echo $action === 'add' ? '➕ Nouvelle Voiture' : '✏️ Modifier Voiture'; ?>
            </h3>
            <a href="cars.php" class="btn btn-secondary">
                ← Retour
            </a>
        </div>
        
        <form method="POST" action="cars.php">
            <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'add' : 'update'; ?>">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <td class="required">Marque</td>
                    <td>
                        <input type="text" 
                               name="marque" 
                               class="form-control" 
                               value="<?php echo $car ? e($car['Marque']) : ''; ?>" 
                               placeholder="Ex: Toyota, Nissan, Ford..."
                               required>
                    </td>
                </tr>
                <tr>
                    <td class="required">Modèle</td>
                    <td>
                        <input type="text" 
                               name="modele" 
                               class="form-control" 
                               value="<?php echo $car ? e($car['Modele']) : ''; ?>" 
                               placeholder="Ex: Hilux, Patrol, Ranger..."
                               required>
                    </td>
                </tr>
                <tr>
                    <td>Année</td>
                    <td>
                        <input type="number" 
                               name="annee" 
                               class="form-control" 
                               value="<?php echo $car ? $car['Annee'] : date('Y'); ?>" 
                               min="1990" 
                               max="<?php echo date('Y') + 1; ?>">
                    </td>
                </tr>
                <tr>
                    <td>Type</td>
                    <td>
                        <select name="type" class="form-select">
                            <option value="4x4" <?php echo ($car && $car['Type'] === '4x4') ? 'selected' : ''; ?>>4x4</option>
                            <option value="SUV" <?php echo ($car && $car['Type'] === 'SUV') ? 'selected' : ''; ?>>SUV</option>
                            <option value="Pickup" <?php echo ($car && $car['Type'] === 'Pickup') ? 'selected' : ''; ?>>Pickup</option>
                            <option value="Autre" <?php echo ($car && $car['Type'] === 'Autre') ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <div style="padding: 1rem; text-align: right; background: #f8f9fa; border-top: 1px solid #ddd;">
                <a href="cars.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo $action === 'add' ? '➕ Ajouter' : '💾 Sauvegarder'; ?>
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- LIST VIEW -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <?php echo $show_archived ? '📦 Voitures Archivées' : '📋 Liste des Voitures'; ?> 
                (<?php echo count($cars); ?>)
            </h3>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">Marque</th>
                        <th width="25%">Modèle</th>
                        <th width="10%">Année</th>
                        <th width="15%">Type</th>
                        <th width="15%">Ajouté le</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cars)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                <?php echo $show_archived ? 'Aucune voiture archivée.' : 'Aucune voiture enregistrée.'; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?php echo $car['Id']; ?></td>
                                <td><strong><?php echo e($car['Marque']); ?></strong></td>
                                <td><?php echo e($car['Modele']); ?></td>
                                <td><?php echo $car['Annee'] ?? '-'; ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $car['Type'] === '4x4' ? 'primary' : 
                                            ($car['Type'] === 'Pickup' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo $car['Type']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($car['CreationDate'])); ?></td>
                                <td>
                                    <?php if ($show_archived): ?>
                                        <form method="POST" action="cars.php" style="display: inline;">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="id" value="<?php echo $car['Id']; ?>">
                                            <button type="submit" class="btn-action" title="Restaurer">
                                                ♻️
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="?action=edit&id=<?php echo $car['Id']; ?>" 
                                           class="btn-action" 
                                           title="Modifier">
                                            ✏️
                                        </a>
                                        <form method="POST" action="cars.php" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $car['Id']; ?>">
                                            <button type="submit" 
                                                    class="btn-action" 
                                                    title="Archiver"
                                                    onclick="return confirm('Archiver cette voiture?')">
                                                🗑️
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<style>
.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.badge-primary { background: #007bff; color: white; }
.badge-warning { background: #ffc107; color: #000; }
.badge-info { background: #17a2b8; color: white; }
</style>

<?php require 'includes/footer.php'; ?>
