<!-- Project Add/Edit Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $action === 'add' ? '➕ Nouveau Devis' : '✏️ Modifier Projet'; ?>
        </h3>
        <a href="projets.php" class="btn btn-secondary">
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
                <td>Client</td>
                <td>
                    <select name="client_id" class="form-select">
                        <option value="">-- Sans client --</option>
                        <?php foreach ($clients as $cli): ?>
                            <option value="<?php echo $cli['Id']; ?>" 
                                    <?php echo ($project && $project['Client_Id'] == $cli['Id']) ? 'selected' : ''; ?>>
                                <?php echo e($cli['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td>Voiture</td>
                <td>
                    <?php 
                    $selected_car_id = ($project && isset($project['CarId'])) ? $project['CarId'] : '';
                    $field_name = 'car_id';
                    $required = false;
                    include 'includes/car_selector.php'; 
                    ?>
                </td>
            </tr>
            
            <tr>
                <td>Matricule</td>
                <td>
                    <input type="text" name="matricule" class="form-control" 
                           value="<?php echo ($project && isset($project['Matricule'])) ? e($project['Matricule']) : ''; ?>"
                           placeholder="Ex: 123 TU 1234">
                </td>
            </tr>
            
            <tr>
                <td class="required">Titre</td>
                <td>
                    <input type="text" name="title" class="form-control" 
                           value="<?php echo ($project && isset($project['Title'])) ? e($project['Title']) : ''; ?>" 
                           placeholder="Ex: Réparation carrosserie" required>
                </td>
            </tr>
            
            <tr>
                <td>Description</td>
                <td>
                    <textarea name="description" class="form-control" rows="3"><?php echo ($project && isset($project['Description'])) ? e($project['Description']) : ''; ?></textarea>
                </td>
            </tr>
            
            <tr>
                <td>Type de Projet</td>
                <td>
                    <select name="type_id" class="form-select">
                        <option value="1" <?php echo ($project && $project['ProjectType_Id'] == 1) ? 'selected' : ''; ?>>
                            📝 Devis
                        </option>
                        <option value="2" <?php echo ($project && $project['ProjectType_Id'] == 2) ? 'selected' : ''; ?>>
                            🔄 En cours
                        </option>
                        <option value="4" <?php echo ($project && $project['ProjectType_Id'] == 4) ? 'selected' : ''; ?>>
                            ❌ Annulé
                        </option>
                    </select>
                </td>
            </tr>
        </table>
        
        <!-- Articles Section -->
        <div style="padding: 1rem; background: #f8f9fa; margin-top: 1rem;">
            <h4 style="color: var(--primary); margin-bottom: 1rem;">📦 Articles / Pièces / Services</h4>
            
            <div id="articles-container">
                <?php 
                $existing_articles = [];
                if ($action === 'edit' && !empty($project_articles)) {
                    foreach ($project_articles as $pa) {
                        $existing_articles[] = [
                            'type' => 'old_article', // We'll determine type later
                            'id' => $pa['Article_Id'],
                            'quantity' => $pa['Quantity'],
                            'price' => $pa['UnitPrice']
                        ];
                    }
                }
                
                if (empty($existing_articles)) {
                    $existing_articles[] = ['type' => '', 'id' => 0, 'quantity' => 1, 'price' => 0];
                }
                
                foreach ($existing_articles as $index => $ea):
                ?>
                <div class="article-row" style="display: grid; grid-template-columns: 150px 1fr 100px 120px 50px; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                    <select name="article_types[]" class="form-select article-type-select" onchange="updateArticleDropdown(this)">
                        <option value="">-- Type --</option>
                        <option value="article_ws" <?php echo $ea['type'] === 'article_ws' ? 'selected' : ''; ?>>Article WS</option>
                        <option value="composant" <?php echo $ea['type'] === 'composant' ? 'selected' : ''; ?>>Composant</option>
                        <option value="service" <?php echo $ea['type'] === 'service' ? 'selected' : ''; ?>>Service</option>
                        <option value="old_article" <?php echo $ea['type'] === 'old_article' ? 'selected' : ''; ?>>Article (Ancien)</option>
                    </select>
                    
                    <select name="article_ids[]" class="form-select article-id-select">
                        <option value="0">-- Sélectionner --</option>
                    </select>
                    
                    <input type="number" name="article_quantities[]" class="form-control" min="1" 
                           value="<?php echo $ea['quantity']; ?>" placeholder="Qté">
                    
                    <input type="number" name="article_prices[]" class="form-control" min="0" step="0.001"
                           value="<?php echo $ea['price']; ?>" placeholder="Prix" onchange="calculateTotal()">
                    
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeArticleRow(this)">🗑️</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="btn btn-sm btn-primary" onclick="addArticleRow()" style="margin-top: 0.5rem;">
                ➕ Ajouter un article
            </button>
            
            <div style="margin-top: 1rem; padding: 1rem; background: white; border-radius: 0.5rem;">
                <h4 style="color: var(--success);">💰 Total: <span id="total-amount">0.000</span> DT</h4>
            </div>
        </div>
        
        <table class="form-table">
            <tr>
                <td>Notes</td>
                <td>
                    <textarea name="notes" class="form-control" rows="2"><?php echo ($project && isset($project['Notes'])) ? e($project['Notes']) : ''; ?></textarea>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" style="background: white; text-align: right; padding: 1rem;">
                    <button type="submit" class="btn btn-lg btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="projets.php" class="btn btn-lg btn-secondary">
                        ❌ Annuler
                    </a>
                </td>
            </tr>
        </table>
    </form>
</div>

<script>
// Article data
const articlesWS = <?php echo json_encode($articles_ws); ?>;
const composants = <?php echo json_encode($composants); ?>;
const services = <?php echo json_encode($services); ?>;
const oldArticles = <?php echo json_encode($old_articles); ?>;

function updateArticleDropdown(selectElement) {
    const row = selectElement.closest('.article-row');
    const articleSelect = row.querySelector('.article-id-select');
    const priceInput = row.querySelector('input[name="article_prices[]"]');
    const selectedType = selectElement.value;
    
    articleSelect.innerHTML = '<option value="0">-- Sélectionner --</option>';
    
    let articles = [];
    if (selectedType === 'article_ws') articles = articlesWS;
    else if (selectedType === 'composant') articles = composants;
    else if (selectedType === 'service') articles = services;
    else if (selectedType === 'old_article') articles = oldArticles;
    
    articles.forEach(art => {
        const option = document.createElement('option');
        option.value = art.Id;
        option.textContent = art.Name;
        option.dataset.price = art.PrixVente || 0;
        articleSelect.appendChild(option);
    });
    
    // Update price when article changes
    articleSelect.onchange = function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.price) {
            priceInput.value = parseFloat(selectedOption.dataset.price).toFixed(3);
            calculateTotal();
        }
    };
}

