<?php
require_once '../config/db.php';

$pdo = getDB();
if ($pdo) {
    echo "DB connected successfully.<br>";
    // Run setup SQL
    $sql = file_get_contents('setup.sql');
    try {
        $pdo->exec($sql);
        echo "Database and tables created successfully!";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Start MySQL first!";
}
?>
<a href="dashboard.php">Go to Dashboard</a>
