<?php
require_once 'includes/config.php';
requireAuth();

$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: employees.php");
    exit();
}

// Get employee
$employee = getRow($conn, "SELECT * FROM employees WHERE Id = ?", [$id]);
if (!$employee) {
    setFlash('danger', 'Employé non trouvé.');
    header("Location: employees.php");
    exit();
}

$page_title = $employee['Prenom'] . ' ' . $employee['Nom'] . ' - Wertani Service';

// Get tab
$tab = $_GET['tab'] ?? 'info';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Add absence
    if ($action === 'add_absence') {
        $date_debut = $_POST['date_debut'] ?? '';
        $date_fin = $_POST['date_fin'] ?? '';
        $type = $_POST['type_absence'] ?? 'Congé Payé';
        $motif = $_POST['motif'] ?? '';
        
        if (!empty($date_debut) && !empty($date_fin)) {
            $date1 = new DateTime($date_debut);
            $date2 = new DateTime($date_fin);
            $jours = $date2->diff($date1)->days + 1;
            
            $sql = "INSERT INTO employee_absences (Employee_Id, DateDebut, DateFin, NombreJours, TypeAbsence, Motif) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            if (executeQuery($conn, $sql, [$id, $date_debut, $date_fin, $jours, $type, $motif])) {
                setFlash('success', 'Absence ajoutée!');
            }
        }
        header("Location: employee_details.php?id=$id&tab=absences");
        exit();
    }
    
    // Add attendance
    if ($action === 'add_attendance') {
        $date_pointage = $_POST['date_pointage'] ?? date('Y-m-d');
        $heure_arrivee = $_POST['heure_arrivee'] ?? null;
        $heure_depart = $_POST['heure_depart'] ?? null;
        $statut = $_POST['statut'] ?? 'Présent';
        
        $heures = 0;
        if ($heure_arrivee && $heure_depart) {
            $t1 = strtotime($heure_arrivee);
            $t2 = strtotime($heure_depart);
            $heures = ($t2 - $t1) / 3600;
        }
        
        $sql = "INSERT INTO employee_attendance (Employee_Id, DatePointage, HeureArrivee, HeureDepart, HeuresTravaillees, Statut) 
                VALUES (?, ?, ?, ?, ?, ?)";
        if (executeQuery($conn, $sql, [$id, $date_pointage, $heure_arrivee, $heure_depart, $heures, $statut])) {
            setFlash('success', 'Pointage ajouté!');
        }
        header("Location: employee_details.php?id=$id&tab=pointage");
        exit();
    }
    
    // Add salary
    if ($action === 'add_salaire') {
        $mois = $_POST['mois'] ?? '';
        $salaire_base = $_POST['salaire_base'] ?? 0;
        $primes = $_POST['primes'] ?? 0;
        $deductions = $_POST['deductions'] ?? 0;
        $avance = $_POST['avance'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        
        if (!empty($mois)) {
            // Split YYYY-MM format
            $date_parts = explode('-', $mois);
            $annee = $date_parts[0] ?? date('Y');
            $mois_num = $date_parts[1] ?? date('m');
            
            // Calculate net salary
            $salaire_net = $salaire_base + $primes - $deductions - $avance;
            
            $sql = "INSERT INTO employee_salaires (Employee_Id, Mois, Annee, SalaireBase, Primes, Deductions, Avance, SalaireNet, Notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if (executeQuery($conn, $sql, [$id, $mois_num, $annee, $salaire_base, $primes, $deductions, $avance, $salaire_net, $notes])) {
                setFlash('success', 'Salaire enregistré avec succès!');
            } else {
                setFlash('danger', 'Erreur lors de l\'enregistrement du salaire.');
            }
        } else {
            setFlash('danger', 'Le mois est requis.');
        }
        header("Location: employee_details.php?id=$id&tab=salaires");
        exit();
    }
}

// Get related data
$absences = getData($conn, "SELECT * FROM employee_absences WHERE Employee_Id = ? ORDER BY DateDebut DESC", [$id]);
$attendance = getData($conn, "SELECT * FROM employee_attendance WHERE Employee_Id = ? ORDER BY DatePointage DESC LIMIT 30", [$id]);
$salaires = getData($conn, "SELECT * FROM employee_salaires WHERE Employee_Id = ? ORDER BY Annee DESC, Mois DESC", [$id]);

require 'includes/header.php';
?>

