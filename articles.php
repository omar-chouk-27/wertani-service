<?php
require_once 'includes/config.php';
requireAuth();

$page_title = 'Suivis Article / Services - Wertani Service';

// Determine which tab/type we're viewing
$type = $_GET['type'] ?? 'ws'; // ws, composant, service
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';
    $post_type = $_POST['type'] ?? 'ws';
    
    // ====================================================================
    // ARTICLE WS ACTIONS
    // ====================================================================
    if ($post_type === 'ws') {
        if ($post_action === 'add') {
            $name = $_POST['name'] ?? '';
            $reference = $_POST['reference'] ?? '';
            $description = $_POST['description'] ?? '';
            $quantity = $_POST['quantity'] ?? 0;
            $prix_vente = $_POST['prix_vente'] ?? 0;
            
            if (!empty($name)) {
                $sql = "INSERT INTO article_ws (Reference, Name, Description, Quantity, PrixVente) 
                        VALUES (?, ?, ?, ?, ?)";
                if (executeQuery($conn, $sql, [$reference, $name, $description, $quantity, $prix_vente])) {
                    $article_id = $conn->lastInsertId();
                    
                    // Handle photo upload
                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                        $file = $_FILES['photo'];
                        $file_name = $file['name'];
                        $file_tmp = $file['tmp_name'];
                        $file_size = $file['size'];
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        
                        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (in_array($file_ext, $allowed_exts) && $file_size < 5000000) {
                            if (!is_dir('uploads/article_photos')) {
                                mkdir('uploads/article_photos', 0777, true);
                            }
                            
                            $new_file_name = uniqid() . '_' . $file_name;
                            $upload_path = 'uploads/article_photos/' . $new_file_name;
                            
                            if (move_uploaded_file($file_tmp, $upload_path)) {
                                try {
                                    $sql_photo = "INSERT INTO article_ws_photos (ArticleWS_Id, PhotoPath, PhotoName, IsMain) 
                                                  VALUES (?, ?, ?, 1)";
                                    executeQuery($conn, $sql_photo, [$article_id, $upload_path, $file_name]);
                                } catch (Exception $e) {}
                            }
                        }
                    }
                    
                    setFlash('success', 'Article WS ajouté avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de l\'ajout.');
                }
            } else {
                setFlash('danger', 'Le nom est requis.');
            }
            header("Location: articles.php?type=ws");
            exit();
        }
        
        if ($post_action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $reference = $_POST['reference'] ?? '';
            $description = $_POST['description'] ?? '';
            $quantity = $_POST['quantity'] ?? 0;
            $prix_vente = $_POST['prix_vente'] ?? 0;
            
            if ($id && !empty($name)) {
                $sql = "UPDATE article_ws SET Reference = ?, Name = ?, Description = ?, Quantity = ?, PrixVente = ? 
                        WHERE Id = ?";
                if (executeQuery($conn, $sql, [$reference, $name, $description, $quantity, $prix_vente, $id])) {
                    
                    // Handle photo upload
                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                        $file = $_FILES['photo'];
                        $file_name = $file['name'];
                        $file_tmp = $file['tmp_name'];
                        $file_size = $file['size'];
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        
                        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (in_array($file_ext, $allowed_exts) && $file_size < 5000000) {
                            if (!is_dir('uploads/article_photos')) {
                                mkdir('uploads/article_photos', 0777, true);
                            }
                            
                            $new_file_name = uniqid() . '_' . $file_name;
                            $upload_path = 'uploads/article_photos/' . $new_file_name;
                            
                            if (move_uploaded_file($file_tmp, $upload_path)) {
                                try {
                                    // Unset current main photo
                                    executeQuery($conn, "UPDATE article_ws_photos SET IsMain = 0 WHERE ArticleWS_Id = ?", [$id]);
                                    
                                    // Add new main photo
                                    $sql_photo = "INSERT INTO article_ws_photos (ArticleWS_Id, PhotoPath, PhotoName, IsMain) 
                                                  VALUES (?, ?, ?, 1)";
                                    executeQuery($conn, $sql_photo, [$id, $upload_path, $file_name]);
                                } catch (Exception $e) {}
                            }
                        }
                    }
                    
                    setFlash('success', 'Article WS modifié avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la modification.');
                }
            } else {
                setFlash('danger', 'ID ou nom invalide.');
            }
            header("Location: articles.php?type=ws&action=edit&id=" . $id);
            exit();
        }
        
        if ($post_action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if ($id) {
                if (executeQuery($conn, "DELETE FROM article_ws WHERE Id = ?", [$id])) {
                    setFlash('success', 'Article WS supprimé avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la suppression.');
                }
            }
            header("Location: articles.php?type=ws");
            exit();
        }
        
        // Add sous-article
        if ($post_action === 'add_sous') {
            $article_id = intval($_POST['article_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $reference = trim($_POST['reference'] ?? '');
            $quantity = intval($_POST['quantity'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $matiere = trim($_POST['matiere'] ?? '');
            
            if ($article_id > 0 && !empty($name)) {
                // Handle file upload
                $file_path = null;
                $file_name = null;
                
                if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                    $file = $_FILES['file'];
                    $file_name = $file['name'];
                    $file_tmp = $file['tmp_name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Create upload directory if it doesn't exist
                    if (!is_dir('uploads/sous_articles')) {
                        mkdir('uploads/sous_articles', 0777, true);
                    }
                    
                    $new_file_name = uniqid() . '_' . $file_name;
                    $upload_path = 'uploads/sous_articles/' . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $file_path = $upload_path;
                    }
                }
                
                $sql = "INSERT INTO sous_article_ws (ArticleWS_Id, Name, Reference, Quantity, Description, Matiere, FilePath, FileName) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        
                try {
                    if (executeQuery($conn, $sql, [$article_id, $name, $reference, $quantity, $description, $matiere, $file_path, $file_name])) {
                        setFlash('success', 'Sous-article ajouté avec succès!');
                    } else {
                        setFlash('danger', 'Erreur lors de l\'ajout du sous-article.');
                    }
                } catch (Exception $e) {
                    setFlash('danger', 'Erreur: ' . $e->getMessage());
                }
            } else {
                // Better error message
                if ($article_id <= 0) {
                    setFlash('danger', 'ID de l\'article invalide (ID reçu: ' . $article_id . ')');
                } elseif (empty($name)) {
                    setFlash('danger', 'Le nom du sous-article est requis.');
                }
            }
            header("Location: articles.php?type=ws&action=edit&id=" . $article_id);
            exit();
        }
        
        // Delete sous-article
        if ($post_action === 'delete_sous') {
            $sous_id = $_POST['sous_id'] ?? 0;
            $article_id = $_POST['article_id'] ?? 0;
            
            if ($sous_id) {
                // Get file path and delete file
                $sous = getRow($conn, "SELECT FilePath FROM sous_article_ws WHERE Id = ?", [$sous_id]);
                if ($sous && $sous['FilePath'] && file_exists($sous['FilePath'])) {
                    unlink($sous['FilePath']);
                }
                
                executeQuery($conn, "DELETE FROM sous_article_ws WHERE Id = ?", [$sous_id]);
                setFlash('success', 'Sous-article supprimé avec succès!');
            }
            header("Location: articles.php?type=ws&action=edit&id=" . $article_id);
            exit();
        }
    }
    
    // ====================================================================
    // COMPOSANT / MATIERE ACTIONS
    // ====================================================================
    if ($post_type === 'composant') {
        if ($post_action === 'add') {
            $name = $_POST['name'] ?? '';
            $reference = $_POST['reference'] ?? '';
            $quantity = $_POST['quantity'] ?? 0;
            $description = $_POST['description'] ?? '';
            $fournisseur_id = $_POST['fournisseur_id'] ?? null;
            $montant_ttc = $_POST['montant_ttc'] ?? 0;
            $categorie = $_POST['categorie'] ?? '';
            
            if (!empty($name)) {
                $sql = "INSERT INTO composant_matiere (Name, Reference, Quantity, Description, Fournisseur_Id, MontantTTC, Categorie) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                if (executeQuery($conn, $sql, [$name, $reference, $quantity, $description, $fournisseur_id, $montant_ttc, $categorie])) {
                    setFlash('success', 'Composant/Matière ajouté avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de l\'ajout.');
                }
            } else {
                setFlash('danger', 'Le nom est requis.');
            }
            header("Location: articles.php?type=composant");
            exit();
        }
        
        if ($post_action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $reference = $_POST['reference'] ?? '';
            $quantity = $_POST['quantity'] ?? 0;
            $description = $_POST['description'] ?? '';
            $fournisseur_id = $_POST['fournisseur_id'] ?? null;
            $montant_ttc = $_POST['montant_ttc'] ?? 0;
            $categorie = $_POST['categorie'] ?? '';
            
            if ($id && !empty($name)) {
                $sql = "UPDATE composant_matiere SET Name = ?, Reference = ?, Quantity = ?, Description = ?, 
                        Fournisseur_Id = ?, MontantTTC = ?, Categorie = ? WHERE Id = ?";
                if (executeQuery($conn, $sql, [$name, $reference, $quantity, $description, $fournisseur_id, $montant_ttc, $categorie, $id])) {
                    setFlash('success', 'Composant/Matière modifié avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la modification.');
                }
            } else {
                setFlash('danger', 'ID ou nom invalide.');
            }
            header("Location: articles.php?type=composant&action=edit&id=" . $id);
            exit();
        }
        
        if ($post_action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if ($id) {
                if (executeQuery($conn, "DELETE FROM composant_matiere WHERE Id = ?", [$id])) {
                    setFlash('success', 'Composant/Matière supprimé avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la suppression.');
                }
            }
            header("Location: articles.php?type=composant");
            exit();
        }
    }
    
    // ====================================================================
    // SERVICES ACTIONS
    // ====================================================================
    if ($post_type === 'service') {
        if ($post_action === 'add') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $prix_service = $_POST['prix_service'] ?? 0;
            
            if (!empty($name)) {
                $sql = "INSERT INTO services (Name, Description, PrixService) VALUES (?, ?, ?)";
                if (executeQuery($conn, $sql, [$name, $description, $prix_service])) {
                    $service_id = $conn->lastInsertId();
                    
                    // Add articles to service if any
                    $article_types = $_POST['article_types'] ?? [];
                    $article_ids = $_POST['article_ids'] ?? [];
                    $article_quantities = $_POST['article_quantities'] ?? [];
                    
                    foreach ($article_types as $index => $article_type) {
                        $article_id = $article_ids[$index] ?? 0;
                        $quantity = $article_quantities[$index] ?? 1;
                        
                        if ($article_id > 0) {
                            $sql_art = "INSERT INTO service_articles (Service_Id, ArticleType, Article_Id, Quantity) 
                                       VALUES (?, ?, ?, ?)";
                            executeQuery($conn, $sql_art, [$service_id, $article_type, $article_id, $quantity]);
                        }
                    }
                    
                    setFlash('success', 'Service ajouté avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de l\'ajout.');
                }
            } else {
                setFlash('danger', 'Le nom est requis.');
            }
            header("Location: articles.php?type=service");
            exit();
        }
        
        if ($post_action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $prix_service = $_POST['prix_service'] ?? 0;
            
            if ($id && !empty($name)) {
                $sql = "UPDATE services SET Name = ?, Description = ?, PrixService = ? WHERE Id = ?";
                if (executeQuery($conn, $sql, [$name, $description, $prix_service, $id])) {
                    // Delete old article associations
                    executeQuery($conn, "DELETE FROM service_articles WHERE Service_Id = ?", [$id]);
                    
                    // Add new articles
                    $article_types = $_POST['article_types'] ?? [];
                    $article_ids = $_POST['article_ids'] ?? [];
                    $article_quantities = $_POST['article_quantities'] ?? [];
                    
                    foreach ($article_types as $index => $article_type) {
                        $article_id = $article_ids[$index] ?? 0;
                        $quantity = $article_quantities[$index] ?? 1;
                        
                        if ($article_id > 0) {
                            $sql_art = "INSERT INTO service_articles (Service_Id, ArticleType, Article_Id, Quantity) 
                                       VALUES (?, ?, ?, ?)";
                            executeQuery($conn, $sql_art, [$id, $article_type, $article_id, $quantity]);
                        }
                    }
                    
                    setFlash('success', 'Service modifié avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la modification.');
                }
            } else {
                setFlash('danger', 'ID ou nom invalide.');
            }
            header("Location: articles.php?type=service&action=edit&id=" . $id);
            exit();
        }
        
        if ($post_action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if ($id) {
                if (executeQuery($conn, "DELETE FROM services WHERE Id = ?", [$id])) {
                    setFlash('success', 'Service supprimé avec succès!');
                } else {
                    setFlash('danger', 'Erreur lors de la suppression.');
                }
            }
            header("Location: articles.php?type=service");
            exit();
        }
    }
}

