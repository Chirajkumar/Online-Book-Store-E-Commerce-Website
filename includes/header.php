<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Book Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    BookStore
                </a>
                
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Books</a></li>
                    <li class="dropdown">
                        <a href="#">Categories <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <?php
                            // Categories dropdown - safe fail
                            if (isset($pdo)) {
                                $cats = $pdo->query("SELECT * FROM categories LIMIT 5")->fetchAll();
                                foreach($cats as $cat) {
                                    echo '<li><a href="products.php?category='.$cat['id'].'">'.$cat['category_name'].'</a></li>';
                                }
                            }
                            ?>
                            <li><a href="products.php">All Categories</a></li>
                        </ul>
                    </li>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                        <li><a href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
                        <li class="dropdown">
                            <a href="#"><i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?> <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="user-dashboard.php">Dashboard</a></li>
                                <li><a href="profile.php">Profile</a></li>
                                <li><a href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php" class="btn-register">Register</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>
    <main>