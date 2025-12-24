<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Suivie de projet - Wertani Service';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_from_project') {
        $project_id = $_POST['project_id'] ?? 0;
        
        if ($project_id) {
            // Get project details
            $project = getRow($conn, "SELECT * FROM Project WHERE Id = ?", [$project_id]);
            
            if ($project) {
                // Create Suivi Projet
                $sql = "INSERT INTO suivi_projet 
                        (Project_Id, Client_Id, Voiture, Matricule, Title, Description, 
                         MontantProjet, CptsCharger, StatutProjet, DateDebut) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'Wertani Service', 'En cours', ?)";
                
                $date_debut = date('Y-m-d');
                
                if (executeQuery($conn, $sql, [
                    $project_id, $project['Client_Id'], $project['Voiture'], $project['Matricule'],
                    $project['Title'], $project['Description'], $project['FinalAmount'], $date_debut
                ])) {
                    $suivi_id = $conn->lastInsertId();
                    
                    // Copy articles from project
                    $project_articles = getData($conn, "SELECT * FROM ProjectArticles WHERE Project_Id = ?", [$project_id]);
                    foreach ($project_articles as $pa) {
                        $sql_art = "INSERT INTO suivi_projet_articles 
                                   (SuiviProjet_Id, ArticleType, Article_Id, Quantity, UnitPrice, TotalPrice) 
                                   VALUES (?, 'old_article', ?, ?, ?, ?)";
                        executeQuery($conn, $sql_art, [
                            $suivi_id, $pa['Article_Id'], $pa['Quantity'], 
                            $pa['UnitPrice'], $pa['TotalPrice']
                        ]);
                    }
                    
                    setFlash('success', 'Suivi de projet créé avec succès!');
                    header("Location: suivi_projet.php?action=edit&id=" . $suivi_id);
                    exit();
                } else {
                    setFlash('danger', 'Erreur lors de la création du suivi.');
                }
            }
        }
        header("Location: suivi_projet.php");
        exit();
    }
    
    if ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        $montant_projet = $_POST['montant_projet'] ?? 0;
        $avance = $_POST['avance'] ?? 0;
        $cpts_charger = $_POST['cpts_charger'] ?? 'Wertani Service';
        $applique_tva = isset($_POST['applique_tva']) ? 1 : 0;
        $taux_tva = $_POST['taux_tva'] ?? 19;
        $statut_projet = $_POST['statut_projet'] ?? 'En cours';
        $date_debut = $_POST['date_debut'] ?? null;
        $date_fin = $_POST['date_fin'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        // Payment fields
        $etat = isset($_POST['etat_payee']) ? 'Payee' : 'Non Payee';
        $payee_le = null;
        $type_paiement = null;
        $num_virement = null;
        $num_cheque = null;
        
        if ($etat === 'Payee') {
            $payee_le = $_POST['payee_le'] ?? null;
            $type_paiement = $_POST['type_paiement'] ?? null;
            
            if ($type_paiement === 'Virement') {
                $num_virement = $_POST['num_virement'] ?? null;
            } elseif ($type_paiement === 'Chèque') {
                $num_cheque = $_POST['num_cheque'] ?? null;
            }
        }
        
        $comptabiliser = isset($_POST['comptabiliser']) ? 1 : 0;
        $entite = $_POST['entite'] ?? 'Wertani Services';
        
        if ($id) {
            $sql = "UPDATE suivi_projet SET 
                    MontantProjet = ?, Avance = ?, CptsCharger = ?, AppliqueTVA = ?, TauxTVA = ?,
                    StatutProjet = ?, DateDebut = ?, DateFin = ?, Notes = ?,
                    Etat = ?, PayeeLe = ?, TypePaiement = ?, NumVirement = ?, NumCheque = ?, 
                    Comptabiliser = ?, Entite = ?
                    WHERE Id = ?";
            
            if (executeQuery($conn, $sql, [
                $montant_projet, $avance, $cpts_charger, $applique_tva, $taux_tva,
                $statut_projet, $date_debut, $date_fin, $notes,
                $etat, $payee_le, $type_paiement, $num_virement, $num_cheque,
                $comptabiliser, $entite, $id
            ])) {
                setFlash('success', 'Suivi de projet mis à jour avec succès!');
            } else {
                setFlash('danger', 'Erreur lors de la mise à jour.');
            }
        }
        
        header("Location: suivi_projet.php?action=edit&id=" . $id);
        exit();
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($id) {
            if (executeQuery($conn, "DELETE FROM suivi_projet WHERE Id = ?", [$id])) {
                setFlash('success', 'Suivi supprimé avec succès!');
            } else {
                setFlash('danger', 'Erreur lors de la suppression.');
            }
        }
        header("Location: suivi_projet.php");
        exit();
    }
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$suivi = null;
$suivi_articles = [];