// Get data based on type and action
$item = null;
$sous_articles = [];
$service_articles = [];

if ($action === 'edit' && $id) {
    if ($type === 'ws') {
        $item = getRow($conn, "SELECT * FROM article_ws WHERE Id = ?", [$id]);
        if (!$item) {
            setFlash('danger', 'Article WS non trouvé (ID: ' . $id . ')');
            header("Location: articles.php?type=ws");
            exit();
        }
        $sous_articles = getData($conn, "SELECT * FROM sous_article_ws WHERE ArticleWS_Id = ? ORDER BY CreationDate DESC", [$id]);
    } elseif ($type === 'composant') {
        $item = getRow($conn, "SELECT * FROM composant_matiere WHERE Id = ?", [$id]);
        if (!$item) {
            setFlash('danger', 'Composant non trouvé (ID: ' . $id . ')');
            header("Location: articles.php?type=composant");
            exit();
        }
    } elseif ($type === 'service') {
        $item = getRow($conn, "SELECT * FROM services WHERE Id = ?", [$id]);
        if (!$item) {
            setFlash('danger', 'Service non trouvé (ID: ' . $id . ')');
            header("Location: articles.php?type=service");
            exit();
        }
        $service_articles = getData($conn, "SELECT * FROM service_articles WHERE Service_Id = ?", [$id]);
    }
}

