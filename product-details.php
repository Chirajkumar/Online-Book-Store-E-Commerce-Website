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
    // Silent fail
}

include 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$product = false;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch (Exception $e) {
        // Silent
    }
}

if(!$product) {
    header("Location: products.php");
    exit();
}

// Get related products
$relatedProducts = [];
if ($pdo) {
    try {
        $related = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
        $related->execute([$product['category_id'], $id]);
        $relatedProducts = $related->fetchAll();
    } catch (Exception $e) {
        // Silent
    }
}
?>

<div class="container">
    <div class="product-details">
        <div class="product-gallery">
            <?php if($product['image']): ?>
                <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>">
            <?php else: ?>
                <img src="assets/images/no-cover.jpg" alt="No cover available">
            <?php endif; ?>
        </div>
        
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['title']); ?></h1>
            <p class="book-author">by <?php echo htmlspecialchars($product['author']); ?></p>
            
            <div class="product-price">Rs<?php echo number_format($product['price'], 2); ?></div>
            
            <span class="stock-info <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                <?php echo $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' available)' : 'Out of Stock'; ?>
            </span>
            
            <div class="product-meta">
                <div class="meta-item">
                    <span class="meta-label">Category:</span>
                    <span class="meta-value">
                        <a href="products.php?category=<?php echo $product['category_id']; ?>">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </a>
                    </span>
                </div>
            </div>
            
            <?php if($product['stock'] > 0): ?>
                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1">
                </div>
                
                <div class="book-actions">
                    <button onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('quantity').value)" 
                            class="btn btn-primary" style="padding: 15px 40px; font-size: 16px;">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="product-description" style="margin-top: 30px;">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if(!empty($relatedProducts)): ?>
        <h2 class="section-title">You May Also Like</h2>
        <div class="book-grid">
            <?php foreach($relatedProducts as $related): ?>
            <div class="book-card">
                <div class="book-image">
                    <?php if($related['image']): ?>
                        <img src="assets/images/products/<?php echo $related['image']; ?>" alt="<?php echo $related['title']; ?>">
                    <?php else: ?>
                        <img src="assets/images/no-cover.jpg" alt="No cover available">
                    <?php endif; ?>
                </div>
                <div class="book-info">
                    <h3 class="book-title"><?php echo htmlspecialchars(substr($related['title'], 0, 30)) . '...'; ?></h3>
                    <p class="book-author">by <?php echo htmlspecialchars($related['author']); ?></p>
                    <div class="book-price">$<?php echo number_format($related['price'], 2); ?></div>
                    <a href="product-details.php?id=<?php echo $related['id']; ?>" class="btn btn-outline">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>