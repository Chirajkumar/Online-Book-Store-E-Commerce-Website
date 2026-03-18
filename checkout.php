<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
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
    die("Checkout service unavailable.");
}

include 'includes/header.php';

// Get cart totals for order
$total_amount = 0;
$cartItems = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, p.title, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cartItems = $stmt->fetchAll();
        
        foreach ($cartItems as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }
    } catch (Exception $e) {
        die("Cart fetch failed.");
    }
}

if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

// Process order (simplified - no payment gateway)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, order_status) VALUES (?, ?, 'Pending')");
        $stmt->execute([$user_id, $total_amount]);
        $order_id = $pdo->lastInsertId();
        
        // Move cart items to order_items
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = "Order placed successfully! Order ID: #$order_id";
        header("Location: orders.php");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Order failed: " . $e->getMessage();
    }
}
?>

<div class="container">
    <h1 class="section-title">Checkout</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <div class="checkout-order-summary">
            <h3>Order Summary</h3>
            <?php foreach ($cartItems as $item): ?>
            <div class="checkout-item">
                <span><?php echo htmlspecialchars($item['title']); ?> x<?php echo $item['quantity']; ?></span>
                <span>Rs<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
            </div>
            <?php endforeach; ?>
            <div class="checkout-total">
                <span>Total:</span>
                <span>$<?php echo number_format($total_amount, 2); ?></span>
            </div>
        </div>
        
        <div class="checkout-form">
            <h3>Billing Information</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" required>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="zip" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="">Select payment</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-checkout" style="width: 100%; padding: 15px; font-size: 18px;">
                    Place Order - Rs<?php echo number_format($total_amount, 2); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.checkout-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 30px;
}
.checkout-order-summary, .checkout-form {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}
.checkout-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}
.checkout-total {
    font-weight: bold;
    font-size: 20px;
    padding-top: 15px;
    border-top: 2px solid #3498db;
    margin-top: 15px;
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
    margin-bottom: 5px;
    font-weight: 500;
}
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}
.btn-checkout {
    background: #27ae60;
    border: none;
    color: white;
    cursor: pointer;
    transition: background 0.3s;
}
.btn-checkout:hover {
    background: #219a52;
}
@media (max-width: 768px) {
    .checkout-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

