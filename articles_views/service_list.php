<!-- Services List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">⚙️ Services (<?php echo count($services); ?>)</h3>
        <a href="?type=service&action=add" class="btn btn-primary">
            ➕ Nouveau Service
        </a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du Service</th>
                <th>Prix</th>
                <th>Articles Utilisés</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($services)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: #999;">
                        Aucun service trouvé. Cliquez sur "Nouveau Service" pour ajouter.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($services as $serv): ?>
                <tr>
                    <td><strong>#<?php echo $serv['Id']; ?></strong></td>
                    <td><strong><?php echo e($serv['Name']); ?></strong></td>
                    <td><strong><?php echo formatCurrency($serv['PrixService']); ?></strong></td>
                    <td>
                        <span class="badge badge-info">
                            <?php echo $serv['article_count']; ?> article(s)
                        </span>
                    </td>
                    <td>
                        <a href="?type=service&action=edit&id=<?php echo $serv['Id']; ?>" class="btn btn-sm btn-primary">
                            ✏️ Modifier
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce service?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="type" value="service">
                            <input type="hidden" name="id" value="<?php echo $serv['Id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                🗑️
                            </button>
                        </form>
                    </td>
                </tr>
                <?php if ($serv['Description']): ?>
                <tr>
                    <td colspan="5" style="background: #f8f9fa; padding: 0.5rem; font-size: 0.85rem;">
                        <strong>Description:</strong> <?php echo e($serv['Description']); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