if ($action === 'edit' && $id) {
    $suivi = getRow($conn, "SELECT * FROM suivi_projet WHERE Id = ?", [$id]);
    $suivi_articles = getData($conn, "SELECT * FROM suivi_projet_articles WHERE SuiviProjet_Id = ?", [$id]);
}

// Get all suivis
$suivis = getData($conn, "
    SELECT s.*, c.Name as client_name
    FROM suivi_projet s
    LEFT JOIN Client c ON s.Client_Id = c.Id
    ORDER BY s.CreationDate DESC
");

// Get projects that are "En cours" but don't have a suivi yet
$projects_sans_suivi = getData($conn, "
    SELECT p.*, c.Name as client_name
    FROM Project p
    LEFT JOIN Client c ON p.Client_Id = c.Id
    LEFT JOIN suivi_projet s ON p.Id = s.Project_Id
    WHERE p.ProjectType_Id = 2 
    AND s.Id IS NULL
    ORDER BY p.CreationDate DESC
");

require 'includes/header.php';
?>

<?php displayFlash(); ?>

<?php if ($action === 'list'): ?>
<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🔄 Suivie de projet (<?php echo count($suivis); ?>)</h3>
    </div>
    
    <?php if (!empty($projects_sans_suivi)): ?>
    <div style="padding: 1rem; background: #fff3cd; border-bottom: 2px solid #ffc107;">
        <h4 style="color: #856404; margin-bottom: 0.5rem;">⚠️ Projets En cours sans suivi</h4>
        <p style="margin-bottom: 0.5rem; color: #856404;">Ces projets ont été confirmés mais n'ont pas encore de suivi:</p>
        <?php foreach ($projects_sans_suivi as $proj): ?>
        <form method="POST" style="display: inline-block; margin-right: 1rem; margin-bottom: 0.5rem;">
            <input type="hidden" name="action" value="create_from_project">
            <input type="hidden" name="project_id" value="<?php echo $proj['Id']; ?>">
            <button type="submit" class="btn btn-sm btn-warning">
                ➕ <?php echo e($proj['ProjectNumber']); ?> - <?php echo e($proj['Title']); ?>
            </button>
        </form>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>N° Suivi</th>
                <th>Titre</th>
                <th>Client</th>
                <th>Montant TTC</th>
                <th>Reste à Payer</th>
                <th>Statut</th>
                <th>État</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($suivis)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: #999;">
                        Aucun suivi trouvé. Confirmez un projet pour créer un suivi.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($suivis as $sv): ?>
                <tr>
                    <td><strong><?php echo e($sv['NumSuivi']); ?></strong></td>
                    <td><?php echo e($sv['Title']); ?></td>
                    <td><?php echo e($sv['client_name'] ?? '-'); ?></td>
                    <td><strong><?php echo formatCurrency($sv['MontantTTC']); ?></strong></td>
                    <td><strong style="color: <?php echo $sv['ResteAPayer'] > 0 ? 'red' : 'green'; ?>;">
                        <?php echo formatCurrency($sv['ResteAPayer']); ?>
                    </strong></td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $sv['StatutProjet'] === 'En cours' ? 'warning' : 
                                ($sv['StatutProjet'] === 'Terminé' ? 'success' : 'danger'); 
                        ?>">
                            <?php echo e($sv['StatutProjet']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $sv['Etat'] === 'Payee' ? 'success' : 'danger'; ?>">
                            <?php echo e($sv['Etat']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?action=edit&id=<?php echo $sv['Id']; ?>" class="btn btn-sm btn-primary">
                            ✏️ Gérer
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce suivi?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $sv['Id']; ?>">
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

<?php else: ?>
<!-- Edit Form -->
<?php include 'suivi_projet_form.php'; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
