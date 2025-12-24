<!-- Composant / Matiere List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📦 Composant / Matière - Purchased Items (<?php echo count($composants); ?>)</h3>
        <a href="?type=composant&action=add" class="btn btn-primary">
            ➕ Nouveau Composant
        </a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Référence</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Quantité</th>
                <th>Fournisseur</th>
                <th>Montant TTC</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($composants)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: #999;">
                        Aucun composant trouvé. Cliquez sur "Nouveau Composant" pour ajouter.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($composants as $comp): ?>
                <tr>
                    <td><strong>#<?php echo $comp['Id']; ?></strong></td>
                    <td><?php echo e($comp['Reference'] ?? '-'); ?></td>
                    <td><strong><?php echo e($comp['Name']); ?></strong></td>
                    <td>
                        <?php if ($comp['Categorie']): ?>
                            <span class="badge badge-<?php 
                                $cat_colors = [
                                    'électrique' => 'warning',
                                    'métallique' => 'secondary',
                                    'bois' => 'info',
                                    'tissu' => 'primary',
                                    'Accessoire' => 'success'
                                ];
                                echo $cat_colors[$comp['Categorie']] ?? 'secondary';
                            ?>">
                                <?php echo e($comp['Categorie']); ?>
                            </span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo $comp['Quantity']; ?></td>
                    <td><?php echo e($comp['fournisseur_name'] ?? '-'); ?></td>
                    <td><strong><?php echo formatCurrency($comp['MontantTTC']); ?></strong></td>
                    <td>
                        <a href="?type=composant&action=edit&id=<?php echo $comp['Id']; ?>" class="btn btn-sm btn-primary">
                            ✏️ Modifier
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce composant?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="type" value="composant">
                            <input type="hidden" name="id" value="<?php echo $comp['Id']; ?>">
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
