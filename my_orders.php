<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

// Get username for this user
$stmt_user = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();
$username = $user_data ? $user_data['username'] : '';

// Get all orders for this user (by username only)
$stmt = $conn->prepare("SELECT o.id, o.customer_name, o.total_price, o.status, o.payment_proof_path, o.created_at FROM orders o WHERE o.customer_name = ? ORDER BY o.created_at DESC");
$stmt->bind_param('s', $username);
$stmt->execute();
$orders = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (isset($_POST['receive_order'])) {
        $oid = intval($_POST['order_id']);
        $conn->query("UPDATE orders SET status='completed' WHERE id=$oid");
    }
    if (isset($_POST['delete_order'])) {
        $oid = intval($_POST['order_id']);
        $conn->query("DELETE FROM order_items WHERE order_id=$oid");
        $conn->query("DELETE FROM orders WHERE id=$oid");
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>My Orders</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/design.css">
<style>
.order-card {
    background: var(--panel);
    border-radius: 18px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 6px 18px rgba(103,55,9,0.15);
}
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 2px solid rgba(161,120,81,0.2);
}
.order-id {
    font-family: 'Viga', sans-serif;
    font-size: 18px;
    color: var(--accent-dark);
    font-weight: 700;
}
.order-date {
    font-size: 14px;
    color: var(--text);
    opacity: 0.7;
}
.order-status {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
}
.status-pending {
    background: rgba(255,193,7,0.2);
    color: #c77d00;
}
.status-confirmed {
    background: rgba(76,175,80,0.2);
    color: #2e7d32;
}
.status-completed {
    background: rgba(33,150,243,0.2);
    color: #1565c0;
}
.status-cancelled {
    background: rgba(244,67,54,0.2);
    color: #c62828;
}
.order-items {
    margin: 16px 0;
}
.order-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    color: var(--text);
}
.item-name {
    font-weight: 600;
}
.item-qty {
    color: var(--accent);
    margin-left: 12px;
}
.order-total {
    display: flex;
    justify-content: space-between;
    padding-top: 16px;
    margin-top: 16px;
    border-top: 2px solid rgba(161,120,81,0.2);
    font-size: 18px;
    font-weight: 700;
    color: var(--accent-dark);
}
.payment-proof {
    margin-top: 12px;
    text-align: center;
}
.payment-proof img {
    max-width: 200px;
    border-radius: 12px;
    border: 3px solid var(--accent);
}
.no-orders {
    text-align: center;
    padding: 60px 20px;
    color: var(--text);
}
.no-orders-icon {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}
</style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="container">
    <div class="content-wrapper" style="max-width: 800px; margin-left: auto; margin-right: auto;">
        <h1 style="font-family: 'Seaweed Script', cursive; color: var(--accent-dark); text-align: center; margin-bottom: 32px; margin-top: 0;">My Orders</h1>
        
        <?php if ($orders->num_rows === 0): ?>
            <div class="no-orders">
                <div class="no-orders-icon">📦</div>
                <h2 style="font-family: 'Viga', sans-serif; color: var(--accent);">No orders yet</h2>
                <p>Start browsing our products and place your first order!</p>
                <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 12px 32px; background: linear-gradient(180deg, var(--accent), var(--accent-dark)); color: var(--panel); border-radius: 12px; text-decoration: none; font-weight: 700;">Browse Products</a>
            </div>
    <?php else: ?>
        <?php while ($order = $orders->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-id">Order #<?= $order['id'] ?></div>
                        <div class="order-date"><?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></div>
                    </div>
                    <span class="order-status status-<?= $order['status'] ?>">
                        <?= $order['status'] ?>
                    </span>
                </div>
                
                <div class="order-items">
                    <?php
                    // Get items for this order
                    $stmt2 = $conn->prepare("
                        SELECT p.name, oi.quantity, oi.price
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ?
                    ");
                    $stmt2->bind_param('i', $order['id']);
                    $stmt2->execute();
                    $items = $stmt2->get_result();
                    
                    while ($item = $items->fetch_assoc()):
                    ?>
                        <div class="order-item">
                            <span>
                                <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="item-qty">×<?= $item['quantity'] ?></span>
                            </span>
                            <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="order-total">
                    <span>Total</span>
                    <span>₱<?= number_format($order['total_price'], 2) ?></span>
                </div>
                
                <?php if ($order['payment_proof_path']): ?>
                    <div class="payment-proof">
                        <p style="font-size: 13px; color: var(--text); opacity: 0.7; margin-bottom: 8px;">Payment Proof</p>
                        <a href="<?= htmlspecialchars($order['payment_proof_path']) ?>" target="_blank" rel="noopener">
                            <img src="<?= htmlspecialchars($order['payment_proof_path']) ?>" alt="Payment proof">
                        </a>
                    </div>
                <?php endif; ?>
                
                <form method="post" style="margin-top: 16px; display: flex; gap: 12px; justify-content: center;">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <?php if ($order['status'] !== 'completed'): ?>
                    <button type="submit" name="receive_order" style="background: linear-gradient(180deg,#A17851,#6F5A47); color: var(--panel); border: none; border-radius: 8px; padding: 8px 18px; font-weight: 700; cursor: pointer;">Mark as Received</button>
                    <?php endif; ?>
                    <button type="submit" name="delete_order" style="background: linear-gradient(180deg,#ef7a7a,#d04b4b); color: var(--panel); border: none; border-radius: 8px; padding: 8px 18px; font-weight: 700; cursor: pointer;">Delete</button>
                </form>
            </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div><!-- end content-wrapper -->
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
