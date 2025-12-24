<?php
require_once 'includes/config.php';
requireAuth();

header('Content-Type: application/json');

$article_id = $_GET['id'] ?? 0;

$photos = [];

if ($article_id) {
    try {
        $photos = getData($conn, "
            SELECT 
                Id,
                PhotoPath,
                PhotoName,
                Description,
                IsMain
            FROM article_ws_photos
            WHERE ArticleWS_Id = ?
            ORDER BY IsMain DESC, CreationDate DESC
        ", [$article_id]);
        
        // Add article name to each photo
        $article = getRow($conn, "SELECT Name FROM article_ws WHERE Id = ?", [$article_id]);
        foreach ($photos as &$photo) {
            $photo['ArticleName'] = $article['Name'] ?? '';
        }
        
    } catch (Exception $e) {
        // Return empty array if table doesn't exist
    }
}

echo json_encode($photos);
