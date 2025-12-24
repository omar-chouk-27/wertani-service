<!-- Composant / Matiere Add/Edit Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $action === 'add' ? '➕ Nouveau Composant/Matière' : '✏️ Modifier Composant/Matière'; ?>
        </h3>
        <a href="?type=composant" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
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
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo ($item && isset($item['Name'])) ? e($item['Name']) : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td>Référence</td>
                <td>
                    <input type="text" name="reference" class="form-control" 
                           value="<?php echo ($item && isset($item['Reference'])) ? e($item['Reference']) : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>Quantité</td>
                <td>
                    <input type="number" name="quantity" class="form-control" min="0"
                           value="<?php echo ($item && isset($item['Quantity'])) ? $item['Quantity'] : 0; ?>">
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <textarea name="description" class="form-control" rows="2"><?php echo ($item && isset($item['Description'])) ? e($item['Description']) : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td>Fournisseur</td>
                <td>
                    <select name="fournisseur_id" class="form-select">
                        <option value="">-- Sélectionner un fournisseur --</option>
                        <?php foreach ($suppliers as $sup): ?>
                            <option value="<?php echo $sup['id']; ?>" 
                                    <?php echo ($item && $item['Fournisseur_Id'] == $sup['id']) ? 'selected' : ''; ?>>
                                <?php echo e($sup['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Montant TTC</td>
                <td>
                    <input type="number" name="montant_ttc" class="form-control" min="0" step="0.001"
                           value="<?php echo ($item && isset($item['MontantTTC'])) ? $item['MontantTTC'] : 0; ?>">
                </td>
            </tr>
            <tr>
                <td>Catégorie</td>
                <td>
                    <select name="categorie" class="form-select">
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php 
                        $categories = ['électrique', 'métallique', 'bois', 'tissu', 'Accessoire'];
                        foreach ($categories as $cat): 
                        ?>
                            <option value="<?php echo $cat; ?>" 
                                    <?php echo ($item && $item['Categorie'] == $cat) ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="background: white; text-align: right; padding: 1rem;">
                    <button type="submit" class="btn btn-lg btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="?type=composant" class="btn btn-lg btn-secondary">
                        ❌ Annuler
                    </a>
                </td>
            </tr>
        </table>
    </form>
</div>
