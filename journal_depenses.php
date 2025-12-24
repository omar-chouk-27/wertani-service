<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Journal Dépenses - Wertani Service';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';
    
    if ($post_action === 'add' || $post_action === 'update') {
        $id = $_POST['id'] ?? 0;
        $date_achat = $_POST['date_achat'] ?? date('Y-m-d');
        $num_doc = $_POST['num_doc'] ?? '';
        $fournisseur_id = $_POST['fournisseur_id'] ?? null;
        $type_doc = $_POST['type_doc'] ?? 'Bon de livraison';
        $cpts_charger = $_POST['cpts_charger'] ?? 'Wertani Service';
        $taux_tva = $_POST['taux_tva'] ?? 0;
        $montant_ttc = $_POST['montant_ttc'] ?? 0;
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
            } elseif ($type_paiement === 'Cheque') {
                $num_cheque = $_POST['num_cheque'] ?? null;
            }
        }
        
        $comptabiliser = isset($_POST['comptabiliser']) ? 1 : 0;
        $entite = $_POST['entite'] ?? 'Wertani Services';
        
        if (!empty($num_doc) && !empty($montant_ttc)) {
            if ($post_action === 'add') {
                $sql = "INSERT INTO journal_depenses 
                        (DateAchat, NumDoc, Fournisseur_Id, TypeDoc, CptsCharger, TauxTVA, MontantTTC, 
                         Etat, PayeeLe, TypePaiement, NumVirement, NumCheque, Comptabiliser, Entite, Notes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                if (executeQuery($conn, $sql, [
                    $date_achat, $num_doc, $fournisseur_id, $type_doc, $cpts_charger, 
                    $taux_tva, $montant_ttc, $etat, $payee_le, $type_paiement, 
                    $num_virement, $num_cheque, $comptabiliser, $entite, $notes
                ])) {
                    setFlash('success', 'Dépense ajoutée avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de l\'ajout.');
                }
            } else {
                $sql = "UPDATE journal_depenses SET 
                        DateAchat = ?, NumDoc = ?, Fournisseur_Id = ?, TypeDoc = ?, CptsCharger = ?, 
                        TauxTVA = ?, MontantTTC = ?, Etat = ?, PayeeLe = ?, TypePaiement = ?, 
                        NumVirement = ?, NumCheque = ?, Comptabiliser = ?, Entite = ?, Notes = ? 
                        WHERE Id = ?";
                
                if (executeQuery($conn, $sql, [
                    $date_achat, $num_doc, $fournisseur_id, $type_doc, $cpts_charger, 
                    $taux_tva, $montant_ttc, $etat, $payee_le, $type_paiement, 
                    $num_virement, $num_cheque, $comptabiliser, $entite, $notes, $id
                ])) {
                    setFlash('success', 'Dépense modifiée avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la modification.');
                }
            }
        } else {
            setFlash('danger', 'Le numéro de document et le montant sont requis.');
        }
        
        header("Location: journal_depenses.php");
        exit();
    }
    
    if ($post_action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($id) {
            if (executeQuery($conn, "DELETE FROM journal_depenses WHERE Id = ?", [$id])) {
                setFlash('success', 'Dépense supprimée avec succès!');
            } else {
                setFlash('danger', 'Erreur lors de la suppression.');
            }
        }
        header("Location: journal_depenses.php");
        exit();
    }
    
    // Group bons de livraison into facture
    if ($post_action === 'group_bons') {
        $bon_ids = $_POST['bon_ids'] ?? [];
        $facture_num = $_POST['facture_num'] ?? '';
        $facture_date = $_POST['facture_date'] ?? date('Y-m-d');
        
        if (!empty($bon_ids) && !empty($facture_num)) {
            // Get total from selected bons
            $placeholders = str_repeat('?,', count($bon_ids) - 1) . '?';
            $bons = getData($conn, "SELECT * FROM journal_depenses WHERE Id IN ($placeholders)", $bon_ids);
            
            $total_ttc = 0;
            $fournisseur_id = null;
            $cpts_charger = null;
            
            foreach ($bons as $bon) {
                $total_ttc += $bon['MontantTTC'];
                if (!$fournisseur_id) $fournisseur_id = $bon['Fournisseur_Id'];
                if (!$cpts_charger) $cpts_charger = $bon['CptsCharger'];
            }
            
            // Create facture
            $sql = "INSERT INTO journal_depenses 
                    (DateAchat, NumDoc, Fournisseur_Id, TypeDoc, CptsCharger, TauxTVA, MontantTTC, 
                     Etat, Comptabiliser, Notes) 
                    VALUES (?, ?, ?, 'Facture', ?, 0, ?, 'Non Payee', 1, ?)";
            
            $notes = 'Facture créée à partir de ' . count($bon_ids) . ' bon(s) de livraison';
            
            if (executeQuery($conn, $sql, [$facture_date, $facture_num, $fournisseur_id, $cpts_charger, $total_ttc, $notes])) {
                $facture_id = $conn->lastInsertId();
                
                // Link bons to this facture
                foreach ($bon_ids as $bon_id) {
                    executeQuery($conn, "UPDATE journal_depenses SET FactureParent_Id = ? WHERE Id = ?", [$facture_id, $bon_id]);
                }
                
                setFlash('success', 'Facture créée avec succès à partir des bons de livraison!');
            } else {
                setFlash('danger', 'Erreur lors de la création de la facture.');
            }
        }
        header("Location: journal_depenses.php");
        exit();
    }
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$depense = null;
if ($action === 'edit' && $id) {
    $depense = getRow($conn, "SELECT * FROM journal_depenses WHERE Id = ?", [$id]);
}

