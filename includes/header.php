<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Système de gestion pour Wertani Service">
    <title><?php echo $page_title ?? 'Wertani Service'; ?></title>
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/main.css">
    
    <!-- Additional page-specific styles -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <img src="assets/images/logo.png" alt="Logo Wertani Service" class="logo-img">
            <h1>Wertani Service</h1>
        </div>
        <div class="user-info">
            <span>Bienvenue, <strong><?php echo htmlspecialchars($_SESSION['user'] ?? 'Utilisateur'); ?></strong></span>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $nav_items = [
            'dashboard.php' => ['icon' => '📊', 'label' => 'Tableau de Bord'],
            'clients.php' => ['icon' => '👥', 'label' => 'Clients'],
            'employees.php' => ['icon' => '👷', 'label' => 'Employés'],
            'articles.php' => ['icon' => '📦', 'label' => 'Suivis Article / Services'],
            'galerie.php' => ['icon' => '🚗', 'label' => 'Galerie Travaux'],
            'journal_depenses.php' => ['icon' => '💰', 'label' => 'Journal Dépenses'],
            'fournisseurs.php' => ['icon' => '🏭', 'label' => 'Fournisseurs'],
            'projets.php' => ['icon' => '📋', 'label' => 'Projets'],
            'suivi_projet.php' => ['icon' => '🔄', 'label' => 'Suivie de projet'],
            'factures.php' => ['icon' => '🧾', 'label' => 'Factures'],
            'rapport_financier.php' => ['icon' => '📈', 'label' => 'Rapport Financier']
        ];
        
        foreach ($nav_items as $page => $item):
            $active_class = ($current_page === $page) ? 'active' : '';
        ?>
            <a href="<?php echo $page; ?>" class="<?php echo $active_class; ?>">
                <span><?php echo $item['icon'] ? $item['icon'] . ' ' : ''; ?><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
        
        <a href="logout.php" class="logout">
            <span>🚪 Déconnexion</span>
        </a>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <div class="container">
