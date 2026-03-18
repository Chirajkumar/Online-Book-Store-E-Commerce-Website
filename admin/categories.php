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
    $db_error = "Database connection failed: " . $e->getMessage() . ". Run setup.php first or check MySQL.";
}

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle add category
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add']) && $pdo) {
    $category_name = trim($_POST['category_name']);
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        if($stmt->execute([$category_name])) {
            $_SESSION['success'] = "Category added successfully!";
            header("Location: categories.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to add category: " . $e->getMessage();
    }
}

// Handle delete category
if(isset($_GET['delete']) && $pdo) {
    $id = (int)$_GET['delete'];
    try {
        // Check if category has products
        $prod_check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $prod_check->execute([$id]);
        if($prod_check->fetchColumn() > 0) {
            $_SESSION['error'] = "Cannot delete category with products!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Category deleted successfully!";
        }
        header("Location: categories.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete category: " . $e->getMessage();
    }
}

// Fetch all categories
$categories = [];
if ($pdo) {
    try {
        $categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
    } catch (Exception $e) {
        $db_error = "Failed to fetch categories: " . $e->getMessage();
    }
} else {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - BookStore</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #2C3E50;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }
        
        .add-category, .categories-list {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .add-category h2, .categories-list h2 {
            color: #2C3E50;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498DB;
        }
        
        .btn-add {
            background: #3498DB;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        
        .btn-add:hover {
            background: #2980B9;
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        .category-info h3 {
            color: #2C3E50;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .category-info p {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.3s;
        }
        
        .delete-btn:hover {
            background: #c0392b;
        }
        
        .success {
            background: #27ae60;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>BookStore Admin</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-book"></i> Products</a></li>
            <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Manage Categories</h1>
        </div>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error" style="background: #e74c3c; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($db_error)): ?>
            <div class="error" style="background: #f39c12; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $db_error; ?> <a href="setup.php" style="color: #fff; font-weight: bold;">Run Setup</a>
            </div>
        <?php endif; ?>
        
        <div class="content-grid">
            <div class="add-category">
                <h2>Add New Category</h2>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="category_name" placeholder="Enter category name" required>
                    </div>
                    <button type="submit" name="add" class="btn-add">Add Category</button>
                </form>
            </div>
            
            <div class="categories-list">
                <h2>All Categories</h2>
                <?php foreach($categories as $category): ?>
                <div class="category-item">
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                        <p>Created: <?php echo date('M d, Y', strtotime($category['created_at'])); ?></p>
                    </div>
                    <a href="?delete=<?php echo $category['id']; ?>" 
                       class="delete-btn"
                       onclick="return confirm('Are you sure? This will affect products in this category.')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>