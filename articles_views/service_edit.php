<!-- Services Add/Edit Form -->
<?php
// Initialize variables with defaults
$nom_value = '';
$desc_value = '';
$prix_value = 0;

// Fill values if editing and item exists
if ($action === 'edit' && !empty($item) && is_array($item)) {
    $nom_value = $item['Name'] ?? '';
    $desc_value = $item['Description'] ?? '';
    $prix_value = $item['PrixService'] ?? 0;
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $action === 'add' ? '➕ Nouveau Service' : '✏️ Modifier Service'; ?>
        </h3>
        <a href="?type=service" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
    <?php if ($action === 'edit' && !empty($item)): ?>
        <div style="background: #d4edda; padding: 1rem; margin: 1rem; border-radius: 8px; border: 2px solid #28a745;">
            <strong style="color: #155724;">✅ Mode Édition - ID: <?php echo $id; ?></strong><br>
            <span style="color: #155724;">
                Nom: <strong><?php echo e($item['Name'] ?? 'N/A'); ?></strong> | 
                Prix: <strong><?php echo number_format($prix_value, 3); ?> TND</strong>
            </span>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="articles.php">
        <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'add' : 'update'; ?>">
        <input type="hidden" name="type" value="service">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <td class="required">Nom du Service</td>
                <td>
                    <input type="text" 
                           name="name" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($nom_value); ?>" 
                           required>
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <textarea name="description" 
                              class="form-control" 
                              rows="3"><?php echo htmlspecialchars($desc_value); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>Prix du Service</td>
                <td>
                    <input type="number" 
                           name="prix_service" 
                           class="form-control" 
                           min="0" 
                           step="0.001"
                           value="<?php echo $prix_value; ?>">
                </td>
            </tr>
            
            <!-- Articles associés -->
            <tr>
                <td colspan="2" style="background: #f8f9fa; padding: 1rem;">
                    <strong>📦 Articles Associés au Service</strong>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addArticleLine()" style="float: right;">
                        ➕ Ajouter Article
                    </button>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div id="articles-container">
                        <?php if ($action === 'edit' && !empty($service_articles)): ?>
                            <?php foreach ($service_articles as $sa): ?>
                                <div class="article-line" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                                    <select name="article_types[]" class="form-select" style="width: 150px;">
                                        <option value="ws" <?php echo $sa['ArticleType'] === 'ws' ? 'selected' : ''; ?>>Article WS</option>
                                        <option value="composant" <?php echo $sa['ArticleType'] === 'composant' ? 'selected' : ''; ?>>Composant</option>
                                    </select>
                                    <select name="article_ids[]" class="form-select">
                                        <option value="">-- Article --</option>
                                        <?php foreach ($articles_ws as $art): ?>
                                            <option value="<?php echo $art['Id']; ?>" <?php echo $sa['Article_Id'] == $art['Id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($art['Name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <?php foreach ($composants as $comp): ?>
                                            <option value="<?php echo $comp['Id']; ?>" <?php echo $sa['Article_Id'] == $comp['Id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($comp['Name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="article_quantities[]" class="form-control" value="<?php echo $sa['Quantity']; ?>" min="1" style="width: 100px;">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">🗑️</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="text-align: right; padding: 1.5rem;">
                    <button type="submit" class="btn btn-lg btn-success">💾 Enregistrer</button>
                    <a href="?type=service" class="btn btn-lg btn-secondary">❌ Annuler</a>
                </td>
            </tr>
        </table>
    </form>
</div>

<script>
function addArticleLine() {
    const container = document.getElementById('articles-container');
    const line = document.createElement('div');
    line.className = 'article-line';
    line.style.cssText = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;';
    
    line.innerHTML = `
        <select name="article_types[]" class="form-select" style="width: 150px;">
            <option value="ws">Article WS</option>
            <option value="composant">Composant</option>
        </select>
        <select name="article_ids[]" class="form-select">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($articles_ws as $art): ?>
                <option value="<?php echo $art['Id']; ?>"><?php echo htmlspecialchars($art['Name']); ?></option>
            <?php endforeach; ?>
            <?php foreach ($composants as $comp): ?>
                <option value="<?php echo $comp['Id']; ?>"><?php echo htmlspecialchars($comp['Name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="article_quantities[]" class="form-control" value="1" min="1" style="width: 100px;">
        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">🗑️</button>
    `;
    
    container.appendChild(line);
}
</script>
