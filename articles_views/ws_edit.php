<!-- Article WS Add/Edit Form -->
<?php
// Initialize all variables with defaults
$nom_value = '';
$ref_value = '';
$qty_value = 0;
$prix_value = 0;
$desc_value = '';

// Fill values if editing and item exists
if ($action === 'edit' && !empty($item) && is_array($item)) {
    $nom_value = $item['Name'] ?? '';
    $ref_value = $item['Reference'] ?? '';
    $qty_value = $item['Quantity'] ?? 0;
    $prix_value = $item['PrixVente'] ?? 0;
    $desc_value = $item['Description'] ?? '';
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $action === 'add' ? '➕ Nouvel Article WS' : '✏️ Modifier Article WS'; ?>
        </h3>
        <a href="?type=ws" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
    <?php if ($action === 'edit' && !empty($item)): ?>
        <div style="background: #d4edda; padding: 1rem; margin: 1rem; border-radius: 8px; border: 2px solid #28a745;">
            <strong style="color: #155724;">✅ Mode Édition - ID: <?php echo $id; ?></strong><br>
            <span style="color: #155724;">
                Nom: <strong><?php echo e($item['Name'] ?? 'N/A'); ?></strong> | 
                Ref: <strong><?php echo e($item['Reference'] ?? 'N/A'); ?></strong> |
                Qté: <strong><?php echo $qty_value; ?></strong>
            </span>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="articles.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'add' : 'update'; ?>">
        <input type="hidden" name="type" value="ws">
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
                <td>Prix de Vente</td>
                <td>
                    <input type="number" 
                           name="prix_vente" 
                           class="form-control" 
                           min="0" 
                           step="0.001"
                           value="<?php echo $prix_value; ?>">
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
                <td>Photo</td>
                <td>
                    <?php
                    // Get current main photo
                    $main_photo = null;
                    if ($action === 'edit') {
                        try {
                            $photo_row = getRow($conn, "SELECT PhotoPath FROM article_ws_photos WHERE ArticleWS_Id = ? AND IsMain = 1", [$id]);
                            if ($photo_row) {
                                $main_photo = $photo_row['PhotoPath'];
                            }
                        } catch (Exception $e) {}
                    }
                    
                    if ($main_photo && file_exists($main_photo)):
                    ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="<?php echo htmlspecialchars($main_photo); ?>" 
                                 alt="Photo actuelle" 
                                 style="max-width: 200px; max-height: 200px; border: 2px solid #ddd; border-radius: 8px; display: block; margin-bottom: 0.5rem;">
                            <small style="color: #666;">Photo principale actuelle</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                    <small style="color: #666;">JPG, PNG, GIF, WEBP (Max 5MB) - <?php echo $main_photo ? 'Remplace la photo principale actuelle' : 'Sera définie comme photo principale'; ?></small>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="background: white; text-align: right; padding: 1rem;">
                    <button type="submit" class="btn btn-lg btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="?type=ws" class="btn btn-lg btn-secondary">
                        ❌ Annuler
                    </a>
                </td>
            </tr>
        </table>
    </form>
</div>


