<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$host = 'localhost';
$dbname = 'bookstore_db';
$username = 'root';
$password = '';

$pdo = false;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Service unavailable.");
}

include 'includes/header.php';

// Fetch order details
$order = false;
$orderItems = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $order = $stmt->fetch();
        
        if ($order) {
            $stmt = $pdo->prepare("SELECT oi.*, p.title, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id = ?");
            $stmt->execute([$id]);
            $orderItems = $stmt->fetchAll();
        }
    } catch (Exception $e) {
        // Silent
    }
}

if (!$order) {
    echo '<div style="text-align: center; padding: 50px; color: #666;">
            <h2>Order not found</h2>
            <a href="orders.php">Back to Orders</a>
          </div>';
    include 'includes/footer.php';
    exit();
}
?>

<div class="container">
    <div class="order-details">
        <h1>Order #<?php echo $order['id']; ?></h1>
        
        <div class="order-header">
            <div class="order-info">
                <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </p>
            </div>
            <a href="orders.php" class="btn btn-outline">← Back to Orders</a>
        </div>
        
        <h3>Order Items</h3>
        <div class="order-items">
            <?php foreach($orderItems as $item): ?>
            <div class="order-item">
                <div class="order-item-image">
                    <img src="assets/images/products/<?php echo $item['image'] ?: 'no-cover.jpg'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                </div>
                <div class="order-item-details">
                    <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                    <p>Qty: <?php echo $item['quantity']; ?> | Price: $<?php echo number_format($item['price'], 2); ?></p>
                    <p><strong>Subtotal: $<?php echo number_format($item['quantity'] * $item['price'], 2); ?></strong></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.order-details {
    max-width: 800px;
}
.order-header {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.status-pending { background: #f39c12; color: white; }
.status-shipped { background: #3498db; color: white; }
.status-delivered { background: #27ae60; color: white; }
.order-items {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.order-item {
    display: flex;
    gap: 20px;
    padding: 20px 0;
    border-bottom: 1px solid #eee;
}
.order-item:last-child {
    border-bottom: none;
}
.order-item-image img {
    width: 80px;
    height: 120px;
    object-fit: cover;
    border-radius: 5px;
}
@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        text-align: center;
    }
    .order-item {
        flex-direction: column;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