<style>
.employee-header {
    background: linear-gradient(135deg, var(--primary) 0%, #c89f2c 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 2rem;
}

.employee-photo-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
}

.employee-initials-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    background: rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: bold;
}

.employee-info h2 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
}

.employee-info p {
    margin: 0.3rem 0;
    opacity: 0.9;
}

.detail-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 3px solid var(--primary);
    padding-bottom: 0.5rem;
}

.detail-tab {
    padding: 0.75rem 1.5rem;
    background: white;
    border: 2px solid #ddd;
    border-radius: 8px 8px 0 0;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    color: #333;
}

.detail-tab:hover {
    background: #f5f5f5;
}

.detail-tab.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.info-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.info-section h4 {
    color: var(--primary);
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary);
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.info-label {
    color: #666;
    font-weight: 600;
}

.info-value {
    color: #333;
}
</style>

<?php displayFlash(); ?>

<!-- Employee Header -->
<div class="employee-header">
    <?php if ($employee['Photo']): ?>
        <img src="<?php echo e($employee['Photo']); ?>" alt="Photo" class="employee-photo-large">
    <?php else: ?>
        <div class="employee-initials-large">
            <?php echo strtoupper(substr($employee['Prenom'], 0, 1) . substr($employee['Nom'], 0, 1)); ?>
        </div>
    <?php endif; ?>
    
    <div class="employee-info">
        <h2><?php echo e($employee['Prenom'] . ' ' . $employee['Nom']); ?></h2>
        <p><strong>📋 <?php echo e($employee['Poste'] ?? 'Non spécifié'); ?></strong></p>
        <p>📞 <?php echo e($employee['Telephone'] ?? '-'); ?> | 📧 <?php echo e($employee['Email'] ?? '-'); ?></p>
        <p>💰 Salaire: <strong><?php echo formatCurrency($employee['Salaire']); ?></strong> | 
           📄 <?php echo e($employee['TypeContrat']); ?> | 
           <span style="background: rgba(255,255,255,0.3); padding: 0.2rem 0.8rem; border-radius: 12px;">
               <?php echo $employee['Statut']; ?>
           </span>
        </p>
    </div>
    
    <div style="margin-left: auto;">
        <a href="employees.php" class="btn btn-secondary">← Retour</a>
        <a href="employees.php?action=edit&id=<?php echo $id; ?>" class="btn btn-primary">✏️ Modifier</a>
    </div>
</div>

<!-- Tabs -->
<div class="detail-tabs">
    <a href="?id=<?php echo $id; ?>&tab=info" class="detail-tab <?php echo $tab === 'info' ? 'active' : ''; ?>">
        📋 Informations
    </a>
    <a href="?id=<?php echo $id; ?>&tab=absences" class="detail-tab <?php echo $tab === 'absences' ? 'active' : ''; ?>">
        🏖️ Absences (<?php echo count($absences); ?>)
    </a>
    <a href="?id=<?php echo $id; ?>&tab=pointage" class="detail-tab <?php echo $tab === 'pointage' ? 'active' : ''; ?>">
        ⏰ Pointage (<?php echo count($attendance); ?>)
    </a>
    <a href="?id=<?php echo $id; ?>&tab=salaires" class="detail-tab <?php echo $tab === 'salaires' ? 'active' : ''; ?>">
        💰 Salaires (<?php echo count($salaires); ?>)
    </a>
</div>

