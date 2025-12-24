<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'add_car_ajax') {
    $marque = $_POST['marque'] ?? '';
    $modele = $_POST['modele'] ?? '';
    $annee = $_POST['annee'] ?? null;
    $type = $_POST['type'] ?? '4x4';
    
    if (empty($marque) || empty($modele)) {
        echo json_encode(['success' => false, 'message' => 'Marque et Modèle requis']);
        exit();
    }
    
    try {
        $sql = "INSERT INTO cars (Marque, Modele, Annee, Type) VALUES (?, ?, ?, ?)";
        if (executeQuery($conn, $sql, [$marque, $modele, $annee, $type])) {
            $car_id = $conn->lastInsertId();
            echo json_encode([
                'success' => true, 
                'car_id' => $car_id,
                'marque' => $marque,
                'modele' => $modele,
                'annee' => $annee,
                'type' => $type
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'insertion']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Action invalide']);
}
?>
