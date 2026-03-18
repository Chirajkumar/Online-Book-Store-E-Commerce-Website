<?php
session_start();

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$totalUsers = 0;
$totalProducts = 0;
$totalOrders = 0;
$totalRevenue = 0;

// Safe DB queries (no error if no DB/tables)
$host = 'localhost';
$dbname = 'bookstore_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE order_status='Delivered'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    // DB not ready - show 0s
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BookStore</title>

    <link rel="stylesheet" href="assets/css/simple-admin.css">
</head>
<body>
    <div class="sidebar">
        <h2>BookStore Admin</h2>
        <ul>
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-book"></i> Products</a></li>
            <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card users">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <p><?php echo $totalUsers; ?></p>
                </div>
            </div>
            
            <div class="stat-card products">
                <i class="fas fa-book stat-icon"></i>
                <div class="stat-info">
                    <h3>Total Books</h3>
                    <p><?php echo $totalProducts; ?></p>
                </div>
            </div>
            
            <div class="stat-card orders">
                <i class="fas fa-shopping-cart stat-icon"></i>
                <div class="stat-info">
                    <h3>Total Orders</h3>
                    <p><?php echo $totalOrders; ?></p>
                </div>
            </div>
            
            <div class="stat-card revenue">
                <i class="fas fa-dollar-sign stat-icon"></i>
                <div class="stat-info">
                    <h3>Total Revenue</h3>
                    <p>₹<?php echo number_format($totalRevenue, 2); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