<?php if ($tab === 'info'): ?>
<!-- Information Tab -->
<div class="info-grid">
    <div class="info-section">
        <h4>📋 Informations Personnelles</h4>
        <div class="info-row">
            <span class="info-label">Matricule:</span>
            <span class="info-value"><?php echo e($employee['Matricule'] ?? '-'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">CIN:</span>
            <span class="info-value"><?php echo e($employee['CIN'] ?? '-'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de Naissance:</span>
            <span class="info-value">
                <?php 
                if ($employee['DateNaissance']) {
                    echo date('d/m/Y', strtotime($employee['DateNaissance']));
                    $age = date_diff(date_create($employee['DateNaissance']), date_create('now'))->y;
                    echo " ($age ans)";
                } else {
                    echo '-';
                }
                ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Adresse:</span>
            <span class="info-value"><?php echo e($employee['Adresse'] ?? '-'); ?></span>
        </div>
    </div>
    
    <div class="info-section">
        <h4>💼 Informations Professionnelles</h4>
        <div class="info-row">
            <span class="info-label">Date d'Embauche:</span>
            <span class="info-value">
                <?php 
                if ($employee['DateEmbauche']) {
                    echo date('d/m/Y', strtotime($employee['DateEmbauche']));
                    $anciennete = date_diff(date_create($employee['DateEmbauche']), date_create('now'));
                    echo "<br><small>Ancienneté: {$anciennete->y} ans, {$anciennete->m} mois</small>";
                } else {
                    echo '-';
                }
                ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Salaire Mensuel:</span>
            <span class="info-value"><strong><?php echo formatCurrency($employee['Salaire']); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Type de Contrat:</span>
            <span class="info-value"><?php echo e($employee['TypeContrat']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut:</span>
            <span class="info-value"><strong><?php echo e($employee['Statut']); ?></strong></span>
        </div>
    </div>
    
    <div class="info-section">
        <h4>📄 Informations Administratives</h4>
        <div class="info-row">
            <span class="info-label">N° CNSS:</span>
            <span class="info-value"><?php echo e($employee['NumCNSS'] ?? '-'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">RIB:</span>
            <span class="info-value"><?php echo e($employee['RIB'] ?? '-'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span class="info-value"><?php echo nl2br(e($employee['Notes'] ?? '-')); ?></span>
        </div>
    </div>
</div>

<?php elseif ($tab === 'absences'): ?>
<!-- Absences Tab -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🏖️ Absences et Congés</h3>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('add-absence-form').style.display='block'">
            ➕ Nouvelle Absence
        </button>
    </div>
</div>

<!-- Add Absence Form -->
<div id="add-absence-form" style="display: none; background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h4 style="margin-bottom: 1.5rem;">➕ Ajouter une Absence</h4>
    <form method="POST">
        <input type="hidden" name="action" value="add_absence">
        <table class="form-table">
            <tr>
                <td class="required">Date Début</td>
                <td><input type="date" name="date_debut" class="form-control" required></td>
            </tr>
            <tr>
                <td class="required">Date Fin</td>
                <td><input type="date" name="date_fin" class="form-control" required></td>
            </tr>
            <tr>
                <td>Type</td>
                <td>
                    <select name="type_absence" class="form-select">
                        <option value="Congé Payé">Congé Payé</option>
                        <option value="Congé Maladie">Congé Maladie</option>
                        <option value="Absence Autorisée">Absence Autorisée</option>
                        <option value="Absence Non Autorisée">Absence Non Autorisée</option>
                        <option value="Autre">Autre</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Motif</td>
                <td><textarea name="motif" class="form-control" rows="2"></textarea></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;">
                    <button type="submit" class="btn btn-success">✅ Ajouter</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-absence-form').style.display='none'">❌ Annuler</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Jours</th>
                <th>Type</th>
                <th>Motif</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($absences)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 2rem;">Aucune absence enregistrée.</td></tr>
            <?php else: ?>
                <?php foreach ($absences as $abs): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($abs['DateDebut'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($abs['DateFin'])); ?></td>
                    <td><strong><?php echo $abs['NombreJours']; ?></strong> jour(s)</td>
                    <td><?php echo e($abs['TypeAbsence']); ?></td>
                    <td><?php echo nl2br(e($abs['Motif'] ?? '-')); ?></td>
                    <td><?php echo e($abs['Statut']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php elseif ($tab === 'pointage'): ?>
<!-- Attendance Tab -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">⏰ Pointage et Présence</h3>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('add-attendance-form').style.display='block'">
            ➕ Ajouter Pointage
        </button>
    </div>
</div>

<!-- Add Attendance Form -->
<div id="add-attendance-form" style="display: none; background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h4 style="margin-bottom: 1.5rem;">➕ Ajouter un Pointage</h4>
    <form method="POST">
        <input type="hidden" name="action" value="add_attendance">
        <table class="form-table">
            <tr>
                <td class="required">Date</td>
                <td><input type="date" name="date_pointage" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></td>
            </tr>
            <tr>
                <td>Heure Arrivée</td>
                <td><input type="time" name="heure_arrivee" class="form-control"></td>
            </tr>
            <tr>
                <td>Heure Départ</td>
                <td><input type="time" name="heure_depart" class="form-control"></td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>
                    <select name="statut" class="form-select">
                        <option value="Présent">Présent</option>
                        <option value="Absent">Absent</option>
                        <option value="Retard">Retard</option>
                        <option value="Congé">Congé</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;">
                    <button type="submit" class="btn btn-success">✅ Ajouter</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-attendance-form').style.display='none'">❌ Annuler</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Arrivée</th>
                <th>Départ</th>
                <th>Heures Travaillées</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($attendance)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 2rem;">Aucun pointage enregistré.</td></tr>
            <?php else: ?>
                <?php foreach ($attendance as $att): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($att['DatePointage'])); ?></td>
                    <td><?php echo $att['HeureArrivee'] ? date('H:i', strtotime($att['HeureArrivee'])) : '-'; ?></td>
                    <td><?php echo $att['HeureDepart'] ? date('H:i', strtotime($att['HeureDepart'])) : '-'; ?></td>
                    <td><strong><?php echo number_format($att['HeuresTravaillees'], 2); ?>h</strong></td>
                    <td><?php echo e($att['Statut']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php elseif ($tab === 'salaires'): ?>
<!-- Salaries Tab -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">💰 Salaires</h3>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('add-salaire-form').style.display='block'">
            ➕ Enregistrer Salaire
        </button>
    </div>
</div>

<!-- Add Salary Form -->
<div id="add-salaire-form" style="display: none; background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h4 style="margin-bottom: 1.5rem;">➕ Enregistrer un Salaire</h4>
    <form method="POST">
        <input type="hidden" name="action" value="add_salaire">
        <table class="form-table">
            <tr>
                <td class="required">Mois</td>
                <td>
                    <select name="mois" class="form-select" required>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo $m == date('m') ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="required">Année</td>
                <td><input type="number" name="annee" class="form-control" value="<?php echo date('Y'); ?>" required></td>
            </tr>
            <tr>
                <td>Salaire Base</td>
                <td><input type="number" name="salaire_base" class="form-control" step="0.001" value="<?php echo $employee['Salaire']; ?>"></td>
            </tr>
            <tr>
                <td>Primes</td>
                <td><input type="number" name="primes" class="form-control" step="0.001" value="0"></td>
            </tr>
            <tr>
                <td>Déductions</td>
                <td><input type="number" name="deductions" class="form-control" step="0.001" value="0"></td>
            </tr>
            <tr>
                <td>Avance</td>
                <td>
                    <input type="number" name="avance" class="form-control" step="0.001" value="0" 
                           placeholder="Avance sur salaire">
                    <small style="color: #666;">Argent déjà donné à l'employé</small>
                </td>
            </tr>
            <tr>
                <td>Date Paiement</td>
                <td><input type="date" name="date_paiement" class="form-control"></td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>
                    <select name="statut_paiement" class="form-select">
                        <option value="En Attente">En Attente</option>
                        <option value="Payé">Payé</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Mode</td>
                <td>
                    <select name="mode_paiement" class="form-select">
                        <option value="Virement">Virement</option>
                        <option value="Espèces">Espèces</option>
                        <option value="Chèque">Chèque</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;">
                    <button type="submit" class="btn btn-success">✅ Enregistrer</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-salaire-form').style.display='none'">❌ Annuler</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Période</th>
                <th>Salaire Base</th>
                <th>Primes</th>
                <th>Déductions</th>
                <th>Avance</th>
                <th>Net à Payer</th>
                <th>Date Paiement</th>
                <th>Statut</th>
                <th>Mode</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($salaires)): ?>
                <tr><td colspan="9" style="text-align: center; padding: 2rem;">Aucun salaire enregistré.</td></tr>
            <?php else: ?>
                <?php foreach ($salaires as $sal): ?>
                <tr>
                    <td><strong><?php echo date('F Y', mktime(0, 0, 0, $sal['Mois'], 1, $sal['Annee'])); ?></strong></td>
                    <td><?php echo formatCurrency($sal['SalaireBase']); ?></td>
                    <td style="color: green;">+<?php echo formatCurrency($sal['Primes']); ?></td>
                    <td style="color: red;">-<?php echo formatCurrency($sal['Deductions']); ?></td>
                    <td style="color: orange;">-<?php echo formatCurrency($sal['Avance'] ?? 0); ?></td>
                    <td><strong><?php echo formatCurrency($sal['SalaireNet']); ?></strong></td>
                    <td><?php echo $sal['DatePaiement'] ? date('d/m/Y', strtotime($sal['DatePaiement'])) : '-'; ?></td>
                    <td><?php echo e($sal['StatutPaiement']); ?></td>
                    <td><?php echo e($sal['ModePaiement']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>