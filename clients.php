<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Gestion des Clients - Wertani Service';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Client data
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $client_type = $_POST['client_type'] ?? 'Normal';
        $fiscal_matricule = $_POST['fiscal_matricule'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (!empty($name) && !empty($phone)) {
            try {
                // Insert client
                $sql = "INSERT INTO Client (Name, PhoneNumber, Email, Address, City, Type, FiscalMatricule, Notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                if (executeQuery($conn, $sql, [$name, $phone, $email, $address, $city, $client_type, $fiscal_matricule, $notes])) {
                    setFlash('success', 'Client ajouté avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de l\'ajout.');
                }
            } catch (Exception $e) {
                setFlash('danger', 'Erreur: ' . $e->getMessage());
            }
        } else {
            setFlash('danger', 'Le nom et téléphone sont requis.');
        }
        
        header("Location: clients.php");
        exit();
    }
    
    if ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $client_type = $_POST['client_type'] ?? 'Normal';
        $fiscal_matricule = $_POST['fiscal_matricule'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if ($id && !empty($name) && !empty($phone)) {
            $sql = "UPDATE Client SET Name = ?, PhoneNumber = ?, Email = ?, Address = ?, City = ?, Type = ?, FiscalMatricule = ?, Notes = ? WHERE Id = ?";
            if (executeQuery($conn, $sql, [$name, $phone, $email, $address, $city, $client_type, $fiscal_matricule, $notes, $id])) {
                setFlash('success', 'Client modifié avec succès!');
            } else {
                setFlash('danger', 'Erreur lors de la modification.');
            }
        }
        
        header("Location: clients.php");
        exit();
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($id) {
            if (executeQuery($conn, "DELETE FROM Client WHERE Id = ?", [$id])) {
                setFlash('success', 'Client supprimé avec succès!');
            } else {
                setFlash('danger', 'Erreur lors de la suppression.');
            }
        }
        
        header("Location: clients.php");
        exit();
    }
}

$action = $_GET['action'] ?? 'list';
$client_id = $_GET['id'] ?? 0;

$client = null;
if ($action === 'edit' && $client_id) {
    $client = getRow($conn, "SELECT * FROM Client WHERE Id = ?", [$client_id]);
}

$clients = getData($conn, "SELECT * FROM Client ORDER BY CreationDate DESC");
?>

<?php include 'includes/header.php'; ?>

<?php displayFlash(); ?>

