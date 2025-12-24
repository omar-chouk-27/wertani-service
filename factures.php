<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Factures - Wertani Service';

require 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📄 Factures</h3>
    </div>
    
    <div style="padding: 2rem; text-align: center;">
        <h2>🚧 En Construction</h2>
        <p>La gestion des factures sera bientôt disponible.</p>
        <p>Pour l'instant, utilisez:</p>
        <ul style="list-style: none; padding: 0;">
            <li><a href="projets.php">📋 Projets / Devis</a></li>
            <li><a href="suivi_projet.php">🔄 Suivie de projet</a></li>
            <li><a href="journal_depenses.php">💰 Journal Dépenses</a></li>
        </ul>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
