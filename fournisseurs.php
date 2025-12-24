<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Gestion des Fournisseurs - Wertani Service';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'update') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $matricule_fiscale = $_POST['matricule_fiscale'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (!empty($name)) {
            if ($action === 'add') {
                $sql = "INSERT INTO supplier (name, phone, email, address, matricule_fiscale, notes) VALUES (?, ?, ?, ?, ?, ?)";
                if (executeQuery($conn, $sql, [$name, $phone, $email, $address, $matricule_fiscale, $notes])) {
                    setFlash('success', 'Fournisseur ajouté avec succès!');
                }
            } else {
                $sql = "UPDATE supplier SET name = ?, phone = ?, email = ?, address = ?, matricule_fiscale = ?, notes = ? WHERE id = ?";
                if (executeQuery($conn, $sql, [$name, $phone, $email, $address, $matricule_fiscale, $notes, $id])) {
                    setFlash('success', 'Fournisseur modifié avec succès!');
                }
            }
        }
        header("Location: fournisseurs.php");
        exit();
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($id) {
            if (executeQuery($conn, "DELETE FROM supplier WHERE id = ?", [$id])) {
                setFlash('success', 'Fournisseur supprimé avec succès!');
            }
        }
        header("Location: fournisseurs.php");
        exit();
    }
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$supplier = null;
if ($action === 'edit' && $id) {
    $supplier = getRow($conn, "SELECT * FROM supplier WHERE id = ?", [$id]);
}

$suppliers = getData($conn, "SELECT * FROM supplier ORDER BY name ASC");

require 'includes/header.php';
?>

<?php displayFlash(); ?>

<?php if ($action === 'list'): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📦 Fournisseurs (<?php echo count($suppliers); ?>)</h3>
        <a href="?action=add" class="btn btn-primary">➕ Nouveau Fournisseur</a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Matricule Fiscale</th>
                <th>Adresse</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($suppliers)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">
                        Aucun fournisseur trouvé.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($suppliers as $sup): ?>
                <tr>
                    <td><strong><?php echo e($sup['name']); ?></strong></td>
                    <td><?php echo e($sup['phone'] ?? '-'); ?></td>
                    <td><?php echo e($sup['email'] ?? '-'); ?></td>
                    <td><?php echo e($sup['matricule_fiscale'] ?? '-'); ?></td>
                    <td><?php echo e($sup['address'] ?? '-'); ?></td>
                    <td>
                        <a href="?action=edit&id=<?php echo $sup['id']; ?>" class="btn btn-sm btn-primary">✏️</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce fournisseur?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $sup['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php else: ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?php echo $action === 'add' ? '➕ Nouveau Fournisseur' : '✏️ Modifier Fournisseur'; ?></h3>
        <a href="fournisseurs.php" class="btn btn-secondary">← Retour</a>
    </div>
    
    <form method="POST">
        <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'add' : 'update'; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <td class="required">Nom</td>
                <td>
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo ($supplier && isset($supplier['name'])) ? e($supplier['name']) : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td>Téléphone</td>
                <td>
                    <input type="text" name="phone" class="form-control" 
                           value="<?php echo ($supplier && isset($supplier['phone'])) ? e($supplier['phone']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Email</td>
                <td>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo ($supplier && isset($supplier['email'])) ? e($supplier['email']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Adresse</td>
                <td>
                    <textarea name="address" class="form-control" rows="2"><?php echo ($supplier && isset($supplier['address'])) ? e($supplier['address']) : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td>Matricule Fiscale</td>
                <td>
                    <input type="text" name="matricule_fiscale" class="form-control" 
                           value="<?php echo ($supplier && isset($supplier['matricule_fiscale'])) ? e($supplier['matricule_fiscale']) : ''; ?>"
                           placeholder="Ex: 1234567/A/M/000">
                    <small style="color: #666;">Numéro de matricule fiscale du fournisseur</small>
                </td>
            </tr>
            <tr>
                <td>Notes</td>
                <td>
                    <textarea name="notes" class="form-control" rows="2"><?php echo ($supplier && isset($supplier['notes'])) ? e($supplier['notes']) : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right; padding: 1rem;">
                    <button type="submit" class="btn btn-lg btn-success">💾 Enregistrer</button>
                    <a href="fournisseurs.php" class="btn btn-lg btn-secondary">❌ Annuler</a>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