// Get filter
$type_doc_filter = $_GET['type_doc'] ?? 'all'; // all, bon, facture

// Get all depenses with filter
$where_clause = "";
if ($type_doc_filter === 'bon') {
    $where_clause = " WHERE d.TypeDoc = 'Bon de livraison'";
} elseif ($type_doc_filter === 'facture') {
    $where_clause = " WHERE d.TypeDoc = 'Facture'";
}

$depenses = getData($conn, "
    SELECT d.*, s.name as fournisseur_name
    FROM journal_depenses d
    LEFT JOIN supplier s ON d.Fournisseur_Id = s.id
    $where_clause
    ORDER BY d.DateAchat DESC, d.CreationDate DESC
");

// Get counts for tabs
$count_all = getRow($conn, "SELECT COUNT(*) as total FROM journal_depenses")['total'] ?? 0;
$count_bon = getRow($conn, "SELECT COUNT(*) as total FROM journal_depenses WHERE TypeDoc = 'Bon de livraison'")['total'] ?? 0;
$count_facture = getRow($conn, "SELECT COUNT(*) as total FROM journal_depenses WHERE TypeDoc = 'Facture'")['total'] ?? 0;

// Get bons de livraison that aren't linked to a facture (for grouping)
$bons_available = getData($conn, "
    SELECT d.*, s.name as fournisseur_name
    FROM journal_depenses d
    LEFT JOIN supplier s ON d.Fournisseur_Id = s.id
    WHERE d.TypeDoc = 'Bon de livraison' 
    AND d.FactureParent_Id IS NULL
    ORDER BY s.name, d.DateAchat DESC
");

// Get suppliers for dropdown
$suppliers = getData($conn, "SELECT id, name FROM supplier ORDER BY name ASC");

require 'includes/header.php';
?>

<style>
.doc-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 3px solid var(--primary);
    padding-bottom: 0.5rem;
}

.doc-tab {
    padding: 0.75rem 1.5rem;
    background: white;
    border: 2px solid #ddd;
    border-radius: 8px 8px 0 0;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    color: #333;
    font-size: 1.05rem;
}

.doc-tab:hover {
    background: #f5f5f5;
}

.doc-tab.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.doc-tab .count {
    background: rgba(255,255,255,0.3);
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    margin-left: 0.5rem;
    font-size: 0.9rem;
}

.doc-tab.active .count {
    background: rgba(255,255,255,0.4);
}
</style>

<?php displayFlash(); ?>

<?php if ($action === 'list'): ?>

<!-- Document Type Tabs -->
<div class="doc-tabs">
    <a href="?type_doc=all" class="doc-tab <?php echo $type_doc_filter === 'all' ? 'active' : ''; ?>">
        📋 Tous <span class="count"><?php echo $count_all; ?></span>
    </a>
    <a href="?type_doc=bon" class="doc-tab <?php echo $type_doc_filter === 'bon' ? 'active' : ''; ?>">
        📦 Bons de Livraison <span class="count"><?php echo $count_bon; ?></span>
    </a>
    <a href="?type_doc=facture" class="doc-tab <?php echo $type_doc_filter === 'facture' ? 'active' : ''; ?>">
        🧾 Factures <span class="count"><?php echo $count_facture; ?></span>
    </a>
</div>

<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">💰 Journal des Dépenses (<?php echo count($depenses); ?>)</h3>
        <div style="display: flex; gap: 0.5rem;">
            <a href="?action=add" class="btn btn-primary">
                ➕ Nouvelle Dépense
            </a>
            <?php if (!empty($bons_available)): ?>
            <button type="button" class="btn btn-success" onclick="document.getElementById('group-bons-modal').style.display='block'">
                📋 Grouper Bons → Facture (<?php echo count($bons_available); ?>)
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>N° Doc</th>
                <th>Fournisseur</th>
                <th>Type</th>
                <th>Cpts Chargé</th>
                <th>Montant TTC</th>
                <th>État</th>
                <th>Comptabilisé</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($depenses)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2rem; color: #999;">
                        Aucune dépense trouvée. Cliquez sur "Nouvelle Dépense" pour ajouter.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($depenses as $dep): ?>
                <tr>
                    <td><?php echo formatDate($dep['DateAchat']); ?></td>
                    <td><strong><?php echo e($dep['NumDoc']); ?></strong></td>
                    <td><?php echo e($dep['fournisseur_name'] ?? '-'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $dep['TypeDoc'] === 'Facture' ? 'success' : 'info'; ?>">
                            <?php echo e($dep['TypeDoc']); ?>
                        </span>
                        <?php if ($dep['FactureParent_Id']): ?>
                            <br><small style="color: #999;">→ Lié à facture</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $dep['CptsCharger'] === 'Wertani Saber' ? 'warning' : 'primary'; ?>">
                            <?php echo e($dep['CptsCharger']); ?>
                        </span>
                    </td>
                    <td><strong><?php echo formatCurrency($dep['MontantTTC']); ?></strong></td>
                    <td>
                        <span class="badge badge-<?php echo $dep['Etat'] === 'Payee' ? 'success' : 'danger'; ?>">
                            <?php echo e($dep['Etat']); ?>
                        </span>
                        <?php if ($dep['Etat'] === 'Payee' && $dep['PayeeLe']): ?>
                            <br><small><?php echo formatDate($dep['PayeeLe']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($dep['Comptabiliser']): ?>
                            <span class="badge badge-success">✓ Oui</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">✗ Non</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?action=edit&id=<?php echo $dep['Id']; ?>" class="btn btn-sm btn-primary">
                            ✏️
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette dépense?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $dep['Id']; ?>">
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

<!-- Group Bons Modal -->
<?php if (!empty($bons_available)): ?>
<div id="group-bons-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow: auto;">
    <div style="background: white; max-width: 900px; margin: 2rem auto; padding: 2rem; border-radius: 1rem;">
        <h3 style="margin-bottom: 1rem;">📋 Grouper Bons de Livraison en Facture</h3>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="group_bons">
            
            <div style="margin-bottom: 1rem;">
                <label style="font-weight: bold;">Numéro de Facture:</label>
                <input type="text" name="facture_num" class="form-control" required>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="font-weight: bold;">Date de Facture:</label>
                <input type="date" name="facture_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <p style="margin-bottom: 1rem; color: #666;">Sélectionnez les bons de livraison à grouper:</p>
            
            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem;">
                <?php 
                $current_fournisseur = null;
                foreach ($bons_available as $bon): 
                    if ($current_fournisseur !== $bon['fournisseur_name']):
                        if ($current_fournisseur !== null) echo '</div>';
                        $current_fournisseur = $bon['fournisseur_name'];
                ?>
                    <div style="margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-left: 3px solid var(--primary);">
                        <h4 style="margin-bottom: 0.5rem; color: var(--primary);">🏭 <?php echo e($current_fournisseur ?: 'Sans fournisseur'); ?></h4>
                <?php endif; ?>
                
                <label style="display: block; padding: 0.5rem; margin-bottom: 0.25rem; background: white; border: 1px solid #ddd; cursor: pointer;">
                    <input type="checkbox" name="bon_ids[]" value="<?php echo $bon['Id']; ?>" style="margin-right: 0.5rem;">
                    <strong><?php echo e($bon['NumDoc']); ?></strong> - 
                    <?php echo formatDate($bon['DateAchat']); ?> - 
                    <strong><?php echo formatCurrency($bon['MontantTTC']); ?></strong>
                </label>
                
                <?php endforeach; ?>
                <?php if ($current_fournisseur !== null) echo '</div>'; ?>
            </div>
            
            <div style="text-align: right;">
                <button type="submit" class="btn btn-lg btn-success">✓ Créer Facture</button>
                <button type="button" class="btn btn-lg btn-secondary" onclick="document.getElementById('group-bons-modal').style.display='none'">✗ Annuler</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Add/Edit Form -->
<?php include 'journal_depenses_form.php'; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
