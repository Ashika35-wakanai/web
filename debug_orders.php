<?php
session_start();
include 'database.php';

echo "<h2>Debug: Database Orders</h2>";

// Check all orders
echo "<h3>All Orders:</h3>";
$result = $conn->query("SELECT id, customer_name, total_price, status, created_at FROM orders ORDER BY created_at DESC LIMIT 10");
if($result->num_rows > 0) {
    echo "<ul>";
    while($row = $result->fetch_assoc()) {
        echo "<li>Order #{$row['id']}: {$row['customer_name']} - ₱{$row['total_price']} ({$row['status']}) - {$row['created_at']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No orders found</p>";
}

echo "<h3>Current Session:</h3>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";

if(isset($_SESSION['username'])) {
    echo "<h3>Orders for " . $_SESSION['username'] . ":</h3>";
    $stmt = $conn->prepare("SELECT id, customer_name, total_price FROM orders WHERE customer_name = ?");
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        echo "<ul>";
        while($row = $result->fetch_assoc()) {
            echo "<li>Order #{$row['id']}: {$row['customer_name']} - ₱{$row['total_price']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No orders found for this user</p>";
    }
}
?>
