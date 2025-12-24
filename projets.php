<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Gestion des Projets (Devis) - Wertani Service';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'update') {
        $id = $_POST['id'] ?? 0;
        $client_id = $_POST['client_id'] ?? null;
        $car_id = $_POST['car_id'] ?? null;
        $voiture = ''; // Legacy field, we'll populate from CarId if available
        $matricule = $_POST['matricule'] ?? '';
        $type_id = $_POST['type_id'] ?? 1; // Default to Devis
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // If CarId is selected, get car details for Voiture field (legacy compatibility)
        if ($car_id) {
            $car = getRow($conn, "SELECT * FROM cars WHERE Id = ?", [$car_id]);
            if ($car) {
                $voiture = $car['Marque'] . ' ' . $car['Modele'];
                if ($car['Annee']) {
                    $voiture .= ' (' . $car['Annee'] . ')';
                }
            }
        }
        
        // Calculate total from selected articles
        $article_types = $_POST['article_types'] ?? [];
        $article_ids = $_POST['article_ids'] ?? [];
        $article_quantities = $_POST['article_quantities'] ?? [];
        $article_prices = $_POST['article_prices'] ?? [];
        
        $total = 0;
        
        // Calculate total amount
        foreach ($article_ids as $index => $article_id) {
            if (empty($article_id) || $article_id == 0) continue;
            $quantity = $article_quantities[$index] ?? 1;
            $price = $article_prices[$index] ?? 0;
            $total += ($price * $quantity);
        }
        
        if (!empty($title)) {
            if ($action === 'add') {
                $sql = "INSERT INTO Project (Client_Id, CarId, Voiture, Matricule, ProjectType_Id, Title, Description, 
                        TotalAmount, FinalAmount, PaymentStatus, Notes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'En attente', ?)";
                
                if (executeQuery($conn, $sql, [$client_id, $car_id, $voiture, $matricule, $type_id, $title, $description, 
                                               $total, $total, $notes])) {
                    $project_id = $conn->lastInsertId();
                    
                    // Add selected articles
                    foreach ($article_ids as $index => $article_id) {
                        if (empty($article_id) || $article_id == 0) continue;
                        
                        $article_type = $article_types[$index] ?? '';
                        $quantity = $article_quantities[$index] ?? 1;
                        $price = $article_prices[$index] ?? 0;
                        $total_price = $price * $quantity;
                        
                        // Store article with type information
                        $sql_article = "INSERT INTO ProjectArticles (Project_Id, Article_Id, Quantity, UnitPrice, TotalPrice) 
                                       VALUES (?, ?, ?, ?, ?)";
                        executeQuery($conn, $sql_article, [$project_id, $article_id, $quantity, $price, $total_price]);
                    }
                    
                    setFlash('success', 'Devis créé avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la création.');
                }
            } else {
                // Update project
                $sql = "UPDATE Project SET Client_Id = ?, CarId = ?, Voiture = ?, Matricule = ?, ProjectType_Id = ?, 
                        Title = ?, Description = ?, TotalAmount = ?, FinalAmount = ?, Notes = ? 
                        WHERE Id = ?";
                
                if (executeQuery($conn, $sql, [$client_id, $car_id, $voiture, $matricule, $type_id, $title, $description, 
                                               $total, $total, $notes, $id])) {
                    // Delete old articles
                    executeQuery($conn, "DELETE FROM ProjectArticles WHERE Project_Id = ?", [$id]);
                    
                    // Add new articles
                    foreach ($article_ids as $index => $article_id) {
                        if (empty($article_id) || $article_id == 0) continue;
                        
                        $quantity = $article_quantities[$index] ?? 1;
                        $price = $article_prices[$index] ?? 0;
                        $total_price = $price * $quantity;
                        
                        $sql_article = "INSERT INTO ProjectArticles (Project_Id, Article_Id, Quantity, UnitPrice, TotalPrice) 
                                       VALUES (?, ?, ?, ?, ?)";
                        executeQuery($conn, $sql_article, [$id, $article_id, $quantity, $price, $total_price]);
                    }
                    
                    setFlash('success', 'Devis modifié avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la modification.');
                }
            }
        } else {
            setFlash('danger', 'Le titre est requis.');
        }
        
        header("Location: projets.php");
        exit();
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($id) {
            if (executeQuery($conn, "DELETE FROM Project WHERE Id = ?", [$id])) {
                setFlash('success', 'Projet supprimé avec succès!');
            } else {
                setFlash('danger', 'Erreur lors de la suppression.');
            }
        }
        header("Location: projets.php");
        exit();
    }
    
    // CONFIRM DEVIS - Move to "En cours" and create Suivi
    if ($action === 'confirm_devis') {
        $id = $_POST['id'] ?? 0;
        if ($id) {
            // Update project to "En cours"
            if (executeQuery($conn, "UPDATE Project SET ProjectType_Id = 2 WHERE Id = ?", [$id])) {
                setFlash('success', '✅ Devis confirmé! Le projet est maintenant "En cours". Accédez à "Suivie de projet" pour le suivi.');
                
                // Note: Suivi Projet will be created from suivi_projet.php when user accesses it
            } else {
                setFlash('danger', 'Erreur lors de la confirmation.');
            }
        }
        header("Location: projets.php");
        exit();
    }
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$project = null;
$project_articles = [];