<?php if ($action === 'list'): ?>
<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">👥 Liste des Clients (<?php echo count($clients); ?>)</h3>
        <a href="?action=add" class="btn btn-primary">
            ➕ Nouveau Client
        </a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Ville</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clients)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #999;">
                        Aucun client trouvé. Cliquez sur "Nouveau Client" pour ajouter un client.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td><strong>#<?php echo $c['Id']; ?></strong></td>
                    <td><strong><?php echo e($c['Name']); ?></strong></td>
                    <td><?php echo e($c['PhoneNumber']); ?></td>
                    <td><?php echo e($c['Email'] ?? '-'); ?></td>
                    <td><?php echo e($c['City'] ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $c['Type'] === 'Societe' ? 'badge-info' : 'badge-secondary'; ?>">
                            <?php echo e($c['Type']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?action=edit&id=<?php echo $c['Id']; ?>" class="btn btn-sm btn-primary">
                            ✏️ Modifier
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $c['Id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                🗑️ Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<!-- Add/Edit Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $action === 'add' ? '➕ Nouveau Client' : '✏️ Modifier Client'; ?>
        </h3>
        <a href="clients.php" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'add' : 'update'; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $client_id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <td class="required">Nom</td>
                <td>
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo ($client && isset($client['Name'])) ? e($client['Name']) : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td class="required">Téléphone</td>
                <td>
                    <input type="tel" name="phone" class="form-control" 
                           value="<?php echo ($client && isset($client['PhoneNumber'])) ? e($client['PhoneNumber']) : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td>Email</td>
                <td>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo ($client && isset($client['Email'])) ? e($client['Email']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Type de Client</td>
                <td>
                    <select name="client_type" id="client_type" class="form-select" onchange="toggleFiscalMatricule()">
                        <option value="Normal" <?php echo ($client && $client['Type'] === 'Normal') ? 'selected' : ''; ?>>
                            Normal
                        </option>
                        <option value="Societe" <?php echo ($client && $client['Type'] === 'Societe') ? 'selected' : ''; ?>>
                            Société
                        </option>
                    </select>
                </td>
            </tr>
            <tr id="fiscal_matricule_row" style="display: <?php echo ($client && $client['Type'] === 'Societe') ? 'table-row' : 'none'; ?>;">
                <td>Matricule Fiscal <span style="color: #999;">(Société uniquement)</span></td>
                <td>
                    <input type="text" name="fiscal_matricule" id="fiscal_matricule" class="form-control" 
                           value="<?php echo ($client && isset($client['FiscalMatricule'])) ? e($client['FiscalMatricule']) : ''; ?>"
                           placeholder="Ex: 1234567ABC">
                </td>
            </tr>
            <tr>
                <td>Adresse</td>
                <td>
                    <textarea name="address" class="form-control" rows="2"><?php echo ($client && isset($client['Address'])) ? e($client['Address']) : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td>Ville</td>
                <td>
                    <input type="text" name="city" class="form-control" 
                           value="<?php echo ($client && isset($client['City'])) ? e($client['City']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Notes</td>
                <td>
                    <textarea name="notes" class="form-control" rows="3"><?php echo ($client && isset($client['Notes'])) ? e($client['Notes']) : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="background: white; text-align: right; padding: 1rem;">
                    <button type="submit" class="btn btn-lg btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="clients.php" class="btn btn-lg btn-secondary">
                        ❌ Annuler
                    </a>
                </td>
            </tr>
        </table>
    </form>
</div>

<?php if ($action === 'edit' && $client): ?>
<!-- Client Details Display -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 Informations du Client</h3>
    </div>
    
    <div class="coordinates-table">
        <div class="coordinate-item">
            <label>Nom</label>
            <div class="value"><?php echo e($client['Name']); ?></div>
        </div>
        <div class="coordinate-item">
            <label>Téléphone</label>
            <div class="value"><?php echo e($client['PhoneNumber']); ?></div>
        </div>
        <div class="coordinate-item">
            <label>Email</label>
            <div class="value"><?php echo e($client['Email'] ?? '-'); ?></div>
        </div>
        <div class="coordinate-item">
            <label>Type</label>
            <div class="value"><?php echo e($client['Type']); ?></div>
        </div>
        <div class="coordinate-item">
            <label>Ville</label>
            <div class="value"><?php echo e($client['City'] ?? '-'); ?></div>
        </div>
        <?php if ($client['Type'] === 'Societe' && $client['FiscalMatricule']): ?>
        <div class="coordinate-item">
            <label>Matricule Fiscal</label>
            <div class="value"><?php echo e($client['FiscalMatricule']); ?></div>
        </div>
        <?php endif; ?>
        <?php if ($client['Address']): ?>
        <div class="coordinate-item" style="grid-column: 1 / -1;">
            <label>Adresse</label>
            <div class="value"><?php echo e($client['Address']); ?></div>
        </div>
        <?php endif; ?>
        <?php if ($client['Notes']): ?>
        <div class="coordinate-item" style="grid-column: 1 / -1;">
            <label>Notes</label>
            <div class="value"><?php echo e($client['Notes']); ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; // End of if ($action === 'list') elseif ($action === 'add' || $action === 'edit') ?>

<script>
function toggleFiscalMatricule() {
    const clientType = document.getElementById('client_type').value;
    const fiscalRow = document.getElementById('fiscal_matricule_row');
    const fiscalInput = document.getElementById('fiscal_matricule');
    
    if (clientType === 'Societe') {
        fiscalRow.style.display = 'table-row';
    } else {
        fiscalRow.style.display = 'none';
        fiscalInput.value = ''; // Clear value if switching to Normal
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('client_type')) {
        toggleFiscalMatricule();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
