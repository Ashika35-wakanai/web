<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
include '../database.php';

// Get low stock ingredients
$low_stock = $conn->query("SELECT * FROM ingredients WHERE stock_qty <= low_stock_limit ORDER BY stock_qty ASC");

// Get pending orders with items
$orders_res = $conn->query("
  SELECT o.id, o.customer_name, o.total_price, o.payment_proof_path, o.created_at, 
         GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') as items
  FROM orders o
  LEFT JOIN order_items oi ON o.id = oi.order_id
  LEFT JOIN products p ON oi.product_id = p.id
  WHERE o.status = 'pending'
  GROUP BY o.id
  ORDER BY o.created_at DESC
");

$low = $conn->query("SELECT COUNT(*) as c FROM ingredients WHERE stock_qty <= low_stock_limit")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/design.css">
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<main class="container">
  <div class="content-wrapper admin-wrap">
    <h1 class="admin-title">Dashboard</h1>
    
    <div class="stats">
      <div class="stat-box">
        <div class="stat-label">Pending Orders</div>
        <div class="stat-value"><?= $pending ?></div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Low Stock Ingredients</div>
        <div class="stat-value"><?= $low ?></div>
      </div>
      <div class="stat-links">
        <a href="products.php">Manage Products</a>
        <a href="ingredients.php">Manage Ingredients</a>
        <a href="reports.php">View Reports</a>
        <a href="../index.php">View Site</a>
      </div>
    </div>

  <?php if ($low_stock->num_rows > 0): ?>
    <div class="low-stock-card">
      <h3>Low Stock Alert</h3>
      <p>The following ingredients are running low:</p>
      <ul>
        <?php while ($ing = $low_stock->fetch_assoc()): ?>
          <li><?= htmlspecialchars($ing['ingredient_name']) ?> - <?= number_format($ing['stock_qty'], 2) ?><?= htmlspecialchars($ing['unit']) ?> (Limit: <?= number_format($ing['low_stock_limit'], 2) ?>)</li>
        <?php endwhile; ?>
      </ul>
    </div>
  <?php endif; ?>

  <h2>Pending Orders</h2>
  <?php if($orders_res->num_rows == 0): ?>
    <p class="empty-state">No pending orders.</p>
  <?php else: while($order = $orders_res->fetch_assoc()): ?>
    <div class="order-card">
      <div class="order-header">
        <div>
          <div class="order-id">Order #<?= $order['id'] ?></div>
          <div class="order-date"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></div>
        </div>
      </div>

      <div class="order-detail">
        <span class="order-detail-label">Customer Name:</span>
        <span class="order-detail-value"><?= htmlspecialchars($order['customer_name'] ?: 'Customer') ?></span>
      </div>

      <div class="order-detail">
        <span class="order-detail-label">Date ordered:</span>
        <span class="order-detail-value"><?= date('M d, Y H:i A', strtotime($order['created_at'])) ?></span>
      </div>

      <div class="order-detail">
        <span class="order-detail-label">Order List:</span>
        <span class="order-detail-value"><?= htmlspecialchars($order['items']) ?></span>
      </div>

      <div class="order-detail">
        <span class="order-detail-label">Subtotal:</span>
        <span class="order-detail-value">₱<?= number_format($order['total_price'], 2) ?></span>
      </div>

      <div class="order-detail">
        <span class="order-detail-label">Paid via:</span>
        <span class="order-detail-value">CASH</span>
      </div>

      <?php if($order['payment_proof_path']): ?>
        <div class="payment-proof">
          <strong>Payment Proof:</strong><br>
          <img src="../<?= htmlspecialchars($order['payment_proof_path']) ?>" alt="Payment Proof">
        </div>
      <?php endif; ?>

      <div class="order-actions">
        <form method="POST" action="order_handler.php" style="display:inline;">
          <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
          <input type="hidden" name="status" value="accepted">
          <button type="submit" class="action-btn accept-btn">Accept</button>
        </form>
        <form method="POST" action="order_handler.php" style="display:inline;">
          <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
          <input type="hidden" name="status" value="cancelled">
          <button type="submit" class="action-btn reject-btn" onclick="return confirm('Reject this order?')">Reject</button>
        </form>
        <form method="POST" action="order_handler.php" style="display:inline;">
          <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
          <input type="hidden" name="status" value="cancelled">
          <button type="submit" class="action-btn cancel-btn" onclick="return confirm('Cancel this order?')">Cancel</button>
        </form>
      </div>
    </div>
  <?php endwhile; endif; ?>
  </div>
</main>
</body>
</html>
