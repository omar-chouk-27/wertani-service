<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Rapport Financier - Wertani';

// Get selected entity filter
$entity_filter = $_GET['entity'] ?? 'all'; // all, services, saber

// Get financial data for each entity
$data_services = [
    'depenses' => 0,
    'revenus' => 0,
    'solde' => 0
];

$data_saber = [
    'depenses' => 0,
    'revenus' => 0,
    'solde' => 0
];

try {
    // Wertani Services
    $depenses_services = getRow($conn, "
        SELECT SUM(MontantTTC) as total 
        FROM journal_depenses 
        WHERE Comptabiliser = 1 AND Etat = 'Payee' AND Entite = 'Wertani Services'
    ");
    $revenus_services = getRow($conn, "
        SELECT SUM(MontantTTC) as total 
        FROM suivi_projet 
        WHERE Comptabiliser = 1 AND Etat = 'Payee' AND Entite = 'Wertani Services'
    ");
    $data_services['depenses'] = $depenses_services['total'] ?? 0;
    $data_services['revenus'] = $revenus_services['total'] ?? 0;
    $data_services['solde'] = $data_services['revenus'] - $data_services['depenses'];
    
    // Wertani Saber
    $depenses_saber = getRow($conn, "
        SELECT SUM(MontantTTC) as total 
        FROM journal_depenses 
        WHERE Comptabiliser = 1 AND Etat = 'Payee' AND Entite = 'Wertani Saber'
    ");
    $revenus_saber = getRow($conn, "
        SELECT SUM(MontantTTC) as total 
        FROM suivi_projet 
        WHERE Comptabiliser = 1 AND Etat = 'Payee' AND Entite = 'Wertani Saber'
    ");
    $data_saber['depenses'] = $depenses_saber['total'] ?? 0;
    $data_saber['revenus'] = $revenus_saber['total'] ?? 0;
    $data_saber['solde'] = $data_saber['revenus'] - $data_saber['depenses'];
} catch (Exception $e) {
    // Entity column might not exist yet
}

// Total combined
$total_depenses = $data_services['depenses'] + $data_saber['depenses'];
$total_revenus = $data_services['revenus'] + $data_saber['revenus'];
$total_solde = $total_revenus - $total_depenses;

// Get filtered data for display
if ($entity_filter === 'services') {
    $display_depenses = $data_services['depenses'];
    $display_revenus = $data_services['revenus'];
    $display_solde = $data_services['solde'];
} elseif ($entity_filter === 'saber') {
    $display_depenses = $data_saber['depenses'];
    $display_revenus = $data_saber['revenus'];
    $display_solde = $data_saber['solde'];
} else {
    $display_depenses = $total_depenses;
    $display_revenus = $total_revenus;
    $display_solde = $total_solde;
}

require 'includes/header.php';
?>

<style>
.entity-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 3px solid var(--primary);
    padding-bottom: 0.5rem;
    flex-wrap: wrap;
}

.entity-tab {
    padding: 0.75rem 1.5rem;
    background: white;
    border: 2px solid #ddd;
    border-radius: 8px 8px 0 0;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    color: #333;
    font-size: 1.05rem;
}

.entity-tab:hover {
    background: #f5f5f5;
    transform: translateY(-2px);
}

.entity-tab.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.financial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.financial-card {
    padding: 2rem;
    border-radius: 12px;
    border-left: 5px solid;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.financial-card h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
}

.financial-card .amount {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0.5rem 0;
}

.financial-card .label {
    color: #666;
    font-size: 0.95rem;
}

.card-revenus {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border-left-color: #4caf50;
}

.card-revenus h4 { color: #2e7d32; }
.card-revenus .amount { color: #1b5e20; }

.card-depenses {
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    border-left-color: #f44336;
}

.card-depenses h4 { color: #c62828; }
.card-depenses .amount { color: #b71c1c; }

.card-solde {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-left-color: #2196f3;
}

.card-solde h4 { color: #1565c0; }
.card-solde .amount { color: #0d47a1; }

.card-solde.positive {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border-left-color: #4caf50;
}

.card-solde.positive h4 { color: #2e7d32; }
.card-solde.positive .amount { color: #1b5e20; }

.card-solde.negative {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border-left-color: #ff9800;
}

.card-solde.negative h4 { color: #e65100; }
.card-solde.negative .amount { color: #bf360c; }

.comparison-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.comparison-table table {
    width: 100%;
    border-collapse: collapse;
}

.comparison-table th {
    background: var(--primary);
    color: white;
    padding: 1rem;
    text-align: left;
    font-size: 1.1rem;
}

.comparison-table td {
    padding: 1rem;
    border-bottom: 1px solid #ddd;
    font-size: 1rem;
}

.comparison-table tr:hover {
    background: #f5f5f5;
}

.entity-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-services {
    background: #e3f2fd;
    color: #1565c0;
}

.badge-saber {
    background: #f3e5f5;
    color: #7b1fa2;
}
</style>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">💰 Rapport Financier</h2>
    </div>
</div>

<!-- Entity Tabs -->
<div class="entity-tabs">
    <a href="?entity=all" class="entity-tab <?php echo $entity_filter === 'all' ? 'active' : ''; ?>">
        📊 Vue Globale
    </a>
    <a href="?entity=services" class="entity-tab <?php echo $entity_filter === 'services' ? 'active' : ''; ?>">
        🔧 Wertani Services
    </a>
    <a href="?entity=saber" class="entity-tab <?php echo $entity_filter === 'saber' ? 'active' : ''; ?>">
        ⚙️ Wertani Saber
    </a>
</div>

<!-- Financial Summary Cards -->
<div class="financial-grid">
    <!-- Revenus -->
    <div class="financial-card card-revenus">
        <h4>💰 Revenus</h4>
        <div class="amount"><?php echo formatCurrency($display_revenus); ?></div>
        <div class="label">Projets payés comptabilisés</div>
    </div>
    
    <!-- Dépenses -->
    <div class="financial-card card-depenses">
        <h4>📉 Dépenses</h4>
        <div class="amount"><?php echo formatCurrency($display_depenses); ?></div>
        <div class="label">Achats payés comptabilisés</div>
    </div>
    
    <!-- Solde -->
    <div class="financial-card card-solde <?php echo $display_solde >= 0 ? 'positive' : 'negative'; ?>">
        <h4><?php echo $display_solde >= 0 ? '✅' : '⚠️'; ?> Solde Net</h4>
        <div class="amount"><?php echo formatCurrency($display_solde); ?></div>
        <div class="label">
            <?php if ($display_solde >= 0): ?>
                Bénéfice
            <?php else: ?>
                Déficit
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Comparison Table (only in global view) -->
<?php if ($entity_filter === 'all'): ?>
<div class="comparison-table">
    <table>
        <thead>
            <tr>
                <th>Entité</th>
                <th>Revenus</th>
                <th>Dépenses</th>
                <th>Solde</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <span class="entity-badge badge-services">🔧 Wertani Services</span>
                </td>
                <td style="color: #2e7d32; font-weight: 600;">
                    <?php echo formatCurrency($data_services['revenus']); ?>
                </td>
                <td style="color: #c62828; font-weight: 600;">
                    <?php echo formatCurrency($data_services['depenses']); ?>
                </td>
                <td style="font-weight: 700; color: <?php echo $data_services['solde'] >= 0 ? '#2e7d32' : '#e65100'; ?>">
                    <?php echo formatCurrency($data_services['solde']); ?>
                </td>
                <td>
                    <?php 
                    if ($total_revenus > 0) {
                        $percent = ($data_services['revenus'] / $total_revenus) * 100;
                        echo number_format($percent, 1) . '%';
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="entity-badge badge-saber">⚙️ Wertani Saber</span>
                </td>
                <td style="color: #2e7d32; font-weight: 600;">
                    <?php echo formatCurrency($data_saber['revenus']); ?>
                </td>
                <td style="color: #c62828; font-weight: 600;">
                    <?php echo formatCurrency($data_saber['depenses']); ?>
                </td>
                <td style="font-weight: 700; color: <?php echo $data_saber['solde'] >= 0 ? '#2e7d32' : '#e65100'; ?>">
                    <?php echo formatCurrency($data_saber['solde']); ?>
                </td>
                <td>
                    <?php 
                    if ($total_revenus > 0) {
                        $percent = ($data_saber['revenus'] / $total_revenus) * 100;
                        echo number_format($percent, 1) . '%';
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
            <tr style="background: #f5f5f5; font-weight: 700;">
                <td>📊 TOTAL</td>
                <td style="color: #2e7d32;">
                    <?php echo formatCurrency($total_revenus); ?>
                </td>
                <td style="color: #c62828;">
                    <?php echo formatCurrency($total_depenses); ?>
                </td>
                <td style="color: <?php echo $total_solde >= 0 ? '#2e7d32' : '#e65100'; ?>">
                    <?php echo formatCurrency($total_solde); ?>
                </td>
                <td>100%</td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div style="margin-top: 2rem; padding: 1.5rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
    <h4 style="margin: 0 0 0.5rem 0; color: #856404;">ℹ️ Information</h4>
    <p style="margin: 0; color: #856404;">
        Ce rapport affiche uniquement les transactions <strong>payées</strong> et <strong>comptabilisées</strong>.
        <?php if ($entity_filter !== 'all'): ?>
            <br>Vous consultez les données de <strong><?php echo $entity_filter === 'services' ? 'Wertani Services' : 'Wertani Saber'; ?></strong>.
        <?php endif; ?>
    </p>
</div>

<?php require 'includes/footer.php'; ?>
