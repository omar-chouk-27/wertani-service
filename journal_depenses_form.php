<!-- Journal Dépenses Add/Edit Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $action === 'add' ? '➕ Nouvelle Dépense' : '✏️ Modifier Dépense'; ?>
        </h3>
        <a href="journal_depenses.php" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'add' : 'update'; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <td class="required">Date d'Achat</td>
                <td>
                    <input type="date" name="date_achat" class="form-control" 
                           value="<?php echo ($depense && isset($depense['DateAchat'])) ? $depense['DateAchat'] : date('Y-m-d'); ?>" required>
                </td>
            </tr>
            
            <tr>
                <td class="required">N° Document</td>
                <td>
                    <input type="text" name="num_doc" class="form-control" 
                           value="<?php echo ($depense && isset($depense['NumDoc'])) ? e($depense['NumDoc']) : ''; ?>" 
                           placeholder="Ex: BL-2025-001 ou FAC-2025-001" required>
                </td>
            </tr>
            
            <tr>
                <td>Fournisseur</td>
                <td>
                    <select name="fournisseur_id" class="form-select">
                        <option value="">-- Sélectionner un fournisseur --</option>
                        <?php foreach ($suppliers as $sup): ?>
                            <option value="<?php echo $sup['id']; ?>" 
                                    <?php echo ($depense && $depense['Fournisseur_Id'] == $sup['id']) ? 'selected' : ''; ?>>
                                <?php echo e($sup['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td class="required">Type de Document</td>
                <td>
                    <select name="type_doc" class="form-select" required>
                        <option value="Bon de livraison" <?php echo ($depense && $depense['TypeDoc'] === 'Bon de livraison') ? 'selected' : ''; ?>>
                            📋 Bon de livraison
                        </option>
                        <option value="Facture" <?php echo ($depense && $depense['TypeDoc'] === 'Facture') ? 'selected' : ''; ?>>
                            🧾 Facture
                        </option>
                    </select>
                    <small style="color: #666;">Les bons de livraison peuvent être groupés en facture plus tard</small>
                </td>
            </tr>
            
            <tr>
                <td class="required">Cpts Chargé</td>
                <td>
                    <select name="cpts_charger" class="form-select" required>
                        <option value="Wertani Service" <?php echo ($depense && $depense['CptsCharger'] === 'Wertani Service') ? 'selected' : ''; ?>>
                            Wertani Service
                        </option>
                        <option value="Wertani Saber" <?php echo ($depense && $depense['CptsCharger'] === 'Wertani Saber') ? 'selected' : ''; ?>>
                            Wertani Saber
                        </option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td>Taux TVA (%)</td>
                <td>
                    <select name="taux_tva" class="form-select" id="taux_tva" onchange="calculateTVA()">
                        <option value="0" <?php echo ($depense && $depense['TauxTVA'] == 0) ? 'selected' : ''; ?>>0% - Sans TVA</option>
                        <option value="7" <?php echo ($depense && $depense['TauxTVA'] == 7) ? 'selected' : ''; ?>>7%</option>
                        <option value="13" <?php echo ($depense && $depense['TauxTVA'] == 13) ? 'selected' : ''; ?>>13%</option>
                        <option value="19" <?php echo ($depense && $depense['TauxTVA'] == 19) ? 'selected' : ''; ?>>19%</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td class="required">Montant TTC</td>
                <td>
                    <input type="number" name="montant_ttc" id="montant_ttc" class="form-control" 
                           min="0" step="0.001" value="<?php echo ($depense && isset($depense['MontantTTC'])) ? $depense['MontantTTC'] : 0; ?>" 
                           onchange="calculateTVA()" required>
                    <div id="tva_info" style="margin-top: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 0.25rem; font-size: 0.85rem;">
                        <strong>TVA:</strong> <span id="tva_amount">0.000</span> DT | 
                        <strong>HT:</strong> <span id="ht_amount">0.000</span> DT
                    </div>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="background: #f0f8ff; padding: 1rem;">
                    <h4 style="color: var(--primary); margin-bottom: 0.5rem;">💳 État de Paiement</h4>
                </td>
            </tr>
            
            <tr>
                <td>État</td>
                <td>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="etat_payee" id="etat_payee" 
                               <?php echo ($depense && $depense['Etat'] === 'Payee') ? 'checked' : ''; ?>
                               onchange="togglePaymentFields()">
                        <strong>Payée</strong>
                    </label>
                </td>
            </tr>
            
            <tbody id="payment_fields" style="display: <?php echo ($depense && $depense['Etat'] === 'Payee') ? 'table-row-group' : 'none'; ?>;">
                <tr>
                    <td>Payée le</td>
                    <td>
                        <input type="date" name="payee_le" class="form-control" 
                               value="<?php echo $depense ? ($depense['PayeeLe'] ?? '') : ''; ?>">
                    </td>
                </tr>
                
                <tr>
                    <td>Type de Paiement</td>
                    <td>
                        <select name="type_paiement" id="type_paiement" class="form-select" onchange="togglePaymentDetails()">
                            <option value="">-- Sélectionner --</option>
                            <option value="Espèce" <?php echo ($depense && $depense['TypePaiement'] === 'Espèce') ? 'selected' : ''; ?>>💵 Espèce</option>
                            <option value="Virement" <?php echo ($depense && $depense['TypePaiement'] === 'Virement') ? 'selected' : ''; ?>>🏦 Virement</option>
                            <option value="Chèque" <?php echo ($depense && $depense['TypePaiement'] === 'Chèque') ? 'selected' : ''; ?>>📝 Chèque</option>
                            <option value="Payer par Saber" <?php echo ($depense && $depense['TypePaiement'] === 'Payer par Saber') ? 'selected' : ''; ?>>👤 Payer par Saber</option>
                        </select>
                    </td>
                </tr>
                
                <tr id="virement_field" style="display: <?php echo ($depense && $depense['TypePaiement'] === 'Virement') ? 'table-row' : 'none'; ?>;">
                    <td>N° Virement</td>
                    <td>
                        <input type="text" name="num_virement" class="form-control" 
                               value="<?php echo ($depense && isset($depense['NumVirement'])) ? e($depense['NumVirement']) : ''; ?>"
                               placeholder="Ex: VIR-2025-001">
                    </td>
                </tr>
                
                <tr id="cheque_field" style="display: <?php echo ($depense && $depense['TypePaiement'] === 'Chèque') ? 'table-row' : 'none'; ?>;">
                    <td>N° Chèque</td>
                    <td>
                        <input type="text" name="num_cheque" class="form-control" 
                               value="<?php echo ($depense && isset($depense['NumCheque'])) ? e($depense['NumCheque']) : ''; ?>"
                               placeholder="Ex: CHQ-123456">
                    </td>
                </tr>
            </tbody>
            
            <tr>
                <td>Comptabiliser</td>
                <td>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="comptabiliser" 
                               <?php echo ($depense && $depense['Comptabiliser']) ? 'checked' : ''; ?>>
                        <span>Inclure dans le Rapport Financier</span>
                    </label>
                    <small style="color: #666;">Cochez pour que cette dépense apparaisse dans les rapports financiers</small>
                </td>
            </tr>
            
            <tr>
                <td>Entité</td>
                <td>
                    <select name="entite" class="form-select">
                        <option value="Wertani Services" <?php echo ($depense && isset($depense['Entite']) && $depense['Entite'] === 'Wertani Services') ? 'selected' : ''; ?>>
                            🔧 Wertani Services
                        </option>
                        <option value="Wertani Saber" <?php echo ($depense && isset($depense['Entite']) && $depense['Entite'] === 'Wertani Saber') ? 'selected' : ''; ?>>
                            ⚙️ Wertani Saber
                        </option>
                    </select>
                    <small style="color: #666;">Choisissez l'entité à laquelle appartient cette dépense</small>
                </td>
            </tr>
            
            <tr>
                <td>Notes</td>
                <td>
                    <textarea name="notes" class="form-control" rows="3"><?php echo ($depense && isset($depense['Notes'])) ? e($depense['Notes']) : ''; ?></textarea>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="background: white; text-align: right; padding: 1rem;">
                    <button type="submit" class="btn btn-lg btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="journal_depenses.php" class="btn btn-lg btn-secondary">
                        ❌ Annuler
                    </a>
                </td>
            </tr>
        </table>
    </form>
</div>

<script>
function togglePaymentFields() {
    const checked = document.getElementById('etat_payee').checked;
    document.getElementById('payment_fields').style.display = checked ? 'table-row-group' : 'none';
}

function togglePaymentDetails() {
    const type = document.getElementById('type_paiement').value;
    document.getElementById('virement_field').style.display = type === 'Virement' ? 'table-row' : 'none';
    document.getElementById('cheque_field').style.display = type === 'Chèque' ? 'table-row' : 'none';
}

function calculateTVA() {
    const tauxTVA = parseFloat(document.getElementById('taux_tva').value) || 0;
    const montantTTC = parseFloat(document.getElementById('montant_ttc').value) || 0;
    
    if (tauxTVA > 0) {
        const montantHT = montantTTC / (1 + (tauxTVA / 100));
        const tva = montantTTC - montantHT;
        
        document.getElementById('tva_amount').textContent = tva.toFixed(3);
        document.getElementById('ht_amount').textContent = montantHT.toFixed(3);
    } else {
        document.getElementById('tva_amount').textContent = '0.000';
        document.getElementById('ht_amount').textContent = montantTTC.toFixed(3);
    }
}

// Calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTVA();
});
</script>
