<!-- Composant / Matiere Add/Edit Form -->
<?php
// Initialize all variables with defaults
$nom_value = '';
$ref_value = '';
$qty_value = 0;
$desc_value = '';
$fournisseur_value = '';
$montant_value = 0;
$categorie_value = '';

// Suppliers list is already loaded in articles.php as $suppliers

// Fill values if editing and item exists
if ($action === 'edit' && !empty($item) && is_array($item)) {
    $nom_value = $item['Name'] ?? '';
    $ref_value = $item['Reference'] ?? '';
    $qty_value = $item['Quantity'] ?? 0;
    $desc_value = $item['Description'] ?? '';
    $fournisseur_value = $item['Fournisseur_Id'] ?? '';
    $montant_value = $item['MontantTTC'] ?? 0;
    $categorie_value = $item['Categorie'] ?? '';
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $action === 'add' ? '➕ Nouveau Composant/Matière' : '✏️ Modifier Composant/Matière'; ?>
        </h3>
        <a href="?type=composant" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
    <?php if ($action === 'edit' && !empty($item)): ?>
        <div style="background: #d4edda; padding: 1rem; margin: 1rem; border-radius: 8px; border: 2px solid #28a745;">
            <strong style="color: #155724;">✅ Mode Édition - ID: <?php echo $id; ?></strong><br>
            <span style="color: #155724;">
                Nom: <strong><?php echo e($item['Name'] ?? 'N/A'); ?></strong> | 
                Ref: <strong><?php echo e($item['Reference'] ?? 'N/A'); ?></strong> |
                Catégorie: <strong><?php echo e($categorie_value ?: 'N/A'); ?></strong>
            </span>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="articles.php">
        <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'add' : 'update'; ?>">
        <input type="hidden" name="type" value="composant">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <td class="required">Nom</td>
                <td>
                    <input type="text" 
                           name="name" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($nom_value); ?>" 
                           required>
                </td>
            </tr>
            <tr>
                <td>Référence</td>
                <td>
                    <input type="text" 
                           name="reference" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($ref_value); ?>">
                </td>
            </tr>
            <tr>
                <td>Quantité</td>
                <td>
                    <input type="number" 
                           name="quantity" 
                           class="form-control" 
                           min="0"
                           value="<?php echo $qty_value; ?>">
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <textarea name="description" 
                              class="form-control" 
                              rows="2"><?php echo htmlspecialchars($desc_value); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>Fournisseur</td>
                <td>
                    <select name="fournisseur_id" class="form-select">
                        <option value="">-- Sélectionner un fournisseur --</option>
                        <?php foreach ($suppliers as $sup): ?>
                            <option value="<?php echo $sup['id']; ?>" 
                                    <?php echo ($fournisseur_value == $sup['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sup['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Montant TTC</td>
                <td>
                    <input type="number" 
                           name="montant_ttc" 
                           class="form-control" 
                           min="0" 
                           step="0.001"
                           value="<?php echo $montant_value; ?>">
                </td>
            </tr>
            <tr>
                <td>Catégorie</td>
                <td>
                    <select name="categorie" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        <option value="électrique" <?php echo ($categorie_value === 'électrique') ? 'selected' : ''; ?>>⚡ Électrique</option>
                        <option value="métallique" <?php echo ($categorie_value === 'métallique') ? 'selected' : ''; ?>>🔩 Métallique</option>
                        <option value="bois" <?php echo ($categorie_value === 'bois') ? 'selected' : ''; ?>>🌳 Bois</option>
                        <option value="tissu" <?php echo ($categorie_value === 'tissu') ? 'selected' : ''; ?>>🧵 Tissu</option>
                        <option value="Accessoire" <?php echo ($categorie_value === 'Accessoire') ? 'selected' : ''; ?>>🔧 Accessoire</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="text-align: right; padding: 1.5rem;">
                    <button type="submit" class="btn btn-lg btn-success">💾 Enregistrer</button>
                    <a href="?type=composant" class="btn btn-lg btn-secondary">❌ Annuler</a>
                </td>
            </tr>
        </table>
    </form>
</div>