function addArticleRow() {
    const container = document.getElementById('articles-container');
    const newRow = document.createElement('div');
    newRow.className = 'article-row';
    newRow.style.cssText = 'display: grid; grid-template-columns: 150px 1fr 100px 120px 50px; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;';
    newRow.innerHTML = `
        <select name="article_types[]" class="form-select article-type-select" onchange="updateArticleDropdown(this)">
            <option value="">-- Type --</option>
            <option value="article_ws">Article WS</option>
            <option value="composant">Composant</option>
            <option value="service">Service</option>
            <option value="old_article">Article (Ancien)</option>
        </select>
        <select name="article_ids[]" class="form-select article-id-select">
            <option value="0">-- Sélectionner --</option>
        </select>
        <input type="number" name="article_quantities[]" class="form-control" min="1" value="1" placeholder="Qté" onchange="calculateTotal()">
        <input type="number" name="article_prices[]" class="form-control" min="0" step="0.001" value="0" placeholder="Prix" onchange="calculateTotal()">
        <button type="button" class="btn btn-sm btn-danger" onclick="removeArticleRow(this)">🗑️</button>
    `;
    container.appendChild(newRow);
}

function removeArticleRow(button) {
    const row = button.closest('.article-row');
    const container = document.getElementById('articles-container');
    
    if (container.children.length > 1) {
        row.remove();
        calculateTotal();
    }
}

function calculateTotal() {
    const quantities = document.querySelectorAll('input[name="article_quantities[]"]');
    const prices = document.querySelectorAll('input[name="article_prices[]"]');
    let total = 0;
    
    quantities.forEach((qtyInput, index) => {
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(prices[index].value) || 0;
        total += qty * price;
    });
    
    document.getElementById('total-amount').textContent = total.toFixed(3);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update existing article dropdowns
    document.querySelectorAll('.article-type-select').forEach(select => {
        if (select.value) {
            updateArticleDropdown(select);
        }
    });
    calculateTotal();
});
</script>
