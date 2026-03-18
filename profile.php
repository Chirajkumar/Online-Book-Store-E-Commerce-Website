<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$host = 'localhost';
$dbname = 'bookstore_db';
$username = 'root';
$password = '';

$pdo = false;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Service unavailable.");
}

include 'includes/header.php';

// Fetch user profile
$user = false;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (Exception $e) {
        // Silent
    }
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $user_id]);
        
        $_SESSION['user_name'] = $name;
        $_SESSION['success'] = "Profile updated successfully!";
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (Exception $e) {
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
    }
}
?>

<div class="container">
    <h1 class="section-title">My Profile</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" style="background: #27ae60; color: white; padding: 12px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" style="background: #e74c3c; color: white; padding: 12px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-container">
        <div class="profile-info">
            <div class="profile-card">
                <div class="profile-avatar">
                    <i class="fas fa-user" style="font-size: 64px; color: #95a5a6;"></i>
                </div>
                <h2><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                <div class="profile-stats">
                    <div class="stat">
                        <strong>0</strong>
                        <span>Total Orders</span>
                    </div>
                    <div class="stat">
                        <strong>$0</strong>
                        <span>Total Spent</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="profile-form">
            <h3>Edit Profile</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="4"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">
                    Update Profile
                </button>
            </form>
            
            <div style="margin-top: 30px;">
                <a href="orders.php" class="btn btn-outline" style="display: inline-block; padding: 12px 24px; margin-right: 10px;">
                    My Orders
                </a>
                <a href="logout.php" class="btn btn-secondary" style="display: inline-block; padding: 12px 24px; background: #95a5a6; color: white; text-decoration: none; border-radius: 5px;">
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 40px;
    margin-top: 30px;
}
.profile-card {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    text-align: center;
}
.profile-avatar {
    margin-bottom: 20px;
}
.profile-email {
    color: #7f8c8d;
    font-size: 16px;
    margin-bottom: 30px;
}
.profile-stats {
    display: flex;
    justify-content: space-around;
    margin-top: 30px;
}
.stat {
    text-align: center;
}
.stat strong {
    display: block;
    font-size: 28px;
    color: #3498db;
}
.profile-form {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
}
.form-group input, .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}
.form-group input:focus, .form-group textarea:focus {
    outline: none;
    border-color: #3498db;
}
.btn {
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-primary {
    background: #3498db;
    color: white;
}
.btn-primary:hover {
    background: #2980b9;
}
.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}
.btn-outline:hover {
    background: #3498db;
    color: white;
}
.btn-secondary {
    background: #95a5a6;
    color: white;
}
.btn-secondary:hover {
    background: #7f8c8d;
}
@media (max-width: 768px) {
    .profile-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

