<?php
/**
 * Wertani Service - Configuration File
 * Database connection and helper functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wertani');

// Create database connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Une erreur est survenue lors de la connexion à la base de données.");
}

/**
 * Execute a query and return all results
 * @param PDO $conn Database connection
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Query results
 */
function getData($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Execute a query and return a single row
 * @param PDO $conn Database connection
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array|false Single row or false
 */
function getRow($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Execute a query and return count
 * @param PDO $conn Database connection
 * @param string $sql SQL query with COUNT(*)
 * @param array $params Parameters for prepared statement
 * @return int Count result
 */
function getCount($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return isset($result['total']) ? (int)$result['total'] : 0;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Execute an INSERT/UPDATE/DELETE query
 * @param PDO $conn Database connection
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return bool Success status
 */
function executeQuery($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Sanitize output for display
 * @param string $str String to sanitize
 * @return string Sanitized string
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user']);
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency
 */
function formatCurrency($amount) {
    return number_format($amount, 3, ',', ' ') . ' DT';
}

/**
 * Format date
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Set flash message
 * @param string $type Type of message (success, error, warning, info)
 * @param string $message Message content
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message array or null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message
 */
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        echo '<div class="alert alert-' . e($flash['type']) . '">';
        echo $flash['message']; // Allow HTML in flash messages
        echo '</div>';
    }
}

/**
 * Alias for displayFlash() for backward compatibility
 */
function displayFlashMessage() {
    displayFlash();
}
