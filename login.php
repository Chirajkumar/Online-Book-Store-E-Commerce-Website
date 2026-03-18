<?php
session_start();

$host = 'localhost';
$dbname = 'bookstore_db';
$username = 'root';
$password = '';

$pdo = false;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $login_error = "Database error. Please try later.";
}

include 'includes/header.php';

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect to admin if admin, otherwise to home
            if($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                header("Location: $redirect");
            }
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Service temporarily unavailable.";
    }
}
?>

<div class="container" style="max-width: 500px; margin: 50px auto;">
    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 30px;">Login to Your Account</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 16px;">
                Login
            </button>
            
            <div style="text-align: center; margin-top: 20px;">
                <p>Don't have an account? <a href="register.php" style="color: var(--secondary-color);">Register here</a></p>
                <p><a href="forgot-password.php" style="color: #666;">Forgot Password?</a></p>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>