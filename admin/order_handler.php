<?php

// handles order status - update, deduct ingredients on acceptance

session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
include '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    if (!in_array($status, ['pending', 'accepted', 'cancelled'])) {
        die('Invalid status');
    }
    
    // If accepting order - deduct ingredients
    if ($status === 'accepted') {
        // Get order items
        $stmt = $conn->prepare("SELECT oi.product_id, oi.quantity FROM order_items oi WHERE oi.order_id = ?");
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $items_res = $stmt->get_result();
        
        while ($item = $items_res->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Get ingredients for this product
            $stmt2 = $conn->prepare("SELECT pi.ingredient_id, pi.quantity_required FROM product_ingredients pi WHERE pi.product_id = ?");
            $stmt2->bind_param('i', $product_id);
            $stmt2->execute();
            $ing_res = $stmt2->get_result();
            
            while ($ing = $ing_res->fetch_assoc()) {
                $ingredient_id = $ing['ingredient_id'];
                $qty_needed = $ing['quantity_required'] * $quantity;
                
                // Deduct from ingredients
                $stmt3 = $conn->prepare("UPDATE ingredients SET stock_qty = stock_qty - ? WHERE id = ?");
                $stmt3->bind_param('di', $qty_needed, $ingredient_id);
                $stmt3->execute();
            }
        }
    }
    
    // Update order status
    $stmt4 = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt4->bind_param('si', $status, $order_id);
    $stmt4->execute();
    
    header('Location: dashboard.php');
    exit;
}
?>
