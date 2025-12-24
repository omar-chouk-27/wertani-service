<!-- Article WS List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🔧 Articles WS - Hand-made / Composed (<?php echo count($articles_ws); ?>)</h3>
        <a href="?type=ws&action=add" class="btn btn-primary">
            ➕ Nouvel Article WS
        </a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Référence</th>
                <th>Nom</th>
                <th>Quantité</th>
                <th>Prix Vente</th>
                <th>Sous-Articles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($articles_ws)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #999;">
                        Aucun article WS trouvé. Cliquez sur "Nouvel Article WS" pour ajouter.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($articles_ws as $art): ?>
                <tr>
                    <td><strong>#<?php echo $art['Id']; ?></strong></td>
                    <td><?php echo e($art['Reference'] ?? '-'); ?></td>
                    <td><strong><?php echo e($art['Name']); ?></strong></td>
                    <td><?php echo $art['Quantity']; ?></td>
                    <td><strong><?php echo formatCurrency($art['PrixVente']); ?></strong></td>
                    <td>
                        <span class="badge badge-info">
                            <?php echo $art['sous_count']; ?> sous-article(s)
                        </span>
                    </td>
                    <td>
                        <a href="?type=ws&action=edit&id=<?php echo $art['Id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                            ✏️
                        </a>
                        <a href="?type=ws&action=edit&id=<?php echo $art['Id']; ?>&show_sous=1" class="btn btn-sm btn-success" title="Gérer Sous-Articles">
                            📋 Sous-Articles
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="type" value="ws">
                            <input type="hidden" name="id" value="<?php echo $art['Id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
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
