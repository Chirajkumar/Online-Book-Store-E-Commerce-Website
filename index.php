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
    // Silent fail for frontend
}

include 'includes/header.php';

// Fetch featured books
$featured = [];
if ($pdo) {
    $featured = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 8")->fetchAll();
}

// Fetch categories
$categories = [];
if ($pdo) {
    $categories = $pdo->query("SELECT * FROM categories LIMIT 6")->fetchAll();
}
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to Online Book Store</h1>
        <p>Discover thousands of books across all genres. Start your reading journey today!</p>
        
        <form class="search-box" action="products.php" method="GET">
            <input type="text" name="search" placeholder="Search for books by title, author, or category...">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</section>

<div class="container">
    <!-- Categories Section -->
    <h2 class="section-title">Browse Categories</h2>
    <div class="categories-grid">
        <?php foreach($categories as $category): 
            $count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $count->execute([$category['id']]);
            $bookCount = $count->fetchColumn();
        ?>
        <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
            <i class="fas fa-book"></i>
            <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
            <p><?php echo $bookCount; ?> Books</p>
        </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Featured Books -->
    <h2 class="section-title">Featured Books</h2>
    <div class="book-grid">
        <?php foreach($featured as $book): ?>
        <div class="book-card">
            <div class="book-image">
                <?php if($book['image']): ?>
                    <img src="assets/images/products/<?php echo $book['image']; ?>" alt="<?php echo $book['title']; ?>">
                <?php else: ?>
                    <img src="assets/images/no-cover.jpg" alt="No cover available">
                <?php endif; ?>
            </div>
            <div class="book-info">
                <h3 class="book-title"><?php echo htmlspecialchars(substr($book['title'], 0, 30)) . '...'; ?></h3>
                <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                <div class="book-price">Rs<?php echo number_format($book['price'], 2); ?></div>
                <div class="book-actions">
                    <a href="product-details.php?id=<?php echo $book['id']; ?>" class="btn btn-outline">Details</a>
                    <?php if($book['stock'] > 0): ?>
                        <button onclick="addToCart(<?php echo $book['id']; ?>)" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> Add
                        </button>
                    <?php else: ?>
                        <button class="btn btn-danger" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Call to Action -->
    <div style="text-align: center; margin: 50px 0;">
        <a href="products.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 18px;">
            View All Books <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>