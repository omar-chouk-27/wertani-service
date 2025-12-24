<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Gestion des Employés - Wertani Service';

// Get action
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';
    
    if ($post_action === 'add' || $post_action === 'update') {
        $id = $_POST['id'] ?? 0;
        $matricule = $_POST['matricule'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $cin = $_POST['cin'] ?? '';
        $date_naissance = $_POST['date_naissance'] ?? null;
        $telephone = $_POST['telephone'] ?? '';
        $email = $_POST['email'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        $poste = $_POST['poste'] ?? '';
        $date_embauche = $_POST['date_embauche'] ?? null;
        $salaire = $_POST['salaire'] ?? 0;
        $type_contrat = $_POST['type_contrat'] ?? 'CDI';
        $statut = $_POST['statut'] ?? 'Actif';
        $num_cnss = $_POST['num_cnss'] ?? '';
        $rib = $_POST['rib'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (!empty($nom) && !empty($prenom)) {
            if ($post_action === 'add') {
                $sql = "INSERT INTO employees (Matricule, Nom, Prenom, CIN, DateNaissance, Telephone, Email, Adresse, 
                        Poste, DateEmbauche, Salaire, TypeContrat, Statut, NumCNSS, RIB, Notes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if (executeQuery($conn, $sql, [
                    $matricule, $nom, $prenom, $cin, $date_naissance, $telephone, $email, $adresse,
                    $poste, $date_embauche, $salaire, $type_contrat, $statut, $num_cnss, $rib, $notes
                ])) {
                    setFlash('success', 'Employé ajouté avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de l\'ajout.');
                }
            } else {
                $sql = "UPDATE employees SET Matricule = ?, Nom = ?, Prenom = ?, CIN = ?, DateNaissance = ?, 
                        Telephone = ?, Email = ?, Adresse = ?, Poste = ?, DateEmbauche = ?, Salaire = ?, 
                        TypeContrat = ?, Statut = ?, NumCNSS = ?, RIB = ?, Notes = ? 
                        WHERE Id = ?";
                if (executeQuery($conn, $sql, [
                    $matricule, $nom, $prenom, $cin, $date_naissance, $telephone, $email, $adresse,
                    $poste, $date_embauche, $salaire, $type_contrat, $statut, $num_cnss, $rib, $notes, $id
                ])) {
                    setFlash('success', 'Employé modifié avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la modification.');
                }
            }
        }
        header("Location: employees.php");
        exit();
    }
    
    if ($post_action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($id) {
            if (executeQuery($conn, "DELETE FROM employees WHERE Id = ?", [$id])) {
                setFlash('success', 'Employé supprimé avec succès!');
            }
        }
        header("Location: employees.php");
        exit();
    }
}

// Get employee for edit
$employee = null;
if ($action === 'edit' && $id) {
    $employee = getRow($conn, "SELECT * FROM employees WHERE Id = ?", [$id]);
}

// Get all employees
$employees = getData($conn, "SELECT * FROM employees ORDER BY Statut ASC, Nom ASC");

// Get statistics
$total_employees = count($employees);
$actif_count = count(array_filter($employees, fn($e) => $e['Statut'] === 'Actif'));
$inactif_count = count(array_filter($employees, fn($e) => $e['Statut'] === 'Inactif'));
$total_salaires = array_sum(array_map(fn($e) => $e['Salaire'], array_filter($employees, fn($e) => $e['Statut'] === 'Actif')));

require 'includes/header.php';
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid;
}

.stat-card.primary { border-left-color: var(--primary); }
.stat-card.success { border-left-color: #28a745; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.danger { border-left-color: #dc3545; }

.stat-label {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.employee-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.status-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-actif { background: #d4edda; color: #155724; }
.status-inactif { background: #f8d7da; color: #721c24; }
.status-suspendu { background: #fff3cd; color: #856404; }

.contract-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    background: #e3f2fd;
    color: #1565c0;
}
</style>

<?php displayFlash(); ?>

<?php if ($action === 'list'): ?>
<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-label">👥 Total Employés</div>
        <div class="stat-value"><?php echo $total_employees; ?></div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">✅ Actifs</div>
        <div class="stat-value"><?php echo $actif_count; ?></div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">❌ Inactifs</div>
        <div class="stat-value"><?php echo $inactif_count; ?></div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label">💰 Masse Salariale Mensuelle</div>
        <div class="stat-value"><?php echo formatCurrency($total_salaires); ?></div>
    </div>
</div>

<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">👥 Liste des Employés (<?php echo count($employees); ?>)</h3>
        <a href="?action=add" class="btn btn-primary">➕ Nouvel Employé</a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Matricule</th>
                <th>Nom Complet</th>
                <th>Poste</th>
                <th>Téléphone</th>
                <th>Contrat</th>
                <th>Salaire</th>
                <th>Date Embauche</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($employees)): ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 2rem;">
                        Aucun employé trouvé.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td>
                        <?php if ($emp['Photo']): ?>
                            <img src="<?php echo e($emp['Photo']); ?>" alt="Photo" class="employee-photo">
                        <?php else: ?>
                            <div class="employee-photo" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                <?php echo strtoupper(substr($emp['Prenom'], 0, 1) . substr($emp['Nom'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo e($emp['Matricule'] ?? '-'); ?></strong></td>
                    <td>
                        <strong><?php echo e($emp['Prenom'] . ' ' . $emp['Nom']); ?></strong>
                        <?php if ($emp['CIN']): ?>
                            <br><small style="color: #666;">CIN: <?php echo e($emp['CIN']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($emp['Poste'] ?? '-'); ?></td>
                    <td><?php echo e($emp['Telephone'] ?? '-'); ?></td>
                    <td><span class="contract-badge"><?php echo e($emp['TypeContrat']); ?></span></td>
                    <td><strong><?php echo formatCurrency($emp['Salaire']); ?></strong></td>
                    <td><?php echo $emp['DateEmbauche'] ? date('d/m/Y', strtotime($emp['DateEmbauche'])) : '-'; ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($emp['Statut']); ?>">
                            <?php echo $emp['Statut']; ?>
                        </span>
                    </td>
                    <td>
                        <a href="employee_details.php?id=<?php echo $emp['Id']; ?>" class="btn btn-sm btn-info">👁️ Détails</a>
                        <a href="?action=edit&id=<?php echo $emp['Id']; ?>" class="btn btn-sm btn-primary">✏️</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet employé?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $emp['Id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
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
            <?php echo $action === 'add' ? '➕ Nouvel Employé' : '✏️ Modifier Employé'; ?>
        </h3>
        <a href="employees.php" class="btn btn-secondary">← Retour</a>
    </div>
    
    <form method="POST" action="employees.php">
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <!-- Informations Personnelles -->
            <tr>
                <td colspan="2" style="background: #f8f9fa; font-weight: bold; padding: 1rem;">
                    📋 Informations Personnelles
                </td>
            </tr>
            <tr>
                <td class="required">Nom</td>
                <td>
                    <input type="text" name="nom" class="form-control" required
                           value="<?php echo ($employee && isset($employee['Nom'])) ? e($employee['Nom']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td class="required">Prénom</td>
                <td>
                    <input type="text" name="prenom" class="form-control" required
                           value="<?php echo ($employee && isset($employee['Prenom'])) ? e($employee['Prenom']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Matricule</td>
                <td>
                    <input type="text" name="matricule" class="form-control" placeholder="Ex: EMP001"
                           value="<?php echo ($employee && isset($employee['Matricule'])) ? e($employee['Matricule']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>CIN</td>
                <td>
                    <input type="text" name="cin" class="form-control" placeholder="Numéro CIN"
                           value="<?php echo ($employee && isset($employee['CIN'])) ? e($employee['CIN']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Date de Naissance</td>
                <td>
                    <input type="date" name="date_naissance" class="form-control"
                           value="<?php echo ($employee && isset($employee['DateNaissance'])) ? e($employee['DateNaissance']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Téléphone</td>
                <td>
                    <input type="text" name="telephone" class="form-control" placeholder="Ex: 98765432"
                           value="<?php echo ($employee && isset($employee['Telephone'])) ? e($employee['Telephone']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Email</td>
                <td>
                    <input type="email" name="email" class="form-control" placeholder="exemple@email.com"
                           value="<?php echo ($employee && isset($employee['Email'])) ? e($employee['Email']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Adresse</td>
                <td>
                    <textarea name="adresse" class="form-control" rows="2"><?php echo ($employee && isset($employee['Adresse'])) ? e($employee['Adresse']) : ''; ?></textarea>
                </td>
            </tr>
            
            <!-- Informations Professionnelles -->
            <tr>
                <td colspan="2" style="background: #f8f9fa; font-weight: bold; padding: 1rem;">
                    💼 Informations Professionnelles
                </td>
            </tr>
            <tr>
                <td>Poste</td>
                <td>
                    <input type="text" name="poste" class="form-control" placeholder="Ex: Soudeur, Peintre"
                           value="<?php echo ($employee && isset($employee['Poste'])) ? e($employee['Poste']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Date d'Embauche</td>
                <td>
                    <input type="date" name="date_embauche" class="form-control"
                           value="<?php echo ($employee && isset($employee['DateEmbauche'])) ? e($employee['DateEmbauche']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Salaire Mensuel</td>
                <td>
                    <input type="number" name="salaire" class="form-control" min="0" step="0.001" placeholder="0.000"
                           value="<?php echo ($employee && isset($employee['Salaire'])) ? $employee['Salaire'] : ''; ?>">
                    <small style="color: #666;">Salaire de base mensuel en DT</small>
                </td>
            </tr>
            <tr>
                <td>Type de Contrat</td>
                <td>
                    <select name="type_contrat" class="form-select">
                        <option value="CDI" <?php echo ($employee && $employee['TypeContrat'] === 'CDI') ? 'selected' : ''; ?>>CDI</option>
                        <option value="CDD" <?php echo ($employee && $employee['TypeContrat'] === 'CDD') ? 'selected' : ''; ?>>CDD</option>
                        <option value="Temporaire" <?php echo ($employee && $employee['TypeContrat'] === 'Temporaire') ? 'selected' : ''; ?>>Temporaire</option>
                        <option value="Stage" <?php echo ($employee && $employee['TypeContrat'] === 'Stage') ? 'selected' : ''; ?>>Stage</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>
                    <select name="statut" class="form-select">
                        <option value="Actif" <?php echo (!$employee || $employee['Statut'] === 'Actif') ? 'selected' : ''; ?>>✅ Actif</option>
                        <option value="Inactif" <?php echo ($employee && $employee['Statut'] === 'Inactif') ? 'selected' : ''; ?>>❌ Inactif</option>
                        <option value="Suspendu" <?php echo ($employee && $employee['Statut'] === 'Suspendu') ? 'selected' : ''; ?>>⚠️ Suspendu</option>
                    </select>
                </td>
            </tr>
            
            <!-- Informations Administratives -->
            <tr>
                <td colspan="2" style="background: #f8f9fa; font-weight: bold; padding: 1rem;">
                    📄 Informations Administratives
                </td>
            </tr>
            <tr>
                <td>N° CNSS</td>
                <td>
                    <input type="text" name="num_cnss" class="form-control" placeholder="Numéro CNSS"
                           value="<?php echo ($employee && isset($employee['NumCNSS'])) ? e($employee['NumCNSS']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>RIB</td>
                <td>
                    <input type="text" name="rib" class="form-control" placeholder="Relevé d'Identité Bancaire"
                           value="<?php echo ($employee && isset($employee['RIB'])) ? e($employee['RIB']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Notes</td>
                <td>
                    <textarea name="notes" class="form-control" rows="3"><?php echo ($employee && isset($employee['Notes'])) ? e($employee['Notes']) : ''; ?></textarea>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="text-align: right; padding: 1.5rem;">
                    <button type="submit" class="btn btn-lg btn-success">💾 Enregistrer</button>
                    <a href="employees.php" class="btn btn-lg btn-secondary">❌ Annuler</a>
                </td>
            </tr>
        </table>
    </form>
</div>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>
