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



// Update order status
if(isset($_POST['update_status']) && $pdo) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $_SESSION['success'] = "Order status updated!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
    }
    header("Location: orders.php");
    exit();
}

// Fetch all orders with user details
$orders = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT o.*, u.name as customer_name, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        $orders = $stmt->fetchAll();
    } catch (Exception $e) {
        $db_error = "Failed to fetch orders.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - BookStore</title>
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
        
        .orders-table {
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
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background: #f39c12;
            color: white;
        }
        
        .status-processing {
            background: #3498DB;
            color: white;
        }
        
        .status-shipped {
            background: #9b59b6;
            color: white;
        }
        
        .status-delivered {
            background: #27ae60;
            color: white;
        }
        
        .status-cancelled {
            background: #e74c3c;
            color: white;
        }
        
        .status-select {
            padding: 5px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .update-btn {
            background: #3498DB;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 5px;
        }
        
        .update-btn:hover {
            background: #2980B9;
        }
        
        .view-details {
            color: #3498DB;
            text-decoration: none;
            font-size: 13px;
        }
        
        .view-details:hover {
            text-decoration: underline;
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
            <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
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
            <h1>Manage Orders</h1>
        </div>
        
        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($order['email']); ?></small>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><strong>Rs<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                <?php echo $order['order_status']; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="Pending" <?php echo $order['order_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Processing" <?php echo $order['order_status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="Shipped" <?php echo $order['order_status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="Delivered" <?php echo $order['order_status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="Cancelled" <?php echo $order['order_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="update-btn">Update</button>
                            </form>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="view-details">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>