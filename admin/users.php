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
    $db_error = "Database connection failed. Run setup.php first.";
}

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete user's orders
if(isset($_GET['delete_orders']) && $pdo) {
    $user_id = (int)$_GET['delete_orders'];
    try {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = "All orders for this user deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: users.php");
    exit();
}

// Fetch all customers
$users = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY id DESC");
        $users = $stmt->fetchAll();
    } catch (Exception $e) {
        $db_error = "Failed to fetch users.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - BookStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            background: #f4f6f9;
        }
        
        .sidebar {
            width: 280px;
            background: #2C3E50;
            color: white;
            padding: 20px;
        }
        
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #34495e;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar ul li {
            margin-bottom: 10px;
        }
        
        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: #3498DB;
            color: white;
        }
        
        .sidebar ul li a i {
            margin-right: 10px;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #2C3E50;
        }
        
        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #2C3E50;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #3498DB;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }
        
        .badge {
            background: #27ae60;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        
        .view-orders {
            color: #3498DB;
            text-decoration: none;
            font-size: 13px;
        }
        
        .view-orders:hover {
            text-decoration: underline;
        }

        .delete-orders-btn {
            display: inline-block;
            padding: 6px 12px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .delete-orders-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>BookStore Admin</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-book"></i> Products</a></li>
            <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success" style="background: #27ae60; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error" style="background: #e74c3c; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($db_error)): ?>
            <div class="error" style="background: #f39c12; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><?php echo $db_error; ?> <a href="setup.php" style="color: #fff; font-weight: bold;">Run Setup</a></div>
        <?php endif; ?>
        
        <div class="header">
            <h1>Manage Users</h1>
        </div>
        
        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Registered</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): 
                        // Get user's order count
                        if ($pdo) {
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                            $stmt->execute([$user['id']]);
                            $orderCount = $stmt->fetchColumn();
                        } else {
                            $orderCount = 0;
                        }
                    ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($user['email']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(substr($user['address'] ?: 'N/A', 0, 50)) . '...'; ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <span class="badge"><?php echo $orderCount; ?> orders</span>
                            <?php if($orderCount > 0): ?>
                                <br>
                                <a href="orders.php?user=<?php echo $user['id']; ?>" class="view-orders">View Orders</a>
                                <br>
                                <a href="?delete_orders=<?php echo $user['id']; ?>" class="delete-orders-btn" onclick="return confirm('Delete ALL <?php echo $orderCount; ?> orders for this user? Cannot be undone.');">
                                    <i class="fas fa-trash"></i> Delete Orders
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>