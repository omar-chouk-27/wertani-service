<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Tableau de Bord - Wertani Service';

// Get statistics
$clients_count = getCount($conn, "SELECT COUNT(*) as total FROM Client");
$articles_count = getCount($conn, "SELECT COUNT(*) as total FROM Article");
$projets_count = getCount($conn, "SELECT COUNT(*) as total FROM Project");
$projets_devis = getCount($conn, "SELECT COUNT(*) as total FROM Project WHERE ProjectType_Id = 1");
$projets_encours = getCount($conn, "SELECT COUNT(*) as total FROM Project WHERE ProjectType_Id = 2");
$factures_count = getCount($conn, "SELECT COUNT(*) as total FROM Project WHERE ProjectType_Id = 3");

// Get first main photo from articles for dashboard boxes
$main_photo = null;
try {
    $photo_row = getRow($conn, "SELECT PhotoPath FROM article_ws_photos WHERE IsMain = 1 ORDER BY CreationDate DESC LIMIT 1");
    if ($photo_row) {
        $main_photo = $photo_row['PhotoPath'];
    }
} catch (Exception $e) {
    // Table might not exist yet, use logo as fallback
    $main_photo = 'assets/images/logo.png';
}
if (!$main_photo) {
    $main_photo = 'assets/images/logo.png';
}

$fournisseurs_count = 0;
try {
    $result = $conn->query("SHOW TABLES LIKE 'supplier'");
    if ($result->rowCount() > 0) {
        $fournisseurs_count = getCount($conn, "SELECT COUNT(*) as total FROM supplier");
    }
} catch (Exception $e) {
    $fournisseurs_count = 0;
}

// Get recent projects with client info
$recent_projects = getData($conn, "
    SELECT p.*, 
           c.Name as client_name,
           pt.Name as type_name
    FROM Project p
    LEFT JOIN Client c ON p.Client_Id = c.Id
    LEFT JOIN ProjectType pt ON p.ProjectType_Id = pt.Id
    ORDER BY p.CreationDate DESC
    LIMIT 5
");

require 'includes/header.php';
?>

<?php displayFlash(); ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">📊 Tableau de Bord - Wertani Service</h2>
    </div>
</div>

<div class="stats-grid">
    <a href="clients.php" class="stat-card stat-card-link">
        <div class="stat-icon-bg">👥</div>
        <div class="stat-overlay">
            <div class="stat-value"><?php echo $clients_count; ?></div>
            <div class="stat-label">Clients</div>
        </div>
    </a>

    <a href="articles.php" class="stat-card stat-card-link">
        <div class="stat-icon-bg">📦</div>
        <div class="stat-overlay">
            <div class="stat-value"><?php echo $articles_count; ?></div>
            <div class="stat-label">Articles</div>
        </div>
    </a>

    <a href="fournisseurs.php" class="stat-card stat-card-link">
        <div class="stat-icon-bg">🏭</div>
        <div class="stat-overlay">
            <div class="stat-value"><?php echo $fournisseurs_count; ?></div>
            <div class="stat-label">Fournisseurs</div>
        </div>
    </a>

    <a href="projets.php" class="stat-card stat-card-link">
        <div class="stat-icon-bg">📋</div>
        <div class="stat-overlay">
            <div class="stat-value"><?php echo $projets_count; ?></div>
            <div class="stat-label">Projets Total</div>
        </div>
    </a>

    <a href="projets.php?type=1" class="stat-card stat-card-link">
        <div class="stat-icon-bg">📝</div>
        <div class="stat-overlay">
            <div class="stat-value"><?php echo $projets_devis; ?></div>
            <div class="stat-label">Devis</div>
        </div>
    </a>

    <a href="suivi_projet.php" class="stat-card stat-card-link">
        <div class="stat-icon-bg">🔄</div>
        <div class="stat-overlay">
            <div class="stat-value"><?php echo $projets_encours; ?></div>
            <div class="stat-label">En Cours</div>
        </div>
    </a>

    <a href="factures.php" class="stat-card stat-card-link">
        <div class="stat-icon-bg">🧾</div>
        <div class="stat-overlay">
            <div class="stat-value"><?php echo $factures_count; ?></div>
            <div class="stat-label">Factures</div>
        </div>
    </a>
</div>

<?php if (!empty($recent_projects)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📌 Projets Récents</h3>
        <a href="projets.php" class="btn btn-primary btn-sm">Voir tout →</a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>N° Projet</th>
                <th>Titre</th>
                <th>Client</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_projects as $projet): ?>
            <tr>
                <td><strong><?php echo e($projet['ProjectNumber'] ?? 'N/A'); ?></strong></td>
                <td><?php echo e($projet['Title']); ?></td>
                <td><?php echo e($projet['client_name'] ?? '-'); ?></td>
                <td>
                    <span class="badge badge-<?php echo $projet['ProjectType_Id'] == 1 ? 'info' : ($projet['ProjectType_Id'] == 2 ? 'warning' : 'success'); ?>">
                        <?php echo e($projet['type_name'] ?? '-'); ?>
                    </span>
                </td>
                <td><strong><?php echo formatCurrency($projet['FinalAmount'] ?? 0); ?></strong></td>
                <td>
                    <span class="badge badge-<?php echo $projet['PaymentStatus'] === 'Paye' ? 'success' : ($projet['PaymentStatus'] === 'Partiel' ? 'warning' : 'danger'); ?>">
                        <?php echo e($projet['PaymentStatus']); ?>
                    </span>
                </td>
                <td>
                    <a href="projets.php?action=edit&id=<?php echo $projet['Id']; ?>" class="btn btn-sm btn-primary">
                        ✏️ Modifier
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">⚡ Actions Rapides</h3>
    </div>
    
    <div class="d-flex flex-wrap gap-2">
        <a href="clients.php?action=add" class="btn btn-primary">
            👥 Nouveau Client
        </a>
        <a href="articles.php?action=add" class="btn btn-primary">
            📦 Nouvel Article
        </a>
        <a href="fournisseurs.php?action=add" class="btn btn-primary">
            🏭 Nouveau Fournisseur
        </a>
        <a href="projets.php?action=add" class="btn btn-primary">
            📋 Nouveau Projet
        </a>
        <a href="journal_depenses.php?action=add" class="btn btn-primary">
            💰 Nouvelle Dépense
        </a>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
