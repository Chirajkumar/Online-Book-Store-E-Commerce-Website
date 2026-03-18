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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query based on filters
$where = [];
$params = [];

if(isset($_GET['category']) && !empty($_GET['category'])) {
    $where[] = "category_id = ?";
    $params[] = $_GET['category'];
}

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "(title LIKE ? OR author LIKE ?)";
    $params[] = "%{$_GET['search']}%";
    $params[] = "%{$_GET['search']}%";
}

if(isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where[] = "price >= ?";
    $params[] = $_GET['min_price'];
}

if(isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where[] = "price <= ?";
    $params[] = $_GET['max_price'];
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Get total count for pagination
$totalProducts = 0;
$totalPages = 1;
$products = [];
$categories = [];
if ($pdo) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products $whereClause");
    $countStmt->execute($params);
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $limit);

    // Get products
    $orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'id DESC';
    $query = "SELECT * FROM products $whereClause ORDER BY $orderBy LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Get categories for filter
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
}
?>

<div class="container">
    <h1 class="section-title">Browse Books</h1>
    
    <!-- Filters Section -->
    <div class="filters-section">
        <form class="filters-form" method="GET">
            <div class="filter-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                            <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Price Range</label>
                <select name="price_range">
                    <option value="">All Prices</option>
                    <option value="0-10" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '0-10') ? 'selected' : ''; ?>>Under $10</option>
                    <option value="10-20" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '10-20') ? 'selected' : ''; ?>>$10 - $20</option>
                    <option value="20-30" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '20-30') ? 'selected' : ''; ?>>$20 - $30</option>
                    <option value="30-50" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '30-50') ? 'selected' : ''; ?>>$30 - $50</option>
                    <option value="50+" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '50+') ? 'selected' : ''; ?>>$50+</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Sort By</label>
                <select name="sort">
                    <option value="id DESC" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'id DESC') ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price ASC" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price ASC') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price DESC" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price DESC') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="title ASC" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'title ASC') ? 'selected' : ''; ?>>Title: A to Z</option>
                </select>
            </div>
            
            <?php if(isset($_GET['search'])): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
            <?php endif; ?>
            
            <div class="filter-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
    
    <!-- Products Grid -->
    <?php if(empty($products)): ?>
        <div style="text-align: center; padding: 50px;">
            <i class="fas fa-book" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
            <h3>No books found</h3>
            <p>Try adjusting your filters or search terms.</p>
        </div>
    <?php else: ?>
        <div class="book-grid">
            <?php foreach($products as $book): ?>
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
                    <div class="book-price">$<?php echo number_format($book['price'], 2); ?></div>
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
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <?php if($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if($page < $totalPages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>