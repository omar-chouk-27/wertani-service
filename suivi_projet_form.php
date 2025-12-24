<!-- Suivi Projet Edit Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🔄 Gestion du Suivi: <?php echo e($suivi['NumSuivi'] ?? ''); ?></h3>
        <a href="suivi_projet.php" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
    <!-- Project Info Display -->
    <div class="coordinates-table" style="padding: 1rem; background: #f8f9fa; border-bottom: 2px solid #ddd;">
        <div class="coordinate-item">
            <label>N° Suivi</label>
            <div class="value"><?php echo e($suivi['NumSuivi']); ?></div>
        </div>
        <div class="coordinate-item">
            <label>Titre</label>
            <div class="value"><?php echo e($suivi['Title']); ?></div>
        </div>
        <div class="coordinate-item">
            <label>Client</label>
            <div class="value">
                <?php 
                $client = getRow($conn, "SELECT Name FROM Client WHERE Id = ?", [$suivi['Client_Id']]);
                echo e($client['Name'] ?? '-');
                ?>
            </div>
        </div>
        <div class="coordinate-item">
            <label>Voiture</label>
            <div class="value"><?php echo e($suivi['Voiture'] ?? '-'); ?></div>
        </div>
        <?php if ($suivi['Matricule']): ?>
        <div class="coordinate-item">
            <label>Matricule</label>
            <div class="value"><?php echo e($suivi['Matricule']); ?></div>
        </div>
        <?php endif; ?>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <table class="form-table">
            <tr>
                <td colspan="2" style="background: #e3f2fd; padding: 1rem;">
                    <h4 style="color: #1976d2; margin: 0;">💰 Informations Financières</h4>
                </td>
            </tr>
            
            <tr>
                <td class="required">Montant du Projet</td>
                <td>
                    <input type="number" name="montant_projet" id="montant_projet" class="form-control" 
                           min="0" step="0.001" value="<?php echo $suivi['MontantProjet']; ?>" 
                           onchange="calculateFinancials()" required>
                </td>
            </tr>
            
            <tr>
                <td>Avance (optionnel)</td>
                <td>
                    <input type="number" name="avance" id="avance" class="form-control" 
                           min="0" step="0.001" value="<?php echo $suivi['Avance']; ?>" 
                           onchange="calculateFinancials()">
                    <small style="color: #666;">Montant payé d'avance par le client</small>
                </td>
            </tr>
            
            <tr>
                <td>Cpts Chargé</td>
                <td>
                    <select name="cpts_charger" class="form-select">
                        <option value="Wertani Service" <?php echo $suivi['CptsCharger'] === 'Wertani Service' ? 'selected' : ''; ?>>
                            Wertani Service
                        </option>
                        <option value="Wertani Saber" <?php echo $suivi['CptsCharger'] === 'Wertani Saber' ? 'selected' : ''; ?>>
                            Wertani Saber
                        </option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td>TVA</td>
                <td>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="applique_tva" id="applique_tva" 
                               <?php echo $suivi['AppliqueTVA'] ? 'checked' : ''; ?>
                               onchange="toggleTVA()">
                        <strong>Appliquer la TVA</strong>
                    </label>
                </td>
            </tr>
            
            <tr id="taux_tva_row" style="display: <?php echo $suivi['AppliqueTVA'] ? 'table-row' : 'none'; ?>;">
                <td>Taux TVA (%)</td>
                <td>
                    <select name="taux_tva" id="taux_tva" class="form-select" onchange="calculateFinancials()">
                        <option value="7" <?php echo $suivi['TauxTVA'] == 7 ? 'selected' : ''; ?>>7%</option>
                        <option value="13" <?php echo $suivi['TauxTVA'] == 13 ? 'selected' : ''; ?>>13%</option>
                        <option value="19" <?php echo $suivi['TauxTVA'] == 19 ? 'selected' : ''; ?>>19%</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td colspan="2">
                    <div style="padding: 1rem; background: #e8f5e9; border-radius: 0.5rem;">
                        <h4 style="color: #2e7d32; margin-bottom: 0.5rem;">📊 Résumé Financier</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div>
                                <strong>Montant HT:</strong><br>
                                <span id="display_montant" style="font-size: 1.2rem; color: #1976d2;">
                                    <?php echo formatCurrency($suivi['MontantProjet']); ?>
                                </span>
                            </div>
                            <div id="tva_display" style="display: <?php echo $suivi['AppliqueTVA'] ? 'block' : 'none'; ?>;">
                                <strong>TVA:</strong><br>
                                <span id="display_tva" style="font-size: 1.2rem; color: #f57c00;">
                                    <?php echo formatCurrency($suivi['TVA']); ?>
                                </span>
                            </div>
                            <div>
                                <strong>Montant TTC:</strong><br>
                                <span id="display_ttc" style="font-size: 1.2rem; font-weight: bold; color: #2e7d32;">
                                    <?php echo formatCurrency($suivi['MontantTTC']); ?>
                                </span>
                            </div>
                            <div>
                                <strong>Avance:</strong><br>
                                <span id="display_avance" style="font-size: 1.2rem; color: #7b1fa2;">
                                    <?php echo formatCurrency($suivi['Avance']); ?>
                                </span>
                            </div>
                            <div>
                                <strong>Reste à Payer:</strong><br>
                                <span id="display_reste" style="font-size: 1.2rem; font-weight: bold; color: #d32f2f;">
                                    <?php echo formatCurrency($suivi['ResteAPayer']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="background: #fff3e0; padding: 1rem;">
                    <h4 style="color: #e65100; margin: 0;">📅 Suivi du Projet</h4>
                </td>
            </tr>
            
            <tr>
                <td>Statut du Projet</td>
                <td>
                    <select name="statut_projet" class="form-select">
                        <option value="En cours" <?php echo $suivi['StatutProjet'] === 'En cours' ? 'selected' : ''; ?>>
                            🔄 En cours
                        </option>
                        <option value="Terminé" <?php echo $suivi['StatutProjet'] === 'Terminé' ? 'selected' : ''; ?>>
                            ✅ Terminé
                        </option>
                        <option value="Annulé" <?php echo $suivi['StatutProjet'] === 'Annulé' ? 'selected' : ''; ?>>
                            ❌ Annulé
                        </option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td>Date Début</td>
                <td>
                    <input type="date" name="date_debut" class="form-control" 
                           value="<?php echo $suivi['DateDebut'] ?? ''; ?>">
                </td>
            </tr>
            
            <tr>
                <td>Date Fin</td>
                <td>
                    <input type="date" name="date_fin" class="form-control" 
                           value="<?php echo $suivi['DateFin'] ?? ''; ?>">
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="background: #f3e5f5; padding: 1rem;">
                    <h4 style="color: #6a1b9a; margin: 0;">💳 Paiement</h4>
                </td>
            </tr>
            
            <tr>
                <td>État</td>
                <td>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="etat_payee" id="etat_payee" 
                               <?php echo $suivi['Etat'] === 'Payee' ? 'checked' : ''; ?>
                               onchange="togglePaymentFields()">
                        <strong>Payée</strong>
                    </label>
                </td>
            </tr>
            
            <tbody id="payment_fields" style="display: <?php echo $suivi['Etat'] === 'Payee' ? 'table-row-group' : 'none'; ?>;">
                <tr>
                    <td>Payée le</td>
                    <td>
                        <input type="date" name="payee_le" class="form-control" 
                               value="<?php echo $suivi['PayeeLe'] ?? ''; ?>">
                    </td>
                </tr>
                
                <tr>
                    <td>Type de Paiement</td>
                    <td>
                        <select name="type_paiement" id="type_paiement" class="form-select" onchange="togglePaymentDetails()">
                            <option value="">-- Sélectionner --</option>
                            <option value="Espèce" <?php echo $suivi['TypePaiement'] === 'Espèce' ? 'selected' : ''; ?>>💵 Espèce</option>
                            <option value="Virement" <?php echo $suivi['TypePaiement'] === 'Virement' ? 'selected' : ''; ?>>🏦 Virement</option>
                            <option value="Chèque" <?php echo $suivi['TypePaiement'] === 'Chèque' ? 'selected' : ''; ?>>📝 Chèque</option>
                        </select>
                    </td>
                </tr>
                
                <tr id="virement_field" style="display: <?php echo $suivi['TypePaiement'] === 'Virement' ? 'table-row' : 'none'; ?>;">
                    <td>N° Virement</td>
                    <td>
                        <input type="text" name="num_virement" class="form-control" 
                               value="<?php echo e($suivi['NumVirement'] ?? ''); ?>">
                    </td>
                </tr>
                
                <tr id="cheque_field" style="display: <?php echo $suivi['TypePaiement'] === 'Chèque' ? 'table-row' : 'none'; ?>;">
                    <td>N° Chèque</td>
                    <td>
                        <input type="text" name="num_cheque" class="form-control" 
                               value="<?php echo e($suivi['NumCheque'] ?? ''); ?>">
                    </td>
                </tr>
            </tbody>
            
            <tr>
                <td>Comptabiliser</td>
                <td>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="comptabiliser" 
                               <?php echo $suivi['Comptabiliser'] ? 'checked' : ''; ?>>
                        <span>Inclure dans le Rapport Financier</span>
                    </label>
                </td>
            </tr>
            
            <tr>
                <td>Entité</td>
                <td>
                    <select name="entite" class="form-select">
                        <option value="Wertani Services" <?php echo (isset($suivi['Entite']) && $suivi['Entite'] === 'Wertani Services') ? 'selected' : ''; ?>>
                            🔧 Wertani Services
                        </option>
                        <option value="Wertani Saber" <?php echo (isset($suivi['Entite']) && $suivi['Entite'] === 'Wertani Saber') ? 'selected' : ''; ?>>
                            ⚙️ Wertani Saber
                        </option>
                    </select>
                    <small style="color: #666;">Choisissez l'entité à laquelle appartient ce projet</small>
                </td>
            </tr>
            
            <tr>
                <td>Notes</td>
                <td>
                    <textarea name="notes" class="form-control" rows="3"><?php echo e($suivi['Notes'] ?? ''); ?></textarea>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="background: white; text-align: right; padding: 1rem;">
                    <button type="submit" class="btn btn-lg btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="suivi_projet.php" class="btn btn-lg btn-secondary">
                        ❌ Annuler
                    </a>
                </td>
            </tr>
        </table>
    </form>
</div>

<!-- Articles Used -->
<?php if (!empty($suivi_articles)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📦 Articles/Services Utilisés</h3>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Article</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($suivi_articles as $art): ?>
            <tr>
                <td>
                    <?php
                    // Try to get article name from different tables
                    $article_name = 'Article ID: ' . $art['Article_Id'];
                    $article_check = getRow($conn, "SELECT Name FROM article WHERE Id = ?", [$art['Article_Id']]);
                    if ($article_check) $article_name = $article_check['Name'];
                    echo e($article_name);
                    ?>
                </td>
                <td><?php echo $art['Quantity']; ?></td>
                <td><?php echo formatCurrency($art['UnitPrice']); ?></td>
                <td><strong><?php echo formatCurrency($art['TotalPrice']); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function toggleTVA() {
    const checked = document.getElementById('applique_tva').checked;
    document.getElementById('taux_tva_row').style.display = checked ? 'table-row' : 'none';
    document.getElementById('tva_display').style.display = checked ? 'block' : 'none';
    calculateFinancials();
}

function togglePaymentFields() {
    const checked = document.getElementById('etat_payee').checked;
    document.getElementById('payment_fields').style.display = checked ? 'table-row-group' : 'none';
}

function togglePaymentDetails() {
    const type = document.getElementById('type_paiement').value;
    document.getElementById('virement_field').style.display = type === 'Virement' ? 'table-row' : 'none';
    document.getElementById('cheque_field').style.display = type === 'Chèque' ? 'table-row' : 'none';
}

function calculateFinancials() {
    const montant = parseFloat(document.getElementById('montant_projet').value) || 0;
    const avance = parseFloat(document.getElementById('avance').value) || 0;
    const appliqueTVA = document.getElementById('applique_tva').checked;
    const tauxTVA = parseFloat(document.getElementById('taux_tva').value) || 19;
    
    let tva = 0;
    let ttc = montant;
    
    if (appliqueTVA) {
        tva = montant * (tauxTVA / 100);
        ttc = montant + tva;
    }
    
    const reste = ttc - avance;
    
    document.getElementById('display_montant').textContent = montant.toFixed(3) + ' DT';
    document.getElementById('display_tva').textContent = tva.toFixed(3) + ' DT';
    document.getElementById('display_ttc').textContent = ttc.toFixed(3) + ' DT';
    document.getElementById('display_avance').textContent = avance.toFixed(3) + ' DT';
    document.getElementById('display_reste').textContent = reste.toFixed(3) + ' DT';
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    calculateFinancials();
});
</script>
