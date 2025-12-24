<!-- Article WS Add Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">➕ Nouvel Article WS</h3>
        <a href="?type=ws" class="btn btn-secondary">
            ← Retour
        </a>
    </div>
    
    <form method="POST" action="articles.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="type" value="ws">
        
        <table class="form-table">
            <tr>
                <td class="required">Nom</td>
                <td>
                    <input type="text" name="name" class="form-control" required>
                </td>
            </tr>
            <tr>
                <td>Référence</td>
                <td>
                    <input type="text" name="reference" class="form-control">
                </td>
            </tr>
            <tr>
                <td>Quantité</td>
                <td>
                    <input type="number" name="quantity" class="form-control" min="0" value="0">
                </td>
            </tr>
            <tr>
                <td>Prix de Vente</td>
                <td>
                    <input type="number" name="prix_vente" class="form-control" min="0" step="0.001" value="0">
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </td>
            </tr>
            <tr>
                <td>Photo Principale</td>
                <td>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                    <small style="color: #666;">JPG, PNG, GIF, WEBP (Max 5MB) - Photo qui sera affichée dans le dashboard</small>
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
