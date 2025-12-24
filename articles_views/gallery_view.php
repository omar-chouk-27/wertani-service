<!-- Gallery View - Photos of Work -->
<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    padding: 2rem 0;
}

.gallery-item {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.gallery-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.gallery-info {
    padding: 1rem;
}

.gallery-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.gallery-subtitle {
    color: #666;
    font-size: 0.9rem;
}

.upload-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 10000;
    justify-content: center;
    align-items: center;
}

.lightbox.active {
    display: flex;
}

.lightbox-content {
    max-width: 90%;
    max-height: 90%;
    position: relative;
}

.lightbox-image {
    max-width: 100%;
    max-height: 85vh;
    object-fit: contain;
}

.lightbox-close {
    position: absolute;
    top: -40px;
    right: 0;
    color: white;
    font-size: 2.5rem;
    cursor: pointer;
    background: none;
    border: none;
}

.lightbox-caption {
    color: white;
    text-align: center;
    padding: 1rem;
    font-size: 1.1rem;
}

.no-photos {
    text-align: center;
    padding: 4rem;
    color: #999;
}

.no-photos-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📸 Galerie Photos de Travaux</h3>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('upload-form').style.display='block'">
            ➕ Ajouter Photo
        </button>
    </div>
</div>

<!-- Upload Form -->
<div id="upload-form" class="upload-section" style="display: none;">
    <h3 style="margin-bottom: 1.5rem;">➕ Ajouter une Photo de Travail</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_photo">
        <input type="hidden" name="type" value="gallery">
        
        <table class="form-table">
            <tr>
                <td class="required">Photo</td>
                <td>
                    <input type="file" name="photo" class="form-control" accept="image/*" required>
                    <small style="color: #666;">JPG, PNG, GIF (Max 5MB)</small>
                </td>
            </tr>
            <tr>
                <td>Article Associé</td>
                <td>
                    <select name="article_id" class="form-select">
                        <option value="">-- Optionnel --</option>
                        <?php
                        $all_articles = getData($conn, "SELECT Id, Name, Reference FROM article_ws ORDER BY Name");
                        foreach ($all_articles as $art):
                        ?>
                            <option value="<?php echo $art['Id']; ?>">
                                <?php echo e($art['Name']); ?>
                                <?php echo $art['Reference'] ? ' (' . e($art['Reference']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <textarea name="description" class="form-control" rows="3" placeholder="Décrivez le travail réalisé..."></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right; padding-top: 1rem;">
                    <button type="submit" class="btn btn-success">✅ Ajouter</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('upload-form').style.display='none'">❌ Annuler</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<?php
// Get all photos
$photos = [];
try {
    $photos = getData($conn, "
        SELECT 
            p.Id,
            p.PhotoPath,
            p.PhotoName,
            p.Description,
            p.ArticleWS_Id,
            a.Name as ArticleName,
            a.Reference
        FROM article_ws_photos p
        LEFT JOIN article_ws a ON p.ArticleWS_Id = a.Id
        ORDER BY p.CreationDate DESC
    ");
} catch (Exception $e) {
    // Table might not exist
}
?>

<?php if (empty($photos)): ?>
    <div class="card">
        <div class="no-photos">
            <div class="no-photos-icon">📸</div>
            <h3>Aucune photo de travaux</h3>
            <p>Ajoutez des photos de vos réalisations pour créer votre galerie.</p>
        </div>
    </div>
<?php else: ?>
    <div class="gallery-grid">
        <?php foreach ($photos as $photo): ?>
            <div class="gallery-item" onclick="openLightbox('<?php echo e($photo['PhotoPath']); ?>', '<?php echo e($photo['Description'] ?? $photo['ArticleName'] ?? ''); ?>')">
                <img src="<?php echo e($photo['PhotoPath']); ?>" alt="Photo" class="gallery-image">
                <div class="gallery-info">
                    <?php if ($photo['ArticleName']): ?>
                        <div class="gallery-title"><?php echo e($photo['ArticleName']); ?></div>
                        <?php if ($photo['Reference']): ?>
                            <div class="gallery-subtitle">Réf: <?php echo e($photo['Reference']); ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($photo['Description']): ?>
                        <div class="gallery-subtitle" style="margin-top: 0.5rem;">
                            <?php echo nl2br(e(substr($photo['Description'], 0, 80))); ?>
                            <?php echo strlen($photo['Description']) > 80 ? '...' : ''; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button class="lightbox-close" onclick="closeLightbox()">×</button>
        <img id="lightbox-image" src="" alt="" class="lightbox-image">
        <div id="lightbox-caption" class="lightbox-caption"></div>
    </div>
</div>

<script>
function openLightbox(imageSrc, caption) {
    document.getElementById('lightbox-image').src = imageSrc;
    document.getElementById('lightbox-caption').textContent = caption;
    document.getElementById('lightbox').classList.add('active');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('lightbox').classList.contains('active')) {
        closeLightbox();
    }
});
</script>