if ($action === 'edit' && $id) {
    $project = getRow($conn, "SELECT * FROM Project WHERE Id = ?", [$id]);
    $project_articles = getData($conn, "SELECT * FROM ProjectArticles WHERE Project_Id = ?", [$id]);
}

// Get all projects (Devis and En cours only - Factures handled separately)
$projects = getData($conn, "
    SELECT p.*, 
           c.Name as client_name,
           pt.Name as type_name
    FROM Project p
    LEFT JOIN Client c ON p.Client_Id = c.Id
    LEFT JOIN ProjectType pt ON p.ProjectType_Id = pt.Id
    WHERE p.ProjectType_Id IN (1, 2, 4)
    ORDER BY p.CreationDate DESC
");

// Get clients for dropdown
$clients = getData($conn, "SELECT Id, Name FROM Client ORDER BY Name ASC");

// Get all article types for dropdowns
$articles_ws = getData($conn, "SELECT Id, Name, PrixVente FROM article_ws ORDER BY Name ASC");
$composants = getData($conn, "SELECT Id, Name, MontantTTC as PrixVente FROM composant_matiere ORDER BY Name ASC");
$services = getData($conn, "SELECT Id, Name, PrixService as PrixVente FROM services ORDER BY Name ASC");
$old_articles = getData($conn, "SELECT Id, Name, SellingPrice as PrixVente FROM Article ORDER BY Name ASC");

require 'includes/header.php';
?>

<?php displayFlash(); ?>

<?php if ($action === 'list'): ?>
<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 Projets / Devis (<?php echo count($projects); ?>)</h3>
        <a href="?action=add" class="btn btn-primary">
            ➕ Nouveau Devis
        </a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>N° Projet</th>
                <th>Titre</th>
                <th>Client</th>
                <th>Voiture</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #999;">
                        Aucun projet trouvé. Cliquez sur "Nouveau Devis" pour créer.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($projects as $proj): ?>
                <tr>
                    <td><strong><?php echo e($proj['ProjectNumber'] ?? 'N/A'); ?></strong></td>
                    <td><?php echo e($proj['Title']); ?></td>
                    <td><?php echo e($proj['client_name'] ?? '-'); ?></td>
                    <td>
                        <?php if ($proj['Voiture'] || $proj['Matricule']): ?>
                            <?php echo e($proj['Voiture'] ?? ''); ?>
                            <?php if ($proj['Matricule']): ?>
                                <br><small style="color: #666;"><?php echo e($proj['Matricule']); ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $proj['ProjectType_Id'] == 1 ? 'info' : 
                                ($proj['ProjectType_Id'] == 2 ? 'warning' : 
                                ($proj['ProjectType_Id'] == 4 ? 'danger' : 'success')); 
                        ?>">
                            <?php echo e($proj['type_name']); ?>
                        </span>
                    </td>
                    <td><strong><?php echo formatCurrency($proj['FinalAmount']); ?></strong></td>
                    <td>
                        <?php if ($proj['ProjectType_Id'] == 1): ?>
                            <!-- Devis - can confirm -->
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Confirmer ce devis? Il passera en mode En cours.');">
                                <input type="hidden" name="action" value="confirm_devis">
                                <input type="hidden" name="id" value="<?php echo $proj['Id']; ?>">
                                <button type="submit" class="btn btn-sm btn-success">
                                    ✓ Confirmer
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="?action=edit&id=<?php echo $proj['Id']; ?>" class="btn btn-sm btn-primary">
                            ✏️ Modifier
                        </a>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce projet?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $proj['Id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                🗑️
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card" style="background: #e8f5e9; border-left: 4px solid #4caf50;">
    <div style="padding: 1rem;">
        <h4 style="color: #2e7d32; margin-bottom: 0.5rem;">ℹ️ Comment ça marche?</h4>
        <ol style="margin: 0; padding-left: 1.5rem; color: #1b5e20;">
            <li><strong>Créez un Devis</strong> avec les articles et prix</li>
            <li><strong>Confirmez le Devis</strong> - il passe en "En cours"</li>
            <li><strong>Accédez à "Suivie de projet"</strong> pour le tracking financier et paiement</li>
        </ol>
    </div>
</div>

<?php else: ?>
<!-- Add/Edit Form -->
<?php include 'projets_form.php'; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