// Get all items for list view
$articles_ws = getData($conn, "
    SELECT *, 
           (SELECT COUNT(*) FROM sous_article_ws WHERE ArticleWS_Id = article_ws.Id) as sous_count
    FROM article_ws 
    ORDER BY CreationDate DESC
");

$composants = getData($conn, "
    SELECT c.*, s.name as fournisseur_name
    FROM composant_matiere c
    LEFT JOIN supplier s ON c.Fournisseur_Id = s.id
    ORDER BY c.CreationDate DESC
");

$services = getData($conn, "
    SELECT s.*,
           (SELECT COUNT(*) FROM service_articles WHERE Service_Id = s.Id) as article_count
    FROM services s
    ORDER BY s.CreationDate DESC
");

// Get suppliers for dropdown
$suppliers = getData($conn, "SELECT id, name FROM supplier ORDER BY name ASC");

// Get article lists for service form
$articles_ws_list = getData($conn, "SELECT Id, Name FROM article_ws ORDER BY Name ASC");
$composants_list = getData($conn, "SELECT Id, Name FROM composant_matiere ORDER BY Name ASC");

require 'includes/header.php';
?>

<?php displayFlash(); ?>

<!-- Tab Navigation -->
<div class="card">
    <div class="card-header" style="border-bottom: 2px solid var(--border);">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="?type=ws" class="btn <?php echo $type === 'ws' ? 'btn-primary' : 'btn-secondary'; ?>">
                🔧 Article WS
            </a>
            <a href="?type=composant" class="btn <?php echo $type === 'composant' ? 'btn-primary' : 'btn-secondary'; ?>">
                📦 Composant / Matière
            </a>
            <a href="?type=service" class="btn <?php echo $type === 'service' ? 'btn-primary' : 'btn-secondary'; ?>">
                ⚙️ Services
            </a>
        </div>
    </div>
</div>

<?php
// Include the appropriate view based on type
$view_file = "articles_views/{$type}_{$action}.php";
if (file_exists($view_file)) {
    include $view_file;
} else {
    // Default: show list
    if ($type === 'ws') {
        include 'articles_views/ws_list.php';
    } elseif ($type === 'composant') {
        include 'articles_views/composant_list.php';
    } elseif ($type === 'service') {
        include 'articles_views/service_list.php';
    }
}
?>

<?php require 'includes/footer.php'; ?>