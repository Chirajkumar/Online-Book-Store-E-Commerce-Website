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
    $cart_error = "Cart service unavailable.";
}

include 'includes/header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle quantity update
if(isset($_POST['update_cart']) && $pdo) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    try {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
    } catch (Exception $e) {
        // Silent
    }
    header("Location: cart.php");
    exit();
}

// Handle remove item
if(isset($_GET['remove']) && $pdo) {
    $cart_id = (int)$_GET['remove'];
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
    } catch (Exception $e) {
        // Silent
    }
    header("Location: cart.php");
    exit();
}

// Get cart items
$cartItems = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, p.title, p.author, p.price, p.image, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cartItems = $stmt->fetchAll();
    } catch (Exception $e) {
        // Silent
    }
}

// Calculate totals
$subtotal = 0;
foreach($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal > 50 ? 0 : 5.99;
$total = $subtotal + $shipping;
?>

<div class="container">
    <h1 class="section-title">Shopping Cart</h1>
    
    <?php if(empty($cartItems)): ?>
        <div style="text-align: center; padding: 50px;">
            <i class="fas fa-shopping-cart" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any books to your cart yet.</p>
            <a href="products.php" class="btn btn-primary" style="margin-top: 20px;">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php foreach($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <?php if($item['image']): ?>
                            <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                        <?php else: ?>
                            <img src="assets/images/no-cover.jpg" alt="No cover">
                        <?php endif; ?>
                    </div>
                    
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p>by <?php echo htmlspecialchars($item['author']); ?></p>
                        <div class="cart-item-price">Rs<?php echo number_format($item['price'], 2); ?> each</div>
                    </div>
                    
                    <div class="cart-item-actions">
                        <form method="POST" class="cart-item-quantity">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                   min="1" max="<?php echo $item['stock']; ?>" 
                                   onchange="this.form.submit()">
                            <input type="hidden" name="update_cart" value="1">
                        </form>
                        
                        <a href="?remove=<?php echo $item['id']; ?>" class="remove-item" 
                           onclick="return confirm('Remove this item from cart?')">
                            <i class="fas fa-trash"></i>
                        </a>
                        
                        <div style="font-weight: 600; margin-top: 10px;">
                            Total: Rs<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                
                <div class="summary-item">
                    <span>Subtotal</span>
                    <span>Rs<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="summary-item">
                    <span>Shipping</span>
                    <span><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free'; ?></span>
                </div>
                
                <div class="summary-item summary-total">
                    <span>Total</span>
                    <span>Rs<?php echo number_format($total, 2); ?></span>
                </div>
                
                <a href="checkout.php" class="btn btn-primary" style="width: 100%; padding: 15px; margin-top: 20px;">
                    Proceed to Checkout
                </a>
                
                <a href="products.php" style="display: block; text-align: center; margin-top: 15px; color: #666;">
                    Continue Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>