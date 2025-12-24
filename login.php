<?php
/**
 * Login Page - Compatible avec plain text, MD5 et password_hash()
 */

require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Try to find user in different possible table names
        $user = null;
        
        // Try 'users' table first
        try {
            $user = getRow($conn, "SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
        } catch (Exception $e) {
            // Table might not exist
        }
        
        // If not found, try 'User' table
        if (!$user) {
            try {
                $user = getRow($conn, "SELECT * FROM User WHERE Username = ?", [$username]);
            } catch (Exception $e) {
                // Table might not exist
            }
        }
        
        if ($user) {
            $stored_password = $user['password'] ?? $user['Password'] ?? '';
            $is_valid = false;
            
            // Check password type and verify
            if (strpos($stored_password, '$2y$') === 0 || strpos($stored_password, '$2a$') === 0) {
                // bcrypt hash
                $is_valid = password_verify($password, $stored_password);
            } elseif (strlen($stored_password) === 32 && ctype_xdigit($stored_password)) {
                // MD5 hash (32 hex characters)
                $password_md5 = md5($password);
                $is_valid = ($password_md5 === $stored_password);
                
                // Upgrade to bcrypt
                if ($is_valid) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        if (isset($user['id'])) {
                            executeQuery($conn, "UPDATE users SET password = ? WHERE id = ?", [$new_hash, $user['id']]);
                        } elseif (isset($user['Id'])) {
                            executeQuery($conn, "UPDATE User SET Password = ? WHERE Id = ?", [$new_hash, $user['Id']]);
                        }
                    } catch (Exception $e) {
                        // Upgrade failed, but login still works
                    }
                }
            } else {
                // Plain text password
                $is_valid = ($password === $stored_password);
                
                // Upgrade to bcrypt
                if ($is_valid) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        if (isset($user['id'])) {
                            executeQuery($conn, "UPDATE users SET password = ? WHERE id = ?", [$new_hash, $user['id']]);
                        } elseif (isset($user['Id'])) {
                            executeQuery($conn, "UPDATE User SET Password = ? WHERE Id = ?", [$new_hash, $user['Id']]);
                        }
                    } catch (Exception $e) {
                        // Upgrade failed, but login still works
                    }
                }
            }
            
            if ($is_valid) {
                // Successful login - set session
                $_SESSION['user'] = $user['username'] ?? $user['Username'] ?? '';
                $_SESSION['user_id'] = $user['id'] ?? $user['Id'] ?? 0;
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
            }
        } else {
            $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
        }
    }
}

$page_title = 'Connexion - Wertani Service';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 1rem;
            overflow: hidden;
            background: linear-gradient(135deg, #e8af2c 0%, #f0c14b 100%);
        }
        
        /* HUGE BACKGROUND LOGO - FILLS ENTIRE SCREEN */
        .login-container::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assets/images/logo.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
            opacity: 0.1;
            z-index: 1;
            pointer-events: none;
        }
        
        /* Dark overlay for better contrast */
        .login-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.05);
            z-index: 2;
            pointer-events: none;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease;
            position: relative;
            z-index: 10;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }
        
        .login-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 0.95rem;
        }
        
        .login-form {
            margin-top: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e8af2c;
        }
        
        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #e8af2c 0%, #f0c14b 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(232, 175, 44, 0.4);
        }
        
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: #fee;
            color: #c00;
            border-left: 4px solid #c00;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f0f0f0;
            color: #666;
            font-size: 0.85rem;
        }

        .error-shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card <?php echo $error ? 'error-shake' : ''; ?>">
            <div class="login-header">
                <img src="assets/images/logo.png" alt="Logo Wertani Service" class="login-logo" onerror="this.style.display='none'">
                <h1 class="login-title">Wertani Service</h1>
                <p class="login-subtitle">Système de Gestion</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="Entrez votre nom d'utilisateur"
                        value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>"
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Entrez votre mot de passe"
                        required
                    >
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Se connecter
                </button>
            </form>
            
            <div class="form-footer">
                <p><strong>Développé par Omar Chouk</strong></p>
                <p><a href="mailto:contact@omarchouk.tn">contact@omarchouk.tn</a></p>
            </div>
        </div>
    </div>
</body>
</html>
