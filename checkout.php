<?php
session_start();
include 'database.php';
$cart = $_SESSION['cart'] ?? [];
if (!$cart) { header('Location: index.php'); exit; }
$ids = implode(',', array_map('intval',array_keys($cart)));
$res = $conn->query("SELECT id,name,price FROM products WHERE id IN ($ids)");
$products = []; while($r=$res->fetch_assoc()) $products[$r['id']] = $r;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    // save order (pending) and upload payment proof
    $total = 0;
    foreach($cart as $pid=>$q) $total += $products[$pid]['price']*$q;
    $payment_path = null;
    if(!empty($_FILES['payment_proof']['tmp_name'])){
        $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $payment_path = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['payment_proof']['tmp_name'], $payment_path);
    }
    $stmt = $conn->prepare("INSERT INTO orders (customer_name,total_price,payment_proof_path,status,created_at) VALUES (?, ?, ?, 'pending', NOW())");
    // Use form input if provided and not empty, otherwise use username from session
    $customer = (!empty($_POST['customer_name'])) ? $_POST['customer_name'] : ($_SESSION['username'] ?? 'Guest');
    $customer = trim($customer); // Remove any whitespace
    $stmt->bind_param('sds', $customer, $total, $payment_path);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    
    // Debug output (remove after testing)
    error_log("Order saved: customer_name='{$customer}', order_id={$order_id}, session_username='{$_SESSION['username']}', post_customer='{$_POST['customer_name']}'");
    
    foreach($cart as $pid=>$q){
        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?, ?, ?, ?)"); 
        $price = $products[$pid]['price'];
        $stmt2->bind_param('iiid', $order_id, $pid, $q, $price);
        $stmt2->execute();
        
        // Record product order count
        $order_date = date('Y-m-d');
        $order_month = date('Y-m');
        $stmt3 = $conn->prepare("INSERT INTO product_order_count (product_id, order_date, order_month, quantity) VALUES (?, ?, ?, ?)");
        $stmt3->bind_param('issi', $pid, $order_date, $order_month, $q);
        $stmt3->execute();
    }
    unset($_SESSION['cart']);
    header('Location: index.php?order=placed');
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Checkout</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/design.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="container">
  <div class="content-wrapper" style="max-width: 600px; margin: 0 auto; padding: 40px 44px;">
    <h1 style="font-family: 'Seaweed Script', cursive; color: var(--accent-dark); text-align: center; margin-bottom: 32px; margin-top: 0;">Checkout</h1>
    
    <form method="post" enctype="multipart/form-data">
      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 15px; color: var(--text); margin-bottom: 8px; font-weight: 600;">Customer Name (optional)</label>
        <input type="text" name="customer_name" style="width: 100%; padding: 14px 16px; border: 3px solid var(--accent); border-radius: 12px; background: rgba(255,255,255,0.6); color: var(--text); font-size: 15px; font-family: 'Viga', sans-serif; box-sizing: border-box;">
      </div>
      
      <div style="margin-bottom: 32px;">
        <label style="display: block; font-size: 15px; color: var(--text); margin-bottom: 8px; font-weight: 600;">Upload Payment Proof (image)</label>
        <input type="file" name="payment_proof" accept="image/*" style="width: 100%; padding: 14px 16px; border: 3px solid var(--accent); border-radius: 12px; background: rgba(255,255,255,0.6); color: var(--text); font-size: 14px; font-family: 'Viga', sans-serif; box-sizing: border-box;">
      </div>
      
      <button type="submit" style="width: 100%; padding: 16px; background: linear-gradient(180deg, var(--accent), var(--accent-dark)); color: var(--panel); border: none; border-radius: 12px; font-weight: 700; font-size: 16px; font-family: 'Viga', sans-serif; cursor: pointer; box-shadow: 0 6px 16px rgba(0,0,0,0.18); transition: transform 0.15s, box-shadow 0.15s;">Place Order</button>
    </form>
  </div><!-- end content-wrapper -->
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
