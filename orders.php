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
    $error = "Service unavailable.";
}

include 'includes/header.php';

if(!isset($_SESSION['user_id'])) {
    echo '<div style="max-width: 600px; margin: 50px auto; text-align: center;">
            <h2>Please <a href="login.php">login</a> to view your orders</h2>
          </div>';
    include 'includes/footer.php';
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user orders
$orders = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll();
    } catch (Exception $e) {
        // Silent
    }
}
?>

<div class="container">
    <h1 class="section-title">My Orders</h1>
    
    <?php if(empty($orders)): ?>
        <div style="text-align: center; padding: 50px;">
            <i class="fas fa-box-open" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
            <h3>No orders found</h3>
            <p>You haven't placed any orders yet.</p>
            <a href="products.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

