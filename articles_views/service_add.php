<!-- Services Add/Edit Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $action === 'add' ? '➕ Nouveau Service' : '✏️ Modifier Service'; ?>
        </h3>
        <a href="?type=service" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
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
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo ($item && isset($item['Name'])) ? e($item['Name']) : ''; ?>" 
                           placeholder="Ex: Réparation de pare-choc" required>
                </td>
            </tr>
            <tr>
                <td>Prix du Service</td>
                <td>
                    <input type="number" name="prix_service" class="form-control" min="0" step="0.001"
                           value="<?php echo ($item && isset($item['PrixService'])) ? $item['PrixService'] : 0; ?>">
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <textarea name="description" class="form-control" rows="3"><?php echo ($item && isset($item['Description'])) ? e($item['Description']) : ''; ?></textarea>
                </td>
            </tr>
        </table>
        
        <div style="padding: 1rem; background: #f8f9fa; margin-top: 1rem;">
            <h4 style="color: var(--primary); margin-bottom: 1rem;">📦 Articles Utilisés dans ce Service</h4>
            <p style="color: #666; font-size: 0.85rem; margin-bottom: 1rem;">
                Sélectionnez les articles ou composants utilisés pour ce service
            </p>
            
            <div id="service-articles-container">
                <?php 
                // Load existing service articles if editing
                $existing_articles = [];
                if ($action === 'edit' && !empty($service_articles)) {
                    foreach ($service_articles as $sa) {
                        $existing_articles[] = [
                            'type' => $sa['ArticleType'],
                            'id' => $sa['Article_Id'],
                            'quantity' => $sa['Quantity']
                        ];
                    }
                }
                
                if (empty($existing_articles)) {
                    $existing_articles[] = ['type' => '', 'id' => 0, 'quantity' => 1]; // One empty row
                }
                
                foreach ($existing_articles as $index => $ea):
                ?>
                <div class="service-article-row" style="display: grid; grid-template-columns: 200px 1fr 150px 50px; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                    <select name="article_types[]" class="form-select article-type-select" onchange="updateArticleOptions(this)">
                        <option value="">-- Type --</option>
                        <option value="article_ws" <?php echo $ea['type'] === 'article_ws' ? 'selected' : ''; ?>>Article WS</option>
                        <option value="composant_matiere" <?php echo $ea['type'] === 'composant_matiere' ? 'selected' : ''; ?>>Composant/Matière</option>
                    </select>
                    
                    <select name="article_ids[]" class="form-select article-id-select">
                        <option value="0">-- Sélectionner un article --</option>
                        <?php if ($ea['type'] === 'article_ws'): ?>
                            <?php foreach ($articles_ws_list as $art): ?>
                                <option value="<?php echo $art['Id']; ?>" <?php echo $ea['id'] == $art['Id'] ? 'selected' : ''; ?>>
                                    <?php echo e($art['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php elseif ($ea['type'] === 'composant_matiere'): ?>
                            <?php foreach ($composants_list as $comp): ?>
                                <option value="<?php echo $comp['Id']; ?>" <?php echo $ea['id'] == $comp['Id'] ? 'selected' : ''; ?>>
                                    <?php echo e($comp['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <input type="number" name="article_quantities[]" class="form-control" min="1" 
                           value="<?php echo $ea['quantity']; ?>" placeholder="Qté">
                    
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeArticleRow(this)">🗑️</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="btn btn-sm btn-primary" onclick="addArticleRow()" style="margin-top: 0.5rem;">
                ➕ Ajouter un article
            </button>
        </div>
        
        <div style="text-align: right; padding: 1rem;">
            <button type="submit" class="btn btn-lg btn-success">
                💾 Enregistrer
            </button>
            <a href="?type=service" class="btn btn-lg btn-secondary">
                ❌ Annuler
            </a>
        </div>
    </form>
</div>

<script>
// Article data for dynamic dropdowns
const articlesWS = <?php echo json_encode($articles_ws_list); ?>;
const composants = <?php echo json_encode($composants_list); ?>;

function updateArticleOptions(selectElement) {
    const row = selectElement.closest('.service-article-row');
    const articleSelect = row.querySelector('.article-id-select');
    const selectedType = selectElement.value;
    
    // Clear existing options
    articleSelect.innerHTML = '<option value="0">-- Sélectionner un article --</option>';
    
    // Add options based on type
    if (selectedType === 'article_ws') {
        articlesWS.forEach(art => {
            const option = document.createElement('option');
            option.value = art.Id;
            option.textContent = art.Name;
            articleSelect.appendChild(option);
        });
    } else if (selectedType === 'composant_matiere') {
        composants.forEach(comp => {
            const option = document.createElement('option');
            option.value = comp.Id;
            option.textContent = comp.Name;
            articleSelect.appendChild(option);
        });
    }
}

function addArticleRow() {
    const container = document.getElementById('service-articles-container');
    const newRow = document.createElement('div');
    newRow.className = 'service-article-row';
    newRow.style.cssText = 'display: grid; grid-template-columns: 200px 1fr 150px 50px; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;';
    newRow.innerHTML = `
        <select name="article_types[]" class="form-select article-type-select" onchange="updateArticleOptions(this)">
            <option value="">-- Type --</option>
            <option value="article_ws">Article WS</option>
            <option value="composant_matiere">Composant/Matière</option>
        </select>
        <select name="article_ids[]" class="form-select article-id-select">
            <option value="0">-- Sélectionner un article --</option>
        </select>
        <input type="number" name="article_quantities[]" class="form-control" min="1" value="1" placeholder="Qté">
        <button type="button" class="btn btn-sm btn-danger" onclick="removeArticleRow(this)">🗑️</button>
    `;
    container.appendChild(newRow);
}

function removeArticleRow(button) {
    const row = button.closest('.service-article-row');
    const container = document.getElementById('service-articles-container');
    
    // Keep at least one row
    if (container.children.length > 1) {
        row.remove();
    } else {
        // Clear the row instead of removing it
        row.querySelector('.article-type-select').value = '';
        row.querySelector('.article-id-select').innerHTML = '<option value="0">-- Sélectionner un article --</option>';
        row.querySelector('input[name="article_quantities[]"]').value = 1;
    }
}
</script>
